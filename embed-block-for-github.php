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

namespace EmbedBlockForGithub;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//DIRECTORY_SEPARATOR



require_once ( __DIR__ . '/includes/Plugin/PluginBase.php' );
require_once ( __DIR__ . '/includes/Cache/Transient.php' );
require_once ( __DIR__ . '/includes/Languages/Message.php' );
require_once ( __DIR__ . '/includes/GitHub/GitHubAPI.php' );


use EmbedBlockForGithub\Plugin\PluginBase;
use EmbedBlockForGithub\Cache\Transient;
use EmbedBlockForGithub\Lang\Message;
use EmbedBlockForGithub\GitHub\API\GitHubAPI;



class embed_block_for_github extends PluginBase {

	private $dev_mode = false;
	private static $instance;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	protected function __construct() 
	{
		# Plugin base
		parent::__construct( __FILE__);
		
		add_action( 'admin_init', array( $this, 'register_settings' )  );
		add_action( 'init', array( $this, 'init_wp_register' ) );
		add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
	}

	public function init_wp_register() {
		wp_register_script(
			'ebg-repository-editor',
			$this->getURL('admin/js/repository-block.js'),
			array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-editor' ),
			$this->getVersionFile('admin/js/repository-block.js')
		);
		wp_register_style(
			'ebg-repository-editor',
			$this->getURL('admin/css/repository-block-editor.css'),
			array(),
			$this->getVersionFile('admin/css/repository-block-editor.css')
		);
		wp_register_style(
			'ebg-repository',
			$this->getURL('public/css/repository-block.css'),
			array(),
			$this->getVersionFile('public/css/repository-block.css')
		);
		register_block_type( 'embed-block-for-github/repository', array(
			'editor_script'   => 'ebg-repository-editor',
			'editor_style'    => 'ebg-repository-editor',
			'style'           => 'ebg-repository',
			'render_callback' => array( $this, 'ebg_embed_repository' ),
			'attributes'      => array(
				'github_url' => array( 'type' => 'string' ),
				'darck_theme' => array( 'type' => 'boolean' ),
				'icon_type_source' => array( 'type' => 'string' ),
				'api_cache' => array( 'type' => 'boolean' ),
				'api_cache_expire' => array( 'type' => 'string' ),
			),
		) );
	}

	function addAdminMenu() {
		/* 
		https://developer.wordpress.org/reference/functions/add_menu_page/ 
		*/
		add_menu_page( 
			'WordPress Embed Block for GitHub', 
			'Embed Block for GitHub', 
			'manage_options', 
			'embed-block-for-github', 
			array($this, 'pageAdminMenu'), 
			plugins_url( 'embed-block-for-github/icon.png'),
			/*4*/
		);
	}

	function register_settings() {
		/* 
			https://developer.wordpress.org/reference/functions/register_setting/
		 */
		$args = array(
            'type' => 'boolean',
            'default' => true,
            );
		register_setting( 'embed-block-for-github', 'darck_theme', $args );
		$args = array(
            'type' => 'string',
            'default' => true,
            );
		register_setting( 'embed-block-for-github', 'icon_type_source', $args );
		$args = array(
            'type' => 'boolean',
            'default' => true,
            );
		register_setting( 'embed-block-for-github', 'api_cache', $args );
		$args = array(
            'type' => 'string',
            'default' => '0',
            );
    	register_setting( 'embed-block-for-github', 'api_cache_expire', $args );

	}



	/* Message according to the error received from GitHub. */
	private function check_message($message, $documentation_url) {
		if ($message == "Not Found") {
			return '<p>' . esc_html__( 'Repository not found. Please check your URL.', 'embed-block-for-github' ) . '</p>';
		}
		elseif ( strpos( $message, 'API rate limit exceeded for ' ) === 0 )
		{
			return '<p>' . esc_html__( 'Sorry, API Github rate limit exceeded for IP. Please try again later.', 'embed-block-for-github' ) . '</p>';
		}
		else 
		{
			return '<p>' . esc_html( sprintf( 'Error: %s', $message ) , 'embed-block-for-github' ) . '</p>';
		}
	}


