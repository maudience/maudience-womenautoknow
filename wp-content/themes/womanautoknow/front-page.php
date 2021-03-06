<?php get_header(); ?>

<?php if ( is_user_logged_in() && ! current_user_can( 'edit_users' ) ) : ?>

	<?php global $post; $layout = wak_theme_get_layout( $post->ID ); ?>

<div class="outer-wrapper front-page-template" id="main-content">

	<?php //wak_theme_slider(); ?>

	<div class="inner-wrapper boxed">

		<?php get_template_part( 'content', 'sidebar-' . $layout ); ?>

	</div>

</div>

<?php

	else :

		global $wak_registration;

		$prefs = wak_registration_plugin_settings();

		$plans = array();
		if ( function_exists( 'wak_payment_plans' ) )
			$plans = wak_get_payment_plans();

		$terms_url = '#';
		$paid_thanks = 'Thank you for your payment. Your account has now been setup and is accessible via the login details your provided.';
		if ( function_exists( 'wak_payments_plugin_settings' ) ) {

			$payprefs = wak_payments_plugin_settings();
			$terms_url = get_permalink( $payprefs['terms_page_id'] );
			$paid_thanks = esc_attr( $payprefs['templates']['paid-signup'] );

		}

?>

<!--[if gte IE 9]>
  <style type="text/css">
    .front-page-template {
       filter: none;
    }
  </style>
<![endif]-->
<style type="text/css">
.front-page-template {
	/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#f1f1f1+0,dedede+100 */
	background: #f1f1f1; /* Old browsers */
	/* IE9 SVG, needs conditional override of 'filter' to 'none' */
	background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2YxZjFmMSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNkZWRlZGUiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
	background: -moz-linear-gradient(top,  #f1f1f1 0%, #dedede 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f1f1f1), color-stop(100%,#dedede)); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  #f1f1f1 0%,#dedede 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  #f1f1f1 0%,#dedede 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  #f1f1f1 0%,#dedede 100%); /* IE10+ */
	background: linear-gradient(to bottom,  #f1f1f1 0%,#dedede 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f1f1f1', endColorstr='#dedede',GradientType=0 ); /* IE6-8 */
}
#website-header nav ul li.right {
    float: right;
    margin-right: 0;
}
#website-header nav ul li.right form .form-group {
	margin-left: 12px;
}
#website-header nav ul li.right form input.form-control {
	border-color: #EC008B;
	height: 32px;
}
.front-page-template .boxed > .row {
	padding: 24px 0 48px 0;
}
.front-page-template .boxed > .row h1.blue {
	margin-bottom: 24px;
}
#why-should-i-join {
	margin: 0 0 0 0;
	padding: 0 0 0 0;
	list-style-type: none;
}
#why-should-i-join li {
	min-height: 75px;
}
#why-should-i-join li p {
	font-size: 14px;
	line-height: 16px;
}
#why-should-i-join li .point-count {
	display: block;
	width: 48px;
	height: 48px;
	float: left;
	line-height: 48px;
	font-size: 24px;
	text-align: center;
	margin-right: 24px;
	margin-bottom: 12px;
}
#why-should-i-join li .point-count span {
	display: block;
	background-color: #EC008B;
	border: 1px solid #EC008B;
	border-radius: 50%;
	color: white;
}
#signup-form {
	padding: 24px 0;
}
#signup-form .form-control {
	border-color: #EC008B;
	margin-bottom: 24px;
}
#signup-form .row {
	margin-left: -15px;
	margin-right: -15px;
}
#mobile-login-box {
	padding-bottom: 12px;
	border-bottom: 1px solid #333;
	margin-bottom: 0;
}
</style>
<div class="outer-wrapper front-page-template" id="main-content">

	<div class="inner-wrapper boxed">

		<div class="row visible-xs visible-xs-12 " id="mobile-login-box">
		<div class="col-xs-12">
			<h1 class="pink">Access Account</h1>
			<form class="form form-inline wak-loginform" id="wak-loginform-mobile" method="post" action="">
				<div class="form-group"><input type="text" class="form-control" name="email" id="wak-username-mobile" value="" placeholder="Email" /><small class="hidden-xs">&nbsp;</small></div>
				<div class="form-group"><input type="password" class="form-control" name="pwd" id="wak-pass-mobile" value="" /><small><a href="<?php echo get_permalink( $prefs['recover_page_id'] ); ?>">Forgot password</a></small></div>
				<div class="form-group"><input type="submit" class="btn btn-danger" id="wak-login-button-mobile" value="LOG IN" /><small class="hidden-xs">&nbsp;</small></div>
			</form>
		</div>
		</div>

		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 hidden-xs">
				<img src="<?php echo get_template_directory_uri() . '/images/wak-women-trans.png'; ?>" alt="" />
				<h1 class="blue">Women Auto Know</h1>
				<ul id="why-should-i-join">
					<li>
						<div class="point-count"><span>1</span></div>
						<h3>                                Sign Up For Free</h3>
						<p>                                                  Sign up and create a free account for your vehicle.</p>
					</li>
					<li>
						<div class="point-count"><span>2</span></div>
						<h3>Reviews</h3>
						<p>Read, write and share your auto shop experience today!</p>
					</li>
					<li>
						<div class="point-count"><span>3</span></div>
						<h3>Maintenance</h3>
						<p>Track your vehicles maintenance and repairs.</p>
					</li>
					<li>
						<div class="point-count"><span>4</span></div>
						<h3>Recalls</h3>
						<p>Read important recall information about your vehicle.</p>
					</li>
					<li>
						<div class="point-count"><span>5</span></div>
						<h3>Empower Women</h3>
						<p>Women empowering other women to have a choice by sharing auto shop reviews...</p>
					</li>
				</ul>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
				<h1 class="pink">Join Now</h1>
				<h4 class="blue">Are you an auto shop? <a href="<?php echo get_permalink( $prefs['signup_page_id'] ); ?>" class="pink">Sign up here</a></h4>
				<p>Registering for a WAK account is easy. Simply fill out the form below and verify your email address by clicking on the link we send to your email account.</p>
