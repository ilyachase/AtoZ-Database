<?php

namespace app\models\activerecord;

use app\models\report\Params;
use yii\base\Exception;

/**
 * This is the model class for table "reports".
 *
 * @property string $filename
 * @property string $email
 * @property integer $status
 * @property integer $count
 * @property string $created
 * @property integer $repeat_in_days
 * @property integer $in_work
 */
class Reports extends \yii\db\ActiveRecord
{
	const STATUS_JUST_CREATED = 0;
	const STATUS_PROCESSING_GOT_PARTS = 1;
	const STATUS_PROCESSING_GENERATED_FINAL_CSV = 2;
	const STATUS_FINISHED = 3;

	private $_statusTexts = [
		self::STATUS_JUST_CREATED                   => '<span class="text-primary">Just created (waiting to get in work)</span>',
		self::STATUS_PROCESSING_GOT_PARTS           => '<span class="text-warning">Processing</span>',
		self::STATUS_PROCESSING_GENERATED_FINAL_CSV => '<span class="text-warning">Processing</span>',
		self::STATUS_FINISHED                       => '<span class="text-success">Finished</span>',
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
			[ [ 'filename', 'email' ], 'required' ],
			[ [ 'params' ], 'string' ],
			[ [ 'status', 'count', 'repeat_in_days', 'in_work' ], 'integer' ],
			[ [ 'created', ], 'safe' ],
			[ [ 'filename', 'email' ], 'string', 'max' => 255 ],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'filename'       => 'Filename',
			'email'          => 'Email',
			'params'         => 'Params',
			'status'         => 'Status',
			'count'          => 'Count',
			'created'        => 'Created',
			'repeat_in_days' => 'Repeat In Days',
			'in_work'        => 'In Work',
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
	 * @param array $emails
	 *
	 * @return string
	 * @throws Exception
	 */
	public function saveCsvReportPart( $csvReport, $lastI, $i, array $emails )
	{
		if ( !$csvReport )
			throw new Exception( "csvReport is empty" );

		if ( !file_exists( $this->getReportDir() ) )
			mkdir( $this->getReportDir() );

		if ( !file_exists( $this->getReportPartsDir() ) )
			mkdir( $this->getReportPartsDir() );

		$filename = $this->getReportPartsDir() . DS . $lastI . '_' . $i . '.csv';
		file_put_contents( $filename, $csvReport );

		if ( count( $emails ) )
		{
			$filename = $this->getEmailsFilename();
			if ( file_exists( $filename ) )
			{
				$emails = array_merge( unserialize( file_get_contents( $filename ) ), $emails );
			}
			file_put_contents( $filename, serialize( $emails ) );
		}

		return $filename;
	}

	/**
	 * @param array $rowToInsert
	 */
	public function addJsonEntity( array $rowToInsert )
	{
		$justCreated = false;
		if ( !file_exists( $this->getJsonFile() ) )
		{
			$justCreated = true;
			file_put_contents( $this->getJsonFile(), '[]' );
		}

		$h = fopen( $this->getJsonFile(), 'c' );
		fseek( $h, -1, SEEK_END );
		fwrite( $h, ( $justCreated ? '' : ',' ) . json_encode( $rowToInsert ) . ']' );
		fclose( $h );
	}

	/**
	 * @return string
	 */
	public function getReportDir()
	{
		return \Yii::getAlias( '@runtime' ) . DS . 'reports' . DS . $this->filename;
	}

	/**
	 * @return string
	 */
	public function getReportPartsDir()
	{
		return $this->getReportDir() . DS . 'parts';
	}

	/**
	 * @return string
	 */
	public function getCsvPath()
	{
		return $this->getReportDir() . DS . 'report.csv';
	}

	/**
	 * @return string
	 */
	public function getJsonFile()
	{
		return $this->getReportDir() . DS . 'report.json';
	}

	/**
	 * @return string
	 */
	public function getEmailsFilename()
	{
		return $this->getReportPartsDir() . DS . 'emails.txt';
	}
}
