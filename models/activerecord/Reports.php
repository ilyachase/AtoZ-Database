<?php

namespace app\models\activerecord;

use app\models\report\Params;
use yii\console\Exception;

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
	const STATUS_JUST_CREATED = 0;
	const STATUS_PROCESSING = 1;
	const STATUS_FINISHED = 2;

	private $_statusTexts = [
		self::STATUS_JUST_CREATED => '<span class="text-primary">Just created (waiting to get in work)</span>',
		self::STATUS_PROCESSING   => '<span class="text-warning">Processing</span>',
		self::STATUS_FINISHED     => '<span class="text-success">Finished</span>',
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

	/**
	 * @param string $csvReport
	 * @param string $lastI
	 * @param string $i
	 *
	 * @throws Exception
	 */
	public function saveCsvReportPart( $csvReport, $lastI, $i )
	{
		if ( !$csvReport )
			throw new Exception( "csvReport string is empty" );

		if ( !file_exists( $this->_getReportDir() ) )
			mkdir( $this->_getReportDir() );

		file_put_contents( $this->_getReportPartsDir() . DS . $lastI . '_' . $i . '.csv', $csvReport );
	}

	/**
	 * @return string
	 */
	private function _getReportDir()
	{
		return \Yii::getAlias( '@runtime' ) . DS . 'reports' . DS . $this->filename;
	}

	/**
	 * @return string
	 */
	private function _getReportPartsDir()
	{
		return $this->_getReportDir() . DS . 'parts';
	}
}
