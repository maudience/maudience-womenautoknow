<?php

	$member      = wp_get_current_user();
	$profile_url = wak_theme_get_profile_url( $member );

	$name = $member->first_name;
	if ( strlen( $name ) == 0 )
		$name = $member->user_login;

	$autoshop_count = array( 'count' => 0, 'IDs' => array() );
	if ( function_exists( 'wak_count_users_autoshops' ) )
		$autoshop_count = wak_count_users_autoshops( $member->ID );

?><div class="outer-wrapper user-related" id="user-navigation"<?php if ( ! is_author() ) echo ' style="display:none;"'; ?>>

	<div class="inner-wrapper boxed">

		<div class="row">
			<div class="col-md-12 col-xs-12">
				<div class="row">
					<div class="col-md-2 col-sm-2 col-xs-6">
						<a href="<?php echo esc_url( $profile_url ); ?>" title="Edit your profile"><i class="fa fa-car"></i>My Cars</a>
					</div>
					<div class="col-md-2 col-sm-2 col-xs-6">
						<a href="<?php echo esc_url( $profile_url . '?show=reviews' ); ?>" title="View your reviews"><i class="fa fa-star"></i>Reviews<span class="blue"><?php if ( function_exists( 'wak_count_users_reviews' ) ) echo wak_count_users_reviews( $member->ID ); else echo 0; ?></span></a>
					</div>
					<div class="col-md-2 col-sm-2 col-xs-6">
						<a href="<?php echo esc_url( $profile_url . '?show=log' ); ?>" title="View your cars maintenance log"><i class="fa fa-list"></i>Maintenance Log</a>
					</div>
					<?php if ( $autoshop_count['count'] > 0 ) : ?>

						<?php if ( $autoshop_count['count'] == 1 ) : ?>
					<div class="col-md-2 col-sm-2 col-xs-6">
						<a href="<?php echo get_permalink( $autoshop_count['IDs'][0] ); ?>" title="My Auto Shop"><i class="fa fa-street-view"></i>My Auto Shop</a>
					</div>
						<?php else : ?>
					<div class="col-md-2 col-sm-2 col-xs-6">
						<a href="<?php echo esc_url( $profile_url . '?show=shops' ); ?>" title="My Auto Shops"><i class="fa fa-street-view"></i>My Auto Shops<span class="blue"><?php echo $autoshop_count['count']; ?></span></a>
					</div>
						<?php endif; ?>

					<?php else : ?>
					<div class="col-md-2 col-sm-2 col-xs-6">
						<a href="<?php echo home_url( '/autoshops/' ); ?>" title="Search Auto Shops"><i class="fa fa-street-view"></i>Search Auto Shops</a>
					</div>
					<?php endif; ?>
					<div class="col-md-2 col-sm-2 col-xs-6">
						<a href="<?php echo esc_url( $profile_url . '?show=recalls' ); ?>" title="Recalls"><i class="fa fa-flash"></i>Recalls<span class="blue"><?php if ( function_exists( 'wak_count_my_recalls' ) ) echo wak_count_my_recalls( $member->ID ); else echo 0; ?></span></a>
					</div>
					<div class="col-md-2 col-sm-2 col-xs-6">
						<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" title="Signout from your account"><i class="fa fa-sign-out pink"></i>Logout</a>
					</div>
				</div>
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
			$( '#toggle-my-account' ).text( 'My Account' ).data( 'state', 'closed' );
			$( '#user-navigation' ).slideUp();

		}
		else {

			$( '#toggle-my-account-wrap' ).addClass( 'selected' );
			$( '#toggle-my-account' ).data( 'state', 'open' ).text( 'Hide Menu' );
			$( '#user-navigation' ).slideDown();

		}

		e.preventDefault();
		$(this).blur();

	});
	
	

});
</script>