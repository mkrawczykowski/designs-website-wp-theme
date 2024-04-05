<?php

/**

 * Description of what this module (or file) is doing.

 *

 * @package file

 */

add_theme_support( 'post-thumbnails');

if ( function_exists( 'acf_add_options_page' ) ) {



	acf_add_options_page(

		array(

			'page_title' => 'SEO settings',

			'menu_title' => 'SEO settings',

			'menu_slug'  => 'acf-settings',

			'capability' => 'edit_posts',

			'redirect'   => false,

			'show_in_graphql' => true,
			'show_in_rest' => true

		)

	);



}





add_filter( 'acf/settings/save_json', 'my_acf_json_save_point' );



/**

 *

 * Makes ACF saving fields to JSON files

 */

function my_acf_json_save_point( $path ) {



	$path = get_stylesheet_directory() . '/acf-json';



	return $path;

}


/**

 *

 * Disabling Gutenberg

 */

// add_filter( 'use_block_editor_for_post', '__return_false', 100 );


// The object type. For custom post types, this is 'post';
// for custom comment types, this is 'comment'. For user meta,
// this is 'user'.
$object_type = 'post';
$meta_args = array( // Validate and sanitize the meta value.
    // Note: currently (4.7) one of 'string', 'boolean', 'integer',
    // 'number' must be used as 'type'. The default is 'string'.
    'type'         => 'number',
    // Shown in the schema for the meta key.
    'description'  => 'A meta key associated with a string meta value.',
    // Return a single value of the type.
    'single'       => true,
    // Show in the WP REST API response. Default: false.
    'show_in_rest' => true,
);
register_meta( $object_type, '_yoast_wpseo_primary_category', $meta_args );

function add_to_media_lib($file_url, $file_path)
{
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');

// Check the type of tile. We'll use this as the 'post_mime_type'.
$file_type = wp_check_filetype(basename($file_url), null);

// Get the path to the upload directory.
$wp_upload_dir = wp_upload_dir();

// Prepare an array of post data for the attachment.
$attachment = array(
    'guid' => $wp_upload_dir['url'] . '/' . basename($file_url),
    'post_mime_type' => $file_type['type'],
    'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_url)),
    'post_content' => '',
    'post_status' => 'inherit',
);

// Insert the attachment.
$attach_id = wp_insert_attachment($attachment, $file_url);

// Generate the metadata for the attachment, and update the database record.
if ($attach_data = wp_generate_attachment_metadata($attach_id, $file_path)) {
    wp_update_attachment_metadata($attach_id, $attach_data);
}

return $attach_id;
}


function my_save_meta_function($post_id){
	// update_field('test', get_the_post_thumbnail_url($post_id), $post_id); // works
	$thumbnail_URL = get_the_post_thumbnail_url($post_id);

	$thumbnail_background_ID = get_field('thumbnail_background', 'options');
	$thumbnail_background_URL = wp_get_attachment_url($thumbnail_background_ID);
	// update_field('test', $thumbnail_background_URL, $post_id); // works
	

	// $im1 = imagecreatefromjpeg($thumbnail_background_URL);
	// $im2 = imagecreatefromjpeg($thumbnail_URL);

	// $result = imagecopyresampled($im1,$im2,250,150,0,0,50,50,50,50);
	// unset($im2);


// Utwórz obrazy z plików JPEG
$im1 = imagecreatefromjpeg($thumbnail_background_URL);
$im2 = imagecreatefromjpeg($thumbnail_URL);

// Pobierz wymiary obu obrazów
$im1_width = imagesx($im1);
$im1_height = imagesy($im1);
$im2_width = imagesx($im2);
$im2_height = imagesy($im2);

// Utwórz nowy obraz, który będzie łącznikiem dla obu obrazów
$new_width = $im1_width; // Możesz zmienić szerokość obrazu łącznika
$new_height = $im1_height + $im2_height; // Nowa wysokość to suma wysokości obu obrazów
$new_im = imagecreatetruecolor($new_width, $new_height);

// Skopiuj pierwszy obraz na nowy obraz
imagecopy($new_im, $im1, 0, 0, 0, 0, $im1_width, $im1_height);

// Skopiuj drugi obraz na nowy obraz
imagecopy($new_im, $im2, 0, $im1_height, 0, 0, $im2_width, $im2_height);

// Zapisz nowy obraz do pliku lub wyświetl go na ekranie
if (imagejpeg($new_im, 'wp-content/uploads/2024/04/temp-1d3e.jpg')){
	imagedestroy($im1);
	imagedestroy($im2);
	imagedestroy($new_im);

	add_to_media_lib('http://localhost/wp-content/uploads/2024/04/temp-1d3e.jpg', '/wp-content/uploads/2024/04/temp-1d3e.jpg');

}

// Zwolnij pamięć używaną przez obrazy


// if ( ! function_exists( 'wp_crop_image' ) ) {
// 	include( ABSPATH . 'wp-admin/includes/image.php' );
//   }

// $file_name = 'sss';
// $file_path = '/wp-content/uploads/2024/04/temp-11.jpg';
// $file_url = 'http://localhost/wp-content/uploads/2024/04/temp-11.jpg';
// $wp_filetype = wp_check_filetype($file_name, null);
// $attachment = array(
//     'guid'           => $file_url,
//     'post_mime_type' => $wp_filetype['type'],
//     'post_title'     => $file_name,
//     'post_status'    => 'inherit',
//     'post_date'      => date('Y-m-d H:i:s')
// );
// $attachment_id = wp_insert_attachment($attachment, $file_path);
// $attachment_post = get_post($attachment_id);
// $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
// wp_update_attachment_metadata($attachment_post->ID, $attachment_data);













	// update_field('test', '1q', $post_id);
}

add_action('wp_after_insert_post', 'my_save_meta_function');
