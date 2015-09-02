<?php

/**
 * Users Badges
 * @version 1.0
 */
function wak_theme_users_badges( $user_id = NULL, $profile = false ) {

	$badges = array(
		'review' => array(
			'img'   => get_template_directory_uri() . '/images/check-icon-small.png',
			'opacity' => '0.2',
			'tooltip' => 'Leave a review to earn this badge'
		),
		'shops'  => array(
			'img'   => get_template_directory_uri() . '/images/wrench-icon-small.png',
			'opacity' => '0.2',
			'tooltip' => 'Add an auto shop to earn this badge'
		)
	);

	if ( function_exists( 'wak_count_users_reviews' ) ) {
		$reviews = wak_count_users_reviews( $user_id );
		if ( $reviews > 0 ) {
			$badges['review']['opacity'] = '1';
			$badges['review']['tooltip'] = false;
		}
		elseif ( ! $profile )
			$badges['review']['tooltip'] = false;
	}

	if ( function_exists( 'wak_count_users_autoshops' ) ) {
		$autoshops = wak_count_users_autoshops( $user_id );
		if ( $autoshops['count'] > 0 ) {
			$badges['shops']['opacity'] = '1';
			$badges['shops']['tooltip'] = false;
		}
		elseif ( ! $profile )
			$badges['shops']['tooltip'] = false;
	}

?>
<div id="wak-badges" style="height: 80px;">
<?php

	foreach ( $badges as $badge ) {

		$element = '<img src="' . esc_url( $badge['img'] ) . '" style="opacity: ' . $badge['opacity'] . '; float: left; margin-right: 24px; "';

		if ( $badge['tooltip'] !== false )
			$element .= ' data-toggle="tooltip" data-placement="top" title="' . esc_attr( $badge['tooltip'] ) . '"';

		$element .= ' width="60" height="60" />';

		echo $element;

	}

?>
</div>
<?php

}

?>