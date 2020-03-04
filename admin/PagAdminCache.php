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

class PagAdminCache extends PageBase implements IPage {

	private $js_acction;

	public function __construct($parent = null, $auto_init = false) {
		parent::__construct( $parent );
		$this->setParentSlug ( 'embed-block-for-github-admin' );
		$this->setPageTitle ( esc_html__( 'WordPress Embed Block for GitHub - Cache Manager', $this->getNameParent() ) );
		$this->setMenuTitle ( esc_html__( 'Cache Manager', $this->getNameParent() ) );
		$this->setMenuSlug ( 'embed-block-for-github-admin-cache' );
		$this->setFunction ( array($this, 'createPage') );
		
		$this->js_acction['root'] =  str_ireplace("-", "_", $this->getMenuSlug());
		$this->js_acction['ajax_get'] = $this->js_acction['root']."-get_ajax";

		if ($auto_init) {
			$this->add_action_wp_register();
		}

		add_action( 'wp_ajax_'.$this->js_acction['ajax_get'], array($this, 'ajax_json_data') );
		//add_action( 'wp_ajax_nopriv_'.$this->js_acction['ajax_get'], array($this, 'ajax_json_data') );
	}


	public function init_wp_register() {
		wp_enqueue_script( 'jquery-datatables-js', $this->parent->getURL( 'admin/js/jquery.dataTables.js'), array('jquery') );
		wp_register_style( 'jquery-datatables-css', $this->parent->getURL('admin/css/jquery.dataTables.css'), array() );
		wp_enqueue_style( 'jquery-datatables-css' );
		
		wp_localize_script( 'embed_block_for_github_admin_ajax', 'ajax_var', array(
			'url'    		=> admin_url( 'admin-ajax.php' ),
			'action' 		=> $this->js_acction['ajax_get'],
			'check_nonce' 	=> $this->wp_create_nonce( 'check_nonce-'.$this->js_acction['ajax_get'] )
		) );
	}

	public function ajax_json_data() {
		check_ajax_referer( 'check_nonce-'.$this->js_acction['ajax_get'], 'security' );

		global $wpdb;
		$return['data'] = $wpdb->get_results( "SELECT id, time_update, TIMESTAMPADD(SECOND, expire, time_update) as time_expire, expire, url FROM `wp_embed_block_for_github_cache_store`" );

		wp_send_json($return);
		wp_die();
	}

    public function createPage()
    {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Cache Manager - Embed Block for GitHub', $this->getNameParent() ); ?></h1>
			<?php
				if ( get_class($this->parent->cache) !== "EmbedBlockForGithub\Cache\CacheStoreTable" ) {
					echo "<p>Only support cache Table mode!</p>";
					//echo "<p>Actual mode (".get_class($this->parent->cache).")</p>";
				} else {
					//echo '<div id="embed_block_for_github_admin_cache_table">Loading...</div>';
					?>

<table id="embed_block_for_github_admin_cache_table" class="display" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th>ID</th>
			<th>time_update</th>
			<th>time_expire</th>
			<th>expire</th>
			<th>url</th>
			<th>Actions</th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<th>ID</th>
			<th>time_update</th>
			<th>time_expire</th>
			<th>expire</th>
			<th>url</th>
			<th>Actions</th>
		</tr>
	</tfoot>
</table>


					<?php
				}
			?>
		</div>
		<?php
	}	
}