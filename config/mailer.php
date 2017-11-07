
<?php
return [
	'class' => 'yii\swiftmailer\Mailer',
	'transport' => [
		'class' => 'Swift_SmtpTransport',
		'host' => 'mail.clcdatahub.com',
		'username' => 'admin@clcdatahub.com',
		'password' => 'iloveclc1@3',
		'port' => '465',
		'encryption' => 'ssl',
	],
];