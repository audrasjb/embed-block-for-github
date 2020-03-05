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

	public function __construct($parent = null, $auto_action = false) {
		parent::__construct( $parent );
		$this->setParentSlug ( 'embed-block-for-github-admin' );
		$this->setPageTitle ( esc_html__( 'WordPress Embed Block for GitHub - Cache Manager', $this->getNameParent() ) );
		$this->setMenuTitle ( esc_html__( 'Cache Manager', $this->getNameParent() ) );
		$this->setMenuSlug ( 'embed-block-for-github-admin-cache' );
		$this->setFunction ( array($this, 'createPage') );
		
		$this->js_acction['root'] =  str_ireplace("-", "_", $this->getMenuSlug());
		$this->js_acction['ajax_get'] = $this->js_acction['root']."-get_ajax";
		$this->js_acction['ajax_remove_id'] = $this->js_acction['root']."-remove_id_ajax";

		if ($auto_action) {
			$this->add_action_all();
		}

		add_action( 'wp_ajax_'.$this->js_acction['ajax_get'], array($this, 'ajax_json_data') );
		//add_action( 'wp_ajax_nopriv_'.$this->js_acction['ajax_get'], array($this, 'ajax_json_data') );

		add_action( 'wp_ajax_'.$this->js_acction['ajax_remove_id'], array($this, 'ajax_remove_id') );
		//add_action( 'wp_ajax_nopriv_'.$this->js_acction['ajax_remove_id'], array($this, 'ajax_remove_id') );
	}


	public function action_admin_enqueue_scripts() {
		wp_localize_script( 'embed_block_for_github_admin_ajax', 'ajax_var', array(
			'url'    				=> admin_url( 'admin-ajax.php' ),
			'action_list' 			=> $this->js_acction['ajax_get'],
			'check_nonce_list' 		=> $this->wp_create_nonce( 'check_nonce-'.$this->js_acction['ajax_get'] ),
			'action_remove'			=> $this->js_acction['ajax_remove_id'],
			'check_nonce_remove'	=> $this->wp_create_nonce( 'check_nonce-'.$this->js_acction['ajax_remove_id'] )
		) );

		wp_enqueue_script( 'jquery-datatables-js', $this->parent->getURL( 'admin/js/jquery.dataTables.js'), array('jquery') );
		wp_register_style( 'jquery-datatables-css', $this->parent->getURL('admin/css/jquery.dataTables.css'), array() );
		wp_enqueue_style( 'jquery-datatables-css' );
	}

	public function ajax_json_data() {
		check_ajax_referer( 'check_nonce-'.$this->js_acction['ajax_get'], 'security' );
		$return['data'] = $this->parent->cache->getAllList();
		wp_send_json($return);
		wp_die();
	}

	public function ajax_remove_id() {
		check_ajax_referer( 'check_nonce-'.$this->js_acction['ajax_remove_id'], 'security' );

		$return['code'] = 999;
		$return['message'] = "unknow";

		$id = $_REQUEST['remove_id'];

		if (empty($id)) {
			$return['code'] = 100;
			$return['message'] = "Not detected ID!";
			
		} else {
			if ( ! $this->parent->cache->isExistID($id) ) {
				$return['code'] = 200;
				$return['message'] = "ID Not exist in DataBase!";
			} else {
				if (! $this->parent->cache->removeId($id)) {
					$return['code'] = 300;
					$return['message'] = "Error in the process the remove ID!";
				} else {
					$return['code'] = 0;
					$return['message'] = "OK";
				}
			}
		}
		wp_send_json($return);
		wp_die();
	}

    public function createPage()
    {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Cache Manager - Embed Block for GitHub', $this->getNameParent() ); ?></h1>
			<br />
			<p>Refres in: <span id="count_seconds_refres_table" ></span> Seconds</p>
			<br />
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
			<th width="40px">ID</th>
			<th width="120px">time_update</th>
			<th width="120px">time_expire</th>
			<th width="40px">expire</th>
			<th width="">url</th>
			<th width="100px">Actions</th>
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