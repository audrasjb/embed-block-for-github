<?php
/**
 * 
 * Author:            VSC55
 * Author URI:        https://github.com/vsc55/embed-block-for-github
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 */

namespace EmbedBlockForGithub\Plugin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class Plugin_Base {

	private $file;
	private $file_name;
	private $directory;
	private $name;
	private $path;
	private $url;
	private $plugin_data;

	protected function __construct($file) {
		$this->file 	 =& $file;
		$this->file_name = basename($this->file);
		$this->directory = dirname($this->file);
		$this->name 	 = basename($this->directory);
		$this->path 	 = plugin_dir_path($this->file);
		$this->url 		 = plugin_dir_url($this->file);

		// Fix Error: Call to undefined function get_plugin_data
		if( !function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$this->plugin_data = get_plugin_data($this->file);
	}

	/**
	 * 
	 */
	public function get_directory() {
		return $this->directory;
	}
	
	/**
	 * 
	 */
	public function get_file() {
		return $this->file;
	}

	/**
	 * 
	 */
	public function get_file_name() {
		return $this->file_name;
	}
	
	/**
	 * 
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * 
	 */
	public function get_URL($file = "") {
		$return_url = $this->path;
		if ( ! empty( trim($file) ) ) {
			$return_url = plugins_url( $file, $this->file );
		}
		return $return_url;
	}

	/**
	 * 
	 */
	public function get_path($file) {
		$return_path = $this->path;
		if ( ! empty(trim( $file ) ) ) {
			$return_path .= $file;
		}
		return $return_path;
	}

	/**
	 * Get version of the file using modified date.
	 * 
	 * @param string $file
	 * @return int
	 */
	protected function get_version_file($file) {
		$path_full = $this->get_path($file);
		if ( file_exists($path_full) ) {
			return filemtime($path_full);
		}
		return -1;
	}

	/**
	 * 
	 */
	public function get_plugin_data($data, $default = "") {
		$return_data = $default;
		if ( ! is_null( $this->plugin_data ) ) {
			if ( array_key_exists( $data, $this->plugin_data ) ) {
				$return_data = $this->plugin_data[$data];
			}
		}
		return $return_data;
	}

}