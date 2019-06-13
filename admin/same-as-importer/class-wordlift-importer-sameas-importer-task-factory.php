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

				$configuration_service = new Wordlift_Configuration_Service();
				$entity_service = new Wordlift_Entity_Uri_Service($configuration_service);

				if($item){

					foreach ($item as $key => $value){
						$record[$header[$key][1]] = $value;
					}

					$entity = $entity_service->get_entity($record['same_as']);

					if(is_null($entity)){
						//var_dump( $record );

						// Create an entity with the specified title, set type, same_as meta
						$post_id = wp_insert_post( array(
							'post_type'   => Wordlift_Entity_Service::TYPE_NAME,
							'post_title'  => $record['title'],
							'post_status' => 'publish',
						) );
						Wordlift_Entity_Type_Service::get_instance()->set( $post_id, $record['type'] );
						add_post_meta( $post_id, Wordlift_Schema_Service::FIELD_SAME_AS, $record['same_as'] );
					} else {
						// Confirm do nothing?
					}
				}

			},
		) );
	}

}
