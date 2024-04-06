<?php defined('ABSPATH') or die; ?>

<?php
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
 * Just tests
 */

// add_action('save_post_design', 'my_save_meta_function', 11);
// function update_post_acf(){
// 	error_log('update post acf 2' );
// }
// function update_post(){
// 	error_log('update post 2' );
// }

// add_action( 'save_post_design' , 'update_post'); //fires first
// add_action( 'acf/save_post' , 'update_post_acf'); //fires second