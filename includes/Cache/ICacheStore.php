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

interface ICacheStore {
    
	public static function get_instance($parent = null);

	/**
	 * Check if the cache exists.
	 * 
	 * @return bool
	 */
	public function isCacheExist();

	/**
	 * Check if storage version in cache is equel plugin version.
	 * 
	 * @return bool True version equal, False diferent version.
	 */
	public function checkCacheVersion ();
	
	/**
	 * Get the name of the option where the database version is saved.
	 * 
	 * @return string 
	 */
	public function getOptionNameToCacheVersion();

	/**
	 * Get the cache version.
	 * 
	 * @return string 
	 */
	public function getVersion();

	/**
	 * Set the cache version.
	 * 
	 * @param string $new_version
	 */
	public function setVersion($new_version);

	/**
	 * Clean cache and regenerate storage cache.
	 * 
	 */
	public function cleanCache ();

	/**
	 * Control Storage Cache.
	 * For example if plugin version not equal of the storage version the clean and 
	 * regenerate the cache storage.
	 */
	public function controlCacheStorage();
	
	/**
	 * Get Cache Status
	 * 
	 * @return bool True enabled, False disabled.
	 */
	public function getStatus();

	/**
	 * Set Cache Status
	 * 
	 * @param bool $new_status
	 */
	public function setStatus(bool $new_status);

	/**
	 * Get url to process.
	 * 
	 * @return string URL Cache
	 */
	public function getUrl();

	/**
	 * Set url to process.
	 * 
	 * @param string $url
	 */
	public function setUrl(string $url);

	/**
	 * Check if url is defined
	 * 
	 * @param string $url Optional, if it is not used, it will be obtained with the "getUrl" option.
	 * @return bool
	 */
	public function isUrlNull($url = null);

	/**
	 * Check if exist the url in the cache.
	 * 
	 * @param string $url Optional, if it is not used, it will be obtained with the "getUrl" option.
	 * @return bool
	 */
	public function isExist($url = null);

	/**
	 * Set new data in cache.
	 * 
	 * @param mixed $data
	 * @param string $url Optional, if it is not used, it will be obtained with the "getUrl" option.
	 * @return bool
	 */
	public function set ($data, $url = null);

	/**
	 * Get data from cache.
	 * 
	 * @param string $url Optional, if it is not used, it will be obtained with the "getUrl" option.
	 * @return mixed 
	 */
	public function get($url = null);

	/**
	 * delete data for cache.
	 * 
	 * @param bool $force 	force deleted although status is false.
	 * @param string $url 	Optional, if it is not used, it will be obtained with the "getUrl" option.
	 */
	public function delete ($force = false, $url = null);

	/**
	 * Get the time in seconds for the expiration of the data in cache.
	 * 
	 * @return int
	 */
	public function getExpiration();

	/**
	 * Set the time in seconds for the expiration of the data in cache.
	 * 
	 * @param int $new_expiration
	 */
	public function setExpiration($new_expiration);

	/**
	 * Number of records in the cache
	 * 
	 * @return int
	 */
	public function count();


	/*
	public function isCacheExist ();
	public function cleanCache ();
	public function isExist($url = null);
	public function set ($data, $url = null);
	public function get($url = null);
	public function delete ($force = false, $url = null);
	public function count();
	*/
}