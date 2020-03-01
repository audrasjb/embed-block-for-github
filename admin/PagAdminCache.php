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

	public function __construct($parent = null) {
		parent::__construct( $parent );
		$this->setParentSlug ( 'embed-block-for-github-admin' );
		$this->setPageTitle ( esc_html__( 'WordPress Embed Block for GitHub - Cache Manager', $this->getNameParent() ) );
		$this->setMenuTitle ( esc_html__( 'Cache Manager', $this->getNameParent() ) );
		$this->setMenuSlug ( 'embed-block-for-github-admin-cache' );
		$this->setFunction ( array($this, 'createPage') );
	}

    public function createPage()
    {
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Cache Manager - Embed Block for GitHub', $this->getNameParent() ); ?></h1>

		</div>
		<?php
	}	
}