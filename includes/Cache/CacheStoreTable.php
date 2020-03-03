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


class CacheStoreTable extends CacheStoreBase implements ICacheStore {

	private static $instance;

	private $table_cache;

	public static function get_instance($parent = null, $table_name = null) {
		if ( is_null (self::$instance ) ) {
			self::$instance = new self ($parent, $table_name);
		}
		return self::$instance;
	}

	public function __construct($parent = null, $table_name = null) {
		parent::__construct( $parent );

		if (! is_null($table_name)) {
			$this->table_cache = $table_name;
		} else {
			$this->table_cache = "cache_store";
			if (! is_null($parent)) {
				$this->table_cache = $this->fixTableName( $parent->getName() )."_".$this->table_cache;
			}
		}
	}

	/**
	 * 
	 * 
	 */
	private function fixTableName($table_name) {
		return str_ireplace("-", "_", $table_name );
	}

	/**
	 * Get the name of the option where the database version is saved.
	 * 
	 * @return string 
	 */
	public function getOptionNameToCacheVersion() {
		return $this->getTableName()."_db_version";
	}

	/**
	 * 
	 * 
	 */
	public function getTableNameFull() {
		global $wpdb;
		return $this->getTableName($wpdb->prefix);
	}

	/**
	 * 
	 * 
	 */
	public function getTableName ($prefix = "") {
		$return_data = $this->table_cache;
		if (! empty($prefix) ) {
			$return_data = $prefix.$return_data;
		}
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	public function setTableName ($name) {
		$this->table_cache = $name;
	}

	/**
	 * 
	 * 
	 */
	private function createTable(){
		$return_data = false;
		if (! empty( $this->getTableName() ) ) {
			if ( ! $this->isExistTable() ) {
				global $wpdb;
				$sql = sprintf("CREATE TABLE IF NOT EXISTS `%s` (
									`id` bigint(20) NOT NULL AUTO_INCREMENT,
									`time_update` datetime NOT NULL DEFAULT current_timestamp(),
									`expire` bigint(20) NOT NULL DEFAULT 0,
									`url` varchar(256) DEFAULT NULL,
									`data` longtext NOT NULL,
									PRIMARY KEY (`id`),
									UNIQUE KEY `URL_UNIQUE` (`url`) USING BTREE, KEY `URL_INDEX` (`url`) USING BTREE
								)
								COLLATE %s", 
						$this->getTableNameFull(),
						$wpdb->collate );

				if( !function_exists('dbDelta') ){
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				}
				dbDelta( $sql );
				$return_data = $this->isExistTable() ;
			}
		}
		return $return_data;
	}

	/**
	 * 
	 */
	private function wpdb_query($str_query) {
		global $wpdb;
		return $wpdb->query( $str_query );
	}

	/**
	 * 
	 * 
	 */
	private function wpdb_get_var($str_query) {
		global $wpdb;
		return $wpdb->get_var( $str_query );
	}

	/**
	 * 
	 * 
	 */
	private function dropTable() {
		if ($this->isExistTable()) {
			$query = sprintf("DROP TABLE IF EXISTS `%s`", $this->getTableNameFull() );
			$this->wpdb_query($query);
		}
		return (! $this->isExistTable() );
	}

