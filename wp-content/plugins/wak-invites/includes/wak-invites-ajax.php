<?php
// No dirrect access
if ( ! defined( 'WAK_INVITES_VER' ) ) exit;

/**
 * AJAX: Send Invite
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_send_invite' ) ) :
	function wak_ajax_send_invite() {

		// Security
		check_ajax_referer( 'wak-send-invite', 'token' );

		global $wpdb, $wak_pending_invites;

		$type    = sanitize_key( $_POST['type'] );
		$user_id = get_current_user_id();
		$name    = sanitize_text_field( $_POST['nameinvite'] );

		if ( ! wak_user_can_invite( $user_id ) )
			wp_send_json_error( 'You can not send invites.' );

		$email = sanitize_text_field( $_POST['emailinvite'] );

		if ( $email == '' || ! is_email( $email ) )
			wp_send_json_error( 'Invalid email. Please try again.' );

		$check = get_user_by( 'email', $email );
		if ( isset( $check->ID ) )
			wp_send_json_error( 'This email belongs to a user that is already a member.' );

		if ( do_not_invite_email( $email ) )
			wp_send_json_error( 'The owner of this email has requested not to receive any further invites.' );

		if ( is_email_pending_invite( $email ) )
			wp_send_json_error( 'This email has already been invited.' );

		$result = wak_send_new_invite( $type, $email, $name, $user_id );

		if ( ! $result )
			wp_send_json_error( 'Could not send an invite at this time. Please try again later.' );

		else {

			$wpdb->insert(
				$wak_pending_invites,
				array( 'type' => 'driver', 'email' => $email, 'invited_by' => $user_id ),
				array( '%s', '%s', '%d' )
			);

			add_user_meta( $user_id, 'last-invited-autoshop', array(
				'name'  => $name,
				'email' => $email
			), true );

			$message = 'Thank you for inviting %recipientname% to Women Auto Know. Your invite has been sent to %email%.';
			$message = str_replace( '%recipientname%', $name, $message );
			$message = str_replace( '%email%', $email, $message );

			wp_send_json_success( $message );

		}

	}
endif;

?>