<?php

namespace app\components;

use yii\web\CookieCollection;

class MyClient extends \yii\httpclient\Client
{
	/** @var bool */
	private $_followLocation = false;

	/** @var bool */
	private $_preserveCookies = false;

	/** @var \yii\web\CookieCollection */
	private $_cookies;

	/**
	 * MyClient constructor.
	 *
	 * @param array $config
	 */
	public function __construct( array $config = [] )
	{
		$this->_cookies = new CookieCollection();
		parent::__construct( $config );
	}

	/**
	 * @param $flag
	 *
	 * @return MyClient $this
	 */
	public function followLocation( $flag )
	{
		$this->_followLocation = $flag;

		return $this;
	}

	/**
	 * @param $flag
	 *
	 * @return MyClient $this
	 */
	public function preserveCookies( $flag )
	{
		$this->_preserveCookies = $flag;

		return $this;
	}

	/**
	 * @param \yii\httpclient\Request $request
	 * @param \yii\httpclient\Response $response
	 */
	public function afterSend( $request, $response )
	{
		if ( $this->_preserveCookies )
		{
			$this->_cookies->fromArray( array_merge( $this->_cookies->toArray(), $response->getCookies()->toArray() ) );
		}

		if ( $this->_followLocation )
		{
			if ( ( $response->headers['http-code'] == 301 || $response->headers['http-code'] == 302 ) && isset( $response->headers['location'] ) )
			{
				$location = $response->headers['location'];
				if ( strpos( $response->headers['location'], '://' ) === false )
				{
					$location = rtrim( $request->getUrl(), '/' ) . $response->headers['location'];
				}
				$this->get( $location )->send();
			}
		}

		parent::afterSend( $request, $response );
	}

	/**
	 * @param \yii\httpclient\Request $request
	 */
	public function beforeSend( $request )
	{
		$request->setCookies( $this->_cookies );
		if ( !isset( $request->headers['content-type'] ) )
		{
			$request->headers['content-type'] = 'application/x-www-form-urlencoded';
		}

		parent::beforeSend( $request );
	}

	/**
	 * @return CookieCollection
	 */
	public function getCurrentCookies()
	{
		return $this->_cookies;
	}
}