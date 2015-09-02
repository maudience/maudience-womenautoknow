<div class="outer-wrapper user-login visitor" id="user-navigation" style="display:none;">

	<div class="inner-wrapper boxed">

		<div class="row">
			<div class="col-md-12 col-xs-12">
				<div class="top-curve"><div class="left-curve"></div><div class="right-curve"></div></div>
				<form id="wak-loginform" class="form" role="form" method="post" action="">
					<div class="row">
						<div class="col-md-3 col-xs-12">
							<input style="display:none">
							<input type="password" style="display:none">
							<input type="text" name="log" class="form-control" autocomplete="off" id="wak-username" placeholder="Email or Username" value="" />
						</div>
						<div class="col-md-3 col-xs-12">
							<input type="password" name="pwd" class="form-control" autocomplete="off" id="wak-pass" placeholder="Password" value="" />
						</div>
						<div class="col-md-3 col-xs-12">
							<p class="form-control-static"><?php do_action( 'wak_login_form' ); ?></p>
							<input type="hidden" name="rememberme" value="forever" />
							<input type="hidden" name="redirect_to" value="<?php echo esc_url( home_url() ); ?>" />
						</div>
						<div class="col-md-3 col-xs-12">
							<input type="submit" class="form-control btn btn-danger btn-block" id="wak-login-button" value="Login" />
						</div>
					</div>
				</form>
			</div>
		</div>

	</div>

</div>
<script type="text/javascript">
jQuery(function($) {

	var togglewrap   = $( '#toggle-my-account-wrap' );
	var togglebutton = $( '#toggle-my-account' );
	var usernav      = $( '#user-navigation' );

	$( '#toggle-my-account' ).on( 'click', function(e){

		console.log( $(this).data( 'state' ) );

		if ( $(this).data( 'state' ) == 'open' ) {

			$( '#toggle-my-account-wrap' ).removeClass( 'selected' );
			$( '#toggle-my-account' ).text( 'Login' ).data( 'state', 'closed' );
			$( '#user-navigation' ).slideUp();

		}
		else {

			$( '#toggle-my-account-wrap' ).addClass( 'selected' );
			$( '#toggle-my-account' ).data( 'state', 'open' ).text( 'Close' );
			$( '#user-navigation' ).slideDown();

		}

		e.preventDefault();
		$(this).blur();

	});
	
	$( 'form#wak-loginform' ).on( 'submit', function(e){

		e.preventDefault();

		var username_el = $( '#wak-username' );
		var password_el = $( '#wak-pass' );
		var submit_el   = $( '#wak-login-button' );

		if ( username_el.val() == '' ) {

			alert( 'Please provide your WAK username or email.' );
			return false;

		}

		if ( username_el.val() == '' ) {

			alert( 'Please provide your WAK account password.' );
			return false;

		}

		$.ajax({
			type       : "POST",
			data       : {
				action    : 'wak-login',
				uname     : username_el.val(),
				password  : password_el.val(),
				token     : '<?php echo wp_create_nonce( 'wak-login' ); ?>'
			},
			beforeSend : function() {

				submit_el.attr( 'disabled', 'disabled' ).val( 'Logging in...' );
				username_el.attr( 'disabled', 'disabled' );
				password_el.attr( 'disabled', 'disabled' );
	
			},
			dataType   : "JSON",
			url        : '<?php echo admin_url( 'admin-ajax.php' ); ?>',
			success    : function( response ) {

				if ( response.success )
					window.location.href = response.data;

				else {

					alert( response.data );

					submit_el.removeAttr( 'disabled' ).val( 'Login' );
					username_el.removeAttr( 'disabled' );
					password_el.removeAttr( 'disabled' ).val( '' );

				}

			}
		});

		return false;

	});

});
</script>