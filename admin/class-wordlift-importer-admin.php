<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wordlift.io
 * @since      1.0.0
 *
 * @package    Wordlift_Importer
 * @subpackage Wordlift_Importer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wordlift_Importer
 * @subpackage Wordlift_Importer/admin
 * @author     David Riccitelli <david@wordlift.io>
 */
class Wordlift_Importer_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wordlift_Importer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wordlift_Importer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wordlift-importer-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wordlift_Importer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wordlift_Importer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wordlift-importer-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function admin_init() {

		/*
		 * @param string   $id          Importer tag. Used to uniquely identify importer.
		 * @param string   $name        Importer name and title.
		 * @param string   $description Importer description.
		 * @param callable $callback    Callback to run.
		 */
		if ( ! function_exists( 'register_importer' ) ) {
			return;
		}

		register_importer( 'wl_importer', 'WordLift Importer', 'WordLift Importer', array(
			$this,
			'importer_callback',
		) );

	}

	public function importer_callback() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/wordlift-importer-admin-display.php';
	}

	public function admin_menu() {

		add_submenu_page(
			'wl_admin_menu',
			__( 'Import/Export' ),
			__( 'Import/Export' ),
			'edit_posts',
			'wl_importer_page',
			array( $this, 'wl_importer_page', )
		);

	}

	public function wl_importer_page() {

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'Sorry, you are not allowed.' ) );
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/wordlift-importer-export.php';

	}

	/**
	 * Process the `export` AJAX hook.
	 *
	 * @since 1.0.0
	 */
	public function export() {

		$exporter = new Wordlift_Importer_Admin_Ajax_Export();
		$exporter->process();

	}

	/**
	 * Process the `import` AJAX hook.
	 *
	 * @since 1.0.0
	 */
	public function import() {

		$importer = new Wordlift_Importer_Admin_Ajax_Import();
		$importer->process();

	}

}
