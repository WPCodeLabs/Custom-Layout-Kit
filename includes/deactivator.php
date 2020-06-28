<?php
/**
 * Fired during plugin deactivation
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit
 */
namespace WPCL\CLK;

class Deactivator {

	/**
	 * Activate Plugin
	 *
	 * Register Post Types, Register Taxonomies, and Flush Permalinks
	 * @since 1.0.0
	 */
	public static function deactivate() {
		/**
		 * Path to the must use directory
		 */
		$mu_path = untrailingslashit( WPMU_PLUGIN_DIR );
		/**
		 * Path to the mu loader file
		 */
		$mu_loader = $mu_path . '/custom-layout-kit-loader.php';
		/**
		 * Delete the file, to not interfere with active plugins
		 */
		if( file_exists( $mu_loader ) ) {
			unlink( $mu_loader );
		}
	}

}