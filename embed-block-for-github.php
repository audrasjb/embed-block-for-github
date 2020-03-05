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
require_once ( __DIR__ . '/includes/Plugin/Config.php' );
require_once ( __DIR__ . '/includes/Languages/Message.php' );
require_once ( __DIR__ . '/includes/GitHub/GitHubAPI.php' );

require_once ( __DIR__ . '/includes/Cache/CacheStoreTable.php' );
require_once ( __DIR__ . '/includes/Cache/CacheStoreTransient.php' );

require_once ( __DIR__ . '/admin/PagAdmin.php' );
require_once ( __DIR__ . '/admin/PagAdminCache.php' );
require_once ( __DIR__ . '/admin/PagAdminApiGitHubRate.php' );


use EmbedBlockForGithub\Plugin\PluginBase;
use EmbedBlockForGithub\Plugin\Config;

use EmbedBlockForGithub\Lang\Message;
use EmbedBlockForGithub\GitHub\API\GitHubAPI;

use EmbedBlockForGithub\Cache\CacheStoreTable;
use EmbedBlockForGithub\Cache\CacheStoreTransient;

use EmbedBlockForGithub\Pags\Admin\PagAdmin;
use EmbedBlockForGithub\Pags\Admin\PagAdminCache;
use EmbedBlockForGithub\Pags\Admin\PagAdminApiGitHubRate;



class embed_block_for_github extends PluginBase {

	private static $instance;

	public $api;
	public $config;

	public $cache;

	private $pag_admin;

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
		
		$this->config = Config::get_instance($this);
		$this->config->prefix 	= strtolower($this->getName());
		$this->config->group 	= strtolower($this->getName());
		$this->config->addOption('db_version', 'string', '');
		$this->config->addOption('darck_theme', 'boolean', false);
		$this->config->addOption('icon_type_source', 'string', 'file');
		$this->config->addOption('api_cache_disable', 'boolean', false);
		$this->config->addOption('api_cache_expire', 'string', '3600');
		$this->config->addOption('api_access_token', 'string', '', true);
		$this->config->addOption('api_access_token_user', 'string', '', true);

		$this->api = GitHubAPI::get_instance($this);
		$this->api->access_token 		= $this->config->getOption("api_access_token");
		$this->api->access_token_user 	= $this->config->getOption("api_access_token_user");
		$this->api->hooks_customMessageGitHub = array($this, 'customMessageGitHub');
	
		
		//$this->cache = CacheStoreTransient::get_instance($this);
		$this->cache = CacheStoreTable::get_instance($this);

