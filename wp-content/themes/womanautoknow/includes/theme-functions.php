<?php

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_theme_top_navigation' ) ) :
	function wak_theme_top_navigation() {

		$locations  = get_nav_menu_locations();

		if ( is_user_logged_in() ) {
			$location = 'primary';
			$id = 'wak-members-nav';
		}
		else {
			$location = 'secondary';
			$id = 'wak-visitors-nav';
		}

		wp_nav_menu( array(
			'theme_location' => $location,
			'container'      => 'nav',
			'container_id'   => $id
		) );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_search_autoshops' ) ) :
	function wak_search_autoshops() {

		global $wak_is_search, $wp_query;

		$wak_is_search = false;
		if ( empty( $_POST ) || ! isset( $_POST['name'] ) ) return;

		$args = $meta_query = $orderby = array();

		$address1 = sanitize_text_field( $_POST['address1'] );
		if ( strlen( $address1 ) > 0 )
			$meta_query[] = array( 'key' => 'address1', 'value' => $address1, 'compare' => 'LIKE' );

		$city = sanitize_text_field( $_POST['city'] );
		if ( strlen( $city ) > 0 )
			$meta_query[] = array( 'key' => 'city', 'value' => $city, 'compare' => 'LIKE' );

		$zip = sanitize_text_field( $_POST['zip'] );
		if ( strlen( $zip ) > 0 )
			$meta_query[] = array( 'key' => 'zip', 'value' => $zip, 'compare' => 'LIKE' );

		$state = sanitize_text_field( $_POST['state'] );
		if ( strlen( $state ) > 0 )
			$meta_query[] = array( 'key' => 'state', 'value' => $state, 'compare' => 'LIKE' );

		if ( ! empty( $meta_query ) )
			$args['meta_query'] = $meta_query;

		

		$args = array_merge( $wp_query->query_vars, $args );

		query_posts( $args );

		echo '<h1 class="text-center">Search Results</h1>';

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_reset_query' ) ) :
	function wak_reset_query() {

		global $wak_is_search;

		if ( $wak_is_search === true )
			wp_reset_query();

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_theme_autoshops_count' ) ) :
	function wak_theme_autoshops_count() {

		$count = get_option( 'wak_autoshop_count', false );
		if ( $count === false ) {

			global $wpdb;

			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'autoshops' AND post_status = 'publish';" );
			if ( $count === NULL )
				$count = 0;

			update_option( 'wak_autoshop_count', $count );

		}

		return $count;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_theme_driver_count' ) ) :
	function wak_theme_driver_count() {

		$count = count_users();

		return $count['avail_roles']['subscriber'];

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_orderby_dropdown' ) ) :
	function wak_orderby_dropdown( $name = '', $id = '', $selected = '' ) {

		$options = array(
			'highest-rated' => 'Sort by highest rating',
			'alphabetical'  => 'Sort Alphabetical'
		);

		$output = '<select name="' . $name . '" id="' . $id . '" class="form-control">';
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


?>