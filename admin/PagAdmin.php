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

    public function createPage()
    {
		$config[] = array (
			"lable" => "Theme/Skin",
			"items" => array(
				0 => array (
					"type" => 'checkbox',
					"label" => "Dark Theme",
					"name" => $this->parent->config->get_option_full('darck_theme'),
					"value" => $this->parent->config->get_option_html('darck_theme')
				),
				1 => array (
					"type" => 'select',
					"label" => "Icon Source",
					"name" => $this->parent->config->get_option_full('icon_type_source'),
					"value" => $this->parent->config->get_option_html('icon_type_source'),
					"options" => array (
						'file_svg' => "Image SVG",
						'font_awesome' => "Font Awesome"
					)
				)
			)
		);

		$config[] = array (
			"lable" => "Cache",
			"items" => array(
				0 => array (
					"type" => 'checkbox',
					"label" => "Disable Cache",
					"name" => $this->parent->config->get_option_full('api_cache'),
					"value" => $this->parent->config->get_option_html('api_cache')
				),
				1 => array (
					"type" => 'number',
					"label" => "Cache Time Expire",
					"name" => $this->parent->config->get_option_full('api_cache_expire'),
					"value" => $this->parent->config->get_option_html('api_cache_expire'),
					"min" => 0
				)
			)
		);

		$config[] = array (
			"lable" => "API GitHub",
			"items" => array(
				0 => array (
					"type" => 'text',
					"label" => "Access Token",
					"name" => $this->parent->config->get_option_full('api_access_token'),
					"value" => $this->parent->config->get_option_html('api_access_token')
				),
				1 => array (
					"type" => 'text',
					"label" => "Access Tocken User",
					"name" => $this->parent->config->get_option_full('api_access_token_user'),
					"value" => $this->parent->config->get_option_html('api_access_token_user')
				),
			)
		);

	
/*
echo '<textarea  rows="15" cols="150">';
print_r($config);
echo "</textarea><br>";
*/

		?>
		<div class="wrap">
			<h1>Global Settings Embed Block for GitHub</h1>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'embed-block-for-github' );
				do_settings_sections( 'embed-block-for-github' );

				foreach ($config as $sections) {
					printf("<h2>%s</h2>", $sections['lable']);
					if ( is_array($sections['items']) ) {
						echo '<table class="form-table">';
						foreach ($sections['items'] as $items) {
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
							echo 	'	</td>';
							echo 	'</tr>';
						}
						echo "</table>";
					}
				}
				?>

				<br><br>


				<?php
					$data = $this->parent->api->getRate();
					if (! empty($data)) {
						if (isset($data->message)) {
							echo "Api Error: ".$data->message."<br>";
						} else {
							echo "<table>";
							foreach ($data->rate as $key => $val) {
								echo "<tr>";
								echo "<td>$key</td>";
								echo "<td>$val</td>";
								echo "</tr>";
							}
							echo "</table>";
						}
					}
				?>
				<a href="https://api.github.com/rate_limit">Rate Limit</a>


				<?php submit_button(); ?>
			</form>
		</div>
		<?php
    }

   
}
