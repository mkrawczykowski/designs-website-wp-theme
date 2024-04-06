<?php defined('ABSPATH') or die; ?>

<?php
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