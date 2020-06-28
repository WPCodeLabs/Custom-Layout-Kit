<?php
/**
 * Fromework class
 *
 * Defines common fields and methods shared by all plugin classes
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit
 */
namespace WPCL\CLK;

class Framework {
	/**
	 * Hook/Filter API class
	 * @since 1.0.0
	 * @access protected
	 * @var (object) $api : Class for registering hooks & actions with WP api
	 */
	protected $api;
	/**
	 * Instances
	 * @since 1.0.0
	 * @access protected
	 * @var (array) $instances : Collection of instantiated classes
	 */
	protected static $instances = array();
	/**
	 * Active theme
	 * @since 1.0.0
	 * @access protected
	 * @var (string) $theme : Which theme is active
	 */
	protected static $theme = '';
	/**
	 * Whether or not to load a class
	 * @since 1.0.0
	 * @access protected
	 * @var (bool) $enabled : Flag to conditionally disable a class
	 */
	protected $enabled = true;
	/**
	 * Constructor
	 * @since 1.0.0
	 * @access protected
	 */
	protected function __construct() {
		// Nothing to do here at this time
	}
	/**
	 * Registers our plugin with WordPress.
	 */
	public static function register( $class_name = null ) {
		// Get called class
		$class_name = !is_null( $class_name ) ? $class_name : get_called_class();
		// Instantiate class
		$class = $class_name::get_instance( $class_name );
		// Check that it's enabled
		if( $class->enabled ) {
			// Create API manager
			$class->api = \WPCL\CLK\Api::get_instance();
			// Register stuff
			$class->api->register( $class );
		}
		// Return instance
		return $class;
	}
	/**
	 * Gets an instance of our class.
	 */
	public static function get_instance( $class_name = null ) {
		// Use late static binding to get called class
		$class = !is_null( $class_name ) ? $class_name : get_called_class();
		// Get instance of class
		if( !isset(self::$instances[$class] ) ) {
			self::$instances[$class] = new $class();
		}
		return self::$instances[$class];
	}
	/**
	 * Helper function to use relative URLs
	 * @since 1.0.0
	 * @access protected
	 */
	protected static function url( $url = '' ) {
		return plugin_dir_url( CLK_ROOT ) . ltrim( $url, '/' );
	}
	/**
	 * Helper function to use relative paths
	 * @since 1.0.0
	 * @access protected
	 */
	protected static function path( $path = '' ) {
		return plugin_dir_path( CLK_ROOT ) . ltrim( $path, '/' );
	}
	/**
	 * Helper function to load Advanced Custom Fields Pro
	 * @since 1.0.0
	 * @access public
	 */
	public static function load_acf() {

		if( file_exists( plugin_dir_path( __DIR__ ) . 'vendor/acf/acf.php' ) ) {
			include_once plugin_dir_path( __DIR__ ) . 'vendor/acf/acf.php';
		}

	}
	/**
	 * Helper function to see if on the edit screen for ACL
	 * @since 1.0.0
	 * @access protected
	 * @return boolean
	 */
	protected static function is_edit_screen() {
		if( !function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$current_screen = get_current_screen();

		if( $current_screen && $current_screen->id === 'custom-layout' ) {
			return true;
		}

		return false;

	}
	/**
	 * Helper function to see if on the edit screen for ACF
	 * @since 1.0.0
	 * @access protected
	 * @return boolean
	 */
	protected static function is_acf_screen() {
		return function_exists( 'get_current_screen' ) && get_current_screen()->id === 'acf-field-group';
	}
	/**
	 * Helper function to flatten options in the field editor
	 * @since 1.0.0
	 * @access protected
	 */
	protected static function flatten_array( $array ) {
		$flat = array();

		foreach( $array as $name => $item ) {
			if( is_array( $item ) ) {
				foreach( $item as $index => $sub_item ) {
					$flat[$index] = $sub_item;
				}
			}
			else {
				$flat[$name] = $item;
			}
		}
		return $flat;
	}
	/**
	 * Helper function to safely decode json files
	 */
	protected static function decode( $path ) {

		$file = file_get_contents( self::path( $path ), true );

		$file = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $file);

		return json_decode( $file, true );
	}
	/**
	 * Helper function to investigate objects/errors, etc
	 */
	protected static function expose( $object ) {
		$hook = is_admin() ? 'admin_footer' : 'wp_footer';

		add_action( $hook, function() use( $object ) {
			printf( '<script id="debug">console.log( %s );</script>', json_encode( $object ) );
		});

	}

}