<?php

namespace app\commands;

use app\components\TrivialHelper;
use yii\console\Controller;
use yii\helpers\Console;

class BaseController extends Controller
{
	/** @var bool Verbose debug */
	public $debug = false;

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
	protected function log( $string, $eol = true, $withoutTrace = false )
	{
		if ( !$withoutTrace )
			\Yii::trace( $string, __METHOD__ );

		Console::stdout( "$string" . ( $eol ? "\n" : "" ) );
	}
}