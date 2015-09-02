<?php

	get_header();

	global $wak_profile;

	$states      = wak_get_states();
	$wak_profile = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

	$wak_profile->is_my_profile = wak_theme_is_my_profile( get_current_user_id() );
	$wak_profile->account_type  = get_user_meta( $wak_profile->ID, 'type', true );
	$wak_profile->profile_tabs  = wak_theme_get_users_tabs( $wak_profile );
	$wak_profile->state         = get_user_meta( $wak_profile->ID, 'state', true );

?>
<div class="inner-wrapper boxed" id="user-profile">

	<div class="row sidebar-right">

		<div class="col-md-9 col-sm-9 col-xs-12" id="the-content">

			<article id="autoshop-<?php echo $wak_profile->ID; ?>" class="user-related">

				<div class="row">

					<div class="col-md-4 col-xs-12" id="wak-public-details">

						<?php echo get_avatar( $wak_profile->ID, 233 ); ?>

						<ul class="nav nav-tabs nav-stacked" id="wak-profile-tab-nav">
							<li><strong>Navigation</strong></li>
<?php

	foreach ( $wak_profile->profile_tabs as $tab => $link ) {

		if ( ! isset( $link['url'] ) )
			echo '<li role="presentation" class="' . $link['classes'] . '"><a href="#' . $tab . '"><i class="fa ' . $link['icon'] . '"></i>' . $link['title'] . '</a></li>';

		else
			echo '<li role="presentation" class="' . $link['classes'] . '"><a href="' . $link['url'] . '"><i class="fa ' . $link['icon'] . '"></i>' . $link['title'] . '</a></li>';

	}

?>
						</ul>
						<ul id="wak-logout">
							<?php echo '<li role="presentation" class="pink"><a href="' . wp_logout_url( home_url() ) . '"><i class="pink fa fa-sign-out"></i>Logout</a></li>'; ?>
						</ul>

						<ul class="nav nav-tabs nav-stacked" id="wak-profile-tab-subnav">
							<li><strong>Pages</strong></li>

<?php

	$locations  = get_nav_menu_locations();
	$menu       = wp_get_nav_menu_object( $locations['profile'] );
	$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );

	 _wp_menu_item_classes_by_context( $menu_items );

	foreach ( $menu_items as $item ) {

		echo '<li role="presentation"><a href="' . $item->url . '"><i class="' . implode( ' ', $item->classes ) . '"></i>' . $item->title . '</a></li>';

	}

?>
						</ul>

					</div>
	
					<div class="col-md-8 col-xs-12">
						<?php //wak_display_users_iq( $wak_profile->ID ); ?>
						<div class="tab-content" id="wak-profile-tab-wrap">

<?php

	foreach ( $wak_profile->profile_tabs as $tab => $link ) {

		if ( isset( $link['url'] ) ) continue;

		echo '<div role="tabpanel" class="tab-pane ' . $link['classes'] . '" id="' . $tab . '">' . "\n";

		get_template_part( 'author/tab', $tab );

		echo '</div>' . "\n";

	}

?></div>
					</div>

				</div>

			</article>

		</div>
		<div class="col-md-3 col-sm-3 col-xs-12" id="sidebar">

			<?php get_sidebar(); ?>

		</div>

	</div>
<script type="text/javascript">
jQuery(function($) {

	$( '#wak-profile-tab-nav a' ).click(function(e){

		e.preventDefault();
		$(this).tab('show');

	});

});
</script>
</div>
<?php get_footer(); ?>