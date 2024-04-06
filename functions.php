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


function media_file_already_exists($filename){
    global $wpdb;
    $query = "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE '%/$filename'";
    return ($wpdb->get_var($query)  > 0) ;
}


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

	error_log( var_export(get_post($post_id), true), false );
}

add_action('save_post_design', 'my_save_meta_function', 5);