		$this->cache->setVersion		( $this->getPluginData('Version') );
		$this->cache->setExpiration 	( $this->config->getOption('api_cache_expire') );

		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'init', array( $this, 'block_assets' ) );
		add_action( 'init', array( $this, 'editor_assets' ) );
		add_action( 'admin_init', array( $this, 'admin_ajax_scripts' ) );

		if ( is_admin() ) {
			$this->initPagsAdmin();
		}
	}

	/**
	 * 
	 */
	private function initPagsAdmin() {

		$pag_admin['main'] = new PagAdmin($this);
		$pag_admin['cache'] = new PagAdminCache($this);
		$pag_admin['api_github_rate'] = new PagAdminApiGitHubRate($this);

		if ( ! empty( $_GET['page'] ) ) {
			foreach ($pag_admin as $key => &$val) {
				if ( $_GET['page'] == $val->getMenuSlug() ) {
					$val->add_action_all();
				}
			}
		}
	}

	/**
	 * 
	 */
	public function register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			// Gutenberg is not active.
			return;
		}
		register_block_type( 'embed-block-for-github/repository', array(
			'editor_script'   => 'ebg-repository-editor',
			'editor_style'    => 'ebg-repository-editor',
			'style'           => 'ebg-repository',
			'render_callback' => array( $this, 'ebg_embed_repository' ),
			'attributes'      => array(
				'github_url' => array( 'type' => 'string' ),
				'custom_theme' => array( 'type' => 'boolean' ),
				'darck_theme' => array( 'type' => 'boolean' ),
				'icon_type_source' => array( 'type' => 'string' ),
				'custom_api_cache' => array( 'type' => 'boolean' ),
				'api_cache_disable' => array( 'type' => 'boolean' ),
				'api_cache_expire' => array( 'type' => 'string' ),
			),
		) );
	}

	/**
	 * 
	 */
	public function block_assets() {
		// Styles.
		wp_register_style (
			'ebg-repository',
			$this->getURL('public/css/repository-block.css'),
			array(),
			$this->getVersionFile('public/css/repository-block.css')
		);
	}

	/**
	 * 
	 */
	public function editor_assets() {
		// Styles.
		wp_register_style (
			'ebg-repository-editor',
			$this->getURL('admin/css/repository-block-editor.css'),
			array(),
			$this->getVersionFile('admin/css/repository-block-editor.css')
		);

		// Scripts.
		wp_register_script(
			'ebg-repository-editor',
			$this->getURL('admin/js/repository-block.js'),
			array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-editor' ),
			$this->getVersionFile('admin/js/repository-block.js')
		);
		wp_localize_script('ebg-repository-editor', 'ebg_repository_editor_gloabl_config', $this->config->getOptions(false));
	}

	/**
	 * 
	 */
	public function admin_ajax_scripts() {
		wp_enqueue_script(
			"embed_block_for_github_admin_ajax", 
			$this->getURL( 'admin/js/admin-ajax.js'), 
			array('jquery'),
			$this->getVersionFile('admin/js/admin-ajax.js'),
		);
	}

	/**
	 * Message according to the error received from GitHub.
	 * 
	 */
	public static function customMessageGitHub($message, $documentation_url) {
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

	/**
	 * 
	 * 
	 */
	public function ebg_embed_repository( $attributes ) {
		// Config globla
		$darck_theme 		= $this->config->getOption('darck_theme');
		$icon_type_source 	= $this->config->getOption('icon_type_source');
		$api_cache_disable	= $this->config->getOption('api_cache_disable');
		$api_cache_expire 	= $this->config->getOption('api_cache_expire');

		/* get attributes value and if value is empty set default value */
		$github_url 		= trim( $attributes['github_url'] );

		$custom_theme 		= (isset($attributes['custom_theme']) ? $attributes['custom_theme'] : false);
		$custom_api_cache	= (isset($attributes['custom_api_cache']) ? $attributes['custom_api_cache'] : false);

		if ($custom_theme) {
			$darck_theme 		= (isset($attributes['darck_theme']) ? $attributes['darck_theme'] : $darck_theme);
			$icon_type_source 	= (! empty($attributes['icon_type_source']) ? $attributes['icon_type_source'] : $icon_type_source);
		}
		if ($custom_api_cache) {
			$api_cache_disable 	= (isset($attributes['api_cache_disable']) ? $attributes['api_cache_disable'] : $api_cache_disable);
			//$api_cache_expire 	= (! empty($attributes['api_cache_expire']) ? $attributes['api_cache_expire'] : $api_cache_expire);
		}
		
		/* DEV: CLEAN TRANSIENT */
		if ( 1 == 2) {
			$this->cache->cleanCache();
		}
		/* DEV: CLEAN TRANSIENT */

		if ( (!  $this->cache->isExist($github_url) ) || ( $api_cache_disable ) ) 
		{
			if ( $this->api->setURL($github_url) ) 
			{
				$data_all = (object)array();
				$data_all->type = $this->api->getTypeURL();
				$data_all->data = $this->api->getData();
				if (! empty($data_all->data)) 
				{
					if (! $api_cache_disable) {
						$this->cache->set($data_all, $github_url);
					}
				}
			}
			if ($this->api->isSetError()) {
				$error['type'] = $this->api->getError();
			}
		}

		if (empty($error['type'])) 
		{
			if ( empty($data_all) )
			{
				$data_all = $this->cache->get($github_url);
			}

			if (isset($data_all->data)) 
			{
				/* If all went well, we loaded the template and generated the replacements. */
				$content = $this::template_generate_info($data_all, $a_remplace);
				if (is_null($content)) {
					$error['type'] = "url_not_valid";
				}
			} else {
				if ( $this->cache->isExist($github_url) ) {
					$error['type'] = "error_cache_data";
				} else {
					$error['type'] = "error_data_is_null";
				}
			}
		}
		
		/* If there is an error, we prepare the error message that has been detected. */
		if (! empty($error['type'])) {
			/* Clean Transient is error detected. */
			$this->cache->delete(true, $github_url);
			

			$content = $this::template_file_require('msg-error.php');
			$a_remplace['%%_ERROR_TITLE_%%'] = "ERROR";

			if ($error['type'] == "get_error_from_github") {
				$error['msg_custom'] = $data_all->data->message;
			}
			
			if (empty($error['msg_custom'])) {
				$a_remplace['%%_ERROR_MESSAGE_%%'] = Message::getMessage($error['type']);
			} else {
				$a_remplace['%%_ERROR_MESSAGE_%%'] = $error['msg_custom'];
			}
		}
		unset ($data_all);
		
		/* If "$content" is not empty, we execute the replaces in the template. */
		if (! empty($content)) { 
			$a_remplace['%%_CFG_DARK_THEME_%%'] = "ebg-br-cfg-dark-theme-" . ($darck_theme ? "on" : "off");
			
			$a_remplace['%%_CFG_CACHE_%%'] = "ebg-br-cfg-cache-" . ($api_cache_disable ? "off" : "on");
			
			$a_remplace['%%_CFG_ICON_TYPE_SOURCE_-_FILE_SVG_%%'] = ($icon_type_source == "file" ? "ebg-br-cfg-icon-type-source-file" : "ebg-br-hide");
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

}


embed_block_for_github::get_instance();