	/* Check if the URL is correct */
	private function check_github_url($github_url) {
		switch(True) {
			case ( '' === trim( $github_url ) ):
				return "url_is_null";
			break;

			case (! filter_var( $github_url, FILTER_VALIDATE_URL ) ):
				return "url_not_valid";
			break;
			
			case ( strpos( $github_url, 'https://github.com/' ) !== 0 ):
				return "url_not_github";
			break;
		}
		return NULL;
	}

	/* Detect type request (user, repo, etc...) */
	private function detect_request($github_url) {
		$slug = str_replace( 'https://github.com/', '', $github_url );
		
		$data_return = (object)array();
		switch ( count(explode("/", $slug)) )
		{
			case 1:
				/* User */
				$data_return->request = wp_remote_get( 'https://api.github.com/users/' . explode("/", $slug)[0] );
				$data_return->type = "user";
				break;

			case 2:
				/* Repo */
				$data_return->request = wp_remote_get( 'https://api.github.com/repos/' . $slug );
				$data_return->type = "repo";
				break;

			default:
				/* ??? */
				$data_return->type = NULL;
		}
		return $data_return;
	}
	
	/* Get data api github */
	private function github_api_get($github_url) {
		$error = NULL;

		/* We check and validate the propiedases are good. */
		$error = $this::check_github_url($github_url);

		if (is_null($error)) {
			$data = (object)array();
			$data = $this::detect_request($github_url);
			if (! is_null( $data->type ) )
			{
				$body = wp_remote_retrieve_body( $data->request );
				$data->data = json_decode( $body );
				if (is_wp_error( $data->response ) ) {
					$error = "info_no_available";
					//TODO: Pendiente mirar $response
					//$response->get_error_message()
				}
				unset($body);
			} else 
			{
				$error = "url_not_valid";
			}
		}
		return array($error, $data);
	}

	public function ebg_embed_repository( $attributes ) {
		/* get attributes value and if value is empty set default value */
		$github_url = trim( $attributes['github_url'] );
		$darck_theme = (in_array("darck_theme", $attributes) ? $attributes['darck_theme'] : false);
		$icon_type_source = (! empty($attributes['icon_type_source']) ? $attributes['icon_type_source'] : "file_svg");
		$api_cache = (in_array("api_cache", $attributes) ? $attributes['api_cache'] : true);
		$api_cache_expire = (! empty($attributes['api_cache_expire']) ? $attributes['api_cache_expire'] : 0);
		
		$cache = Transient::get_instance($this);
		$cache->setId("", sanitize_title_with_dashes( $github_url ));

		
		/* DEV: CLEAN TRANSIENT */
		if ($this->dev_mode) { $cache->delete(true); }
		/* DEV: CLEAN TRANSIENT */

		if (! $api_cache) {
			$cache->delete(true);
		}
		if ( ! $cache->isExist() )
		{
			list($error['type'], $data_all) = $this->github_api_get($github_url);
			if (empty($error['type'])) {
				if ($api_cache) {
					$cache->set($data_all, $api_cache_expire);
				}
			}
		}
		if (empty($error['type'])) 
		{
			if ( empty($data_all) )
			{
				$data_all = $cache->get();
			}

			if (isset($data_all->data)) 
			{
				/* We check if any error has been received from github. */
				if (isset( $data_all->data->message ) )
				{
					$error['type'] = "get_error_from_github";
					$error['msg_custom'] =  $this::check_message($data_all->data->message, $data_all->data->documentation_url);
				}

				/* If all went well, we loaded the template and generated the replacements. */
				$content = $this::template_generate_info($data_all, $a_remplace);
				if (is_null($content)) {
					$error['type'] = "url_not_valid";
				}
			} else {
				if ( $cache->isExist() ) {
					$error['type'] = "error_cache_data";
					$cache->delete(true);
				} else {
					$error['type'] = "error_data_is_null";
				}
			}
		}
		if (isset($data_all)) {
			unset($data_all);
		}		

		/* If there is an error, we prepare the error message that has been detected. */
		if (! empty($error['type'])) {
			/* Clean Transient is error detected. */
			$cache->delete(true);

			$content = $this::template_file_require('msg-error.php');
			$a_remplace['%%_ERROR_TITLE_%%'] = "ERROR";
			if (empty($error['msg_custom'])) {
				$a_remplace['%%_ERROR_MESSAGE_%%'] = Message::getMessage($error['type']);
			} else {
				$a_remplace['%%_ERROR_MESSAGE_%%'] = $error['msg_custom'];
			}
		}
		unset ($cache);
		
		/* If "$content" is not empty, we execute the replaces in the template. */
		if (! empty($content)) { 
			$a_remplace['%%_CFG_DARK_THEME_%%'] = "ebg-br-cfg-dark-theme-" . ($darck_theme ? "on" : "off");
			$a_remplace['%%_CFG_ICON_TYPE_SOURCE_-_FILE_SVG_%%'] = ($icon_type_source == "file_svg" ? "ebg-br-cfg-icon-type-source-file_svg" : "ebg-br-hide");
			$a_remplace['%%_CFG_ICON_TYPE_SOURCE_-_FONT_AWESOME_%%'] = ($icon_type_source == "font_awesome" ? "ebg-br-cfg-icon-type-source-font_awesome" : "ebg-br-hide");
			$a_remplace['%%_URL_ICO_LINK_%%'] = $this->getURL("public/images/link.svg");

			foreach ($a_remplace as $key => $val) {
				$content = str_replace($key, $val, $content);
			}
			return $content;
		}
	}

