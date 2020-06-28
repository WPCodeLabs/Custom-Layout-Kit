<?php
/**
 * Bootstrap all of the main plugin files
 *
 * Load all core classes
 * Load all optional classes
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit
 */
namespace WPCL\CLK;

class Loader extends \WPCL\CLK\Framework {
	/**
	 * Function to kickoff plugin actions
	 * @since 1.0.0
	 * @access public
	 */
	public function burn_baby_burn() {
		$this->load_core_classes();
		$this->load_plugin_textdomain();
		/**
		 * use actions to load addons, to ensure they are loaded first
		 */
		add_action( 'after_setup_theme', array( $this, 'load_theme_classes' ) );
		add_action( 'after_setup_theme', array( $this, 'load_plugin_classes' ) );
	}
	/**
	 * Wrapper to load the text domain
	 * @since 1.0.0
	 * @access private
	 */
	private function load_plugin_textdomain() {
		load_plugin_textdomain( 'custom_layout_kit', false, basename( dirname( CLK_ROOT ) ) . '/languages' );
	}
	/**
	 * Load all the core classes of the plugin
	 * @since 1.0.0
	 * @access private
	 */
	private function load_core_classes() {
		Classes\Controller::register();
		Classes\Admin::register();
		Classes\Frontend::register();
		Classes\PostTypes::register();
		Classes\Widgets::register();
	}
	/**
	 * Load classes for themes
	 * @since 1.0.0
	 * @access public
	 */
	public function load_theme_classes() {

		$theme = wp_get_theme();

		if( $theme ) {
			/**
			 * Set a variable so we don't have to keep looking it up
			 */
			self::$theme = $theme->template;

			switch( $theme->template ) {
				case 'genesis':
					Addons\Genesis::register();
					break;
				case 'astra':
					Addons\Astra::register();
					break;
				default:
					// Nothing to do here
					break;
			}
		}
	}
	/**
	 * Load classes for additional plugins
	 * @since 1.0.0
	 * @access public
	 */
	public function load_plugin_classes() {
		/**
		 * Check for woocommerce
		 */
		if( class_exists('WooCommerce') ) {
			Addons\Woocommerce::register();
		}
	}

} // end class