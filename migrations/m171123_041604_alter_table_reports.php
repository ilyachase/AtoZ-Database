<?php

use yii\db\Migration;

class m171123_041604_alter_table_reports extends Migration
{
	public function safeUp()
	{
		$this->addColumn( 'reports', 'last_finished', $this->dateTime() );
		$this->addColumn( 'reports', 'repeat_in_days', $this->smallInteger()->unsigned() );
		$this->createIndex( 'last_finished_repeat_in_days', 'reports', [ 'last_finished', 'repeat_in_days' ] );
	}

	public function safeDown()
	{
		$this->dropColumn( 'reports', 'last_finished' );
		$this->dropColumn( 'reports', 'repeat_in_days' );
	}
}
