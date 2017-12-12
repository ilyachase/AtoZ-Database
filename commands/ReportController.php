<?php

namespace app\commands;

use app\models\activerecord\Reports;
use app\models\Client;
use yii\helpers\FileHelper;

class ReportController extends BaseController
{
	const PAGES_LIMIT = 4;

	const CSV_ROW_COMPANY = 'Business Name';
	const CSV_ROW_WEBSITE = 'Website';
	const CSV_ROW_PHONE = 'Phone';
	const CSV_ROW_CITY = 'Physical City';
	const CSV_ROW_STATE = 'Physical State';
	const CSV_ROW_EX_TITLE_I = 'Executive Title ';
	const CSV_ROW_EX_FIRSTNAME_I = 'Executive First Name ';
	const CSV_ROW_EX_LASTNAME_I = 'Executive Last Name ';
	const CSV_ROW_KEYWORD = 'keyword';

	const EX_MAX_I = 20;

	private $_finalCsvColumnsTitle = [
		self::CSV_ROW_COMPANY,
		self::CSV_ROW_WEBSITE,
		self::CSV_ROW_PHONE,
		self::CSV_ROW_CITY,
		self::CSV_ROW_STATE,
		'Executive Title',
		'Executive Name',
		'Executive Email',
	];

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

		self::log( "Started working on report $report->filename" );

		try
		{
			$this->_getParts( $report );
			$this->_generateReport( $report );
			$this->_sendReport( $report );
		}
		catch ( \Exception $e )
		{
			$report->in_work = false;
			\Yii::$app->db->close();
			\Yii::$app->db->open();
			$report->save();

			throw $e;
		}

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
	private function _getParts( Reports $report )
	{
		if ( $report->status >= Reports::STATUS_PROCESSING_GOT_PARTS )
			return;

		self::log( "Entering 'getting parts' step." );

		$params = $report->getParams();

		$client = new Client();
		$client->checkLogin();

		$client->getKeywordsAutocomplete( $params->keyword );
		$data = $client->getSearchResult( $params->keywords, 1 );

		self::log( "Getting keywords" );
		$keywords = $this->_extractKeywords( $data );

		$lastI = 1;
		for ( $i = 2; $i <= $data->totalpages; $i++ )
		{
			if ( $i % self::PAGES_LIMIT == 0 )
			{
				if ( $report->isHavePart( $lastI, $i ) )
				{
					if ( count( $keywords ) )
						$keywords = [];

					self::log( "Already got part {$lastI}_{$i}, continue..." );
					$lastI = $i;
				}
				else
				{
					$i = $lastI + 1;
					break;
				}
			}
		}

		for ( ; $i <= $data->totalpages; $i++ )
		{
			$keywords = array_merge( $keywords, $this->_extractKeywords( $client->getSearchResult( $params->keywords, $i ) ) );

			if ( $i % self::PAGES_LIMIT == 0 )
			{
				$emails = $client->extractEmails( $keywords, $report );
				$fn = $report->saveCsvReportPart( $client->getCsvReport( $keywords ), $lastI, $i, $emails );
				self::log( "Got " . $this->_countLines( $fn ) . " lines for $lastI - $i (of $data->totalpages total)." );
				$lastI = $i;
				$keywords = [];
			}
		}

		if ( count( $keywords ) )
		{
			$emails = $client->extractEmails( $keywords, $report );
			$fn = $report->saveCsvReportPart( $client->getCsvReport( $keywords ), $lastI, $i, $emails );
			self::log( "Got " . $this->_countLines( $fn ) . " lines for $lastI - $i" );
		}

		$report->status = Reports::STATUS_PROCESSING_GOT_PARTS;
		\Yii::$app->db->close();
		\Yii::$app->db->open();
		$report->save();
	}

	/**
	 * @param Reports $report
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	private function _generateReport( Reports $report )
	{
		if ( $report->status >= Reports::STATUS_PROCESSING_GENERATED_FINAL_CSV )
			return;

		self::log( "Entering 'generate report' step." );

		$finalCsvHandle = fopen( $report->getCsvPath(), 'w' );
		$files = FileHelper::findFiles( $report->getCreateReportPartsDir() );
		natsort( $files );

		$emails = [];
		$emailsFilename = null;
		if ( ( $key = array_search( $report->getEmailsFilename(), $files ) ) !== false )
		{
			$emailsFilename = $files[$key];
			$emails = unserialize( file_get_contents( $emailsFilename ) );
		}

		fputcsv( $finalCsvHandle, $this->_finalCsvColumnsTitle );
		foreach ( $files as $file )
		{
			if ( $file == $report->getEmailsFilename() )
				continue;

			self::log( "File: $file" );
			$partSourceHandle = fopen( $file, 'r' );

			$columns = fgetcsv( $partSourceHandle );
			while ( ( $data = fgetcsv( $partSourceHandle ) ) !== false )
			{
				$namedSourceRow = [];
				foreach ( $columns as $k => $column )
				{
					$namedSourceRow[$column] = $data[$k];
				}

				$c = 0;
				for ( $i = 1; $i <= self::EX_MAX_I; $i++ )
				{
					if ( $namedSourceRow[self::CSV_ROW_EX_FIRSTNAME_I . $i] )
					{
						$rowToInsert = [
							$namedSourceRow[self::CSV_ROW_COMPANY],
							$namedSourceRow[self::CSV_ROW_WEBSITE],
							$namedSourceRow[self::CSV_ROW_PHONE],
							$namedSourceRow[self::CSV_ROW_CITY],
							$namedSourceRow[self::CSV_ROW_STATE],
							$namedSourceRow[self::CSV_ROW_EX_TITLE_I . $i],
							$namedSourceRow[self::CSV_ROW_EX_FIRSTNAME_I . $i] . ' ' . $namedSourceRow[self::CSV_ROW_EX_LASTNAME_I . $i],
						];

						if ( isset( $emails[$namedSourceRow[self::CSV_ROW_KEYWORD]] ) && isset( $emails[$namedSourceRow[self::CSV_ROW_KEYWORD]][$i - 1] ) )
						{
							$rowToInsert[] = $emails[$namedSourceRow[self::CSV_ROW_KEYWORD]][$i - 1];
						}
						else
						{
							$rowToInsert[] = '';
						}

						fputcsv( $finalCsvHandle, $rowToInsert );

						$report->addJsonEntity( $rowToInsert );

						self::log( '.', false, true );
						$c++;
					}
				}

				if ( $c )
				{
					$report->count += $c;
					$report->save();
				}
			}

			fclose( $partSourceHandle );
			unlink( $file );
			self::log( '', true, true );
		}

		if ( $emailsFilename )
			unlink( $emailsFilename );

		rmdir( $report->getCreateReportPartsDir( false ) );

		fclose( $finalCsvHandle );

		$report->status = Reports::STATUS_PROCESSING_GENERATED_FINAL_CSV;
		$report->created = \Yii::$app->formatter->asDatetime( time(), 'php:Y-m-d H:i:s' );
		$report->save();
	}

	/**
	 * @param string $filepath
	 *
	 * @return int
	 */
	private function _countLines( $filepath )
	{
		$linecount = 0;
		$handle = fopen( $filepath, "r" );

		while ( !feof( $handle ) )
		{
			fgets( $handle );
			$linecount++;
		}
		fclose( $handle );

		return $linecount;
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
			self::log( "Mail sended to $report->email" );
		}
	}
}
