<?php

namespace app\models;

use app\commands\BaseController;
use app\commands\ReportController;
use app\models\activerecord\Reports;
use app\models\report\Params;
use \linslin\yii2\curl\Curl;
use yii\web\HttpException;

class Client
{
	const PROCESSES_NUM = 10;

	/** @var Curl */
	private $_curl;

	/** @var string */
	private $_cookiePath;

	public function __construct()
	{
		$this->_cookiePath = tempnam( \Yii::getAlias( "@runtime" ) . DS . 'cookie', 'cookie' );
		$this->_curl = new Curl();
		$this->_curl->reset()
			->setOptions( [
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HEADER         => 1,
				CURLOPT_FOLLOWLOCATION => 1,
				CURLOPT_ENCODING       => "",
				CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML => like Gecko) Chrome/58.0.3029.96 Safari/537.36',
				CURLOPT_CONNECTTIMEOUT => 120,
				CURLOPT_TIMEOUT        => 120,
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_COOKIEJAR      => $this->_cookiePath,
				CURLOPT_COOKIEFILE     => $this->_cookiePath,
			] );
	}

	public function __destruct()
	{
		if ( $this->_curl->curl !== null )
			curl_close( $this->_curl->curl );
		if ( file_exists( $this->_cookiePath ) )
			unlink( $this->_cookiePath );
	}

	public function checkLogin()
	{
		$this->_curl->get( 'https://www.carnegielibrary.org/research/page/3/' );
		$this->_curl->get( 'https://www.atozdatabases.com/' );
		$this->_curl
			->setPostParams( [
				'referer'         => 'https://www.carnegielibrary.org/research/page/3/',
				'authrefLanguage' => 'null',
			] )
			->post( 'https://www.atozdatabases.com/home' );
		$this->_curl
			->setPostParams( [
				'libraryCardId' => 11812015896843,
				'accountId'     => 2011000265,
				'isInternal'    => false,
				'refURL'        => 'https://www.carnegielibrary.org/research/page/3/',
			] )
			->post( 'https://www.atozdatabases.com/librarycardsignin' );

		$this->_curl
			->setPostParams( [
				'accountId'     => 2011000265,
				'isInternal'    => false,
				'refURL'        => 'https://www.carnegielibrary.org/research/page/3/',
				'libraryCardId' => 11812015896843,
			] )
			->post( 'https://www.atozdatabases.com/librarycardsignin' );
	}

	/**
	 * @param string $keyword
	 *
	 * @return mixed
	 */
	public function getKeywordsAutocomplete( $keyword )
	{
		$this->_curl->setPostParams( [
			'field'    => 'SIC_Description',
			'criteria' => trim( $keyword ),
			'database' => 'business',
			'page'     => 'search',
		] )->post( 'https://www.atozdatabases.com/ajax/rpc/getReferenceCallData.htm?' );

		return json_decode( $this->_curl->response )->jsonarray;
	}

	/**
	 * @param array $keywords
	 *
	 * @return string
	 */
	public function getCount( array $keywords )
	{
		$keywordsQuery = '';
		foreach ( $keywords as $keyword )
		{
			$keywordsQuery .= '&Add_SIC_Description=' . urlencode( $keyword );
		}

		$this->_curl
			->setRequestBody( 'count=---&database=business&search_count=&page=search&searchType=general&searchmode=&mode=&marketingSelect=&cancelSearch=&searchCheckedDetails=&nameTreeView=&selectTreeView=&parentTreeView=&treeMetaField=&nameTreeViewExpenditure=&selectTreeViewExpenditure=&parentTreeViewExpenditure=&treeMetaFieldExpenditure=&Map_proximity=N%2FA&Map_Physical_State=N%2FA&Map_Vendor_State_County=N%2FA&Meta~SIC_Description=on&Meta~Record_Type=on&Ref_Physical_State_Physical_City=Select+a+State&Ref_CBSA_Code=Select+a+State&Ref_Vendor_State_County=Select+a+State&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip_Paste=&Add_Physical_Address=&Add_proximity=&Add_proximity=&Add_proximity=&Ref_SIC_Description=auto&Ref_Advanced_SIC_Keyword=SIC_Description&hid_SIC_Description=auto&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry_Paste=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS_Paste=&Add_Company_Name=&Add_Prefix=-1&Add_First_Name=&Add_Middle_Initial=&Add_Last_Name=&Add_Suffix=-1&Add_Employees_Advanced_From=&Add_Employees_Advanced_To=&Add_SalesAnnualRevenue_Advanced_From=&Add_SalesAnnualRevenue_Advanced_To=&Add_Phone=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code_Paste=&Add_EIN=&Add_URL=&Add_Record_Type=1&countForDownload=38%2C849' . $keywordsQuery )
			->post( 'https://www.atozdatabases.com/ajax/search-business-updatecount.htm' );

		return json_decode( $this->_curl->response )->count;
	}

	/**
	 * @param array $keywords
	 * @param $page
	 *
	 * @return \stdClass
	 * @throws HttpException
	 */
	public function getSearchResult( array $keywords, $page )
	{
		$keywordsQuery = '';
		foreach ( $keywords as $keyword )
		{
			$keywordsQuery .= '&Add_SIC_Description=' . urlencode( $keyword );
		}

		$page = (int) $page;
		if ( $page < 1 )
			throw new HttpException( 400, 'Page should be unsigned positive integer' );

		if ( $page == 1 )
		{
			$this->_curl
				->setRequestBody( 'count=---&database=business&search_count=&page=search&searchType=general&searchmode=&mode=&marketingSelect=&cancelSearch=&searchCheckedDetails=&nameTreeView=&selectTreeView=&parentTreeView=&treeMetaField=&nameTreeViewExpenditure=&selectTreeViewExpenditure=&parentTreeViewExpenditure=&treeMetaFieldExpenditure=&Map_proximity=N%2FA&Map_Physical_State=N%2FA&Map_Vendor_State_County=N%2FA&Meta~SIC_Description=on&Meta~Record_Type=on&Ref_Physical_State_Physical_City=Select+a+State&Ref_CBSA_Code=Select+a+State&Ref_Vendor_State_County=Select+a+State&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip_Paste=&Add_Physical_Address=&Add_proximity=&Add_proximity=&Add_proximity=&Ref_SIC_Description=auto&Ref_Advanced_SIC_Keyword=SIC_Description&hid_SIC_Description=auto&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry_Paste=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS_Paste=&Add_Company_Name=&Add_Prefix=-1&Add_First_Name=&Add_Middle_Initial=&Add_Last_Name=&Add_Suffix=-1&Add_Employees_Advanced_From=&Add_Employees_Advanced_To=&Add_SalesAnnualRevenue_Advanced_From=&Add_SalesAnnualRevenue_Advanced_To=&Add_Phone=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code_Paste=&Add_EIN=&Add_URL=&Add_Record_Type=1&countForDownload=' . $keywordsQuery )
				->post( 'https://www.atozdatabases.com/ajax/search-business-updatecount.htm?persist=yes' );

			$this->_curl
				->setPostParams( [
					'database'            => 'business',
					'page'                => 'search',
					'removeStateCriteria' => '',
				] )
				->post( 'https://www.atozdatabases.com/ajax/search-result.htm' );
		}
		else
		{
			$this->_curl
				->setRequestBody( 'database=business&mode=&page=result&dynamicColumn=&selectedRecordCount=0&maxRecordCount=1000&uniqueIdForDetailPage=&searchType=general&currentPage=' . $page . '&combinedsearchType=&resultFrom=&isMap=&corporateLinkageId=&corporateLinkageField=&corporateFamilyCondition=&corporateFamilyRecId=&corporateFamilyUltimateId=&corporateFamilyImmediateId=&marketingSelect=&reverseJob=&printCredits=0&downloadCredits=0&emailCredits=0&recordsPerCred=&isPatronUser=&isBulkDownloadAvailable=&userSearchCount=38849&removeStateCriteria=&findPersonOnResidents=&paginationuppertextbox=2&paginationuppertextbox=2&sort_by=1&then1_by=1&then2_by=1&email_format=pdf&email_level_detail=results_export&page_type=print&format_print=1&level_detail_Print=1&download_format=1&level_detail=1&paginationuppertextbox=' )
				->post( 'https://www.atozdatabases.com/ajax/search-result-business1.htm' );
		}

		$result = json_decode( $this->_curl->response );
		unset( $result->jsonArray[0] );
		unset( $result->jsonArray[1] );

		return $result;
	}

	/**
	 * @param array $csvKeywords
	 * @param array $searchKeywords
	 * @param string $keyword
	 *
	 * @return string
	 */
	public function getCsvReport( array $csvKeywords, array $searchKeywords = [], $keyword = '' )
	{
		if ( $keyword )
			$this->getKeywordsAutocomplete( $keyword );

		if ( count( $searchKeywords ) )
			$this->getSearchResult( $searchKeywords, 1 );

		$keywordsString = urlencode( implode( ',', $csvKeywords ) );

		$this->_curl
			->setRequestBody( "format=comma&viewMode=custom_export&database=business&customeName=&selectedCheckboxes=$keywordsString&selectedSections=Business+Name%2CFirst+Name%2CLast+Name%2CWebsite%2CPhone%2CPhysical+City%2CPhysical+State%2CExecutive+First+Name+1%2CExecutive+Last+Name+1%2CExecutive+First+Name+2%2CExecutive+Last+Name+2%2CExecutive+First+Name+3%2CExecutive+Last+Name+3%2CExecutive+First+Name+4%2CExecutive+Last+Name+4%2CExecutive+First+Name+5%2CExecutive+Last+Name+5%2CExecutive+First+Name+6%2CExecutive+Last+Name+6%2CExecutive+First+Name+7%2CExecutive+Last+Name+7%2CExecutive+First+Name+8%2CExecutive+Last+Name+8%2CExecutive+First+Name+9%2CExecutive+Last+Name+9%2CExecutive+First+Name+10%2CExecutive+Last+Name+10%2CExecutive+First+Name+11%2CExecutive+Last+Name+11%2CExecutive+First+Name+12%2CExecutive+Last+Name+12%2CExecutive+First+Name+13%2CExecutive+Last+Name+13%2CExecutive+First+Name+14%2CExecutive+Last+Name+14%2CExecutive+First+Name+15%2CExecutive+Last+Name+15%2CExecutive+First+Name+16%2CExecutive+First+Name+17%2CExecutive+Last+Name+16%2CExecutive+Last+Name+17%2CExecutive+First+Name+18%2CExecutive+Last+Name+18%2CExecutive+First+Name+19%2CExecutive+Last+Name+19%2CExecutive+First+Name+20%2CExecutive+Last+Name+20%2CExecutive+Title+1%2CExecutive+Title+2%2CExecutive+Title+3%2CExecutive+Title+4%2CExecutive+Title+5%2CExecutive+Title+6%2CExecutive+Title+7%2CExecutive+Title+8%2CExecutive+Title+9%2CExecutive+Title+10%2CExecutive+Title+11%2CExecutive+Title+12%2CExecutive+Title+13%2CExecutive+Title+14%2CExecutive+Title+15%2CExecutive+Title+16%2CExecutive+Title+17%2CExecutive+Title+18%2CExecutive+Title+19%2CExecutive+Title+20&downloadOptCheckedVal=0&totalRecordCount=38979&searchType=general" )
			->post( 'https://www.atozdatabases.com/ajax/submitDownload.htm' );
		$this->_curl
			->post( 'https://www.atozdatabases.com/exportdownload.htm' );

		$result = $this->_curl->response;

		$result = explode( "\n", $result );

		foreach ( $result as $k => $line )
		{
			if ( $k == 0 )
			{
				$result[$k] .= ReportController::CSV_ROW_KEYWORD . ",";
				continue;
			}

			if ( $k == count( $result ) - 1 )
				break;

			$result[$k] .= '"' . $csvKeywords[$k - 1] . '",';
		}

		$result = implode( "\n", $result );

		return $result;
	}

	/**
	 * @param string $id
	 *
	 * @return \StdClass
	 */
	public function getDetails( $id )
	{
		$this->_curl->post( "https://www.atozdatabases.com/ajax/rpc/totalSelectedRecordsCount.htm?_synchronizerTokenResult=1508318135910572191596789001015&database=business&mode=&page=result&dynamicColumn=&selectedRecordCount=0&maxRecordCount=1000&uniqueIdForDetailPage=$id&searchType=general&currentPage=1&totalRecords=38979&combinedsearchType=&resultFrom=&isMap=&corporateLinkageId=&corporateLinkageField=&corporateFamilyCondition=&corporateFamilyRecId=&corporateFamilyUltimateId=&corporateFamilyImmediateId=&marketingSelect=&reverseJob=&printCredits=0&downloadCredits=0&emailCredits=0&recordsPerCred=&isPatronUser=&isBulkDownloadAvailable=&userSearchCount=38979&removeStateCriteria=&findPersonOnResidents=&paginationuppertextbox=1&undefined=$id&paginationuppertextbox=1&sort_by=1&then1_by=1&then2_by=1&email_format=pdf&email_level_detail=results_export&page_type=print&format_print=1&level_detail_Print=1&download_format=1&level_detail=1&paginationuppertextbox=&checkbox=$id" );
		$this->_curl->post( "https://www.atozdatabases.com/ajax/details" );

		return json_decode( $this->_curl->response )->detailsJsonArray[1][0];
	}

	/**
	 * @param string[] $keywords
	 * @param string $keyword
	 * @param string $email
	 *
	 * @return int
	 */
	public function enqueueReport( array $keywords, $keyword, $email )
	{
		$params = new Params();
		{
			$params->keywords = $keywords;
			$params->keyword = $keyword;
		}

		$report = new Reports();
		$report->filename = sha1( var_export( $keywords, true ) . $keyword . time() );
		$report->email = $email;
		$report->setParams( $params );
		$report->save();

		return $report->filename;
	}

	/**
	 * @param string[] $keywords
	 * @param Reports $report
	 *
	 * @return array
	 */
	public function extractEmails( $keywords, Reports $report )
	{
		$result = [];

		if ( !function_exists( 'pcntl_fork' ) || count( $keywords ) == 1 )
		{
			BaseController::log( "Extracting emails inside single process." );
			$client = new Client();
			$client->checkLogin();

			foreach ( $keywords as $k => $keyword )
			{
				$details = $client->getDetails( $keyword );
				$result[$keyword] = $this->_extractEmails( $details );
				BaseController::log( "Extracted emails for $keyword. (" . ( $k + 1 ) . " / " . count( $keywords ) . ")", true, true );
			}

			return $result;
		}
		else
		{
			BaseController::log( "Extracting emails using " . self::PROCESSES_NUM . " processes." );
			$pid = null;
			$step = 0;
			while ( self::PROCESSES_NUM * $step < count( $keywords ) )
			{
				for ( $currentProcessNum = 0 + self::PROCESSES_NUM * $step; $currentProcessNum < self::PROCESSES_NUM * ( $step + 1 ); $currentProcessNum++ )
				{
					$pid = pcntl_fork();
					if ( $pid == 0 )
						break;
				}

				if ( $pid )
				{
					for ( $i = 0; $i < self::PROCESSES_NUM; $i++ )
						pcntl_wait( $status );

					$step++;
					continue;
				}

				if ( !isset( $keywords[$currentProcessNum] ) )
					exit( 0 );
				$client = new Client();
				$client->checkLogin();
				$details = $client->getDetails( $keywords[$currentProcessNum] );
				file_put_contents( $report->getDetailsTempFilename( $keywords[$currentProcessNum] ), serialize( $details ) );
				BaseController::log( "Got details for $keywords[$currentProcessNum]. (" . ( $currentProcessNum + 1 ) . " / " . count( $keywords ) . ")", true, true );

				exit( 0 );
			}

			foreach ( $keywords as $keyword )
			{
				if ( !file_exists( $report->getDetailsTempFilename( $keyword ) ) || !( $data = file_get_contents( $report->getDetailsTempFilename( $keyword ) ) ) )
					continue;

				$result[$keyword] = $this->_extractEmails( unserialize( $data ) );
				unlink( $report->getDetailsTempFilename( $keyword ) );
				BaseController::log( "Extracted emails for $keyword.", true, true );
			}

			rmdir( $report->getCreateDetailsDir( false ) );
		}

		return $result;
	}

	/**
	 * @param \StdClass $data
	 *
	 * @return array
	 */
	private function _extractEmails( $data )
	{
		$result = [];
		if ( isset( $data->{'Executive Directory'}[0] ) && isset( $data->{'Executive Directory'}[0][1] ) && count( $data->{'Executive Directory'}[0][1] ) )
		{
			foreach ( $data->{'Executive Directory'}[0][1] as $k => $row )
			{
				if ( strpos( $row[1], '<br/>' ) !== false )
				{
					$email = explode( '<br/>', $row[1] );
					$email = trim( array_pop( $email ) );
					$result[$k] = $email;
				}
			}
		}

		return $result;
	}
}