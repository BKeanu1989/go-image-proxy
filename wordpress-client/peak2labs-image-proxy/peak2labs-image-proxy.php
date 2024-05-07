<?php
/*
 * Plugin Name: Peak 2 Labs Image Proxy
 */

 //     $str = '<div class="imageframe-align-center"><span class=" fusion-imageframe imageframe-dropshadow imageframe-39 hover-type-none" style="-webkit-box-shadow: 0px 0px 4px rgba(0,0,0,0.3);box-shadow: 0px 0px 4px rgba(0,0,0,0.3);"><img width="800" height="400" alt="Trespa" title="ka4463" src="http://localhost:3333/wp-content/uploads/2023/03/ka4463.png" class="img-responsive wp-image-35251" srcset="http://localhost:3333/wp-content/uploads/2023/03/ka4463-200x100.png 200w, http://localhost:3333/wp-content/uploads/2023/03/ka4463-400x200.png 400w, http://localhost:3333/wp-content/uploads/2023/03/ka4463-600x300.png 600w, http://localhost:3333/wp-content/uploads/2023/03/ka4463.png 800w" sizes="(max-width: 800px) 100vw, 600px" /></span></div>';

 class Peak2_Image_Proxy_Avada 
 {
     const IMAGE_PROXY_URL_PREFIX = "http://localhost:8080/image/?url=";
     protected $url;
     protected $defaultQuality = 80;
     protected $defaultImageFormat = ".jpeg"; // for image conversion & jpeg for quality adaption as default
     protected $data = [];
     protected $avada_image = false;
     protected $imageBase = false;
     protected $oldSrcSetsString = false;
     protected $oldSrc = false;
     protected $oldImgFormat = false;

     public $newSrcSetsString = false; 
     public $newSrc = false;

     public function __construct($avada_image) {
         try {
            //code...
            error_log("we are here right?");
            $this->avada_image = $avada_image;
    
           $this->parse_image_src($this->avada_image);
           $this->parse_srcsets($this->avada_image);
   
           $this->set_new_src();
           $this->set_new_srcsets();
         } catch (\Throwable $th) {
            //throw $th;
            error_log($th->get_message());
            return $avada_image;
         }
     }
 
     public static function block_any_work($avada_image) {
        // search for strings like deactivate-peak2, for example a class
         return false;
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
         error_log("img src result");
         error_log(print_r($matches,1));
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
        error_log(print_r($_SERVER, 1));
        $str = "";
        
        $opts = new Peak2_Image_Proxy_Options();
        $str .= self::IMAGE_PROXY_URL_PREFIX . $this->imageBase . $this->oldImgFormat . $opts->as_string();
        // return $str;
        $this->newSrc = $str;
     }
 
     protected function set_new_srcsets() {
        $str = "";

        $count = count($this->data);

        foreach ($this->data as $key => $x) {
            error_log(print_r($x, 1));
            $defaultOpts = new Peak2_Image_Proxy_Options(80, ".jpeg",$x["width"]);
            $str .= self::IMAGE_PROXY_URL_PREFIX . $x["url"] . $defaultOpts->as_string() . " " . $x["width"] . "w";             
            error_log("string now: $str");
            if ($key !== $count) {
                $str .= ", ";
            }
        }
        error_log("new srcset: ");
        error_log($str);
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
        error_log("output");
        error_log($output);
        return $output;
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

 add_filter( 'fusion_element_image_content', 'peak2_avada_test', 999, 2);
function peak2_avada_test($html, $args) {
    // return $html;
    $peak2_image = new Peak2_Image_Proxy_Avada($html);
    return $peak2_image->as_html();
}

// test
// von go image proxy