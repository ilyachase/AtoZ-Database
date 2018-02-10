<?php

namespace app\commands;

use app\components\TrivialHelper;
use yii\console\Controller;
use yii\helpers\Console;

class BaseController extends Controller
{
	/** @var bool Verbose debug */
	public $debug = false;

	/** @var bool */
	private static $_Debug = false;

	/**
	 * @param string $actionID
	 *
	 * @return \string[]
	 */
	public function options( $actionID )
	{
		return [ 'debug' ];
	}

	/**
	 * @param \yii\base\Action $action
	 *
	 * @return bool
	 */
	public function beforeAction( $action )
	{
		self::$_Debug = $this->debug;
		return parent::beforeAction( $action );
	}

	/**
	 * @param string $className
	 *
	 * @return bool
	 */
	protected function _isProcessExists( $className )
	{
		if ( TrivialHelper::IsWindows() )
		{
			return false; // :\
		}
		else
		{
			exec( "ps aux|grep yii|grep -v 'grep'|grep '$className'|wc -l", $result );
			return (int) $result[0] > 2;
		}
	}

	/**
	 * @param $string
	 * @param bool $eol
	 * @param bool $withoutTrace
	 */
	public static function log( $string, $eol = true, $withoutTrace = false )
	{
		if ( !$withoutTrace )
			\Yii::trace( $string );

		if ( self::$_Debug )
			Console::stdout( "$string" . ( $eol ? "\n" : "" ) );
	}
}