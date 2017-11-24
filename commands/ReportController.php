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

	const EX_MAX_I = 20;

	private $_finalCsvColumnsTitle = [
		self::CSV_ROW_COMPANY,
		self::CSV_ROW_WEBSITE,
		self::CSV_ROW_PHONE,
		self::CSV_ROW_CITY,
		self::CSV_ROW_STATE,
		'Executive Title',
		'Executive Name',
	];

	public function actionIndex()
	{
		ini_set( 'memory_limit', '128M' );
		$report = Reports::find()->where( [ '=', 'status', Reports::STATUS_JUST_CREATED ] )->one();
		if ( !$report )
		{
			$query = \Yii::$app->db->createCommand( 'SELECT filename, last_finished, repeat_in_days FROM `reports` WHERE last_finished IS NOT NULL AND repeat_in_days IS NOT NULL AND repeat_in_days != 0 AND UNIX_TIMESTAMP(last_finished) < UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - 86400 * repeat_in_days' )->queryOne();
			if ( $query && isset( $query['filename'] ) )
			{
				$report = Reports::findOne( $query['filename'] );
				$report->status = Reports::STATUS_JUST_CREATED;
			}
		}

		if ( !$report )
			return;

		try
		{
			$this->_getParts( $report );
			$this->_generateReport( $report );
			$this->_sendReport( $report );
		}
		catch ( \Exception $e )
		{
			$report->status = Reports::STATUS_JUST_CREATED;
			$report->save();

			throw $e;
		}

		$this->log( "", true, true );
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
	 */
	private function _getParts( Reports $report )
	{
		if ( $report->status >= Reports::STATUS_PROCESSING_GOT_PARTS )
			return;

		$this->log( "Started working on report $report->filename" );
		$params = $report->getParams();

		$client = new Client();
		$client->checkLogin();

		$client->getKeywordsAutocomplete( $params->keyword );
		$data = $client->getSearchResult( $params->keywords, 1 );

		$this->log( "Getting keywords" );
		$keywords = $this->_extractKeywords( $data );

		$lastI = 1;
		for ( $i = 2; $i <= $data->totalpages; $i++ )
		{
			$keywords = array_merge( $keywords, $this->_extractKeywords( $client->getSearchResult( $params->keywords, $i ) ) );

			if ( $i % self::PAGES_LIMIT == 0 )
			{
//				$fn = $report->saveCsvReportPart( $client->getCsvReport( $keywords ), $lastI, $i );
//				$this->log( "Got " . $this->_countLines( $fn ) . " lines for $lastI - $i" );
				$lastI = $i;
				$keywords = [];
			}
		}

		$emails = $client->extractEmails( $keywords );
erd(123);
		if ( count( $keywords ) )
		{
			$fn = $report->saveCsvReportPart( $client->getCsvReport( $keywords ), $lastI, $i );
			$this->log( "Got " . $this->_countLines( $fn ) . " lines for $lastI - $i" );
		}

		$report->status = Reports::STATUS_PROCESSING_GOT_PARTS;
		$report->save();
	}

	/**
	 * @param Reports $report
	 */
	private function _generateReport( Reports $report )
	{
		if ( $report->status >= Reports::STATUS_PROCESSING_GENERATED_FINAL_CSV )
			return;

		$this->log( "Creating final csv" );

		$finalCsvHandle = fopen( $report->getCsvPath(), 'w' );
		$files = FileHelper::findFiles( $report->getReportPartsDir() );
		natsort( $files );

		foreach ( $files as $file )
		{
			$this->log( "File: $file" );
			$partSourceHandle = fopen( $file, 'r' );

			$columns = fgetcsv( $partSourceHandle );
			fputcsv( $finalCsvHandle, $this->_finalCsvColumnsTitle );
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

						fputcsv( $finalCsvHandle, $rowToInsert );

						$report->addJsonEntity( $rowToInsert );

						$this->log( '.', false, true );
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
			$this->log( '', true, true );
		}

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
			$this->log( "Mail sended to $report->email" );
		}
	}
}
