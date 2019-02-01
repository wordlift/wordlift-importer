<?php
/**
 * Exporter.
 *
 * Process an export request and exports a TSV file.
 *
 * @since 1.0.0
 */

/**
 * Define the {@link Wordlift_Importer_Admin_Ajax_Export} class.
 *
 * @since 1.0.0
 */
class Wordlift_Importer_Admin_Ajax_Export {

	public function process() {

		set_time_limit( 900 );

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=wordlift-export.tsv' );
		header( 'Content-Type: text/tab-separated-values; charset=' . get_option( 'blog_charset' ), true );

		$args = array(
			// Do not use `-1` otherwise `offset` is ignored.
			'posts_per_page'         => PHP_INT_MAX,
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'post_type'              => $_POST['post_types'],
			'post_status'            => 'any',
			'order'                  => 'DESC',
			'orderby'                => 'ID',

		);

		if ( ! empty( $_POST['offset'] ) && is_numeric( $_POST['offset'] ) ) {
			$args['offset'] = (int) $_POST['offset'];
		}

		if ( ! empty( $_POST['limit'] ) && is_numeric( $_POST['limit'] ) ) {
			$args['posts_per_page'] = (int) $_POST['limit'];
		}

		$posts = get_posts( $args );

		$out = fopen( 'php://output', 'w' );

		fputcsv( $out, array(
			'post:permalink',
			'post:post_content',
			'postmeta:entity_same_as',
			'postmeta:_wl_alt_label',
			'thumbnail:url',
		), "\t" );

		/** @var WP_Post $post */
		foreach ( $posts as $post ) {

			$fields = array(
				// Permalink.
				get_permalink( $post->ID ),
				// Post content.
				$post->post_content,
				// sameAs.
				implode( ', ', get_post_meta( $post->ID, Wordlift_Schema_Service::FIELD_SAME_AS ) ),
				// Synonyms.
				implode( ', ', get_post_meta( $post->ID, Wordlift_Entity_Service::ALTERNATIVE_LABEL_META_KEY ) ),
				// Thumbnail URL.
				get_the_post_thumbnail_url( $post->ID ),
			);

			fputcsv( $out, $fields, "\t" );

		}

		fclose( $out );
		wp_die();

	}

}