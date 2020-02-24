<?php

namespace EmbedBlockForGithub\Admin\Config;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class PagAdmin {

	public $parent = null;
	private $options;

	public function __construct($parent = null) {
		$this->parent = (object)array();
		if (! is_null($parent)) {
			$this->parent = $parent;
		}

		add_action( 'admin_menu', array( $this, 'addMenuItem' ) );
        add_action( 'admin_init', array( $this, 'registerPluginSettings' ) );
	}

	/**
	 * https://developer.wordpress.org/reference/functions/add_menu_page/ 
	 * 
	 */
	public function addMenuItem() {
		// Crea una seccion en el menu genral.
		add_menu_page(
			'WordPress Embed Block for GitHub', 
			'Embed Block for GitHub', 
			'manage_options', 
			'embed-block-for-github', 
			array($this, 'createPage'), 
			plugins_url( 'embed-block-for-github/icon.png'),
			/*4*/
		);

		// Crea una seccion de la seccion Settings.
		/*
		add_options_page(
            'Config Embed Block for GitHub', 
            'Embed Block for GitHub',
            'manage_options', 
            'embed-block-for-github',
            array( $this, 'createPage' )
		);
		*/
	}


	/**
	 * https://developer.wordpress.org/reference/functions/register_setting/
	 * 
	 */
	public function registerPluginSettings() {
		$args = array(
            'type' => 'boolean',
            'default' => true,
            );
		register_setting( 'embed-block-for-github', 'darck_theme', $args );

		$args = array(
            'type' => 'string',
            'default' => 'file_svg',
            );
		register_setting( 'embed-block-for-github', 'icon_type_source', $args );

		$args = array(
            'type' => 'boolean',
            'default' => true,
            );
		register_setting( 'embed-block-for-github', 'api_cache', $args );

		$args = array(
            'type' => 'string',
            'default' => '0',
            );
		register_setting( 'embed-block-for-github', 'api_cache_expire', $args );

		$args = array(
            'type' => 'string',
            'default' => '',
            );
		register_setting( 'embed-block-for-github', 'api_access_token_user', $args );

		$args = array(
            'type' => 'string',
            'default' => '',
            );
		register_setting( 'embed-block-for-github', 'api_access_token', $args );
	}

   
    public function createPage()
    {
		?>
		<div class="wrap">
			<h1>Global Settings Embed Block for GitHub</h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'embed-block-for-github' ); ?>
				<?php do_settings_sections( 'embed-block-for-github' ); ?>

				<h2>Theme/Skin</h2>
				<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="darck_theme">Dark Theme</label></th>
					<td><input type="checkbox" name="darck_theme" value="checked" <?php echo esc_attr( get_option('darck_theme') ); ?> /></td>
					</tr>
					<th scope="row">Icon Source</th>
					<td>
						<select name="icon_type_source">
							<?php
								$icon_type_source = esc_attr( get_option('icon_type_source') );
								$list_opt = array( 
									'file_svg' => "Image SVG",
									'font_awesome' => "Font Awesome"
								);
								foreach ($list_opt as $key => $val) {
									printf('<option value="%s" %s>%s</option>', $key, ($icon_type_source == $key ? ' selected="selected" ' : ''), $val);
								}
								unset($icon_type_source);
								unset($list_opt);
							?>
						</select>
					</td>
					</tr>

				</table>

				<h2>Cache</h2>
				<table class="form-table">
					<tr valign="top">
					<th scope="row"><label for="api_cache">Disable Cache</label></th>
					<td><input type="checkbox" name="api_cache" value="checked" <?php echo esc_attr( get_option('api_cache') ); ?> /></td>
					</tr>

					<tr valign="top">
					<th scope="row">api_cache_expire</th>
					<td><input type="number" name="api_cache_expire"  min="0" value="<?php echo esc_attr( get_option('api_cache_expire') ); ?>" /></td>
					</tr>
				</table>
				

				<h2>API GitHub</h2>
				<table class="form-table">
					<tr valign="top">
					<th scope="row">api_access_token_user</th>
					<td><input type="text" name="api_access_token_user" value="<?php echo esc_attr( get_option('api_access_token_user') ); ?>" /></td>
					</tr>
					
					<tr valign="top">
					<th scope="row">api_access_token</th>
					<td><input type="text" name="api_access_token" value="<?php echo esc_attr( get_option('api_access_token') ); ?>" /></td>
					</tr>
				</table>


				<br><br>


				<?php
					$data = $this->parent->api->getRate();
					if (! empty($data)) {
						echo "<table>";
						foreach ($data->rate as $key => $val) {
							echo "<tr>";
							echo "<td>$key</td>";
							echo "<td>$val</td>";
							echo "</tr>";
						}
						echo "</table>";
					}
				?>
				<a href="https://api.github.com/rate_limit">Rate Limit</a>


				
				<?php submit_button(); ?>

			</form>
		</div>


		<?php
    }

   
}
