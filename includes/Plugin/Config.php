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
     * Gets the value of the option we request.
     * https://developer.wordpress.org/reference/functions/get_option/
     * 
     * @param string $option_name   Name option
     * @param mixed  $default       Value to use in case the option does not exist. 
     * @return mixed                value saved
     */
    public function get_option ($option_name, $default = false) {
        return get_option($this->get_option_full($option_name), $default);
    }

    /**
     * Get the value of the option we pass and prepare it with the esc_attr function.
     * 
     * @param string $option_name   Name option
     * @param mixed  $default       Value to use in case the option does not exist. 
     * @return mixed                value saved
     */
    public function get_option_html($option_name, $default = false) {
        return esc_attr( $this->get_option($option_name, $default) );
    }

    /**
     * Get the full name of the option with the prefix added.
     * 
     * @param string  $option_name   Name option
     * @return string                Full name option
     */
    public function get_option_full($option_name) {
        return $this->prefix.'_-_'.$option_name;
    }
}