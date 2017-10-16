<?php

namespace app\controllers;

use app\models\Client;
use yii\web\Controller;

class SiteController extends Controller
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
	 * @param int $page
	 *
	 * @return string
	 */
	public function actionSearch( array $keywords, $page = 1 )
	{
		$client = new Client();
		$client->checkLogin();

		$data = $client->getSearchResult( $keywords, $page );

		return $this->render( 'search', [
			'totalrecords' => $data->totalrecords,
			'rows'         => $data->jsonArray,
			'currentPage'  => $page,
			'totalPages'   => $data->totalpages,
		] );
	}
}
