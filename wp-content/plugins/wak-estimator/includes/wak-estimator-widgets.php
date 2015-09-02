<?php
// No dirrect access
if ( ! defined( 'WAK_ESTIMATOR_VER' ) ) exit;

/**
 * Widget: Estimator
 * @since 1.0
 * @version 1.0
 */
if ( ! class_exists( 'WAK_Estimator' ) ) :
	class WAK_Estimator extends WP_Widget {

		/**
		 * Construct
		 */
		function WAK_Estimator() {

			// Basic details about our widget
			$widget_ops = array( 
				'classname'   => 'widget-wak-estimator',
				'description' => __( 'Ajax driven estimator.', 'wakestimator' )
			);

			$this->WP_Widget( 'wak_estimator', __( '(WAK) Estimator', 'wakestimator' ), $widget_ops );
			$this->alt_option_name = 'wak_estimator';

		}

		/**
		 * Widget Output
		 */
		function widget( $args, $instance ) {

			extract( $args, EXTR_SKIP );

			echo $before_widget;

			// Title
			if ( ! empty( $instance['title'] ) )
				echo $before_title . $instance['title'] . $after_title;

			echo wak_estimator_shortcode( array() );

			// Footer
			echo $after_widget;

		}

		/**
		 * Outputs the options form on admin
		 */
		function form( $instance ) {

			// Defaults
			$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Estimator';

?>
<p class="pml-widget-field">
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'wakestimator' ); ?>:</label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php

		}

		/**
		 * Processes widget options to be saved
		 */
		function update( $new_instance, $old_instance ) {

			$instance = $old_instance;

			$instance['title'] = sanitize_text_field( $new_instance['title'] );

			return $instance;

		}

	}
endif;
?>