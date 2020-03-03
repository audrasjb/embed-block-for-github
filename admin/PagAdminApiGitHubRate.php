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

require_once ('IPage.php' );
require_once ('PageBase.php' );

use EmbedBlockForGithub\Pages\IPage;
use EmbedBlockForGithub\Pages\PageBase;

class PagAdminApiGitHubRate extends PageBase implements IPage {

	private $js_id = "js_ajax_embed_block_for_github_admin_api_github_rate";
	private $js_acction = "embed_block_for_github_admin_api_github_rate";

	public function __construct($parent = null) {
		parent::__construct( $parent );
		$this->setParentSlug ( 'embed-block-for-github-admin' );
		$this->setPageTitle ( esc_html__( 'WordPress Embed Block for GitHub - API GitHub Rate Limit', $this->getNameParent() ) );
		$this->setMenuTitle ( esc_html__( 'API GitHub Rate Limit', $this->getNameParent() ) );
		$this->setMenuSlug ( 'embed-block-for-github-admin-api-github-rate' );
		$this->setFunction ( array($this, 'createPage') );


		wp_enqueue_script( $this->js_id, $this->parent->getURL( 'admin/js/admin-ajax.js'), array('jquery') );
		wp_localize_script( $this->js_id, 'ajax_var', array(
			'url'    		=> admin_url( 'admin-ajax.php' ),
			'action' 		=> $this->js_acction,
			'check_nonce' 	=> $this->wp_create_nonce( 'check_nonce-'.$this->js_acction )
		) );

		add_action( 'wp_ajax_'.$this->js_acction, array($this, 'ajax_json_data') );
		//add_action( 'wp_ajax_nopriv_'.$this->js_acction, array($this, 'ajax_json_data') );
	}

	public function ajax_json_data() {
		/**
		 * https://api.github.com/rate_limit
		 */
		check_ajax_referer( 'check_nonce-'.$this->js_acction, 'security' );
		$return = $this->parent->api->getRate();
		wp_send_json($return);
		wp_die();
	}

    public function createPage()
    {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'API GitHub Rate Limit - Embed Block for GitHub', $this->getNameParent() ); ?></h1>

			<h2><?php echo esc_html__( 'Rate Status', $this->getNameParent() ); ?></h2>
			<div id="embed_block_for_github_admin_api_github_rate_info_rate">Loading...</div>

			<h2><?php echo esc_html__( 'Resources Status', $this->getNameParent() ); ?></h2>
			<div id="embed_block_for_github_admin_api_github_rate_info_resources">Loading...</div>			
		</div>
		<?php
	}	
}