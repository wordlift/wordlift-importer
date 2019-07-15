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
					 *
					 */

					$same_as_uris = $record['wordlift:same_as'];
					$entity = self::get_entity( $same_as_uris );

					if(is_null($entity)){

						if(!empty(trim($record['wordpress:post_title']))){

							/*
							 * Create an entity with:
							 * ----------------------
							 * 1. wordpress:post_title
							 * 2. wordlift:same_as
							 */
							$post_id = wp_insert_post( array(
								'post_type'   => Wordlift_Entity_Service::TYPE_NAME,
								'post_title'  => $record['wordpress:post_title'],
								'post_status' => 'publish'
							) );

							if(!empty($same_as_uris)){
								$uri_array = explode(",", $same_as_uris);
								foreach ($uri_array as $uri) {
									add_post_meta( $post_id, Wordlift_Schema_Service::FIELD_SAME_AS, trim($uri) );
								}
							}

							printf( 'Inserting %s as %s ID:%s', $record['wordlift:same_as'], Wordlift_Entity_Service::TYPE_NAME, $post_id );
							echo PHP_EOL;

						}

					} else {
						$post_id = $entity->ID;

						if(!empty(trim($record['wordpress:post_title']))) {

							/*
							 * Update entity with:
							 * ----------------------
							 * 1. wordpress:post_title
							 */
							wp_update_post( array(
								'ID'         => $post_id,
								'post_title' => $record['wordpress:post_title']
							) );

							printf( 'Updating %s in %s ID:%s', $record['wordlift:same_as'], Wordlift_Entity_Service::TYPE_NAME, $post_id );
							echo PHP_EOL;

						}
					}

					/*
					 * Common tasks:
					 * -------------
					 * 1. wordlift:type
					 * 2. wordlift:alt_label
					 * 3. wordlift:url
					 */
					$type_array = explode(",", $record['wordlift:type']);
					Wordlift_Entity_Type_Service::get_instance()->set( $post_id, trim($type_array[0]) || 'http://schema.org/Thing' );

					// Conditionally add alt_label (can be multiple)
					if ( isset($record['wordlift:alt_label']) && !empty(trim($record['wordlift:alt_label'])) ) {
						$alt_label = get_post_meta( $post_id, Wordlift_Entity_Service::ALTERNATIVE_LABEL_META_KEY);
						if( !in_array($record['wordlift:alt_label'], $alt_label) ){
							add_post_meta( $post_id, Wordlift_Entity_Service::ALTERNATIVE_LABEL_META_KEY, $record['wordlift:alt_label'] );
						}
					}

					// Conditionally add url (can be multiple)
					if ( isset($record['wordlift:url']) && !empty(trim($record['wordlift:url'])) ) {

						if(substr( $record['wordlift:url'], 0, 4 ) !== "http"){
							$record['wordlift:url'] = site_url($record['wordlift:url']);
						}

						$url = get_post_meta( $post_id, Wordlift_Schema_Url_Property_Service::META_KEY);
						if( !in_array($record['wordlift:url'], $url) ){
							add_post_meta( $post_id, Wordlift_Schema_Url_Property_Service::META_KEY, $record['wordlift:url'] );
						}
					}

					// TODO implement acf:<field_name>

				}

			},
		) );
	}

	static function get_entity($uris){

		if(empty($uris)) return null;

		$uri_array = explode(",",$uris);

		foreach ($uri_array as $uri) {
			$entity = Wordlift_Entity_Service::get_instance()->get_entity_post_by_uri( $uri );
			if(!is_null($entity)) return $entity;
		}

		return null;
	}

}
