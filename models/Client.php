<?php

namespace app\models;

use app\models\activerecord\Reports;
use app\models\report\Params;
use \linslin\yii2\curl\Curl;
use yii\web\HttpException;

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

		return $this->_curl->response;
	}

	//TODO: use keywords and keyword steps
	public function getDetails( $id )
	{
		$this->_curl->setRequestBody( "accountId=2011000265&isInternal=false&refURL=https%3A%2F%2Fwww.carnegielibrary.org%2Fresearch%2Fpage%2F3%2F&libraryCardId=11812015896843" )
			->get( "https://www.atozdatabases.com/usbusiness/search" );
		$this->_curl->setRequestBody( "accountId=2011000265&database=business&sourceAlias=portlet.business&sourceAliasId=&marketingSelect=&page=search&postAuthType=hpdeux&postAuthAction=&search_count=&searchType=findbusiness&stateCity=&findajob=&findBusinessSearchType=findaPerson&findPersonOnResidents=NO&vi_leftchk_Physical_State_Physical_City=" )
			->get( "https://www.atozdatabases.com/usbusiness/search" );
		$this->_curl->setRequestBody( "database=business&field=Advanced_SIC_Keyword&page=search" )
			->get( "https://www.atozdatabases.com/ajax/rpc/getMetadataCall.htm" );
		$this->_curl->setRequestBody( "field=SIC_Description&criteria=auto&database=business&page=search" )// TODO: criteria
		->get( "https://www.atozdatabases.com/ajax/rpc/getReferenceCallData.htm?" );
		$this->_curl->setRequestBody( "count=---&database=business&search_count=&page=search&searchType=general&searchmode=&mode=&marketingSelect=&cancelSearch=&searchCheckedDetails=&nameTreeView=&selectTreeView=&parentTreeView=&treeMetaField=&nameTreeViewExpenditure=&selectTreeViewExpenditure=&parentTreeViewExpenditure=&treeMetaFieldExpenditure=&Map_proximity=N%2FA&Map_Physical_State=N%2FA&Map_Vendor_State_County=N%2FA&Meta~SIC_Description=on&Meta~Record_Type=on&Ref_Physical_State_Physical_City=Select+a+State&Ref_CBSA_Code=Select+a+State&Ref_Vendor_State_County=Select+a+State&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip_Paste=&Add_Physical_Address=&Add_proximity=&Add_proximity=&Add_proximity=&Ref_SIC_Description=auto&Ref_Advanced_SIC_Keyword=SIC_Description&hid_SIC_Description=auto&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry_Paste=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS_Paste=&Add_Company_Name=&Add_Prefix=-1&Add_First_Name=&Add_Middle_Initial=&Add_Last_Name=&Add_Suffix=-1&Add_Employees_Advanced_From=&Add_Employees_Advanced_To=&Add_SalesAnnualRevenue_Advanced_From=&Add_SalesAnnualRevenue_Advanced_To=&Add_Phone=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code_Paste=&Add_EIN=&Add_URL=&Add_Record_Type=1&countForDownload=&Add_SIC_Description=5531001&Add_SIC_Description=5087093&Add_SIC_Description=5013061&Ref_keywordSICMap=5531001-5531001+-+Auto+%26+Home+Supply+Stores&Ref_keywordSICMap=5087093-5087093+-+Auto+Inspection+Equipment+%26+Supplies+Wholesale&Ref_keywordSICMap=5013061-5013061+-+Auto+Machine+Shop+Equipment+%26+Supplies+Wholesale" )// TODO: keywords
		->get( "https://www.atozdatabases.com/ajax/search-business-updatecount.htm?persist=yes" );
		$this->_curl->setRequestBody( "count=---&database=business&search_count=38%2C979&page=search&searchType=general&searchmode=&mode=&marketingSelect=&cancelSearch=&searchCheckedDetails=leftchk_SIC_Description%2Cleftchk_Record_Type&nameTreeView=&selectTreeView=&parentTreeView=&treeMetaField=&nameTreeViewExpenditure=&selectTreeViewExpenditure=&parentTreeViewExpenditure=&treeMetaFieldExpenditure=&Map_proximity=N%2FA&Map_Physical_State=N%2FA&Map_Vendor_State_County=N%2FA&Meta%7ESIC_Description=on&Meta%7ERecord_Type=on&Ref_Physical_State_Physical_City=Select+a+State&Ref_CBSA_Code=Select+a+State&Ref_Vendor_State_County=Select+a+State&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip=&Add_Physical_Zip_Paste=&Add_Physical_Address=&Add_proximity=&Add_proximity=&Add_proximity=&Ref_SIC_Description=auto&Ref_Advanced_SIC_Keyword=SIC_Description&hid_SIC_Description=auto&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry=&Add_Industry_Paste=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS=&Add_NAICS_Paste=&Add_Company_Name=&Add_Prefix=-1&Add_First_Name=&Add_Middle_Initial=&Add_Last_Name=&Add_Suffix=-1&Add_Employees_Advanced_From=&Add_Employees_Advanced_To=&Add_SalesAnnualRevenue_Advanced_From=&Add_SalesAnnualRevenue_Advanced_To=&Add_Phone=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code=&Add_Area_Code_Paste=&Add_EIN=&Add_URL=&Add_Record_Type=1&countForDownload=" )// TODO: auto
		->get( "https://www.atozdatabases.com/usbusiness/result" );
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
}