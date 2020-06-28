<?php
/**
 * Genesis Support
 *
 * Defines all functions necessary for Genesis support out of the box
 * Registers theme hooks specific to genesis, and overrides the 404 hook
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit
 */
namespace WPCL\CLK\Addons;

class Genesis extends \WPCL\CLK\Framework implements \WPCL\CLK\Interfaces\Action_Hook_Subscriber, \WPCL\CLK\Interfaces\Filter_Hook_Subscriber {
	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public function get_actions() {
		return array(
			array( 'clk/before_do_layout' => 'not_found_entry_content_open' ),
			// array( 'clk/before_render_editor/editor=fl-builder' => 'before_render_flbuilder' )
		);
	}

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		return array(
			array( 'clk/pre_theme_hooks' => array( 'load_theme_hooks', 8, 2 ) ),
			array( 'clk/force_layout/hook=genesis_404_entry_content' => array( 'entry_content_404', 10, 2 ) ),
		);
	}

	public function load_theme_hooks( $theme_hooks, $theme_support ) {

		/**
		 * Bail early if the theme already handled their own hooks
		 */
		if( $theme_support ) {
			return $theme_hooks;
		}
		/**
		 * Add our known defaults
		 */
		$theme_hooks = array(
			'HEADER' => array(
				'genesis_before' => 'Genesis Before',
				'genesis_before_header' => 'Genesis Before header',
				'genesis_header_right' => 'Genesis Header Right',
				'genesis_after_header' => 'Genesis After Header',
			),
			'CONTENT AREA' => array(
				'genesis_before_content_sidebar_wrap' => 'Gensis Before Content Sidebar Wrap',
				'genesis_before_content' => 'Genesis Before Content',
				'genesis_before_loop' => 'Genesis Before Loop',
				'genesis_loop' => 'Genesis Loop',
				'genesis_before_while' => 'Genesis Before While',
				'genesis_after_endwhile' => 'Genesis After While',
				'genesis_after_loop' => 'Genesis After Loop',
				'genesis_after_content' => 'Genesis After Content',
				'genesis_after_content_sidebar_wrap' => 'Gensis After Content Sidebar Wrap',
			),
			'ENTRY' => array(
				'genesis_before_entry' => 'Genesis Before Entry',
				'genesis_entry_header' => 'Genesis Entry Header',
				'genesis_entry_content' => 'Genesis Entry Content',
				'genesis_404_entry_content' => 'Genesis 404 Entry Content',
				'genesis_entry_footer' => 'Genesis Entry Footer',
				'genesis_after_entry' => 'Genesis After Entry',
			),
			'SIDEBAR' => array(

				'genesis_before_sidebar_widget_area' => 'Genesis Before Sidebar Widget Area',
				'genesis_sidebar' => 'Genesis Sidebar',
				'genesis_after_sidebar_widget_area' => 'Genesis After Sidebar Widget Area',
				'genesis_before_sidebar_alt_widget_area' => 'Genesis Before Sidebar Alt Widget Area',
				'genesis_sidebar_alt' => 'Genesis Sidebar Alt',
				'genesis_after_sidebar_alt_widget_area' => 'Genesis Before Sidebar Widget Area After Sidebar Alt Widget Area',
			),
			'FOOTER' => array(
				'genesis_before_footer' => 'Genesis Before Footer',
				'genesis_footer' => 'Genesis Footer',
				'genesis_after_footer' => 'Genesis After Footer',
				'genesis_after' => 'Genesis After',
			),
		);
		return $theme_hooks;
	}

	public function entry_content_404( $do_layout, $layout ) {

		if( !is_404() ) {
			return false;
		}

		/**
		 * Get content
		 */
		ob_start();

		do_action( 'clk/do_layout', $layout['id'] );

		$layout_content = ob_get_clean();



		/**
		 * Add a filter to override the 404 content
		 */

		if( $layout['remove'] === '1' ) {

			/**
			 * Remove the default loop
			 */
			remove_action( 'genesis_loop', 'genesis_404' );
			/**
			 * Add replacement
			 */
			add_action( 'genesis_loop', function( $content ) use( $layout_content ) {

				genesis_markup(
					[
						'open'    => '<article class="entry">',
						'context' => 'entry-404',
					]
				);

				genesis_markup(
					[
						'open'    => '<div %s>',
						'close'   => '</div>',
						'content' => $layout_content,
						'context' => 'entry-content',
					]
				);

				genesis_markup(
					[
						'close'   => '</article>',
						'context' => 'entry-404',
					]
				);

			});
		}
		else {
			add_filter( 'genesis_404_entry_content', function( $content ) use( $layout_content ) {
				return $layout_content;
			});
		}
		/**
		 * Return true to override the default action
		 */
		return true;
	}
	// /**
	//  * Removed the post content before rendering a custom layout in a different location
	//  */
	// public function before_render_flbuilder( $hook ) {
	// 	remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
	// }
}