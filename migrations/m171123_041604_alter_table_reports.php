<?php

use yii\db\Migration;

class m171123_041604_alter_table_reports extends Migration
{
	public function safeUp()
	{
		$this->addColumn( 'reports', 'repeat_in_days', $this->smallInteger()->unsigned() );
	}

	public function safeDown()
	{
		$this->dropColumn( 'reports', 'repeat_in_days' );
	}
}
