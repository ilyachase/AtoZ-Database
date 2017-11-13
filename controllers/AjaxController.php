<?php

namespace app\controllers;

use app\models\Client;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class AjaxController extends Controller
{
	/** @var Client */
	private $_client;

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'actions' => [ 'login', 'logout' ],
						'allow'   => true,
						'roles'   => [ '?' ],
					],
					[
						'allow' => true,
						'roles' => [ '@' ],
					],
				],
			],
			'verbs'  => [
				'class'   => VerbFilter::className(),
				'actions' => [
					'keywordautocomplete' => [ 'get' ],
					'enqueuereport'       => [ 'get' ],
					'getcount'            => [ 'post' ],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	/**
	 * @param \yii\base\Action $action
	 *
	 * @return bool
	 */
	public function beforeAction( $action )
	{
		if ( !\Yii::$app->request->isAjax && \Yii::$app->request->userIP != '127.0.0.1' )
			return false;

		$this->_client = new Client();
		$this->_client->checkLogin();

		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

		return parent::beforeAction( $action );
	}

	/**
	 * @param string $keyword
	 *
	 * @return mixed
	 */
	public function actionKeywordautocomplete( $keyword )
	{
		$cacheKey = __METHOD__ . '/' . $keyword;

		$data = \Yii::$app->cache->get( $cacheKey );
		if ( $data === false )
		{
			$data = $this->_client->getKeywordsAutocomplete( $keyword );
			\Yii::$app->cache->set( $cacheKey, $data, CACHE_DEFAULT_DURATION );
		}

		return $data;
	}

	/**
	 * @return string
	 */
	public function actionGetcount()
	{
		$cacheKey = __METHOD__ . '/' . sha1( var_export( \Yii::$app->request->post( 'keywords' ), true ) . \Yii::$app->request->post( 'keyword' ) );

		$data = \Yii::$app->cache->get( $cacheKey );
		if ( $data === false )
		{
			$this->_client->getKeywordsAutocomplete( \Yii::$app->request->post( 'keyword' ) );
			$data = $this->_client->getCount( \Yii::$app->request->post( 'keywords' ) );
			\Yii::$app->cache->set( $cacheKey, $data, CACHE_DEFAULT_DURATION );
		}

		return $data;
	}

	/**
	 * @param array $keywords
	 * @param string[] $keyword
	 * @param string $email
	 *
	 * @return bool|int
	 */
	public function actionEnqueuereport( array $keywords, $keyword, $email )
	{
		$client = new Client();

		return $client->enqueueReport( $keywords, $keyword, $email );
	}
}
