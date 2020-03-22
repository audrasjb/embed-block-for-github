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


class Cache_Store_Table extends Cache_Store_Base implements ICache_Store {

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

		if ( ! is_null($table_name) ) {
			$this->table_cache = $table_name;
		} else {
			$this->table_cache = "cache_store";
			if ( ! is_null($parent) ) {
				$this->table_cache = $this->fix_table_name( $parent->get_name() ) . "_" . $this->table_cache;
			}
		}
	}

	/**
	 * 
	 * 
	 */
	private function fix_table_name($table_name) {
		return str_ireplace("-", "_", $table_name );
	}

	/**
	 * Get the name of the option where the database version is saved.
	 * 
	 * @return string 
	 */
	public function get_option_name_to_cache_version() {
		return $this->get_table_name() . "_db_version";
	}

	/**
	 * 
	 * 
	 */
	public function get_table_name_full() {
		global $wpdb;
		return $this->get_table_name($wpdb->prefix);
	}

	/**
	 * 
	 * 
	 */
	public function get_table_name($prefix = "") {
		$return_data = $this->table_cache;
		if ( ! empty($prefix) ) {
			$return_data = $prefix . $return_data;
		}
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	public function set_table_name($name) {
		$this->table_cache = $name;
	}

	/**
	 * 
	 * 
	 */
	private function create_table() {
		$return_data = false;
		if (! empty( $this->get_table_name() ) ) {
			if ( ! $this->is_exist_table() ) {
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
						$this->get_table_name_full(),
						$wpdb->collate );

				if( ! function_exists('dbDelta') ) {
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				}
				dbDelta( $sql );
				$return_data = $this->is_exist_table() ;
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
	private function wpdb_get_results($str_query) {
		global $wpdb;
		return $wpdb->get_results( $str_query );
	}

	/**
	 * 
	 * 
	 */
	private function drop_table() {
		if ($this->is_exist_table()) {
			$query = sprintf("DROP TABLE IF EXISTS `%s`", $this->get_table_name_full() );
			$this->wpdb_query($query);
		}
		return (! $this->is_exist_table() );
	}

	/**
	 * 
	 * 
	 */
	private function truncate_table() {
		$return_data  = false;
		if ( $this->is_exist_table() ) {
			$query = sprintf("TRUNCATE TABLE `%s`", $this->get_table_name_full() );
			$this->wpdb_query( $query );
			if ( 0 === $this->count() ) {
				$return_data = true;
			}
		}
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	private function is_exist_table($table_name = null) {
		if ( is_null($table_name) ) {
			$table_name = $this->get_table_name_full();
		}
		
		/**
		 * FIX: The underscore is a wild card so we have to change "_" to "\ _" to avoid false positives. 
		 * 		False positive example: "cache_store" = "cache-store"
		 * FIX: El guion bajo es un comodin por lo que tenemos que cambiar "_" por "\_" para evitar falsos positivos. 
		 * 		Ejemplo falso positivo: "cache_store" = "cache-store"
		 */
		$table_name_fix =  str_ireplace("_", "\_", $table_name );

		$query = sprintf("SHOW TABLES LIKE '%s'", $table_name_fix );
		if ( $this->wpdb_get_var($query) === $table_name ) {
			return true;
		}
		return false;
	}

	/**
	 * 
	 * 
	 */
	private function get_id_by_URL($url = null) {
		if ( is_null($url) ) {
			$url = $this->get_URL();
		}
		$query = sprintf("SELECT id FROM `%s` WHERE url = '%s'", $this->get_table_name_full(), $url );
		$return_data = $this->wpdb_get_var( $query );
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	private function is_exist_data_expred() {
		$return_data = false;
		if ( $this->is_exist_table() ) {
			$query = sprintf("SELECT count(*) FROM `%s` WHERE `expire` > 0 and  now() > TIMESTAMPADD(SECOND, expire, time_update)", $this->get_table_name_full() );
			$count = $this->wpdb_get_var( $query );
			if ( $count > 0 ) {
				$return_data = true;
			}
		}
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	private function clean_expired_data() {
		$return_data = false;
		if ( $this->is_exist_table() ) {
			$table_name = $this->get_table_name_full();
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
		if ($this->is_exist_table()) {
			$query = sprintf("SELECT COUNT(*) FROM `%s`", $this->get_table_name_full() );
			$return_data = $this->wpdb_get_var( $query );
		}
		return settype($return_data, "integer");
	}

	/**
	 * 
	 */
	public function is_cache_exist() {
		return $this->is_exist_table();
	}

	/**
	 * 
	 */
	public function clean_cache () {
		if ( ! $this->is_exist_table() ) {
			$this->create_table();
		}
		if ( $this->count() > 0 ) {
			$this->truncate_table();
		}
	}

	/**
	 * 
	 */
	public function control_cache_storage() {
		if ( ! $this->check_cache_version() ) {
			if ( $this->is_exist_table() ) {
				$this->drop_table();
			}
			if ( $this->create_table() ) {
				$opt = $this->get_option_name_to_cache_version();
				$ver = $this->get_version();
				if ( get_option( $opt ) !== false ) {
					update_option( $opt, $ver );
				} else {
					add_option( $opt, $ver );
				}
			}
		} else {
			if ( $this->is_exist_data_expred() ) {
				$this->clean_expired_data();
			}
		}
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
				$query = sprintf("SELECT COUNT(*) FROM `%s` WHERE url = '%s'", $this->get_table_name_full(), $url );
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
	public function set($data, $url = null) {
		$this->control_cache_storage();

		$return_data = false;
		if ( is_null($url) ) {
			$url = $this->get_URL();
		}
		if (! $this->is_URL_null($url)) {
			if ($this->get_status()) {
				
				$data = json_encode( $data );

				if (! $this->is_exist($url)) {
					$query = sprintf("INSERT INTO `%s` (`id`, `time_update`, `expire`, `url`, `data`) VALUES (NULL, NOW(), '%s', '%s', '%s')", 
								$this->get_table_name_full(),
								$this->get_expiration(),
								$url,
								$data
							);
				} else {
					$query = sprintf("UPDATE `%s` SET `time_update` = NOW(), `data` = '%s' WHERE url = '%s'", 
								$this->get_table_name_full(),
								$data,
								$url
							);
				}
				$this->wpdb_query($query);

				if ( $this->is_exist($url) ) {
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
		$this->control_cache_storage();

		$return_data = "";
		if ( is_null($url) ) {
			$url = $this->get_URL();
		}
		if ( ! $this->is_URL_null($url) ) {
			if ( $this->get_status() ) {
				if ( $this->is_exist($url) ) {
					$query = sprintf("SELECT data FROM `%s` WHERE url = '%s'",  $this->get_table_name_full(), $url );
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
	public function delete($force = false, $url = null) {
		$this->control_cache_storage();

		$return_data = false;
		if ( is_null($url) ) {
			$url = $this->get_URL();
		}
		if ( ! $this->is_URL_null($url) ) {
			if ( ( $this->get_status() ) || ( $force ) ) {
				if ( $this->is_exist($url) ) {
					$query = sprintf("DELETE FROM `%s` WHERE url = '%s'",  $this->get_table_name_full(), $url );
					$return_data = $this->wpdb_query( $query );
					if ( ! $this->is_exist($url) ) {
						$return_data = true;
					}
				}
			}
		}
		return $return_data;
	}

	/**
	 * 
	 */
	public function is_exist_id($id = null) {
		$return_data = false;
		if ( ! is_null($id) ) {
			$id = trim( $id );
			if ( ! empty( $id ) ) {
				$query = sprintf("SELECT count(*) FROM `%s` WHERE `id` = %s", $this->get_table_name_full(), $id );
				$count = $this->wpdb_get_var( $query );
				if ($count > 0) {
					$return_data = true;
				}
			}
		}
		return $return_data;
	}

	/**
	 * 
	 */
	public function remove_id($id = "") {
		$return_data = false;
		if ( $this->is_exist_id($id) ) {
			$id = trim( $id );
			$query = sprintf("DELETE FROM `%s` WHERE `id` = %s", $this->get_table_name_full(), $id );
			$this->wpdb_query($query);

			if ( ! $this->is_exist_id($id) ) { 
				$return_data = true;
			}
		}
		return $return_data;
	}

	/**
	 * 
	 */
	public function get_all_list() {
		$query = sprintf("SELECT id, time_update, TIMESTAMPADD(SECOND, expire, time_update) as time_expire, expire, url FROM `%s`", $this->get_table_name_full() );
		return $this->wpdb_get_results($query);
	}

}