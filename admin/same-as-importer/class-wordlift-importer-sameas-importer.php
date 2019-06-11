<?php

class Wordlift_Importer_SameAs_Importer {

	public function __construct() {

		add_action( 'admin_init', array( $this, 'register_importer' ) );

	}

	public function register_importer() {

		if ( ! function_exists( 'register_importer' ) ) {
			return;
		}

		register_importer(
			'wl_importer_using_sameas',
			__( 'WordLift Importer (using sameAs).', 'wordlift-importer' ),
			__( 'Merge data using the `sameAs`field.', 'wordlift-importer' ),
			array( $this, 'callback', )
		);

	}

	public function callback() {

		wp_enqueue_script( 'plupload' );

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/wordlift-importer-sameas-importer-partial.php';

	}

}
