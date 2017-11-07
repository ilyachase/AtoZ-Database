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
			'created'  => $this->dateTime(),
		] );
		$this->addPrimaryKey( 'filename', 'reports', 'filename' );
		$this->createIndex( 'status', 'reports', 'status' );
		$this->createIndex( 'created', 'reports', 'created' );
	}

	public function safeDown()
	{
		$this->dropTable( 'reports' );
	}
}
