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

require_once ('interface-cache-store.php' );
require_once ('class-cache-store-base.php' );

use EmbedBlockForGithub\Cache\ICache_Store;
use EmbedBlockForGithub\Cache\Cache_Store_Base;

/**
 * TODO: Pendiente controlar posibles solapamientos en las funciones set, get, delete 
 * al usar el mismo registro y no haber un control de bloqueos.
 * Posible solución crear una tabla para el cache.
 */

class Cache_Store_Transient extends Cache_Store_Base implements ICache_Store {

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
	public function is_cache_exist() {
		$return_data = true;
		if ( ! get_transient($this->transient_cache_storage) ) {
			$return_data = false;
		}
		return $return_data;
	}
	
	/**
	 * Clean cache and regenerate storage cache.
	 * 
	 * @param bool $only_clean True only clean cache, False Clean and regenerate cache.
	 */
	public function clean_cache($only_clean = false) {
		if ( get_transient($this->transient_cache_storage) ) {
			delete_transient( $this->transient_cache_storage );
		}
		if (! $only_clean) {
			$cache_data = (object)array();
			set_transient( $this->transient_cache_storage, json_encode( $cache_data ) , $this->get_expiration() );
		}
	}

	/**
	 * Control Storage Cache.
	 * For example if plugin version not equal of the storage version the clean and 
	 * regenerate the cache storage.
	 */
	public function control_cache_storage() {
		if ( ! $this->check_cache_version() ) {
			$this->clean_cache();

			$opt = $this->get_option_name_to_cache_version();
			$ver = $this->get_version();
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
	public function get_option_name_to_cache_version() {
		return "embed_block_for_github_cache_store" . "_db_version";
	}

	/**
	 * 
	 */
	public function count() {
		$this->control_cache_storage();

		$cache_json = get_transient( $this->transient_cache_storage );
		$cache_data = json_decode( $cache_json );
		$return_data = count( $cache_data );
		return $return_data;
	}

	/**
	 * 
	 */
	public function is_exist($url = null) {
		$this->control_cache_storage();

		$return_data = false;
		if ( is_null($url) ) {
			$url = $this->get_URL();
		}
		if ( ! $this->is_URL_null($url) ) {
			if ( $this->get_status() ) {
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
	public function set($data, $url = null) {
		$this->control_cache_storage();

		$return_data = false;
		if ( is_null($url) ) {
			$url = $this->get_URL();
		}
		if ( ! $this->is_URL_null($url) ) {
			if ( $this->get_status() ) {
				$url_fix = sanitize_title_with_dashes( $url );

				$cache_data = $this->get_cache_storage();
				$cache_data->{$url_fix} = $data;
				$this->set_cache_storage($cache_data);
				$return_data = true;
			}
		}
		return $return_data;
	}

	/**
	 *
	 */
	public function get($url = null) {
		$this->control_cache_storage();

		$return_data = "";
		if ( is_null($url) ) {
			$url = $this->get_URL();
		}
		if ( ! $this->is_URL_null($url) ) {
			if ( $this->get_status() ) {
				if ( $this->is_exist($url) ) {
					$url_fix = sanitize_title_with_dashes( $url );
					$cache_data = $this->get_cache_storage();
					$return_data = $cache_data->{$url_fix};
				}
			}
		}
		return $return_data;
	}

	/**
	 *
	 */
	public function delete($force = false, $url = null) {
		$this->control_cache_storage();

		$return_data = false;
		if ( is_null($url) ) {
			$url = $this->get_URL();
		}
		if ( ! $this->is_URL_null($url) ) {
			if ( ( $this->get_status() ) || ( $force ) ) {
				if ( $this->is_exist($url) ) {
					$url_fix 	= sanitize_title_with_dashes( $url );
					$cache_data = $this->get_cache_storage();
				  	unset ($cache_data->{$url_fix});
					$this->set_cache_storage($cache_data);

					if ( ! $this->is_exist($url) ) {
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
	private function get_cache_storage() {
		$this->control_cache_storage();
		$return_data = json_decode( get_transient( $this->transient_cache_storage ) );
		return $return_data;
	}

	/**
	 * set all cache
	 * 
	 * @param object $data
	 */
	private function set_cache_storage($data) {
		$this->control_cache_storage();
		set_transient( $this->transient_cache_storage, json_encode( $data ), $this->get_expiration() );
	}

}