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

abstract class PageBase {

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
		if (! is_null($parent)) {
			$this->parent = $parent;
        }
        
        $this->icon_url = "";
        $this->page_title = "";
        $this->menu_title = "";
        $this->sub_menu_title = "";
        $this->capability = "manage_options";
        $this->menu_slug = "";
        $this->parent_slug = "";
        $this->position = null;
        $this->function = '';

        add_action( 'admin_menu', array( $this, 'addMenuItem' ) );
    }

    public function addMenuItem() {

        $add_menu = $this->inMainMenu();
        $add_submenu = $this->inSubMenu();

        if ( $add_menu ) {
            // https://developer.wordpress.org/reference/functions/add_menu_page/
            add_menu_page( 
                $this->getPageTitle(),
                $this->getMenuTitle(),
                $this->getCapability(),
                $this->getMenuSlug(),
                $this->getFunction(),
                $this->getIconUrl(),
                $this->getPosition()
            );
        }
        if ( $add_submenu ) {
            // https://developer.wordpress.org/reference/functions/add_submenu_page/
            if ( ($add_menu) && ( $add_submenu ) ) {
                $parent_slug = $this->getMenuSlug();
                $submenu_title = $this->getSubMenuTitle();
            } else {
                $parent_slug = $this->getParentSlug();
                $submenu_title = $this->getMenuTitle();
            }
            add_submenu_page(
                $parent_slug,
                $this->getPageTitle(),
                $submenu_title,
                $this->getCapability(),
                $this->getMenuSlug(),
                $this->getFunction(),
                $this->getPosition()
            );
        }
    }

    /**
     * 
     */
    public function getNameParent(){
        return $this->parent->getName();
    }

    /**
     * 
     */
    public function inMainMenu() {
        $return_data = false;
        if ( empty( $this->getParentSlug() ) ) { 
            $return_data = true;
        }
        return $return_data;
    }

    /**
     * 
     */
    public function inSubMenu () {
        $return_data = false;
        if ( ! empty( $this->getParentSlug() ) ) { 
            $return_data = true;
        }
        if ( ! empty( $this->getSubMenuTitle() ) ) {
            $return_data = true;
        }
        return $return_data;
    }

    /**
     * 
     */
    public function getIconUrl() {
        return $this->icon_url;
    }

    /**
     * 
     */
    public function setIconUrl($new_icon_url) {
        $this->icon_url = $new_icon_url;
    }


    /**
     * 
     */
    public function getPageTitle() {
        return $this->page_title;
    }

    /**
     * 
     */
    public function setPageTitle($new_title) {
        $this->page_title = $new_title;
    }

    /**
     * 
     */
    public function getMenuTitle() {
        return $this->menu_title;
    }

    /**
     * 
     */
    public function setMenuTitle($new_title) {
        $this->menu_title = $new_title;
    }

    /**
     * 
     */
    public function getSubMenuTitle() {
        return $this->sub_menu_title;
    }

    /**
     * 
     */
    public function setSubMenuTitle($new_sub_menu_title) {
        $this->sub_menu_title = $new_sub_menu_title;
    }

    /**
     * 
     */
    public function getCapability() {
        return $this->capability;
    }

    /**
     * 
     */
    public function setCapability($new_capability) {
        $this->capability = $new_capability;
    }

    /**
     * 
     */
    public function getMenuSlug() {
        return $this->menu_slug;
    }

    /**
     * 
     */
    public function setMenuSlug($new_menu_slug) {
        $this->menu_slug = $new_menu_slug;
    }

    /**
     * 
     */
    public function getParentSlug() {
        return $this->parent_slug;
    }

    /**
     * 
     */
    public function setParentSlug($new_parent_slug) {
        $this->parent_slug = $new_parent_slug;
    }

    /**
     * 
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * 
     */
    public function setPosition($new_position) {
        $this->position = $new_position;
    }

    /**
     * 
     */
    public function getFunction() {
        return $this->function;
    }

    /**
     * 
     */
    public function setFunction($new_function) {
        $this->function = $new_function;
    }
}