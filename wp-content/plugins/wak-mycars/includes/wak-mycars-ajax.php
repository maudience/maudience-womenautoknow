<?php
// No dirrect access
if ( ! defined( 'WAK_MYCARS_VER' ) ) exit;

/**
 * AJAX: Load Edit Car Form
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_load_edit_car_form' ) ) :
	function wak_ajax_load_edit_car_form() {

		// Security
		check_ajax_referer( 'wak-mycar-edit', 'token' );

		$car_id = absint( $_POST['car_id'] );

		if ( $car_id === NULL || $car_id == 0 ) die( -1 );

		 wak_edit_car_form( $car_id );
		 die;

	}
endif;

/**
 * AJAX: Add New Car
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_load_add_new_car' ) ) :
	function wak_ajax_load_add_new_car() {

		// Security
		check_ajax_referer( 'wak-add-new-car', 'token' );

		wak_edit_car_form();
		die;

	}
endif;

/**
 * AJAX: Submit Car
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_submit_new_car' ) ) :
	function wak_ajax_submit_new_car() {

		if ( ! is_user_logged_in() ) die( 0 );

		// Get the form
		parse_str( $_POST['form'], $post );
		unset( $_POST );

		//echo '<pre>' . print_r( $post, true ) . '</pre>';
		//die;

		$data = wp_parse_args( $post['wak_edit_car'], array(
			'token'             => '',
			'car_id'            => '',
			'name'              => '',
			'make'              => '',
			'model'             => '',
			'year'              => '',
			'customyear'        => '',
			'VIN'               => '',
			'mileage'           => '',
			'monthly_usage'     => 0,
			'insurer'           => '',
			'insurance_renewal' => '',
			'user_id'           => get_current_user_id()
		) );

		// Security
		if ( ! wp_verify_nonce( $data['token'], 'submit-new-wak-car' . $data['user_id'] ) ) die( 'BAD TOKEN' );

		$now   = current_time( 'timestamp' );
		$prefs = wak_autoshops_plugin_settings();

		unset( $data['token'] );

		$clean = array();
		foreach ( $data as $key => $value ) {
			$key = sanitize_text_field( $key );
			if ( $key != '' )
				$clean[ $key ] = sanitize_text_field( $value );
		}
		$data = $clean;

		// Edit a car
		if ( $data['car_id'] != '' ) {

			$car = wak_get_car( $data['car_id'] );
			if ( $car === false || $car === NULL ) {

				echo '<div class="alert alert-warning">Could not locate car. Please refresh this page and try again.</div>';

				wak_edit_car_form( $data );
				die;

			}

			wak_update_my_car( $data );

			echo '<div class="alert alert-success">Car updated. Reloading profile...</div>';
			echo '<script type="text/javascript">location.reload();</script>';

			die;

		}

		// Add a Car
		else {

			$new_car = wak_add_new_car( $data );

			if ( $new_car === false ) {

				echo '<div class="alert alert-warning">Could not add car. Please refresh this page and try again.</div>';

				wak_edit_car_form( $data );
				die;

			}

			else {

				echo '<div class="alert alert-success">Car added. Reloading profile...</div>';
				echo '<script type="text/javascript">location.reload();</script>';

				die;

			}

		}

		die;

	}
endif;

/**
 * AJAX: Delete Car
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_delete_my_car' ) ) :
	function wak_ajax_delete_my_car() {

		// Security
		check_ajax_referer( 'wak-delete-car', 'token' );

		$car_id = absint( $_POST['car_id'] );

		if ( $car_id === NULL || $car_id == 0 ) wp_send_json_error();

		$user_id = get_current_user_id();
		$car     = wak_get_car( $car_id );

		if ( ! isset( $car->user_id ) || $car->user_id != $user_id ) wp_send_json_error();

		// If car was successfully deleted then we also delete log entries
		if ( wak_delete_my_car( $car_id ) ) {

			global $wpdb, $wak_mycar_log_db;

			$wpdb->delete(
				$wak_mycar_log_db,
				array( 'car_id' => $car_id, 'user_id' => $user_id ),
				array( '%d', '%d' )
			);

			wp_send_json_success();

		 }

		else
			wp_send_json_error();

	}
endif;

/**
 * AJAX: Get Car Log
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_get_car_log' ) ) :
	function wak_ajax_get_car_log() {

		// Security
		check_ajax_referer( 'wak-mycar-get-log', 'token' );

		$car_id  = absint( $_POST['car_id'] );
		$user    = wp_get_current_user();

		$car     = wak_get_car_with_log( $car_id );

		if ( ! isset( $car->car_id ) || $car->user_id != $user->ID )
			die( -1 );

?>
<div class="container-fluid">
	<div class="row border-bottom">
		<div class="col-md-3 col-sm-3 col-xs-12">
			<strong>Date</strong>
		</div>
		<div class="col-md-9 col-sm-9 col-xs-12">
			<strong>Entry</strong>
		</div>
	</div>
	<div id="wak-log-entry-list">
<?php

		$print_url = add_query_arg( array( 'view' => 'car-log', 'car' => $car_id ), wak_theme_get_profile_url( $user ) );

		if ( empty( $car->history ) ) {

			$nothing = __( 'There are no log entries for this car.', '' );
			if ( $car->name != '' )
				$nothing = sprintf( __( '%s does not have any log entries yet.', '' ), $car->name );

?>
		<div class="row">
			<div class="col-md-12 text-center no-entry">
				<p><?php echo esc_attr( $nothing ); ?></p>
			</div>
		</div>
<?php

		}
		else {

			$categories  = wak_get_log_categories();
			$date_format = get_option( 'date_format' );
			foreach ( $car->history as $entry ) {

				$text = '';
				if ( array_key_exists( $entry->detail, $categories ) )
					$text = '<em>' . $categories[ $entry->detail ] . ' - </em>';

				$text .= esc_attr( $entry->entry );

?>
		<div class="row" id="car-log-entry<?php echo $entry->id; ?>">
			<div class="col-md-3 col-sm-3 col-xs-3">
				<p><?php echo date( $date_format, $entry->time ); ?></p>
			</div>
			<div class="col-md-8 col-sm-8 col-xs-8">
				<p><?php echo $text; ?></p>
			</div>
			<div class="col-md-1 col-sm-1 col-xs-1 text-right">
				<p><i class="fa fa-times delete-car-log-entry" data-entry="<?php echo $entry->id; ?>" title="Delete Entry"></i></p>
			</div>
		</div>
<?php

			}

		}

?>
	</div>
	<div class="row" id="new-log-entry-form" style="display:none;">
		<div class="col-md-12">
			<input type="text" class="form-control" placeholder="new log entry" id="wak-new-car-log-entry-field" value="" />
			<?php echo wak_log_cat_dropdown( 'wak-new-car-log-entry-cat', 'wak-new-car-log-entry-cat', 'Select category' ); ?>
		</div>
	</div>
	<div class="row" id="car-log-entry-action-row">
		<div class="col-md-12">
			<button type="button" class="btn btn-danger" id="add-new-car-log-entry">Add Entry</button><?php if ( ! empty( $car->history ) ) { ?> <a href="<?php echo $print_url; ?>" target="_blank" class="btn btn-default">Print</a><?php } ?> <button type="button" class="btn btn-default pull-right" data-dismiss="modal" id="close-car-log-modal" aria-label="Close">Close</button> <button data-car="<?php echo $car_id; ?>" type="button" id="submit-new-car-entry" class="btn btn-danger pull-right" style="display:none;">Save Entry</button>
		</div>
	</div>
</div>
<?php

		die;

	}
endif;

/**
 * AJAX: Add to Car Log
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_add_to_car_log' ) ) :
	function wak_ajax_add_to_car_log() {

		// Security
		check_ajax_referer( 'wak-mycar-add-to-log', 'token' );

		$car_id  = absint( $_POST['car_id'] );
		$entry   = sanitize_text_field( $_POST['entry'] );
		$cat     = sanitize_key( $_POST['cat'] );
		$user    = wp_get_current_user();
		$now     = current_time( 'timestamp' );

		$car     = wak_get_car( $car_id );

		if ( ! isset( $car->user_id ) || $car->user_id != $user->ID )
			wp_send_json_error( 1 );

		if ( strlen( $entry ) < 3 )
			wp_send_json_error( __( 'The log entry must be longer then three characters.', '' ) );

		$new_entry = array(
			'car_id'  => $car_id,
			'user_id' => $user->ID,
			'entry'   => $entry,
			'detail'  => $cat,
			'time'    => $now
		);

		$entry_id = wak_add_to_car_log( $new_entry );
		if ( $entry_id === false )
			wp_send_json_error( __( 'Could not save your entry. Please reload the page and try again.' ) );

		$entry = wak_get_car_log_entry( $entry_id );

		$categories = wak_get_log_categories();

		wp_send_json_success( '
	<div class="row pink">
		<div class="col-md-3 col-sm-3 col-xs-12">
			<p>' . date( get_option( 'date_format' ), $entry->time ) . '</p>
		</div>
		<div class="col-md-3 col-sm-3 col-xs-12">
			<p>' . ( ( array_key_exists( $entry->detail, $categories ) ) ? $categories[ $entry->detail ] : '-' ) . '</p>
		</div>
		<div class="col-md-6 col-sm-6 col-xs-12">
			<p>' . esc_attr( $entry->entry ) . '</p>
		</div>
	</div>' );

	}
endif;

/**
 * AJAX: Delete Log Entry
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_delete_car_log_entry' ) ) :
	function wak_ajax_delete_car_log_entry() {

		// Security
		check_ajax_referer( 'wak-delete-car-log-entry', 'token' );

		$entry_id = absint( $_POST['entryid'] );

		global $wpdb, $wak_mycar_log_db;

		$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_mycar_log_db} WHERE id = %d;", $entry_id ) );

		if ( ! isset( $entry->user_id ) || $entry->user_id != get_current_user_id() )
			wp_send_json_error();

		$wpdb->delete(
			$wak_mycar_log_db,
			array( 'id' => $entry_id ),
			array( '%d' )
		);

		wp_send_json_success();

	}
endif;

/**
 * AJAX: Get Car Models
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_get_car_models' ) ) :
	function wak_ajax_get_car_models() {

		$make = sanitize_text_field( $_POST['make'] );

		global $wpdb;

		$table = 'wak_vehicle';
		$options = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT model FROM {$table} WHERE make = %s ORDER BY model ASC;", $make ) );

		if ( empty( $options ) ) {

			echo '<input type="text" name="wak_edit_car[model]" value="" placeholder="Other" />';
			die;

		}

		echo wak_car_model_dropdown( 'wak_edit_car[model]', 'wak-edit-car-model', 'Select car model', '', $make );
		die;

	}
endif;

/**
 * AJAX: Get Car Years
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_get_car_years' ) ) :
	function wak_ajax_get_car_years() {

		$make = sanitize_text_field( $_POST['make'] );
		$model = sanitize_text_field( $_POST['model'] );

		global $wpdb;

		$table = 'wak_vehicle';
		$options = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT year FROM {$table} WHERE make = %s AND model = %s ORDER BY model ASC;", $make, $model ) );

		if ( empty( $options ) ) {

			echo '<input type="text" name="wak_edit_car[year]" value="" placeholder="Other" />';
			die;

		}

		echo wak_car_year_dropdown( 'wak_edit_car[year]', 'wak-edit-car-year', 'Select year', '', $make, $model );
		die;

	}
endif;

?>