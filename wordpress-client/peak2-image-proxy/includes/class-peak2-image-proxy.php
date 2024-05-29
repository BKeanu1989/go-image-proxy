<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://bkeanu1989.github.io
 * @since      1.0.0
 *
 * @package    Peak2_Image_Proxy
 * @subpackage Peak2_Image_Proxy/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Peak2_Image_Proxy
 * @subpackage Peak2_Image_Proxy/includes
 * @author     Peak2Labs <info@peak2labs.com>
 */
class Peak2_Image_Proxy {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Peak2_Image_Proxy_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PEAK2_IMAGE_PROXY_VERSION' ) ) {
			$this->version = PEAK2_IMAGE_PROXY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'peak2-image-proxy';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_avada_image_hook();
		$this->define_settings_hook();
		$this->define_cron_listeners();
		$this->define_header_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Peak2_Image_Proxy_Loader. Orchestrates the hooks of the plugin.
	 * - Peak2_Image_Proxy_i18n. Defines internationalization functionality.
	 * - Peak2_Image_Proxy_Admin. Defines all hooks for the admin area.
	 * - Peak2_Image_Proxy_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-peak2-image-proxy-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-peak2-image-proxy-avada.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-peak2-image-proxy-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-peak2-image-proxy-cron.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-peak2-image-proxy-header.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-peak2-image-proxy-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-peak2-image-proxy-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-peak2-image-proxy-public.php';

		$this->loader = new Peak2_Image_Proxy_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Peak2_Image_Proxy_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Peak2_Image_Proxy_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Peak2_Image_Proxy_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Peak2_Image_Proxy_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	private function define_avada_image_hook() {
		$plugin_avada = new Peak2_Image_Proxy_Avada($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('fusion_element_image_content', $plugin_avada, 'upgrade_avada_image', 999, 2);
	}


	private function define_settings_hook() {
		$plugin_options = new Peak2_Image_Proxy_Settings($this->get_plugin_name(), $this->get_version());


		$this->loader->add_action('admin_menu', $plugin_options, 'add_options_page');
		$this->loader->add_action('admin_init', $plugin_options, 'register_settings');
	}

	private function define_cron_listeners() {

		$cron_plugin = new Peak2_Image_Proxy_Cron($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action( "image_proxy_health_check", $cron_plugin, "evaluate_transient");
	}

	private function define_header_hooks() {

		$header = new Peak2_Image_Proxy_Header($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action("wp_head", $header, "add_preconnect_to_header");
	}
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Peak2_Image_Proxy_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
