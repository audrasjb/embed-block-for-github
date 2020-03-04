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

interface IPage {

    public function addMenuItem();

    public function createPage();

    public function init_wp_register();
}