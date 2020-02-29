<?php
/**
 * 
 * Author:            VSC55
 * Author URI:        https://github.com/vsc55/embed-block-for-github
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 */

namespace EmbedBlockForGithub\Cache;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * TODO: Pendiente controlar posibles solapamientos en las funciones set, get, delete 
 * al usar el mismo registro y no haber un control de bloqueos.
 * Posible solución crear una tabla para el cache.
 */

class Transient {

	private static $instance;

	private $parent = null;

	private $status;
	private $url;
	private $url_fix;
	private $expiration;
	private $transient_cache_version;
	private $transient_cache_storage;
	
	public static function get_instance($parent = null) {
		if ( is_null (self::$instance ) ) {
			self::$instance = new self ($parent);
		}
		return self::$instance;
	}
	
	public function __construct($parent) {
		$this->id 			= "";
		$this->status 		= true;
		$this->url			= "";
		$this->url_fix		= "";
		$this->expiration	= 0;
		$this->parent 		= (object)array();
		if (! is_null($parent)) {
			$this->parent = $parent;
		}
		
		$this->transient_cache_version 	= "_ebg_repository_cache_version";
		$this->transient_cache_storage 	= "_ebg_repository_cache_storage";
	}

	/**
	 * Check if the cache exists.
	 * 
	 * @return bool
	 */
	private function isCacheExist() {
		$return_data = true;
		if (! get_transient($this->transient_cache_version) ) {
			$return_data = false;
		}
		if (! get_transient($this->transient_cache_storage) ) {
			$return_data = false;
		}
		return $return_data;
	}

	/**
	 * Check if storage version in cache is equel plugin version.
	 * 
	 * @return bool True version equal, False diferent version.
	 */
	private function checkCacheVersion () {
		$return_data = true;
		if (! $this->isCacheExist()) {
			$return_data = false;
		} else {
			$cache_version = get_transient( $this->transient_cache_version );
			$plugin_version = $this->parent->getPluginData('Version');
			if (empty($cache_version) ) {
				$return_data = false;
			} else {
				if ($cache_version != $plugin_version) {
					$return_data = false;
				}
			}
		}
		return $return_data;
	}
	
	/**
	 * Clean cache and regenerate storage cache.
	 * 
	 * @param bool $only_clean True only clean cache, False Clean and regenerate cache.
	 */
	public function cleanCache ($only_clean = false) {
		if (get_transient($this->transient_cache_version) ) {
			delete_transient( $this->transient_cache_version );
		}
		if (get_transient($this->transient_cache_storage) ) {
			delete_transient( $this->transient_cache_storage );
		}
		if (! $only_clean) {
			$cache_data = (object)array();
			$cache_version = $this->parent->getPluginData('Version');
			set_transient( $this->transient_cache_version, $cache_version );
			set_transient( $this->transient_cache_storage, json_encode( $cache_data ) , $this->getExpiration());
		}
	}

	/**
	 * Control Storage Cache.
	 * For example if plugin version not equal of the storage version the clean and 
	 * regenerate the cache storage.
	 */
	public function controlCacheStorage() {
		if (! $this->checkCacheVersion()) {
			$this->cleanCache();
		}
	}
	
	/**
	 * Get Cache Status
	 * 
	 * @return bool True enabled, False disabled.
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Set Cache Status
	 * 
	 * @param bool $new_status
	 */
	public function setStatus(bool $new_status) {
		$this->status = $new_status;
	}

	/**
	 * Get url to process.
	 * 
	 * @return string URL Cache
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * Set url to process.
	 * 
	 * @param string $url
	 */
	public function setUrl(string $url) {
		$this->url = $url;
		$this->url_fix = sanitize_title_with_dashes( $url);
	}

	/**
	 * Check if url is defined
	 * 
	 * @return bool
	 */
	public function isUrlNull() {
		return empty( $this->getUrl() );
	}

	/**
	 * Check if exist the url in the cache.
	 * 
	 * @return bool
	 */
	public function isExist() {
		$this->controlCacheStorage();
		$return_data = false;
		if (! $this->isUrlNull) {
			if ($this->getStatus()) {
				$cache_json = get_transient( $this->transient_cache_storage );
				$cache_data = json_decode( $cache_json );
				if ( property_exists($cache_data, $this->url_fix) ) {
					$return_data = true;
				}
			}
		}
		return $return_data;
	}

	/**
	 * Set new data in cache.
	 * 
	 * @param mixed $data
	 * @return bool
	 */
	public function set ($data) {
		$this->controlCacheStorage();
		$return_data = false;
		if (! $this->isUrlNull) {
			if ($this->getStatus()) {
				$cache_data = $this->getCacheStorage();
				$cache_data->{$this->url_fix} = $data;
				$this->setCacheStorage($cache_data);
				$return_data = true;
			}
		}
		return $return_data;
	}

	/**
	 * Get data from cache.
	 * 
	 * @return mixed 
	 */
	public function get() {
		$this->controlCacheStorage();
		$return_data = "";
		if (! $this->isUrlNull) {
			if ($this->getStatus()) {
				if ($this->isExist()) {
					$cache_data = $this->getCacheStorage();
					$return_data = $cache_data->{$this->url_fix};
				}
			}
		}
		return $return_data;
	}

	/**
	 * delete data for cache.
	 * 
	 * @param bool $force 	force deleted although status is false.
	 */
	public function delete ($force = false) {
		$this->controlCacheStorage();
		if (! $this->isUrlNull) {
			if ( ($this->getStatus()) || ($force) ) {
				if ($this->isExist()) {
					$cache_data = $this->getCacheStorage();
				  	unset ($cache_data->{$this->url_fix});
					$this->setCacheStorage($cache_data);
				}
			}
		}
	}

	/**
	 * Get all cache.
	 * 
	 * @return object
	 */
	private function getCacheStorage(){
		$this->controlCacheStorage();
		$return_data = json_decode( get_transient( $this->transient_cache_storage ) );
		return $return_data;
	}

	/**
	 * set all cache
	 * 
	 * @param object $data
	 */
	private function setCacheStorage($data) {
		$this->controlCacheStorage();
		set_transient( $this->transient_cache_storage, json_encode( $data ), $this->getExpiration() );
	}

	/**
	 * Get the time in seconds for the expiration of the data in cache.
	 * 
	 * @return int
	 */
	public function getExpiration() {
		return $this->expiration;
	}

	/**
	 * Set the time in seconds for the expiration of the data in cache.
	 * 
	 * @param int $new_expiration
	 */
	public function setExpiration($new_expiration) {
		$this->expiration = $new_expiration;
	}
}