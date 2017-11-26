<?php

use yii\db\Migration;

class m171126_085408_alter_table_reports extends Migration
{
	public function safeUp()
	{
		$this->addColumn( 'reports', 'in_work', $this->boolean()->notNull()->defaultValue( false ) );
		$this->createIndex( 'in_work', 'reports', 'in_work' );
	}

	public function safeDown()
	{
		$this->dropColumn( 'reports', 'in_work' );
	}
}
