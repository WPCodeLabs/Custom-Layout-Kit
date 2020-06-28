<?php
/**
 * @wordpress-plugin
 * Plugin Name: Advanced Custom Layout Loader
 * Plugin URI:  https://wpcodelabs.com
 * Description: MU Loader for Advanced Custom Layouts
 * Version:     1.0.0
 * Author:      WP Code Labs
 * Author URI:  https://wpcodelabs.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: custom_layout_kit
 */

/**
 * Check for ACF, and set constant to show or hide the admin and maybe load ACF
 * On ACL edit screen only
 *
 */
function _custom_layout_kit_maybe_use_acf() {
	/**
	 * Include the plugins file
	 */
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	/**
	 * Check if Custom Layouts is active
	 */
	$clk_active = is_plugin_active( 'customs-layout-kit/customs-layout-kit.php' );
	/**
	 * Check for ACF (lite)
	 */
	$acf_active = is_plugin_active( 'advanced-custom-fields/acf.php' );
	/**
	 * Check for ACF Pro
	 */
	$acfpro_active = is_plugin_active( 'advanced-custom-fields-pro/acf.php' );
	/**
	 * If either is plugin is installed by the user
	 */
	define( 'CLK_HAS_ACF', ( $acf_active === true || $acfpro_active === true  ) );
	/**
	 * Set hook if acl is active
	 */
	if( $clk_active ) {
		add_action( 'muplugins_loaded', '_custom_layout_kit_load_acf' );
	}

}
_custom_layout_kit_maybe_use_acf();
/**
 * Load version of ACFPRO from Advanced Custom Layouts
 */
function _custom_layout_kit_load_acf() {
	/**
	 * Get the requested page
	 */
	$request_uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	/**
	 * Make sure we are in the admin post edit screen, and the post variable is set
	 */
	if( $request_uri !== '/wp-admin/post.php' || !isset( $_GET['post'] ) ) {
		return;
	}
	/**
	 * Make sure we are editing the clk-layout post type
	 */
	if( !function_exists( 'get_post_type' ) || get_post_type( $_GET['post'] ) !== 'custom-layout' ) {
		return;
	}
	/**
	 * Path to Custom Layouts version of ACF
	 */
	$plugin_path = untrailingslashit( WP_PLUGIN_DIR ) . '/custom-layout-kit/includes/framework.php';
	/**
	 * Include our main plugin file
	 */
	if( file_exists( $plugin_path ) ) {
		include_once $plugin_path;
	}
	/**
	 * Load Advanced Custom Fields
	 */
	if( class_exists('\\WPCL\\CLK\\Framework') ) {
		\WPCL\CLK\Framework::load_acf();
	}
}