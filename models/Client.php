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

		return json_decode( $this->_curl->response)->jsonarray;
	}
}