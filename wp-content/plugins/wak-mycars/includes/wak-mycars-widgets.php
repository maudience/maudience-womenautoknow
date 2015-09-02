<?php
// No dirrect access
if ( ! defined( 'WAK_MYCARS_VER' ) ) exit;

/**
 * Widget: Add to Maintenance Log
 * @since 1.0
 * @version 1.0
 */
if ( ! class_exists( 'WAK_Add_To_Maintenance_Log' ) ) :
	class WAK_Add_To_Maintenance_Log extends WP_Widget {

		/**
		 * Construct
		 */
		function WAK_Add_To_Maintenance_Log() {

			// Basic details about our widget
			$widget_ops = array( 
				'classname'   => 'widget-wak-mycars-log',
				'description' => __( 'Add a new log entry for a car.', 'wakmycars' )
			);

			$this->WP_Widget( 'wak_add_to_maintenance', __( '(WAK) Maintenance Log', 'wakmycars' ), $widget_ops );
			$this->alt_option_name = 'wak_add_to_maintenance';

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

			$user   = wp_get_current_user();
			$mycars = wak_get_my_cars( $user->ID );

			if ( empty( $mycars ) ) {

				echo '<p>You must add a car to your profile before you can add entries into your maintenance log.</p>';

			}

			else {

				$submit_url = add_query_arg( array( 'show' => 'log' ), wak_theme_get_profile_url( $user ) );

				if ( strlen( $instance['desc'] ) > 0 )
					echo '<p class="description">' . nl2br( $instance['desc'] ) . '</p>';

?>
<form class="form" role="form" action="<?php echo $submit_url; ?>" method="post">
	<input type="hidden" name="wak_new_log_entry[user_id]" value="<?php echo $user->ID; ?>" />
	<input type="hidden" name="wak_new_log_entry[token]" value="<?php echo wp_create_nonce( 'wak-add-new-log-entry' ); ?>" />
	<div class="form-group">
		<select name="wak_new_log_entry[car]" id="" class="form-control"><?php

				echo '<option value="">Select car</option>';

				foreach ( $mycars as $car ) {

					$carname = $car->name;
					if ( $carname == '' )
						$carname = $car->make . ' ' . $car->model . ' ' . $car->year;

					echo '<option value="' . $car->car_id . '">' . $carname . '</option>';

				}

		?></select>
	</div>
	<div class="form-group">
		<input type="text" name="wak_new_log_entry[mileage]" id="" placeholder="Milage" value="" class="form-control" />
	</div>
	<div class="form-group">
		<input type="date" name="wak_new_log_entry[date]" id="" placeholder="Date" value="" class="form-control" />
	</div>
	<h5 class="text-center">How Much Did you Spend?</h5>
<?php

				$services = wak_get_log_services();

				foreach ( $services as $service_id => $label ) {

					if ( $service_id == 0 || $service_id == 99 ) continue;

					echo '<div class="form-group">';

					echo '$ <input type="text" name="wak_new_log_entry[service][' . $service_id . ']" class="form-control half" size="9" value="" placeholder="' . $label . '" />';

					echo '</div>';

				}

?>
	<div class="form-group submit-row">
		<input type="submit" class="btn btn-danger btn-block" value="Add To Log" />
	</div>
</form>
<?php

			}

			// Footer
			echo $after_widget;

		}

		/**
		 * Outputs the options form on admin
		 */
		function form( $instance ) {

			// Defaults
			$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Add Car Maintenance';
			$desc  = isset( $instance['desc'] ) ? esc_attr( $instance['desc'] ) : '';

?>
<p class="pml-widget-field">
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'wakmycars' ); ?>:</label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p class="pml-widget-field">
	<label for="<?php echo esc_attr( $this->get_field_id( 'desc' ) ); ?>"><?php _e( 'Description', 'wakmycars' ); ?>:</label>
	<textarea cols="40" rows="5" id="<?php echo esc_attr( $this->get_field_id( 'desc' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'desc' ) ); ?>"><?php echo esc_attr( $desc ); ?></textarea>
</p>
<?php

		}

		/**
		 * Processes widget options to be saved
		 */
		function update( $new_instance, $old_instance ) {

			$instance = $old_instance;

			$instance['title'] = sanitize_text_field( $new_instance['title'] );
			$instance['desc']  = sanitize_text_field( $new_instance['desc'] );

			return $instance;

		}

	}
endif;

?>