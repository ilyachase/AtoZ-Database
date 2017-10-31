<?php

use yii\db\Migration;

class m171031_071958_create_table_reports extends Migration
{
	public function safeUp()
	{
		$this->createTable( 'reports', [
			'filename' => $this->string( 255 )->notNull(),
			'email'    => $this->string( 255 )->notNull(),
			'params'   => $this->text(),
			'status'   => $this->smallInteger()->unsigned()->notNull()->defaultValue( 0 ),
			'count'    => $this->bigInteger()->notNull()->defaultValue( 0 ),
		] );
		$this->addPrimaryKey( 'filename', 'reports', 'filename' );
	}

	public function safeDown()
	{
		$this->dropTable( 'reports' );
	}
}
