<?php
/**
 * Plugin Name: Custom Avatars
 * Plugin URI: https://www.isupercoder.com/wp-custom-avatars
 * Description: Give your WordPress blog custom avatars for users if they're not already using Gravatar. Created by <a href="https://www.ielectrify.com">iElectrify</a> and <a href="https://www.fahdmurtaza.com">Fahd Murtaza</a>
 * Author: Sherice Jacob & Fahad Murtaza
 * Author URI: http://www.ielectrify.com
 * Version: 1.2.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Pre-2.6 compatibility
 */
if (!defined('WP_CONTENT_URL')) {
	define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
}
if (!defined('WP_CONTENT_DIR')) {
	define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}
if (!defined('WP_PLUGIN_URL')) {
	define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
}
if (!defined('WP_PLUGIN_DIR')) {
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

/**
 * Validate Gravatar
 *
 * @param string $wpca_email User email.
 *
 * @return bool Whether the Gravatar is valid or not.
 */
function wpca_validate_gravatar($wpca_email)
{
	$wpca_uri = 'https://www.gravatar.com/avatar.php?gravatar_id=' . md5($wpca_email) . '?&default=identicon&r=any&size=80';
	$wpca_headers = wp_remote_head($wpca_uri);

	if (is_wp_error($wpca_headers) || !isset($wpca_headers['response']) || $wpca_headers['response']['code'] !== 200) {
		return false;
	}

	return true;
}

/**
 * Get Images from Directory
 *
 * @return string Randomly selected image from the directory.
 */
function wpca_get_images_from_dir()
{
	$wpca_dir_path = plugin_dir_path(__FILE__) . 'images/';
	$wpca_dir_array = [];

	if ($wpca_handle = opendir($wpca_dir_path)) {
		while (false !== ($wpca_file = readdir($wpca_handle))) {
			if ($wpca_file != "." && $wpca_file != "..") {
				$wpca_dir_array[] = $wpca_file;
			}
		}
		closedir($wpca_handle);
	}

	return $wpca_dir_array[array_rand($wpca_dir_array)];
}

/**
 * Parse Attributes
 *
 * @param string $wpca_input Attributes input.
 *
 * @return string Attribute value.
 */
function wpca_parse_attributes($wpca_input)
{
	$wpca_attr = simplexml_load_string($wpca_input);
	foreach ($wpca_attr->attributes() as $wpca_a => $wpca_b) {
		if ($wpca_a == "width") {
			return $wpca_b;
		}
	}
	return '';
}

/**
 * Modify Comment Author Avatar
 *
 * @param string $wpca_args Avatar arguments.
 *
 * @return string Modified avatar HTML.
 */
function wpca_wavatar_comment_author($wpca_args)
{
	global $comment;

	if (!wpca_validate_gravatar($comment->comment_author_email)) {
		$wpca_attr = wpca_parse_attributes($wpca_args);

		$wpca_image_url = WP_PLUGIN_URL . '/custom-avatars-plugin/images/' . wpca_get_images_from_dir();
		$wpca_img_html = "<img class='wavatar' src='{$wpca_image_url}' width='{$wpca_attr}' height='{$wpca_attr}' alt='Favatar' />";
		return $wpca_img_html;
	} else {
		return $wpca_args;
	}
}

add_filter('get_avatar', 'wpca_wavatar_comment_author', 10, 3);
