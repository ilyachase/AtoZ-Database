<?php

namespace app\controllers;

use app\models\activerecord\Reports;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class ApiController extends Controller
{
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

	public function beforeAction( $action )
	{
		\Yii::$app->response->format = Response::FORMAT_JSON;

		return parent::beforeAction( $action );
	}

	/**
	 * Displays homepage.
	 *
	 * @return string
	 */
	public function actionIndex()
	{
		return '';
	}

	/**
	 * @param $id
	 *
	 * @throws HttpException
	 */
	public function actionReport( $id )
	{
		$report = Reports::findOne( $id );
		if ( !$report )
			throw new HttpException( 404, "Report with such id not found." );
		header( 'Content-Type: application/json; charset=UTF-8' );

		$handle = fopen( $report->getJsonFile(), 'r' );
		while ( !feof( $handle ) )
		{
			echo fread( $handle, 1024 * 1024 );
			ob_flush();
			flush();
		}

	}
}