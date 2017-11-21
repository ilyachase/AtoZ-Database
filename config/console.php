<?php

$params = require( __DIR__ . '/params.php' );
$db = require( __DIR__ . '/db.php' );
$mailer = require( __DIR__ . '/mailer.php' );

$config = [
	'id'                  => 'basic-console',
	'basePath'            => dirname( __DIR__ ),
	'bootstrap'           => [ 'log' ],
	'controllerNamespace' => 'app\commands',
	'components'          => [
		'cache'  => [
			'class' => 'yii\caching\FileCache',
		],
		'log'    => [
			'targets' => [
				[
					'class'   => 'yii\log\EmailTarget',
					'levels'  => [ 'error', 'warning' ],
					'message' => [
						'from'    => [ 'admin@clcdatahub.com' ],
						'to'      => [ 'ilya.chase@yandex.ru' ],
						'subject' => 'Atoz service log',
					],
					'enabled' => !YII_DEBUG,
				],
				[
					'class'          => 'yii\log\FileTarget',
					'logFile'        => '@runtime/logs/trace.log',
					'levels'         => [ 'trace' ],
					'logVars'        => [],
					'exportInterval' => 3,
				],
			],
		],
		'db'     => $db,
		'mailer' => $mailer,
	],
	'params'              => $params,
	/*
	'controllerMap' => [
		'fixture' => [ // Fixture generation command line.
			'class' => 'yii\faker\FixtureController',
		],
	],
	*/
];

if ( YII_ENV_DEV )
{
	// configuration adjustments for 'dev' environment
	$config['bootstrap'][] = 'gii';
	$config['modules']['gii'] = [
		'class' => 'yii\gii\Module',
	];
}

return $config;
