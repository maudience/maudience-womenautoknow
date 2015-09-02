<?php
// No dirrect access
if ( ! defined( 'WAK_AUTOSHOPS_VER' ) ) exit;

/**
 * AJAX: New Review
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_new_review' ) ) :
	function wak_ajax_new_review() {

		// Security
		check_ajax_referer( 'wak-reviews-new', 'token' );

		if ( ! is_user_logged_in() ) die( 0 );

		$autoshop_id = absint( $_POST['aid'] );
		$user_id     = get_current_user_id();

		$now         = current_time( 'timestamp' );
		$limit       = $now - 300;
		$autoshop    = get_post( $autoshop_id );

		if ( ! isset( $autoshop->ID ) )
			die( '<p class="text-danger text-center">' . __( 'Could not locate the auto shop. Please reload this page and try again.', '' ) . '</p>' );

		global $wpdb, $wak_review_db;

		$check = $wpdb->get_row( $wpdb->prepare( "
			SELECT * 
			FROM {$wak_review_db} 
			WHERE user_id = %d 
			AND autoshop_id = %d;", $user_id, $autoshop_id ) );

		if ( isset( $check->id ) ) {

			if ( $check->status == 0 )
				die( '<div class="alert alert-info">' . __( 'Your review is pending review by WAK Staff. It will become visible as soon as it has been approved.', '' ) . '</div>' );

			else
				die( '<div class="alert alert-danger">' . __( 'You have already left a review for this auto shop.', '' ) . '</div>' );

		}

		$user = get_userdata( $user_id );

		wak_review_submit_form( array(
			'autoshop_id'  => $autoshop_id,
			'user_id'      => $user_id,
			'post_title'   => $autoshop->post_title,
			'display_name' => $user->display_name
		) );

		die;

	}
endif;

/**
 * AJAX: Submit Review
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_submit_review' ) ) :
	function wak_ajax_submit_review() {

		if ( ! is_user_logged_in() ) die( 0 );

		// Get the form
		parse_str( $_POST['form'], $post );
		unset( $_POST );

		$data = wp_parse_args( $post['wak_new_review'], array(
			'autoshop_id'  => NULL,
			'user_id'      => NULL,
			'token'        => '',
			'is_pro'       => 0,
			'is_comf'      => 0,
			'will_return'  => 0,
			'recommended'  => 0,
			'wheels'       => 0,
			'review'       => '',
			'edit'         => 0
		) );

		// Security
		if ( ! wp_verify_nonce( $data['token'], 'submit-new-wak-review' . $data['user_id'] . $data['autoshop_id'] ) ) die( -1 );

		$autoshop_id = absint( $data['autoshop_id'] );
		$user_id     = get_current_user_id();

		if ( $user_id != $data['user_id'] ) die( -1 );

		$now         = current_time( 'timestamp' );
		$limit       = $now - 300;
		$autoshop    = get_post( $autoshop_id );
		$prefs       = wak_autoshops_plugin_settings();

		if ( ! isset( $autoshop->ID ) )
			die( '<p class="text-danger text-center">' . __( 'Could not locate the auto shop. Please reload this page and try again.', '' ) . '</p>' );

		global $wpdb, $wak_review_db;

		$check = $wpdb->get_row( $wpdb->prepare( "
			SELECT * 
			FROM {$wak_review_db} 
			WHERE user_id = %d 
			AND autoshop_id = %d;", $user_id, $autoshop_id ) );

		if ( isset( $check->id ) ) {

			if ( $check->status == 0 )
				die( '<div class="alert alert-info">' . __( 'Your review is pending review by WAK Staff. It will become visible as soon as it has been approved.', '' ) . '</div>' );

			else
				die( '<div class="alert alert-danger">' . __( 'You have already left a review for this auto shop.', '' ) . '</div>' );

		}

		$user = get_userdata( $user_id );

		$data['post_title']   = $autoshop->post_title;
		$data['display_name'] = $user->display_name;

		$wheels = absint( $data['wheels'] );
		if ( $wheels == 0 ) {

			echo '<div class="alert alert-warning">' . __( 'You can not give zero wheels. Please select at least one.', '' ) . '</div>';

			wak_review_submit_form( $data );
			die;

		}

		$review = sanitize_text_field( $data['review'] );
		if ( strlen( $review ) < 10 ) {

			echo '<div class="alert alert-warning">' . __( 'Your review is too short. Please try again.', '' ) . '</div>';

			wak_review_submit_form( $data );
			die;

		}

		$status = 0;
		if ( $prefs['review_mod'] == 2 && wak_count_users_reviews( $user_id ) >= 1 )
			$status = 1;
		elseif ( $prefs['review_mod'] == 3 )
			$status = 1;

		$wpdb->insert(
			$wak_review_db,
			array(
				'status'      => $status,
				'autoshop_id' => $autoshop_id,
				'user_id'     => $user_id,
				'time'        => $now,
				'is_pro'      => $data['is_pro'],
				'is_comf'     => $data['is_comf'],
				'will_return' => $data['will_return'],
				'recommended' => $data['recommended'],
				'wheels'      => $wheels,
				'review'      => $review
			),
			array( '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s' )
		);

		// Delete rating - will trigger a new count
		delete_post_meta( $autoshop_id, 'rating' );
		delete_post_meta( $autoshop_id, 'total_reviews' );

		if ( $status == 0 )
			echo '<div class="alert alert-success">' . __( 'Your review has been successfully submitted. Once reviewed by our staff, it will become visible.', '' ) . '</div>';

		else
			echo '<div class="alert alert-success">' . __( 'Thank you! Your review has been successfully submitted.', '' ) . '</div>';

		die;

	}
endif;

?>