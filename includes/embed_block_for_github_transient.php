<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class embed_block_for_github_transient {
	public $id;
	public $enabled;

	public function __construct($id = "", $enabled = true) {
		$this->id = $id;
		$this->enabled = $enabled;
	}

	function isSetId() {
		return (! empty( $this->id ) );
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

	function set ($data) {
		if ( ( $this->enabled ) && ( $this->isSetId() ) ) {
			set_transient( $this->id, json_encode( $data ) );
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
