<?php

namespace app\components;

use Yii;

class TrivialHelper
{
	/**
	 * @param string $text
	 */
	public static function AddWarning( $text )
	{
		self::AddMessage( $text, 'warning' );
	}

	/**
	 * @param string $text
	 * @param string $key
	 */
	public static function AddMessage( $text, $key = 'message' )
	{
		if ( ( !isset( Yii::$app->params[$key] ) ) || ( !Yii::$app->params[$key] ) )
		{
			Yii::$app->params[$key] = $text;
		}
		else
		{
			Yii::$app->params[$key] .= '<br/>' . $text;
		}
	}

	/**
	 * @param string $text
	 */
	public static function AddError( $text )
	{
		self::AddMessage( $text, 'error' );
	}

	/**
	 * @return bool
	 */
	public static function IsWindows()
	{
		return substr( php_uname(), 0, 7 ) == "Windows";
	}
}