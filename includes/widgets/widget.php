<?php
/**
 * Layout Widget
 *
 * Defines Widget functionality
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package custom_layout_kit
 */
namespace WPCL\CLK\Widgets;

class Widget extends \WP_Widget {

	public $widget_id_base;
	public $widget_name;
	public $widget_options;
	public $control_options;

	/**
	 * Constructor, initialize the widget
	 * @param $id_base, $name, $widget_options, $control_options ( ALL optional )
	 * @since 1.0.0
	 */
	public function __construct() {
		// Construct some options
		$this->widget_id_base = 'clk_widget';
		$this->widget_name    = 'Custom Layouts';
		$this->widget_options = array(
			'classname'   => 'custom_layout',
			'description' => 'Place a custom layout anywhere' );
		$this->fields = array(
			array(
				'id'   => 'title',
				'type' => 'text',
				'label' => __( 'Title', 'custom_layout_kit' ),
				'default' => '',
			),
			array(
				'id'   => 'layout',
				'type' => 'select',
				'label' => 'Custom layout',
				'default' => '',
				'options' => $this->get_layouts(),
			),
		);
		// Construct parent
		parent::__construct( $this->widget_id_base, $this->widget_name, $this->widget_options );
	}

	/**
	 * Create back end form for specifying image and content
	 * @param $instance
	 * @see https://codex.wordpress.org/Function_Reference/wp_parse_args
	 * @since 1.0.0
	 */
	public function form( $instance ) {
		printf( '<div class="%s_widget_form" style="padding-top: 10px; padding-bottom: 10px;">', $this->id_base );
		/**
		 * Loop through each field and add to widget form
		 */
		foreach( $this->fields as $field => $args ) {
			/**
			 * Set value, or default
			 */
			if( !isset( $instance[ $args['id'] ] ) ) {
				$instance[ $args['id'] ] = $args['default'];
			}
			echo '<div class="field" style="margin-bottom: 14px;">';

				do_action( 'clk/widget/do_input', $this, $args, $instance[ $args['id'] ] );

				if( isset( $args['description'] ) && !empty( $args['description'] ) ) {
					printf( '<p class="description">%s</p>', esc_attr( $args['description'] ) );
				}

			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Update form values
	 * @param $new_instance, $old_instance
	 * @since 1.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		/**
		 * Loop through each field and sanitize
		 */
		foreach( $this->fields as $field => $args ) {
			if( isset( $args['sanitize'] ) && function_exists( $args['sanitize'] ) ) {
				$instance[$args['id']] = call_user_func( $args['sanitize'], $new_instance[$args['id']] );
			} else {
				$instance[$args['id']] = sanitize_text_field( $new_instance[$args['id']] );
			}
		}
		return $instance;
	}

	/**
	 * Output widget on the front end
	 * @param $args, $instance
	 * @since 1.0.0
	 */
	public function widget( $args, $instance ) {
		// Display before widget args
		echo $args['before_widget'];
		// Display Title
		if( !empty( $instance['title'] ) ) {
			$instance['title']  = apply_filters( 'widget_title', $instance['title'], $instance, $this->widget_id_base );
			// Again check if filters cleared name, in the case of 'dont show titles' filter or something
			$instance['title']  = ( !empty( $instance['title']  ) ) ? $args['before_title'] . $instance['title']  . $args['after_title'] : '';
			// Display Title
			echo $instance['title'];
		}

		do_action( 'clk/do_layout', $instance['layout'] );

		// Display after widgets args
		echo $args['after_widget'];
	}
	/**
	 * Gets all of the layouts for the widget select
	 */
	private function get_layouts() {
		$layouts = get_posts( array(
		    'posts_per_page' => -1,
		    'post_type'      => array( 'custom-layout' ),
		));

		$options = array(
			'' => '__Select__',
		);

		foreach( $layouts as $layout ) {
			$options[$layout->ID] = $layout->post_title;
		}

		return $options;
	}

} // end class