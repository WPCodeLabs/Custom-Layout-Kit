<?php
/**
 * Controller
 *
 * Defines all functionality for setting and validating layout rules
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit_lite
 */
namespace WPCL\CLK\Classes;

class Controller extends \WPCL\CLK\Framework implements \WPCL\CLK\Interfaces\Action_Hook_Subscriber {
	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public function get_actions() {
		return array(
			array( 'get_header' => 'set_layout_hooks' ),
		);
	}
	/**
	 * Checks display rules for each layout against current conditions
	 *
	 * If display is true, set the appropriate hooks
	 * @since 1.0.0
	 */
	public function set_layout_hooks() {

		$layouts = $this->get_layouts();

		foreach( $layouts as $layout ) {
			/**
			 * Look for first exclusion rule that is true to immidietly invalidate
			 */
			if( $this->is_valid( $layout['exclude'] ) === true ) {
				continue;
			}
			/**
			 * Look for first inclusion rule that is true
			 */
			if( $this->is_valid( $layout['include'] ) === true ) {
				/**
				 * Let the frontend handle doing the display stuff
				 */
				Frontend::get_instance()->set_hook( $layout );
			}
		}
	}
	/**
	 * Rebuild the cache that is used to display custom layouts
	 * @param  boolean $return : Whether to return the results
	 * @return [array]         : Layout rules array
	 */
	public function rebuild_display_cache( $return = false ) {
		/**
		 * Make sure we have ACF Loaded
		 */
		self::load_acf();

		if( !class_exists( 'acf' ) ) {
			return false;
		}

		$cache = array();

		$layouts = get_posts( array(
		    'posts_per_page' => -1,
		    'post_type'      => array( 'custom-layout' ),
		    'fields'         => 'ids'
		));

		foreach( $layouts as $id ) {
			$data = array(
				'id'       => $id,
				'hook'     => get_post_meta( $id, 'clk_action', true ),
				'priority' => get_post_meta( $id, 'clk_priority', true ),
				'remove'   => get_post_meta( $id, 'clk_remove_actions', true ),
				'inline'   => get_post_meta( $id, 'clk_edit_inline', true ),
				'include'  => array(),
				'exclude'  => array(),
			);
			/**
			 * If there's no hook, we don't need to waste time
			 */
			if( empty( $data['hook'] ) ) {
				continue;
			}

			/**
			 * Do inclusion rules
			 */
			if( have_rows( 'clk_inclusion_rules', $id ) ) {

				while( have_rows( 'clk_inclusion_rules', $id ) ) { the_row();

					if( have_rows( 'clk_rule_group', $id ) ) {

						$group = array();

						while( have_rows( 'clk_rule_group', $id ) ) { the_row();

							$view = get_sub_field( 'clk_view' );

							$rule = self::parse_display_rule();

							if( !empty( $rule ) ) {
								$group[] = $rule;
							}

						}

						if( !empty( $group ) ) {
							$data['include'][] = $group;
						}

					}

				}

			}
			/**
			 * If we have no inclusion rules, we can bail
			 */
			if( empty( $data['include'] ) ) {
				continue;
			}
			/**
			 * Do exclusion rules
			 */
			if( have_rows( 'clk_exclusion_rules', $id ) ) {

				while( have_rows( 'clk_exclusion_rules', $id ) ) { the_row();

					if( have_rows( 'clk_rule_group', $id ) ) {

						$group = array();

						while( have_rows( 'clk_rule_group', $id ) ) { the_row();

							$view = get_sub_field( 'clk_view' );

							$rule = self::parse_display_rule();

							if( !empty( $rule ) ) {
								$group[] = $rule;
							}

						}

						if( !empty( $group ) ) {
							$data['exclude'][] = $group;
						}

					}

				}

			}
			/**
			 * Add it to the collection of layouts
			 */
			$cache[] = $data;
		}

		/**
		 * Set the transient, if needed
		 */
		set_transient( '_clk_display_cache', $cache, 0 );

		if( $return ) {
			return $cache;
		}
	}
	/**
	 * Create single rule
	 */
	private static function parse_display_rule() {

		$view = get_sub_field( 'clk_view' );

		if( empty( $view ) ) {
			return false;
		}

		$rule = array(
			'view' => $view,
			'condition' => get_sub_field( "clk_{$view}_condition" ),
			'value' => '',
		);
		/**
		 * Maybe do individual posts
		 */
		if( $rule['view'] === 'single' ) {
			$rule['value'] = get_sub_field( 'clk_posts' );
		}
		/**
		 * Maybe do user roles
		 */
		elseif( $rule['view'] === 'user' ) {
			$rule['value'] = get_sub_field( 'clk_user_role' );
		}
		/**
		 * Maybe do archive type
		 */
		elseif( in_array( $rule['view'], array( 'archive', 'singular' ) ) ) {
			/**
			 * Determine subtype
			 */
			switch( $rule['condition'] ) {
				case 'term':
					$rule['value'] = get_sub_field( 'clk_terms' );
					break;
				case 'post_type':
					$rule['value'] = get_sub_field( 'clk_post_type' );
					break;
				case 'author':
					$rule['value'] = get_sub_field( 'clk_author' );
					break;
				case 'post_type':
					$rule['value'] = get_sub_field( 'clk_post_type' );
					break;
				case 'template':
					$rule['value'] = get_sub_field( 'clk_page_template' );
					break;
				default:
					# code...
					break;
			}
		}

		return $rule;
	}
	/**
	 * Get the layouts
	 *
	 * Try to get from cache, else rebuild the cache and return results
	 * @return array : array of all layouts with layout rules
	 */
	private function get_layouts() {
		/**
		 * First try to get from cache
		 */
		$layouts = get_transient( '_clk_display_cache' );
		/**
		 * If no cache is available, rebuild cache and get results
		 */
		if( $layouts === false ) {

			$layouts = $this->rebuild_display_cache( true );

		}

		return $layouts;
	}

