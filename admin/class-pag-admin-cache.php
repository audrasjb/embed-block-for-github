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

class Pag_Admin_Cache extends Page_Base implements IPage {

	private $js_acction;

	public function __construct($parent = null, $auto_action = false) {
		parent::__construct( $parent );
		$this->set_parent_slug 	( 'embed-block-for-github-admin' );
		$this->set_page_title 	( esc_html__( 'WordPress Embed Block for GitHub - Cache Manager', $this->get_name_parent() ) );
		$this->set_menu_title 	( esc_html__( 'Cache Manager', $this->get_name_parent() ) );
		$this->set_menu_slug 	( 'embed-block-for-github-admin-cache' );
		$this->set_function 	( array($this, 'create_page') );
		
		$this->js_acction['root'] 			= str_ireplace("-", "_", $this->get_menu_slug() );
		$this->js_acction['ajax_get'] 		= $this->js_acction['root']."-get_ajax";
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
		wp_localize_script( 'embed_block_for_github_admin_ajax', 'embed_block_for_github__ajax_var', array(
			'url'    				=> admin_url( 'admin-ajax.php' ),
			'action_list' 			=> $this->js_acction['ajax_get'],
			'check_nonce_list' 		=> $this->wp_create_nonce( 'check_nonce-'.$this->js_acction['ajax_get'] ),
			'action_remove'			=> $this->js_acction['ajax_remove_id'],
			'check_nonce_remove'	=> $this->wp_create_nonce( 'check_nonce-'.$this->js_acction['ajax_remove_id'] ),
			//'locate'				=> "en-US",
			'locate'				=> "es-ES",
			'css_id'		=> array (
				'info_table' 	 => "embed_block_for_github_admin_cache_table",
			),
		) );

		wp_enqueue_script( 'jquery-datatables-js', $this->parent->get_URL( 'admin/js/jquery.dataTables.js'), array('jquery') );
		wp_register_style( 'jquery-datatables-css', $this->parent->get_URL('admin/css/jquery.dataTables.css'), array() );
		wp_enqueue_style( 'jquery-datatables-css' );
	}

	public function ajax_json_data() {
		check_ajax_referer( 'check_nonce-'.$this->js_acction['ajax_get'], 'security' );
		$return['data'] = $this->parent->cache->get_all_list();
		wp_send_json($return);
		wp_die();
	}

	public function ajax_remove_id() {
		check_ajax_referer( 'check_nonce-'.$this->js_acction['ajax_remove_id'], 'security' );

		$return['code'] = 999;
		$return['message'] = "unknow";

		$id = $_REQUEST['remove_id'];

		if (empty($id)) {
			$return['code'] 	= 100;
			$return['message'] 	= "Not detected ID!";
			
		} else {
			if ( ! $this->parent->cache->is_exist_id($id) ) {
				$return['code'] 	= 200;
				$return['message'] 	= "ID Not exist in DataBase!";
			} else {
				if (! $this->parent->cache->remove_id($id)) {
					$return['code'] 	= 300;
					$return['message'] 	= "Error in the process the remove ID!";
				} else {
					$return['code'] 	= 0;
					$return['message'] 	= "OK";
				}
			}
		}
		wp_send_json($return);
		wp_die();
	}

    public function create_page() {
		//TODO: Pending implement WP_List_Table
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Cache Manager - Embed Block for GitHub', $this->get_name_parent() ); ?></h1>
			<br />
			
			<?php
				if ( get_class($this->parent->cache) !== "EmbedBlockForGithub\Cache\Cache_Store_Table" ) {
					echo "<p>" . esc_html__( 'Only support cache Table mode!', $this->get_name_parent() ) . "</p>";
					//echo "<p>Actual mode (".get_class($this->parent->cache).")</p>";
				} else {
					//echo '<div id="embed_block_for_github_admin_cache_table">Loading...</div>';
					?>

<table id="embed_block_for_github_admin_cache_table" class="display" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th width="40px"><?php echo esc_html__( 'ID', $this->get_name_parent() ); ?></th>
			<th width="120px"><?php echo esc_html__( 'Time Update', $this->get_name_parent() ); ?> </th>
			<th width="120px"><?php echo esc_html__( 'Time Expire', $this->get_name_parent() ); ?> </th>
			<th width="40px"><?php echo esc_html__( 'Expire', $this->get_name_parent() ); ?> </th>
			<th width=""><?php echo esc_html__( 'URL', $this->get_name_parent() ); ?></th>
			<th width="100px"><?php echo esc_html__( 'Actions', $this->get_name_parent() ); ?> </th>
		</tr>
	</thead>
</table>

					<?php
				}
			?>
		</div>
		<?php
	}	
}