<?php

class Peak2_Image_Proxy_Header
{
    private $plugin_name;
    private $version;
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public static function add_preconnect_to_header()
    {
        $endpoint = Peak2_Image_Proxy_Settings::get_endpoint_option();
        if ($endpoint !== '') :
        ?>

            <link rel="preconnect" href="<?php echo $endpoint ?>" />
        <?php
        endif;
    }
}