	/**
	 * Validate a set of rules for a custom field
	 *
	 * Called recursivly to validate nested rules
	 * @param  array   $groups An array of rules or rule groups
	 * @param  integer $depth  At what depth of the array are we at.
	 * @return bool    $valid  Whether or not all rules are valid in the set
	 * @since 1.0.0
	 */
	private function is_valid( $groups = array(), $depth = 0 ) {
		/**
		 * Assume false when validating any, true if valuating all
		 * @var boolean
		 */
		$valid = $depth === 1;
		/**
		 * If empty just bail
		 */
		if( empty( $groups ) ) {
			return $valid;
		}
		/**
		 * Maybe do top level
		 */
		if( $depth < 2 ) {
			/**
			 * Increment depth
			 */
			$recursive = $depth + 1;
			/**
			 * If any of the groups are true, it's valid
			 */
			foreach( $groups as $index => $group ) {
				/**
				 * At the groups (top) level, any ruleset can be valid
				 * The first TRUE condition validates the ruleset
				 *
				 * Evaluates an OR condition
				 */

				if( $depth === 0 && $valid === true ) {

					break;
				}
				/**
				 * At the group level, all rules must be valid
				 * The first FALSE value invalidates the group
				 *
				 * Evaluates an AND condition
				 */
				if( $depth === 1 && $valid === false ) {
					break;
				}
				/**
				 * Re-enter loop recursively
				 */
				$valid = $this->is_valid( $group, $recursive );
			}

		}
		/**
		 * This level evaluates a single rule
		 */
		elseif( $depth === 2 ) {
			/**
			 * Merge with defaults
			 */
			$rule = wp_parse_args( $groups, array(
				'view' => '',
				'condition' => '',
				'value' => array()
			) );
			/**
			 * Evaluate based on condition
			 */
			switch ( $rule['view'] ) {
				case 'all':
					$valid = true;
					break;
				case 'frontpage':
					$valid = is_front_page();
					break;
				case 'blog':
					$valid = is_home();
					break;
				case '404':
					$valid = is_404();
					break;
				case 'search':
					$valid = is_search();
					break;
				case 'single':
					$valid = in_array( get_the_id(), $rule['value'] );
					break;
				case 'singular':
					$valid = is_singular() && $this->is_singular( $rule['condition'], $rule['value'] );
					break;
				case 'archive':
					$valid = $this->is_archive( $rule['condition'], $rule['value'] );
					break;
				case 'user':
					$valid = $this->is_user( $rule['value'] );
					break;
				default:
					$valid = false;
					break;
			}
		}
		return $valid;
	}
	/**
	 * Validates conditions for single views
	 */
	private function is_singular( $condition, $value ) {

		$valid = false;

		switch( $condition ) {
			case 'term':
				$valid = $this->has_terms( $value );
				break;
			case 'post_type':
				$valid = in_array( get_post_type(), $value );
				break;
			case 'author':
				$valid = in_array( get_post_field( 'post_author', get_the_id() ), $value ) ;
				break;
			case 'template':
				$valid = in_array( get_page_template_slug(), $value );
				break;
			default:
				$valid = is_singular();
				break;
		}

		return $valid;
	}
	/**
	 * Check archive conditions
	 */
	private function is_archive( $condition, $value ) {

		$valid = false;

		switch( $condition ) {
			case 'term':
				$valid = empty( $value ) ? $this->is_tax() : $this->has_terms( $value );
				break;
			case 'post_type':
				$valid = is_post_type_archive( $value );
				break;
			case 'author':
				$valid = is_author( $value );
				break;
			case 'date':
				$valid = is_date();
				break;
			default:
				$valid = is_archive();
				break;
		}

		return $valid;
	}
	/**
	 * Validates if it is a taxonomy arcive of any type
	 */
	private function is_tax() {

		if( is_category() ) {
			return true;
		}

		elseif( is_tag() ) {
			return true;
		}

		elseif( is_tax() ) {
			return true;
		}

		return false;
	}
	/**
	 * Validates whether the current user meets current conditions
	 * @param  array  $roles Array of user conditions
	 * @return boolean $valid Whether current user meets those conditions
	 * @since 1.0.0
	 */
	private function is_user( $role ) {

		$valid = false;
		/**
		 * Check for not logged in users
		 */
		if( in_array( 'none', $role ) && is_user_logged_in() === false ) {
			$valid = true;
		}
		/**
		 * Check for all logged in users
		 */
		elseif( in_array( 'all', $role ) && is_user_logged_in() === true ) {
			$valid = true;
		}
		/**
		 * Lastly check role
		 */
		elseif( is_user_logged_in() === true ) {
			$user = wp_get_current_user();
			foreach( $user->roles as $urole ) {
				if( $valid ) {
					break;
				}
				$valid = $urole === $role;
			}
		}
		return $valid;
	}
	/**
	 * Checks for multiple terms
	 * @param  [type]  $terms [description]
	 * @return boolean $match : true to match any, false to match all
	 */
	private function has_terms( $terms = array(), $match = true ) {

		$valid = false;

		if( !is_array( $terms ) || empty( $terms ) ) {
			return false;
		}

		foreach( $terms as $id ) {
			/**
			 * Check each individual term
			 */
			$valid = $this->has_term( $id );
			/**
			 * We can stop checking if we have the value we're looking for
			 */
			if( $valid === $match ) {
				break;
			}
		}

		return $valid;
	}

	/**
	 * Check if a single term exists
	 * @param  array  $terms Array of taxonomy terms
	 * @return boolean  Checkes if current post / archive has specific terms
	 * @since 1.0.0
	 */
	private function has_term( $term_id ) {

		if( empty( $term_id ) ) {
			return false;
		}

		$valid = false;

		$term = get_term_by( 'term_taxonomy_id', $term_id );

		/**
		 * Check for term on archives
		 */
		if( is_archive() ) {

			switch( $term->taxonomy ) {
				case 'category':
					$valid = is_category( $term->slug );
					break;
				case 'post_tag':
					$valid = is_tag( $term->slug );
					break;
				default:
					$valid = is_tax( $term->taxonomy, $term->slug );
					break;
			}
		}
		/**
		 * Check for term on singulars
		 */
		elseif( is_singular() ) {
			$valid = has_term( $term->slug, $term->taxonomy );
		}

		return $valid;
	}
}