<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2019-01-31
 * Time: 11:50
 */

abstract class Field_Names {
	const PERMALINK = 'permalink';
	const POST_CONTENT = 'content';
	const SAME_AS = 'same_as';
	const ALT_LABELS = 'alt_labels';
	const THUMBNAILS = 'thumbnails';
	const TITLE = 'title';
	const URL = 'url';
	const TYPE = 'type';

	public static $fields = array(
		self::POST_CONTENT => 'do_post_content',
		self::SAME_AS      => 'do_same_as',
		self::ALT_LABELS   => 'do_alt_labels',
		self::THUMBNAILS   => 'do_thumbnails',
		self::TITLE        => 'do_title',
		self::URL          => 'do_url',
		self::TYPE         => 'do_type',
	);

	public static function do_type( $header, $data, $post_id ) {
		if ( 'yes' !== filter_input( INPUT_POST, 'do_' . self::TYPE ) ) {
			return;
		}

		$values = explode( ', ', $header->get_field_value( Field_Names::TYPE, $data ) );
		for ( $i = 0; $i < count( $values ); $i ++ ) {
			Wordlift_Entity_Type_Service::get_instance()
			                            ->set( $post_id, $values[ $i ], 0 === $i );
		}
	}

	public static function do_url( $header, $data, $post_id ) {
		if ( 'yes' !== filter_input( INPUT_POST, 'do_' . self::URL ) ) {
			return;
		}

		$value = $header->get_field_value( Field_Names::URL, $data );
		if ( empty( $value ) ) {
			return;
		}

		echo( "Setting URL $value for post $post_id...<br/>" );
		self::add_post_meta( $post_id, Wordlift_Schema_Url_Property_Service::META_KEY, $value );
	}

