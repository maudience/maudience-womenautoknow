<?php
// No dirrect access
if ( ! defined( 'WAK_PAYMENTS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_load_go_premium' ) ) :
	function wak_ajax_load_go_premium() {

		// Security
		check_ajax_referer( 'wak-load-payment-form', 'token' );

		$post_id  = absint( $_POST['shopid'] );
		$user_id  = get_current_user_id();
		$post     = get_post( $post_id );
		$owner_id = wak_get_autoshop_owner( $post_id );

		if ( ! isset( $post->ID ) || get_post_status( $post_id ) != 'publish' || $owner_id != $user_id ) die( -1 );

		wak_autoshop_upgrade_form( $post );
		die;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_upgrade_autoshop' ) ) :
	function wak_ajax_upgrade_autoshop() {

		// Get the form
		parse_str( $_POST['form'], $post );
		unset( $_POST );

		//echo '<pre>' . print_r( $post, true ) . '</pre>';
		//die;

		$data = wp_parse_args( $post['wak_upgrade_autoshop'], array(
			'token'      => '',
			'post_id'    => '',
			'plan'       => '',
			'first_name' => '',
			'last_name'  => '',
			'card'       => '',
			'exp_mm'     => '',
			'exp_yy'     => '',
			'cvv'        => '',
			'terms'      => 0
		) );

		// Security
		if ( ! wp_verify_nonce( $data['token'], 'submit-new-wak-payment' . $data['post_id'] ) ) die( 'BAD TOKEN' );

		$data = wak_sanitize_and_validate_form( $data );

		if ( ! empty( $data['errors'] ) ) {

			wp_send_json_error( '<div class="alert alert-warning">The following errors were found:<ul><li>' . implode( '</li><li>', $data['errors'] ) . '</li></ul><div>' );

		}

		$charge = wak_charge_payment( $data );

		if ( ! is_array( $charge ) ) {

			wp_send_json_error( '<div class="alert alert-warning">' . esc_attr( $charge ) . '<div>' );

		}

		$plans = wak_payment_plans();

		$until = 'month';
		if ( $data['plan'] == 'one_time' )
			$until = $plans['one_time']['length'];

		$until = wak_upgrade_autoshop( 'premium', $data['post_id'], $until );

		ob_start();

?>
<div class="alert alert-success">Payment Successful. Your auto shop has now been upgraded.</p></div>
<form>
	<div class="row form-group">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-premium-">WAK Reference</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12"><?php echo $charge['payment_id']; ?></div>
	</div>
	<div class="row form-group">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-premium-">Payment Plan</label></div>
		<div class="col-md-8 col-sm-8 col-xs-12"><?php echo $plans[ $data['plan'] ]['label']; ?></div>
	</div>
	<?php if ( $data['plan'] == 'one_time' ) : ?>
	<div class="row form-group">
		<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-premium-"><?php if ( $data['plan'] != 'one_time' ) echo 'Next Payment'; else echo 'Expires'; ?></label></div>
		<div class="col-md-8 col-sm-8 col-xs-12"><?php echo $until; ?></div>
	</div>
	<?php endif; ?>
</form>
<?php

		$content = ob_get_contents();
		ob_end_clean();

		wp_send_json_success( $content );

	}
endif;

?>