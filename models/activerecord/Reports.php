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
 * @property integer $count_all
 * @property integer $count_pages_done
 * @property string $created
 * @property integer $repeat_in_days
 * @property integer $in_work
 */
class Reports extends \yii\db\ActiveRecord
{
	const STATUS_JUST_CREATED = 0;
	const STATUS_PROCESSING = 1;
	const STATUS_FINISHED = 2;

	private $_statusTexts = [
		self::STATUS_JUST_CREATED                   => '<span class="text-primary">Just created (waiting to get in work)</span>',
		self::STATUS_PROCESSING                     => '<span class="text-warning">Processing</span>',
		self::STATUS_FINISHED                       => '<span class="text-success">Finished (sended)</span>',
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
			[ [ 'status', 'count_all', 'count_pages_done', 'repeat_in_days' ], 'integer' ],
			[ [ 'in_work' ], 'boolean' ],
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

		$filename = $this->_getFilepathForPath( $lastI, $i );
		file_put_contents( $filename, $csvReport );

		if ( count( $emails ) )
		{
			$emailsFilename = $this->getEmailsFilename();
			if ( file_exists( $emailsFilename ) )
			{
				$emails = array_merge( unserialize( file_get_contents( $emailsFilename ) ), $emails );
			}
			file_put_contents( $emailsFilename, serialize( $emails ) );
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
	public function getCreateReportDir()
	{
		$dirname = \Yii::getAlias( '@runtime' ) . DS . 'reports' . DS . $this->filename;

		if ( !file_exists( $dirname ) )
			mkdir( $dirname );

		return $dirname;
	}

	/**
	 * @param bool $create
	 *
	 * @return string
	 */
	public function getCreateReportPartsDir( $create = true )
	{
		$dirname = $this->getCreateReportDir() . DS . 'parts';

		if ( $create && !file_exists( $dirname ) )
			mkdir( $dirname );

		return $dirname;
	}

	/**
	 * @return string
	 */
	public function getCsvPath()
	{
		return $this->getCreateReportDir() . DS . 'report.csv';
	}

	/**
	 * @return string
	 */
	public function getJsonFile()
	{
		return $this->getCreateReportDir() . DS . 'report.json';
	}

	/**
	 * @return string
	 */
	public function getEmailsFilename()
	{
		return $this->getCreateReportPartsDir() . DS . 'emails.txt';
	}

	/**
	 * @param bool $create
	 *
	 * @return string
	 */
	public function getCreateDetailsDir( $create = true )
	{
		$dirname = $this->getCreateReportDir() . DS . 'details';

		if ( $create && !file_exists( $dirname ) )
			@mkdir( $dirname );

		return $dirname;
	}

	/**
	 * @param string $keyword
	 *
	 * @return string
	 */
	public function getDetailsTempFilename( $keyword )
	{
		return $this->getCreateDetailsDir() . DS . $keyword . '.txt';
	}

	/**
	 * @param int $lastI
	 * @param int $i
	 *
	 * @return bool
	 */
	public function isHavePart( $lastI, $i )
	{
		return file_exists( $this->_getFilepathForPath( $lastI, $i ) );
	}

	/**
	 * @param int $lastI
	 * @param int $i
	 *
	 * @return string
	 */
	private function _getFilepathForPath( $lastI, $i )
	{
		return $this->getCreateReportPartsDir() . DS . $lastI . '_' . $i . '.csv';
	}

	/**
	 * @return int
	 */
	public function getCountDone()
	{
		if ( $this->count_pages_done === null )
			return 0;

		$countDone = ( $this->count_pages_done - 1 ) * \app\commands\ReportController::RESPONSE_PER_PAGE_ITEMS_COUNT;
		if ( $this->count_all && $countDone > $this->count_all )
		{
			return $this->count_all;
		}

		return $countDone;
	}
}