<?php

		if ( ! $wak_registration->result ) :

			if ( $wak_registration->signup == 'driver' && ! empty( $wak_registration->errors ) ) {

				$message = 'The following errors were found:<ul>';
				foreach ( $wak_registration->errors as $error_code => $error ) {
					$message .= '<li>' . $error . '</li>';
				}
				$message .= '</ul>';

				echo '<div class="alert alert-warning">' . $message . '</div>';

			}

?>
				<form method="post" action="" id="signup-form" autocomplete="off">
					<div class="row">
						<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
							<div class="form-group">
								<input type="text" name="wak_new_driver[first_name]" class="form-control" placeholder="First Name" id="wak-new-driver-first-name" value="<?php if ( isset( $_POST['wak_new_driver']['first_name'] ) ) echo esc_attr( $_POST['wak_new_driver']['first_name'] ); ?>" autocomplete="off" />
							</div>
						</div>
						<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
							<div class="form-group">
								<input type="text" name="wak_new_driver[last_name]" class="form-control" placeholder="Last Name" id="wak-new-driver-last-name" value="<?php if ( isset( $_POST['wak_new_driver']['last_name'] ) ) echo esc_attr( $_POST['wak_new_driver']['last_name'] ); ?>" autocomplete="off" />
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<input type="text" name="wak_new_driver[email]" class="form-control" placeholder="Email" id="wak-new-driver-email" value="<?php if ( isset( $_POST['wak_new_driver']['email'] ) ) echo esc_attr( $_POST['wak_new_driver']['email'] ); ?>" autocomplete="off" />
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<input type="password" name="wak_new_driver[pwd1]" class="form-control" placeholder="Password" id="wak-new-driver-pwd1" value="" autocomplete="off" />
								<div class="form-control-static text-dandger" id="pwd-messages" style="display:none;"></div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group">
								<input type="password" name="wak_new_driver[pwd2]" class="form-control" placeholder="Confirm Password" id="wak-new-driver-pwd2" value="" autocomplete="off" />
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="checkbox">
								<label for="wak-new-driver-newletter"><input type="checkbox" name="wak_new_driver[newletter]" id="wak-new-driver-newletter"<?php if ( isset( $_POST['wak_new_driver']['newletter'] ) ) echo ' checked="checked"'; ?> value="1" /> I would like to subscribe to WAK news. <span>Unsubscribe at any time</span></label>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="checkbox">
								<label for="wak-new-driver-terms"><input type="checkbox" name="wak_new_driver[terms]" id="wak-new-driver-terms" value="1" /> I have read and accept WAK's <a href="<?php echo esc_url( $terms_url ); ?>" class="pink">Terms and conditions</a>.</label>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="captcha-section">

								<?php wak_registration_display_captcha( 'recaptcha1' ); ?>

							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12 text-right">
							<input type="submit" class="btn btn-lg btn-danger" value="Join WAK" />
						</div>
					</div>
				</form>
				<h4 class="blue">Are you an auto shop? <a href="<?php echo get_permalink( $prefs['signup_page_id'] ); ?>" class="pink">Sign up here</a></h4>
			</div>
		</div>

	</div>

