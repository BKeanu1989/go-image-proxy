<?php

add_action( 'admin_menu', 'peak2labs_admin_menu' );
function peak2labs_admin_menu() {
    add_options_page( 'Peak2Labs Image Proxy', 'Peak2Labs Image Proxy', 'manage_options', 'peak2labs-image', 'peak2labs_options_page' );
}



function peak2labs_image_proxy_settings_init() {
    // Add settings section
    add_settings_section(
        'peak2_labs_image_proxy_main', // ID
        __('Main Settings', 'my-custom-plugin'), // Title
        'peak2_labs_image_proxy_main_callback', // Callback
        'peak2_labs_image_proxy' // Page
    );

    // Add settings field
    add_settings_field(
        'peak2_labs_image_proxy_text_1', // ID
        __('Text Field 1', 'my-custom-plugin'), // Title
        'peak2_labs_image_proxy_text_1_callback', // Callback
        'peak2_labs_image_proxy', // Page
        'peak2_labs_image_proxy_main' // Section
    );

    // Add another settings field
    add_settings_field(
        'peak2_labs_image_proxy_text_2', // ID
        __('Text Field 2', 'my-custom-plugin'), // Title
        'peak2_labs_image_proxy_text_2_callback', // Callback
        'peak2_labs_image_proxy', // Page
        'peak2_labs_image_proxy_main' // Section
    );

    // Register settings
    register_setting('peak2_labs_image_proxy', 'peak2_labs_image_proxy_text_1');
    register_setting('peak2_labs_image_proxy', 'peak2_labs_image_proxy_text_2');
}

add_action('admin_init', 'peak2labs_image_proxy_settings_init');

function peak2labs_options_page() {
    ?>
    <div class="wrap">
        <h2>Peak2 Labs Image Proxz Options</h2>
        <?php settings_errors(); ?>

        <?php
            $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'halle_options';
        ?>

        <h2 class="nav-tab-wrapper">
            <a href="?page=mokka-exporter&tab=halle_options" class="nav-tab <?php echo $active_tab == 'halle_options' ? 'nav-tab-active' : ''; ?>">Halle</a>
            <a href="?page=mokka-exporter&tab=nordachse_options" class="nav-tab <?php echo $active_tab == 'nordachse_options' ? 'nav-tab-active' : ''; ?>">Nordachse</a>
        </h2>

        <form action="options.php" method="POST">
            <?php 
                if ($active_tab === 'halle_options'):
                    settings_fields( 'mokka-exporter-halle-settings-group' );
                    do_settings_sections( 'mokka-exporter--halle-options' );
                elseif ($active_tab === 'nordachse_options'):
                    settings_fields( 'mokka-exporter-nordachse-settings-group' );
                    do_settings_sections( 'mokka-exporter--nordachse-options' );
                endif;
            ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}