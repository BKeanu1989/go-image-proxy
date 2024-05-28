<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://bkeanu1989.github.io
 * @since      1.0.0
 *
 * @package    Peak2_Image_Proxy
 * @subpackage Peak2_Image_Proxy/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Peak2_Image_Proxy
 * @subpackage Peak2_Image_Proxy/includes
 * @author     Peak2Labs <info@peak2labs.com>
 */
class Peak2_Image_Proxy_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'peak2-image-proxy',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
