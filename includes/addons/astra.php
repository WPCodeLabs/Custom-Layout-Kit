<?php
/**
 * Astra Theme Support
 *
 * Defines all functionality needed to support Astra out of the box
 * Loads theme hooks for both astra core, and astra w/ woocommerce
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit
 */
namespace WPCL\CLK\Addons;

class Astra extends \WPCL\CLK\Framework implements \WPCL\CLK\Interfaces\Filter_Hook_Subscriber {

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		return array(
			array( 'clk/pre_theme_hooks' => array( 'load_theme_hooks', 8, 2 ) ),
			array( 'clk/theme_hooks/woocommerce', 'load_woocommerce_hooks' ),
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
			'HEAD' => array(
				'astra_html_before' => 'Astra Html Before',
				'astra_head_top' => 'Astra Head Top',
				'astra_head_bottom' => 'Astra Head Bottom',
			),
			'HEADER' => array(
				'astra_body_top' => 'Astra Body Top',
				'astra_header_before' => 'Astra Header Before',
				'astra_masthead_top' => 'Astra Masthead Top',
				'astra_main_header_bar_top' => 'Astra Main Header Bar Top',
				'astra_masthead_content' => 'Astra Masthead Content',
				'astra_masthead_toggle_buttons_before' => 'Astra Masthead Toggle Buttons Before',
				'astra_masthead_toggle_buttons_after' => 'Astra Masthead Toggle Buttons After',
				'astra_main_header_bar_bottom' => 'Astra Main Header Bar Bottom',
				'astra_masthead_bottom' => 'Astra Masthead Bottom',
				'astra_header_after' => 'Astra Header After',
			),
			'CONTENT' => array(
				'astra_content_before' => 'Astra Content Before',
				'astra_content_top' => 'Astra Content Top',
				'astra_primary_content_top' => 'Astra Primary Content Top',
				'astra_content_loop' => 'Astra Content Loop',
				'astra_template_parts_content_none' => 'Astra Template Parts Content None',
				'astra_content_while_before' => 'Astra Content While Before',
				'astra_template_parts_content_top' => 'Astra Template Parts Content Top',
				'astra_template_parts_content' => 'Astra Template Parts Content',
				'astra_entry_before' => 'Astra Entry Before',
				'astra_entry_top' => 'Astra Entry Top',
				'astra_single_header_before' => 'Astra Single Header Before',
				'astra_single_header_top' => 'Astra Single Header Top',
				'astra_single_post_title_after' => 'Astra Single Post Title After',
				'astra_single_header_bottom' => 'Astra Single Header Bottom',
				'astra_single_header_after' => 'Astra Single Header After',
				'astra_entry_content_before' => 'Astra Entry Content Before',
				'astra_entry_content_404_page' => 'Astra 404 Entry Content',
				'astra_entry_content_after' => 'Astra Entry Content After',
				'astra_entry_bottom' => 'Astra Entry Bottom',
				'astra_entry_after' => 'Astra Entry After',
				'astra_template_parts_content_bottom' => 'Astra Template Parts Content Bottom',
				'astra_primary_content_bottom' => 'Astra Primary Content Bottom',
				'astra_content_while_after' => 'Astra Content While After',
				'astra_content_bottom' => 'Astra Content Bottom',
				'astra_content_after' => 'Astra Content After',
			),
			'COMMENT' => array(
				'astra_comments_before' => 'Astra Comments Before',
				'astra_comments_after' => 'Astra Comments After',
			),
			'SIDEBAR' => array(
				'astra_sidebars_before' => 'Astra Sidebars Before',
				'astra_sidebars_after' => 'Astra Sidebars After',
			),
			'FOOTER' => array(
				'astra_footer_before' => 'Astra Footer Before',
				'astra_footer_content_top' => 'Astra Footer Content Top',
				'astra_footer_inside_container_top' => 'Astra Footer Inside Container Top',
				'astra_footer_inside_container_bottom' => 'Astra Footer Inside Container Bottom',
				'astra_footer_content_bottom' => 'Astra Footer Content Bottom',
				'astra_footer_after' => 'Astra Footer After',
				'astra_body_bottom' => 'Astra Body Bottom',
			),
			'SPECIAL' => array(
				'astra_404_content_template' => '404 Content',
			),
		);
		return $theme_hooks;
	}

	public function load_woocommerce_hooks( $woo_hooks ) {
		$astra_woo_hooks = array(
			'WOOCOMMERCE - SHOP' => array(
				'astra_woo_shop_category_before' => 'Astra Woo Shop Category Before',
				'astra_woo_shop_category_after' => 'Astra Woo Shop Category After',
				'astra_woo_shop_title_before' => 'Astra Woo Shop Title Before',
				'astra_woo_shop_title_after' => 'Astra Woo Shop Title After',
				'astra_woo_shop_rating_before' => 'Astra Woo Shop Rating Before',
				'astra_woo_shop_rating_after' => 'Astra Woo Shop Rating After',
				'astra_woo_shop_price_before' => 'Astra Woo Shop Price Before',
				'astra_woo_shop_price_after' => 'Astra Woo Shop Price After',
				'astra_woo_shop_add_to_cart_before' => 'Astra Woo Shop Add To Cart Before',
				'astra_woo_shop_add_to_cart_after' => 'Astra Woo Shop Add To Cart After',
			),
			'WOOCOMMERCE - DISTRACTION FREE CHECKOUT' => array(
				'astra_woo_checkout_masthead_top' => 'Astra Woo Checkout Masthead Top',
				'astra_woo_checkout_main_header_bar_top' => 'Astra Woo Checkout Main Header Bar Top',
				'astra_woo_checkout_main_header_bar_bottom' => 'Astra Woo Checkout Main Header Bar Bottom',
				'astra_woo_checkout_masthead_bottom' => 'Astra Woo Checkout Masthead Bottom',
				'astra_woo_checkout_footer_content_top' => 'Astra Woo Checkout Footer Content Top',
				'astra_woo_checkout_footer_content_bottom' => 'Astra Woo Checkout Footer Content Bottom',
			),
		);

		return array_merge_recursive( $woo_hooks, $astra_woo_hooks);
	}
}