<?php
/**
 * Uninstall Embed Block for GitHub
 *
 * @link              https://github.com/vsc55/embed-block-for-github
 * @since             0.1
 * @package           Embed Block for GitHub
 * @subpackage        Uninstall
 * 
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
