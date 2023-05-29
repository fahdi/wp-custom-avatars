<?php
/*
Plugin Name: Custom Avatars
Plugin URI: http://www.ielectrify.com/resources/bloggingtips/custom-wordpress-avatars/
Description: Give your Wordpress blog custom avatars for users if they're not already using Gravatar.  Created by <a href="http://www.ielectrify.com">iElectrify</a> and <a href="http://www.fahdmurtaza.com">Fahd Murtaza</a>
Author: Sherice Jacob & Fahd Murtaza
Author URI: http://www.ielectrify.com
Version: 1.1
*/
// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
?>
<?php
function validate_gravatar($email) {
	$uri = 'http://www.gravatar.com/avatar.php?gravatar_id=' . md5($email) . '?&default=identicon&r=any&size=80';
	$headers = wp_get_http_headers($uri);

	// Check the headers
	if (!is_array($headers)) :
		$has_valid_avatar = FALSE;
	elseif (isset($headers["content-disposition"])) :
		$has_valid_avatar = TRUE;
	else :
		$has_valid_avatar = FALSE;
	endif;

	return $has_valid_avatar;
}

function getImagesFromDir() {
//if ($handle = opendir(dirname(__FILE__).'\\images\\')) {
	if ($handle = opendir(dirname(__FILE__).'/images/')) {
		$dir_array = array();
		while (false !== ($file = readdir($handle))) {
			if($file!="." && $file!=".."){
				$dir_array[] = $file;
			}
		}
		closedir($handle);
		return $dir_array[rand(0,count($dir_array)-1)];
	}
}

function parse_attributes($input) {
	$attr = simplexml_load_string($input);
	foreach($attr->attributes() as $a => $b) {
		if($a == "width") {
			return $b;
		}
	}
}


function wavatar_comment_author ($args)
{
	global $comment;

	if(!validate_gravatar($comment->comment_author_email)) {
		$attr = parse_attributes($args);

		return "<img class='wavatar' src='".WP_PLUGIN_URL."/wordpress-custom-avatars-plugin/images/".getImagesFromDir()."' width='".$attr."' height='".$attr."' alt='Wavatar' />";
	} else {
		return $args;
	}
}

add_filter('get_avatar','wavatar_comment_author', 0);
?>
