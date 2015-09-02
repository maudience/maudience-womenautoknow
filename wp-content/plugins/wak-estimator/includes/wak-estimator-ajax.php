<?php
// No dirrect access
if ( ! defined( 'WAK_ESTIMATOR_VER' ) ) exit;

/**
 * AJAX: Get Estimate
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_ajax_get_estimate' ) ) :
	function wak_estimator_ajax_get_estimate() {

		// Security
		//check_ajax_referer( 'wak-estimator-filter', 'token' );

		$state   = sanitize_text_field( $_POST['es_state'] );
		$service = sanitize_text_field( $_POST['es_service'] );

		if ( $state == '' || $service == '' )
			die( 0 );

		$label = __( 'Average part cost in %s', '' );
		$label_user = __( 'Average part cost based on WAK members.', '' );
		if ( $service == 'labor' ) {
			$label = __( 'Average labor cost in %s', '' );
			$label_user = __( 'Average labor cost based on WAK members.', '' );
		}

		$states  = wak_get_states();
		$member  = is_user_logged_in();
		$average = get_service_estimate_by_state( $state, $service );
		$users   = get_service_estimate_by_state_user( $state, $service );

		if ( $average === NULL && $users === NULL ) {

?>
<div class="row">
	<div class="col-md-12">
		<h1 class="pink text-center">-</h1>
		<p class="text-center"><?php _e( 'No data available.', '' ); ?></p>
	</div>
</div>
<?php

			die;

		}

?>
<div class="row">
	<div class="col-md-12">
		<h3 class="text-center"><?php echo sprintf( $label, $states[ $state ] ); ?></h3>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div id="wak-estimate-amount" class="pink text-center"><?php echo '$ ' . number_format( $average, 2, '.', ',' ); ?></div>
		<p class="text-center"><button type="button" data-dismiss="modal" aria-label="Close" class="btn btn-danger">Close</button></p>
	</div>
</div>
<?php

		die;

	}
endif;

?>