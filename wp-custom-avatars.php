<?php
/*
Plugin Name: Custom Avatars
Plugin URI: http://www.ielectrify.com/resources/bloggingtips/custom-wordpress-avatars/
Description: Give your WordPress blog custom avatars for users if they're not already using Gravatar. Created by <a href="https://www.ielectrify.com">iElectrify</a> and <a href="https://www.fahdmurtaza.com">Fahd Murtaza</a>
Author: Sherice Jacob & Fahd Murtaza
Author URI: http://www.ielectrify.com
Version: 1.1
*/

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}
if ( ! defined( 'WP_PLUGIN_URL' ) ) {
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
}
if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}

function validate_gravatar( $email ) {
	$uri     = 'https://www.gravatar.com/avatar.php?gravatar_id=' . md5( $email ) . '?&default=identicon&r=any&size=80';
	$headers = wp_remote_head( $uri );

	// Check the headers
	if ( is_wp_error( $headers ) || ! isset( $headers['response'] ) || $headers['response']['code'] !== 200 ) {
		return false;
	}

	return true;
}

function get_images_from_dir() {
	$dir_path  = plugin_dir_path( __FILE__ ) . 'images/';
	$dir_array = array();
	if ( $handle = opendir( $dir_path ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( $file != "." && $file != ".." ) {
				$dir_array[] = $file;
			}
		}
		closedir( $handle );
	}

	return $dir_array[ array_rand( $dir_array ) ];
}

function parse_attributes( $input ) {
	$attr = simplexml_load_string( $input );
	foreach ( $attr->attributes() as $a => $b ) {
		if ( $a == "width" ) {
			return $b;
		}
	}

	return '';
}

function wavatar_comment_author( $args ) {
	global $comment;

	if ( ! validate_gravatar( $comment->comment_author_email ) ) {
		$attr = parse_attributes( $args );

		$image_url = WP_PLUGIN_URL . '/wordpress-custom-avatars-plugin/images/' . get_images_from_dir();
		$img_html  = "<img class='wavatar' src='{$image_url}' width='{$attr}' height='{$attr}' alt='Wavatar' />";

		return $img_html;
	} else {
		return $args;
	}
}

add_filter( 'get_avatar', 'wavatar_comment_author', 0 );
