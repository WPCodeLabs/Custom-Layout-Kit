<?php
/**
 * Admin functions
 *
 * Defines all functionality that is specific to admin
 *
 * Creates custom field values, imports advanced custom fields settings, and creates
 * cached values for our display rules
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit
 */
namespace WPCL\CLK\Classes;

class Admin extends \WPCL\CLK\Framework implements \WPCL\CLK\Interfaces\Action_Hook_Subscriber, \WPCL\CLK\Interfaces\Filter_Hook_Subscriber {

	/**
	 * Constructor
	 * @since 1.0.0
	 * @access protected
	 */
	protected function __construct() {
		/**
		 * Only need to load this class in admin
		 */
		// $this->enabled = is_admin();
	}
	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public function get_actions() {
		return array(
			array( 'admin_enqueue_scripts' => 'enqueue_styles' ),
			array( 'acf/init' => 'import_acf_settings' ),
			array( 'acf/save_post' => 'invalidate_layout_cache' ),
			array( 'delete_post' => 'invalidate_layout_cache' ),
			array( 'plugins_loaded' => 'load_acf' ),
			// array( 'init' => 'updater' ),
			// array( 'admin_menu' => 'add_options_page' ),
			// array( 'admin_init' => 'register_settings' ),
			// array( 'admin_init' => 'activate_license' ),
		);

	}

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		return array(
			array( 'acf/settings/show_admin' => 'acf_show_admin' ),
			array( 'acf/load_field/name=clk_action' => 'load_values' ),
			array( 'acf/load_field/name=clk_singular_condition' => 'load_values' ),
			array( 'acf/load_field/name=clk_archive_condition' => 'load_values' ),
			array( 'acf/load_field/name=clk_view' => 'load_values' ),
			array( 'acf/load_field/name=clk_terms' => 'load_values' ),
			array( 'acf/load_field/name=clk_post_type' => 'load_values' ),
			array( 'acf/load_field/name=clk_page_template' => 'load_values' ),
			array( 'acf/load_field/name=clk_user_role' => 'load_values' ),
			array( 'acf/load_field/name=clk_author' => 'load_values' ),
			array( 'clk/display_fields' => 'manual_placement_field' ),
			array( 'sanitize_option_custom_layout_kit' => array( 'activate_license', 10, 3 ) ),

		);
	}

	public function updater() {

		if ( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			include self::path( 'includes/updates.php' );
		}
		// retrieve our license key from the DB
		$license_key = trim( get_option( md5( 'clk_license_key' ) ) );
		// setup the updater
		$edd_updater = new \EDD_SL_Plugin_Updater( CLK_API_URL, __FILE__, array(
			'version' 	=> CLK_VERSION,		// current version number
			'license' 	=> $license_key,	// license key (used get_option above to retrieve from DB)
			'item_id'       => CLK_PRODUCT_ID,	// id of this plugin
			'author' 	=> 'WP Code Labs',	// author of this plugin
			'url'           => home_url(),
		        'beta'          => false // set to true if you wish customers to receive update notifications of beta releases
		) );
	}

	public function add_options_page() {
		add_options_page(
			__( 'Advanced Custom Layouts', 'custom_layout_kit' ),
			__( 'Advanced Custom Layouts', 'custom_layout_kit' ),
			'manage_options',
			'custom_layout_kit',
			array( $this, 'render_options_page' )
		);
	}

	public static function get_option( $name = '' ) {

		$options = get_option( 'custom_layout_kit' );

		$fields = array(
			md5( 'clk_license_key' ) => array(
				'type'  => 'license',
				'label' => __( 'License Key', 'custom_layout_kit' ),
				'value' => isset( $options[ md5( 'clk_license_key' ) ] ) ? $options[ md5( 'clk_license_key' ) ] : '',
			),
		);

		if( !empty( $name ) ) {
			if( isset( $options[$name] ) ) {
				return $options[$name];
			}
			else {
				return false;
			}
		}
		else {
			return $fields;
		}
	}

	public function register_settings() {

		$fields = self::get_option();
	    // Add Setting

        // Add Section
        add_settings_section( 'general', __( 'Settings', 'custom_layout_kit'), null, 'custom_layout_kit' );

        register_setting( 'custom_layout_kit', 'custom_layout_kit' );

	    foreach( $fields as $key => $field ) {

	    	add_settings_field(
	    		$key,
	    		$field['label'],
	    		array( $this, 'render_settings_field' ),
	    		'custom_layout_kit',
	    		'general',
	    		array(
	    			'key'   => $key,
	    			'field' => $field,
	    		)
	    	);
	    }
	}

	public function render_options_page( $field ) {
		echo '<div class="wrap">';
			echo '<form method="post" action="options.php">';
				wp_nonce_field( 'update-options' );
				settings_fields( 'custom_layout_kit' );
				do_settings_sections( 'custom_layout_kit' );
				submit_button();
			echo '</form>';
		echo '</div>';
	}

	public function render_settings_field( $args ) {
		/**
		 * Render field
		 */
		switch ( $args['field']['type'] ) {
			case 'license':

				$license = get_option( 'clk_license_status' );

				$license_message = !empty( $licence ) ? 'License Valid' : 'License Invalid';

				echo '<fieldset class="clk_license_field">';
				printf( '<input type="text" name="custom_layout_kit[%1$s]" id="custom_layout_kit[%1$s]" value="%2$s" class="widefat">', $args['key'], $args['field']['value'] );
				printf( '<p class="license_status active">%s</p>', $license_message  );
				echo '</fieldset>';
				break;
			// case 'checkbox':
			// 	printf( '<input type="checkbox" name="mdm_analytics[%1$s]" id="mdm_analytics[%1$s]" value="1" class="widefat" %2$s>', $field['id'], checked( $this->options[ $field['id'] ], "1", false ) );
			// 	break;
			// case 'select':
			// 	printf( '<select name="mdm_analytics[%1$s]" id="mdm_analytics[%1$s]" class="widefat">', $field['id'] );
			// 	foreach( $field['options'] as $option => $label ) {
			// 		printf( '<option value="%s" %s>%s</option>', $option, selected( $this->options[ $field['id'] ], $option, false ), $label );
			// 	}
			// 	echo '</select>';
			default:
				// Do nothing by default
				break;
		}
	}

	function activate_license( $value ) {

		update_option( 'debug_settings', $value );

		if( !empty( $value[md5( 'clk_license_key' )] ) ) {

			$license = trim( $value[md5( 'clk_license_key' )] );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_id'    => CLK_PRODUCT_ID, // The ID of the item in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( CLK_API_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				$message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if( empty( $license_data ) ) {
					$message = __( 'An error occurred, please try again.' );
				}

				if ( !empty( $license_data ) && false === $license_data->success ) {

					switch( $license_data->error ) {

						case 'expired' :

							$message = sprintf(
								__( 'Your license key expired on %s.' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;

						case 'revoked' :

							$message = __( 'Your license key has been disabled.' );
							break;

						case 'missing' :

							$message = __( 'Invalid license.' );
							break;

						case 'invalid' :
						case 'site_inactive' :

							$message = __( 'Your license is not active for this URL.' );
							break;

						case 'item_name_mismatch' :

							$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), EDD_SAMPLE_ITEM_NAME );
							break;

						case 'no_activations_left':

							$message = __( 'Your license key has reached its activation limit.' );
							break;

						default :

							$message = __( 'An error occurred, please try again.' );
							break;
					}

				}

			}
			// Check if anything passed on a message constituting a failure
			if ( !empty( $message ) ) {
				add_settings_error( 'custom_layout_kit', 'clk_license_error', $message, 'error' );
				update_option( 'clk_license_status', 'invalid' );
			} else {
				update_option( 'clk_license_status', 'valid' );
			}
		}

		update_option( 'clk_license_status', 'invalid' );

		return $value;

	}
	/**
	 * Register the css for the admin area
	 *
	 * Only need these styles on the post type edit screen
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		if( self::is_edit_screen() ) {
			wp_enqueue_style( 'clk/admin', self::url( 'assets/css/admin.min.css' ), array(), CLK_VERSION, 'all' );
		}
	}
	/**
	 * Maybe hide the advanced custom fields admin
	 * @return bool : True/false to show admin
	 */
	public function acf_show_admin( $value ) {
		return defined( 'CLK_HAS_ACF' ) ? CLK_HAS_ACF : $value;
	}
	/**
	 * Invalidate the layout display rules
	 *
	 * Invalidates the cache on save or delete
	 */
	public function invalidate_layout_cache( $post_id ) {
		if( get_post_type( $post_id ) === 'custom-layout' ) {
			delete_transient( '_clk_display_cache' );
		}
	}
	/**
	 * Wrapper to load ACF values for select items
	 * @param  object $field ACF Field Object
	 * @return object $field ACF Field Object
	 */
	public function load_values( $field ) {
		/**
		 * If we are on the edit screen, it's safe to import values
		 */
		if( self::is_edit_screen() ) {

			switch( $field['name'] ) {
				// case 'clk_archive_condition':
				// 	$field['choices'] = $this->load_conditions( 'archive' );
				// 	break;
				// case 'clk_singular_condition':
				// 	$field['choices'] = $this->load_conditions( 'singular' );
				// 	break;
				// case 'clk_view':
				// 	$field['choices'] = $this->load_views();
				// 	break;
				case 'clk_action' :
					$field['choices'] = $this->load_theme_hooks();
					break;
				case 'clk_post_type' :
					$field['choices'] = $this->load_post_types();
					break;
				case 'clk_terms' :
					$field['choices'] = $this->load_terms();
					break;
				case 'clk_page_template' :
					$field['choices'] = $this->load_templates();
					break;
				case 'clk_user_role' :
					$field['choices'] = $this->load_user_roles();
					break;
				case 'clk_author' :
					$field['choices'] = $this->load_authors();
					break;
				default:
					// Nothing to do here, just leave as is
					break;
			}
		}
		/**
		 * Else we still need to import conditions only
		 *
		 * Required to not break conditional display within the field editor
		 */
		// elseif( $field['name'] === 'clk_condition' ) {
		// 	$field['choices'] = self::flatten_array( $this->load_conditions() );
		// }

		return $field;
	}
	/**
	 * Load user roles for ACF User Role select field
	 * * @return array Array of user roles
	 */
	private function load_user_roles() {
		global $wp_roles;

		$values = array(
			'none' => 'Not Logged In',
			'all'  => 'All Logged In',
		);

		foreach( $wp_roles->roles as $value => $role ) :

			$values[$value] = $role['name'];

		endforeach;

		return $values;
	}
	/**
	 * Load post types
	 * @return array Array of post types
	 */
	private function load_post_types() {
		$values = array();

		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach( $post_types as $post_type ) {
			if( in_array( $post_type->name, array( 'fl-builder-template' ) ) ) {
				continue;
			}

			$values[$post_type->name] = $post_type->label;
		}

		return $values;
	}
	/**
	 * Load terms
	 * @return array Associative array of taxonomies => terms
	 */
	private function load_terms() {

		$values = array();

		$tax_objects = get_taxonomies( [ 'public' => true ], 'objects' );

		foreach( $tax_objects as $tax ) {

			$terms = get_terms( $tax->name, array(
			    'hide_empty' => false,
			) );

			if( empty( $terms ) ) {
				continue;
			}

			$values[$tax->label] = array();

			foreach( $terms as $term ) {
				$values[$tax->label][$term->term_taxonomy_id] = $term->name;
			}
		}

		return $values;
	}
	/**
	 * Load page templates
	 * @return array Array of page templates
	 */
	private function load_templates() {

		include_once ABSPATH . 'wp-admin/includes/theme.php';

		$values = array(
			'none' => 'Default'
		);

		$templates = get_page_templates();

		foreach( $templates as $name => $path ) {
			$values[$path] = $name;
		}

		return $values;
	}
	/**
	 * Load condition
	 * @return array Array of conditions on which to show/hide
	 */
	private function load_conditions( $type ) {
		/**
		 * Archive conditions
		 */
		if( $type === 'archive' ) {
			return array(
				'term' => 'Term Archive',
				'post_type' => 'Post Type Archive',
				'author' => 'Author Archive',
				'date' => 'Date Archive',
			);
		}
		/**
		 * Singular conditions
		 */
		else {
			return array(
				'term' => 'Term',
				'post_type' => 'Post Type',
				'author' => 'Author',
				'template' => 'Page Template',
			);
		}
	}
	/**
	 * Load Views
	 */
	private function load_views() {
		return array(
			'all' => 'Entire Website',
			'frontpage' => 'Front Page',
			'blog' => 'Blog Page',
			'404' => '404 Page',
			'search' => 'Search Results',
			'singular' => 'Singular',
			'archive' => 'Archive',
			'user' => 'User Role',
			'single' => 'Individual Posts',
		);
	}
	/**
	 * Load Authors
	 * @return array Array of authors
	 */
	private function load_authors() {

		$users = get_users();
		$authors = array();

		foreach( $users as $user ) {
			if( user_can( $user->ID, 'edit_posts' ) ) {
				$authors[$user->ID] = $user->data->user_nicename;
			}
		}
		return $authors;
	}
	/**
	 * Loads the theme hooks for ACF Action select field
	 * @return array : associative array of all theme hooks
	 */
	private function load_theme_hooks() {
		/**
		 * Step 1 : Define default core hooks
		 */
		$default_hooks = array(
			'CORE' => array(
				'wp_head' => 'WP Head',
				'wp_body_open' => 'WP Body Open',
				'wp_footer'    => 'WP Footer',
			),
		);
		/**
		 * Step 2 : Theme Support
		 *
		 * Allows 3rd party themes to declare theme support, which will override theme hooks
		 */
		$theme_support = get_theme_support( 'custom-layouts' );
		/**
		 * If we have declared theme support, set them
		 */
		$theme_hooks = isset( $theme_support[0] ) && is_array( $theme_support[0] ) ? $theme_support[0] : array();
		/**
		 * Pre filter, so we can add known themes (if support is not already applied)
		 */
		$theme_hooks = apply_filters( 'clk/pre_theme_hooks', $theme_hooks, !empty( $theme_hooks ) );
		/**
		 * Step 3 : Merge Hooks Together, filter, and return
		 */
		$theme_hooks = array_merge( $default_hooks, $theme_hooks );
		/**
		 * Post filter, to allow themes to filter themselves
		 */
		$theme_hooks = apply_filters( 'clk/theme_hooks', $theme_hooks );

		return $theme_hooks;
	}

	/**
	 * Add the message field for placing the layout with a shortcode
	 * @param  array $field ACF settings field
	 * @return array $field ACF
	 */
	public function manual_placement_field( $field ) {

		/**
		 * Get the requested page
		 */
		$request_uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
		/**
		 * Make sure we are in the admin post edit screen, and the post variable is set
		 */
		if( $request_uri !== '/wp-admin/post.php' || !isset( $_GET['post'] ) ) {
			return $field;
		}
		/**
		 * Make sure we are editing the acl-layout post type
		 */
		if( !function_exists( 'get_post_type' ) || get_post_type( $_GET['post'] ) !== 'custom-layout' ) {
			return $field;
		}

		$message  = sprintf( '<p><span class="clk_notes_label"><strong>Shortcode:</strong></span> <code><strong>[clk_layout id="%s"]</strong></code><p>', $_GET['post'] );
		$message .= '<p><span class="clk_notes_label"><strong>Widget:</strong></span> Place in any sidebar using the Advanced Custom Layout widget.</p>';
		$message .= '<p><strong>Note:</strong> Manual placement bypasses all display rules</p>';

		$field['fields'][] = array(
			'key' => 'clk_manual_placement_message',
			'label' => 'Manual Placement',
			'name' => '',
			'type' => 'message',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => $message,
			'new_lines' => '',
			'esc_html' => 0,
		);

		return $field;
	}
	/**
	 * Import the ACF display settings fields
	 */
	public function import_acf_settings() {

		$fields = self::decode( 'assets/json/fields.json' );

		$fields[0]['key'] = 'clk_layout_display_settings';

		$fields = apply_filters( 'clk/display_fields', $fields[0] );

		acf_add_local_field_group( $fields );
	}

} // end class