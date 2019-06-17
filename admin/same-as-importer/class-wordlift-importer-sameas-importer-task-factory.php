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

				if($item){

					foreach ($item as $key => $value){
						$record[$header[$key][0].':'.$header[$key][1]] = $value;
					}

					/*
					 * Recognized keys for import:
					 *
					 * wordpress:post_title
					 * wordlift:same_as
					 * wordlift:type
					 * wordlift:url
					 * wordlift:alt_label
					 * acf:<field_name>
					 *
					 */

					$entity = Wordlift_Entity_Service::get_instance()->get_entity_post_by_uri( $record['wordlift:same_as'] );

					if(is_null($entity)){

						// Create an entity with the specified title, same_as meta
						$post_id = wp_insert_post( array(
							'post_type'   => Wordlift_Entity_Service::TYPE_NAME,
							'post_title'  => $record['wordpress:post_title'],
							'post_status' => 'publish'
						) );

						add_post_meta( $post_id, Wordlift_Schema_Service::FIELD_SAME_AS, $record['wordlift:same_as'] );

						printf( 'Inserting %s as %s ID:%s', $record['wordlift:same_as'], Wordlift_Entity_Service::TYPE_NAME, $post_id );
						echo PHP_EOL;
					} else {
						$post_id = $entity->ID;

						// Update title
						wp_update_post( array(
							'ID'          => $post_id,
							'post_title'  => $record['wordpress:post_title']
						) );

						printf( 'Updating %s in %s ID:%s', $record['wordlift:same_as'], Wordlift_Entity_Service::TYPE_NAME, $post_id );
						echo PHP_EOL;
					}

					// Common tasks: Set type, alt_label
					Wordlift_Entity_Type_Service::get_instance()->set( $post_id, $record['wordlift:type'] );
					if ( isset($record['wordlift:alt_label']) ) {
						add_post_meta( $post_id, Wordlift_Entity_Service::ALTERNATIVE_LABEL_META_KEY, $record['wordlift:alt_label'] );
					}
					// TODO implement acf:<field_name>

				}

			},
		) );
	}

}
