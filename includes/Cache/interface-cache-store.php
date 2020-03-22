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

interface ICache_Store {
    
	public static function get_instance($parent = null);

	/**
	 * Check if the cache exists.
	 * 
	 * @return bool
	 */
	public function is_cache_exist();

	/**
	 * Check if storage version in cache is equel plugin version.
	 * 
	 * @return bool True version equal, False diferent version.
	 */
	public function check_cache_version();
	
	/**
	 * Get the name of the option where the database version is saved.
	 * 
	 * @return string 
	 */
	public function get_option_name_to_cache_version();

	/**
	 * Get the cache version.
	 * 
	 * @param bool $only_version	True only number version, False NameClass + version
	 * @return string 
	 */
	public function get_version($only_version = false);

	/**
	 * Set the cache version.
	 * 
	 * @param string $new_version
	 */
	public function set_version($new_version);

	/**
	 * Clean cache and regenerate storage cache.
	 * 
	 */
	public function clean_cache();

	/**
	 * Control Storage Cache.
	 * For example if plugin version not equal of the storage version the clean and 
	 * regenerate the cache storage.
	 */
	public function control_cache_storage();
	
	/**
	 * Get Cache Status
	 * 
	 * @return bool True enabled, False disabled.
	 */
	public function get_status();

	/**
	 * Set Cache Status
	 * 
	 * @param bool $new_status
	 */
	public function set_status(bool $new_status);

	/**
	 * Get url to process.
	 * 
	 * @return string URL Cache
	 */
	public function get_URL();

	/**
	 * Set url to process.
	 * 
	 * @param string $url
	 */
	public function set_URL(string $url);

	/**
	 * Check if url is defined
	 * 
	 * @param string $url Optional, if it is not used, it will be obtained with the "get_URL" option.
	 * @return bool
	 */
	public function is_URL_null($url = null);

	/**
	 * Check if exist the url in the cache.
	 * 
	 * @param string $url Optional, if it is not used, it will be obtained with the "get_URL" option.
	 * @return bool
	 */
	public function is_exist($url = null);

	/**
	 * Set new data in cache.
	 * 
	 * @param mixed $data
	 * @param string $url Optional, if it is not used, it will be obtained with the "get_URL" option.
	 * @return bool
	 */
	public function set($data, $url = null);

	/**
	 * Get data from cache.
	 * 
	 * @param string $url Optional, if it is not used, it will be obtained with the "get_URL" option.
	 * @return mixed 
	 */
	public function get($url = null);

	/**
	 * delete data for cache.
	 * 
	 * @param bool $force 	force deleted although status is false.
	 * @param string $url 	Optional, if it is not used, it will be obtained with the "get_URL" option.
	 */
	public function delete($force = false, $url = null);

	/**
	 * Get the time in seconds for the expiration of the data in cache.
	 * 
	 * @return int
	 */
	public function get_expiration();

	/**
	 * Set the time in seconds for the expiration of the data in cache.
	 * 
	 * @param int $new_expiration
	 */
	public function set_expiration($new_expiration);

	/**
	 * Number of records in the cache
	 * 
	 * @return int
	 */
	public function count();


	/*
	public function is_cache_exist ();
	public function clean_cache ();
	public function is_exist($url = null);
	public function set ($data, $url = null);
	public function get($url = null);
	public function delete ($force = false, $url = null);
	public function count();
	*/
}