</div>
<script type="text/javascript">
( function( $ ) {

	$('#wak-new-driver-pwd1').keyup(function(e) {
		$('#pwd-messages').show();
		var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
		var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
		var enoughRegex = new RegExp("(?=.{4,}).*", "g");
		if (false == enoughRegex.test($(this).val())) {
			$('#pwd-messages').html('Too short');
		} else if (strongRegex.test($(this).val())) {
			$('#pwd-messages').removeClass().addClass( 'form-control-static text-success' );
			$('#pwd-messages').html('Strong!');
		} else if (mediumRegex.test($(this).val())) {
			$('#pwd-messages').removeClass().addClass( 'form-control-static text-danger' );
			$('#pwd-messages').html('Medium!');
		} else {
			$('#pwd-messages').removeClass().addClass( 'form-control-static text-warning' );
			$('#pwd-messages').html('Weak!');
		}
		return true;
	});

	$('#wak-new-shop-pwd1').keyup(function(e) {
		var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
		var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
		var enoughRegex = new RegExp("(?=.{4,}).*", "g");
		if (false == enoughRegex.test($(this).val())) {
			$('#pwd-messages-shop').html('Too short');
		} else if (strongRegex.test($(this).val())) {
			$('#pwd-messages-shop').removeClass().addClass( 'form-control-static text-success' );
			$('#pwd-messages-shop').html('Strong!');
		} else if (mediumRegex.test($(this).val())) {
			$('#pwd-messages-shop').removeClass().addClass( 'form-control-static text-danger' );
			$('#pwd-messages-shop').html('Medium!');
		} else {
			$('#pwd-messages-shop').removeClass().addClass( 'form-control-static text-warning' );
			$('#pwd-messages-shop').html('Weak!');
		}
		return true;
	});

	$( 'input.toggle-payment-box' ).click(function(){

		var type = $(this).data( 'type' );

		$( '.wak-type-box' ).hide();
		$( '#wak-shop-' + type ).show();
		$( '#wak-autoshop-accept-terms' ).show();

	});

} )( jQuery );
</script>

<?php if ( $prefs['captcha_sitekey'] != '' ) { ?>
<script type="text/javascript">
	var recaptcha1;
	var recaptcha2;

	var waksignup = function() {

		recaptcha1 = grecaptcha.render( 'recaptcha1', {
			'sitekey' : '<?php echo esc_attr( $prefs['captcha_sitekey'] ); ?>',
			'theme' : 'light'
		});

		recaptcha2 = grecaptcha.render( 'recaptcha2', {
			'sitekey' : '<?php echo esc_attr( $prefs['captcha_sitekey'] ); ?>',
			'theme' : 'light'
		});

	};
</script>
<?php } ?>

<?php

		elseif ( $wak_registration->signup == 'driver' ) :

?>
			<div class="alert alert-success"><?php echo esc_attr( $prefs['success_signup'] ); ?></div>
		</div>
	</div>
</div>
<?php

		endif;

?>
<?php endif; ?>
<?php get_footer(); ?>