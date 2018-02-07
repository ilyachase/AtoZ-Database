<?php

use yii\db\Migration;

class m180205_111858_alter_table_reports extends Migration
{
	public function safeUp()
	{
		$this->dropColumn( 'reports', 'count' );
		$this->addColumn( 'reports', 'count_all', $this->bigInteger() );
		$this->addColumn( 'reports', 'count_pages_done', $this->bigInteger() );
	}

	public function safeDown()
	{
		$this->addColumn( 'reports', 'count', $this->bigInteger()->notNull()->defaultValue( 0 ) );
	}
}
