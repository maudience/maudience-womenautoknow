<?php
// No dirrect access
if ( ! defined( 'WAK_MYCARS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_car' ) ) :
	function wak_get_car( $car_id = NULL ) {

		// Minimum requirements
		if ( $car_id === NULL || strlen( $car_id ) == 0 )
			return false;

		global $wpdb, $wak_mycars_db;

		// Get the car currently in the db
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_mycars_db} WHERE car_id = %d;", $car_id ) );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_count_users_cars' ) ) :
	function wak_count_users_cars( $user_id = 0 ) {

		global $wpdb, $wak_mycars_db;

		$count = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*) 
			FROM {$wak_mycars_db} 
			WHERE user_id = %d;", $user_id ) );

		if ( $count === NULL ) $count = 0;

		return $count;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_car_with_log' ) ) :
	function wak_get_car_with_log( $car_id = NULL, $limit = NULL ) {

		// Minimum requirements
		if ( $car_id === NULL || strlen( $car_id ) == 0 )
			return false;

		global $wpdb, $wak_mycars_db, $wak_mycar_log_db;

		// Get the car currently in the db
		$car = wak_get_car( $car_id );
		if ( $car === false || $car === NULL ) return false;

		if ( $limit === NULL )
			$limit = '';
		else
			$limit = ' LIMIT 0,' . absint( $limit );

		// Get the cars history
		$car->history = wak_get_cars_log( $car->car_id );

		return $car;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_cars_log' ) ) :
	function wak_get_cars_log( $car_id = NULL ) {

		$log = array();

		if ( $car_id === NULL ) return $log;

		global $wpdb, $wak_mycar_log_db;

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wak_mycar_log_db} WHERE car_id = %d ORDER BY time ASC;", absint( $car_id ) ) );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_add_to_car_log' ) ) :
	function wak_add_to_car_log( $data = array() ) {

		$now = current_time( 'timestamp' );
		$data = wp_parse_args( $data, array(
			'car_id'  => NULL,
			'user_id' => NULL,
			'time'    => $now,
			'entry'   => NULL,
			'detail'  => NULL
		) );

		if ( $data['car_id'] === NULL || strlen( $data['entry'] ) < 3 ) return false;

		global $wpdb, $wak_mycar_log_db;

		$wpdb->insert(
			$wak_mycar_log_db,
			$data,
			array( '%d', '%d', '%d', '%s', '%s' )
		);

		return $wpdb->insert_id;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_car_log_entry' ) ) :
	function wak_get_car_log_entry( $entry_id = NULL ) {

		if ( $entry_id === NULL ) return false;

		global $wpdb, $wak_mycar_log_db;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_mycar_log_db} WHERE id = %d;", $entry_id ) );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_my_cars' ) ) :
	function wak_get_my_cars( $user_id = NULL ) {

		// Minimum requirements
		if ( $user_id === NULL || strlen( $user_id ) == 0 )
			return false;

		global $wpdb, $wak_mycars_db;

		// Get the car currently in the db
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wak_mycars_db} WHERE user_id = %d;", $user_id ) );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_my_cars_with_log' ) ) :
	function wak_get_my_cars_with_log( $user_id = NULL, $limit = NULL ) {

		// Minimum requirements
		if ( $user_id === NULL || strlen( $user_id ) == 0 )
			return false;

		global $wpdb, $wak_mycars_db, $wak_mycar_log_db;

		// Get the car currently in the db
		$cars = wak_get_my_cars( $user_id );

		if ( ! empty( $cars ) ) {

			if ( $limit === NULL )
				$limit = '';
			else
				$limit = ' LIMIT 0,' . absint( $limit );

			$result = array();
			foreach ( $cars as $row => $car ) {

				$result[ $row ] = $car;
				$result[ $row ]->history = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wak_mycar_log_db} WHERE car_id = %d ORDER BY time DESC {$limit};", $car->car_id ) );

			}
			$cars = $result;

		}

		return $cars;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_add_new_car' ) ) :
	function wak_add_new_car( $args = array() ) {

		$now = current_time( 'timestamp' );

		$args = wp_parse_args( $args, array(
			'user_id'           => NULL,
			'status'            => 1,
			'name'              => '',
			'make'              => NULL,
			'model'             => NULL,
			'year'              => NULL,
			'VIN'               => NULL,
			'mileage'           => 0,
			'last_edit'         => $now,
			'monthly_usage'     => 0,
			'insurer'           => '',
			'insurance_renewal' => ''
		) );

		// Minimum requirements
		if ( $args['user_id'] === NULL || $args['make'] === NULL )
			return false;

		global $wpdb, $wak_mycars_db;

		if ( strlen( $args['insurance_renewal'] ) == 10 )
			$args['insurance_renewal'] = strtotime( $args['insurance_renewal'], $now );

		if ( strlen( $args['customyear'] ) == 4 ) {

			$wpdb->insert(
				$wpdb->prefix . 'vehicle',
				array( 'year' => $args['customyear'], 'make' => $args['make'], 'model' => $args['model'] ),
				array( '%d', '%s', '%s' )
			);

			$args['year'] = $args['customyear'];

		}

		$wpdb->insert(
			$wak_mycars_db,
			array(
				'user_id'           => absint( $args['user_id'] ),
				'status'            => absint( $args['status'] ),
				'name'              => sanitize_text_field( $args['name'] ),
				'make'              => sanitize_text_field( $args['make'] ),
				'model'             => sanitize_text_field( $args['model'] ),
				'year'              => absint( $args['year'] ),
				'VIN'               => sanitize_text_field( $args['VIN'] ),
				'mileage'           => absint( $args['mileage'] ),
				'last_edit'         => absint( $args['last_edit'] ),
				'monthly_usage'     => absint( $args['monthly_usage'] ),
				'insurer'           => sanitize_text_field( $args['insurer'] ),
				'insurance_renewal' => absint( $args['insurance_renewal'] )
			),
			array( '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d', '%s', '%d' )
		);

		return $wpdb->insert_id;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_update_my_car' ) ) :
	function wak_update_my_car( $args = array() ) {

		$now = current_time( 'timestamp' );

		// Minimum requirements
		if ( ! isset( $args['car_id'] ) || $args['car_id'] === NULL || strlen( $args['car_id'] ) == 0 )
			return false;

		global $wpdb, $wak_mycars_db;

		// Get the car currently in the db
		$car = wak_get_car( $args['car_id'] );
		if ( $car === false || $car === NULL ) return false;

		// 
		$args = wp_parse_args( $args, (array) $car );

		if ( strlen( $args['insurance_renewal'] ) == 10 )
			$args['insurance_renewal'] = strtotime( $args['insurance_renewal'], $now );

		if ( strlen( $args['customyear'] ) == 4 ) {

			$wpdb->insert(
				$wpdb->prefix . 'vehicle',
				array( 'year' => $args['customyear'], 'make' => $args['make'], 'model' => $args['model'] ),
				array( '%d', '%s', '%s' )
			);

			$args['year'] = $args['customyear'];

		}

		$wpdb->update(
			$wak_mycars_db,
			array(
				'user_id'           => $args['user_id'],
				'status'            => $args['status'],
				'name'              => $args['name'],
				'make'              => $args['make'],
				'model'             => $args['model'],
				'year'              => $args['year'],
				'VIN'               => $args['VIN'],
				'mileage'           => $args['mileage'],
				'last_edit'         => $args['last_edit'],
				'monthly_usage'     => $args['monthly_usage'],
				'insurer'           => $args['insurer'],
				'insurance_renewal' => $args['insurance_renewal']
				
			),
			array( 'car_id' => $args['car_id'] ),
			array( '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d', '%s', '%d' ),
			array( '%d' )
		);

		return true;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_delete_my_car' ) ) :
	function wak_delete_my_car( $car_id = NULL ) {

		// Minimum requirements
		if ( $car_id === NULL || strlen( $car_id ) == 0 )
			return false;

		global $wpdb, $wak_mycars_db;

		// Get the car currently in the db
		$car = wak_get_car( $car_id );
		if ( $car === false || $car === NULL ) return -1;

		$wpdb->delete(
			$wak_mycars_db,
			array( 'car_id' => $car->car_id ),
			array( '%d' )
		);

		return true;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_log_categories' ) ) :
	function wak_get_log_categories() {

		return array(
			'service'     => __( 'Car service', '' ),
			'insurance'   => __( 'Insurance', '' ),
			'maintenance' => __( 'Maintenance', '' ),
			'note'        => __( 'Note', '' )
		);

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_log_cat_dropdown' ) ) :
	function wak_log_cat_dropdown( $name = '', $id = '', $none = '', $selected = '' ) {

		$output = '<select name="' . $name . '" id="' . $id . '" class="form-control">';

		if ( $none != '' )
			$output .= '<option value="">' . $none . '</option>';

		$categories = wak_get_log_categories();

		foreach ( $categories as $value => $label ) {
			$output .= '<option value="' . $value . '"';
			if ( $selected == $value ) $output .= ' selected="selected"';
			$output .= '>' . $label . '</option>';
		}

		$output .= '</select>';

		return $output;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_process_mycars_admin_actions' ) ) :
	function wak_process_mycars_admin_actions() {

		if ( ! current_user_can( 'moderate_comments' ) ) return;

		// Bulk Ation - Delete cars
		if ( isset( $_GET['action'] ) && $_GET['action'] != '-1' && isset( $_GET['cars'] ) && ! empty( $_GET['cars'] ) ) {

			global $wpdb, $wak_mycars_db;

			$act  = sanitize_key( $_GET['action'] );
			$cars = array();
			$done = 0;

			foreach ( $_GET['cars'] as $car_id ) {
				if ( $car_id == '' || $car_id == 0 ) continue;
				$cars[] = absint( $car_id );
			}

			if ( ! empty( $cars ) ) {

				if ( $act == 'delete' ) {

					foreach ( $cars as $car_id ) {

						$wpdb->delete(
							$wak_mycars_db,
							array( 'car_id' => $car_id ),
							array( '%d' )
						);

						$done++;

					}

					$url = remove_query_arg( array( 'action', 'cars' ) );
					$url = add_query_arg( array( 'deleted' => 1, 'multi' => $done ), $url );
					wp_safe_redirect( $url );
					exit;

				}

			}

		}

		// Bulk action - Delete log entries
		if ( isset( $_GET['action'] ) && $_GET['action'] != '-1' && isset( $_GET['logentries'] ) && ! empty( $_GET['logentries'] ) ) {

			global $wpdb, $wak_mycar_log_db;

			$act     = sanitize_key( $_GET['action'] );
			$entries = array();
			$done    = 0;

			foreach ( $_GET['logentries'] as $entry_id ) {
				if ( $entry_id == '' || $entry_id == 0 ) continue;
				$entries[] = absint( $entry_id );
			}

			if ( ! empty( $entries ) ) {

				if ( $act == 'delete' ) {

					foreach ( $entries as $entry_id ) {

						$wpdb->delete(
							$wak_mycar_log_db,
							array( 'id' => $entry_id ),
							array( '%d' )
						);

						$done++;

					}

					$url = remove_query_arg( array( 'action', 'logentries' ) );
					$url = add_query_arg( array( 'deleted' => 1, 'multi' => $done ), $url );
					wp_safe_redirect( $url );
					exit;

				}

			}

		}

		// Update Car
		if ( isset( $_POST['wak_car']['car_id'] ) ) {

			$entry_id = absint( $_POST['wak_car']['car_id'] );

			$entry = wak_get_car( $entry_id );

			if ( isset( $entry->car_id ) ) {

				$url = remove_query_arg( array( 'action', 'car_id' ) );

				if ( wak_update_my_car( $_POST['wak_car'] ) ) {

					$url = add_query_arg( array( 'edited' => 1 ), $url );
					wp_safe_redirect( $url );
					exit;

				}
				else {

					$url = add_query_arg( array( 'edited' => 0 ), $url );
					wp_safe_redirect( $url );
					exit;

				}

			}

		}

		// Update screen options tab
		if ( isset( $_REQUEST['wp_screen_options']['option'] ) && isset( $_REQUEST['wp_screen_options']['value'] ) ) {
			
			if ( $_REQUEST['wp_screen_options']['option'] == 'wak_cars_per_page' ) {
				$value = absint( $_REQUEST['wp_screen_options']['value'] );
				update_user_meta( get_current_user_id(), 'wak_cars_per_page', $value );
			}

			if ( $_REQUEST['wp_screen_options']['option'] == 'wak_carlogs_per_page' ) {
				$value = absint( $_REQUEST['wp_screen_options']['value'] );
				update_user_meta( get_current_user_id(), 'wak_carlogs_per_page', $value );
			}

		}

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_show_car_details' ) ) :
	function wak_show_car_details( $car, $echo = true ) {

		$details = array();

		if ( $car->make != '' )
			$details[] = $car->make;

		if ( $car->model != '' )
			$details[] = $car->model;

		if ( $car->year != '' )
			$details[] = $car->year;

		if ( $car->mileage != '' )
			$details[] = $car->mileage . ' mi.';

		if ( ! empty( $details ) )
			$details = '<address>' . implode( ' ', $details ) . '</address>';
		else
			$details = '';

		if ( ! $echo )
			return $details;

		echo $details;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_edit_car_form' ) ) :
	function wak_edit_car_form( $car_id = NULL ) {

		$user_id = get_current_user_id();

		if ( $car_id !== NULL ) {

			$car = wak_get_car( $car_id );

			if ( $car === false || ! isset( $car->user_id ) || $car->user_id != $user_id ) {
				echo '<p>' . __( 'Could not load this car.', '' ) . '</p>';
				return;
			}

		}

		else {

			$car                    = new stdClass();
			$car->name              = '';
			$car->make              = '';
			$car->model             = '';
			$car->year              = '';
			$car->VIN               = '';
			$car->mileage           = '';
			$car->monthly_usage     = '';
			$car->insurer           = '';
			$car->insurance_renewal = '';

		}

		if ( strlen( $car->insurance_renewal ) > 3 )
			$car->insurance_renewal = date( 'Y-m-d', $car->insurance_renewal );

		$usage_options = array(
			0 => 'Do not remind me',
			1 => '0 - 1000 mi. / month',
			2 => '1000 - 5000 mi. / month',
			3 => '5000 mi. or more / month'
		);

?>
<form id="wak-edit-car-form" method="post" action="" style="padding: 0 24px;">
	<input type="hidden" name="wak_edit_car[car_id]" value="<?php echo $car_id; ?>" />
	<input type="hidden" name="wak_edit_car[token]" value="<?php echo wp_create_nonce( 'submit-new-wak-car' . $user_id ); ?>" />
	<div class="row form-group">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-edit-car-name">Car Name</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12">
			<input type="text" class="form-control" placeholder="-" name="wak_edit_car[name]" id="wak-edit-car-name" value="<?php echo esc_attr( $car->name ); ?>" />
			<p><small>Name your car to make it more personal.</small></p>
		</div>
	</div>
	<div class="row form-group">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-edit-car-make">Make</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12">
			<?php echo wak_car_make_dropdown( 'wak_edit_car[make]', 'wak-edit-car-make', 'Select car make', esc_attr( $car->make ) ); ?>
		</div>
	</div>
	<div class="row form-group" id="wak-select-car-model"<?php if ( $car->model == '' ) echo ' style="display:none;"'; ?>>
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-edit-car-model">Model</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12" id="wak-select-car-model-wrap">
			<?php echo wak_car_model_dropdown( 'wak_edit_car[model]', 'wak-edit-car-model', 'Select car model', esc_attr( $car->model ), esc_attr( $car->make ) ); ?>
		</div>
	</div>
	<div class="row form-group" id="wak-select-car-year"<?php if ( $car->year == '' ) echo ' style="display:none;"'; ?>>
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-edit-car-year">Year</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12" id="wak-select-car-year-wrap">
			<?php echo wak_car_year_dropdown( 'wak_edit_car[year]', 'wak-edit-car-year', 'Select year', esc_attr( $car->year ), esc_attr( $car->make ), esc_attr( $car->model ) ); ?>
			<div id="custom-car-year" style="display:none;">
				<input type="text" class="form-control" name="wak_edit_car[customyear]" placeholder="Add Year" id="wak-edit-car-customyear" value="" />
			</div>
		</div>
	</div>
	<div class="row form-group" style="display:none;">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-edit-car-VIN">VIN</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12">
			<input type="text" class="form-control" name="wak_edit_car[VIN]" maxlength="17" placeholder="-" id="wak-edit-car-VIN" value="<?php echo esc_attr( $car->VIN ); ?>" />
			<p><small>17 characters long.</small></p>
		</div>
	</div>
	<div class="row form-group">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-edit-car-mileage">Mileage</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12">
			<input type="text" placeholder="mi." class="form-control half" name="wak_edit_car[mileage]" id="wak-edit-car-mileage" value="<?php echo esc_attr( $car->mileage ); ?>" />
		</div>
	</div>
	<div class="row form-group">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-edit-car-insurer">Insurer</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12">
			<input type="text" class="form-control" placeholder="-" name="wak_edit_car[insurer]" id="wak-edit-car-insurer" value="<?php echo esc_attr( $car->insurer ); ?>" />
		</div>
	</div>
	<div class="row form-group">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-edit-car-monthly_usage">Monthly Usage</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12">
			<select class="form-control" name="wak_edit_car[monthly_usage]" id="wak-edit-car-monthly_usage"><?php

		foreach ( $usage_options as $key => $value ) {

			echo '<option value="' . $key . '"';
			if ( $car->monthly_usage == $key ) echo ' selected="selected"';
			echo '>' . $value . '</option>';

		}

?></select>
			<p><small>Let us remind you when this car is up for service.</small></p>
		</div>
	</div>
	<div class="row form-group">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-edit-car-insurance_renewal">Insurance Renewal</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12">
			<input type="date" class="form-control" name="wak_edit_car[insurance_renewal]" id="wak-edit-car-insurance_renewal" value="<?php echo esc_attr( $car->insurance_renewal ); ?>" />
			<p><small>Let us remind you when your insurance is up for renewal.</small></p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 text-right" style="padding-top: 12px;">
			<?php if ( $car_id !== NULL ) : ?><button class="btn btn-default pull-left wak-delete-car-button" data-car="<?php echo $car_id; ?>">Delete Car</button><?php endif; ?><input type="submit" class="btn btn-danger" id="submit-wak-car-button" value="Save" />
		</div>
	</div>
</form>
<script type="text/javascript">
jQuery(function($) {

	$( 'select#wak-edit-car-year' ).on( 'change', function(e){

		var selectedyear = $(this).find( ':selected' );
		if ( selectedyear.val() == '-1' )
			$( '#custom-car-year' ).show();
		else
			$( '#custom-car-year' ).hide();

	});

	<?php if ( $car_id !== NULL ) { ?>

	/**
	 * Delete Car
	 * @version 1.0
	 */
	$( '.wak-delete-car-button' ).on( 'click', function(e){

		var cartodelete = $(this).data( 'car' );

		if ( confirm( WAKCars.confirmdelete ) )
			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-delete-my-car',
					token     : WAKCars.deletetoken,
					car_id    : cartodelete
				},
				beforeSend : function() {
					$(this).attr( 'disabled', 'disabled' ).val( WAKCars.deleting );
				},
				dataType   : "JSON",
				url        : WAKCars.ajaxurl,
				success    : function( response ) {

					if ( response.success ) {
						$( '#my-car' + cartodelete ).remove();
						$( '#wak-add-car' ).modal( 'hide' );
						$( '#wak-edit-car' ).modal( 'hide' );
					}

					else {
						$(this).removeAttr( 'disabled' );
						alert( WAKCars.faileddelete );
					}

				}
			});

		return false;

	});

	<?php } ?>

	var selectedmake  = '';
	var selectedmodel = '';

	$( 'form#wak-edit-car-form' ).on( 'change', 'select#wak-edit-car-make', function(){

		console.log( 'make changed' );
		var modelcontainer = $( '#wak-select-car-model-wrap' );
		selectedmake = $(this).find( ':selected' ).val();

		$.ajax({
			type       : "POST",
			data       : {
				action    : 'wak-get-car-model',
				make      : selectedmake
			},
			beforeSend : function() {

				$( '#wak-select-car-year-wrap' ).empty();
				$( '#wak-select-car-year' ).hide();

				$( '#wak-select-car-model-wrap' ).empty();
				$( '#wak-select-car-model' ).show();

			},
			dataType   : "HTML",
			url        : WAKCars.ajaxurl,
			success    : function( response ) {

				$( '#wak-select-car-model-wrap' ).html( response );

			}
		});

	});

	$( 'form#wak-edit-car-form' ).on( 'change', 'select#wak-edit-car-model', function(){

		console.log( 'model changed' );
		var yearcontainer = $( '#wak-select-car-yar-wrap' );
		selectedmodel = $(this).find( ':selected' ).val();

		$.ajax({
			type       : "POST",
			data       : {
				action    : 'wak-get-car-year',
				model     : selectedmodel,
				make      : selectedmake
			},
			beforeSend : function() {
				$( '#wak-select-car-year-wrap' ).empty();
				$( '#wak-select-car-year' ).show();
			},
			dataType   : "HTML",
			url        : WAKCars.ajaxurl,
			success    : function( response ) {

				$( '#wak-select-car-year-wrap' ).html( response );

			}
		});

	});

});
</script>
<?php

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_car_make_dropdown' ) ) :
	function wak_car_make_dropdown( $name = '', $id = '', $none = '', $selected = '' ) {

		global $wpdb;

		$table = 'wak_vehicle';
		$options = $wpdb->get_col( "SELECT DISTINCT make FROM {$table} ORDER BY make ASC;" );

		if ( empty( $options ) ) return '';

		$output = '<select name="' . $name . '" id="' . $id . '" class="form-control">';

		if ( $none != '' ) {

			$output .= '<option value=""';
			if ( $selected == '' ) $output .= ' selected="selected"';
			$output .= '>' . $none . '</option>';

		}

		foreach ( $options as $value ) {
			$output .= '<option value="' . $value . '"';
			if ( $selected == $value ) $output .= ' selected="selected"';
			$output .= '>' . $value . '</option>';
		}

		$output .= '</select>';

		return $output;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_car_model_dropdown' ) ) :
	function wak_car_model_dropdown( $name = '', $id = '', $none = '', $selected = '', $make = '' ) {

		global $wpdb;

		$table = 'wak_vehicle';
		$options = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT model FROM {$table} WHERE make = %s ORDER BY model ASC;", $make ) );

		if ( empty( $options ) ) return '';

		$output = '<select name="' . $name . '" id="' . $id . '" class="form-control">';

		if ( $none != '' ) {

			$output .= '<option value=""';
			if ( $selected == '' ) $output .= ' selected="selected"';
			$output .= '>' . $none . '</option>';

		}

		foreach ( $options as $value ) {
			$output .= '<option value="' . $value . '"';
			if ( $selected == $value ) $output .= ' selected="selected"';
			$output .= '>' . $value . '</option>';
		}

		$output .= '</select>';

		return $output;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_car_year_dropdown' ) ) :
	function wak_car_year_dropdown( $name = '', $id = '', $none = '', $selected = '', $make = '', $model = '' ) {

		global $wpdb;

		$table = 'wak_vehicle';
		$options = range( 1974, date( 'Y' ) );

		if ( empty( $options ) ) return '';

		$output = '<select name="' . $name . '" id="' . $id . '" class="form-control">';

		if ( $none != '' ) {

			$output .= '<option value=""';
			if ( $selected == '' ) $output .= ' selected="selected"';
			$output .= '>' . $none . '</option>';

		}

		foreach ( $options as $value ) {
			$output .= '<option value="' . $value . '"';
			if ( $selected == $value ) $output .= ' selected="selected"';
			$output .= '>' . $value . '</option>';
		}

		$output .= '<option value="-1">Other</option>';

		$output .= '</select>';

		return $output;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_log_services' ) ) :
	function wak_get_log_services() {

		return array(
			0  => 'Personal Note',
			1  => 'Rotate / Balance Tires',
			2  => 'Wheel Alignment',
			3  => 'Brake Service',
			4  => 'Oil Change',
			5  => 'Battery',
			6  => 'Radiator Flush & Fill',
			7  => 'Transmission Maint.',
			8  => 'Belts & Hoses',
			9  => 'Replace Tires',
			10 => 'Wiper Blades',
			11 => 'Spark Plugs',
			12 => 'Fuel Filter',
			13 => 'Air Filter',
			99 => 'Other'
		);

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_spent_on_car' ) ) :
	function wak_spent_on_car( $car_id = NULL ) {

		global $wpdb, $wak_mycar_log_db;

		$total = $wpdb->get_var( $wpdb->prepare( "SELECT SUM( amount ) FROM {$wak_mycar_log_db} WHERE car_id = %d;", $car_id ) );
		if ( $total === NULL )
			$total = 0;

		return number_format( $total, 2, '.', '' );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_update_maintenance_log' ) ) :
	function wak_update_maintenance_log() {

		$user = wp_get_current_user();

		if ( ! isset( $_POST['wak_new_log_entry'] ) || $_POST['wak_new_log_entry']['user_id'] != $user->ID || ! wp_verify_nonce( $_POST['wak_new_log_entry']['token'], 'wak-add-new-log-entry' ) ) return;

		$car_id    = absint( $_POST['wak_new_log_entry']['car'] );
		$milage    = absint( $_POST['wak_new_log_entry']['mileage'] );
		$date      = sanitize_text_field( $_POST['wak_new_log_entry']['date'] );
		$datecheck = explode( '-', $date );
		if ( count( $datecheck ) != 3 ) return;

		$date = strtotime( $date );

		$expenditures = array();
		foreach ( $_POST['wak_new_log_entry']['service'] as $service_id => $value ) {

			$service_id = absint( $service_id );
			$value      = sanitize_text_field( $value );

			if ( $service_id == '' || $value == '' || absint( $value ) == 0 ) continue;

			$expenditures[ $service_id ] = number_format( $value, 2, '.', '' );

		}

		if ( empty( $expenditures ) ) return;

		global $wpdb, $wak_mycar_log_db;

		$services = wak_get_log_services();
		foreach ( $expenditures as $service_id => $amount ) {

			$wpdb->insert(
				$wak_mycar_log_db,
				array(
					'car_id'   => $car_id,
					'user_id'  => $user->ID,
					'category' => $service_id,
					'entry'    => sprintf( 'Paid $ %s for %s', $amount, $services[ $service_id ] ),
					'amount'   => $amount,
					'time'     => $date,
					'mileage'  => $milage
				)
			);

		}

		wp_redirect( wak_theme_get_profile_url( $user ) . '?show=log' );
		exit;

	}
endif;
?>