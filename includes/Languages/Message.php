<?php
/**
 * 
 * Author:            VSC55
 * Author URI:        https://github.com/vsc55/embed-block-for-github
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 */

namespace EmbedBlockForGithub\Lang;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Message {

    public static function getMessage($message, $arg = array()) {
        $msg_return = "";
		switch($message) {
			case "url_is_null":
				$msg_return = '<p>' . esc_html__( 'Use the Sidebar to add the URL of the GitHub Repository to embed.', 'embed-block-for-github' ) . '</p>';
			break;

			case "url_not_valid":
				$msg_return = '<p>' . esc_html__( 'The specified URL is not valid. Check the address using the sidebar to add the repository URL.', 'embed-block-for-github' ) . '</p>';				
			break;

			case "url_not_github":
				$msg_return = '<p>' . esc_html__( 'The specified URL is not from GitHub. Check the address using the sidebar to add the correct GitHub repository URL (only https allowed).', 'embed-block-for-github' ) . '</p>';
			break;

			case "info_no_available":
				$msg_return = '<p>' . esc_html__( 'No information available. Please check your URL.', 'embed-block-for-github' ) . '</p>';
			break;

			case "error_cache_data":
				$msg_return = '<p>' . esc_html__( 'Error detected in cache data. Refresh the page to load the correct data.', 'embed-block-for-github' ) . '</p>';
				break;

			case "error_data_is_null":
				$msg_return = '<p>' . esc_html__( 'No data detected. Please check your URL.', 'embed-block-for-github' ) . '</p>';
				break;
		}
		return $msg_return;
	}
	
}