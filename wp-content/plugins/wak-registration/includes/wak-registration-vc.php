<?php
// No dirrect access
if ( ! defined( 'WAK_REGISTER_VER' ) ) exit;

/**
 * Map Shortcodes in VC
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_vc_map_shortcodes' ) ) :
	function wak_registration_vc_map_shortcodes() {

		// wak_registration
		vc_map( array(
			'name'                    => __( 'WAK Registration', 'wakregister' ),
			'base'                    => 'wak_registration',
			'description'             => __( 'Handles member and auto shop signups.', 'wakregister' ),
			'class'                   => 'icon-wpb-information-white',
			'icon'                    => 'icon-wpb-information-white',
			'category'                => 'WAK',
			'params'                  => array(
				array(
					'type'        => 'textfield',
					'holder'      => 'div',
					'heading'     => __( 'Title', 'wakregister' ),
					'param_name'  => 'title',
					'value'       => '',
					'description' => __( 'Optional title to show.', 'wakregister' )
				)
			),
			'custom_markup'           => ''
		) );

	}
endif;

?>