<?php
/**
 * Post Types
 *
 * Registers custom post types with WordPress
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit
 */
namespace WPCL\CLK\Classes;

class PostTypes extends \WPCL\CLK\Framework implements \WPCL\CLK\Interfaces\Action_Hook_Subscriber {
	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public function get_actions() {
		return array(
			array( 'init' => 'add_post_types' ),
			array( 'add_meta_boxes' => array( 'remove_yoast_metabox', 999 ) ),
		);
	}
	/**
	 * Register each custom post type with wordpress
	 * @since  1.0.0
	 */
	public static function add_post_types() {

		$labels = array(
			'name'                  => _x( 'Custom Layouts', 'Post Type General Name', '_s' ),
			'singular_name'         => _x( 'Custom Layout', 'Post Type Singular Name', '_s' ),
			'menu_name'             => __( 'Custom Layouts', '_s' ),
			'name_admin_bar'        => __( 'Custom Layouts', '_s' ),
			'parent_item_colon'     => __( 'Parent Layout:', '_s' ),
			'all_items'             => __( 'Custom Layouts', '_s' ),
			'add_new_item'          => __( 'Add New Layout', '_s' ),
			'add_new'               => __( 'Add New', '_s' ),
			'new_item'              => __( 'New Layout', '_s' ),
			'edit_item'             => __( 'Edit Layout', '_s' ),
			'update_item'           => __( 'Update Layout', '_s' ),
			'view_item'             => __( 'View Layout', '_s' ),
			'search_items'          => __( 'Search Layouts', '_s' ),
			'not_found'             => __( 'Not found', '_s' ),
			'not_found_in_trash'    => __( 'Not found in Trash', '_s' ),
			'items_list'            => __( 'Layout list', '_s' ),
			'items_list_navigation' => __( 'Layout list navigation', '_s' ),
			'filter_items_list'     => __( 'Filter block list', '_s' ),
		);
		$rewrite = array(
			'slug'                  => 'custom-layout',
			'with_front'            => true,
			'pages'                 => true,
			'feeds'                 => true,
		);
		$args = array(
			'label'                 => __( 'Custom Layout', '_s' ),
			'description'           => __( 'Custom Layouts', '_s' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'revisions', ),
			'hierarchical'          => true,
			'public'                => is_admin(),
			'show_ui'               => true,
			// 'show_in_menu'          => 'themes.php',
			'show_in_menu'          => true,
			'menu_position'         => 20,
			'menu_icon'             => 'dashicons-text',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => is_user_logged_in(),
			'capability_type'       => 'page',
			'show_in_rest'          => true,
			'rewrite'               => $rewrite,
		);
		register_post_type( 'custom-layout', $args );

	}

	public function remove_yoast_metabox() {
		remove_meta_box( 'wpseo_meta', 'custom-layout', 'normal' );
	}

}