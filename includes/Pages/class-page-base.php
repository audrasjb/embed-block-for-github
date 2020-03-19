<?php
/**
 * 
 * Author:            VSC55
 * Author URI:        https://github.com/vsc55/embed-block-for-github
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 */
namespace EmbedBlockForGithub\Pages;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class Page_Base {

    public $parent = null;

    private $icon_url;
    private $page_title;
    private $menu_title;
    private $sub_menu_title;
    private $capability;
    private $menu_slug;
    private $parent_slug;
    private $position;
    private $function;

    public function __construct($parent = null) {

        $this->parent = (object)array();
		if ( ! is_null($parent) ) {
			$this->parent = $parent;
        }
        
        $this->icon_url         = "";
        $this->page_title       = "";
        $this->menu_title       = "";
        $this->sub_menu_title   = "";
        $this->capability       = "manage_options";
        $this->menu_slug        = "";
        $this->parent_slug      = "";
        $this->position         = null;
        $this->function         = '';

        add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
    }

    /**
     * 
     */
    public function add_action_all () {
        $this->add_action_init();
        $this->add_action_admin_init();
        $this->add_action_wp_enqueue_scripts();
        $this->add_action_admin_enqueue_scripts();
    }

    /*
	public function action_init() { }
	public function action_admin_init() { }
	public function action_wp_enqueue_scripts() { }
	public function action_admin_enqueue_scripts () { }
	*/

    /**
     * 
     */
    public function add_action_init () {
        return $this->add_action('init');
    }

    /**
     * 
     */
    public function add_action_admin_init () {
        return $this->add_action('admin_init');
    }

    /**
     * 
     */
    public function add_action_wp_enqueue_scripts() {
        return $this->add_action('wp_enqueue_scripts');
    }

    /**
     * 
     */
    public function add_action_admin_enqueue_scripts() {
        return $this->add_action('admin_enqueue_scripts');
    }

    /**
     * 
     */
    private function add_action($action) {
        if ( method_exists ($this, 'action_'.$action) ) {
            add_action($action, array($this, 'action_'.$action) );
            return true;
        }
        return false;
    }
    
    /**
     * 
     */
    public function wp_create_nonce($id) {
        if( ! function_exists('wp_create_nonce') ){
            require_once( ABSPATH . 'wp-includes/pluggable.php' );
        }
        return wp_create_nonce($id);
    }

    /**
     * 
     */
    public function add_menu_item() {

        $add_menu = $this->in_main_menu();
        $add_submenu = $this->in_sub_menu();

        if ( $add_menu ) {
            // https://developer.wordpress.org/reference/functions/add_menu_page/
            add_menu_page( 
                $this->get_page_title(),
                $this->get_menu_title(),
                $this->get_capability(),
                $this->get_menu_slug(),
                $this->get_function(),
                $this->get_icon_URL(),
                $this->get_position()
            );
        }
        if ( $add_submenu ) {
            // https://developer.wordpress.org/reference/functions/add_submenu_page/
            if ( ( $add_menu ) && ( $add_submenu ) ) {
                $parent_slug = $this->get_menu_slug();
                $submenu_title = $this->get_sub_menu_title();
            } else {
                $parent_slug = $this->get_parent_slug();
                $submenu_title = $this->get_menu_title();
            }
            add_submenu_page(
                $parent_slug,
                $this->get_page_title(),
                $submenu_title,
                $this->get_capability(),
                $this->get_menu_slug(),
                $this->get_function(),
                $this->get_position()
            );
        }
    }

    /**
     * 
     */
    public function get_name_parent() {
        return $this->parent->get_name();
    }

    /**
     * 
     */
    public function in_main_menu() {
        $return_data = false;
        if ( empty( $this->get_parent_slug() ) ) { 
            $return_data = true;
        }
        return $return_data;
    }

    /**
     * 
     */
    public function in_sub_menu() {
        $return_data = false;
        if ( ! empty( $this->get_parent_slug() ) ) { 
            $return_data = true;
        }
        if ( ! empty( $this->get_sub_menu_title() ) ) {
            $return_data = true;
        }
        return $return_data;
    }

    /**
     * 
     */
    public function get_icon_URL() {
        return $this->icon_url;
    }

    /**
     * 
     */
    public function set_icon_URL($new_icon_url) {
        $this->icon_url = $new_icon_url;
    }


    /**
     * 
     */
    public function get_page_title() {
        return $this->page_title;
    }

    /**
     * 
     */
    public function set_page_title($new_title) {
        $this->page_title = $new_title;
    }

    /**
     * 
     */
    public function get_menu_title() {
        return $this->menu_title;
    }

    /**
     * 
     */
    public function set_menu_title($new_title) {
        $this->menu_title = $new_title;
    }

    /**
     * 
     */
    public function get_sub_menu_title() {
        return $this->sub_menu_title;
    }

    /**
     * 
     */
    public function set_sub_menu_title($new_sub_menu_title) {
        $this->sub_menu_title = $new_sub_menu_title;
    }

    /**
     * 
     */
    public function get_capability() {
        return $this->capability;
    }

    /**
     * 
     */
    public function set_capability($new_capability) {
        $this->capability = $new_capability;
    }

    /**
     * 
     */
    public function get_menu_slug() {
        return $this->menu_slug;
    }

    /**
     * 
     */
    public function set_menu_slug($new_menu_slug) {
        $this->menu_slug = $new_menu_slug;
    }

    /**
     * 
     */
    public function get_parent_slug() {
        return $this->parent_slug;
    }

    /**
     * 
     */
    public function set_parent_slug($new_parent_slug) {
        $this->parent_slug = $new_parent_slug;
    }

    /**
     * 
     */
    public function get_position() {
        return $this->position;
    }

    /**
     * 
     */
    public function set_position($new_position) {
        $this->position = $new_position;
    }

    /**
     * 
     */
    public function get_function() {
        return $this->function;
    }

    /**
     * 
     */
    public function set_function($new_function) {
        $this->function = $new_function;
    }

}