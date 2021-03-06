<?php

namespace app\controllers;

use app\models\activerecord\Reports;
use app\models\Client;
use app\models\LoginForm;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class SiteController extends Controller
{
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
	 * Displays homepage.
	 *
	 * @return string
	 */
	public function actionIndex()
	{
		return $this->render( 'index' );
	}

	/**
	 * @param array $keywords
	 * @param string $keyword
	 * @param int $page
	 *
	 * @return string
	 */
	public function actionSearch( array $keywords, $keyword, $page = 1 )
	{
		$cacheKey = __METHOD__ . "/$keyword/" . sha1( var_export( $keywords, true ) ) . '/' . $page;

		$data = \Yii::$app->cache->get( $cacheKey );
		if ( $data === false )
		{
			$client = new Client();
			$client->checkLogin();
			$client->getKeywordsAutocomplete( $keyword );

			$data = $client->getSearchResult( $keywords, $page );
			\Yii::$app->cache->set( $cacheKey, $data, CACHE_DEFAULT_DURATION );
		}

		return $this->render( 'search', [
			'totalrecords' => $data->totalrecords,
			'rows'         => $data->jsonArray,
			'currentPage'  => $page,
			'totalPages'   => $data->totalpages,
		] );
	}

	/**
	 * Login action.
	 *
	 * @return Response|string
	 */
	public function actionLogin()
	{
		if ( !\Yii::$app->user->isGuest )
		{
			return $this->goHome();
		}

		$model = new LoginForm();
		if ( $model->load( \Yii::$app->request->post() ) && $model->login() )
		{
			return $this->goBack();
		}
		return $this->render( 'login', [
			'model' => $model,
		] );
	}

	/**
	 * @param string $id
	 *
	 * @return string
	 */
	public function actionDetails( $id )
	{
		$cacheKey = __METHOD__ . '/' . sha1( var_export( $id, true ) );

		$data = \Yii::$app->cache->get( $cacheKey );
		if ( $data === false )
		{
			$client = new Client();
			$client->checkLogin();

			$data = $client->getDetails( $id );
			\Yii::$app->cache->set( $cacheKey, $data, CACHE_DEFAULT_DURATION );
		}

		return $this->render( 'details', [
			'data' => $data,
		] );
	}

	/**
	 * @param int $id
	 * @param string $action
	 *
	 * @return string
	 * @throws HttpException
	 */
	public function actionReport( $id, $action = '' )
	{
		$report = Reports::findOne( $id );
		if ( !$report )
			throw new HttpException( 404, "Report with such id not found." );

		if ( $action == 'download' && $report->status == Reports::STATUS_FINISHED )
		{
			return \Yii::$app->response->sendFile( $report->getCsvPath(), $report->filename . '.csv' );
		}

		return $this->render( 'report', [
			'model' => $report,
		] );
	}
}
