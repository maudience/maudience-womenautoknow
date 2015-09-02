<?php
// No dirrect access
if ( ! defined( 'WAK_AUTOSHOPS_VER' ) ) exit;

/**
 * Map Shortcodes in VC
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_reviews_vc_map_shortcodes' ) ) :
	function wak_reviews_vc_map_shortcodes() {

		// pmleague_login_form
		vc_map( array(
			'name'                    => __( 'WAK Reviews', 'wakreviews' ),
			'base'                    => 'wak_reviews',
			'description'             => __( 'Autoshop Reviews.', 'wakreviews' ),
			'class'                   => 'icon-wpb-information-white',
			'icon'                    => 'icon-wpb-information-white',
			'category'                => 'WAK',
			'params'                  => array(
				array(
					'type'        => 'textfield',
					'holder'      => 'div',
					'heading'     => __( 'Title', 'wakreviews' ),
					'param_name'  => 'title',
					'value'       => '',
					'description' => __( 'Optional title to show.', 'wakreviews' )
				)
			),
			'custom_markup'           => ''
		) );

	}
endif;

?>