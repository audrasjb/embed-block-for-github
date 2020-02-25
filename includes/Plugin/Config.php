<?php

namespace EmbedBlockForGithub\Plugin;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Config {

    public $parent = null;
    private static $instance;

    public $prefix = "";
    public $group = "";

    public static function get_instance($parent = null) {
		if ( is_null (self::$instance ) ) {
			self::$instance = new self;
		}
		if ( ! is_null( $parent ) ) {
			self::$instance->parent = $parent;
		}
		return self::$instance;
	}

    protected function __construct($parent = null) {
        $this->parent = (object)array();
		if (! is_null($parent)) {
			$this->parent = $parent;
        }
    }

    /**
     * https://developer.wordpress.org/reference/functions/register_setting/
     * 
     */
    public function register_setting($option_name, $args) {
		register_setting( $this->group, $this->get_option_full($option_name), $args);
    }

    /**
     * https://developer.wordpress.org/reference/functions/get_option/
     * 
     */
    public function get_option ($option_name, $default = false) {
        return get_option($this->get_option_full($option_name), $default);
    }

    /**
     * 
     * 
     */
    public function get_option_html($option_name, $default = false) {
        return esc_attr( $this->get_option($option_name, $default) );
    }

    /**
     * 
     * 
     */
    public function get_option_full($option_name) {
        return $this->prefix.'_-_'.$option_name;
    }
}