<?php

namespace EmbedBlockForGithub\Cache;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Transient {
	public $id = "";
	public $enabled = true;

	private static $instance;

	public $parent = null;

	public static function get_instance($parent = null) {
		if ( is_null (self::$instance ) ) {
			self::$instance = new self;
		}
		if ( ! is_null( $parent ) ) {
			self::$instance->parent = $parent;
		}
		return self::$instance;
	}
	
	public function __construct() {
		$this->parent = (object)array();
	}

	function isSetId() {
		return (! empty( $this->getId() ) );
	}

	public function setId($prefix = "", $postfix = "") {
		$plugin_version = $this->parent->getPluginData('Version');
		$id = "_ebg_repository_".$plugin_version."_";
		if (! empty($prefix)) 	{ $id = "_".$prefix.$id; }
		if (! empty($postfix)) 	{ $id = $id.$postfix."_"; }
		$this->id = $id;
		return $this->getId();
	}

	public function getId() {
		return $this->id;
	}



	function isExist() {
		if ( ( $this->enabled ) && ( $this->isSetId() ) && (  get_transient($this->id) ) ) {
			return true;
		} 
		return false;
	}

	function get() {
		if ( ( $this->enabled ) && ( $this->isSetId() ) ) {
			return json_decode( get_transient( $this->id ) );
		}
	}

	function set ($data, $expiration = 0) {
		if ( ( $this->enabled ) && ( $this->isSetId() ) ) {
			set_transient( $this->id, json_encode( $data ) , $expiration);
			return True;
		}
		return False;
	}

	function delete ($force = false) {
		if ( ( $this->enabled ) || ( $force ) ) {
			if ( $this->isExist() ) {
				delete_transient( $this->id );
			}
		}

	}

}
