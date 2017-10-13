<?php

namespace app\controllers;

use app\models\Client;
use yii\filters\VerbFilter;
use yii\web\Controller;

class AjaxController extends Controller
{
	/** @var Client */
	private $_client;

	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
			'verbs' => [
				'class'   => VerbFilter::className(),
				'actions' => [
					'keywordautocomplete' => [ 'get' ],
					'getcount'            => [ 'post' ],
				],
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
		return $this->_client->getKeywordsAutocomplete( $keyword );
	}

	public function actionGetcount()
	{
		return $this->_client->getCount( \Yii::$app->request->post( 'keywords' ) );
	}
}
