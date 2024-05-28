<?php


function my_custom_settings_page() {
    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Output your settings page content
    echo '<div class="wrap">';
    echo '<h1>'. esc_html__('My Custom Settings', 'your-textdomain'). '</h1>';

    // Example: Display a form or other content
    echo '<form method="post" action="options.php">';
    settings_fields('my_custom_settings_group'); // Make sure to register your settings group
    do_settings_sections('my_custom_settings_page');
    submit_button(__('Save Changes'));
    echo '</form>';
}


function my_custom_settings_init() {
    // Register settings group
    error_log("my custom settings init");
    register_setting('my_custom_settings_group', 'my_custom_setting_value');

    // Add settings section
    add_settings_section(
        'my_custom_settings_section',
        __('Custom Settings', 'your-textdomain'),
        '__return_empty_string',
        'my_custom_settings_page'
    );

    // Add settings field
    add_settings_field(
        'my_custom_setting_value',
        __('Custom Setting Value', 'your-textdomain'),
        'my_custom_setting_value_callback',
        'my_custom_settings_page',
        'my_custom_settings_section'
    );
}

function my_custom_setting_value_callback() {
    $value = get_option('my_custom_setting_value');
    echo '<input type="text" id="my_custom_setting_value" name="my_custom_setting_value" value="'. esc_attr($value). '" />';
}

// add_options_page(
//     'My Custom Settings', // Page title
//     'My Custom Settings', // Menu title
//     'manage_options', // Capability
//     'my-custom-settings-page', // Menu slug
//     'my_custom_settings_page' // Function to display content
// );


function peak2_labs_image_proxy_admin_menu() {
    add_options_page( 'Peak2 Labs', 'Peak2 Labs', 'manage_options', 'mokka-exporter', 'peak2labs_option_page' );
}

function peak2labs_option_page() {
    echo "<p>peak 2 labs options page</p>";
}

