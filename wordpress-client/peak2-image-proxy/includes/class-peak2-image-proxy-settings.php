<?php

class Peak2_Image_Proxy_Settings
{

    private $plugin_name;
    private $version;

    const PEAK2_LABS_ENDPOINT_IDENTIFIER = "peak2_labs_image_proxy_endpoint";
    const PEAK2_LABS_DEFAULT_QUALITY_IDENTIFIER = "peak2_labs_image_proxy_default_quality";
    const PEAK2_LABS_DEFAULT_FORMAT_IDENTIFIER = "peak2_labs_image_proxy_default_format";
    
    public function __construct($plugin_name, $version) 
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function add_options_page() {
        // add_options_page( 'Peak2 Labs Settings', 'Peak2 Labs', 'manage_options', 'peak2-labs-settings', 'peak2labs_option_page' );
        add_options_page( 'Peak2 Labs Settings', 'Peak2 Labs', 'manage_options', 'peak2-labs-settings', [$this, 'options_page'] );
    }

    public static function register_settings() {
        add_settings_section(
            'peak2_labs_image_proxy_main', // ID
            __('Main Settings', 'my-custom-plugin'), // Title
            ['Peak2_Image_Proxy_Settings', 'peak2_labs_image_proxy_main_callback'], // Callback
            'peak2_labs_image_proxy' // Page
        );

        add_settings_field(
            'peak2_labs_image_proxy_endpoint', // ID
            __('Endpoint', 'my-custom-plugin'), // Title
            ['Peak2_Image_Proxy_Settings', 'peak2_labs_image_proxy_endpoint_callback'], // Callback
            'peak2_labs_image_proxy', // Page
            'peak2_labs_image_proxy_main' // Section
        );

        add_settings_field(
            "peak2_labs_image_proxy_default_quality", // ID
            __('Default Image Quality', 'my-custom-plugin'), // Title
            ['Peak2_Image_Proxy_Settings', 'peak2_labs_image_proxy_default_quality_callback'], // Callback
            'peak2_labs_image_proxy', // Page
            'peak2_labs_image_proxy_main' // Section
        );

        add_settings_field(
            "peak2_labs_image_proxy_default_format", // ID
            __('Default Image Format', 'my-custom-plugin'), // Title
            ['Peak2_Image_Proxy_Settings', 'peak2_labs_image_proxy_default_format_callback'], // Callback
            'peak2_labs_image_proxy', // Page
            'peak2_labs_image_proxy_main' // Section
        );


        register_setting('peak2_labs_image_proxy', 'peak2_labs_image_proxy_endpoint');
        register_setting('peak2_labs_image_proxy', 'peak2_labs_image_proxy_default_quality');
        register_setting('peak2_labs_image_proxy', 'peak2_labs_image_proxy_default_format');
    } 

    public static function peak2_labs_image_proxy_main_callback() {
        ?>
            <p><?php _e('Please provide the necessary data:'); ?></p>
        <?php
    }

    public static function peak2_labs_image_proxy_endpoint_callback($args) {
        $endpoint = self::get_endpoint_option();
        
        ?>
        <div class="form-group">
            <input type="text" id="<?php echo self::PEAK2_LABS_ENDPOINT_IDENTIFIER ?>" name="<?php echo self::PEAK2_LABS_ENDPOINT_IDENTIFIER?>" value="<?php echo esc_attr($endpoint) ?>" />
        </div>
        <?php
    }

    public static function peak2_labs_image_proxy_default_quality_callback($args) {
        $value = self::get_default_quality_option();
        
        ?>
        <div class="form-group">
            <input type="number" min="1" max="100" id="<?php echo self::PEAK2_LABS_DEFAULT_QUALITY_IDENTIFIER ?>" name="<?php echo self::PEAK2_LABS_DEFAULT_QUALITY_IDENTIFIER?>" value="<?php echo esc_attr($value) ?>" />
        </div>
        <?php
    }

    public static function peak2_labs_image_proxy_default_format_callback($args) 
    {
        $value = self::get_default_format_option();
        $selectOptions = [".jpeg", ".png"];
        
        ?>
        <div class="form-group">
            <select name="<?php echo self::PEAK2_LABS_DEFAULT_FORMAT_IDENTIFIER?>" id="<?php echo self::PEAK2_LABS_DEFAULT_FORMAT_IDENTIFIER ?>">
                <?php foreach ($selectOptions as $item) {
                    $selected = ($value === $item) ? 'selected="selected"' : '';
                    echo "<option value='$item' $selected>$item</option>";
                } ?>
            </select>
        </div>
        <?php
    }

    public static function get_endpoint_option()
    {
        return get_option(self::PEAK2_LABS_ENDPOINT_IDENTIFIER);
    }

    public static function get_default_quality_option()
    {
        return get_option(self::PEAK2_LABS_DEFAULT_QUALITY_IDENTIFIER);
    }

    public static function get_default_format_option()
    {
        return get_option(self::PEAK2_LABS_DEFAULT_FORMAT_IDENTIFIER);
    }

    public static function options_page() {
        // $
        ?>
        <div class="wrap">
            <h2>Peak 2 Labs Settings</h2>

            <?php settings_errors( ); ?>

            <!-- <form action="options-general.php?page=peak2-labs-settings" method="POST"> -->
            <form action="options.php" method="POST">
                <!-- <input type="hidden" name="action" value="save_peak2_labs_settings"> -->
                <?php
                    settings_fields('peak2_labs_image_proxy');
                    do_settings_sections('peak2_labs_image_proxy');
                ?>

                <?php submit_button() ?>
            </form>
        </div>

<?php
    }

}