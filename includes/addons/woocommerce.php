<?php
/**
 * Woocommerce Support
 *
 * Defines all functions necessary for woocommerce support
 *
 * Registers woocommerce display hooks
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit
 */
namespace WPCL\CLK\Addons;

class Woocommerce extends \WPCL\CLK\Framework implements \WPCL\CLK\Interfaces\Filter_Hook_Subscriber {

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		return array(
			array( 'clk/theme_hooks' => 'load_theme_hooks' ),
		);
	}

	public function load_theme_hooks( $theme_hooks ) {
		/**
		 * Add our known defaults
		 */
		$woo_hooks = array(
			'WOOCOMMERCE - GLOBAL' => array(
				'woocommerce_before_main_content' => 'Woocommerce Before Main Content',
				'woocommerce_after_main_content' => 'Woocommerce After Main Content',
				'woocommerce_sidebar' => 'Woocommerce Sidebar',
				'woocommerce_breadcrumb' => 'Woocommerce Breadcrumb',
				'woocommerce_before_template_part' => 'Woocommerce Before Template Part',
				'woocommerce_after_template_part' => 'Woocommerce After Template Part',
			),
			'WOOCOMMERCE - SHOP' => array(
				'woocommerce_archive_description' => 'Woocommerce Archive Description',
				'woocommerce_before_shop_loop' => 'Woocommerce Before Shop Loop',
				'woocommerce_before_shop_loop_item_title' => 'Woocommerce Before Shop Loop Item Title',
				'woocommerce_after_shop_loop_item_title' => 'Woocommerce After Shop Loop Item Title',
				'woocommerce_after_shop_loop' => 'Woocommerce After Shop Loop',
			),
			'WOOCOMMERCE - PRODUCT' => array(
				'woocommerce_before_single_product' => 'Woocommerce Before Single Product',
				'woocommerce_before_single_product_summary' => 'Woocommerce Before Single Product Summary',
				'woocommerce_single_product_summary' => 'Woocommerce Single Product Summary',
				'woocommerce_after_single_product_summary' => 'Woocommerce After Single Product Summary',
				'woocommerce_simple_add_to_cart' => 'Woocommerce Simple Add To Cart',
				'woocommerce_before_add_to_cart_form' => 'Woocommerce Before Add To Cart Form',
				'woocommerce_before_add_to_cart_button' => 'Woocommerce Before Add To Cart Button',
				'woocommerce_before_add_to_cart_quantity' => 'Woocommerce Before Add To Cart Quantity',
				'woocommerce_after_add_to_cart_quantity' => 'Woocommerce After Add To Cart Quantity',
				'woocommerce_after_add_to_cart_button' => 'Woocommerce After Add To Cart Button',
				'woocommerce_after_add_to_cart_form' => 'Woocommerce After Add To Cart Form',
				'woocommerce_product_meta_start' => 'Woocommerce Product Meta Start',
				'woocommerce_product_meta_end' => 'Woocommerce Product Meta End',
				'woocommerce_share' => 'Woocommerce Share',
				'woocommerce_after_single_product' => 'Woocommerce After Single Product',
			),
			'WOOCOMMERCE - CART' => array(
				'woocommerce_check_cart_items' => 'Woocommerce Check Cart Items',
				'woocommerce_cart_reset' => 'Woocommerce Cart Reset',
				'woocommerce_cart_updated' => 'Woocommerce Cart Updated',
				'woocommerce_cart_is_empty' => 'Woocommerce Cart Is Empty',
				'woocommerce_before_calculate_totals' => 'Woocommerce Before Calculate Totals',
				'woocommerce_cart_calculate_fees' => 'Woocommerce Cart Calculate Fees',
				'woocommerce_after_calculate_totals' => 'Woocommerce After Calculate Totals',
				'woocommerce_before_cart' => 'Woocommerce Before Cart',
				'woocommerce_before_cart_table' => 'Woocommerce Before Cart Table',
				'woocommerce_before_cart_contents' => 'Woocommerce Before Cart Contents',
				'woocommerce_cart_contents' => 'Woocommerce Cart Contents',
				'woocommerce_after_cart_contents' => 'Woocommerce After Cart Contents',
				'woocommerce_cart_coupon' => 'Woocommerce Cart Coupon',
				'woocommerce_cart_actions' => 'Woocommerce Cart Actions',
				'woocommerce_after_cart_table' => 'Woocommerce After Cart Table',
				'woocommerce_cart_collaterals' => 'Woocommerce Cart Collaterals',
				'woocommerce_before_cart_totals' => 'Woocommerce Before Cart Totals',
				'woocommerce_cart_totals_before_order_total' => 'Woocommerce Cart Totals Before Order Total',
				'woocommerce_cart_totals_after_order_total' => 'Woocommerce Cart Totals After Order Total',
				'woocommerce_proceed_to_checkout' => 'Woocommerce Proceed To Checkout',
				'woocommerce_after_cart_totals' => 'Woocommerce After Cart Totals',
				'woocommerce_after_cart' => 'Woocommerce After Cart',
			),
			'WOOCOMMERCE - CHECKOUT' => array(
				'woocommerce_before_checkout_form' => 'Woocommerce Before Checkout Form',
				'woocommerce_checkout_before_customer_details' => 'Woocommerce Checkout Before Customer Details',
				'woocommerce_checkout_after_customer_details' => 'Woocommerce Checkout After Customer Details',
				'woocommerce_checkout_billing' => 'Woocommerce Checkout Billing',
				'woocommerce_before_checkout_billing_form' => 'Woocommerce Before Checkout Billing Form',
				'woocommerce_after_checkout_billing_form' => 'Woocommerce After Checkout Billing Form',
				'woocommerce_before_order_notes' => 'Woocommerce Before Order Notes',
				'woocommerce_after_order_notes' => 'Woocommerce After Order Notes',
				'woocommerce_checkout_shipping' => 'Woocommerce Checkout Shipping',
				'woocommerce_checkout_before_order_review' => 'Woocommerce Checkout Before Order Review',
				'woocommerce_checkout_order_review' => 'Woocommerce Checkout Order Review',
				'woocommerce_review_order_before_cart_contents' => 'Woocommerce Review Order Before Cart Contents',
				'woocommerce_review_order_after_cart_contents' => 'Woocommerce Review Order After Cart Contents',
				'woocommerce_review_order_before_order_total' => 'Woocommerce Review Order Before Order Total',
				'woocommerce_review_order_after_order_total' => 'Woocommerce Review Order After Order Total',
				'woocommerce_review_order_before_payment' => 'Woocommerce Review Order Before Payment',
				'woocommerce_review_order_before_submit' => 'Woocommerce Review Order Before Submit',
				'woocommerce_review_order_after_submit' => 'Woocommerce Review Order After Submit',
				'woocommerce_review_order_after_payment' => 'Woocommerce Review Order After Payment',
				'woocommerce_checkout_after_order_review' => 'Woocommerce Checkout After Order Review',
				'woocommerce_after_checkout_form' => 'Woocommerce After Checkout Form',
			),
			'WOOCOMMERCE - ACCOUNT' => array(
				'woocommerce_before_account_navigation' => 'Woocommerce Before Account Navigation',
				'woocommerce_account_navigation' => 'Woocommerce Account Navigation',
				'woocommerce_after_account_navigation' => 'Woocommerce After Account Navigation',
			),
		);

		$woo_hooks = apply_filters( 'clk/theme_hooks/woocommerce', $woo_hooks );

		return array_merge_recursive( $theme_hooks, $woo_hooks);
	}

}