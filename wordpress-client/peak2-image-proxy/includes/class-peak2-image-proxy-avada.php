<?php
/*
 * Plugin Name: Peak 2 Labs Image
 */

 //     $str = '<div class="imageframe-align-center"><span class=" fusion-imageframe imageframe-dropshadow imageframe-39 hover-type-none" style="-webkit-box-shadow: 0px 0px 4px rgba(0,0,0,0.3);box-shadow: 0px 0px 4px rgba(0,0,0,0.3);"><img width="800" height="400" alt="Trespa" title="ka4463" src="http://localhost:3333/wp-content/uploads/2023/03/ka4463.png" class="img-responsive wp-image-35251" srcset="http://localhost:3333/wp-content/uploads/2023/03/ka4463-200x100.png 200w, http://localhost:3333/wp-content/uploads/2023/03/ka4463-400x200.png 400w, http://localhost:3333/wp-content/uploads/2023/03/ka4463-600x300.png 600w, http://localhost:3333/wp-content/uploads/2023/03/ka4463.png 800w" sizes="(max-width: 800px) 100vw, 600px" /></span></div>';

// define('PEAK2LABS_IMAGE_PROXY_PLUGIN_PATH', WP_PLUGIN_DIR . '/peak2labs-image-proxy/');
// require_once PEAK2LABS_IMAGE_PROXY_PLUGIN_PATH . 'settings.php';
// require_once PEAK2LABS_IMAGE_PROXY_PLUGIN_PATH . 'menu.php';
// require_once PEAK2LABS_IMAGE_PROXY_PLUGIN_PATH . 'options.php';



