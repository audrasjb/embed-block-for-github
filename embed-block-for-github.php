<?php
/**
 * @link              https://jeanbaptisteaudras.com
 * @since             0.1
 * @package           Embed Block for GitHub
 *
 * Plugin Name:       Embed Block for GitHub
 * Plugin URI:        https://jeanbaptisteaudras.com/embed-block-for-github-gutenberg-wordpress/
 * Description:       Easily embed GitHub repositories in Gutenberg Editor.
 * Version:           0.3
 * Author:            audrasjb
 * Author URI:        https://jeanbaptisteaudras.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       embed-block-for-github
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class embed_block_for_github {

	private function msgdebug ($msg) {
		//$this->msgdebug("PAHT:".plugin_dir_path( __FILE__ ));
		error_log("DEBUG: ".$msg, 0);
	}

	public function __construct() {
		add_action( 'init', array( $this, 'init_wp_register' ) );
	}

	public function init_wp_register() {
		wp_register_script(
			'ebg-repository-editor',
			$this->plugin_url('repository-block.js'),
			array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-editor' ),
			$this->plugin_file_ver('repository-block.js')
		);
		wp_register_style(
			'ebg-repository-editor',
			$this->plugin_url('repository-block-editor.css'),
			array(),
			$this->plugin_file_ver('repository-block.css')
		);
		wp_register_style(
			'ebg-repository',
			$this->plugin_url('repository-block.css'),
			array(),
			$this->plugin_file_ver('repository-block.css')
		);
		register_block_type( 'embed-block-for-github/repository', array(
			'editor_script'   => 'ebg-repository-editor',
			'editor_style'    => 'ebg-repository-editor',
			'style'           => 'ebg-repository',
			'render_callback' => array( $this, 'ebg_embed_repository' ),
			'attributes'      => array(
				'github_url' => array( 'type' => 'string' ),
				'darck_mode' => array( 'type' => 'boolean' ),
			),
		) );
	}

	private function process_template( $template, $data ) {
		ob_start();
		if ( ! locate_template( $this->plugin_name() . '/' . $template, true ) ) {
			require 'templates/' . $template;
		}
		return ob_get_clean();
	}

	/* Get Path install plugin */
	private function plugin_path(){
		return plugin_dir_path( __FILE__ );
	}

	/* Get Path install plugin and file name. */
	private function plugin_file($file){
		if (strlen(trim($file)) > 0) {
			return $this->plugin_path() . $file;
		}
		return "";
	}

	/* Get version of the file using modified date. */
	private function plugin_file_ver($file) {
		return filemtime( $this->plugin_file($file) );
	}

	/* Get folder name plugin */
	private function plugin_name() {
		return basename( dirname( __FILE__ ) );
	}

	private function plugin_url($file) {
		if (strlen(trim($file)) > 0) {
			return plugins_url( $file, __FILE__ );
		}
		return "";
	}

	private function check_message($message) {
		if ($message == "Not Found") {
			return '<p>' . esc_html__( 'Error: Reposity not found. Please check your URL.', 'embed-block-for-github' ) . '</p>';
		} else {
			return '<p>' . esc_html( sprintf( 'Error: %s', $message ) , 'embed-block-for-github' ) . '</p>';
		}
	}

	public function ebg_embed_repository( $attributes ) {
		$github_url = trim( $attributes['github_url'] );
		$darck_mode = (in_array("darck_mode", $attributes) ? $attributes['darck_mode'] : false);
		
		$a_remplace = [];
		$a_remplace['%%_WRAPPER_DARK_MODE_%%'] = "ebg-br-wrapper-dark-mode-" . ($darck_mode ? "on" : "off");
		
		if ( '' === trim( $github_url ) ) {
			$content = '<p>' . esc_html__( 'Use the Sidebar to add the URL of the GitHub Repository to embed.', 'embed-block-for-github' ) . '</p>';
		} else {
	
			if ( filter_var( $github_url, FILTER_VALIDATE_URL ) ) {
				if ( strpos( $github_url, 'https://github.com/' ) === 0 ) {
					if ( get_transient( '_ebg_repository_' . sanitize_title_with_dashes( $github_url ) ) ) 
					{
						$data = json_decode( get_transient( '_ebg_repository_' . sanitize_title_with_dashes( $github_url ) ) );
						if (isset( $data->message ) ) 
						{
							$content = $this->check_message($data->message);
						}
						else {
							$content = $this->process_template('repository.php', $data);
							$a_remplace['%%_DATA_AVATAR_URL_%%'] = $data->owner->avatar_url;
							$a_remplace['%%_DATA_REPO_URL_%%'] = $data->html_url;
							$a_remplace['%%_DATA_REPO_NAME_%%'] = $data->name;
							$a_remplace['%%_DATA_AUTOR_URL_%%'] = $data->owner->html_url;
							$a_remplace['%%_DATA_AUTOR_NAME_%%'] = $data->owner->login;
							$a_remplace['%%_DATA_DESCIPTION_%%'] = $data->description;
						}
						unset($data);
					} 
					else {
						$slug = str_replace( 'https://github.com/', '', $github_url );
						$request = wp_remote_get( 'https://api.github.com/repos/' . $slug );

						$body = wp_remote_retrieve_body( $request );
						$data = json_decode( $body );
						if ( ! is_wp_error( $response ) ) {
							set_transient( '_ebg_repository_' . sanitize_title_with_dashes( $github_url ), json_encode( $data ) );
							if (isset( $data->message ) ) 
							{
								$content = $this->check_message($data->message);
							} 
							else {
								$content = $this->process_template('repository.php', $data);
								
								$a_remplace['%%_DATA_AVATAR_URL_%%'] = $data->owner->avatar_url;
								$a_remplace['%%_DATA_REPO_URL_%%'] = $data->html_url;
								$a_remplace['%%_DATA_REPO_NAME_%%'] = $data->name;
								$a_remplace['%%_DATA_AUTOR_URL_%%'] = $data->owner->html_url;
								$a_remplace['%%_DATA_AUTOR_NAME_%%'] = $data->owner->login;
								$a_remplace['%%_DATA_DESCIPTION_%%'] = $data->description;
							} 
						} else {
							$content = '<p>' . esc_html__( 'No information available. Please check your URL.', 'embed-block-for-github' ) . '</p>';
						}
						unset($data);
					}
				} else {
					$content = '<p>' . esc_html__( 'Use the Sidebar to add the URL of the GitHub Repository to embed.', 'embed-block-for-github' ) . '</p>';
				}
			} else {
				$content = '<p>' . esc_html__( 'Use the Sidebar to add the URL of the GitHub Repository to embed.', 'embed-block-for-github' ) . '</p>';
			}
		}

		foreach ($a_remplace as $key => $val) {
			$content = str_replace($key, $val, $content);
		}
		return $content;
	}

}

$embed_block_for_github = new embed_block_for_github();