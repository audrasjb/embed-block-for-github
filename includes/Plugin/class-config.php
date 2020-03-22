<?php
/**
 * 
 * Author:            VSC55
 * Author URI:        https://github.com/vsc55/embed-block-for-github
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 */

namespace EmbedBlockForGithub\Plugin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Config {

    private $parent = null;
    private static $instance;

    public $prefix  = "";
    public $group   = "";

    private $list_options;

    public static function get_instance($parent = null) {
		if ( is_null (self::$instance ) ) {
			self::$instance = new self ($parent);
        }
		return self::$instance;
	}

    protected function __construct($parent = null) {
        $this->list_options = array();
        $this->parent = (object)array();
		if ( ! is_null( $parent ) ) {
			$this->parent = $parent;
        }
        add_action( 'admin_init', array( $this, 'register_options' ) );
    }

    /**
     * Number of options in the list.
     * 
     * @return int
     */
    public function count() {
        return count( $list_options );
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
    public function add_option($name, $type, $default = null, $only_admin = false) {
        $this->list_options[$name] = array (
            'type'          => $type,
            'default'       => $default,
            'only_admin'    => $only_admin,
            'full_name'     => $this->get_name_option_full($name),
            'register'      => false,
        );
    }

    /**
     * Remove an option from the list of options.
     * 
     * @param string $option        Option name
     * @return bool                 True ok, False not exist option.
     */
    public function del_option($option) {
        $return_data = false;
        if ( $this->is_exist_option( $option ) ) {
            if ( $this->list_options[$option]['register'] ) {
                unregister_setting(
                    $this->group,
                    $this->get_name_option_full($key)
                );
            }
            unset( $this->list_options[$option] );
            $return_data = true;
        }
        return $return_data;
    }


    /**
     * Check if the option we specify is in the list of options.
     * 
     * @param string 
     * @return bool True exists, False not exists.
     */
    public function is_exist_option($option) {
        if ( array_key_exists($option, $this->list_options) ) {
            return true;
        }
        return false;
    }


    /**
     * Register in wordpress all the options we have added.
     * https://developer.wordpress.org/reference/functions/register_setting/
     */
    public function register_options() {
        foreach ( $this->list_options as $key => &$val ) {
            if ( $val['register'] ) {
                continue;
            }
            register_setting(
                $this->group,
                $val['full_name'],
                array(
                    'type'      => $val['type'],
                    'default'   => $val['default'],
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
    public function get_options($all = false, $html = true) {
        $return_data = array();
        foreach ( $this->list_options as $key => $val ) {
            if ( (! $all) && ( $val['only_admin']) ) {
                continue;
            }
            $return_data[$key] = $this->get_option($key, $html);
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
    public function get_option($option, $html = true) {
        if ( $this->is_exist_option($option) ) {
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
    public function get_name_option_full($option) {
        return $this->prefix . '_-_' . $option;
    }
}