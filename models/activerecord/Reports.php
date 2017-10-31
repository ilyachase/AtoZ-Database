<?php

namespace app\models\activerecord;

use app\models\report\Params;

/**
 * This is the model class for table "reports".
 *
 * @property string $filename
 * @property string $email
 * @property integer $status
 * @property integer $count
 */
class Reports extends \yii\db\ActiveRecord
{
	const STATUS_PROCESSING = 0;
	const STATUS_FINISHED = 1;

	private $_statusTexts = [
		self::STATUS_PROCESSING => '<span class="text-warning">Processing</span>',
		self::STATUS_FINISHED   => '<span class="text-success">Finished</span>',
	];

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'reports';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[ [ 'filename' ], 'required' ],
			[ [ 'params' ], 'string' ],
			[ [ 'status', 'count' ], 'integer' ],
			[ [ 'filename', 'email' ], 'string', 'max' => 255 ],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'filename' => 'Filename',
			'email'    => 'Email',
			'params'   => 'Params',
			'status'   => 'Status',
			'count'    => 'Count',
		];
	}

	/**
	 * @param Params $params
	 */
	public function setParams( Params $params )
	{
		$this->params = serialize( $params );
	}

	/**
	 * @return Params
	 */
	public function getParams()
	{
		return unserialize( $this->params );
	}

	/**
	 * @return string
	 */
	public function getStatusHtml()
	{
		return $this->_statusTexts[$this->status];
	}
}
