<?php defined('ABSPATH') or die; ?>

<?php
require_once get_template_directory() . '/inc/actions.php';
require_once get_template_directory() . '/inc/registers.php';
//   require_once get_template_directory() . '/inc/styles-scripts.php';
//   require_once get_template_directory() . '/inc/acf-functions.php';
//   require_once get_template_directory() . '/inc/acf-blocks.php';
//   require_once get_template_directory() . '/inc/menus.php';
//   require_once get_template_directory() . '/inc/helpers.php';
//   require_once get_template_directory() . '/inc/shortcodes.php';











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
	if (!get_field('generate_featured_image', $post_id, true, true)){
		return;
	}
	$image_random_file_name = get_field('generated_featured_image_random_name', $post_id);
	update_field('field_660ecfc33f30d', $image_random_file_name, $post_id);
	$image_seo_file_name = get_field('generated_featured_image_name', $post_id);
	$seo_friendly_name = get_field('seo_friendly_name', $post_id, true, true);
	$current_file_name = '';

	if ($seo_friendly_name){
		if ($image_seo_file_name){
			$current_file_name = $image_seo_file_name;
		} else {
			return;
		}
	}

	if (!$seo_friendly_name){
		if ($image_random_file_name){
			$current_file_name = $image_random_file_name;
		} else {
			return;
		}
	}

	$current_file_name .= '.jpg';
	

	if (media_file_already_exists($current_file_name)){
		return;
	}

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

	$upload_dir = './wp-content/uploads/' . date('Y') . '/' . date('m') . '/';
	if (!is_dir($upload_dir)) {
		mkdir($upload_dir, 0755, true);
	}
	update_field('field_660ecfc33f30d', 'ok!', $post_id);

	// error_log( 'Saved post ID! : ' . $post_id, false);
	
	if (is_dir($upload_dir) && is_writable($upload_dir)) {
		if (imagejpeg($new_im, $upload_dir . $current_file_name)) {
			imagedestroy($im1);
			imagedestroy($im2);
			imagedestroy($new_im);
			error_log('after imagedestroys');

			$generated_image_file = 'http://localhost/wp-content/uploads/' . date('Y') . '/' . date('m') . '/' . $current_file_name;

			add_to_media_lib($generated_image_file, 'wp-content/uploads/' . date('Y') . '/' . date('m') . '/' . $current_file_name);
			
			if ($seo_friendly_name){
				$testing = update_field('field_66105da321278', $generated_image_file, $post_id); //seo_friendly_name_url
				// apply_filters('acf/update_value', $generated_image_file, $post_id,  'field_66105da321278');

				error_log('update_field field_66105da321278 ' . $testing );
			}
			if (!$seo_friendly_name){
				update_field('field_66105de121279', $generated_image_file, $post_id); //non_seo_friendly_name_url
			}
			
		} else {
			update_field('field_660ecfc33f30d', 'nok', $post_id);
		}
	} else {
		update_field('field_660ecfc33f30d', 'dir nok', $post_id);
	}

	error_log( var_export(get_post($post_id), true), false );
}


