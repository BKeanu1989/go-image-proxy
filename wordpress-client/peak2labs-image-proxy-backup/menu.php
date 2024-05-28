<?php

include_once (PEAK2LABS_IMAGE_PROXY_PLUGIN_PATH . 'includes/settings.php');

add_action('admin_menu', 'peak2labs_menu_page');
function peak2labs_menu_page() {
    add_menu_page(
        'Peak2Labs',
        'Image Proxy',
        'edit_image_proxy',
        PEAK2LABS_IMAGE_PROXY_PLUGIN_PATH . 'views/main.php',
        null,
        'dashicons-download',
        10
    );
}

function wc_exporter_page() {
    include_once(PEAK2LABS_IMAGE_PROXY_PLUGIN_PATH . 'views/foo.php');
}



// Callback functions for the settings fields
function peak2_labs_image_proxy_main_callback() {
    echo '<p>Main settings description goes here.</p>';
}

function peak2_labs_image_proxy_text_1_callback($args) {
    $options = get_option('peak2_labs_image_proxy_url');
    echo "<input type='text' name='my_custom_plugin[text_1]' value='{$options['text_1']}' />";
}

function peak2_labs_image_proxy_text_2_callback($args) {
    $options = get_option('my_custom_plugin');
    echo "<input type='text' name='my_custom_plugin[text_2]' value='{$options['text_2']}' />";
}

if (!wp_next_scheduled('my_daily_event')) {
    wp_schedule_event(time(), 'daily', 'my_daily_event');
}

function my_daily_event_handler() {
    // Your scheduled task logic here
    error_log('Daily event triggered.');
}

add_action('my_daily_event', 'my_daily_event_handler');

