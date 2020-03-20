<?php
/**
 * 
 * Author:            VSC55
 * Author URI:        https://github.com/vsc55/embed-block-for-github
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 */
namespace EmbedBlockForGithub\Pags\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once ( __DIR__ . '/../includes/Pages/interface-page.php' );
require_once ( __DIR__ . '/../includes/Pages/class-page-base.php' );

use EmbedBlockForGithub\Pages\IPage;
use EmbedBlockForGithub\Pages\Page_Base;

class Pag_Admin_API_GitHub_Rate extends Page_Base implements IPage {

	private $js_acction;

	public function __construct($parent = null, $auto_action = false) {
		parent::__construct( $parent );
		$this->set_parent_slug 	( 'embed-block-for-github-admin' );
		$this->set_page_title 	( esc_html__( 'WordPress Embed Block for GitHub - API GitHub Rate Limit', $this->get_name_parent() ) );
		$this->set_menu_title 	( esc_html__( 'API GitHub Rate Limit', $this->get_name_parent() ) );
		$this->set_menu_slug 	( 'embed-block-for-github-admin-api-github-rate' );
		$this->set_function 	( array($this, 'create_page') );

		$this->js_acction['root'] 		= str_ireplace("-", "_", $this->get_menu_slug() );
		$this->js_acction['ajax_get'] 	= $this->js_acction['root']."-get_ajax";

		if ($auto_action) {
			$this->add_action_all();
		}

		add_action( 'wp_ajax_'.$this->js_acction['ajax_get'], array($this, 'ajax_json_data') );
		//add_action( 'wp_ajax_nopriv_'.$this->js_acction['ajax_get'], array($this, 'ajax_json_data') );
	}


	/**
	 * 
	 */
	public function action_admin_enqueue_scripts() {
		wp_localize_script( 'embed_block_for_github_admin_ajax', 'embed_block_for_github__ajax_var', array(
			'url'    		=> admin_url( 'admin-ajax.php' ),
			'action' 		=> $this->js_acction['ajax_get'],
			'check_nonce' 	=> $this->wp_create_nonce( 'check_nonce-'.$this->js_acction['ajax_get'] ),
			'css_id'		=> array (
				'info_rate' 	 => "embed_block_for_github_admin_api_github_rate_info_rate",
        		'info_resources' => "embed_block_for_github_admin_api_github_rate_info_resources",
			),
		) );
	}

	/**
	 * 
	 */
	public function ajax_json_data() {
		/**
		 * https://api.github.com/rate_limit
		 */
		check_ajax_referer( 'check_nonce-'.$this->js_acction['ajax_get'], 'security' );
		$return = $this->parent->api->get_rate();
		wp_send_json($return);
		wp_die();
	}

	/**
	 * 
	 */
    public function create_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'API GitHub Rate Limit - Embed Block for GitHub', $this->get_name_parent() ); ?></h1>

			<h2><?php echo esc_html__( 'Rate Status', $this->get_name_parent() ); ?></h2>
			<div id="embed_block_for_github_admin_api_github_rate_info_rate"><?php echo esc_html__( 'Loading...', $this->get_name_parent() ); ?></div>

			<h2><?php echo esc_html__( 'Resources Status', $this->get_name_parent() ); ?></h2>
			<div id="embed_block_for_github_admin_api_github_rate_info_resources"><?php echo esc_html__( 'Loading...', $this->get_name_parent() ); ?></div>			
		</div>
		<?php
	}
}