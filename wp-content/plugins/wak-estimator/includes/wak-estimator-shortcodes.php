<?php
// No dirrect access
if ( ! defined( 'WAK_ESTIMATOR_VER' ) ) exit;

/**
 * Shortcode: Estimator
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_shortcode' ) ) :
	function wak_estimator_shortcode( $atts, $content = '' ) {

		extract( shortcode_atts( array(
			'title'  => ''
		), $atts ) );

		ob_start();

		if ( $title != '' )
			echo '<h6>' . $title . '</h6>';

?>
<div id="wak-estimator">
	<form class="form" role="form" method="post" action="">
		<div class="form-group">
			<?php echo wak_states_dropdown( 'state', 'wak-estimate-state', 'Select State' ); ?>
		</div>
		<div class="form-group">
			<?php echo wak_estimator_service_dropdown( 'service', 'wak-estimate-service', 'Select Service' ); ?>
		</div>
		<div class="form-group">
			<input type="submit" class="form-control btn btn-danger btn-block" value="Get an Estimate" />
		</div>
	</form>
</div>
<div class="modal fade" id="wak-esitmator-results" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<h1 class="text-center pink"><i class="fa fa-spinner fa-spin"></i></h1>
				<p class="text-center">loading results ...</p>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
( function( $ ) {

	

} )( jQuery );
</script>
<?php

		$output = ob_get_contents();
		ob_end_clean();

		return do_shortcode( $output );

	}
endif;

?>