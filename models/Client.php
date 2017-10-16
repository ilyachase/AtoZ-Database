<?php

namespace app\models;

use \linslin\yii2\curl\Curl;

class Client
{
	/** @var Curl */
	private $_curl;

	public function __construct()
	{
		$cookiePath = \Yii::getAlias( "@runtime" ) . DS . 'cookie.txt';
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
				CURLOPT_COOKIEJAR      => $cookiePath,
				CURLOPT_COOKIEFILE     => $cookiePath,
			] );
	}

	public function checkLogin()
	{
		$this->_curl->get( 'https://www.atozdatabases.com/search' );
		if ( strpos( $this->_curl->response, 'WHAT YOUR PATRONS WANT' ) === false )
		{
			return;
		}

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
	 */
	public function getSearchResult( array $keywords, $page )
	{
		$keywordsQuery = '';
		foreach ( $keywords as $keyword )
		{
			$keywordsQuery .= '&Add_SIC_Description=' . urlencode( $keyword );
		}

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
}