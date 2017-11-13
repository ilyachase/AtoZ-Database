<?php

use yii\db\Migration;

class m171113_133548_cookie_dir extends Migration
{
	public function safeUp()
	{
		$dir = \Yii::getAlias( "@runtime" ) . DS . 'cookie';
		if ( !file_exists( $dir ) )
		{
			$check = mkdir( $dir );
			if ( $check )
				echo "$dir created.\n";
		}

		$dir = \Yii::getAlias( "@runtime" ) . DS . 'reports';
		if ( !file_exists( $dir ) )
		{
			$check = mkdir( $dir );
			if ( $check )
				echo "$dir created.\n";
		}
	}

	public function safeDown()
	{
	}
}
