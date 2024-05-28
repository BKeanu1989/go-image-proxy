<?php

class Peak2_Image_Proxy_Cron 
{

    private $plugin_name;
    private $version;
    public function __construct($plugin_name, $version) 
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public static function evaluate_transient() 
    {

        // make request to image proxy endpoint 
        // 

        error_log("evaluate transient");

        

    } 
}