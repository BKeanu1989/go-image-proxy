<?php
/*
 * Plugin Name: Peak 2 Labs Image Proxy
 */

//  register_activation_hook(
// 	__FILE__,
// 	'pluginprefix_function_to_run'
// );

// register_deactivation_hook(
// 	__FILE__,
// 	'pluginprefix_function_to_run'
// );

// function themeslug_enqueue_style() {
//     wp_enqueue_style( 'my-theme', 'style.css', false );
// }

function plugin_enqueue_script() {
    wp_enqueue_script( 'peak2-image-proxy', plugins_url( "public/peak2labs-image-proxy.js", __FILE__ ), false );
}

// add_action( 'wp_enqueue_scripts', 'themeslug_enqueue_style' );
add_action( 'wp_enqueue_scripts', 'plugin_enqueue_script' );
