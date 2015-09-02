<?php
// No dirrect access
if ( ! defined( 'WAK_AUTOSHOPS_VER' ) ) exit;

/**
 * Shortcode: Reviews
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_reviews_shortcode' ) ) :
	function wak_reviews_shortcode( $atts, $content = '' ) {

		extract( shortcode_atts( array(
			'title'  => ''
		), $atts ) );

		ob_start();

		if ( $title != '' )
			echo '<h6>' . $title . '</h6>';

?>
<div id="wak-reviews">

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