	private function template_file_require( $template, $data = array() ) {
		ob_start();
		if ( ! locate_template( $this->getName() . '/' . $template, true, false) ) {
			$filename = $this->getPath('templates/' . $template);
			if (! file_exists( $filename ) ) {
				return NULL;
			}
			require $filename;
		}
		return ob_get_clean();
	}

	private function template_collect_values_to_replace($data, $prefix_text, &$a_remplace) {
		foreach ($data as $key => $value) {
			$new_prefix_text = $prefix_text."_".strtoupper($key);
			//echo "Debug >> Key:". $key . " - Valor Tipo:" . gettype($value) . "<br>";
			if (is_object($value)) {
				$this->{__FUNCTION__}($value, $new_prefix_text, $a_remplace);
			} else {
				$a_remplace[$new_prefix_text.'_%%'] = $value;
				$a_remplace[$new_prefix_text.'_%_CLASS_HIDE_IS_NULL_%%'] = (empty(trim($value)) ? "ebg-br-hide": "");
			}
		}
	}
	
	private function template_generate_info($data_all, &$a_remplace) {
		// https://api.github.com/users/vsc55
		// https://api.github.com/repos/vsc55/embed-block-for-github

		$name_file = 'info-'.strtolower($data_all->type).'.php';
		$content = $this::template_file_require($name_file, $data_all->data);
		if ( (! is_null($content)) && (! empty($content)) ) 
		{
			switch(strtolower($data_all->type))
			{
				case "user":
					$a_remplace['%%_CUSTOM_DATA_USER_CREATED_AT_ONLY_DATE_%%'] = date_format( date_create( $data_all->data->created_at ), 'd/m/Y');
					$a_remplace['%%_CUSTOM_DATA_USER_CREATED_AT_ONLY_DATE_%_CLASS_HIDE_IS_NULL_%%'] = (empty($data_all->data->created_at) ? "ebg-br-hide": "");
					$a_remplace['%%_CUSTOM_DATA_USER_UPDATED_AT_ONLY_DATE_%%'] = date_format( date_create( $data_all->data->updated_at ), 'd/m/Y');
					$a_remplace['%%_CUSTOM_DATA_USER_UPDATED_AT_ONLY_DATE_%_CLASS_HIDE_IS_NULL_%%'] = (empty($data_all->data->updated_at) ? "ebg-br-hide": "");
					break;
				case "repo":
					break;
			}
			$this::template_collect_values_to_replace($data_all->data, "%%_DATA_".strtoupper($data_all->type), $a_remplace);		
		}
		return $content;
	}


	function pageAdminMenu() {
		echo "Hola Mundo!!";
	}

}


//$embed_block_for_github = new embed_block_for_github();
embed_block_for_github::get_instance();