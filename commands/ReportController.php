<?php

namespace app\commands;

use app\models\activerecord\Reports;
use app\models\Client;
use yii\base\Exception;

class ReportController extends BaseController
{
	const PAGES_LIMIT = 5;

	const RESPONSE_PER_PAGE_ITEMS_COUNT = 25;

	const CSV_ROW_EX_TITLE_I = 'Executive Title ';
	const CSV_ROW_EX_FIRSTNAME_I = 'Executive First Name ';
	const CSV_ROW_EX_LASTNAME_I = 'Executive Last Name ';
	const CSV_ROW_KEYWORD = 'keyword';

	const EX_MAX_I = 20;

	/** @var resource */
	private $_finalCsvHandle;

	public function actionIndex()
	{
		ini_set( 'memory_limit', '256M' );
		$report = Reports::find()
			->where( [ '!=', 'status', Reports::STATUS_FINISHED ] )
			->andWhere( [ 'in_work' => false ] )
			->one();

		if ( !$report )
		{
			$query = \Yii::$app->db->createCommand( 'SELECT filename, created, repeat_in_days FROM `reports` WHERE created IS NOT NULL AND repeat_in_days IS NOT NULL AND in_work = 0 AND repeat_in_days != 0 AND UNIX_TIMESTAMP(created) < UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - 86400 * repeat_in_days' )->queryOne();
			if ( $query && isset( $query['filename'] ) )
			{
				$report = Reports::findOne( $query['filename'] );
				$report->status = Reports::STATUS_JUST_CREATED;
			}
		}

		if ( !$report )
			return;

		$tr = \Yii::$app->db->beginTransaction();
		$report->in_work = true;
		$report->save();
		$tr->commit();

		$this->_finalCsvHandle = fopen( $report->getCsvPath(), 'a' );
		self::log( "Started working on report $report->filename" );

		try
		{
			$this->_generateReport( $report );
			$this->_sendReport( $report );
		}
		catch ( \Exception $e )
		{
			$report->in_work = false;
			\Yii::$app->db->close();
			\Yii::$app->db->open();
			$report->save();

			$this->_gracefulFinishWork();

			throw $e;
		}

		$this->_gracefulFinishWork();

		self::log( "", true, true );
	}

	/**
	 * @param $searchResult
	 *
	 * @return array
	 */
	private function _extractKeywords( $searchResult )
	{
		$result = [];
		foreach ( $searchResult->jsonArray as $row )
		{
			$result[] = $row[0];
		}

		return $result;
	}

	/**
	 * @param Reports $report
	 *
	 * @throws \yii\base\Exception
	 * @throws \yii\db\Exception
	 * @throws \yii\web\HttpException
	 */
	private function _generateReport( Reports $report )
	{
		if ( $report->status >= Reports::STATUS_PROCESSING )
			return;

		self::log( "Entering 'getting data' step." );

		$report->status = Reports::STATUS_PROCESSING;
		$report->save();

		$client = new Client();
		$client->checkLogin();
		$params = $report->getParams();
		$client->getKeywordsAutocomplete( $params->keyword );

		if ( $report->count_pages_done === null )
			$report->count_pages_done = 1;

		$searchResult = null;
		do
		{
			if ( $searchResult === null )
			{
				$searchResult = $client->getSearchResult( $params->keywords, $report->count_pages_done );
				$keywords = $this->_extractKeywords( $searchResult );
				if ( $report->count_all === null )
				{
					$report->count_all = $searchResult->totalrecords;
					$report->save();
				}
			}
			else
			{
				$keywords = $this->_extractKeywords( $client->getSearchResult( $params->keywords, $report->count_pages_done ) );
			}

			if ( !$searchResult || !$searchResult->totalpages )
			{
				throw new Exception( "Coudln't get proper searchResult:\n" . var_export( $searchResult, true ) );
			}

			$details = $client->getDetailsByKeywords( $keywords, $report );
			if ( $report->count_pages_done == 1 )
			{
				fputcsv( $this->_finalCsvHandle, \app\models\report\Details::GetCsvTitileColumns() );
			}

			foreach ( $details as $detail )
			{
				foreach ( $detail->getCsvRows() as $row )
				{
					fputcsv( $this->_finalCsvHandle, $row );
					$report->addJsonEntity( $row );
				}
			}

			self::log( "Done page {$report->count_pages_done} of {$searchResult->totalpages}." );
			$report->count_pages_done++;
			\Yii::$app->db->close();
			\Yii::$app->db->open();
			$report->save();
		}
		while ( $report->count_pages_done <= $searchResult->totalpages );

		$report->created = \Yii::$app->formatter->asDatetime( time(), 'php:Y-m-d H:i:s' );
		\Yii::$app->db->close();
		\Yii::$app->db->open();
		$report->save();
	}

	/**
	 * @param Reports $report
	 */
	private function _sendReport( Reports $report )
	{
		$c = \Yii::$app->mailer->compose()
			->setTo( $report->email )
			->setSubject( "Report" )
			->setTextBody( "Report is in attachments" )
			->setFrom( 'admin@clcdatahub.com' )
			->attach( $report->getCsvPath(), [ 'fileName' => 'report.csv', 'contentType' => 'text/csv' ] )
			->send();

		if ( $c )
		{
			$report->status = Reports::STATUS_FINISHED;
			$report->save();
			self::log( "Mail sent to $report->email" );
		}
	}

	private function _gracefulFinishWork()
	{
		fclose( $this->_finalCsvHandle );
	}
}
