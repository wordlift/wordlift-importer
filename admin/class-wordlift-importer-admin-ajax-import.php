<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2019-01-31
 * Time: 11:50
 */

abstract class Fields {
	const PERMALINK = 0;
	const POST_CONTENT = 1;
	const SAME_ASS = 2;
	const ALT_LABELS = 3;
	const THUMBNAIL_URLS = 4;
}

class Wordlift_Importer_Admin_Ajax_Import {

	public function process() {

		@set_time_limit( 900 );

		// Start sending some data.
		echo( 'starting...<br/>' );
		ob_flush();

		if ( false === ( $handle = fopen( $_FILES['file']['tmp_name'], 'r' ) ) ) {
			wp_send_json_error( 'Cannot open the file.' );
		}

		// Skip the header line.
		fgets( $handle );

		$do_same_as       = 'yes' === filter_input( INPUT_POST, 'do_same_as' );
		$do_alt_labels    = 'yes' === filter_input( INPUT_POST, 'do_alt_labels' );
		$do_thumbnails    = 'yes' === filter_input( INPUT_POST, 'do_thumbnails' );
		$do_content       = 'yes' === filter_input( INPUT_POST, 'do_content' );
		$force_thumbnails = 'yes' === filter_input( INPUT_POST, 'force_thumbnails' );

		echo( 'same_as: ' . ( $do_same_as ? 'yes' : 'no' ) . '<br/>' );
		echo( 'alt_labels: ' . ( $do_alt_labels ? 'yes' : 'no' ) . '<br/>' );
		echo( 'thumbnails: ' . ( $do_thumbnails ? 'yes' : 'no' ) . '<br/>' );
		echo( 'content: ' . ( $do_content ? 'yes' : 'no' ) . '<br/>' );

		while ( false !== ( $data = fgetcsv( $handle, 0, "\t" ) ) ) {

			$debug = '';

			if ( strpos( $data[ Fields::PERMALINK ], 'http' ) ) {
				echo( $data[ Fields::PERMALINK ] . ' not a valid URL.<br/>' );
				continue;
			}

			// Remove the host.
			$path    = preg_replace( '~^https?://[^/]+~', '', $data[ Fields::PERMALINK ], 1 );
			$url     = home_url( $path );
			$post_id = url_to_postid( $url );

			// Skip post if not found.
			if ( 0 === $post_id ) {
				echo( $data[ Fields::PERMALINK ] . " not found.<br/>" );
				continue;
			}

			if ( $do_same_as && ! empty( $data[ Fields::SAME_ASS ] ) ) {
				$debug  .= 'S';
				$values = explode( ', ', $data[ Fields::SAME_ASS ] );
				foreach ( $values as $value ) {
					$this->add_post_meta( $post_id, Wordlift_Schema_Service::FIELD_SAME_AS, $value );
				}
			}

			if ( $do_alt_labels && ! empty( $data[ Fields::ALT_LABELS ] ) ) {
				$debug  .= 'L';
				$values = explode( ', ', $data[ Fields::ALT_LABELS ] );
				foreach ( $values as $value ) {
					$this->add_post_meta( $post_id, Wordlift_Entity_Service::ALTERNATIVE_LABEL_META_KEY, $value );
				}
			}

			if ( $do_thumbnails && ! empty( $data[ Fields::THUMBNAIL_URLS ] ) ) {
				$debug  .= 'I';
				$values = explode( ', ', $data[ Fields::THUMBNAIL_URLS ] );
				foreach ( $values as $value ) {
					$this->set_post_image_from_url( $value, $post_id, $force_thumbnails );
				}
			}

			if ( $do_content && ! empty( $data[ Fields::POST_CONTENT ] ) ) {
				$debug .= 'C';
				wp_update_post( array(
					'ID'           => $post_id,
					'post_content' => $data[ Fields::POST_CONTENT ],
				) );
			}

			edit_post_link( $post_id, 'Data imported to post ', ' (' . $debug . ').<br/>', $post_id );

			clean_post_cache( $post_id );

			ob_flush();

		}

		fclose( $handle );

	}

	private function add_post_meta( $post_id, $meta_key, $meta_value ) {

		if ( empty( $meta_value ) || in_array( $meta_value, get_post_meta( $post_id, $meta_key ) ) ) {
			return false;
		}

		return add_post_meta( $post_id, $meta_key, $meta_value );
	}

	private function set_post_image_from_url( $url, $post_id, $force = false ) {

		if ( ! $force && ! empty( get_post_thumbnail_id( $post_id ) ) ) {
			return false;
		}

		if ( 0 !== strpos( $url, 'http' ) ) {
			return false;
		}

		// Save the image and get the local path.
		$image = Wordlift_Remote_Image_Service::save_from_url( $url );

		if ( false === $image ) {
			echo( "Error saving image from $url.<br/>" );

			return false;
		}

		// Get the local URL.
		$filename     = $image['path'];
		$url          = $image['url'];
		$content_type = $image['content_type'];

		// Use the post title as label.
		$label = get_the_title( $post_id );

		$attachment = array(
			'guid'           => $url,
			// post_title, post_content (the value for this key should be the empty string), post_status and post_mime_type
			'post_title'     => $label,
			// Set the title to the post title.
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_mime_type' => $content_type,
		);

		// Create the attachment in WordPress and generate the related metadata.
		$attachment_id = wp_insert_attachment( $attachment, $filename, $post_id );

		// Set the source URL for the image.
		wl_set_source_url( $attachment_id, $url );

		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// Set it as the featured image.
		set_post_thumbnail( $post_id, $attachment_id );

	}

}