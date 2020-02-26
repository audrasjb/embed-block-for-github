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

    private $list_options;

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
        $this->list_options = array();

		if (! is_null($parent)) {
			$this->parent = $parent;
        }
    }

    /**
     * Number of options in the list.
     * 
     * @return int
     */
    public function count () {
        return count($list_options);
    }

    /**
     * Add a new option to the list of options.
     * 
     * @param string $name          Option name to check.
     * @param string $type          Option type (string, boolean, etc..)
     * @param string $default       Default value return if value not set.
     * @param bool   $only_admin    Indicates that this administration options for 
     *                              example a tokern or a sensitive security data.
     * 
     */
    public function addOption($name, $type, $default = null, $only_admin = false) {
        $this->list_options[$name] = array (
            'type' => $type,
            'default' => $default,
            'only_admin' => $only_admin,
            'full_name' => $this->getNameOptionFull($name),
            'register' => false,
        );
    }

    /**
     * Remove an option from the list of options.
     * 
     * @param string $option        Option name
     * @return bool                 True ok, False not exist option.
     */
    public function delOption($option) {
        if ( $this->isExistOption($option) ) {
            if ($this->list_options[$option]['register']) {
                unregister_setting(
                    $this->group,
                    $this->getNameOptionFull($key)
                );
            }
            unset($this->list_options[$option]);
        } else {
            return false;
        }
        return true;
    }


    /**
     * Check if the option we specify is in the list of options.
     * 
     * @param string 
     * @return bool True exists, False not exists.
     */
    public function isExistOption($option) {
        if (array_key_exists($option, $this->list_options)) {
            return true;
        }
        return false;
    }


    /**
     * Register in wordpress all the options we have added.
     * https://developer.wordpress.org/reference/functions/register_setting/
     */
    public function registerSettings() {
        foreach ($this->list_options as $key => &$val) {
            if ($val['register']) {
                continue;
            }
            register_setting(
                $this->group,
                $val['full_name'],
                array(
                    'type' =>  $val['type'],
                    'default' => $val['default'],
                )
            );
            $val['register'] = true;
        }
    }

    /**
     * Get an array with the options we have defined.
     * 
     * @param bool $all     Add all options to the array, even those marked "only_admin".
     * @param bool $html    Determine if the "esc_attr" function is applied to the text 
     *                      to be returned, by default it is "true".
     * @return array        array with values saved
     */
    public function getOptions($all = false, $html = true){
        $return_data = array();
        foreach ($this->list_options as $key => $val) {
            if ( (! $all) && ($val['only_admin']) ) {
                continue;
            }
            $return_data[$key] = $this->getOption($key, $html);
        }
		return $return_data;
    }
    
    /**
     * Gets the value of the option we request.
     * https://developer.wordpress.org/reference/functions/get_option/
     * 
     * @param string $option        Option name
     * @param bool   $html          Determine if the "esc_attr" function is applied 
     *                              to the text to be returned, by default it is "true".
     * @return mixed                value saved
     */
    public function getOption($option, $html = true) {
        if ( $this->isExistOption($option) ) {
            $data_option = $this->list_options[$option];
            $return_data = get_option($data_option['full_name'], $data_option['default']);
            if ($html) {
                $return_data = esc_attr( $return_data );
            }
        } else {
            $return_data = null;
        }
        return $return_data;
    }
    
    /**
     * Get the full name of the option with the prefix added.
     * 
     * @param string  $option       Option name
     * @return string               Full option name
     */
    public function getNameOptionFull($option) {
        return $this->prefix.'_-_'.$option;
    }
}