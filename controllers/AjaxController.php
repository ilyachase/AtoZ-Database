<?php

namespace app\controllers;

use app\models\activerecord\Reports;
use app\models\Client;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\HttpException;

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
					'set-interval'        => [ 'get' ],
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
			$this->_client = new Client();
			$this->_client->checkLogin();

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
			$this->_client = new Client();
			$this->_client->checkLogin();

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
		$this->_client = new Client();

		return $this->_client->enqueueReport( $keywords, $keyword, $email );
	}

	/**
	 * @param string $id
	 *
	 * @return array
	 * @throws HttpException
	 */
	public function actionReportInfo( $id )
	{
		$model = Reports::findOne( $id );
		if ( !$model )
			throw new HttpException( 404, "Report with such id not found." );

		$statusHtml = $model->getStatusHtml();
		$report = $model->toArray();
		$report['count_done'] = $model->getCountDone();
		$report['count_all'] = $model->count_all !== null ? $model->count_all : 'Unknown';
		$report['status_html'] = $statusHtml;

		return $report;
	}

	/**
	 * @param string $id
	 * @param int $value
	 *
	 * @return bool
	 * @throws HttpException
	 */
	public function actionSetInterval( $id, $value )
	{
		$report = Reports::findOne( $id );
		if ( !$report )
			throw new HttpException( 404, "Report with such id not found." );

		$value = (int) $value;

		if ( $value < 0 )
			throw new HttpException( 400, "Incorrect value." );

		$report->repeat_in_days = $value;

		return $report->save();
	}
}
