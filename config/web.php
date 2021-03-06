<?php

$params = require( __DIR__ . '/params.php' );
$db = require( __DIR__ . '/db.php' );

$config = [
	'id'         => 'basic',
	'basePath'   => dirname( __DIR__ ),
	'bootstrap'  => [ 'log' ],
	'components' => [
		'request'      => [
			// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
			'cookieValidationKey' => 'a9eOuFxHz9jTrvPQT2lmH9PeggwwrimF',
		],
		'cache'        => [
			'class' => 'yii\caching\FileCache',
		],
		'user'         => [
			'identityClass'   => 'app\models\User',
			'enableAutoLogin' => true,
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'mailer'       => [
			'class'            => 'yii\swiftmailer\Mailer',
			// send all mails to a file by default. You have to set
			// 'useFileTransport' to false and configure a transport
			// for the mailer to send real emails.
			'useFileTransport' => true,
		],
		'log'          => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets'    => [
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
			],
		],
		'db'           => $db,
		'urlManager'   => [
			'enablePrettyUrl'     => true,
			'showScriptName'      => false,
			'enableStrictParsing' => false,
		],
		'fileCache' => [
			'class' => 'yii\caching\FileCache',
		],
	],
	'params'     => $params,
];

if ( YII_ENV_DEV )
{
	// configuration adjustments for 'dev' environment
	$config['bootstrap'][] = 'debug';
	$config['modules']['debug'] = [
		'class' => 'yii\debug\Module',
		// uncomment the following to add your IP if you are not connecting from localhost.
		//'allowedIPs' => ['127.0.0.1', '::1'],
	];

	$config['bootstrap'][] = 'gii';
	$config['modules']['gii'] = [
		'class' => 'yii\gii\Module',
		// uncomment the following to add your IP if you are not connecting from localhost.
		//'allowedIPs' => ['127.0.0.1', '::1'],
	];
}

return $config;