	public static function do_title( $header, $data, $post_id ) {
		if ( 'yes' !== filter_input( INPUT_POST, 'do_' . self::TITLE ) ) {
			return;
		}

		$value = $header->get_field_value( Field_Names::TITLE, $data );
		if ( empty( $value ) ) {
			return;
		}

		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => $value,
		) );

	}

	public static function do_same_as( $header, $data, $post_id ) {
		if ( 'yes' !== filter_input( INPUT_POST, 'do_' . self::SAME_AS ) ) {
			return;
		}

		// same as.
		$same_as = $header->get_field_value( Field_Names::SAME_AS, $data );
		if ( empty( $same_as ) ) {
			return;
		}

		$values = explode( ', ', $same_as );
		foreach ( $values as $value ) {
			self::add_post_meta( $post_id, Wordlift_Schema_Service::FIELD_SAME_AS, $value );
		}

	}

	public static function do_alt_labels( $header, $data, $post_id ) {
		if ( 'yes' !== filter_input( INPUT_POST, 'do_' . self::ALT_LABELS ) ) {
			return;
		}

		$alt_labels = $header->get_field_value( Field_Names::ALT_LABELS, $data );
		if ( empty( $alt_labels ) ) {
			return;
		}
		$values = explode( ', ', $alt_labels );
		foreach ( $values as $value ) {
			self::add_post_meta( $post_id, Wordlift_Entity_Service::ALTERNATIVE_LABEL_META_KEY, $value );
		}
	}

	public static function do_thumbnails( $header, $data, $post_id ) {
		if ( 'yes' !== filter_input( INPUT_POST, 'do_' . self::THUMBNAILS ) ) {
			return;
		}

		$thumbnails = $header->get_field_value( Field_Names::THUMBNAILS, $data );
		if ( empty( $thumbnails ) ) {
			return;
		}
		$values = explode( ', ', $thumbnails );
		foreach ( $values as $value ) {
			self::set_post_image_from_url( $value, $post_id, 'yes' === filter_input( INPUT_POST, 'force_thumbnails' ) );
		}
	}

	public static function do_post_content( $header, $data, $post_id ) {
		if ( 'yes' !== filter_input( INPUT_POST, 'do_' . self::POST_CONTENT ) ) {
			return;
		}

		$post_content = $header->get_field_value( Field_Names::POST_CONTENT, $data );
		if ( empty( $post_content ) ) {
			return;
		}
		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $post_content,
		) );
	}


	private static function add_post_meta( $post_id, $meta_key, $meta_value ) {

		if ( empty( $meta_value )
		     || ( false !== get_post_meta( $post_id, $meta_key )
		          && in_array( $meta_value, get_post_meta( $post_id, $meta_key ) ) ) ) {
			return false;
		}

		return add_post_meta( $post_id, $meta_key, $meta_value );
	}

	private static function set_post_image_from_url( $url, $post_id, $force = false ) {

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

/**
 *
 *
 * @since 1.2.0
 */
class Wordlift_Importer_Header {

	private $fields;

	public function __construct( $fields ) {

		$this->fields = array_flip( $fields );

	}

	public function has_field( $field ) {

		return isset( $this->fields[ $field ] );
	}

	public function get_field_index( $field ) {

		return isset( $this->fields[ $field ] ) ? $this->fields[ $field ] : - 1;
	}

	public function get_field_value( $field, $data ) {

		if ( ! $this->has_field( $field ) ) {
			return null;
		}

		$index = $this->get_field_index( $field );

		return $data[ $index ];
	}

}

class Wordlift_Importer_Admin_Ajax_Import {

	public function process() {

		@set_time_limit( 3600 );

		// Start sending some data.
		echo( 'starting...<br/>' );
		ob_flush();

		if ( false === ( $handle = fopen( $_FILES['file']['tmp_name'], 'r' ) ) ) {
			wp_send_json_error( 'Cannot open the file.' );
		}

		/*
		 * Get the fields from the header.
		 *
		 * @see https://github.com/wordlift/wordlift-importer/issues/2
		 * @since 1.2.0
		 */
		$header_fields = fgetcsv( $handle, 0, "\t" );
		// Bail out if we can't determine the fields from the header.
		if ( false === $header_fields ) {
			wp_send_json_error( 'Cannot get the fields from the header.' );
		}

		// Create an instance of the `Wordlift_Importer_Header` class.
		$header = new Wordlift_Importer_Header( $header_fields );
		if ( ! $header->has_field( Field_Names::PERMALINK ) &&
		     ! $header->has_field( Field_Names::TITLE ) ) {

			wp_send_json_error( 'The title field is required when the permalink isn`t provided.' );
		}

		while ( false !== ( $data = fgetcsv( $handle, 0, "\t" ) ) ) {

			$debug = '';

			try {
				// Create new posts when the permalink field isn't found.
				$post_id = $this->get_or_create_post( $header, $data );
			} catch ( Exception $e ) {
				echo( $e->getMessage() . '<br/>' );
				continue;
			}

			foreach ( Field_Names::$fields as $field_name => $field_callback ) {
				call_user_func( array( 'Field_Names', $field_callback ), $header, $data, $post_id );
			}

			edit_post_link( $post_id, 'Data imported to post ', ' (' . $debug . ').<br/>', $post_id );

			clean_post_cache( $post_id );

			ob_flush();

		}

		fclose( $handle );

	}

	/**
	 * Get the post ID for the specified data.
	 *
	 * If the header contains a `permalink` field, the function will look for a matching post. If not found it'll
	 * throw an exception.
	 *
	 * If the header doesn't contain a `permalink` field then the `title` field is required (an exception is thrown if
	 * not provided) and a new post is created with the `title`.
	 *
	 * @param Wordlift_Importer_Header $header A {@link Wordlift_Importer_Header} instance to get the field data.
	 * @param array                    $data An array of fields' data.
	 *
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 * @throws Exception
	 *
	 * @since 1.2.0
	 */
	private function get_or_create_post( $header, $data ) {

		// If the permalink field hasn't been provided, or is empty, create a new post.
		$permalink = $header->get_field_value( Field_Names::PERMALINK, $data );
		if ( empty( $permalink ) ) {

			// Check that the title field has been provided and has a value.
			$title = $header->get_field_value( Field_Names::TITLE, $data );
			if ( empty( $title ) ) {
				throw new Exception( 'The title field is required when the permalink isn`t provided.' );
			}

			// Create an entity with the specified title.
			$post_id = wp_insert_post( array(
				'post_type'   => Wordlift_Entity_Service::TYPE_NAME,
				'post_title'  => $title,
				'post_status' => 'publish',
			) );

			echo( "$post_id created.<br/>" );

			return $post_id;
		}

		// Check that the permalink is valid.
		if ( strpos( $permalink, 'http' ) ) {
			throw new Exception( $permalink . ' not a valid URL.<br/>' );
		}

		// Remove the host.
		$path = preg_replace( '~^https?://[^/]+~', '', $permalink, 1 );
		$url  = home_url( $path );

		// Try to find the post.
		$post_id = url_to_postid( $url );

		// Throw an exception if the post is not found.
		if ( 0 === $post_id ) {
			throw new Exception( "$permalink not found.<br/>" );
		}

		// Finally return a post id.
		return $post_id;
	}

}
