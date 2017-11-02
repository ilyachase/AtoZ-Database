<?php

namespace app\commands;

use app\models\activerecord\Reports;
use app\models\Client;

class ReportController extends BaseController
{
	public function actionIndex()
	{
		ini_set( 'memory_limit', '128M' );
		$report = Reports::findOne( [ 'status' => Reports::STATUS_JUST_CREATED ] );
		if ( !$report )
			return;

		$report->status = Reports::STATUS_PROCESSING;
//		$report->save();
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
			$this->log( ".", false, false );

//			if ( $i % 40 == 0 )
			if ( $i % 2 == 0 )
			{
				$report->saveCsvReportPart( $client->getCsvReport( $keywords ), $lastI, $i );
				$lastI = $i;
				$keywords = [];
			}
		}

		if ( count( $keywords ) )
		{
			$report->saveCsvReportPart( $client->getCsvReport( $keywords ), $lastI, $i );
		}

		$this->log( "", true, false );
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
}
