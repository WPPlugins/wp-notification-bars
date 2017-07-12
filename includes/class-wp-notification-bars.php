<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://mythemeshop.com
 * @since      1.0
 *
 * @package    MTSNBF
 * @subpackage MTSNBF/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0
 * @package    MTSNBF
 * @subpackage MTSNBF/includes
 * @author     MyThemeShop
 */
class MTSNBF {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      MTSNBF_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0
	 */
	public function __construct() {

		$this->plugin_name = 'wp-notification-bars';
		$this->version = '1.0.1';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_shared_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - MTSNBF_Loader. Orchestrates the hooks of the plugin.
	 * - MTSNBF_i18n. Defines internationalization functionality.
	 * - MTSNBF_Admin. Defines all hooks for the dashboard.
	 * - MTSNBF_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-notification-bars-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-notification-bars-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-notification-bars-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-notification-bars-public.php';

		/**
		 * The class responsible for defining all actions that occur in both sides of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-notification-bars-shared.php';

		$this->loader = new MTSNBF_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the MTSNBF_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new MTSNBF_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new MTSNBF_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'check_version' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Register our post type
		$this->loader->add_action( 'init', $plugin_admin, 'mts_notification_cpt' );

		// Metaboxes
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_custom_meta_box' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_custom_meta' );

		// Add preview button to poblish metabox
		$this->loader->add_action( 'post_submitbox_misc_actions', $plugin_admin, 'add_preview_button' );

		$this->loader->add_filter( 'post_updated_messages', $plugin_admin, 'mtsnb_update_messages' );

		// Force notification bar metabox
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'mtsnb_select_metabox_insert' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'mtsnb_select_metabox_save' );
		$this->loader->add_action( 'wp_ajax_mtsnb_get_bars', $plugin_admin, 'mtsnb_get_bars' );
		$this->loader->add_action( 'wp_ajax_mtsnb_get_bar_titles', $plugin_admin, 'mtsnb_get_bar_titles' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new MTSNBF_Public( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * Register all of the hooks related to both public and dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_shared_hooks() {

		$plugin_shared = new MTSNBF_Shared( $this->get_plugin_name(), $this->get_version() );

		// get/set bar settings
		$this->loader->add_action( 'wp', $plugin_shared, 'get_notification_bar_data' );
		// Display bar on front end
		$this->loader->add_action( 'wp_footer', $plugin_shared, 'display_bar' );
		// Ajax Preview on backend
		$this->loader->add_action( 'wp_ajax_preview_bar', $plugin_shared, 'preview_bar' );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_shared, 'enqueue_styles', -1 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_shared, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_shared, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_shared, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0
	 * @return    MTSNBF_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
