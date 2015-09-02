<?php

	$post_id  = get_the_ID();
	$post_url = get_the_permalink( $post_id );
	$states   = wak_get_states();

?>
<div id="autoshop-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php wak_display_autoshop_rating( $post_id ); ?>
	<h4><a href="<?php echo $post_url; ?>"><?php the_title(); ?></a><small><?php

	$phone = get_post_meta( $post_id, 'phone', true );
	if ( $phone != '' )
		echo '<a href="' . $post_url . '">' . wak_clean_phone_number( $phone ) . '</a> ';

	echo esc_attr( get_post_meta( $post_id, 'zip', true ) ) . ' ';

	$state = get_post_meta( $post_id, 'state', true );
	if ( isset( $states[ $state ] ) )
		echo $states[ $state ];
	else
		echo $state;

?></small></h4>
	<p class="autoshop-action-links"><?php echo get_review_actions( $post_id ); ?></p>
</div>