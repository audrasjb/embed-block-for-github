<?php

namespace EmbedBlockForGithub\Plugin;

abstract class PluginBase {

	private $file;
	private $fileName;
	private $directory;
	private $name;
	private $path;
	private $url;
	private $pluginData;

	protected function __construct($file) {
		$pluginClass = get_class($this);
		$this->file =& $file;
		$this->fileName = basename($this->file);
		$this->directory = dirname($this->file);
		$this->name = basename($this->directory);
		$this->path = plugin_dir_path($this->file);
		$this->url = plugin_dir_url($this->file);

		// Fix Error: Call to undefined function get_plugin_data
		if( !function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$this->pluginData = get_plugin_data($this->file);
	}

	public function getDirectory() {
		return $this->directory;
	}
    
	public function getFile() {
		return $this->file;
	}

	public function getFileName() {
		return $this->fileName;
	}
	
	public function getName() {
		return $this->name;
	}

	public function getURL($file = "") {
		$return_url = $this->path;
		if (! empty( trim($file) ) ) {
			$return_url = plugins_url( $file, $this->file );
		}
		return $return_url;
	}

	public function getPath($file) {
		$return_path = $this->path;
		if (! empty(trim( $file ) ) ) {
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
	protected function getVersionFile($file) {
		$path_full = $this->getPath($file);
		if (file_exists($path_full)) {
			return filemtime($path_full);
		}
		return -1;
	}

	public function getPluginData($data, $default = "") {
		$return_data = $default;
		if (! is_null($this->pluginData) ) {
			if ( in_array($data, $this->pluginData) ) {
				$return_data = $this->pluginData[$data];
			}
			
		}
		return $return_data;
	}

}