<?php

namespace app\controllers;

use app\models\Client;
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
		];
	}

	/**
	 * @param \yii\base\Action $action
	 *
	 * @return bool
	 */
	public function beforeAction( $action )
	{
//		if ( !\Yii::$app->request->isAjax )
//			return false;

		$this->_client = new Client();

		return parent::beforeAction( $action );
	}

	public function actionKeywordautocomplete()
	{

	}
}
