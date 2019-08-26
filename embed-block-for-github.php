<?php
/**
 * @link              https://jeanbaptisteaudras.com
 * @since             0.1
 * @package           GitHub Embed Block
 *
 * Plugin Name:       GitHub Embed Block
 * Plugin URI:        https://jeanbaptisteaudras.com/embed-block-for-github-gutenberg-wordpress/
 * Description:       Easily embed GitHub repositories in Gutenberg Editor.
 * Version:           0.1
 * Author:            audrasjb
 * Author URI:        https://jeanbaptisteaudras.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       embed-block-for-github
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function ebg_embed_repository( $attributes ) {

	$github_url = trim( $attributes['github_url'] );

	if ( '' === trim( $github_url ) ) {
		$content = '<p>Use the Sidebar to add the URL of the GitHub Repository to embed.</p>';
	} else {
		if ( filter_var( $github_url, FILTER_VALIDATE_URL ) ) {
			if ( strpos( $github_url, 'https://github.com/' ) === 0 ) {
				if ( get_transient( '_ebg_repository_' . sanitize_title_with_dashes( $github_url ) ) ) {
					$data = json_decode( get_transient( '_ebg_repository_' . sanitize_title_with_dashes( $github_url ) ) );
					$content = '
<div class="ebg-br-wrapper">
	<img class="ebg-br-header-logo" src="' . plugin_dir_url( __FILE__ ) . '/images/github.svg" alt="' . esc_html__( 'GitHub Card', 'embed-block-for-github' ) . '" />
	<div class="ebg-br-avatar">
		<img class="ebg-br-header-avatar" src="' . $data->owner->avatar_url . '" alt="" width="150" height="150" />
	</div>
	<div class="ebg-br-main">
		<p class="ebg-br-title">
			<strong><a target="_blank" rel="noopener noreferrer" href="' . $data->html_url . '">' . $data->name . ' <span class="screen-reader-text">(' . esc_html__( 'this link opens in a new window', 'embed-block-for-github' ) . ')</span></a></strong>
			<em>by <a target="_blank" rel="noopener noreferrer" href="' . $data->owner->html_url . '">' . $data->owner->login . ' <span class="screen-reader-text">(' . esc_html__( 'this link opens in a new window', 'embed-block-for-github' ) . ')</span></a></em>
		</p>
		<p class="ebg-br-description">' . $data->description . '</p>
		<p class="ebg-br-footer">
			<span class="ebg-br-subscribers">
				<img src="' . plugin_dir_url( __FILE__ ) . '/images/subscribe.svg" alt="" /> 
				' . $data->subscribers_count . ' ' . esc_html__( 'Subscribers', 'embed-block-for-github' ) . '
			</span>
			<span class="ebg-br-watchers">
				<img src="' . plugin_dir_url( __FILE__ ) . '/images/watch.svg" alt="" /> 
				' . $data->watchers_count . ' ' . esc_html__( 'Watchers', 'embed-block-for-github' ) . '
			</span>
			<span class="ebg-br-forks">
				<img src="' . plugin_dir_url( __FILE__ ) . '/images/fork.svg" alt="" /> 
				' . $data->forks_count . ' ' . esc_html__( 'Forks', 'embed-block-for-github' ) . '
			</span>
			<a target="_blank" rel="noopener noreferrer" class="ebg-br-link" href="' . $data->html_url . '">' . esc_html__( 'Check out this repository on GitHub.com', 'embed-block-for-github' ) . ' <span class="screen-reader-text">(' . esc_html__( 'this link opens in a new window', 'embed-block-for-github' ) . ')</span></a>
		</p>
	</div>
</div>
					';
				} else {
					$slug = str_replace( 'https://github.com/', '', $github_url );
					$request = wp_remote_get( 'https://api.github.com/repos/' . $slug );
					$body = wp_remote_retrieve_body( $request );
					$data = json_decode( $body );
					if ( ! is_wp_error( $response ) ) {
						set_transient( '_ebg_repository_' . sanitize_title_with_dashes( $github_url ), json_encode( $data ) );
						$content = '
<div class="ebg-br-wrapper">
	<img class="ebg-br-header-logo" src="' . plugin_dir_url( __FILE__ ) . '/images/github.svg" alt="' . esc_html__( 'GitHub Card', 'embed-block-for-github' ) . '" />
	<div class="ebg-br-avatar">
		<img class="ebg-br-header-avatar" src="' . $data->owner->avatar_url . '" alt="" width="150" height="150" />
	</div>
	<div class="ebg-br-main">
		<p class="ebg-br-title">
			<strong><a target="_blank" rel="noopener noreferrer" href="' . $data->html_url . '">' . $data->name . ' <span class="screen-reader-text">(' . esc_html__( 'this link opens in a new window', 'embed-block-for-github' ) . ')</span></a></strong>
			<em>by <a target="_blank" rel="noopener noreferrer" href="' . $data->owner->html_url . '">' . $data->owner->login . ' <span class="screen-reader-text">(' . esc_html__( 'this link opens in a new window', 'embed-block-for-github' ) . ')</span></a></em>
		</p>
		<p class="ebg-br-description">' . $data->description . '</p>
		<p class="ebg-br-footer">
			<span class="ebg-br-subscribers">
				<img src="' . plugin_dir_url( __FILE__ ) . '/images/subscribe.svg" alt="" /> 
				' . $data->subscribers_count . ' ' . esc_html__( 'Subscribers', 'embed-block-for-github' ) . '
			</span>
			<span class="ebg-br-watchers">
				<img src="' . plugin_dir_url( __FILE__ ) . '/images/watch.svg" alt="" /> 
				' . $data->watchers_count . ' ' . esc_html__( 'Watchers', 'embed-block-for-github' ) . '
			</span>
			<span class="ebg-br-forks">
				<img src="' . plugin_dir_url( __FILE__ ) . '/images/fork.svg" alt="" /> 
				' . $data->forks_count . ' ' . esc_html__( 'Forks', 'embed-block-for-github' ) . '
			</span>
			<a target="_blank" rel="noopener noreferrer" class="ebg-br-link" href="' . $data->html_url . '">' . esc_html__( 'Check out this repository on GitHub.com', 'embed-block-for-github' ) . ' <span class="screen-reader-text">(' . esc_html__( 'this link opens in a new window', 'embed-block-for-github' ) . ')</span></a>
		</p>
	</div>
</div>
						';
					} else {
						$content = '<p>' . esc_html__( 'No information available. Please check your URL.', 'embed-block-for-github' ) . '</p>';
					}
				}
			} else {
				$content = '<p>' . esc_html__( 'Use the Sidebar to add the URL of the GitHub Repository to embed.', 'embed-block-for-github' ) . '</p>';
			}
		} else {
			$content = '<p>' . esc_html__( 'Use the Sidebar to add the URL of the GitHub Repository to embed.', 'embed-block-for-github' ) . '</p>';
		}
	}

	return $content;
}
function ebg_enqueue_scripts() {
	wp_register_script(
		'ebg-repository-editor',
		plugins_url( 'repository-block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-editor' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'repository-block.js' )
	);
	wp_register_style(
		'ebg-repository-editor',
		plugins_url( 'repository-block.css', __FILE__ ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'repository-block.css' )
	);
	wp_register_style(
		'ebg-repository',
		plugins_url( 'repository-block.css', __FILE__ ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'repository-block.css' )
	);
	register_block_type( 'embed-block-for-github/repository', array(
		'editor_script'   => 'ebg-repository-editor',
		'editor_style'    => 'ebg-repository-editor',
		'style'           => 'ebg-repository',
		'render_callback' => 'ebg_embed_repository',
		'attributes'      => array(
			'github_url' => array( 'type' => 'string' ),
		),
	) );
}
add_action( 'init', 'ebg_enqueue_scripts' );
