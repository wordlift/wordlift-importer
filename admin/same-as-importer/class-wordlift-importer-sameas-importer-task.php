<?php
/**
 * @since 1.0.0
 * @package Wordlift_Importer
 * @subpackage Wordlift_Importer/admin/task
 */

/**
 * Define the  class.
 *
 * @since 1.0.0
 */
class Wordlift_Importer_SameAs_Importer_Task implements Wordlift_Task {

	/**
	 * The task id.
	 *
	 * @since 1.0.0
	 */
	const ID = '_wlimp_sameas_importer';

	private $filename;

	/**
	 * @var callable[]
	 */
	private $callables;
	/**
	 * @var bool|string
	 */
	private $header;

	/**
	 * Wordlift_Geo_Places_Task constructor.
	 *
	 * @param callable[] $callables
	 */
	public function __construct( $filename, $callables ) {

		$this->filename  = $filename;
		$this->callables = $callables;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {

		return self::ID;
	}

	/**
	 * {@inheritDoc}
	 * @throws Exception
	 */
	public function list_items( $limit = 0, $offset = 0 ) {

		$i       = 0;
		$records = array();
		$handle  = fopen( $this->filename, "r" );

		if ( feof( $handle ) ) {
			throw new Exception( "File $this->filename is empty." );
		}

		$this->header = $this->get_header( fgetcsv( $handle, 0, "\t" ) );

		while ( ! feof( $handle ) && ( $i < $limit || 0 === $limit ) ) {
			// Skip.
			if ( $offset > $i ++ ) {
				continue;
			}

			$records[] = fgetcsv( $handle, 0, "\t" );;
		}

		fclose( $handle );

		return $records;
	}

	/**
	 * {@inheritDoc}
	 */
	function count_items() {

		$count  = 0;
		$handle = fopen( $this->filename, "r" );

		while ( ! feof( $handle ) ) {
			$line  = fgets( $handle, 4096 );
			$count += substr_count( $line, PHP_EOL );
		}

		fclose( $handle );

		// Remove the header from the count.
		return $count - 1;
	}

	/**
	 * {@inheritDoc}
	 */
	public function process_item( $item ) {

		foreach ( $this->callables as $callable ) {
			call_user_func( $callable, $item, $this->header );
		}

	}

	private function get_header( $fields ) {

		return array_map( function ( $item ) {
			return explode( ':', $item );
		}, $fields );
	}

}
