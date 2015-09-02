<?php
// No dirrect access
if ( ! defined( 'WAK_INVITES_VER' ) ) exit;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_plugin_settings' ) ) :
	function wak_invites_plugin_settings() {

		$default = array(
			'emails_driver' => '',
			'emails_shop'   => ''
		);

		$saved = get_option( 'wak_invites_plugin_prefs', $default );
		
		return wp_parse_args( $saved, $default );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_sanitize_invites_plugin_settings' ) ) :
	function wak_sanitize_invites_plugin_settings( $new ) {

		$saved = wak_invites_plugin_settings();

		return wp_parse_args( $new, $saved );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_invite_by_email' ) ) :
	function wak_get_invite_by_email( $email = '' ) {

		global $wpdb, $wak_pending_invites;

		$email = sanitize_email( $email );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_pending_invites} WHERE email = %s;", $email ) );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'is_email_pending_invite' ) ) :
	function is_email_pending_invite( $email = '' ) {

		global $wpdb, $wak_pending_invites;

		$check = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wak_pending_invites} WHERE email = %s;", $email ) );
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
if ( ! function_exists( 'do_not_invite_email' ) ) :
	function do_not_invite_email( $email = '' ) {

		global $wpdb, $wak_blocked_invites;

		$check = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wak_blocked_invites} WHERE email = %s;", $email ) );
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
if ( ! function_exists( 'block_email_from_invites' ) ) :
	function block_email_from_invites( $email = '' ) {

		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) return false;

		if ( do_not_invite_email( $email ) ) return false;

		$invite = wak_get_invite_by_email( $email );
		if ( ! isset( $invite->id ) ) return false;

		global $wpdb, $wak_blocked_invites, $wak_pending_invites;

		$wpdb->insert(
			$wak_blocked_invites,
			array( 'email' => $email, 'invited_by' => $invite->id ),
			array( '%s', '%d' )
		);

		$wpdb->delete(
			$wak_pending_invites,
			array( 'id' => $invite->id ),
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
if ( ! function_exists( 'wak_user_can_invite' ) ) :
	function wak_user_can_invite( $user_id = '' ) {

		$check = get_user_meta( $user_id, 'wak_invite_block', true );
		if ( $check == 1 )
			return false;

		return true;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_is_pledged_autoshop_owner' ) ) :
	function wak_invites_is_pledged_autoshop_owner( $user_id = '' ) {

		$autoshops = new WP_Query( array(
			'post_type'      => 'autoshops',
			'posts_per_page' => 6,
			'post_status'    => 'publish',
			'fields'         => 'ID',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => 'owner_id',
					'value' => $user_id
				),
				array(
					'key'   => 'pledged',
					'value' => 1
				)
			)
		) );

		$count = 0;
		if ( $autoshops->have_posts() )
			$count = count( $autoshops->posts );

		wp_reset_postdata();

		if ( $count == 0 )
			return false;

		return true;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_send_new_invite' ) ) :
	function wak_send_new_invite( $type = 'driver', $email = '', $name = '', $inviters_id = '' ) {

		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) return false;

		$prefs = wak_invites_plugin_settings();

		$message = $prefs['emails_driver'];
		if ( $type == 'shop' )
			$message = $prefs['emails_shop'];

		$user = get_userdata( $inviters_id );

		$message = str_replace( '%INVITERSNAME%', $user->first_name . ' ' . $user->last_name, $message );
		$message = str_replace( '%NAME%', $name, $message );
		$message = str_replace( '%WEBSIETURL%', home_url( '/' ), $message );
		$message = wpautop( $message );

		$block_url = add_query_arg( array( 'unsubscribe' => urlencode( $email ), 'do' => 'no-more-invites' ), home_url( '/' ) );

		$message .= '<p><small style="color:gray;">Invites are sent by WAK members and not by Women Auto Know directly. To prevent this email from receiving any further invites click <a href="' . $block_url . '">here</a>.</small></p>';

		$subject = 'Invitation to join Women Auto Know';

		$headers = array();
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: WAK <donotreply@womenautoknow.com>';

		return wp_mail( $email, $subject, $message, $headers );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_lead_page_access' ) ) :
	function wak_invites_lead_page_access() {

		if ( is_page( WAK_INVITES_LEAD_PAGE_ID ) ) {

			if ( ! is_user_logged_in() ) {

				wp_redirect( home_url() );
				exit;

			}

			$user   = wp_get_current_user();
			$invite = get_user_meta( $user->ID, 'last-invited-autoshop', true );
			$invite = maybe_unserialize( $invite );
			if ( ! is_array( $invite ) ) {

				//wp_die( '<pre>' . print_r( $invite, true ) . '</pre>' );

				wp_redirect( wak_theme_get_profile_url( $user ) );
				exit;

			}

		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_cf7_recipient' ) ) :
	function wak_invites_cf7_recipient( $WPCF7_ContactForm ) {

		if ( ! is_user_logged_in() || $WPCF7_ContactForm->id() != WAK_INVITES_CF7_ID ) return;

		$user_id = get_current_user_id();

		$invited = get_user_meta( $user_id, 'last-invited-autoshop', true );
		if ( ! is_array( $invited ) ) {
			delete_user_meta( $user_id, 'last-invited-autoshop' );
			return;
		}

		// Get current form
		$wpcf7      = WPCF7_ContactForm::get_current();

		// get current SUBMISSION instance
		$submission = WPCF7_Submission::get_instance();

		// Ok go forward
		if ( $submission ) {

			// do some replacements in the cf7 email body
			$mail         = $wpcf7->prop( 'mail' );

			// Change recipient to user that was just invited
			$mail['recipient'] = $invited['name'] . ' <' . $invited['email'] . '>';

			// Save the email body
			$wpcf7->set_properties( array(
				"mail" => $mail
			));

			delete_user_meta( $user_id, 'last-invited-autoshop' );

			return $wpcf7;

		}

	}
endif;

?>