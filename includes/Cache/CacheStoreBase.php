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

class CacheStoreBase {

	private static $instance;

	private $parent = null;

	private $status;
	private $url;
	private $expiration;
	private $version;
	
	public static function get_instance($parent = null) {
		if ( is_null (self::$instance ) ) {
			self::$instance = new self ($parent);
		}
		return self::$instance;
	}
	
	public function __construct($parent) {
		$this->status 		= true;
		$this->url			= "";
		$this->expiration	= 0;
		$this->version		= 0;
		$this->parent 		= (object)array();
		if (! is_null($parent)) {
			$this->parent = $parent;
			$this->version = $parent->getPluginData('Version');
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
	 * Check if storage version in cache is equel plugin version.
	 * 
	 * @return bool True version equal, False diferent version.
	 */
	public function checkCacheVersion () {
		$return_data = true;
		if (! $this->isCacheExist()) {
			$return_data = false;
		} else {
			$cache_version = get_option( $this->getOptionNameToCacheVersion(), "");
			if (empty($cache_version) ) {
				$return_data = false;
			} else {
				if ($cache_version !=  $this->getVersion()) {
					$return_data = false;
				}
			}
		}
		return $return_data;
	}

	/**
	 * Get the cache version.
	 * 
	 * @param bool $only_version	True only number version, False NameClass + version
	 * @return string 
	 */
	public function getVersion($only_version = false) {
		$return_data = $this->version;
		if (! $only_version) {
			$return_data = get_class($this)."_".$return_data;
		} 
		return $return_data;
	}

	/**
	 * Set the cache version.
	 * 
	 * @param string $new_version
	 */
	public function setVersion($new_version) {
		$this->version = $new_version;
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
	}

	/**
	 * Check if url is defined
	 * 
	 * @param string $url Optional, if it is not used, it will be obtained with the "getUrl" option.
	 * @return bool
	 */
	public function isUrlNull($url = null) {
		if ( is_null($url) ) {
			$url = $this->getUrl();
		}
		return empty( $url );
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