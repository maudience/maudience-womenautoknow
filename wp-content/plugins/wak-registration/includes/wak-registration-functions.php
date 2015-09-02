<?php
// No dirrect access
if ( ! defined( 'WAK_REGISTER_VER' ) ) exit;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_plugin_settings' ) ) :
	function wak_registration_plugin_settings() {

		$default = array(
			'signup_page_id'  => 0,
			'recover_page_id' => 0,
			'success_signup'  => 'Welcome to the WAK Community! An email has been sent to you to finilize the process.',
			'verify_email'    => 1,
			'emails'          => array(
				'activate'       => '',
				'resetpass'      => ''
			),
			'captcha_sitekey' => '',
			'captcha_secret'  => ''
		);

		$saved = get_option( 'wak_registration_plugin_prefs', $default );
		
		return wp_parse_args( $saved, $default );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_sanitize_registration_plugin_settings' ) ) :
	function wak_sanitize_registration_plugin_settings( $new ) {

		$saved = wak_registration_plugin_settings();

		if ( ! isset( $new['verify_email'] ) )
			$new['verify_email'] = 0;

		return wp_parse_args( $new, $saved );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_generate_username' ) ) :
	function wak_generate_username( $email ) {

		$emailparts = explode( '@', $email );
		$username   = str_replace( array( '.', '-', '_' ), '', $emailparts[0] );

		if ( username_exists( $username ) !== NULL || pending_username_exists( $username ) ) {

			do {

				$username = $username . md_random( 1, 9999 );

			} while ( username_exists( $username ) !== NULL || pending_username_exists( $username ) );

		}

		return $username;

	}
endif;

	

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_register_new_driver' ) ) :
	function wak_register_new_driver( $submitted_data ) {

		if ( ! wak_verify_captcha( $_POST['g-recaptcha-response'], $_POST['g-recaptcha-key'] ) ) return array( 'captcha' => 'Could not verify that you are a human.' );

		if ( isset( $_POST['recaptcha'] ) && ! empty( $_POST['recaptcha'] ) ) return array( 'captcha' => 'Could not verify that you are a human. (error 2)' );

		$errors  = array();
		$data    = array();
		$default = array(
			'username'   => '',
			'email'      => '',
			'pwd1'       => '',
			'pwd2'       => '',
			'first_name' => '',
			'last_name'  => '',
			'zip'        => '',
			'state'      => '',
			'terms'      => 0,
			'newsletter' => 0
		);

		$prefs          = wak_registration_plugin_settings();
		$submitted_data = wp_parse_args( $submitted_data, $default );

		$data['type']            = 'driver';
		$data['activation_code'] = wak_new_activation_key();

		$email = sanitize_text_field( $submitted_data['email'] );
		if ( ! is_email( $email ) )
			$errors['email'] = 'Invalid email. Please check the email and try again.';

		elseif ( email_exists( $email ) )
			$errors['email'] = 'This email is already in use. Please provide a different one.';

		elseif ( pending_email_exists( $email ) )
			$errors['email'] = 'This email is already pending membership. Please contact support in order to re-send your activation email.';

		else
			$data['email'] = $email;

		$data['username'] = wak_generate_username( $email );

		$pwd1 = sanitize_text_field( $submitted_data['pwd1'] );
		$pwd2 = sanitize_text_field( $submitted_data['pwd2'] );
		if ( $pwd1 == '' )
			$errors['pwd1'] = 'Password can not be empty.';
		elseif ( ! is_ok_password( $pwd1, $username ) )
			$errors['pwd1'] = 'Your selected password is too easy. Please provide a stronger password.';
		elseif ( $pwd1 != $pwd2 )
			$errors['pwd1'] = 'Password confirmation mismatch.';
		else {

			if ( $prefs['verify_email'] == 1 )
				$data['password'] = wp_hash_password( $pwd1 );
			else
				$data['password'] = $pwd1;

		}

		if ( ! empty( $errors ) ) return $errors;

		$first_name = sanitize_text_field( $submitted_data['first_name'] );
		if ( $first_name == '' )
			$errors['first_name'] = 'Please enter your first name.';
		else
			$data['first_name'] = $first_name;

		$last_name = sanitize_text_field( $submitted_data['last_name'] );
		if ( $last_name == '' )
			$errors['last_name'] = 'Please enter your last name.';
		else
			$data['last_name'] = $last_name;

		if ( $submitted_data['terms'] == 0 )
			$errors['terms'] = 'You must accept the terms and conditions.';

		if ( ! empty( $errors ) ) return $errors;

		$data['newsletter'] = absint( $submitted_data['newsletter'] );
		$data['IP']         = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		$data['time']       = current_time( 'timestamp' );

		if ( $prefs['verify_email'] == 1 ) {

			global $wpdb, $wak_pending_registrations_db;

			$wpdb->insert(
				$wak_pending_registrations_db,
				$data,
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%d' )
			);

			wak_send_email_activation( $data );

			return true;

		}
		else {

			$new_user_id = wp_insert_user( array(
				'user_login'      => $data['username'],
				'user_pass'       => $data['password'],
				'user_email'      => $data['email'],
				'first_name'      => $data['first_name'],
				'last_name'       => $data['last_name'],
				'display_name'    => $data['first_name'] . ' ' . $data['last_name'],
				'role'            => 'subscriber'
			) );

			if ( ! is_wp_error( $new_user_id ) ) {

				add_user_meta( $new_user_id, 'newsletter', $data['newsletter'], true );

				return true;

			}
			else {

				$errors['email'] = 'Could not register you as a user. Error given: ' . $data['username'];
				return $errors;

			}

		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_register_new_shop' ) ) :
	function wak_register_new_shop( $submitted_data ) {

		if ( ! wak_verify_captcha( $_POST['g-recaptcha-response'], $_POST['g-recaptcha-key'] ) ) return array( 'captcha' => 'Could not verify that you are a human.' );

		global $wpdb, $wak_pending_registrations_db, $wak_payments_db;

		$errors  = array();
		$data    = array();
		$billing = array();
		$card    = array();
		$default = array(
			'username'         => '',
			'email'            => '',
			'pwd1'             => '',
			'pwd2'             => '',
			'shopname'         => '',
			'address1'         => '',
			'city'             => '',
			'zip'              => '',
			'state'            => '',
			'website'          => '',
			'facebook'         => '',
			'twitter'          => '',
			'phone'            => '',
			'type'             => 0,
			'terms'            => 0,
			'first_name'       => '',
			'last_name'        => '',
			'billing-address1' => '',
			'billing-city'     => '',
			'billing-zip'      => '',
			'billing-state'    => '',
			'card-name'        => '',
			'card-number'      => '',
			'card-mm'          => '',
			'card-yy'          => ''
		);

		$prefs = wak_registration_plugin_settings();

		$submitted_data = wp_parse_args( $submitted_data, $default );

		$data['type'] = 'auto shop';
		if ( $submitted_data['type'] == 1 )
			$data['type'] = 'premium auto shop';

		$data['activation_code'] = wak_new_activation_key();

		$email = sanitize_text_field( $submitted_data['email'] );
		if ( ! is_email( $email ) )
			$errors['email'] = 'Invalid email. Please check the email and try again.';

		elseif ( email_exists( $email ) )
			$errors['email'] = 'This email is already in use. Please provide a different one.';

		elseif ( pending_email_exists( $email ) )
			$errors['email'] = 'This email is already pending membership. Please contact support in order to re-send your activation email.';

		else
			$data['email'] = $email;

		$data['username'] = wak_generate_username( $email );

		$pwd1 = sanitize_text_field( $submitted_data['pwd1'] );
		$pwd2 = sanitize_text_field( $submitted_data['pwd2'] );
		if ( $pwd1 == '' )
			$errors['pwd1'] = 'Password can not be empty.';
		elseif ( ! is_ok_password( $pwd1, $username ) )
			$errors['pwd1'] = 'Your selected password is too easy. Please provide a stronger password.';
		elseif ( $pwd1 != $pwd2 )
			$errors['pwd1'] = 'Password confirmation mismatch.';
		else
			$data['password'] = wp_hash_password( $pwd1 );

		if ( ! empty( $errors ) ) return $errors;

		$shopname = sanitize_text_field( $submitted_data['shopname'] );
		if ( $shopname == '' )
			$errors['shopname'] = 'Please enter the auto shop name.';

		$address = sanitize_text_field( $submitted_data['address1'] );
		if ( $address == '' )
			$errors['address1'] = 'Please enter your business address.';

		$city = sanitize_text_field( $submitted_data['city'] );
		if ( $city == '' )
			$errors['city'] = 'Please enter a city.';

		$first_name = sanitize_text_field( $submitted_data['first_name'] );
		if ( $first_name == '' )
			$errors['first_name'] = 'Please enter your first name.';
		else
			$data['first_name'] = $first_name;

		$last_name = sanitize_text_field( $submitted_data['last_name'] );
		if ( $last_name == '' )
			$errors['last_name'] = 'Please enter your last name.';
		else
			$data['last_name'] = $last_name;

		$zip = sanitize_text_field( $submitted_data['zip'] );
		$zip = str_replace( ' ', '', $zip );
		if ( strlen( $zip ) <> 5 )
			$errors['zip'] = 'Invalid zip code.';
		else
			$data['zip'] = absint( $zip );

		$state = sanitize_text_field( $submitted_data['state'] );
		if ( strlen( $state ) <> 2 )
			$errors['state'] = 'Invalid state.';
		else
			$data['state'] = $state;

		if ( $submitted_data['terms'] == 0 )
			$errors['terms'] = 'You must accept the terms and conditions.';

		if ( ! empty( $errors ) ) return $errors;

		$data['newsletter'] = 0;

		// Free accounts need verification
		if ( $submitted_data['type'] == 0 ) {

			$data['IP']         = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
			$data['time']       = current_time( 'timestamp' );

			$new_autoshop_id = wp_insert_post( array(
				'post_type'   => 'autoshops',
				'post_title'  => $shopname,
				'post_status' => 'pending',
				'post_author' => 1
			) );

			if ( ! is_wp_error( $new_autoshop_id ) ) {

				$data['autoshop_id'] = $new_autoshop_id;

				$wpdb->insert(
					$wak_pending_registrations_db,
					$data,
					array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d' )
				);

				add_post_meta( $new_autoshop_id, 'address1', $address, true );
				add_post_meta( $new_autoshop_id, 'city',     $city, true );
				add_post_meta( $new_autoshop_id, 'zip',      $zip, true );
				add_post_meta( $new_autoshop_id, 'state',    $state, true );

				add_post_meta( $new_autoshop_id, 'phone',    sanitize_text_field( $submitted_data['phone'] ), true );
				add_post_meta( $new_autoshop_id, 'website',  sanitize_text_field( $submitted_data['website'] ), true );
				add_post_meta( $new_autoshop_id, 'facebook', sanitize_text_field( $submitted_data['facebook'] ), true );
				add_post_meta( $new_autoshop_id, 'twitter',  sanitize_text_field( $submitted_data['twitter'] ), true );

			}

			wak_send_email_activation( $data );

			return true;

		}

		// Paid customers do not need verifiacation
		$type = 1;
		if ( $submitted_data['type'] == 2 )
			$type = 2;

		$first_name = sanitize_text_field( $submitted_data['first_name'] );
		if ( $first_name == '' )
			$errors['first_name'] = 'Please enter your first name.';
		else
			$data['first_name'] = $first_name;

		$last_name = sanitize_text_field( $submitted_data['last_name'] );
		if ( $last_name == '' )
			$errors['last_name'] = 'Please enter your last name.';
		else
			$data['last_name'] = $last_name;

		$billing_address = sanitize_text_field( $submitted_data['billing-address1'] );
		if ( $billing_address == '' )
			$errors['billing-address1'] = 'Please provide a billing address.';
		else
			$billing['address'] = $billing_address;

		$billing_city = sanitize_text_field( $submitted_data['billing-city'] );
		if ( $billing_city == '' )
			$errors['billing-city'] = 'Please provide a billing city.';
		else
			$billing['city'] = $billing_city;

		$billing_zip = sanitize_text_field( $submitted_data['billing-zip'] );
		if ( $billing_zip == '' )
			$errors['billing-zip'] = 'Please provide a billing zip code.';
		else
			$billing['zip'] = $billing_zip;

		$billing_state = sanitize_text_field( $submitted_data['billing-state'] );
		if ( strlen( $billing_state ) <> 2 )
			$errors['billing-state'] = 'Invalid billing state.';
		else
			$billing['state'] = $billing_state;

		if ( ! empty( $errors ) ) return $errors;

		$card_number = sanitize_text_field( $submitted_data['card-number'] );
		if ( $card_number == '' )
			$errors['card-number'] = 'Missing credit card number.';
		elseif ( strlen( $card_number ) < 12 )
			$errors['card-number'] = 'Invalid credit card number.';
		else
			$card['card'] = $card_number;

		$card_cvv = sanitize_text_field( $submitted_data['card-cvv'] );
		if ( $card_cvv == '' )
			$errors['card-cvv'] = 'Missing CVV number.';
		elseif ( strlen( $card_cvv ) < 3 )
			$errors['card-cvv'] = 'Invalid CVV number.';
		else
			$card['cvv'] = $card_cvv;

		$month = sanitize_text_field( $submitted_data['card-mm'] );
		$year  = sanitize_text_field( $submitted_data['card-yy'] );
		$now   = current_time( 'timestamp' );

		if ( strtotime( $month . '/' . date( 'd', $now ) . '/' . $year ) < $now )
			$errors['card-exp'] = 'Invalid card expiration date.';
		else {
			$card['exp_mm'] = $month;
			$card['exp_yy'] = $year;
		}

		if ( ! empty( $errors ) ) return $errors;

		$new_autoshop_id = wp_insert_post( array(
			'post_type'   => 'autoshops',
			'post_title'  => $shopname,
			'post_status' => 'pending',
			'post_author' => 1
		) );

		if ( ! is_wp_error( $new_autoshop_id ) ) {

			add_post_meta( $new_autoshop_id, 'address1', $address, true );
			add_post_meta( $new_autoshop_id, 'city',     $city, true );
			add_post_meta( $new_autoshop_id, 'zip',      $zip, true );
			add_post_meta( $new_autoshop_id, 'state',    $state, true );

			add_post_meta( $new_autoshop_id, 'phone',    sanitize_text_field( $submitted_data['phone'] ), true );
			add_post_meta( $new_autoshop_id, 'website',  sanitize_text_field( $submitted_data['website'] ), true );
			add_post_meta( $new_autoshop_id, 'facebook', sanitize_text_field( $submitted_data['facebook'] ), true );
			add_post_meta( $new_autoshop_id, 'twitter',  sanitize_text_field( $submitted_data['twitter'] ), true );

		}
		else return array( 'autoshop' => 'Could not save your auto shop. Please try again or contact support for further assistance.' );

		if ( function_exists( 'wak_charge_payment' ) ) {

			$result = wak_charge_payment( array(
				'plan'            => ( ( $type == 1 ) ? 'monthly_subscription' : 'pledged' ),
				'first_name'      => $data['first_name'],
				'last_name'       => $data['last_name'],
				'card'            => $card['card'],
				'exp_mm'          => $card['exp_mm'],
				'exp_yy'          => $card['exp_yy'],
				'cvv'             => $card['cvv'],
				'post_id'         => $new_autoshop_id,
				'billing-address' => $billing['address'],
				'billing-city'    => $billing['city'],
				'billing-zip'     => $billing['zip'],
				'billing-state'   => $billing['state'],
				'user_id'         => 0
			) );

			// Failed
			if ( ! is_array( $result ) ) {

				wp_delete_post( $new_autoshop_id, true );

				return array( 'card-number' => $result );

			}

			else {

				wp_update_post( array(
					'ID'          => $new_autoshop_id,
					'post_status' => 'publish'
				) );

				if ( $type == 1 ) {
					add_post_meta( $new_autoshop_id, 'premium_until', date( 'm/d/Y', strtotime( '+1 month', $now ) ), true );
				}
				else {
					add_post_meta( $new_autoshop_id, 'pledged', 1, true );
					add_post_meta( $new_autoshop_id, 'pledged_date', date( 'Y-m-d', $now ), true );
				}

				// Add user
				$new_user_id = wp_insert_user( array(
					'user_login'      => $data['username'],
					'user_pass'       => $pwd1,
					'user_email'      => $data['email'],
					'first_name'      => $data['first_name'],
					'last_name'       => $data['last_name'],
					'display_name'    => $data['first_name'],
					'role'            => 'subscriber',
					'user_registered' => date( 'Y-m-d H:i:s', $now )
				) );

				if ( ! is_wp_error( $new_user_id ) ) {

					// Update the payment with the new users ID
					$payment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_payments_db} WHERE payment_id = %s;", $result['payment_id'] ) );
					if ( isset( $payment->id ) )
						$wpdb->update(
							$wak_payments_db,
							array( 'user_id'    => $new_user_id ),
							array( 'payment_id' => $payment->payment_id ),
							array( '%d' ),
							array( '%d' )
						);

					add_user_meta( $new_user_id, 'newsletter', 0, true );

					add_post_meta( $new_autoshop_id, 'owner_id', $new_user_id, true );
					add_post_meta( $new_autoshop_id, 'added_by', $new_user_id, true );

					add_user_meta( $new_user_id, 'billing-address', $billing['address'], true );
					add_user_meta( $new_user_id, 'billing-city', $billing['city'], true );
					add_user_meta( $new_user_id, 'billing-zip', $billing['zip'], true );
					add_user_meta( $new_user_id, 'billing-state', $billing['state'], true );

				}

				// Inform admin about the payment
				if ( $type == 1 ) {
					$subject = 'WAK New Premium Auto Shop Signup';
					$message = 'A new auto shop has signed up on Women Auto Know.' . "\n";
				}
				else {
					$subject = 'WAK New Pledged Auto Shop Signup';
					$message = 'A new auto shop has taken the pledge and signed up as a pledged auto shop.' . "\n";
				}

				$message .= '<strong>Auto Shop Name:</strong> ' . $shopname . "\n";
				$message .= '<strong>WAK URL:</strong> ' . get_permalink( $new_autoshop_id ) . "\n";
				$message .= '<strong>Email:</strong> ' . $data['email'] . "\n";
				$message .= '<strong>Name:</strong> ' . $data['first_name'] . ' ' . $data['last_name'] . "\n";
				$message .= '<strong>Admin edit link:</strong> ' . add_query_arg( array( 'post' => $new_autoshop_id, 'action' => 'edit' ), admin_url( 'post.php' ) ) . "\n";

				$headers = array();
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				$headers[] = 'From: WAK <donotreply@womenautoknow.com>';

				wp_mail( get_option( 'admin_email' ), $subject, $message, $headers );

			}

		}

		return true;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_registration_by_code' ) ) :
	function wak_get_registration_by_code( $code = '' ) {

		global $wpdb, $wak_pending_registrations_db;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_pending_registrations_db} WHERE activation_code = %s;", $code ) );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_new_activation_key' ) ) :
	function wak_new_activation_key() {

		global $wpdb, $wak_pending_registrations_db;

		do {

			$id    = strtolower( wp_generate_password( 12, false, false ) );
			$query = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_pending_registrations_db} WHERE activation_code = %s;", $id ) );

		} while ( ! empty( $query ) );
	
		return $id;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'allowed_username' ) ) :
	function allowed_username( $username = '' ) {

		$not_allowed = array( 'admin', 'administrator', 'wak', 'womenautoknow', 'audra', 'staff', 'employee', 'hr', 'system', 'default', 'editor', 'contributor', 'subscriber' );

		$check = in_array( strtolower( $username ), $not_allowed );

		if ( $check )
			return false;

		return true;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'pending_username_exists' ) ) :
	function pending_username_exists( $username = '' ) {

		global $wpdb, $wak_pending_registrations_db;

		$check = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wak_pending_registrations_db} WHERE username = %s;", $username ) );

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
if ( ! function_exists( 'pending_email_exists' ) ) :
	function pending_email_exists( $email = '' ) {

		global $wpdb, $wak_pending_registrations_db;

		$check = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wak_pending_registrations_db} WHERE email = %s;", $email ) );

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
if ( ! function_exists( 'autoshop_has_pending_registration' ) ) :
	function autoshop_has_pending_registration( $autoshop_id = '' ) {

		global $wpdb, $wak_pending_registrations_db;

		$check = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wak_pending_registrations_db} WHERE autoshop_id = %d;", $autoshop_id ) );

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
if ( ! function_exists( 'is_ok_password' ) ) :
	function is_ok_password( $password = '', $username = '' ) {

		if ( strlen( $password ) < 4 ) return false;

		if ( $password == $username ) return false;

		if ( in_array( (string) $password, array( '12345', 'password', '01234', '98765', '11111', '22222', '333333', '44444', '55555', '66666', '77777', '88888', '99999', '00000' ) ) ) return false;

		return true;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'allowed_username' ) ) :
	function allowed_username( $username = '' ) {

		$not_allowed = array( 'admin', 'administrator', 'wak', 'womenautoknow', 'audra', 'staff', 'employee', 'hr', 'system', 'default', 'editor', 'contributor', 'subscriber' );

		return in_array( strtolower( $username ), $not_allowed );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'registration_has_error' ) ) :
	function registration_has_error( $key = '' ) {

		global $wak_registration;

		if ( ! isset( $wak_registration->errors ) || ! array_key_exists( $key, $wak_registration->errors ) )
			return;

		echo ' has-error';

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_section_has_error' ) ) :
	function wak_section_has_error( $keys = array() ) {

		if ( empty( $keys ) ) return false;

		global $wak_registration;

		$in = false;
		foreach ( $keys as $key ) {

			if ( isset( $wak_registration->errors[ $key ] ) )
				$in = true;

		}

		return $in;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_send_email_activation' ) ) :
	function wak_send_email_activation( $data = array() ) {

		$prefs = wak_registration_plugin_settings();

		// Enabled
		if ( $prefs['verify_email'] == 1 ) {

			$message = str_replace( '%ACTIVATIONLINK%', wak_get_activation_link( $data['activation_code'] ), $prefs['emails']['activate'] );
			$to      = $data['email'];
			$subject = 'WAK Account Activation';

			$headers = array();
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$headers[] = 'From: WAK <donotreply@womenautoknow.com>';

			if ( wp_mail( $to, $subject, $message, $headers ) )
				return true;

			return false;

		}

		// Disabled
		else {

			$entry = wak_get_registration_by_code( $data['activation_code'] );

			if ( isset( $entry->id ) ) {

				global $wpdb, $wak_pending_registrations_db;

				$new_user_id = wp_insert_user( array(
					'user_login'      => $entry->username,
					'user_pass'       => $data['password'],
					'user_email'      => $entry->email,
					'first_name'      => $entry->first_name,
					'last_name'       => $entry->last_name,
					'display_name'    => $entry->first_name,
					'role'            => 'subscriber',
					'user_registered' => date( 'Y-m-d H:i:s', $entry->time )
				) );

				if ( ! is_wp_error( $new_user_id ) ) {

					add_user_meta( $new_user_id, 'newsletter', $entry->newsletter, true );

					if ( $entry->autoshop_id > 0 ) {
						add_post_meta( $entry->autoshop_id, 'owner_id', $new_user_id, true );
						add_post_meta( $entry->autoshop_id, 'added_by', $new_user_id, true );
					}

					$wpdb->delete(
						$wak_pending_registrations_db,
						array( 'id' => $entry->id ),
						array( '%d' )
					);

					return true;

				}

			}

		}

		return false;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_activation_link' ) ) :
	function wak_get_activation_link( $code = '' ) {

		$url = add_query_arg( array( 'do' => 'verify-email', 'token' => $code ), home_url( '/' ) );

		return '<a href="' . $url . '" title="Activate your WAK Account" target="_blank">Click here to activate your account</a>.';

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_process_registrations_admin_actions' ) ) :
	function wak_process_registrations_admin_actions() {

		if ( ! current_user_can( 'moderate_comments' ) ) return;

		if ( isset( $_GET['action'] ) && isset( $_GET['reg_id'] ) && strlen( $_GET['reg_id'] ) > 0 && in_array( $_GET['action'], array( 'approve', 'resend', 'delete' ) ) ) {

			$act      = sanitize_key( $_GET['action'] );
			$entry_id = absint( $_GET['reg_id'] );

			global $wpdb, $wak_pending_registrations_db;

			$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_pending_registrations_db} WHERE id = %d;", $entry_id ) );

			if ( isset( $entry->id ) ) {

				if ( $act == 'approve' ) {

					$new_user_id = wp_insert_user( array(
						'user_login'      => $entry->username,
						'user_pass'       => 'TempPasswordWillBeReplaced',
						'user_email'      => $entry->email,
						'first_name'      => $entry->first_name,
						'last_name'       => $entry->last_name,
						'display_name'    => $entry->first_name,
						'role'            => 'subscriber',
						'user_registered' => date( 'Y-m-d H:i:s', $entry->time )
					) );

					if ( ! is_wp_error( $new_user_id ) ) {

						add_user_meta( $new_user_id, 'newsletter', $entry->newsletter, true );

						// Update the password with the already hashed one
						$wpdb->update(
							$wpdb->users,
							array( 'user_pass' => $entry->password ),
							array( 'ID' => $new_user_id ),
							array( '%s' ),
							array( '%d' )
						);

						$wpdb->delete(
							$wak_pending_registrations_db,
							array( 'id' => $entry_id ),
							array( '%d' )
						);

						$url = remove_query_arg( array( 'action', 'reg_id' ) );
						$url = add_query_arg( array( 'updated' => 1 ), $url );
						wp_safe_redirect( $url );
						exit;

					}
					else {

						$error = $new_user_id->get_error_message();

						$url = remove_query_arg( array( 'action', 'reg_id' ) );
						$url = add_query_arg( array( 'error' => 1, 'message' => urlencode( $error ) ), $url );
						wp_safe_redirect( $url );
						exit;

					}

				}

				elseif ( $act == 'resend' ) {

					$url = remove_query_arg( array( 'action', 'reg_id' ) );

					if ( wak_send_email_activation( array( 'email' => $entry->email, 'activation_code' => $entry->activation_code ) ) ) {

						$url = add_query_arg( array( 'resent' => 1 ), $url );

						$wpdb->update(
							$wak_pending_registrations_db,
							array( 'emails_sent' => $entry->emails_sent + 1 ),
							array( 'id' => $entry_id ),
							array( '%d' ),
							array( '%d' )
						);

					}
					else
						$url = add_query_arg( array( 'error' => 1, 'message' => urlencode( 'Could not send email.' ) ), $url );

					wp_safe_redirect( $url );
					exit;

				}
				elseif ( $act == 'delete' ) {

					$wpdb->delete(
						$wak_pending_registrations_db,
						array( 'id' => $entry_id ),
						array( '%d' )
					);

					$url = remove_query_arg( array( 'action', 'reg_id' ) );
					$url = add_query_arg( array( 'deleted' => 1 ), $url );
					wp_safe_redirect( $url );
					exit;

				}

			}

		}

		if ( isset( $_GET['action'] ) && $_GET['action'] != '-1' && isset( $_GET['registrations'] ) && ! empty( $_GET['registrations'] ) ) {

			global $wpdb, $wak_pending_registrations_db;

			$act           = sanitize_key( $_GET['action'] );
			$registrations = array();
			$done          = 0;

			foreach ( $_GET['registrations'] as $reg_id ) {
				if ( $reg_id == '' || $reg_id == 0 ) continue;
				$registrations[] = absint( $reg_id );
			}

			if ( ! empty( $registrations ) ) {

				if ( $act == 'delete' ) {

					foreach ( $registrations as $entry_id ) {

						$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_pending_registrations_db} WHERE id = %d;", $entry_id ) );

						if ( $entry === NULL ) continue;

						$wpdb->delete(
							$wak_pending_registrations_db,
							array( 'id' => $entry_id ),
							array( '%d' )
						);

						$done++;

					}

					$url = remove_query_arg( array( 'action', 'registrations' ) );
					$url = add_query_arg( array( 'deleted' => 1, 'multi' => $done ), $url );
					wp_safe_redirect( $url );
					exit;

				}

				elseif ( $act == 'approve' ) {

					foreach ( $registrations as $entry_id ) {

						$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_pending_registrations_db} WHERE id = %d;", $entry_id ) );

						if ( $entry === NULL ) continue;

						$new_user_id = wp_insert_user( array(
							'user_login'      => $entry->username,
							'user_pass'       => 'TempPasswordWillBeReplaced',
							'user_email'      => $entry->email,
							'first_name'      => $entry->first_name,
							'last_name'       => $entry->last_name,
							'display_name'    => $entry->first_name,
							'role'            => 'subscriber',
							'user_registered' => date( 'Y-m-d H:i:s', $entry->time )
						) );

						if ( ! is_wp_error( $new_user_id ) ) {

							if ( $entry->autoshop_id > 0 ) {
								add_post_meta( $entry->autoshop_id, 'owner_id', $new_user_id, true );
								add_post_meta( $entry->autoshop_id, 'added_by', $new_user_id, true );
							}

							add_user_meta( $new_user_id, 'newsletter', $entry->newsletter, true );

							// Update the password with the already hashed one
							$wpdb->update(
								$wpdb->users,
								array( 'user_pass' => $entry->password ),
								array( 'ID' => $new_user_id ),
								array( '%s' ),
								array( '%d' )
							);

							$wpdb->delete(
								$wak_pending_registrations_db,
								array( 'id' => $entry_id ),
								array( '%d' )
							);

							$done++;

						}

					}

					$url = remove_query_arg( array( 'action', 'registrations' ) );
					$url = add_query_arg( array( 'updated' => 1, 'multi' => $done ), $url );
					wp_safe_redirect( $url );
					exit;

				}

			}

		}

		if ( isset( $_REQUEST['wp_screen_options']['option'] ) && isset( $_REQUEST['wp_screen_options']['value'] ) ) {
			
			if ( $_REQUEST['wp_screen_options']['option'] == 'wak_pending_reg_per_page' ) {
				$value = absint( $_REQUEST['wp_screen_options']['value'] );
				update_user_meta( get_current_user_id(), 'wak_pending_reg_per_page', $value );
			}

		}

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_recover_password' ) ) :
	function wak_recover_password() {

		if ( is_user_logged_in() || ! isset( $_POST['wak-username-email'] ) || $_POST['wak-username-email'] == '' || ! isset( $_POST['g-recaptcha-response'] ) || $_POST['g-recaptcha-response'] == '' ) return;

		global $wak_password_recovery;

		$account = sanitize_text_field( $_POST['wak-username-email'] );

		$user = get_user_by( 'login', $account );
		if ( ! isset( $user->ID ) )
			$user = get_user_by( 'email', $account );

		if ( ! isset( $user->ID ) ) {

			$wak_password_recovery = 'error';
			return;

		}

		if ( ! wak_verify_captcha( $_POST['g-recaptcha-response'], $_POST['g-recaptcha-key'] ) ) {

			$wak_password_recovery = 'captcha';
			return;

		}

		$new_password = wp_generate_password( 8, true, true );

		wp_update_user( array(
			'ID'        => $user->ID,
			'user_pass' => $new_password
		) );

		$message = str_replace( '%NEWPASSWORD%', $new_password, $prefs['resetpass'] );

		$to      = $user->user_email;
		$subject = 'WAK Password Reset';

		$headers = array();
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: WAK <donotreply@womenautoknow.com>';

		if ( wp_mail( $to, $subject, $message, $headers ) )
			$wak_password_recovery = true;

		else
			$wak_password_recovery = 'error';

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_get_captchas' ) ) :
	function wak_registration_get_captchas() {

		return array(
			'9ht93r739rh' => array(
				'question' => '5 + 2',
				'answer'   => 7
			),
			'jfw39hr831' => array(
				'question' => '8 - 4',
				'answer'   => 4
			),
			'0r30ur3hjr' => array(
				'question' => '1 + 2 + 1',
				'answer'   => 4
			),
			'ah78rr8or3' => array(
				'question' => '2 + 3',
				'answer'   => 5
			),
			'r893ry9rhr' => array(
				'question' => '3 - 2',
				'answer'   => 1
			),
			'2e1e9ep2e' => array(
				'question' => '10 - 1',
				'answer'   => 9
			),
			'123214re3' => array(
				'question' => '8 + 3',
				'answer'   => 11
			),
			'073fhf373' => array(
				'question' => '5 + 5',
				'answer'   => 10
			)
		);

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_display_captcha' ) ) :
	function wak_registration_display_captcha( $id = '' ) {

		$prefs = wak_registration_plugin_settings();

		// Use google captcha
		if ( $prefs['captcha_sitekey'] != '' && $id != '' ) {

?>
<div id="<?php echo $id; ?>"></div>
<?php

			return;

		}

		// Else use our own
		$captchas = wak_registration_get_captchas();
		$number = mt_rand( 0, 8 );

		$count = 0;
		foreach ( $captchas as $key => $value ) {

			if ( $count == $number ) {

?>
<strong>Verify your enrollment by answering the following math question:</strong>
<div style="display:none;">
	<label>Please verify form:</label>
	<input type="text" name="recaptcha" value="" />
</div>
<div class="captcha" style="padding: 24px 0;">
	<span><?php echo $value['question']; ?> = </span><input type="text" size="8" class="form-control" style="display: inline; width: 100px;" name="g-recaptcha-response[]" />
</div>
<input type="hidden" name="g-recaptcha-key[]" value="<?php echo $key; ?>" />
<?php

			}

			$count ++;

		}

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_verify_captcha' ) ) :
	function wak_verify_captcha( $sent = '', $key = '' ) {

		if ( is_array( $sent ) ) {

			$s = '';
			foreach ( $sent as $field ) {
				if ( $field == '' ) continue;
				$s = sanitize_text_field( $field );
			}

			$sent = $s;

		}

		if ( is_array( $key ) ) {

			$k = '';
			foreach ( $key as $field ) {
				if ( $field == '' ) continue;
				$k = sanitize_text_field( $field );
			}

			$key = $k;

		}

		$prefs = wak_registration_plugin_settings();

		// Use our own
		if ( $prefs['captcha_sitekey'] == '' ) {

			$captchas = wak_registration_get_captchas();
			if ( $key != '' ) {

				if ( ! array_key_exists( $key, $captchas ) )
					return false;

				if ( $captchas[ $key ]['answer'] == $sent )
					return true;

				return false;

			}

			return false;

		}

		// Use googles captcha
		$args = array(
			'secret'   => $prefs['captcha_secret'],
			'response' => $sent
		);

		$url     = add_query_arg( $args, 'https://www.google.com/recaptcha/api/siteverify' );
		$request = wp_remote_post( $url );

		$result = false;
		if ( ! is_wp_error( $request ) ) {

			$body = maybe_unserialize( $request['body'] );
			$info = json_decode( $body );

			if ( $info->success )
				$result = true;

		}

		return $result;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_register_login_form_reset' ) ) :
	function wak_register_login_form_reset() {

		$prefs = wak_registration_plugin_settings();

		echo '<a href="' . get_permalink( $prefs['recover_page_id'] ) . '">Forgot Password?</a>';

	}
endif;

?>