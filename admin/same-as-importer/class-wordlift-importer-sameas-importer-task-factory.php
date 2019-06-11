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
class Wordlift_Importer_SameAs_Importer_Task_Factory {

	static function create() {

		// Bail out if this is not ajax and it's not our action.
		if ( ! wp_doing_ajax() || Wordlift_Importer_SameAs_Importer_Task::ID !== $_REQUEST['action'] ) {
			return null;
		}

		$filename = $_FILES['file']['tmp_name'];

		return new Wordlift_Importer_SameAs_Importer_Task( $filename, array(
			function ( $item, $header ) {

				var_dump( $header );
				var_dump( $item );
			},
		) );
	}

}
