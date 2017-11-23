<?php

namespace app\controllers;

use app\models\activerecord\Reports;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class ApiController extends Controller
{
	public function behaviors()
	{
		return [
			'verbs' => [
				'class'   => VerbFilter::className(),
				'actions' => [
					'report'      => [ 'get' ],
					'report-info' => [ 'get' ],
				],
			]
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
		if ( $report->status != Reports::STATUS_FINISHED )
			throw new HttpException( 400, "Report is not finished yet." );

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