// error_log("init peak 2 labs image proxy");
class Peak2_Image_Proxy_Avada 
 {
    //  const IMAGE_PROXY_URL_PREFIX = "https://local.kevin-fechner.site/image/?url=";
     protected $url;
     private $plugin_name;
     private $version;
     protected $defaultQuality = 80;
     protected $defaultImageFormat = ".jpeg"; // for image conversion & jpeg for quality adaption as default
     protected $data = [];
     protected $avada_image = false;
     protected $imageBase = false;
     protected $oldSrcSetsString = false;
     protected $oldSrc = false;
     protected $oldImgFormat = false;
     protected $options = false;

     public $newSrcSetsString = false; 
     public $newSrc = false;

     public function __construct($plugin_name, $version) {
        
		$this->plugin_name = $plugin_name;
		$this->version = $version;

    }

    public static function get_image_proxy_prefix() {
        $endpoint = Peak2_Image_Proxy_Settings::get_endpoint_option();

        return trailingslashit($endpoint) . "image/?url=";
    }
    
    public function set_image($avada_image) {
        try {
           $this->avada_image = $avada_image;
           $this->parse_options();
          $this->parse_image_src($this->avada_image);
          $this->parse_srcsets($this->avada_image);
       
          $this->set_new_src();
          $this->set_new_srcsets();
       } catch (\Throwable $th) {
           error_log($th->getMessage());
           return $avada_image;
       }

     }
 
     public static function block_any_work($avada_image) {
        if (str_contains( $avada_image, "peak2-ignore" )) {
            return true;
        }
        return false;
     }

     public function parse_options() {

        // peak2-ignore
        // peak2-q-90
        $wantedQuality = $this->parse_quality();
        $wantedFormat = $this->parse_format();
        // peak2-f-jpeg

        // peak2-w-400
        // peak2-h-500

     }

     public function parse_quality() {
        preg_match('/peak2-q-(\d+)/', $this->avada_image, $matches);

        if (count($matches) > 0) {
            return $matches[1];
        }
     }

     
     public function parse_format() {
        preg_match('/peak2-f-(\w+)/', $this->avada_image, $matches);

        if (count($matches) > 0) {
            return $matches[1];
        }
     }
     
     /**
      * get input from avada image
      *
      * @param string $html
      * @return string of src
      */
     protected function parse_image_src($html) {
         $re = '/src=\"((.*)(.png|.jpeg|.jpg|.webp))\"/m';
     
         preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
        //  error_log("img src result");
        //  error_log(print_r($matches,1));
         $this->oldSrc = $matches[0][1];
         $this->imageBase = $matches[0][2];
         $this->oldImgFormat = $matches[0][3];
        //  $this->oldSrcString = $matches[0][0];
        //  $avadaSrc = 
         return $matches[0][1];
     }


 /**
  * gets input from avada image
  *
  * @param string $html
  * @return void
  */
     protected function parse_srcsets($html) {
         $data = [];
         preg_match('/srcset="([^"]+)"/', $html, $matches);
 
         $srcsetAttribute = $matches[1];
         $this->oldSrcSetsString = $srcsetAttribute;
         $srcsetValues = explode(', ', $srcsetAttribute);
 
         foreach ($srcsetValues as $key => $value) {
             $re = '/^(.*(.png|.jpeg|.jpg|.gif)) (\d*)w$/m';
         
             preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);
             if (count($matches) > 0) {
                 $arr = [
                    "rawUrl" => $matches[0][0],
                     "url" => $matches[0][1],
                     "format" => $matches[0][2],
                     "width" => $matches[0][3]
                 ];
                 $data[] = $arr;
             }
         }
         $this->data = $data;
        //  return $data;
     }

     protected function set_new_src() {
        // error_log(print_r($_SERVER, 1));
        $str = "";
        
        $opts = new Peak2_Image_Proxy_Options();
        $str .= self::get_image_proxy_prefix() . $this->imageBase . $this->oldImgFormat . $opts->as_string();
        // return $str;
        $this->newSrc = $str;
     }
 
     protected function set_new_srcsets() {
        $str = "";

        $count = count($this->data);

        foreach ($this->data as $key => $x) {
            // error_log(print_r($x, 1));
            $defaultOpts = new Peak2_Image_Proxy_Options(80, ".jpeg",$x["width"]);
            $str .= self::get_image_proxy_prefix() . $x["url"] . $defaultOpts->as_string() . " " . $x["width"] . "w";             
            // error_log("string now: $str");
            if ($key !== $count) {
                $str .= ", ";
            }
        }
        // error_log("new srcset: ");
        // error_log($str);
        $this->newSrcSetsString = $str;
        // return $str;
     }
 
    public function as_html() {
        if (self::block_any_work($this->avada_image)) return $this->avada_image; 

        if (!$this->oldSrc || !$this->newSrc) {
            error_log("ERRORS");
            error_log($this->oldSrc);
            error_log($this->newSrc);
        }

        $replaced_img_p2 = str_replace($this->oldSrcSetsString, $this->newSrcSetsString, $this->avada_image);
        // error_log("replaced img p2");
        // error_log($replaced_img_p2);


        $replaced_img_p1 = str_replace($this->oldSrc,$this->newSrc, $replaced_img_p2);
        // error_log("replaced image: $replaced_img_p1");
        //     error_log("old src: $this->oldSrc");
        // error_log("new src: $this->newSrc");
        
        
        
        
        $output = apply_filters("peak2_image_proxy_avada_html", $replaced_img_p1, $this->avada_image);
        // error_log("output");
        // error_log($output);
        return $output;
    }

    public function upgrade_avada_image($html, $args) {
        $instance = new Peak2_Image_Proxy_Avada("peak2 labs image proxy", "1.0.0");

        $instance->set_image($html);

        return $instance->as_html();
    }
 }
 
 class Peak2_Image_Proxy_Options 
 {
 
     protected $width;
     protected $format;
     protected $quality;
     protected $height;
     
     public function __construct($quality = 80, $format = ".jpeg", $width = false, $height = false) {
         $this->width = $width;
         $this->format = str_replace(".", "", $format);
         $this->quality = $quality;
         $this->height = $height;
     }

     public function get_format() {
        return $this->format;
     }

     public function set_width($payload) {
        $this->width = $payload;
     }

     public function set_heigth($payload) {
        $this->heigth = $payload;
     }
 
     /**
      * retuns the image options as urls params
      *
      * @return string
      */
     public function as_string() {
         $str = "";
         $str .= "&f={$this->get_format()}&q={$this->quality}";
         if ($this->height) {
             $str .= "&h={$this->height}";
         }
         if ($this->width) {
            $str .= "&w={$this->width}";
         }
         return $str;
     }

 }

// add_filter( 'fusion_element_image_content', 'peak2_avada_test', 999, 2);
// function peak2_avada_test($html, $args) {
//     // return $html;
//     error_log("avada image hook");
//     $peak2_image = new Peak2_Image_Proxy_Avada($html);
//     $output = $peak2_image->as_html();

//     $wp_instance_ip = "http://kunststoffplattenprofisde-go_image_proxy-1";
//     $val = str_replace(site_url(), $wp_instance_ip, $output);
//     // error_log("val:");
//     // error_log($val);
//     return $html;
// }

// add_action('admin_init', 'foo_test');
// add_action( 'admin_menu', 'foo_test' );

// function foo_test() {
//     error_log("settings options included");

// }
// cronjob for testing health of image proxy regularly
// settings for site url of image proxy
// safety token 