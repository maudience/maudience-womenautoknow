<?php
// No dirrect access
if ( ! defined( 'WAK_REGISTER_VER' ) ) exit;

/**
 * Shortcode: Signup
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_shortcode' ) ) :
	function wak_registration_shortcode( $atts, $content = '' ) {

		extract( shortcode_atts( array(
			'title'  => ''
		), $atts ) );

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

		ob_start();

		if ( $title != '' )
			echo '<h6>' . $title . '</h6>';

		$selected = 'autoshop';

		if ( ! $wak_registration->result ) :

?>
<style type="text/css">
body input[type="text"].form-control, body input[type="number"].form-control, body input[type="email"].form-control, body input[type="date"].form-control, body input[type="password"].form-control, body select.form-control {
	border-color: #EC008B;
}
#the-content .row {
	margin-bottom: 0;
}
</style>
<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?onload=waksignup&render=explicit&ver=1.0.1" async defer></script>
<div id="wak-signup">
				<h2 class="pink">Auto Shop Enrollment</h2>
				<div class="alert alert-info">Join the WAK community and gain access to thousands of pledged drivers.</div>
<?php

			if ( $wak_registration->signup == 'autoshop' && ! empty( $wak_registration->errors ) ) {

				$message = 'The following errors were found:<ul>';
				foreach ( $wak_registration->errors as $error_code => $error ) {
					$message .= '<li>' . $error . '</li>';
				}
				$message .= '</ul>';

				echo '<div class="alert alert-warning">' . $message . '</div>';

			}

			$show = 'first';
			if ( empty( $wak_registration->errors ) || wak_section_has_error( array( 'shopname', 'address1', 'city', 'zip', 'state', 'phone', 'website', 'facebook', 'twitter' ) ) )
				$show = 'first';

			elseif ( wak_section_has_error( array( 'username', 'email', 'pwd1', 'pwd2' ) ) )
				$show = 'second';

			elseif ( wak_section_has_error( array( 'captcha', 'type', 'terms', 'billing-address1', 'billing-city', 'billing-zip', 'billing-state', 'card-name', 'card-number', 'card-exp', 'card-cvv' ) ) )
				$show = 'third';

?>
				<form id="wak-signup-shop-form" method="post" class="form" action="">
					<div class="panel-group" id="accordionshop" role="tablist" aria-multiselectable="false">
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="headingThree">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionshop" href="#collapseThree" <?php if ( $show == 'first' ) echo 'aria-expanded="true"'; else echo 'aria-expanded="false" class="collapsed"'; ?> aria-controls="collapseThree">Auto Shop Details</a>
								</h4>
							</div>
							<div id="collapseThree" class="panel-collapse collapse<?php if ( $show == 'first' ) echo ' in'; ?>" role="tabpanel" aria-labelledby="headingThree"<?php if ( $show != 'first' ) echo ' aria-expanded="false" style="height:0px;"'; else echo ' aria-expanded="true"'; ?>>
								<div class="panel-body">

									<div class="row">
										<div class="col-md-6 col-sm-6 col-xs-12">

											<div class="form-group<?php registration_has_error( 'shopname' ); ?>">
												<label class="control-label" for="wak-new-shop-shopname">Auto Shop Name</label>
												<input type="text" name="wak_new_shop[shopname]" class="form-control" placeholder="required" id="wak-new-shop-shopname" value="<?php if ( isset( $_POST['wak_new_shop']['shopname'] ) ) echo esc_attr( $_POST['wak_new_shop']['shopname'] ); ?>" />
											</div>
											<div class="form-group<?php registration_has_error( 'address1' ); ?>">
												<label class="control-label" for="wak-new-shop-address1">Business Address</label>
												<input type="text" name="wak_new_shop[address1]" class="form-control" placeholder="required" id="wak-new-shop-address1" value="<?php if ( isset( $_POST['wak_new_shop']['address1'] ) ) echo esc_attr( $_POST['wak_new_shop']['address1'] ); ?>" />
											</div>
											<div class="form-group<?php registration_has_error( 'city' ); ?>">
												<label class="control-label" for="wak-new-shop-city">City</label>
												<input type="text" name="wak_new_shop[city]" class="form-control" placeholder="required" id="wak-new-shop-city" value="<?php if ( isset( $_POST['wak_new_shop']['city'] ) ) echo esc_attr( $_POST['wak_new_shop']['city'] ); ?>" />
											</div>

											<div class="row" style="margin-left: -15px; margin-right: -15px;">
												<div class="col-md-4 col-sm-4 col-xs-4">
													<div class="form-group<?php registration_has_error( 'zip' ); ?>">
														<label class="control-label" for="wak-new-shop-zip">Zip</label>
														<input type="text" name="wak_new_shop[zip]" class="form-control" placeholder="required" maxlength="5" id="wak-new-shop-zip" value="<?php if ( isset( $_POST['wak_new_shop']['zip'] ) ) echo esc_attr( $_POST['wak_new_shop']['zip'] ); ?>" />
													</div>
												</div>
												<div class="col-md-8 col-sm-8 col-xs-8">
													<div class="form-group<?php registration_has_error( 'state' ); ?>">
														<label class="control-label" for="wak-new-shop-state">State</label>
														<?php echo wak_states_dropdown( 'wak_new_shop[state]', 'wak-new-shop-state', 'Select State', ( ( isset( $_POST['wak_new_shop']['state'] ) ) ? esc_attr( $_POST['wak_new_shop']['state'] ) : '' ) ); ?>
													</div>
												</div>
											</div>

										</div>
										<div class="col-md-6 col-sm-6 col-xs-12">

											<div class="form-group<?php registration_has_error( 'phone' ); ?>">
												<label class="control-label" for="wak-new-shop-phone">Phone</label>
												<input type="text" name="wak_new_shop[phone]" class="form-control" id="wak-new-shop-phone" value="<?php if ( $selected == 'autoshop' && isset( $_POST['wak_new_shop']['phone'] ) ) echo esc_attr( $_POST['wak_new_shop']['phone'] ); ?>" />
											</div>
											<div class="form-group<?php registration_has_error( 'website' ); ?>">
												<label class="control-label" for="wak-new-shop-website">Website</label>
												<input type="text" name="wak_new_shop[website]" class="form-control" placeholder="http://" id="wak-new-shop-website" value="<?php if ( isset( $_POST['wak_new_shop']['website'] ) ) echo esc_attr( $_POST['wak_new_shop']['website'] ); ?>" />
											</div>
											<div class="form-group<?php registration_has_error( 'facebook' ); ?>">
												<label class="control-label" for="wak-new-shop-facebook">Facebook</label>
												<input type="text" name="wak_new_shop[facebook]" class="form-control" placeholder="http://" id="wak-new-shop-facebook" value="<?php if ( isset( $_POST['wak_new_shop']['facebook'] ) ) echo esc_attr( $_POST['wak_new_shop']['facebook'] ); ?>" />
											</div>
											<div class="form-group<?php registration_has_error( 'twitter' ); ?>">
												<label class="control-label" for="wak-new-shop-twitter">Twitter</label>
												<input type="text" name="wak_new_shop[twitter]" class="form-control" placeholder="http://" id="wak-new-shop-twitter" value="<?php if ( isset( $_POST['wak_new_shop']['twitter'] ) ) echo esc_attr( $_POST['wak_new_shop']['twitter'] ); ?>" />
											</div>

										</div>
									</div>

									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12 text-right">
											<a class="btn btn-danger btn-lg" data-toggle="collapse" data-parent="#accordionshop" href="#collapseFour" aria-controls="collapseFour">Next</a>
										</div>
									</div>

								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="headingFour">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionshop" href="#collapseFour" <?php if ( $show == 'second' ) echo 'aria-expanded="true"'; else echo 'aria-expanded="false" class="collapsed"'; ?> aria-controls="collapseFour">Login Details</a>
								</h4>
							</div>
							<div id="collapseFour" class="panel-collapse collapse<?php if ( $show == 'second' ) echo ' in'; ?>" role="tabpanel" aria-labelledby="headingFour"<?php if ( $show != 'second' ) echo ' aria-expanded="false" style="height:0px;"'; else echo ' aria-expanded="true"'; ?>>
								<div class="panel-body">

									<div class="row">
										<div class="col-md-6 col-sm-6 col-xs-12">

											<div class="form-group<?php registration_has_error( 'username' ); ?>">
												<label class="control-label" for="wak-new-shop-username">Username</label>
												<input type="text" name="wak_new_shop[username]" class="form-control half" placeholder="required" id="wak-new-shop-username" value="<?php if ( isset( $_POST['wak_new_shop']['username'] ) ) echo esc_attr( $_POST['wak_new_shop']['username'] ); ?>" />
											</div>
											<div class="form-group<?php registration_has_error( 'email' ); ?>">
												<label class="control-label" for="wak-new-shop-email">Email</label>
												<input type="text" name="wak_new_shop[email]" class="form-control" placeholder="required" id="wak-new-shop-email" value="<?php if ( isset( $_POST['wak_new_shop']['email'] ) ) echo esc_attr( $_POST['wak_new_shop']['email'] ); ?>" />
											</div>

										</div>
										<div class="col-md-6 col-sm-6 col-xs-12">

											<div class="form-group<?php registration_has_error( 'pwd1' ); ?>">
												<label class="control-label" for="wak-new-shop-pwd1">Password</label>
												<input type="password" name="wak_new_shop[pwd1]" class="form-control" placeholder="required" id="wak-new-shop-pwd1" value="<?php if ( isset( $_POST['wak_new_shop']['pwd1'] ) ) echo esc_attr( $_POST['wak_new_shop']['pwd1'] ); ?>" />
												<div class="form-control-static text-dandger" id="pwd-messages-shop"></div>
											</div>
											<div class="form-group<?php registration_has_error( 'pwd1' ); ?>">
												<label class="control-label" for="wak-new-shop-pwd2">Confirm Password</label>
												<input type="password" name="wak_new_shop[pwd2]" class="form-control" placeholder="required" id="wak-new-shop-pwd2" value="<?php if ( isset( $_POST['wak_new_shop']['pwd2'] ) ) echo esc_attr( $_POST['wak_new_shop']['pwd2'] ); ?>" />
											</div>

										</div>
									</div>

									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12 text-right">
											<a class="btn btn-danger btn-lg" data-toggle="collapse" data-parent="#accordionshop" href="#collapseFive" aria-controls="collapseFive">Next</a>
										</div>
									</div>

								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="headingFive">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionshop" href="#collapseFive" <?php if ( $show == 'third' ) echo 'aria-expanded="true"'; else echo 'aria-expanded="false" class="collapsed"'; ?> aria-controls="collapseFive">Payment Details</a>
								</h4>
							</div>
							<div id="collapseFive" class="panel-collapse collapse<?php if ( $show == 'third' ) echo ' in'; ?>" role="tabpanel" aria-labelledby="headingFive"<?php if ( $show != 'third' ) echo ' aria-expanded="false" style="height:0px;"'; else echo ' aria-expanded="true"'; ?>>
								<div class="panel-body">

									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<p>Please select your account type.</p>
										</div>
									</div>

									<div class="row" style="margin-bottom: 24px;">
										<div class="col-md-4 col-sm-4 col-xs-12">
											<label for="wak-new-shop-regular"><input type="radio" name="wak_new_shop[type]" id="wak-new-shop-regular" value="0" class="toggle-payment-box" data-type="free"<?php if ( isset( $_POST['wak_new_shop']['type'] ) ) checked( $_POST['wak_new_shop']['type'], 0 ); ?> /> Free Account</label>
										</div>
										<?php if ( isset( $payprefs['disable_pay_signup'] ) && $payprefs['disable_pay_signup'] == 0 ) : ?>
										<div class="col-md-4 col-sm-4 col-xs-12">
											<label for="wak-new-shop-premium"><input type="radio" name="wak_new_shop[type]" id="wak-new-shop-premium" value="1" class="toggle-payment-box" data-type="pay"<?php if ( isset( $_POST['wak_new_shop']['type'] ) ) checked( $_POST['wak_new_shop']['type'], 1 ); ?> /> Premium Account <?php echo esc_attr( $plans['monthly_subscription']['label'] ); ?></label>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-12">
											<label for="wak-new-shop-pledged"><input type="radio" name="wak_new_shop[type]" id="wak-new-shop-pledged" value="2" class="toggle-payment-box" data-type="pay"<?php if ( isset( $_POST['wak_new_shop']['type'] ) ) checked( $_POST['wak_new_shop']['type'], 2 ); ?> /> Pledged Auto Shop <?php echo esc_attr( $plans['pledged']['label'] ); ?></label>
										</div>
										<?php endif; ?>
									</div>

									<div class="row">
										<div class="col-md-6 col-sm-6 col-xs-12">
											<div class="form-group<?php registration_has_error( 'first_name' ); ?>">
												<label class="control-label" for="wak-new-shop-first_name">First Name</label>
												<input type="text" name="wak_new_shop[first_name]" class="form-control" placeholder="required" id="wak-new-shop-first_name" value="<?php if ( isset( $_POST['wak_new_shop']['first_name'] ) ) echo esc_attr( $_POST['wak_new_shop']['first_name'] ); ?>" />
											</div>
										</div>
										<div class="col-md-6 col-sm-6 col-xs-12">
											<div class="form-group<?php registration_has_error( 'last_name' ); ?>">
												<label class="control-label" for="wak-new-shop-last_name">Last Name</label>
												<input type="text" name="wak_new_shop[last_name]" class="form-control" placeholder="required" id="wak-new-shop-last_name" value="<?php if ( isset( $_POST['wak_new_shop']['last_name'] ) ) echo esc_attr( $_POST['wak_new_shop']['last_name'] ); ?>" />
											</div>
										</div>
									</div>

									<div id="wak-shop-free" class="wak-type-box" style="<?php if ( isset( $_POST['wak_new_shop']['type'] ) && $_POST['wak_new_shop']['type'] == 0 )  echo 'display:block;'; else echo 'display:none;'; ?>">

										<div class="row">
											<div class="col-md-12 col-sm-12 col-xs-12">
												<div class="form-group">
													<div class="checkbox">
														<label for="wak-new-free-shop-terms"><input type="checkbox" name="wak_new_shop[terms]" id="wak-new-free-shop-terms" value="1" /> I have read and accept WAK's <a href="<?php echo esc_url( $terms_url ); ?>" class="pink">Terms and conditions</a>.</label>
													</div>
												</div>
											</div>
										</div>

									</div>

									<div id="wak-shop-pay" class="wak-type-box" style="<?php if ( isset( $_POST['wak_new_shop']['type'] ) && $_POST['wak_new_shop']['type'] != 0 ) echo 'display:block;'; else echo 'display:none;'; ?>">

										<div class="row">
											<div class="col-md-6 col-sm-6 col-xs-12">

												<div class="form-group<?php registration_has_error( 'billing-address1' ); ?>">
													<label class="control-label" for="wak-new-shop-billing-address1">Billing Address</label>
													<input type="text" name="wak_new_shop[billing-address1]" class="form-control" placeholder="required" id="wak-new-shop-billing-address1" value="<?php if ( isset( $_POST['wak_new_shop']['billing-address1'] ) ) echo esc_attr( $_POST['wak_new_shop']['billing-address1'] ); ?>" />
												</div>
												<div class="form-group<?php registration_has_error( 'billing-city' ); ?>">
													<label class="control-label" for="wak-new-shop-billing-city">Billing City</label>
													<input type="text" name="wak_new_shop[billing-city]" class="form-control" placeholder="required" id="wak-new-shop-billing-city" value="<?php if ( isset( $_POST['wak_new_shop']['billing-city'] ) ) echo esc_attr( $_POST['wak_new_shop']['billing-city'] ); ?>" />
												</div>

												<div class="row">
													<div class="col-md-4 col-sm-4 col-xs-4">
														<div class="form-group<?php registration_has_error( 'billing-zip' ); ?>">
															<label class="control-label" for="wak-new-shop-billing-zip">Billing Zip</label>
															<input type="text" name="wak_new_shop[billing-zip]" class="form-control" placeholder="required" maxlength="5" id="wak-new-shop-billing-zip" value="<?php if ( isset( $_POST['wak_new_shop']['billing-zip'] ) ) echo esc_attr( $_POST['wak_new_shop']['billing-zip'] ); ?>" />
														</div>
													</div>
													<div class="col-md-8 col-sm-8 col-xs-8">
														<div class="form-group<?php registration_has_error( 'billing-state' ); ?>">
															<label class="control-label" for="wak-new-shop-billing-state">Billing State</label>
															<?php echo wak_states_dropdown( 'wak_new_shop[billing-state]', 'wak-new-shop-billing-state', 'Select State', ( ( isset( $_POST['wak_new_shop']['billing-state'] ) ) ? esc_attr( $_POST['wak_new_shop']['billing-state'] ) : '' ) ); ?>
														</div>
													</div>
												</div>

											</div>
											<div class="col-md-6 col-sm-6 col-xs-12">

												<div class="form-group<?php registration_has_error( 'card-number' ); ?>">
													<label class="control-label" for="wak-new-shop-card-number">Credit Card Number</label>
													<input type="text" name="wak_new_shop[card-number]" class="form-control" placeholder="required" id="wak-new-shop-card-number" value="<?php if ( isset( $_POST['wak_new_shop']['card-number'] ) ) echo esc_attr( $_POST['wak_new_shop']['card-number'] ); ?>" />
												</div>
												<div class="form-group<?php registration_has_error( 'card-exp' ); ?>">
													<label class="control-label" for="wak-new-shop-card-number">Card Expiration</label>
													<?php if ( function_exists( 'wak_list_option_months' ) ) : ?>
													<div>
														<select name="wak_new_shop[card-mm]" id="wak-new-shop-card-mm" class="form-control short pull-left"><?php wak_list_option_months( ( isset( $_POST['wak_new_shop']['card-mm'] ) ) ? $_POST['wak_new_shop']['card-mm'] : '' ); ?></select><div class="pull-left"> - </div><select name="wak_new_shop[card-yy]" class="form-control short pull-left" id="wak-new-shop-card-yy"><?php wak_list_option_card_years( ( isset( $_POST['wak_new_shop']['card-mm'] ) ) ? $_POST['wak_new_shop']['card-yy'] : '' ); ?></select>
													</div>
													<?php endif; ?>
													<div class="clear clearfix"></div>
												</div>
												<div class="form-group<?php registration_has_error( 'card-cvv' ); ?>">
													<label class="control-label" for="wak-new-shop-card-cvv">CVV Number</label>
													<input type="text" name="wak_new_shop[card-cvv]" class="form-control" maxlength="4" placeholder="required" id="wak-new-shop-card-cvv" value="<?php if ( isset( $_POST['wak_new_shop']['card-cvv'] ) ) echo esc_attr( $_POST['wak_new_shop']['card-cvv'] ); ?>" />
												</div>

											</div>
										</div>

										<div class="row">
											<div class="col-md-12 col-sm-12 col-xs-12">
												<div class="form-group">
													<div class="checkbox">
														<label for="wak-new-pay-shop-terms"><input type="checkbox" name="wak_new_shop[terms]" id="wak-new-pay-shop-terms" value="1" /> I have read and accept WAK's <a href="<?php echo esc_url( $terms_url ); ?>" class="pink">Terms and conditions</a>.</label>
													</div>
												</div>
											</div>
										</div>

									</div>

									<div id="wak-autoshop-accept-terms" style="<?php if ( ! empty( $wak_registration->errors ) ) echo 'display:block;'; else echo 'display:none;'; ?>">

										<div class="row">
											<div class="col-md-12 col-sm-12 col-xs-12">
												<?php wak_registration_display_captcha( 'recaptcha2' ); ?>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12 col-sm-12 col-xs-12 text-right">
												<input type="submit" class="btn btn-lg btn-danger" value="Join WAK" />
											</div>
										</div>

									</div>

								</div>
							</div>
						</div>
					</div>

				</form>
			</div>
</div>
<script type="text/javascript">
( function( $ ) {

	$('#wak-new-driver-pwd1').keyup(function(e) {
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

		else :

			if ( $wak_registration->signup == 'driver' || $wak_registration->result == 'new-shop' ) {

?>
<div class="alert alert-success"><?php echo esc_attr( $prefs['success_signup'] ); ?></div>
<?php

				// if this is a free auto shop signup, show a warning that the shop needs to be approved before it shows.
				if ( $wak_registration->result == 'new-shop' )
					echo '<div class="alert alert-info">Please note that your auto shop will not be visible until it has been moderated by WAK.</div>';

			}
			else {

?>
<div class="alert alert-success"><?php echo $paid_thanks; ?></div>
<?php

			}

		endif;

		$output = ob_get_contents();
		ob_end_clean();

		return do_shortcode( $output );

	}
endif;
?>