<?php
/**
 * @wordpress-plugin
 * Plugin Name: Custom Layout Kit
 * Plugin URI:  https://bitbucket.org/midwestdigitalmarketing/cornerstone/
 * Description: Custom layouts, that can be displays anywhere in your theme.
 * Version:     0.1.0
 * Author:      WP Code Labs
 * Author URI:  https://www.wpcodelabs.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: custom_layout_kit
 */

define( 'CLK_ROOT', __FILE__ );

define( 'CLK_VERSION', '0.1.0' );

define( 'CLK_ACTIVE', true );

define( 'CLK_API_URL', 'https://www.wpcodelabs.com' );

define( 'CLK_PRODUCT_ID', 123 );

// If this file is called directly, abort
if ( !defined( 'WPINC' ) ) {
    die( 'Bugger Off Script Kiddies!' );
}

/**
 * Class autoloader
 * Do some error checking and string manipulation to accomodate our namespace
 * and autoload the class based on path
 * @since 1.0.0
 * @see http://php.net/manual/en/function.spl-autoload-register.php
 * @param (string) $className : fully qualified classname to load
 */
function custom_layout_kit_autoload_register( $className ) {
	// Reject it if not a string
	if( !is_string( $className ) ) {
		return false;
	}
	// Check and make damned sure we're only loading things from this namespace
	if( strpos( $className, 'WPCL\CLK' ) === false ) {
		return false;
	}
	// Replace backslashes
	$className = strtolower( str_replace( '\\', '/', $className ) );
	// Ensure there is no slash at the beginning of the classname
	$className = ltrim( $className, '/' );
	// Replace some known constants
	$className = str_ireplace( 'WPCL/CLK/', '', $className );
	// Append full path to class
	$path  = sprintf( '%1$sincludes/%2$s.php', plugin_dir_path( CLK_ROOT ), $className );
	// include the class...
	if( file_exists( $path ) ) {
		include_once( $path );
	}
}

/**
 * Code to run during plugin activation
 */
function custom_layout_kit_activate() {
	\WPCL\CLK\Activator::activate();
}
/**
 * Code to run during plugin activation
 */
function custom_layout_kit_deactivate() {
	\WPCL\CLK\Deactivator::deactivate();
}
/**
 * Create our mu loader file to manage version of ACF
 */
function custom_layout_kit_create_loader() {
	/**
	 * Path to the must use directory
	 */
	$mu_path = untrailingslashit( WPMU_PLUGIN_DIR );
	/**
	 * Path to the mu loader file
	 */
	$mu_loader_path = $mu_path . '/custom-layouts-kit-loader.php';
	/**
	 * Loader Content
	 */
	$mu_loader_content = file_get_contents( plugin_dir_path( CLK_ROOT ) . 'includes/mu-loader.php' );
	/**
	 * Stop here, if the file exists and the content matches the current version
	 * of the loader file
	 */
	if( file_exists( $mu_loader_path ) && md5( $mu_loader_content ) === md5( file_get_contents( $mu_loader_path ) ) ) {
		return;
	}
	/**
	 * Maybe make the mu-plugins directory if it doesn't exist
	 */
	if( !is_dir( $mu_path ) ) {
		mkdir( $mu_path );
	}
	/**
	 * Put the loader file in place
	 */
	file_put_contents( $mu_loader_path, $mu_loader_content );
}
/**
 * Kick off the plugin
 * Check PHP version and make sure our other funcitons will be supported
 * Register autoloader function
 * Register activation & deactivation hooks
 * Create an instance of our main plugin class
 * Finally, Burn Baby Burn...
 */
function custom_layout_kit_run() {
	// If version is less than minimum, register notice
	if( version_compare( '5.3.0', phpversion(), '>=' ) ) {
		// Deactivate plugin
		deactivate_plugins( plugin_basename( __FILE__ ) );
		// Print message to user
		wp_die( 'Irks! This plugin requires minimum PHP v5.3.0 to run. Please update your version of PHP.' );
	}
	// Create our loader
	custom_layout_kit_create_loader();
	// Register Autoloader
	spl_autoload_register( 'custom_layout_kit_autoload_register' );
	// Add activation Hook
	register_activation_hook( CLK_ROOT, 'custom_layout_kit_activate' );
	// Add deactivation Hook
	register_deactivation_hook( CLK_ROOT, 'custom_layout_kit_deactivate' );
	// Instantiate our plugin
	$loader = \WPCL\CLK\Loader::get_instance();
	// Run the plugin
	$loader->burn_baby_burn();

}
custom_layout_kit_run();

/**
 * Wrapper function for do_layout action
 *
 * Used to expose this function as a 'nicename' in add_action
 */
if( !function_exists( 'clk_render' ) ) {
	function clk_render( $id ) {
		/**
		 * Check for existance of the ID
		 */
		if( empty( $id ) || $id == false ) {
			return;
		}
		/**
		 * Call the function
		 */
		\WPCL\CLK\Classes\FrontEnd::get_instance()->render( $id );
	}
}