	/**
	 * 
	 * 
	 */
	private function truncateTable() {
		$return_data  = false;
		if ($this->isExistTable()) {
			$query = sprintf("TRUNCATE TABLE `%s`", $this->getTableNameFull() );
			$this->wpdb_query( $query );
			if ($this->count() == 0) {
				$return_data = true;
			}
		}
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	private function isExistTable($table_name = null) {
		if ( is_null($table_name) ) {
			$table_name = $this->getTableNameFull();
		}
		
		/**
		 * FIX: The underscore is a wild card so we have to change "_" to "\ _" to avoid false positives. 
		 * 		False positive example: "cache_store" = "cache-store"
		 * FIX: El guion bajo es un comodin por lo que tenemos que cambiar "_" por "\_" para evitar falsos positivos. 
		 * 		Ejemplo falso positivo: "cache_store" = "cache-store"
		 */
		$table_name_fix =  str_ireplace("_", "\_", $table_name );

		$query = sprintf("SHOW TABLES LIKE '%s'", $table_name_fix );
		if ( $this->wpdb_get_var($query) == $table_name ) {
			return true;
		}
		return false;
	}

	/**
	 * 
	 * 
	 */
	private function getIdByUrl($url = null) {
		if ( is_null($url) ) {
			$url = $this->getUrl();
		}
		$query = sprintf("SELECT id FROM `%s` WHERE url = '%s'", $this->getTableNameFull(), $url );
		$return_data = $this->wpdb_get_var( $query );
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	private function isExistDataExpred() {
		$return_data = false;
		if ($this->isExistTable()) {
			$query = sprintf("SELECT count(*) FROM `%s` WHERE `expire` > 0 and  now() > TIMESTAMPADD(SECOND, expire, time_update)", $this->getTableNameFull() );
			$count = $this->wpdb_get_var( $query );
			if ($count > 0) {
				$return_data = true;
			}
		}
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	private function cleanExpiredData() {
		$return_data = false;
		if ($this->isExistTable()) {
			$table_name = $this->getTableNameFull();
			$query = sprintf("DELETE FROM `%s` WHERE `id` IN ( SELECT ID FROM `%s` WHERE `expire` > 0 and  now() > TIMESTAMPADD(SECOND, expire, time_update) )", 
							$table_name,
							$table_name
						);
			$this->wpdb_query($query);
		}
		return $return_data;
	}



	/**
	 * 
	 */
	public function count() {
		$return_data = 0;
		if ($this->isExistTable()) {
			$query = sprintf("SELECT COUNT(*) FROM `%s`", $this->getTableNameFull() );
			$return_data = $this->wpdb_get_var( $query );
		}
		return settype($return_data, "integer");
	}

	/**
	 * 
	 */
	public function isCacheExist() {
		return $this->isExistTable();
	}

	/**
	 * 
	 */
	public function cleanCache () {
		if (! $this->isExistTable()) {
			$this->createTable();
		}
		if ($this->count() > 0) {
			$this->truncateTable();
		}
	}	

	/**
	 * 
	 */
	public function controlCacheStorage() {
		if (! $this->checkCacheVersion()) {
			if ($this->isExistTable()) {
				$this->dropTable();
			}
			if ( $this->createTable() ) {
				$opt = $this->getOptionNameToCacheVersion();
				$ver =  $this->getVersion();
				if ( get_option( $opt ) !== false ) {
					update_option( $opt, $ver );
				} else {
					add_option( $opt, $ver );
				}
			}
		} else {
			if ( $this->isExistDataExpred() ) {
				$this->cleanExpiredData();
			}
		}
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
				$query = sprintf("SELECT COUNT(*) FROM `%s` WHERE url = '%s'", $this->getTableNameFull(), $url );
				$num = $this->wpdb_get_var( $query );
				if ($num > 0 ) {
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
				
				$data = json_encode( $data );

				if (! $this->isExist($url)) {
					$query = sprintf("INSERT INTO `%s` (`id`, `time_update`, `expire`, `url`, `data`) VALUES (NULL, NOW(), '%s', '%s', '%s')", 
								$this->getTableNameFull(),
								$this->getExpiration(),
								$url,
								$data
							);
				} else {
					$query = sprintf("UPDATE `%s` SET `time_update` = NOW(), `data` = '%s' WHERE url = '%s'", 
								$this->getTableNameFull(),
								$data,
								$url
							);
				}
				$this->wpdb_query($query);

				if ($this->isExist($url)) {
					//TODO: No se controla si UPDATE ha funcionado bien.
					$return_data = true;
				}
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
					$query = sprintf("SELECT data FROM `%s` WHERE url = '%s'",  $this->getTableNameFull(), $url );
					$return_data = $this->wpdb_get_var( $query );
					$return_data = json_decode( $return_data );
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
					$query = sprintf("DELETE FROM `%s` WHERE url = '%s'",  $this->getTableNameFull(), $url );
					$return_data = $this->wpdb_query( $query );
					if (! $this->isExist($url)) {
						$return_data = true;
					}
				}
			}
		}
		return $return_data;
	}

}