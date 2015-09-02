<?php

	global $wak_password_recovery;

	get_header();

	$prefs = wak_registration_plugin_settings();

	$message = '<div class="alert alert-info">Please enter your WAK username or email address to receive a new password.</div>';

	if ( $wak_password_recovery == 'captcha' )
		$message = '<div class="alert alert-warning">Could not verify that you are a human. Please try again.</div>';

	elseif ( $wak_password_recovery == 'error' )
		$message = '<div class="alert alert-warning">Username or Email not found.</div>';

	elseif ( $wak_password_recovery === true )
		$message = '<div class="alert alert-success">A new password has been sent. Please check your email.</div>';

?>
<div class="outer-wrapper">

	<div class="inner-wrapper boxed">

		<div class="row" id="password-recovery">

			<div class="col-md-12 col-xs-12">

				<div class="row">
					<form method="post" action="">
						<div class="hidden-xs col-md-3 col-sm-3"></div>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<h1 class="text-center">Reset Password</h1>
							<?php echo $message; ?>
							<div class="form-group">
								<input type="text" placeholder="Username or Email" value="" name="wak-username-email" class="form-control" /><br />
							</div>
							<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $prefs['captcha_sitekey'] ); ?>"></div><br />
							<div class="form-group">
								<input type="submit" id="wak-recover-my-password" value="Send New Password" class="btn btn-danger btn-block" />
							</div>
						</div>
						<div class="hidden-xs col-md-3 col-sm-3"></div>
					</form>
				</div>

			</div>

		</div>

	</div>

</div>

<?php get_footer(); ?>