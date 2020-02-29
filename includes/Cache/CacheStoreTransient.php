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

require_once ('ICacheStore.php' );
require_once ('CacheStoreBase.php' );

use EmbedBlockForGithub\Cache\ICacheStore;
use EmbedBlockForGithub\Cache\CacheStoreBase;

/**
 * TODO: Pendiente controlar posibles solapamientos en las funciones set, get, delete 
 * al usar el mismo registro y no haber un control de bloqueos.
 * Posible solución crear una tabla para el cache.
 */

class CacheStoreTransient extends CacheStoreBase implements ICacheStore {

	private static $instance;

	private $transient_cache_storage;
	
	public static function get_instance($parent = null) {
		if ( is_null (self::$instance ) ) {
			self::$instance = new self ($parent);
		}
		return self::$instance;
	}
	
	public function __construct($parent) {
		parent::__construct( $parent );

		$this->transient_cache_storage 	= "_ebg_repository_cache_storage";
	}

	/**
	 * Check if the cache exists.
	 * 
	 * @return bool
	 */
	public function isCacheExist() {
		$return_data = true;
		if (! get_transient($this->transient_cache_storage) ) {
			$return_data = false;
		}
		return $return_data;
	}
	
	/**
	 * Clean cache and regenerate storage cache.
	 * 
	 * @param bool $only_clean True only clean cache, False Clean and regenerate cache.
	 */
	public function cleanCache ($only_clean = false) {
		if (get_transient($this->transient_cache_storage) ) {
			delete_transient( $this->transient_cache_storage );
		}
		if (! $only_clean) {
			$cache_data = (object)array();
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

			$opt = $this->getOptionNameToCacheVersion();
			$ver =  $this->getVersion();
			if ( get_option( $opt ) !== false ) {
				update_option( $opt, $ver );
			} else {
				add_option( $opt, $ver );
			}
		}
	}


	/**
	 * 
	 */
	public function getOptionNameToCacheVersion() {
		return "embed_block_for_github_cache_store"."_db_version";
	}

	/**
	 * 
	 */
	public function count () {
		$this->controlCacheStorage();

		$cache_json = get_transient( $this->transient_cache_storage );
		$cache_data = json_decode( $cache_json );
		$return_data = count ($cache_data);
		return $return_data;
	}

	/**
	 * 
	 */
	public function isExist($url = null) {
		$this->controlCacheStorage();

		$return_data = false;
		if ( is_null($url) ) {
			$url = $this->getUrl();
		}
		if (! $this->isUrlNull($url)) {
			if ($this->getStatus()) {
				$cache_json = get_transient( $this->transient_cache_storage );
				$cache_data = json_decode( $cache_json );

				$url_fix = sanitize_title_with_dashes( $url );
				if ( property_exists($cache_data, $url_fix) ) {
					$return_data = true;
				}
			}
		}
		return $return_data;
	}

	/**
	 * 
	 */
	public function set ($data, $url = null) {
		$this->controlCacheStorage();

		$return_data = false;
		if ( is_null($url) ) {
			$url = $this->getUrl();
		}
		if (! $this->isUrlNull($url)) {
			if ($this->getStatus()) {
				$url_fix = sanitize_title_with_dashes( $url );

				$cache_data = $this->getCacheStorage();
				$cache_data->{$url_fix} = $data;
				$this->setCacheStorage($cache_data);
				$return_data = true;
			}
		}
		return $return_data;
	}

	/**
	 *
	 */
	public function get($url = null) {
		$this->controlCacheStorage();

		$return_data = "";
		if ( is_null($url) ) {
			$url = $this->getUrl();
		}
		if (! $this->isUrlNull($url)) {
			if ($this->getStatus()) {
				if ($this->isExist($url)) {
					$url_fix = sanitize_title_with_dashes( $url );
					$cache_data = $this->getCacheStorage();
					$return_data = $cache_data->{$url_fix};
				}
			}
		}
		return $return_data;
	}

	/**
	 *
	 */
	public function delete ($force = false, $url = null) {
		$this->controlCacheStorage();

		$return_data = false;
		if ( is_null($url) ) {
			$url = $this->getUrl();
		}
		if (! $this->isUrlNull($url)) {
			if ( ($this->getStatus()) || ($force) ) {
				if ($this->isExist($url)) {
					$url_fix = sanitize_title_with_dashes( $url );
					$cache_data = $this->getCacheStorage();
				  	unset ($cache_data->{$url_fix});
					$this->setCacheStorage($cache_data);

					if (! $this->isExist($url) ) {
						$return_data = true;
					}
				}
			}
		}
		return $return_data;
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

}