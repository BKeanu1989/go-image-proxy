# Resources 
- WP MVC https://iandunn.name/content/presentations/wp-oop-mvc/mvc.php#/6
https://behind-the-scenes.net/using-wps-the_content-function-and-filter-hook/
https://wordpress.stackexchange.com/questions/142957/use-the-content-outside-the-loop


---
```php
<?php
/**
 * Display the post content. Optinally allows post ID to be passed
 * @uses the_content()
 * @link http://stephenharris.info/get-post-content-by-id/
 * @link https://wordpress.stackexchange.com/questions/142957/use-the-content-outside-the-loop
 * @param int $id Optional. Post ID.
 * @param string $more_link_text Optional. Content for when there is more text.
 * @param bool $stripteaser Optional. Strip teaser content before the more text. Default is false.
 */
function sh_the_content_by_id( $post_id=0, $more_link_text = null, $stripteaser = false ){
    global $post;
    $post = get_post($post_id);
    setup_postdata( $post, $more_link_text, $stripteaser );
    the_content();
    wp_reset_postdata( $post );
}

```

we might just "upload" the created files into media -> creating them & using php to upload. (save into db) 

---
// read

http://localhost:3333/wp-content/uploads

```php
function wporg_callback() {
  global $post;
  var_dump($post);


}
add_action( 'wp_head', 'wporg_callback' );
```
