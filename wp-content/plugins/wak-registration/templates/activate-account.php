<?php

	get_header();

	$activation_code = '';
	if ( isset( $_GET['token'] ) )
		$activation_code = sanitize_text_field( $_GET['token'] );

	$entry = wak_get_registration_by_code( $activation_code );

?>

<style type="text/css">
#wak-activate-account { padding: 48px 0; }
</style>
<div class="outer-wrapper" id="main-content">

	<div class="inner-wrapper boxed">

		<div class="row" id="wak-activate-account">

			<div class="col-md-12 col-xs-12" id="the-content">

				<h1 class="text-center">Activate Account</h1>

<?php

	if ( isset( $entry->id ) ) {

		global $wpdb, $wak_pending_registrations_db;

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

			if ( $entry->autoshop_id > 0 ) {
				add_post_meta( $entry->autoshop_id, 'owner_id', $new_user_id, true );
				add_post_meta( $entry->autoshop_id, 'added_by', $new_user_id, true );
			}

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
				array( 'id' => $entry->id ),
				array( '%d' )
			);

?>
				<h4 class="text-success text-center">Your account has been successfully activated.</h4>
				<p class="text-center">You can now login to your WAK account using the email address and password you provided.</p>
<script type="text/javascript">
jQuery(function($) {

	$( '#toggle-my-account' ).click();

});
</script>
<?php

		}

	}
	else {

?>
				<h4 class="text-center text-danger">Invalid Activation Code</h4>
				<p class="text-center">The code you provided is not valid. Please check the code and try again.</p>
<?php

	}

?>

			</div>

		</div>

	</div>

</div>

<?php get_footer(); ?>