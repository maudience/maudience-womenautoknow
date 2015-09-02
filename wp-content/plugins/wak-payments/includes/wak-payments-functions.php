<?php
// No dirrect access
if ( ! defined( 'WAK_PAYMENTS_VER' ) ) exit;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_payments_plugin_settings' ) ) :
	function wak_payments_plugin_settings() {

		$default = array(
			'disable_pay_signup' => 0,
			'templates' => array(
				'button'      => __( 'Go Pro', 'wakpayments' ),
				'title'       => __( 'Upgrade Auto Shop', 'wakpayments' ),
				'info'        => '<p>The content on this page can be set by administrators or staff in the admin area.</p><p>The content here should inform auto shop owners why they should pay for premium listings and what is included.</p>',
				'submit'      => __( 'Make Payment', 'wakpayments' ),
				'paid-signup' => __( 'Thank you for your payment. Your account has now been setup and is accessible via the login details your provided.', 'wakpayments' )
			),
			'terms_page_id'      => 0,
			'authorize_net_api'  => '',
			'authorize_net_key'  => '',
			'authorize_net_test' => 0
		);

		$saved = get_option( 'wak_payments_plugin_prefs', $default );
		
		return wp_parse_args( $saved, $default );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_sanitize_payment_plugin_settings' ) ) :
	function wak_sanitize_payment_plugin_settings( $new ) {

		if ( ! isset( $new['disable_pay_signup'] ) )
			$new['disable_pay_signup'] = 0;

		if ( ! isset( $new['authorize_net_test'] ) )
			$new['authorize_net_test'] = 0;

		$saved = wak_payments_plugin_settings();

		return wp_parse_args( $new, $saved );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_payment_plans' ) ) :
	function wak_payment_plans() {

		$default = array(
			'monthly_subscription' => array(
				'enabled' => 0,
				'cost'    => 0.00,
				'label'   => __( 'Monthly Subscription', 'wakpayments' ),
				'payment' => 'WAK Premium Auto Shop Subscription'
			),
			'one_time' => array(
				'enabled' => 0,
				'cost'    => 0.00,
				'length'  => 30,
				'label'   => __( 'One time payment', 'wakpayments' ),
				'payment' => 'WAK Premium Auto Shop Upgrade'
			),
			'pledged' => array(
				'enabled' => 0,
				'cost'    => 0.00,
				'label'   => __( 'Annual Fee', 'wakpayments' ),
				'payment' => 'WAK Pledged Auto Shop Payment'
			),
		);

		$saved = get_option( 'wak_payment_plans_prefs', $default );
		
		return wp_parse_args( $saved, $default );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_sanitize_payment_plans_settings' ) ) :
	function wak_sanitize_payment_plans_settings( $new ) {

		if ( ! isset( $new['monthly_subscription']['enabled'] ) )
			$new['monthly_subscription']['enabled'] = 0;

		if ( ! isset( $new['one_time']['enabled'] ) )
			$new['one_time']['enabled'] = 0;

		$saved = wak_payment_plans();

		return wp_parse_args( $new, $saved );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_payments_autoshop_actions' ) ) :
	function wak_payments_autoshop_actions( $options, $post_id, $user_id, $is_owner, $pledged ) {

		// Temporary Block until Audra signs up for Authorize.net
		return $options;

		$prefs = wak_payments_plugin_settings();

		if ( $is_owner && ! $pledged && ! autoshop_is_premium( $post_id ) )
			$options[] = '<button type="button" data-backdrop="static" class="go-pro-autoshop btn blue-button btn-xs" data-toggle="modal" data-target="#upgrade-autoshop-premium" data-shop="' . $post_id . '">' . esc_attr( $prefs['templates']['button'] ) . '</button>';

		return $options;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_upgrade_form' ) ) :
	function wak_autoshop_upgrade_form( $post = NULL ) {

		if ( ! is_object( $post ) ) return '';

		$prefs   = wak_payments_plugin_settings();
		$user_id = get_current_user_id();
		$plans   = wak_get_payment_plans( $user_id );

		$tabs = array();

		if ( strlen( $prefs['templates']['info'] ) > 0 )
			$tabs['premiuminfo'] = array(
				'class' => 'active',
				'title' => __( 'Information', 'wakpayments' )
			);

		$tabs['premiumpayment'] = array(
			'class' => ( ( isset( $tabs['premiuminfo'] ) ) ? '' : 'active' ),
			'title' => __( 'Payment', 'wakpayments' )
		);

?>
<form id="wak-upgrade-autoshop-form" method="post" action="" style="padding: 0 24px;">
	<input type="hidden" name="wak_upgrade_autoshop[post_id]" value="<?php echo $post->ID; ?>" />
	<input type="hidden" name="wak_upgrade_autoshop[token]" value="<?php echo wp_create_nonce( 'submit-new-wak-payment' . $post->ID ); ?>" />
	<div role="tabpanel">
		<ul class="nav nav-tabs" role="tablist">
<?php

		foreach ( $tabs as $tab_id => $tab ) {

			echo '<li role="presentation" class="' . $tab['class'] . '"><a href="#' . $tab_id . '" aria-controls="' . $tab_id . '" role="tab" data-toggle="tab">' . esc_attr( $tab['title'] ) . '</a></li>';

		}

?>
		</ul>
	</div>
	<div class="tab-content">

		<?php if ( strlen( $prefs['templates']['info'] ) > 0 ) : ?>

		<div role="tabpanel" class="tab-pane active" id="premiuminfo">
			<?php echo wpautop( wptexturize( $prefs['templates']['info'] ) ); ?>
		</div>

		<?php endif; ?>

		<div role="tabpanel" class="tab-pane" id="premiumpayment">
			<div class="row form-group flat">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-premium-">Autoshop</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12"><?php echo $post->post_title; ?></div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-premium-">Plan</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
<?php

		if ( ! empty( $plans ) ) {
			foreach ( $plans as $plan_id => $plan ) {

				if ( $plan_id == 'pledged' ) continue;

?>
					<label class="radio" for="wak-premium-<?php echo $plan_id; ?>">
						<input type="radio" class="toggle-cvv" data-type="<?php echo $plan_id; ?>" name="wak_upgrade_autoshop[plan]" id="wak-premium-<?php echo $plan_id; ?>" value="<?php echo esc_attr( $plan_id ); ?>" /> <?php echo esc_attr( $plan['label'] ); ?>
					</label>
<?php

			}
		}
		else {

			echo '<p>There are currently no payment plans available.</p>';

		}

?>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-premium-">Name on Card</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control" placeholder="First Name" name="wak_upgrade_autoshop[first_name]" id="wak-premium-" value="" />
					<input type="text" class="form-control" placeholder="Last Name" name="wak_upgrade_autoshop[last_name]" id="wak-premium-" value="" />
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-premium-">Card Number</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control" placeholder="required" name="wak_upgrade_autoshop[card]" id="wak-premium-" value="" />
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-premium-">Expiration Date</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<select name="wak_upgrade_autoshop[exp_mm]" id="wak-premium-" class="form-control short pull-left"><?php wak_list_option_months(); ?></select>
					<select name="wak_upgrade_autoshop[exp_yy]" id="wak-premium-" class="form-control short pull-left"><?php wak_list_option_card_years(); ?></select>
				</div>
			</div>
			<div class="row form-group" id="wak-card-cvv" style="display:none;">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-premium-">CVV</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control mini" maxlength="4" placeholder="required" name="wak_upgrade_autoshop[cvv]" id="wak-premium-" value="" /><br />
					<small><a href="https://www.cvvnumber.com/cvv.html" target="_blank" style="font-size:11px">What is my CVV code?</a></small>
				</div>
			</div>

			<?php if ( $prefs['terms_page_id'] != 0 ) : ?>

			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-premium-">Terms</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12"><label class="terms" for=""><input type="checkbox" name="wak_upgrade_autoshop[terms]" id="wak-premium-" value="1" /> I have read and accept the <a href="<?php echo get_permalink( $prefs['terms_page_id'] ); ?>" class="pink" target="_blank">terms and conditions</a>.</label></div>
			</div>

			<?php endif; ?>

			<div class="row">
				<div class="col-md-12 text-right" style="padding-top: 12px;">
					<input type="submit" class="btn btn-danger" id="submit-new-autoshop-payment-button" value="<?php echo esc_attr( $prefs['templates']['submit'] ); ?>" />
				</div>
			</div>
		</div>
	</div>
</form>
<div id="submitting-payment" style="display:none;">
	<h1 class="text-center pink"><i class="fa fa-spinner fa-spin blue"></i></h1>
	<p class="text-danger text-center"><?php _e( 'processing payment ...', 'wakpayments' ); ?></p>
</div>
<script type="text/javascript">
jQuery(function($) {

	$( '#wak-upgrade-autoshop-form' ).on( 'click', 'input.toggle-cvv', function(){

		if ( $(this).data( 'type' ) == 'monthly_subscription' )
			$( '#wak-card-cvv' ).show();
		else
			$( '#wak-card-cvv' ).hide();

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
if ( ! function_exists( 'wak_get_payment_plans' ) ) :
	function wak_get_payment_plans( $user_id = NULL ) {

		$plans = wak_payment_plans();

		$available = array();
		foreach ( $plans as $plan_id => $plan ) {

			if ( $plan['enabled'] == 0 ) continue;

			$available[ $plan_id ] = $plan;

		}

		return $available;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_sanitize_and_validate_form' ) ) :
	function wak_sanitize_and_validate_form( $data = array() ) {

		$errors = array();
		$clean  = array();
		$now    = current_time( 'timestamp' );

		$plan = sanitize_key( $data['plan'] );
		if ( ! in_array( $data['plan'], array( 'monthly_subscription', 'one_time' ) ) )
			$errors[] = 'No payment plan selected.';
		else
			$clean['plan'] = $plan;

		$post_id = absint( $data['post_id'] );
		if ( $post_id == 0 || get_post_status( $post_id ) === false )
			$errors[] = 'Auto shop not found. Please reload the page and try again.';
		else
			$clean['post_id'] = $post_id;

		// Billing address validation
		$first_name = sanitize_text_field( $data['first_name'] );
		if ( strlen( $first_name ) == 0 )
			$errors[] = 'Please enter your first name.';
		else
			$clean['first_name'] = $first_name;

		$last_name = sanitize_text_field( $data['last_name'] );
		if ( strlen( $last_name ) == 0 )
			$errors[] = 'Please enter your last name.';
		else
			$clean['last_name'] = $last_name;

		// Validate Card
		$card = sanitize_text_field( $data['card'] );
		if ( strlen( $card ) < 12 )
			$errors[] = 'Invalid credit card number.';
		else
			$clean['card'] = $card;

		$month = sanitize_text_field( $data['exp_mm'] );
		$year  = sanitize_text_field( $data['exp_yy'] );

		if ( strtotime( $month . '/' . date( 'd', $now ) . '/' . $year ) < $now )
			$errors[] = 'Invalid card expiration date.';
		else {
			$clean['exp_mm'] = $month;
			$clean['exp_yy'] = $year;
		}

		if ( $data['plan'] == 'monthly_subscription' ) {

			$cvv = sanitize_text_field( $data['cvv'] );
			if ( strlen( $cvv ) < 3 || strlen( $cvv ) > 4 )
				$errors[] = 'Invalid CVV code.';
			else
				$clean['cvv'] = $cvv;

		}

		// Terms agreed to?
		if ( ! isset( $data['terms'] ) || $data['terms'] == 0 )
			$errors[] = 'You must accept the terms & conditions.';
		else
			$clean['terms'] = 1;

		// Append errros
		$clean['errors'] = $errors;

		return $clean;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_new_payment_id' ) ) :
	function wak_new_payment_id( $plan = false ) {

		$prefix = 'C';
		if ( $plan != 'one_time' )
			$prefix = 'S';

		global $wpdb, $wak_payments_db;

		do {

			$id = $prefix . strtoupper( wp_generate_password( 6, false, false ) );
			$query = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_payments_db} WHERE payment_id = %s;", $id ) );

		} while ( ! empty( $query ) );
	
		return $id;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_charge_payment' ) ) :
	function wak_charge_payment( $data = array() ) {

		$plans  = wak_payment_plans();
		$now    = current_time( 'timestamp' );

		if ( ! isset( $data['plan'] ) || ! array_key_exists( $data['plan'], $plans ) ) return 'This plan is not available.';

		if ( $plans[ $data['plan'] ]['enabled'] == 0 ) return 'The selected plan is no longer available.';

		$cost = number_format( $plans[ $data['plan'] ]['cost'], 2, '.', '' );
		$data['payment_id'] = wak_new_payment_id( $data['plan'] );

		if ( ! isset( $data['user_id'] ) )
			$data['user_id'] = get_current_user_id();

		$new_payment = array(
			'payment_id'  => $data['payment_id'],
			'status'      => 0,
			'type'        => $data['plan'],
			'amount_paid' => $cost,
			'time'        => $now,
			'user_id'     => $user_id,
			'object_id'   => $data['post_id'],
			'charged'     => 1,
			'first_name'  => $data['first_name'],
			'last_name'   => $data['last_name'],
			'IP'          => $_SERVER['REMOTE_ADDR']
		);

		if ( $data['plan'] == 'monthly_subscription' ) {

			$subscription_id = wak_authorize_net_subscription( $data, $plans[ $data['plan'] ] );
			if ( ! is_array( $subscription_id ) ) {
				$new_payment['subscription_id'] = $subscription_id;
				$new_payment['status'] = 2;
			}

			else return $subscription_id['errors'];

		}

		elseif ( $data['plan'] == 'one_time' ) {

			$transaction_id = wak_authorize_net_charge( $data, $plans[ $data['plan'] ] );
			if ( ! is_array( $transaction_id ) ) {
				$new_payment['transaction_id'] = $transaction_id;
				$new_payment['status'] = 1;
			}

			else return $transaction_id['errors'];

		}

		elseif ( $data['plan'] == 'pledged' ) {

			$subscription_id = wak_authorize_net_subscription( $data, $plans[ $data['plan'] ], true );
			if ( ! is_array( $subscription_id ) ) {
				$new_payment['subscription_id'] = $subscription_id;
				$new_payment['status'] = 2;
			}

			else return $subscription_id['errors'];

		}

		global $wpdb, $wak_payments_db;

		$wpdb->insert(
			$wak_payments_db,
			$new_payment,
			array( '%s', '%d', '%s', '%f', '%d', '%d', '%d', '%d', '%s' )
		);

		return $new_payment;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_upgrade_autoshop' ) ) :
	function wak_upgrade_autoshop( $upgrade_to = 'premium', $autoshop_id = NULL, $ends = '' ) {

		$now = current_time( 'timestamp' );

		if ( $ends == 'month' ) {

			$until = strtotime( '+1 month', $now );

		}
		else {

			$until = $now + ( $ends * DAY_IN_SECONDS );

		}

		if ( $upgrade_to == 'premium' )
			update_post_meta( $autoshop_id, 'premium_until', date( 'm/d/Y', $until ) );

		return date( 'm/d/Y', $until );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_list_option_months' ) ) :
	function wak_list_option_months( $selected = '' ) {
		$months = array (
			"01"  =>  __( '(01) January', 'wakpayments' ),
			"02"  =>  __( '(02) February', 'wakpayments' ),
			"03"  =>  __( '(03) March', 'wakpayments' ),
			"04"  =>  __( '(04) April', 'wakpayments' ),
			"05"  =>  __( '(05) May', 'wakpayments' ),
			"06"  =>  __( '(06) June', 'wakpayments' ),
			"07"  =>  __( '(07) July', 'wakpayments' ),
			"08"  =>  __( '(08) August', 'wakpayments' ),
			"09"  =>  __( '(09) September', 'wakpayments' ),
			"10"  =>  __( '(10) October', 'wakpayments' ),
			"11"  =>  __( '(11) November', 'wakpayments' ),
			"12"  =>  __( '(12) December', 'wakpayments' )
		);

		foreach ( $months as $number => $text ) {
			echo '<option value="' . $number . '"';
			if ( $selected == $number ) echo ' selected="selected"';
			echo '>' . $text . '</option>';
		}
	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_list_option_card_years' ) ) :
	function wak_list_option_card_years( $selected = '', $number = 16 ) {

		$now = current_time( 'timestamp' );

		$yyyy = date( 'Y', $now );
		$count = 0;
		$options = array();

		while ( $count <= (int) $number ) {
			$count ++;
			if ( $count > 1 ) {
				$yyyy++;
			}
			$options[ $yyyy ] = $yyyy;
		}

		foreach ( $options as $key => $value ) {
			echo '<option value="' . $key . '"';
			if ( $selected == $key ) echo ' selected="selected"';
			echo '>' . $value . '</option>';
		}
	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_count_payments_by_status' ) ) :
	function wak_count_payments_by_status( $status = NULL ) {

		global $wpdb, $wak_payments_db;

		if ( $status == -1 )
			$count = $wpdb->get_var( "
				SELECT COUNT(*) 
				FROM {$wak_payments_db};" );

		else
			$count = $wpdb->get_var( $wpdb->prepare( "
				SELECT COUNT(*) 
				FROM {$wak_payments_db} 
				WHERE status = %d;", $status ) );

		if ( $count === NULL ) $count = 0;

		return $count;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_payment_status' ) ) :
	function wak_get_payment_status( $status = NULL ) {

		if ( $status == 0 )
			return __( 'Pending', '' );
		elseif ( $status == 1 )
			return __( 'Completed', '' );
		elseif ( $status == 2 )
			return __( 'Active', '' );
		elseif ( $status == 3 )
			return __( 'Cancelled', '' );

		return __( 'Deleted', '' );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_user_has_payments' ) ) :
	function wak_user_has_payments( $autoshop_id = NULL, $user_id = NULL ) {

		global $wpdb, $wak_payments_db;

		$check = $wpdb->get_var( $wpdb->prepare( "
			SELECT * 
			FROM {$wak_payments_db} 
			WHERE object_id = %d 
			AND user_id = %d;", $user_id ) );

		if ( $check === NULL )
			return false;

		return true;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'autoshop_has_subscription' ) ) :
	function autoshop_has_subscription( $autoshop_id = NULL ) {

		global $wpdb, $wak_payments_db;

		$check = $wpdb->get_var( $wpdb->prepare( "
			SELECT id 
			FROM {$wak_payments_db} 
			WHERE object_id = %d 
			AND status = 2 
			AND subscription_id != '' 
			ORDER BY time LIMIT 0,1;", $autoshop_id ) );

		if ( $check === NULL )
			return false;

		return true;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_autoshop_subscription' ) ) :
	function wak_get_autoshop_subscription( $autoshop_id = NULL ) {

		global $wpdb, $wak_payments_db;

		return $wpdb->get_row( $wpdb->prepare( "
			SELECT * 
			FROM {$wak_payments_db} 
			WHERE object_id = %d 
			AND status = 2 
			AND subscription_id != '' 
			ORDER BY time LIMIT 0,1;", $autoshop_id ) );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_autoshop_payment' ) ) :
	function wak_get_autoshop_payment( $autoshop_id = NULL ) {

		global $wpdb, $wak_payments_db;

		return $wpdb->get_row( $wpdb->prepare( "
			SELECT * 
			FROM {$wak_payments_db} 
			WHERE object_id = %d 
			AND status = 2 
			AND transaction_id != '' 
			ORDER BY time LIMIT 0,1;", $autoshop_id ) );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_cancel_autoshop_premium_by_admin' ) ) :
	function wak_cancel_autoshop_premium_by_admin( $post_id ) {

		if ( autoshop_has_subscription( $post_id ) ) {

			$subscription = wak_get_autoshop_subscription( $post_id );

			if ( isset( $subscription->subscription_id ) ) {

				$result = wak_authorize_net_cancel_subscription( $subscription->subscription_id );

				if ( $result === true ) {

					global $wpdb, $wak_payments_db;

					$wpdb->update(
						$wak_payments_db,
						array( 'status' => 3 ),
						array( 'id' => $subscription->id ),
						array( '%d' ),
						array( '%d' )
					);

				}

			}

		}

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_payments_tab' ) ) :
	function wak_autoshops_payments_tab( $autoshop_id, $user_id, $data ) {

		if ( ! wak_user_has_payments( $autoshop_id, $user_id ) ) return;

		echo '<li role="presentation"><a href="#paymenthistory" aria-controls="paymenthistory" role="tab" data-toggle="tab">Premium</a></li>';

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_payment_tab_content' ) ) :
	function wak_autoshops_payment_tab_content( $autoshop_id, $user_id, $data ) {

		if ( ! wak_user_has_payments( $autoshop_id, $user_id ) ) return;

		$is_premium       = autoshop_is_premium( $autoshop_id );
		$is_pledged       = autoshop_has_pledged( $autoshop_id );
		$has_subscription = autoshop_has_subscription( $autoshop_id );
		$ends             = get_post_meta( $autoshop_id, 'premium_until', true );

?>
<div role="tabpanel" class="tab-pane" id="paymenthistory">
	<div class="row form-group slim">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="">Current Status</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12"><?php if ( $is_pledged ) echo 'Pledged'; elseif ( $is_premium ) echo 'Premium'; else echo 'Standard'; ?></div>
	</div>
	<?php if ( $is_premium ) : ?>
	<div class="row form-group">
		<div class="col-md-4 col-sm-4 col-xs-12"><label><?php if ( $has_subscription ) echo 'Renewed'; else echo 'Ends'; ?></label></div>
		<div class="col-md-8 col-sm-8 col-xs-12"><strong><?php echo date_i18n( get_option( 'date_format'), strtotime( $ends ) ); ?></strong><?php if ( $has_subscription ) : ?> - <a href="#" class="pink">Cancel Subscription</a><?php endif; ?></div>
	</div>
	<?php endif; ?>
	<div class="row form-group slim">
		<div class="col-md-4 col-sm-4 col-xs-12"><label>Payment History</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12">
			<?php wak_my_payment_history_list( $user_id ); ?>
		</div>
	</div>
</div>
<?php

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_my_payment_history_list' ) ) :
	function wak_my_payment_history_list( $user_id = NULL ) {

		if ( $user_id === NULL ) return '';

		global $wpdb, $wak_payments_db;

		$history = $wpdb->get_results( $wpdb->prepare( "
			SELECT * 
			FROM {$wak_payments_db} 
			WHERE user_id = %d 
			ORDER BY time DESC LIMIT 0,5;", $user_id ) );

		if ( empty( $history ) ) return '<p>No payment history found.</p>';

?>
<div id="my-wak-payment-history">
<?php

		$date_format = get_option( 'date_format' );
		foreach ( $history as $entry ) {

?>
	<div class="row">
		<div class="col-md-4"><strong>Date</strong></div>
		<div class="col-md-4"><strong>Amount</strong></div>
		<div class="col-md-4"><strong>Payment ID</strong></div>
	</div>
	<div class="row">
		<div class="col-md-4"><?php echo date_i18n( $date_format, $entry->time ); ?></div>
		<div class="col-md-4"><?php echo '$ ' . number_format( $entry->amount_paid, 2, '.', ',' ); ?></div>
		<div class="col-md-4"><?php echo $entry->payment_id; ?></div>
	</div>
<?php

		}

?>
</div>
<?php

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_process_payment_admin_actions' ) ) :
	function wak_process_payment_admin_actions() {

		if ( ! current_user_can( 'moderate_comments' ) ) return;

		if ( isset( $_GET['action'] ) && isset( $_GET['payment_id'] ) && strlen( $_GET['payment_id'] ) > 0 && in_array( $_GET['action'], array( 'approve', 'cancel', 'delete' ) ) ) {

			$act      = sanitize_key( $_GET['action'] );
			$entry_id = absint( $_GET['payment_id'] );
			$plans    = wak_payment_plans();

			global $wpdb, $wak_payments_db;

			$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_payments_db} WHERE id = %d;", $entry_id ) );

			if ( isset( $entry->id ) ) {

				if ( $act == 'approve' ) {

					$until = 'month';
					if ( $entry->type == 'one_time' )
						$until = $plans['one_time']['length'];

					wak_upgrade_autoshop( 'premium', $entry->object_id, $until );

					$wpdb->update(
						$wak_payments_db,
						array( 'status' => 1, 'transaction_id' => 'WAKapproved' ),
						array( 'id' => $entry_id ),
						array( '%d', '%s' ),
						array( '%d' )
					);

					$url = remove_query_arg( array( 'action', 'payment_id' ) );
					$url = add_query_arg( array( 'updated' => 1 ), $url );
					wp_safe_redirect( $url );
					exit;

				}

				elseif ( $act == 'cancel' ) {

					$result = wak_authorize_net_cancel_subscription( $entry->subscription_id );

					$url = remove_query_arg( array( 'action', 'payment_id', 'paged', 's' ) );

					if ( $result === true ) {

						$wpdb->update(
							$wak_payments_db,
							array( 'status' => 3 ),
							array( 'id' => $entry_id ),
							array( '%d' ),
							array( '%d' )
						);

						$url = add_query_arg( array( 'updated' => 3, 'result' => urlencode( $result ) ), $url );

					}
					else {

						$url = add_query_arg( array( 'error' => 1, 'message' => urlencode( $result ) ), $url );

					}

					wp_safe_redirect( $url );
					exit;

				}
				elseif ( $act == 'delete' ) {

					$wpdb->delete(
						$wak_payments_db,
						array( 'id' => $entry_id ),
						array( '%d' )
					);

					if ( $entry->status == 0 )
						delete_post_meta( $entry->object_id, 'premium_until' );

					$url = remove_query_arg( array( 'action', 'payment_id' ) );
					$url = add_query_arg( array( 'deleted' => 1 ), $url );
					wp_safe_redirect( esc_url( $url ) );
					exit;

				}

			}

		}

		if ( isset( $_GET['action'] ) && $_GET['action'] != '-1' && isset( $_GET['payments'] ) && ! empty( $_GET['payments'] ) ) {

			global $wpdb, $wak_payments_db;

			$act      = sanitize_key( $_GET['action'] );
			$payments = array();
			$done     = 0;

			foreach ( $_GET['payments'] as $payment_id ) {
				if ( $payment_id == '' || $payment_id == 0 ) continue;
				$payments[] = absint( $payment_id );
			}

			if ( ! empty( $payments ) ) {

				if ( $act == 'delete' ) {

					foreach ( $payments as $entry_id ) {

						$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_payments_db} WHERE id = %d;", $entry_id ) );

						if ( $entry === NULL ) continue;

						$wpdb->delete(
							$wak_payments_db,
							array( 'id' => $entry_id ),
							array( '%d' )
						);

						if ( $entry->status == 0 )
							delete_post_meta( $entry->object_id, 'premium_until' );

						$done++;

					}

					$url = remove_query_arg( array( 'action', 'payments' ) );
					$url = add_query_arg( array( 'deleted' => 1, 'multi' => $done ), $url );
					wp_safe_redirect( $url );
					exit;

				}

				elseif ( $act == 'approve' ) {

					foreach ( $payments as $entry_id ) {

						$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_payments_db} WHERE id = %d;", $entry_id ) );

						if ( $entry === NULL ) continue;

						$until = 'month';
						if ( $entry->type == 'one_time' )
							$until = $plans['one_time']['length'];

						wak_upgrade_autoshop( 'premium', $entry->object_id, $until );

						$wpdb->update(
							$wak_payments_db,
							array( 'status' => 1, 'transaction_id' => 'WAKapproved' ),
							array( 'id' => $entry_id ),
							array( '%d', '%s' ),
							array( '%d' )
						);

						$done++;

					}

					$url = remove_query_arg( array( 'action', 'payments' ) );
					$url = add_query_arg( array( 'updated' => 1, 'multi' => $done ), $url );
					wp_safe_redirect( $url );
					exit;

				}

			}

		}

		if ( isset( $_REQUEST['wp_screen_options']['option'] ) && isset( $_REQUEST['wp_screen_options']['value'] ) ) {
			
			if ( $_REQUEST['wp_screen_options']['option'] == 'wak_payments_per_page' ) {
				$value = absint( $_REQUEST['wp_screen_options']['value'] );
				update_user_meta( get_current_user_id(), 'wak_payments_per_page', $value );
			}

		}

	}
endif;

?>