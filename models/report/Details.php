<?php

namespace app\models\report;

class Details
{
	/** @var string */
	private $_businessName = '';

	/** @var string */
	private $_website = '';

	/** @var string */
	private $_phone = '';

	/** @var string */
	private $_physicalCity = '';

	/** @var string */
	private $_physicalState = '';

	private static $_FinalCsvColumnsTitle = [
		'Business Name',
		'Website',
		'Phone',
		'Physical City',
		'Physical State',
		'Executive Title',
		'Executive Name',
		'Executive Email',
	];

	private $_executives = [];

	/**
	 * Details constructor.
	 *
	 * @param \stdClass $jsonObject
	 */
	public function __construct( $jsonObject )
	{
		if ( !$jsonObject->Overview )
			\Yii::error( "Invalid json object:\n" . var_export( $jsonObject, true ) );

		foreach ( $jsonObject->Overview as $k => $field )
		{
			switch ( $field[0] )
			{
				case 'Business Name':
					$this->_businessName = trim( htmlspecialchars_decode( $jsonObject->Overview[$k][1] ) );
					break;
				case 'Website':
					$this->_website = strip_tags( $jsonObject->Overview[$k][1] );
					break;
				case 'Phone':
					$this->_phone = trim( $jsonObject->Overview[$k][1] );
					break;
			}
		}

		foreach ( $jsonObject->{'Job Postings'} as $k => $field )
		{
			switch ( $field[0] )
			{
				case 'Job Physical City':
					$this->_physicalCity = trim( $jsonObject->{'Job Postings'}[$k][1] );
					break;
				case 'Job Physical State':
					$this->_physicalState = trim( $jsonObject->{'Job Postings'}[$k][1] );
					break;
			}
		}

		if ( !isset( $jsonObject->{'Executive Directory'}[0] ) || !isset( $jsonObject->{'Executive Directory'}[0][1] ) || !count( $jsonObject->{'Executive Directory'}[0][1] ) )
			return;

		foreach ( $jsonObject->{'Executive Directory'}[0][1] as $personData )
		{
			list( $name, $email ) = $this->_extractNameAndEmail( $personData[1] );
			$this->_executives[] = [
				$personData[2],
				$name,
				$email,
			];
		}
	}

	/**
	 * @return array
	 */
	public function getCsvRows()
	{
		$result = [];
		foreach ( $this->_executives as $person )
		{
			$result[] = [
				$this->_businessName,
				$this->_website,
				$this->_phone,
				$this->_physicalCity,
				$this->_physicalState,
				$person[0],
				$person[1],
				$person[2],
			];
		}

		return $result;
	}

	/**
	 * @param \StdClass $data
	 *
	 * @return array
	 */
	private function _extractNameAndEmail( $data )
	{
		$email = '';
		if ( strpos( $data, '<br/>' ) !== false )
		{
			$data = explode( '<br/>', $data );
			$email = trim( array_pop( $data ) );
			$name = str_replace( 'Ms ', '', str_replace( 'Mr ', '', trim( array_pop( $data ) ) ) );
		}
		elseif ( strpos( $data, '<br />' ) !== false )
		{
			$data = explode( '<br />', $data );
			$email = trim( array_pop( $data ) );
			$name = str_replace( 'Ms ', '', str_replace( 'Mr ', '', trim( array_pop( $data ) ) ) );
		}
		else
		{
			$name = str_replace( 'Ms ', '', str_replace( 'Mr ', '', trim( $data ) ) );
		}

		return [ $name, $email ];
	}

	/**
	 * @return string[]
	 */
	public static function GetCsvTitileColumns()
	{
		return self::$_FinalCsvColumnsTitle;
	}

	/**
	 * @return string
	 */
	public function getBusinessName()
	{
		return $this->_businessName;
	}
}