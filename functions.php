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



$object_type = 'post';
$meta_args = array(
    'type'         => 'number',
    'description'  => 'A meta key associated with a string meta value.',
    'single'       => true,
    'show_in_rest' => true,
);
register_meta( $object_type, '_yoast_wpseo_primary_category', $meta_args );




function add_to_media_lib($file_url, $file_path) {
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');

	$file_type = wp_check_filetype(basename($file_url), null);

	$wp_upload_dir = wp_upload_dir();

	$attachment = array(
		'guid' => $wp_upload_dir['url'] . '/' . basename($file_url),
		'post_mime_type' => $file_type['type'],
		'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_url)),
		'post_content' => '',
		'post_status' => 'inherit',
	);

	$attach_id = wp_insert_attachment($attachment, $file_url);

	if ($attach_data = wp_generate_attachment_metadata($attach_id, $file_path)) {
		wp_update_attachment_metadata($attach_id, $attach_data);
	}

	return $attach_id;
	}

function my_save_meta_function($post_id){
	$thumbnail_URL = get_the_post_thumbnail_url($post_id);
	$thumbnail_background_ID = get_field('thumbnail_background', 'options');
	$thumbnail_background_URL = wp_get_attachment_url($thumbnail_background_ID);

	$im1 = imagecreatefromjpeg($thumbnail_background_URL);
	$im2 = imagecreatefromjpeg($thumbnail_URL);

	$im1_width = imagesx($im1);
	$im1_height = imagesy($im1);
	$im2_width = imagesx($im2);
	$im2_height = imagesy($im2);

	$new_width = $im1_width;
	$new_height = $im1_height + $im2_height;
	$new_im = imagecreatetruecolor($new_width, $new_height);

	imagecopy($new_im, $im1, 0, 0, 0, 0, $im1_width, $im1_height);

	imagecopy($new_im, $im2, 0, $im1_height, 0, 0, $im2_width, $im2_height);

	$upload_dir = './wp-content/uploads/2024/04/';
	if (!is_dir($upload_dir)) {
		mkdir($upload_dir, 0755, true);
	}

	if (is_dir($upload_dir) && is_writable($upload_dir)) {
		if (imagejpeg($new_im, $upload_dir . 'temp-1-2.jpg')) {
			imagedestroy($im1);
			imagedestroy($im2);
			imagedestroy($new_im);
			update_field('test', 'ok', $post_id);

			add_to_media_lib('http://localhost/wp-content/uploads/2024/04/temp-1-2.jpg', 'wp-content/uploads/2024/04/temp-1-2.jpg');
		} else {
			update_field('test', 'nok', $post_id);
		}
	} else {
		update_field('test', 'dir nok', $post_id);
	}
}

add_action('save_post_design', 'my_save_meta_function');
