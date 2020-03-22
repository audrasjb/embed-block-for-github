<?php
/**
 * Uninstall Embed Block for GitHub
 *
 * @link              https://github.com/vsc55/embed-block-for-github
 * @since             0.1
 * @package           Embed Block for GitHub
 * @subpackage        Uninstall
 * 
 * Author:            VSC55
 * Author URI:        https://github.com/vsc55/embed-block-for-github
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Delete options
$prefix_options = 'embed-block-for-github_-_';
$array_options = array(
    'darck_theme',
    'icon_type_source',
    'api_cache_disable',
    'api_cache_expire',
    'api_access_token',
    'api_access_token_user',
);
foreach ($array_options as $option) {
	if (get_option($prefix_options.$option)) {
        delete_option($prefix_options.$option);
    }
}
unset($array_options);
unset($prefix_options);


// Delete transients
$prefix_transients = '_ebg_repository_';
$array_transients = array(
    'cache_version',
    'cache_storage'
);
foreach ($array_transients as $transient) {
	delete_transient($prefix_transients.$transient);
}
unset($array_transients);
unset($prefix_transients);


// Delete tables in database
global $wpdb;
$table_name = 'embed_block_for_github_cache_store';
$table_name_full = $wpdb->prefix .$table_name;
$wpdb->query("DROP TABLE IF EXISTS `$table_name`");
delete_option($table_name.'_db_version');