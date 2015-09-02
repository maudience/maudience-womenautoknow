<?php
// No dirrect access
if ( ! defined( 'WAK_ESTIMATOR_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_get_car_makers' ) ) :
	function wak_estimator_get_car_makers() {

		return array(
			1  => 'Acura',
			2  => 'Audi',
			3  => 'BMW',
			4  => 'Buick',
			5  => 'Cadillac',
			6  => 'Chevrolet',
			7  => 'Chrysler',
			8  => 'Dodge',
			9  => 'FIAT',
			10 => 'Ford',
			11 => 'GMC',
			12 => 'Geo',
			13 => 'Honda',
			14 => 'Hummer',
			15 => 'Hyundai',
			16 => 'Infiniti',
			17 => 'Isuzu',
			18 => 'Jaguar',
			19 => 'Jeep',
			20 => 'Kia',
			21 => 'Land Rover',
			22 => 'Lexus',
			23 => 'Lincoln',
			24 => 'Mazda',
			25 => 'Mercedes-Benz',
			26 => 'Mercury',
			27 => 'Mini',
			28 => 'Mitsubishi',
			29 => 'Nissan',
			30 => 'Oldsmobile',
			31 => 'Plymouth',
			32 => 'Pontiac',
			33 => 'Porsche',
			34 => 'Ram',
			35 => 'Saab',
			36 => 'Saturn',
			37 => 'Scion',
			38 => 'Smart',
			39 => 'Subaru',
			40 => 'Suzuki',
			41 => 'Toyota',
			42 => 'Volkswagen',
			43 => 'Volvo'
		);

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_make_dropdown' ) ) :
	function wak_estimator_make_dropdown( $name = '', $id = '', $first = '', $selected = '' ) {

		$options = wak_estimator_get_car_makers();

		$output = '<select class="form-control" name="' . $name . '" id="' . $id . '">';

		if ( $first != '' )
			$output .= '<option value="">' . $first . '</option>';

		foreach ( $options as $value => $label ) {

			$output .= '<option value="' . $value . '"';
			if ( $selected == $value ) $output .= ' selected="selected"';
			$output .= '>' . $label . '</option>';

		}

		$output .= '</select>';

		return $output;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_year_dropdown' ) ) :
	function wak_estimator_year_dropdown( $name = '', $id = '', $first = '', $selected = '' ) {

		$options = array( 2000, 2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015 );

		$output = '<select class="form-control" disabled="disabled" name="' . $name . '" id="' . $id . '">';

		if ( $first != '' )
			$output .= '<option value="">' . $first . '</option>';

		foreach ( $options as $value ) {

			$output .= '<option value="' . $value . '"';
			if ( $selected == $value ) $output .= ' selected="selected"';
			$output .= '>' . $value . '</option>';

		}

		$output .= '</select>';

		return $output;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_model_dropdown' ) ) :
	function wak_estimator_model_dropdown( $name = '', $id = '', $first = '', $selected = '' ) {

		$options = array( '120' => '120' );

		$output = '<select class="form-control" disabled="disabled" name="' . $name . '" id="' . $id . '">';

		if ( $first != '' )
			$output .= '<option value="">' . $first . '</option>';

		foreach ( $options as $value => $label ) {

			$output .= '<option value="' . $value . '"';
			if ( $selected == $value ) $output .= ' selected="selected"';
			$output .= '>' . $label . '</option>';

		}

		$output .= '</select>';

		return $output;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_service_dropdown' ) ) :
	function wak_estimator_service_dropdown( $name = '', $id = '', $first = '', $selected = '' ) {

		$options = array(
			'part_cost' => __( 'Average Part Cost', '' ),
			'labor'     => __( 'Average Labor Cost', '' )
		);

		$output = '<select class="form-control" name="' . $name . '" id="' . $id . '">';

		if ( $first != '' )
			$output .= '<option value="">' . $first . '</option>';

		foreach ( $options as $value => $label ) {

			$output .= '<option value="' . $value . '"';
			if ( $selected == $value ) $output .= ' selected="selected"';
			$output .= '>' . $label . '</option>';

		}

		$output .= '</select>';

		return $output;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'get_service_estimate_by_state' ) ) :
	function get_service_estimate_by_state( $state, $select = 'part_cost' ) {

		if ( ! in_array( $select, array( 'part_cost', 'labor' ) ) ) return 0;

		global $wpdb;

		$table = $wpdb->prefix . 'wak_estimator';
		return $wpdb->get_var( $wpdb->prepare( "
			SELECT {$select} 
			FROM {$table} 
			WHERE state = %s;", $state ) );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'get_service_estimate_by_state_user' ) ) :
	function get_service_estimate_by_state_user( $state, $select = 'part_cost' ) {

		if ( ! in_array( $select, array( 'part_cost', 'labor' ) ) ) return 0;

		global $wpdb;

		$table = $wpdb->prefix . 'wak_estimator_user';
		return $wpdb->get_var( $wpdb->prepare( "
			SELECT AVG( {$select} ) 
			FROM {$table} 
			WHERE state = %s;", $state ) );

	}
endif;

?>