<?php

	global $wak_profile;

	if ( $wak_profile === NULL )
		$wak_profile = wp_get_current_user();

	if ( $wak_profile->is_my_profile )
		$title = 'Your Profile';
	else
		$title = sprintf( '%s\'s Profile', $wak_profile->display_name );

	if ( $wak_profile->is_my_profile ) :

?>
<div class="inline-row">
	<div style="padding-top:12px;">
		<div class="widget"><h4 class="widget-title">Notifications</h4></div>
	</div>
</div>
<?php

	else :

		get_template_part( 'author/tab', 'shops' );

	endif;

?>