<?php

	global $wak_profile;

	if ( $wak_profile === NULL )
		$wak_profile = wp_get_current_user();

	$name = $wak_profile->first_name;
	if ( strlen( $name ) == 0 )
		$name = $wak_profile->user_login;

	$wak_profile->age        = get_user_meta( $wak_profile->ID, 'age', true );
	$wak_profile->gender     = absint( get_user_meta( $wak_profile->ID, 'gender', true ) );
	$wak_profile->zip        = get_user_meta( $wak_profile->ID, 'zip', true );
	$wak_profile->state      = get_user_meta( $wak_profile->ID, 'state', true );
	$wak_profile->newsletter = absint( get_user_meta( $wak_profile->ID, 'newsletter', true ) );

?>
<div class="inline-row">
<?php

	if ( isset( $_GET['updated'] ) && $_GET['updated'] == 1 )
		echo '<div class="alert alert-success">Profile updated.</div>';

	elseif ( isset( $_POST['wak_profile'] ) )
		echo '<div class="alert alert-warning"><strong class="alert-link">The following errors were found:</strong><ul><li>' . implode( '</li><li>', $_POST['wak_profile'] ) . '</li></ul></div>';

?>
		<form id="wak-edit-my-profile-form" class="form-horizontal" method="post" action="">
			<input type="hidden" name="wak_profile[profile_id]" value="<?php echo absint( $wak_profile->ID ); ?>" />
			<input type="hidden" name="wak_profile[token]" value="<?php echo wp_create_nonce( 'wak-edit-my-profile' . $wak_profile->ID ); ?>" />

			<div class="widget"><h4 class="widget-title">Profile Details</h4></div>
			<div class="form-group">
				<label class="col-sm-4 control-label">Account Type</label>
				<div class="col-sm-8">
					<p class="form-control-static"><?php echo wak_theme_display_users_account_type( $wak_profile->ID ); ?></p>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-4 control-label" for="wak-profile-fname">First Name</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="wak_profile[first_name]" id="wak-profile-fname" value="<?php echo esc_attr( $wak_profile->first_name ); ?>" placeholder="Required" />
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-4 control-label" for="wak-profile-lname">Last Name</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="wak_profile[last_name]" id="wak-profile-lname" value="<?php echo esc_attr( $wak_profile->last_name ); ?>" placeholder="Required" />
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-4 control-label" for="wak-profile-female">Gender</label>
				<div class="col-sm-8">
					<label class="radio-inline" for="wak-profile-female"><input type="radio"<?php checked( $wak_profile->gender, 0 ); ?> name="wak_profile[gender]" id="wak-profile-female" value="0" /> Female</label> 
					<label class="radio-inline" for="wak-profile-male"><input type="radio"<?php checked( $wak_profile->gender, 1 ); ?> name="wak_profile[gender]" id="wak-profile-male" value="1" /> Male</label>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-4 control-label" for="wak-profile-age">Age</label>
				<div class="col-sm-8">
					<input type="number" class="form-control" name="wak_profile[age]" id="wak-profile-age" value="<?php echo esc_attr( $wak_profile->age ); ?>" placeholder="Required" style="width: 100px;" />
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-4 control-label" for="wak-profile-state">State</label>
				<div class="col-sm-8">
					<?php echo wak_states_dropdown( 'wak_profile[state]', 'wak-profile-state', 'Select State', $wak_profile->state ); ?>
				</div>
			</div>

			<div class="form-group push-down">
				<label class="col-sm-4 control-label" for="wak-profile-zip">Zip Code</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="wak_profile[zip]" id="wak-profile-zip" value="<?php echo esc_attr( $wak_profile->zip ); ?>" placeholder="Required" style="width: 100px;" />
				</div>
			</div>

			<?php if ( function_exists( 'user_has_autoshops' ) && user_has_autoshops( $wak_profile->ID ) ) : ?>

			<div class="widget"><h4 class="widget-title">Billing Address</h4></div>
			<div class="form-group push-down">
				<label class="col-sm-4 control-label" for="wak-profile-billing-address">Address</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="wak_profile[billing-address]" id="wak-profile-billing-address" value="<?php echo esc_attr( get_user_meta( $wak_profile->ID, 'billing-address', true ) ); ?>" placeholder="Required" />
				</div>
			</div>

			<div class="form-group push-down">
				<label class="col-sm-4 control-label" for="wak-profile-billing-city">City</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="wak_profile[billing-city]" id="wak-profile-billing-city" value="<?php echo esc_attr( get_user_meta( $wak_profile->ID, 'billing-city', true ) ); ?>" placeholder="Required" />
				</div>
			</div>

			<div class="form-group push-down">
				<label class="col-sm-4 control-label" for="wak-profile-billing-zip">Zip</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="wak_profile[billing-zip]" id="wak-profile-billing-zip" value="<?php echo esc_attr( get_user_meta( $wak_profile->ID, 'billing-zip', true ) ); ?>" placeholder="Required" />
				</div>
			</div>

			<div class="form-group push-down">
				<label class="col-sm-4 control-label" for="wak-profile-billing-state">State</label>
				<div class="col-sm-8">
					<?php echo wak_states_dropdown( 'wak_profile[billing-state]', 'wak-profile-billing-state', 'Select State', get_user_meta( $wak_profile->ID, 'billing-state', true ) ); ?>
				</div>
			</div>

			<?php endif; ?>

			<div class="widget"><h4 class="widget-title">Subscriptions</h4></div>
			<div class="form-group push-down">
				<label class="col-sm-4 control-label" for="wak-profile-newsletter">Newsletter</label>
				<div class="col-sm-8">
					<div class="checkbox"><label for=""><input type="checkbox" name="wak_profile[newsletter]" id="wak-profile-newsletter" value="1"<?php checked( $wak_profile->newsletter, 1 ); ?> /></label></div>
				</div>
			</div>

			<div class="widget"><h4 class="widget-title">Login Details</h4></div>
			<div class="form-group">
				<label class="col-sm-4 control-label" for="wak-profile-email">Email</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="wak_profile[email]" id="wak-profile-email" value="<?php echo esc_attr( $wak_profile->user_email ); ?>" placeholder="Required" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label" for="wak-profile-pwd1">New Password</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="wak_profile[new_pwd]" id="wak-profile-pwd1" value="" />
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-4 control-label">&nbsp;</label>
				<div class="col-sm-8 col-sm-8">
					<input type="text" class="form-control" name="wak_profile[new_pwd2]" id="wak-profile-pwd2" value="" />
				</div>
			</div>

			<div class="form-group text-right">
				<label class="col-sm-4 control-label">&nbsp;</label>
				<div class="col-sm-8 col-sm-8">
					<input type="submit" class="btn btn-danger" value="Save Changes" />
				</div>
			</div>

		</form>
</div>