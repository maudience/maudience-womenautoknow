<?php
// No dirrect access
if ( ! defined( 'WAK_AUTOSHOPS_VER' ) ) exit;

/**
 * AJAX: Load Add Autoshop
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_load_new_autoshop_form' ) ) :
	function wak_ajax_load_new_autoshop_form() {

		// Security
		check_ajax_referer( 'wak-add-new-autoshop', 'token' );

		wak_new_autoshop_form();
		die;

	}
endif;

/**
 * AJAX: Submit Autoshop
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_submit_autoshop' ) ) :
	function wak_ajax_submit_autoshop() {

		if ( ! is_user_logged_in() ) die( 0 );

		// Get the form
		parse_str( $_POST['form'], $post );
		unset( $_POST );

		$data = wp_parse_args( $post['wak_new_autoshop'], array(
			'name'           => NULL,
			'user_id'        => NULL,
			'token'          => '',
			'address1'       => '',
			'city'           => '',
			'zip'            => '',
			'state'          => '',
			'contact_person' => '',
			'phone'          => '',
			'facebook'       => '',
			'website'        => '',
			'twitter'        => '',
			'oh'             => array(
				'monday'         => array(
					'from' => '',
					'to'   => ''
				),
				'tuesday'        => array(
					'from' => '',
					'to'   => ''
				),
				'wednesday'      => array(
					'from' => '',
					'to'   => ''
				),
				'thursday'       => array(
					'from' => '',
					'to'   => ''
				),
				'friday'         => array(
					'from' => '',
					'to'   => ''
				),
				'saturday'       => array(
					'from' => '',
					'to'   => ''
				),
				'sunday'         => array(
					'from' => '',
					'to'   => ''
				)
			)
		) );

		// Security
		if ( ! wp_verify_nonce( $data['token'], 'submit-new-wak-autoshop' . $data['user_id'] ) ) die( -1 );

		$user_id     = get_current_user_id();

		if ( $user_id != $data['user_id'] ) die( -1 );

		$now         = current_time( 'timestamp' );
		$prefs       = wak_autoshops_plugin_settings();

		$name = sanitize_text_field( $data['name'] );

		if ( $data['name'] === NULL || strlen( $name ) < 2 ) {

			echo '<div class="alert alert-warning">You must provide an auto shop name.</div>';
			wak_new_autoshop_form( $data );
			die;

		}

		unset( $data['token'] );
		unset( $data['user_id'] );

		$clean = array();
		foreach ( $data as $key => $value ) {

			if ( $key != 'oh' ) {
				$key = sanitize_text_field( $key );
				if ( $key != '' )
					$clean[ $key ] = sanitize_text_field( $value );
			}
			else {
				$clean_oh = array();
				foreach ( $value as $day => $hours ) {
					$clean_day = sanitize_key( $day );
					$clean_oh[ $clean_day ] = array(
						'from' => sanitize_text_field( $hours['from'] ),
						'to'   => sanitize_text_field( $hours['to'] )
					);
				}
				$clean['oh'] = $clean_oh;
			}
		}
		$data = $clean;

		global $wpdb;

		$check = $wpdb->get_row( $wpdb->prepare( "
			SELECT * 
			FROM {$wpdb->posts} 
			WHERE ( post_title LIKE %s OR post_title = %s )
			AND post_type = 'autoshops';", $name, $name ) );

		if ( isset( $check->id ) ) {

			if ( $check->post_status == 'pending' )
				die( '<div class="alert alert-info">' . __( 'An auto shop with this name has already been submitted and is currently pending approval. Please add a different auto shop.', '' ) . '</div>' );

			else
				die( '<div class="alert alert-danger">' . __( 'This auto shop already exists.', '' ) . '</div>' );

		}

		$post_id = wp_insert_post( array(
			'post_title'  => $name,
			'post_type'   => 'autoshops',
			'post_status' => 'pending'
		) );

		if ( $post_id === NULL || is_wp_error( $post_id ) ) {

			echo '<div class="alert alert-warning">Failed to add auto shop. Please try again later.</div>';
			wak_new_autoshop_form( $clean );
			die;

		}

		else {

			add_post_meta( $post_id, 'pledged', 0, true );
			add_post_meta( $post_id, 'premium', 0, true );
			add_post_meta( $post_id, 'added_by', $user_id, true );

			foreach ( $data as $key => $value ) {
				add_post_meta( $post_id, $key, $value, true );
			}

			echo '<div class="alert alert-success">Thank you! Your auto shop has been submitted and is awaiting moderation.</div>';
			echo '<p class="text-center"><button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">Close</button></p>';

		}

		die;

	}
endif;

/**
 * AJAX: Load Autoshop Editor
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_edit_autoshop' ) ) :
	function wak_ajax_edit_autoshop() {

		// Security
		check_ajax_referer( 'wak-edit-autoshop', 'token' );

		$post_id  = absint( $_POST['shopid'] );
		$post     = get_post( $post_id );
		$owner_id = wak_get_autoshop_owner( $post_id );
		if ( ! isset( $post->ID ) || ( ! current_user_can( 'moderate_comments' ) && $owner_id != $data['user_id'] ) ) die( -1 );

		$data = array(
			'name'           => $post->post_title,
			'post_id'        => $post_id,
			'user_id'        => get_current_user_id(),
			'address1'       => get_post_meta( $post_id, 'address1', true ),
			'city'           => get_post_meta( $post_id, 'city', true ),
			'zip'            => get_post_meta( $post_id, 'zip', true ),
			'state'          => get_post_meta( $post_id, 'state', true ),
			'contact_person' => get_post_meta( $post_id, 'contact_person', true ),
			'phone'          => get_post_meta( $post_id, 'phone', true ),
			'facebook'       => get_post_meta( $post_id, 'facebook', true ),
			'website'        => get_post_meta( $post_id, 'website', true ),
			'twitter'        => get_post_meta( $post_id, 'twitter', true ),
			'oh'             => get_post_meta( $post_id, 'oh', true )
		);

		wak_new_autoshop_form( $data );
		die;

	}
endif;

/**
 * AJAX: Update Autoshop
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_update_autoshop' ) ) :
	function wak_ajax_update_autoshop() {

		// Get the form
		parse_str( $_POST['form'], $post );

		$data = wp_parse_args( $post['wak_new_autoshop'], array(
			'name'           => NULL,
			'user_id'        => NULL,
			'token'          => '',
			'address1'       => '',
			'city'           => '',
			'zip'            => '',
			'state'          => '',
			'contact_person' => '',
			'phone'          => '',
			'facebook'       => '',
			'website'        => '',
			'twitter'        => '',
			'oh'             => array(
				'monday'         => array(
					'from' => '',
					'to'   => ''
				),
				'tuesday'        => array(
					'from' => '',
					'to'   => ''
				),
				'wednesday'      => array(
					'from' => '',
					'to'   => ''
				),
				'thursday'       => array(
					'from' => '',
					'to'   => ''
				),
				'friday'         => array(
					'from' => '',
					'to'   => ''
				),
				'saturday'       => array(
					'from' => '',
					'to'   => ''
				),
				'sunday'         => array(
					'from' => '',
					'to'   => ''
				)
			)
		) );

		// Security
		if ( ! wp_verify_nonce( $data['token'], 'submit-new-wak-autoshop' . $data['user_id'] ) ) die( -1 );

		$post_id  = absint( $_POST['shopid'] );
		$post     = get_post( $post_id );
		$owner_id = wak_get_autoshop_owner( $post_id );
		if ( ! isset( $post->ID ) || get_post_status( $post_id ) != 'publish' || ( ! current_user_can( 'moderate_comments' ) && $owner_id != $data['user_id'] ) ) die( -1 );

		unset( $data['token'] );
		unset( $data['user_id'] );

		$clean = array();
		foreach ( $data as $key => $value ) {

			if ( $key != 'oh' ) {
				$key = sanitize_text_field( $key );
				if ( $key != '' )
					$clean[ $key ] = sanitize_text_field( $value );
			}
			else {
				$clean_oh = array();
				foreach ( $value as $day => $hours ) {
					$clean_day = sanitize_key( $day );
					$clean_oh[ $clean_day ] = array(
						'from' => sanitize_text_field( $hours['from'] ),
						'to'   => sanitize_text_field( $hours['to'] )
					);
				}
				$clean['oh'] = $clean_oh;
			}
		}
		$data = $clean;

		$name = sanitize_text_field( $data['name'] );

		if ( $name != $post->post_title )
			$post_id = wp_update_post( array(
				'post_title'  => $name,
				'post_type'   => 'autoshops',
				'post_status' => 'pending'
			) );

		foreach ( $data as $key => $value ) {
			add_post_meta( $post_id, $key, $value, true );
		}

		echo '<div class="alert alert-success">Autoshop Updated</div>';
		echo '<p class="text-center"><button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">Close</button></p>';

		die;

	}
endif;

/**
 * AJAX: Load more Auto Shops
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_ajax_load_more_autoshops' ) ) :
	function wak_ajax_load_more_autoshops() {

		// Security
		check_ajax_referer( 'wak-more-autoshops', 'token' );

		// Get the form
		parse_str( $_POST['form'], $post );

		$post['paged'] = absint( $_POST['page'] );

		$autoshops = new WAK_Autoshops( $post );
		
		$autoshops->query();

		if ( empty( $autoshops->results ) ) die( 0 );

		//echo '<div class="row"><div class="col-md-12"><pre>' . print_r( $autoshops->request, true ) . '</pre></div></div>';

		echo '<div class="row">';

		$counter = 0;
		$states  = wak_get_states();

		foreach ( $autoshops->results as $autoshop ) {

			$post_url = get_the_permalink( $autoshop->ID );
			$state    = get_post_meta( $autoshop->ID, 'state', true );

			if ( $counter != 0 && $counter % 2 == 0 )
				echo '<div class="row">' . "\n";

			echo '<div class="col-md-6 col-sm-6 col-xs-12">' . "\n";

			if ( autoshop_has_pledged( $autoshop->ID ) || autoshop_is_premium( $autoshop->ID ) ) {
				$post_class = 'autoshops pledged';
				$address    = get_post_meta( $autoshop->ID, 'address1', true );
				$address    .= ', ' . get_post_meta( $autoshop->ID, 'city', true );
				$address    .= ', ' . get_post_meta( $autoshop->ID, 'zip', true );
				$address    .= ' ' . $states[ $state ];
				$address    = esc_attr( $address );
			}
			else {
				$post_class = 'autoshops';
				$address    = get_post_meta( $autoshop->ID, 'city', true );

				if ( isset( $states[ $state ] ) )
					$address .= ' ' . $states[ $state ];
				else
					$address .= ' ' . $state;

				$address    = esc_attr( $address );
			}

?>
		<div id="autoshop-<?php echo $autoshop->ID; ?>" class="<?php echo $post_class; ?>">
			<?php wak_display_autoshop_rating( $autoshop->ID ); ?>
			<h4><a href="<?php echo $post_url; ?>"><?php echo esc_attr( $autoshop->post_title ); ?></a><small><?php echo $address; ?></small></h4>
			<p class="autoshop-action-links"><?php echo get_review_actions( $autoshop->ID ); ?></p>
		</div>
<?php

			echo '</div>' . "\n";

			if ( $counter != 0 && $counter % 2 != 0 )
				echo '</div>' . "\n";

			$counter ++;

		}

		if ( $counter % 2 != 0 )
			echo '<div class="col-md-6 col-sm-6 col-xs-12 text-center"><button id="wak-add-new-autoshop-button" class="btn btn-default">Add Auto Shop</button></div>' . "\n";

		echo '</div>';

		die;

	}
endif;

?>