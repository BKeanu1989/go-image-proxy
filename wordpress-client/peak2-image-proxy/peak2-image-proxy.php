<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://bkeanu1989.github.io
 * @since             1.0.0
 * @package           Peak2_Image_Proxy
 *
 * @wordpress-plugin
 * Plugin Name:       Peak2Labs Image Proxy
 * Plugin URI:        https://github.com/BKeanu1989/go-image-proxy/tree/master/wordpress-client/peak2-image-proxyhttps://github.com/BKeanu1989/go-image-proxy/
 * Description:       Wordpress image proxy connector
 * Version:           1.0.0
 * Author:            Peak2Labs
 * Author URI:        https://bkeanu1989.github.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       peak2-image-proxy
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PEAK2_IMAGE_PROXY_VERSION', '1.0.0' );
define('PEAK2LABS_IMAGE_PROXY_PLUGIN_PATH', WP_PLUGIN_DIR . '/peak2labs-image-proxy/');


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-peak2-image-proxy-activator.php
 */
function activate_peak2_image_proxy() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-peak2-image-proxy-activator.php';
	Peak2_Image_Proxy_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-peak2-image-proxy-deactivator.php
 */
function deactivate_peak2_image_proxy() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-peak2-image-proxy-deactivator.php';
	Peak2_Image_Proxy_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_peak2_image_proxy' );
register_deactivation_hook( __FILE__, 'deactivate_peak2_image_proxy' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-peak2-image-proxy.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_peak2_image_proxy() {

	$plugin = new Peak2_Image_Proxy();
	$plugin->run();

	
}
if (!wp_next_scheduled("image_proxy_health_check")) {
	error_log("inside block to schedule event");
	wp_schedule_event( time(), "five_minutes", "image_proxy_health_check");
}
run_peak2_image_proxy();
