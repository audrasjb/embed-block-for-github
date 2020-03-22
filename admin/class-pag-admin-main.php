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

class Pag_Admin_Main extends Page_Base implements IPage {

	public function __construct($parent = null) {
		parent::__construct( $parent );

		$this->set_page_title 		( esc_html__( 'WordPress Embed Block for GitHub', $this->get_name_parent() ) );
		$this->set_menu_title 		( esc_html__( 'Embed Block for GitHub', $this->get_name_parent() ) );
		$this->set_menu_slug 		( 'embed-block-for-github-admin' );
		$this->set_function 		( array($this, 'create_page') );
		$this->set_icon_URL 		( plugins_url( 'embed-block-for-github/icon.png') );
		$this->set_sub_menu_title 	( esc_html__( 'Global Settings', $this->get_name_parent() ) );
		//$this->set_position = 4;
	}

	/**
	 * 
	 */
    public function create_page() {
		$config[] = array (
			"lable" => esc_html__( 'Theme/Skin', $this->get_name_parent() ),
			"items" => array(
				0 => array (
					"type"  => 'checkbox',
					"label" => esc_html__( 'Dark Theme', $this->get_name_parent() ),
					"name"  => $this->parent->config->get_name_option_full('darck_theme'),
					"value" => $this->parent->config->get_option('darck_theme'),
				),
				1 => array (
					"type"    => 'select',
					"label"   => esc_html__( 'Icon Source', $this->get_name_parent() ),
					"name"    => $this->parent->config->get_name_option_full('icon_type_source'),
					"value"   => $this->parent->config->get_option('icon_type_source'),
					"options" => array (
						'file' 			=> esc_html__( 'File Image', $this->get_name_parent() ),
						'font_awesome' 	=> esc_html__( 'Font Awesome', $this->get_name_parent() ),
					),
				)
			)
		);

		$config[] = array (
			"lable" => esc_html__( 'Cache', $this->get_name_parent() ),
			"items" => array(
				0 => array (
					"type"  => 'checkbox',
					"label" => esc_html__( 'Disable Cache', $this->get_name_parent() ),
					"name"  => $this->parent->config->get_name_option_full('api_cache_disable'),
					"value" => $this->parent->config->get_option('api_cache_disable'),
					"info"  => esc_html__( 'WARNING: Github has a limit of hourly queries, it is recommended to use cache to avoid exceeding said limit.', $this->get_name_parent() ),
				),
				1 => array (
					"type"    => 'number',
					"label"   => esc_html__( 'Cache Time Expire', $this->get_name_parent() ),
					"name"    => $this->parent->config->get_name_option_full('api_cache_expire'),
					"value"   => $this->parent->config->get_option('api_cache_expire'),
					"min"     => 0,
					"info"    => esc_html__( 'The maximum value in seconds that we will keep the data in cache before refreshing it. Default 0 (no expiration)', $this->get_name_parent() ),
					"default" => "0",
				),
			)
		);

		$config[] = array (
			"lable" => esc_html__( 'Token API GitHub', $this->get_name_parent() ),
			"items" => array(
				0 => array (
					"type"  => 'text',
					"label" => esc_html__( 'Access User', $this->get_name_parent() ),
					"name"  => $this->parent->config->get_name_option_full('api_access_token_user'),
					"value" => $this->parent->config->get_option('api_access_token_user'),
				),
				1 => array (
					"type"  => 'text',
					"label" => esc_html__( 'Access Token', $this->get_name_parent() ),
					"name"  => $this->parent->config->get_name_option_full('api_access_token'),
					"value" => $this->parent->config->get_option('api_access_token'),
				),
			)
		);
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Global Settings - Embed Block for GitHub', $this->get_name_parent() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'embed-block-for-github' );
				do_settings_sections( 'embed-block-for-github' );

				foreach ($config as $sections) {
					printf("<h2>%s</h2>", $sections['lable']);
					if ( is_array($sections['items']) ) {
						echo '<table class="form-table">';
						foreach ($sections['items'] as $items) {
							if ( ( isset($items['default']) ) && ( empty($items['value']) ) ) {
								$items['value'] = $items['default'];
							} 

							echo 	'<tr valign="top">';
							printf	('	<th scope="row"><label for="%s">%s</label></th>', $items['name'], $items['label'] );
							echo 	'	<td>';
							switch(strtolower($items['type'])) {
								case "text":
									printf('<input type="text" name="%s" value="%s" />', $items['name'], $items['value'] );
									break;
								case "number":
									printf('<input type="number" name="%s" min="%u" value="%s" />', $items['name'], $items['min'], $items['value'] );
									break;
								case "checkbox":
									printf('<input type="checkbox" name="%s" value="checked" %s />', $items['name'], $items['value'] );
									break;
								case "select":
									printf('<select name="%s">', $items['name']);
									foreach ($items['options'] as $key => $val) {
										printf('<option value="%s" %s>%s</option>', $key, ($items['value'] == $key ? ' selected="selected" ' : ''), $val);
									}
									echo "</select>";
									break;
							}
							if (!empty($items['info'])) {
								printf('<p>%s</p>', $items['info']);
							}
							echo 	'	</td>';
							echo 	'</tr>';
						}
						echo "</table>";
					}
				}
				?>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	
}