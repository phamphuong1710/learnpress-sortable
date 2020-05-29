<?php
/*
Plugin Name: LearnPress - Sortable Question
Plugin URI: http://thimpress.com/learnpress
Description: Supports type of question Sortable lets user fill out the text into one ( or more than one ) space.
Author: HTCMage
Version: 1.0.1
Author URI: http://thimpress.com
Tags: learnpress, lms, add-on, fill-in-blank
Text Domain: htc-sortable
Domain Path: /languages/
*/

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

define( 'LP_ADDON_SORTABLE_FILE', __FILE__ );
define( 'LP_ADDON_SORTABLE_VER', '1.0.1' );
define( 'LP_ADDON_SORTABLE_REQUIRE_VER', '1.0.0' );
define( 'LP_QUESTION_SORTABLE_VER', '1.0.0' );
define( 'LP_ADDON_SORTABLE_URL', plugin_dir_path( __FILE__ ) );

/**
 * Class LP_Addon_Sortable_Preload
 */
class LP_Addon_Sortable_Preload {

	/**
	 * LP_Addon_Sortable_Preload constructor.
	 */
	public function __construct() {
		add_action( 'learn-press/ready', array( $this, 'load' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Load addon
	 */
	public function load() {
		LP_Addon::load( 'LP_Addon_Sortable', 'inc/load.php', __FILE__ );
		remove_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notice
	 */
	public function admin_notices() {
		?>
		<div class="error">
			<p><?php echo wp_kses(
					sprintf(
						__( '<strong>%s</strong> addon version %s requires %s version %s or higher is <strong>installed</strong> and <strong>activated</strong>.', 'htc-sortable' ),
						__( 'LearnPress Sortable', 'htc-sortable' ),
						LP_ADDON_SORTABLE_VER,
						sprintf( '<a href="%s" target="_blank"><strong>%s</strong></a>', admin_url( 'plugin-install.php?tab=search&type=term&s=learnpress' ), __( 'LearnPress', 'htc-sortable' ) ),
						LP_ADDON_SORTABLE_REQUIRE_VER
					),
					array(
						'a'      => array(
							'href'  => array(),
							'blank' => array(),
						),
						'strong' => array()
					)
				); ?>
			</p>
		</div>
		<?php
	}
}

new LP_Addon_Sortable_Preload();
