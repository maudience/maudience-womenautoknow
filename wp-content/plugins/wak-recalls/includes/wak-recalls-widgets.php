<?php
// No dirrect access
if ( ! defined( 'WAK_RECALLS_VER' ) ) exit;

/**
 * Widget: Estimator
 * @since 1.0
 * @version 1.0
 */
if ( ! class_exists( 'WAK_My_Recalls' ) ) :
	class WAK_My_Recalls extends WP_Widget {

		/**
		 * Construct
		 */
		function WAK_My_Recalls() {

			// Basic details about our widget
			$widget_ops = array( 
				'classname'   => 'widget-wak-recalls',
				'description' => __( 'Recall warning.', 'wakrecalls' )
			);

			$this->WP_Widget( 'wak_my_recalls', __( '(WAK) Recalls', 'wakrecalls' ), $widget_ops );
			$this->alt_option_name = 'wak_my_recalls';

		}

		/**
		 * Widget Output
		 */
		function widget( $args, $instance ) {

			if ( ! is_user_logged_in() ) return;

			extract( $args, EXTR_SKIP );

			$user    = wp_get_current_user();
			$recalls = wak_count_my_recalls( $user->ID );

			if ( $recalls == 0 ) return;

			echo $before_widget;

			// Title
			if ( ! empty( $instance['title'] ) )
				echo $before_title . $instance['title'] . $after_title;

			$template = str_replace( '%COUNT%', $recalls, $instance['recalls'] );
			echo '<p>' . nl2br( $template ) . '</p>';

			$url = wak_theme_get_profile_url( $user );
			$url = add_query_arg( array( 'show' => 'recalls' ), $url );
			echo '<p class="text-center"><a href="' . $url . '" class="btn btn-danger btn-block"><i class="fa fa-flash"></i> View Recalls</a></p>';

			// Footer
			echo $after_widget;

		}

		/**
		 * Outputs the options form on admin
		 */
		function form( $instance ) {

			// Defaults
			$title   = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Recalls';
			$recalls = isset( $instance['recalls'] ) ? $instance['recalls'] : 'We found a total of <strong>%COUNT%</strong> recalls for your cars!';

?>
<p class="wak-widget-field">
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'wakrecalls' ); ?>:</label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p class="wak-widget-field">
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Recall Notice', 'wakrecalls' ); ?>:</label>
	<textarea id="<?php echo esc_attr( $this->get_field_id( 'recalls' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'recalls' ) ); ?>" cols="40" rows="5"><?php echo esc_attr( $recalls ); ?></textarea>
</p>
<?php

		}

		/**
		 * Processes widget options to be saved
		 */
		function update( $new_instance, $old_instance ) {

			$instance = $old_instance;

			$instance['title']   = sanitize_text_field( $new_instance['title'] );
			$instance['recalls'] = trim( $new_instance['recalls'] );

			return $instance;

		}

	}
endif;

?>