<?php
/**
 * Front End
 *
 * Defines all functionality for the front end display of custom layouts
 *
 * Enqueues scripts & styles, displays custom layouts, creates inline editors,
 * checks validity of current display rules, and filters content
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit
 */
namespace WPCL\CLK\Classes;

class FrontEnd extends \WPCL\CLK\Framework implements \WPCL\CLK\Interfaces\Action_Hook_Subscriber, \WPCL\CLK\Interfaces\Filter_Hook_Subscriber, \WPCL\CLK\Interfaces\Shortcode_Hook_Subscriber {
	/**
	 * Flag for whether a layout is being displayed on this page
	 * @since 1.0.0
	 * @access protected
	 * @var (bool) $has_layout : Bool that flags if we have a layout
	 */
	protected $has_layout = false;

	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public function get_actions() {
		return array(
			array( 'wp_enqueue_scripts' => 'enqueue_scripts' ),
			array( 'wp_enqueue_scripts' => 'enqueue_styles' ),
			array( 'get_header' => array( 'render_editor_inline', 999 ) ),
			array( 'clk/do_layout' => 'render' ),
		);
	}
	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		return array(
			array( 'clk/the_content' => array( 'run_shortcode', 8 ) ),
			array( 'clk/the_content' => array( 'do_blocks', 9 ) ),
			array( 'clk/the_content' => 'wptexturize' ),
			array( 'clk/the_content' => 'convert_smilies' ),
			array( 'clk/the_content' => 'convert_chars' ),
			// array( 'clk/the_content' => 'wpautop' ),
			array( 'clk/the_content' => 'shortcode_unautop' ),
			array( 'clk/the_content' => 'do_shortcode' ),
			array( 'clk/the_content' => 'wp_make_content_images_responsive' ),
			array( 'clk/the_content' => 'prepend_attachment' ),
		);
	}
	/**
	 * Get the shortcode hooks this class subscribes to.
	 * @return array
	 */
	public function get_shortcodes() {
		return array(
			array( 'clk_layout' => 'do_layout_shortcode' ),
		);
	}
	/**
	 * Register the javascript for the frontend
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( 'clk/public', self::url( 'assets/js/public.min.js' ), array( 'jquery' ), CLK_VERSION, true );

		$editor = self::get_editor( get_the_id() );

		if( $editor === 'fl-builder' && \FLBuilderModel::is_builder_active() ) {
			wp_enqueue_script( 'clk/public' );
		}

		elseif( $editor === 'elementor' && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			wp_enqueue_script( 'clk/public' );
		}

	}
	/**
	 * Register the stylesheets for the frontend
	 *
	 * Only need to enqueue these assets if a front end builder is active
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		wp_register_style( 'clk/public', self::url( 'assets/css/public.min.css' ), array(), CLK_VERSION, 'all' );

		$editor = self::get_editor( get_the_id() );

		if( $editor === 'fl-builder' && \FLBuilderModel::is_builder_active() ) {
			wp_enqueue_style( 'clk/public' );
		}

		elseif( $editor === 'elementor' && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			wp_enqueue_style( 'clk/public' );
		}

	}
	public function set_hook( $layout ) {
		/**
		 * Maybe (try) to remove other actions
		 *
		 * If actions are set *after* this runs, it may not be possible to remove
		 */
		if( $layout['remove'] === '1' ) {
			remove_all_actions( $layout['hook'] );
		}
		/**
		 * Set the priority
		 */
		$priority = !empty( $layout['priority'] ) ? $layout['priority'] : 10;
		/**
		 * Add placeholder action, in order to check 'has action'
		 */
		add_action( $layout['hook'], 'clk_render', $priority );
		/**
		 * Add real action, which is passed as a closure
		 */
		add_action( $layout['hook'], function() use( $layout ) {
			/**
			 * Can be removed and another action provided
			 */
			if( has_action( $layout['hook'], 'clk_render' ) ) {
				/**
				 * Do before action. Useful if needs wrapped in some additional markup
				 */
				do_action( 'clk/before_render', $layout );
				/**
				 * Do the actual layout
				 */
				$this->render( $layout['id'] );
				/**
				 * Do after action. Useful to close any additional markup
				 */
				do_action( 'clk/after_render', $layout );
			}
		}, $priority );
	}
	/**
	 * Render the layout
	 * @param  int/string $id Post ID of the customlayout
	 * @since 1.0.0
	 */
	public static function render( $id = '' ) {

		if( empty( $id ) || $id === false ) {
			return;
		}

		if( get_post_status( $id ) !== 'publish' ) {
			return;
		}

		switch( self::get_editor( $id ) ) {
			/**
			 * Render layouts build with elementor
			 */
			case 'elementor':
				if( \Elementor\Plugin::$instance->preview->is_preview_mode() && get_the_id() !== $id ) {

					$edit_link = str_ireplace('action=edit', 'action=elementor', get_edit_post_link( $id ) );

					echo '<div class="custom-layout-edit-wrap">';
						printf( '<a href="%s" class="custom-layout-edit" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"></path></svg></a>', $edit_link );
					echo '</div>';
				}
				echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true );

				break;
			/**
			 * Render layouts built with beaver builder
			 */
			case 'fl-builder':
				/**
				 * Maybe output widget like edit link if builder is active
				 */
				if( \FLBuilderModel::is_builder_active() && get_the_id() !== $id ) {
					echo '<div class="custom-layout-edit-wrap">';
						printf( '<a href="%s?fl_builder" class="custom-layout-edit" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"></path></svg></a>', get_post_permalink( $id ) );
					echo '</div>';
				}
				/**
				 * Render content
				 */
				\FLBuilder::render_query( array(
				    'post_type' => 'custom-layout',
				    'p'         => $id,
				) );
				break;
			/**
			 * Render layouts built with WP default editor
			 */
			default:
				$layout = get_post( $id );
				echo apply_filters( 'clk/the_content', get_the_content( null, true, $layout ) );
				break;
		}
	}
	/**
	 * Shortcode wrapper for do_layout
	 * @param  array  $atts shortcode atts
	 * @return string   String of rendered HTML content
	 * @since 1.0.0
	 */
	public function do_layout_shortcode( $atts = array() ) {

		$atts = shortcode_atts( array( 'id' => false ), $atts );

		if( empty( $atts['id'] ) ) {
			return false;
		}

		ob_start();

		$this->render( $atts['id'] );

		return ob_get_clean();
	}
	/**
	 * Disables inline editor on certain hooks
	 *
	 * The inline editor can't be rendered (known) on the 404 content hooks,
	 * & woocommerce hooks. And probably many others.
	 *
	 * If the hook doesn't fire on the post type, it can't be used for editing.
	 *
	 * @param  string $hook the action hook we're checking
	 * @return bool         whether or not we can render the editor.
	 * @since 1.0.0
	 */
	public static function can_render_editor( $hook ) {
		/**
		 * Bail if a known woocommerce hook
		 */
		if( strpos( $hook, 'woocommerce' ) !== false ) {
			return false;
		}
		/**
		 * Bail if an astra woocommerce hook
		 */
		if( strpos( $hook, 'astra_woo' ) !== false ) {
			return false;
		}
		/**
		 * Bail if a known 404 page content
		 */
		if( strpos( $hook, '404' ) !== false ) {
			return false;
		}
		/**
		 * Apply filters and return
		 */
		return apply_filters( 'clk/can_render_editor/hooks', true, $hook );
	}
	/**
	 * Attempt to render the editor inline
	 *
	 * Checks if editor is able to be rendered in place, and sets a hook to render
	 * editor at the specified hook.
	 *
	 * @since 1.0.0
	 */
	public function render_editor_inline() {

		if( is_user_logged_in() === false ) {
			return;
		}

		if( !is_singular( 'custom-layout' ) ) {
			return;
		}
		/**
		 * Get the editor
		 */
		$editor = self::get_editor( get_the_id() );

		/**
		 * Maybe render beaver builder inline
		 */
		if( $editor === 'fl-builder' || $editor === 'elementor' ) {

			$hook     = get_post_meta( get_the_id(), 'clk_action', true );
			$priority = get_post_meta( get_the_id(), 'clk_priority', true );
			$inline   = get_post_meta( get_the_id(), 'clk_edit_inline', true );
			$remove   = get_post_meta( get_the_id(), 'clk_remove_actions', true );

			if( !empty( $hook ) && $inline === '1' && self::can_render_editor( $hook ) ) {

				do_action( 'clk/before_render_editor', $hook );

				do_action( "clk/before_render_editor/editor={$editor}", $hook );

					add_action( 'loop_start', array( $this, 'disable_default_loop_content' ), 1 );

					add_action( 'loop_end', array( $this, 'enable_default_loop_content' ), 1 );

					if( $remove == '1' ) {
						remove_all_actions( $hook );
					}

					add_action( $hook, array( '\\WPCL\\CLK\\Classes\\Frontend', 'render_editor' ), $priority );

				do_action( "clk/after_render_editor/editor={$editor}", $hook );

				do_action( 'clk/after_render_editor', $hook );
			}
		}
	}
	/**
	 * Display actual front-end editor
	 *
	 * Either runs the content if already in the loop, or runs a false main loop
	 * in order to render content in the correct location
	 */
	public static function render_editor() {
		/**
		 * If in main loop, just render the content
		 */
		if( in_the_loop() ) {
			remove_filter( 'the_content', '__return_false', 99999999 );
			the_content();
		}

		/**
		 * Else we need to replace the loop
		 */
		else {
			 global $wp_query;
			/**
			 * Stash the global WP Query Object
			 * @var [type]
			 */
			$wp_query_stash = $wp_query;
			/**
			 * Create a new WP Query Object
			 * @var [type]
			 */
			$wp_query = new \WP_Query( array( 'p' => get_the_id(), 'post_type' => 'custom-layout' ) );

			if ( have_posts() ) :

				while ( have_posts() ) : the_post();
					the_content();
				endwhile;

			endif;
			/**
			 * Restore the global WP_Query Object
			 */
			$wp_query = $wp_query_stash;

			// Restore original Post Data
			wp_reset_postdata();
			// Restore the original query data
			wp_reset_query();
		}
	}
	/**
	 * Disables the content from being returned during the content filter
	 *
	 * Also, puts it back for any non-main query
	 * @param  object $WP_Query WP_QUERY object passed from the loop
	 */
	public function disable_default_loop_content( $WP_Query ) {

		if( is_main_query() ) {
			add_filter( 'the_content', '__return_false', 99999999 );
		}

		else {
			remove_filter( 'the_content', '__return_false', 99999999 );
		}
	}
	/**
	 * Forces removal of our content filter
	 */
	public function enable_default_loop_content() {
		remove_filter( 'the_content', '__return_false', 99999999 );
	}
	/**
	 * Retrieaves what, if any, front-end editor the layout was built with
	 *
	 * Supports Beaver Builder and Elementor
	 * @param  int/string $id ID of the layout
	 * @return string     ID of the buildeer
	 */
	public static function get_editor( $id ) {

		$editor = 'standard';

		if( class_exists( 'Elementor\Plugin' ) && \Elementor\Plugin::instance()->db->is_built_with_elementor( $id ) === true ) {
			$editor = 'elementor';
		}

		else if( class_exists( 'FLBuilderModel' ) && get_post_meta( $id, '_fl_builder_enabled', true ) === '1' ) {
			$editor = 'fl-builder';
		}

		return $editor;
	}
}