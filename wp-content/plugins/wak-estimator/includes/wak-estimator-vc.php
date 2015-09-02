<?php
// No dirrect access
if ( ! defined( 'WAK_ESTIMATOR_VER' ) ) exit;

/**
 * Map Shortcodes in VC
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_vc_map_shortcodes' ) ) :
	function wak_estimator_vc_map_shortcodes() {

		// wak_estimator
		vc_map( array(
			'name'                    => __( 'WAK Estimator', 'wakestimator' ),
			'base'                    => 'wak_estimator',
			'description'             => __( 'Ajax driven estimator.', 'wakestimator' ),
			'class'                   => 'icon-wpb-information-white',
			'icon'                    => 'icon-wpb-information-white',
			'category'                => 'WAK',
			'params'                  => array(
				array(
					'type'        => 'textfield',
					'holder'      => 'div',
					'heading'     => __( 'Title', 'wakestimator' ),
					'param_name'  => 'title',
					'value'       => '',
					'description' => __( 'Optional title to show.', 'wakestimator' )
				)
			),
			'custom_markup'           => ''
		) );

	}
endif;

?>