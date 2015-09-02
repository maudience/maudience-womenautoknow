<?php
// No dirrect access
if ( ! defined( 'WAK_AUTOSHOPS_VER' ) ) exit;

/**
 * Parse Reviews
 * Parses the database search results by assigning review
 * edits to the appropriate review.
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_parse_reviews' ) ) :
	function wak_parse_reviews( $query = array() ) {

		$reviews = $temp = array();
		if ( ! empty( $query ) ) {

			// Insert rows into $reviews
			foreach ( $query as $row ) {

				if ( $row->edit == 0 ) {

					$reviews[ $row->id ] = $row;
					$reviews[ $row->id ]->edits = array();

				}
				else {

					if ( isset( $reviews[ $row->edit ] ) )
						$reviews[ $row->edit ]->edits[ $row->time ] = $row;

					else
						$temp[ $row->edit ] = $row;

				}

			}

			// Insert temp rows
			if ( ! empty( $temp ) ) {

				foreach ( $temp as $row_id => $row ) {

					if ( ! isset( $reviews[ $row->edit ] ) ) continue;

					$reviews[ $row->edit ]->edits[ $row->time ] = $row;

				}

			}

			// Sort review edit history
			if ( ! empty( $reviews ) ) {

				$sorted = array();
				foreach ( $reviews as $id => $review ) {

					if ( ! empty( $review->edits ) )
						krsort( $review->edits, SORT_NUMERIC );

					$review->edit_count = count( $review->edits );

					$sorted[ $id ] = $review;

				}
				$reviews = $sorted;

			}

		}

		return $reviews;

	}
endif;

/**
 * Get Autoshop Reviews
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_autoshop_reviews' ) ) :
	function wak_get_autoshop_reviews( $autoshop_id = NULL, $status = 1, $number = 5, $pledged = false ) {

		global $wpdb, $wak_review_db;

		if ( $number === NULL )
			$number = '';
		else
			$number = 'LIMIT 0,' . absint( $number );

		if ( $autoshop_id !== NULL )
			$query = $wpdb->get_results( $wpdb->prepare( "
				SELECT * 
				FROM {$wak_review_db} 
				WHERE autoshop_id = %d 
				AND review != '' 
				AND status = %d 
				ORDER BY time DESC 
				{$number};", $autoshop_id, $status ) );

		else
			$query = $wpdb->get_results( $wpdb->prepare( "
				SELECT * 
				FROM {$wak_review_db} reviews 
				INNER JOIN {$wpdb->postmeta} pl ON ( reviews.autoshop_id = pl.post_id AND pl.meta_key = 'pledged' )
				WHERE reviews.status = %d 
				AND reviews.review != '' 
				AND pl.meta_value = 1 
				ORDER BY reviews.time DESC 
				{$number};", $status ) );

		return wak_parse_reviews( $query );

		$pledged = array();
		if ( ! empty( $query ) ) {
			foreach ( $query as $id => $row ) {
				if ( ! autoshop_has_pledged( $row->autoshop_id ) ) continue;
				$pledged[ $id ] = $row;
			}
		}

		return $pledged;

	}
endif;

/**
 * Get Autoshop Rating
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_autoshop_rating' ) ) :
	function wak_get_autoshop_rating( $autoshop_id = NULL, $new = false ) {

		$rating  = get_post_meta( $autoshop_id, 'rating', true );
		$pledged = autoshop_has_pledged( $autoshop_id );

		if ( $rating == '' || $new ) {

			global $wpdb, $wak_review_db;

			$query = $wpdb->get_var( $wpdb->prepare( "
				SELECT AVG( wheels ) 
				FROM {$wak_review_db} 
				WHERE autoshop_id = %d 
				AND edit = 0 
				AND status = 1;", $autoshop_id ) );

			if ( $query !== NULL )
				$rating = (float) $query;

			else {
				if ( $pledged )
					$rating = 5.00;
				else
					$rating = 0.00;
			}

			update_post_meta( $autoshop_id, 'rating', $rating );

		}

		if ( $rating == '' && $pledged )
			$rating = 5.00;
		elseif ( $rating == '' )
			$rating = 0.00;

		return number_format( (float) $rating, 1, '.', '' );

	}
endif;

/**
 * Display Autoshop Rating
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_display_autoshop_rating' ) ) :
	function wak_display_autoshop_rating( $autoshop_id = NULL ) {

		$pledged = autoshop_has_pledged( $autoshop_id );
		$rating  = wak_get_autoshop_rating( $autoshop_id );
		$premium = autoshop_is_premium( $autoshop_id );

		if ( $rating > 0.00 ) {

			$image = absint( $rating );

			if ( $pledged )
				$image .= 'p';
			elseif ( $premium )
				$image .= 'n';
			else
				$image .= 's';

			$title = sprintf( 'Earned %s out of 5.', sprintf( _n( '1 Wheel', '%d Wheels', absint( $rating ), 'wakauto' ), absint( $rating ) ) );

		}
		else {
			$image = 'default';
			$title = 'Given no wheels.';
		}

		$image .= '.png';

		$reviews = wak_count_autoshop_reviews( $autoshop_id );

		$tooltip = '';
		if ( $reviews == 0 && $pledged )
			$tooltip = 'Based on a single review by Women Auto Know';

		elseif ( $reviews > 0 )
			$tooltip = sprintf( 'Based on %s', sprintf( _n( '1 driver review', '%d driver reviews', $reviews, '' ), $reviews ) );

		if ( $tooltip != '' )
			$tooltip = 'data-toggle="tooltip" data-placement="top" title="' . $tooltip . '"';

		echo '<div class="wak-autoshop-rating"><img ' . $tooltip . ' src="' . plugins_url( 'assets/images/' . $image, WAK_AUTOSHOPS ) . '" alt="' . $title . '" title="' . $title . '" /></div>';

	}
endif;

/**
 * Display Autoshop Rating
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_display_users_autoshop_rating' ) ) :
	function wak_display_users_autoshop_rating( $user_id = NULL, $autoshop_id = NULL ) {

		global $wpdb, $wak_review_db;

		$rating = $wpdb->get_var( $wpdb->prepare( "
			SELECT wheels 
			FROM {$wak_review_db} 
			WHERE user_id = %d 
			AND autoshop_id = %d 
			AND edit = 0 
			ORDER BY time DESC 
			LIMIT 0,1;", $user_id, $autoshop_id ) );

		if ( $rating !== NULL ) {

			$image = absint( $rating );

			if ( autoshop_has_pledged( $autoshop_id ) )
				$image .= 'p';
			elseif ( autoshop_is_premium( $autoshop_id ) )
				$image .= 'n';
			else
				$image .= 's';

			$title = sprintf( 'Given %s out of 5.', sprintf( _n( '1 Wheel', '%d Wheels', absint( $rating ), 'wakauto' ), absint( $rating ) ) );

		}
		else {

			$image = absint( $user_id );

			if ( autoshop_has_pledged( $autoshop_id ) )
				$image .= 'p';
			elseif ( autoshop_is_premium( $autoshop_id ) )
				$image .= 'n';
			else
				$image .= 's';
			
			$title = sprintf( 'Given %s out of 5.', sprintf( _n( '1 Wheel', '%d Wheels', absint( $user_id ), 'wakauto' ), absint( $user_id ) ) );

		}

		$image .= '.png';

		echo '<div class="wak-users-autoshop-rating"><img src="' . plugins_url( 'assets/images/' . $image, WAK_AUTOSHOPS ) . '" alt="' . $title . '" title="' . $title . '" /></div>';

	}
endif;

/**
 * Get Users Reviews
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_users_reviews' ) ) :
	function wak_get_users_reviews( $user_id = NULL, $status = 1, $number = 5 ) {

		global $wpdb, $wak_review_db;

		if ( $number === NULL )
			$number = '';
		else
			$number = 'LIMIT 0,' . absint( $number );

		$query = $wpdb->get_results( $wpdb->prepare( "
			SELECT * 
			FROM {$wak_review_db} 
			WHERE user_id = %d 
			AND status = %d 
			ORDER BY time DESC 
			{$number};", $user_id, $status ) );

		return wak_parse_reviews( $query );

	}
endif;

/**
 * User Has Rated
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_user_has_rated' ) ) :
	function wak_user_has_rated( $user_id = NULL, $autoshop_id = NULL ) {

		global $wpdb, $wak_review_db;

		$check = $wpdb->get_row( $wpdb->prepare( "
			SELECT * 
			FROM {$wak_review_db} 
			WHERE user_id = %d 
			AND autoshop_id = %d 
			AND edit = 0;", $user_id, $autoshop_id ) );

		$result = false;
		if ( isset( $check->id ) )
			$result = true;

		return $result;

	}
endif;

/**
 * Get Review
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_review' ) ) :
	function wak_get_review( $review_id = NULL ) {

		global $wpdb, $wak_review_db;

		return $wpdb->get_row( $wpdb->prepare( "
			SELECT * 
			FROM {$wak_review_db} 
			WHERE id = %d;", $review_id ) );

	}
endif;

/**
 * Get Users Average Rating
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_users_average_rating' ) ) :
	function wak_get_users_average_rating( $user_id = NULL ) {

		$ratings = get_user_meta( $user_id, 'avg_rating', true );

		if ( $ratings == '' ) {

			global $wpdb, $wak_review_db;

			$ratings = $wpdb->get_row( $wpdb->prepare( "
				SELECT AVG( wheels ) 
				FROM {$wak_review_db} 
				WHERE user_id = %d 
				AND edit = 0 
				AND status != 2;", $user_id ) );

			if ( $ratings === NULL )
				$ratings = 0;

			update_user_meta( $user_id, 'avg_rating', (float) $ratings );

		}

		return number_format( (float) $ratings, 1, '.', '' );

	}
endif;

/**
 * Add New Review
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_add_review' ) ) :
	function wak_add_review( $new_review = array() ) {

		$entry = shortcode_atts( array(
			'autoshop_id' => NULL,
			'user_id'     => NULL,
			'status'      => 0,
			'wheels'      => 0,
			'entry'       => '',
			'time'        => current_time( 'timestamp' ),
			'edit'        => 0
		), $new_review );

		if ( $entry['autoshop_id'] === NULL || $entry['user_id'] === NULL || strlen( $entry['entry'] ) == 0 ) return false;

		global $wpdb, $wak_review_db;

		$wpdb->insert(
			$wak_review_db,
			$entry,
			array( '%d', '%d', '%d', '%d', '%s', '%d', '%d' )
		);

		return $wpdb->insert_id;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_count_autoshop_reviews' ) ) :
	function wak_count_autoshop_reviews( $autoshop_id = NULL ) {

		$count = get_post_meta( $autoshop_id, 'total_reviews', true );

		if ( $count == '' ) {

			global $wpdb, $wak_review_db;

			$count = $wpdb->get_var( $wpdb->prepare( "
				SELECT COUNT(*) 
				FROM {$wak_review_db} 
				WHERE autoshop_id = %d 
				AND status = 1 
				AND edit = 0;", $autoshop_id ) );

			if ( $count === NULL ) $count = 0;

			update_post_meta( $autoshop_id, 'total_reviews', $count );

		}

		return $count;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_count_reviews_by_status' ) ) :
	function wak_count_reviews_by_status( $status = 0 ) {

		global $wpdb, $wak_review_db;

		if ( $status == -1 )
			$count = $wpdb->get_var( "
				SELECT COUNT(*) 
				FROM {$wak_review_db} 
				WHERE edit = 0;" );

		else
			$count = $wpdb->get_var( $wpdb->prepare( "
				SELECT COUNT(*) 
				FROM {$wak_review_db} 
				WHERE status = %d 
				AND edit = 0;", $status ) );

		if ( $count === NULL ) $count = 0;

		return $count;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_count_users_reviews' ) ) :
	function wak_count_users_reviews( $user_id = 0 ) {

		global $wpdb, $wak_review_db;

		$count = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*) 
			FROM {$wak_review_db} 
			WHERE status = 1 
			AND user_id = %d  
			AND edit = 0;", $user_id ) );

		if ( $count === NULL ) $count = 0;

		return $count;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_review_status' ) ) :
	function wak_get_review_status( $status = NULL ) {

		if ( $status == 0 )
			return __( 'Pending', '' );
		elseif ( $status == 1 )
			return __( 'Published', '' );
		elseif ( $status == 2 )
			return __( 'Flagged', '' );
		elseif ( $status == 3 )
			return __( 'SPAM', '' );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_display_autoshop_reviews' ) ) :
	function wak_display_autoshop_reviews( $autoshop_id = NULL, $number = 3, $show = true ) {

		$reviews = wak_get_autoshop_reviews( $autoshop_id, 1, $number );

		$total = count( $reviews );
		$temp  = $number;
		if ( $total < $number )
			$number = $total;

		if ( empty( $reviews ) ) {

?>
<p><?php _e( 'This autoshop has not received any reviews yet.', '' ); ?></p>
<?php

			echo get_review_actions( $autoshop_id, NULL, $show );

			return;

		}

		if ( ! is_user_logged_in() ) {

			$prefs = wak_autoshops_plugin_settings();

			if ( $prefs['visitors_viewing_review'] == '' )
				$prefs['visitors_viewing_review'] = '<p>Signup to view reviews for this auto shop.</p>';

			echo wpautop( wptexturize( $prefs['visitors_viewing_review'] ) );

			return;

		}

?>
<div id="wak-review-list-wrapper">
	<div class="review-list">
<?php

		$counter     = 0;
		$date_format = get_option( 'date_format' );
		foreach ( $reviews as $review ) {

			$by = get_userdata( $review->user_id );
			$author = 'n/a';
			if ( isset( $by->ID ) )
				$author = '<a href="' . wak_theme_get_profile_url( $by ) . '">' . $by->display_name . '</a>';

?>
		<div class="item<?php if ( $counter == 0 ) echo ' active'; ?>">
			<blockquote>
				<p><?php echo nl2br( $review->review ); ?></p>
				<?php wak_display_users_autoshop_rating( $review->wheels, $autoshop_id ); ?>
				<footer><?php printf( 'Posted %s by %s', date_i18n( $date_format, $review->time ), $author ); ?></footer>
			</blockquote>
		</div>
<?php

			$counter ++;

		}

?>
	</div>
<?php

	echo get_review_actions( $autoshop_id, NULL, $show );

?>
</div>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'get_review_actions' ) ) :
	function get_review_actions( $post_id = NULL, $user_id = NULL, $view = true, $classes = 'btn-default btn-xs' ) {

		if ( $post_id === NULL ) {

			global $post;

			if ( ! isset( $post->ID ) || $post->ID == 0 ) return;

		}
		else {

			$_post = get_post( (int) $post_id );
			if ( ! isset( $_post->ID ) ) return;
			$post = $_post;

		}

		if ( $user_id === NULL ) {

			if ( ! is_user_logged_in() ) return '<button type="button" class="btn ' . $classes . '" data-toggle="tooltip" data-placement="bottom" title="Sign on FREE to write review.">' . __( 'Add New Review', '' ) . '</button>';

			$user = wp_get_current_user();

		}
		else {

			$user = get_userdata( $user_id );
			if ( ! isset( $user->ID ) ) return '<button type="button" class="new-autoshop-review btn ' . $classes . '" disabled="disabled">' . __( 'Add New Review', '' ) . '</button>';

		}

		$options  = array();

		$is_owner = wak_is_autoshop_owner( $post->ID, $user->ID );
		$reviewed = wak_user_has_rated( $user->ID, $post->ID );
		$pledged  = autoshop_has_pledged( $post->ID );

		if ( $is_owner )
			$options[] = '<button type="button" data-backdrop="static" class="edit-my-autoshop btn btn-danger btn-xs" data-toggle="modal" data-target="#front-end-autoshop-edit" data-shop="' . $post->ID . '">' . __( 'Edit Auto Shop', '' ) . '</button>';

		if ( $view === true )
			$options[] = '<a href="' . get_permalink( $post->ID ) . '" class="btn ' . $classes . '">' . __( 'View Auto Shop and Reviews', '' ) . '</a>';

		if ( ! $is_owner && ! $reviewed )
			$options[] = '<button type="button" data-backdrop="static" class="new-autoshop-review btn btn-xs ' . $classes . '" data-toggle="modal" data-target="#add-new-wak-review" data-id="' . $post->ID . '" data-pledged="' . ( ( $pledged ) ? '1' : '0' ) . '">' . __( 'Add New Review', '' ) . '</button>';

		if ( current_user_can( 'edit_others_posts' ) )
			$options[] = '<a href="' . admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) . '" class="btn btn-default btn-xs">' . __( 'Admin Edit', '' ) . '</a>';

		$options = apply_filters( 'wak_autoshop_actions', $options, $post_id, $user_id, $is_owner, $pledged );

		return implode( ' ', $options );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_review_carousel' ) ) :
	function wak_review_carousel() {

		if ( isset( $_POST['name'] ) ) return;

		$prefs   = wak_autoshops_plugin_settings();
		$reviews = wak_get_autoshop_reviews( NULL, 1, $prefs['carousel_number'], true );

		$number = $prefs['carousel_number'];
		if ( ! empty( $reviews ) ) {

			if ( is_user_logged_in() && count( $reviews ) > 3 ) {

				$user_count = wak_count_users_reviews( get_current_user_id() );
				$max = ceil( $number / 3 );
				if ( $user_count == 0 )
					$every = ceil( $prefs['carousel_frequency'] / 2 );
				else
					$every = $prefs['carousel_frequency'];

				$_reviews = array();

				$counter = $added = 0;
				foreach ( $reviews as $review ) {

					$counter ++;

					if ( strlen( $prefs['carousel_call_to_action'] ) > 3 && $every > 0 && $counter % $every == 0 && $added <= $max ) {
						$entry = new stdClass();
						$entry->call_to_action = true;
						$_reviews[] = $entry;
						$added ++;
					}

					$_reviews[] = $review;

				}

				if ( $added < $max ) {
					$entry = new stdClass();
					$entry->call_to_action = true;
					$_reviews[] = $entry;
					$added ++;
				}

				$number  = $prefs['carousel_number'] + $added;
				$reviews = $_reviews;

			}

?>
<div class="row">
	<div class="col-md-12">
		<div id="carousel-autoshop-reviews" class="carousel slide" data-ride="carousel" data-interval="5000">
			<ol class="carousel-indicators" style="top:-35px !important;">
<?php

			for ( $i = 0; $i < $number; $i++ ) {

				$class = '';
				if ( $i == 0 ) $class = ' class="active"';

?>
				<li data-target="#carousel-autoshop-reviews" data-slide-to="<?php echo $i; ?>"<?php echo $class; ?>></li>
<?php

			}

?>
			</ol>

			<div class="carousel-inner" role="listbox">
<?php

			$counter     = 0;
			$date_format = get_option( 'date_format' );

			foreach ( $reviews as $row_id => $review ) {

				if ( isset( $review->call_to_action ) ) {

					$content = wpautop( wptexturize( $prefs['carousel_call_to_action'] ) );

?>
<div class="item<?php if ( $counter == 0 ) echo ' active'; ?>">
	<div class="jumbotron">
		<?php echo $content; ?>
	</div>
</div>
<?php

				}

				else {

					$by = get_userdata( $review->user_id );
					$author = 'n/a';
					if ( isset( $by->ID ) )
						$author = '<a href="' . wak_theme_get_profile_url( $by ) . '">' . $by->display_name . '</a>';

					$autoshop_url = get_permalink( $review->autoshop_id );

					$body = $review->review;
					if ( strlen( $body ) > $prefs['caoursel_review_length'] ) {
						$body = substr( $body, 0, $prefs['caoursel_review_length'] );
						$body .= '...';
					}

?>
				<div class="item<?php if ( $counter == 0 ) echo ' active'; ?><?php if ( autoshop_has_pledged( $review->autoshop_id ) ) echo ' pledged'; ?>">
					<h2><?php echo nl2br( $body ); ?></h2>
					<?php wak_display_users_autoshop_rating( $review->wheels, $review->autoshop_id ); ?>
					<p><?php printf( 'Posted %s by %s for %s', date_i18n( $date_format, $review->time ), $author, '<a href="' . $autoshop_url . '">' . get_the_title( $review->autoshop_id ) . '</a>' ); ?></p>
				</div>
<?php

				}

				$counter ++;

			}

?>
		</div>
	</div>
</div>
<?php

		}

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_review_submit_form' ) ) :
	function wak_review_submit_form( $data = array() ) {

		extract( wp_parse_args( $data, array(
			'autoshop_id'  => NULL,
			'user_id'      => NULL,
			'post_title'   => '',
			'display_name' => '',
			'is_pro'       => 0,
			'is_comf'      => 0,
			'will_return'  => 0,
			'recommended'  => 0,
			'wheels'       => 0,
			'review'       => ''
		) ) );

?>
<form id="new-review-for-<?php echo $autoshop_id; ?>" method="post" action="" style="padding: 0 24px;">

	<input type="hidden" name="wak_new_review[autoshop_id]" value="<?php echo $autoshop_id; ?>" />
	<input type="hidden" name="wak_new_review[user_id]" value="<?php echo $user_id; ?>" />
	<input type="hidden" name="wak_new_review[token]" value="<?php echo wp_create_nonce( 'submit-new-wak-review' . $user_id . $autoshop_id ); ?>" />

	<div class="row">
		<label class="col-sm-2 control-label">Auto Shop</label>
		<div class="col-sm-10"><?php echo get_the_title( $autoshop_id ); ?></div>
	</div>

	<div class="row">
		<label class="col-sm-2 control-label">Your Name</label>
		<div class="col-sm-10"><?php echo esc_attr( $display_name ); ?></div>
	</div>

	<div class="row">
		<label class="col-sm-2 control-label"></label>
		<div class="col-sm-10">
			<label class="checkbox-inline" for="wak-new-review-pro">
				<input type="checkbox" id="wak-new-review-pro" name="wak_new_review[is_pro]"<?php checked( $is_pro, 1 ); ?> value="1"> Is Professional
			</label>
			<label class="checkbox-inline" for="wak-new-review-nice">
				<input type="checkbox" id="wak-new-review-nice" name="wak_new_review[is_comf]"<?php checked( $is_comf, 1 ); ?> value="1"> Is Comfortable
			</label>
			<label class="checkbox-inline" for="wak-new-review-return">
				<input type="checkbox" id="wak-new-review-return" name="wak_new_review[will_return]"<?php checked( $will_return, 1 ); ?> value="1"> Will Return
			</label>
			<label class="checkbox-inline" for="wak-new-review-recommend" style="margin-left: 0 !important;">
				<input type="checkbox" id="wak-new-review-recommend" name="wak_new_review[recommended]"<?php checked( $recommended, 1 ); ?> value="1"> Would recommend to family and friends
			</label>
		</div>
	</div>

	<div class="row push-down">
		<label class="col-sm-2 control-label" for="wak-new-review-wheels">Rating</label>
		<div class="col-sm-10">
<?php

		$img = 's';
		if ( autoshop_has_pledged( $autoshop_id ) )
			$img = 'p';
		elseif ( autoshop_is_premium( $autoshop_id ) )
			$img = 'n';

?>
			<div class="inline-radio">
				<label for="wak-review-wheels-one">
					<img src="<?php echo plugins_url( 'assets/images/1' . $img . '.png', WAK_AUTOSHOPS ); ?>" alt="Bad" />
					<input type="radio" name="wak_new_review[wheels]" id="wak-review-wheels-one" value="1" /> Bad
				</label>
			</div>
			<div class="inline-radio">
				<label for="wak-review-wheels-two">
					<img src="<?php echo plugins_url( 'assets/images/2' . $img . '.png', WAK_AUTOSHOPS ); ?>" alt="Poor" />
					<input type="radio" name="wak_new_review[wheels]" id="wak-review-wheels-two" value="2" /> Poor
				</label>
			</div>
			<div class="inline-radio">
				<label for="wak-review-wheels-three">
					<img src="<?php echo plugins_url( 'assets/images/3' . $img . '.png', WAK_AUTOSHOPS ); ?>" alt="Average" />
					<input type="radio" name="wak_new_review[wheels]" id="wak-review-wheels-three" value="3" /> Average
				</label>
			</div>
			<div class="inline-radio">
				<label for="wak-review-wheels-four">
					<img src="<?php echo plugins_url( 'assets/images/4' . $img . '.png', WAK_AUTOSHOPS ); ?>" alt="Good" />
					<input type="radio" name="wak_new_review[wheels]" id="wak-review-wheels-four" value="4" /> Good
				</label>
			</div>
			<div class="inline-radio">
				<label for="wak-review-wheels-five">
					<img src="<?php echo plugins_url( 'assets/images/5' . $img . '.png', WAK_AUTOSHOPS ); ?>" alt="Excellent" />
					<input type="radio" name="wak_new_review[wheels]" id="wak-review-wheels-five" value="5" /> Excellent
				</label>
			</div>
		</div>
	</div>

	<div class="row push-down">
		<label class="col-sm-2 control-label" for="wak-new-review-review">Review</label>
		<div class="col-sm-10">
			<textarea name="wak_new_review[review]" id="wak-new-review-review" cols="30" rows="5" class="form-control"><?php echo esc_attr( $review ); ?></textarea>
		</div>
	</div>

	<div class="row">
		<label class="col-sm-2 control-label">&nbsp;</label>
		<div class="col-sm-10">
			<input type="submit" class="btn btn-danger pull-right" id="submit-new-review-button" value="<?php _e( 'Submit Review', '' ); ?>" />
		</div>
	</div>

</form>
<?php

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_review_status_dropdown' ) ) :
	function wak_review_status_dropdown( $name = '', $id = '', $selected = '' ) {

		$options = array(
			0 => 'Pending',
			1 => 'Published',
			2 => 'Flagged',
			3 => 'SPAM'
		);

		$output = '<select name="' . $name . '" id="' . $id . '">';
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
if ( ! function_exists( 'wak_process_review_admin_actions' ) ) :
	function wak_process_review_admin_actions() {

		if ( current_user_can( 'moderate_comments' ) && isset( $_GET['action'] ) && isset( $_GET['autoshop_id'] ) && strlen( $_GET['autoshop_id'] ) > 0 && in_array( $_GET['action'], array( 'publish', 'pending', 'flag', 'spam', 'delete' ) ) ) {

			$act      = sanitize_key( $_GET['action'] );
			$entry_id = absint( $_GET['autoshop_id'] );

			global $wpdb, $wak_review_db;

			$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_review_db} WHERE id = %d;", $entry_id ) );

			if ( isset( $entry->id ) ) {

				if ( in_array( $act, array( 'publish', 'pending', 'flag', 'spam' ) ) ) {

					$status = 0;
					if ( $act == 'publish' )
						$status = 1;

					elseif ( $act == 'flag' )
						$status = 2;

					elseif ( $act == 'spam' )
						$status = 3;

					$wpdb->update(
						$wak_review_db,
						array( 'status' => $status ),
						array( 'id' => $entry_id ),
						array( '%d' ),
						array( '%d' )
					);

					// Delete rating - will trigger a new count
					delete_post_meta( $entry->autoshop_id, 'rating' );
					delete_post_meta( $entry->autoshop_id, 'total_reviews' );

					$url = remove_query_arg( array( 'action', 'autoshop_id' ) );
					$url = add_query_arg( array( 'updated' => $status ), $url );
					wp_safe_redirect( $url );
					exit;

				}
				elseif ( $act == 'delete' ) {

					$wpdb->delete(
						$wak_review_db,
						array( 'id' => $entry_id ),
						array( '%d' )
					);

					// Delete rating - will trigger a new count
					delete_post_meta( $entry->autoshop_id, 'rating' );
					delete_post_meta( $entry->autoshop_id, 'total_reviews' );

					$url = remove_query_arg( array( 'action', 'autoshop_id' ) );
					$url = add_query_arg( array( 'deleted' => 1 ), $url );
					wp_safe_redirect( esc_url( $url ) );
					exit;

				}

			}

		}

		if ( current_user_can( 'moderate_comments' ) && isset( $_GET['action'] ) && $_GET['action'] != '-1' && isset( $_GET['reviews'] ) && ! empty( $_GET['reviews'] ) ) {

			global $wpdb, $wak_review_db;

			$act      = sanitize_key( $_GET['action'] );
			$reviews  = array();
			$done     = 0;

			foreach ( $_GET['reviews'] as $review_id ) {
				if ( $review_id == '' || $review_id == 0 ) continue;
				$reviews[] = absint( $review_id );
			}

			if ( ! empty( $reviews ) ) {

				if ( $act == 'delete' ) {

					foreach ( $reviews as $entry_id ) {

						$autoshop_id = $wpdb->get_var( $wpdb->prepare( "SELECT autoshop_id FROM {$wak_review_db} WHERE id = %d;", $entry_id ) );

						if ( $autoshop_id === NULL ) continue;

						$wpdb->delete(
							$wak_review_db,
							array( 'id' => $entry_id ),
							array( '%d' )
						);

						// Delete rating - will trigger a new count
						delete_post_meta( $autoshop_id, 'rating' );
						delete_post_meta( $autoshop_id, 'total_reviews' );

						$done++;

					}

					$url = remove_query_arg( array( 'action', 'reviews' ) );
					$url = add_query_arg( array( 'deleted' => 1, 'multi' => $done ), $url );
					wp_safe_redirect( esc_url( $url ) );
					exit;

				}

				else {

					$status = 0;
					if ( $act == 'publish' )
						$status = 1;

					elseif ( $act == 'flag' )
						$status = 2;

					elseif ( $act == 'spam' )
						$status = 3;

					foreach ( $reviews as $entry_id ) {

						$autoshop_id = $wpdb->get_var( $wpdb->prepare( "SELECT autoshop_id FROM {$wak_review_db} WHERE id = %d;", $entry_id ) );

						if ( $autoshop_id === NULL ) continue;

						$wpdb->update(
							$wak_review_db,
							array( 'status' => $status ),
							array( 'id' => $entry_id ),
							array( '%d' ),
							array( '%d' )
						);

						// Delete rating - will trigger a new count
						delete_post_meta( $autoshop_id, 'rating' );
						delete_post_meta( $autoshop_id, 'total_reviews' );

						$done++;

					}

					$url = remove_query_arg( array( 'action', 'reviews' ) );
					$url = add_query_arg( array( 'updated' => $status, 'multi' => $done ), $url );
					wp_safe_redirect( $url );
					exit;

				}

			}

		}

		if ( current_user_can( 'moderate_comments' ) && isset( $_POST['wak_review']['entry_id'] ) ) {

			$entry_id = absint( $_POST['wak_review']['entry_id'] );

			global $wpdb, $wak_review_db;

			$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_review_db} WHERE id = %d;", $entry_id ) );

			if ( isset( $entry->id ) ) {

				$new_data = wp_parse_args( $_POST['wak_review'], array(
					'status'      => $entry->status,
					'autoshop_id' => $entry->autoshop_id,
					'user_id'     => $entry->user_id,
					'time'        => $entry->time,
					'is_pro'      => $entry->is_pro,
					'is_comf'     => $entry->is_comf,
					'will_return' => $entry->will_return,
					'recommended' => $entry->recommended,
					'wheels'      => $entry->wheels,
					'review'      => $entry->review
				) );

				$wpdb->update(
					$wak_review_db,
					array(
						'status'      => absint( $new_data['status'] ),
						'autoshop_id' => absint( $new_data['autoshop_id'] ),
						'user_id'     => absint( $new_data['user_id'] ),
						'time'        => absint( $new_data['time'] ),
						'is_pro'      => absint( $new_data['is_pro'] ),
						'is_comf'     => absint( $new_data['is_comf'] ),
						'will_return' => absint( $new_data['will_return'] ),
						'recommended' => absint( $new_data['recommended'] ),
						'wheels'      => absint( $new_data['wheels'] ),
						'review'      => sanitize_text_field( $new_data['review'] )
					),
					array( 'id' => $entry_id ),
					array( '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s' ),
					array( '%d' )
				);

				// Delete rating - will trigger a new count
				delete_post_meta( $entry->autoshop_id, 'rating' );
				delete_post_meta( $entry->autoshop_id, 'total_reviews' );

				$url = remove_query_arg( array( 'action', 'autoshop_id' ) );
				$url = add_query_arg( array( 'edited' => 1 ), $url );
				wp_safe_redirect( $url );
				exit;

			}

		}

		if ( isset( $_REQUEST['wp_screen_options']['option'] ) && isset( $_REQUEST['wp_screen_options']['value'] ) ) {
			
			if ( $_REQUEST['wp_screen_options']['option'] == 'wak_reviews_per_page' ) {
				$value = absint( $_REQUEST['wp_screen_options']['value'] );
				update_user_meta( get_current_user_id(), 'wak_reviews_per_page', $value );
			}

		}

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_owners_autoshop_reviews' ) ) :
	function wak_get_owners_autoshop_reviews( $owner_id = NULL, $status = 1, $number = 5 ) {

		global $wpdb, $wak_review_db;

		$reviews      = array();
		$autoshop_ids = wak_get_users_autoshops( $owner_id, $status, $number );

		if ( $number === NULL )
			$number = '';
		else
			$number = 'LIMIT 0,' . absint( $number );

		if ( ! empty( $autoshop_ids ) ) {

			$autoshop_ids = implode( ', ', $autoshop_ids );

			$reviews = $wpdb->get_results( $wpdb->prepare( "
				SELECT * 
				FROM {$wak_review_db} 
				WHERE autoshop_id IN ({$autoshop_ids}) 
				AND status = %d 
				ORDER BY time DESC 
				{$number};", $status ) );

		}

		return $reviews;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_myreviews_my_shops_tab' ) ) :
	function wak_myreviews_my_shops_tab() {

		$wak_profile = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

		$counter     = 0;
		$date_format = get_option( 'date_format' );

		$wak_profile->is_my_profile = wak_theme_is_my_profile( get_current_user_id() );

		$number = NULL;

		$reviews     = array();
		$counter     = 0;
		$date_format = get_option( 'date_format' );

		if ( function_exists( 'wak_get_users_reviews' ) )
			$reviews = wak_get_users_reviews( $wak_profile->ID, 1, $number );

		$name = $wak_profile->first_name;
		if ( strlen( $name ) == 0 )
			$name = $wak_profile->user_login;

		$title   = sprintf( 'Reviews by %s', $name );
		$nothing = sprintf( '%s has not yet published any reviews.', $name );

		if ( $wak_profile->is_my_profile ) {
			$title   = 'My Reviews';
			$nothing = 'You have not published any reviews yet.';
		}

?>
<div id="wak-users-reviews">
	<div class="widget"><h4 class="widget-title"><?php echo $title; ?></h4></div>
	<div class="row">
<?php

		if ( ! empty( $reviews ) ) :

			foreach ( $reviews as $review ) :

				$review_body    = esc_attr( $review->review );
				$autoshop_url   = get_permalink( $review->autoshop_id );
				$autoshop_title = get_the_title( $review->autoshop_id );

?>
		<div class="col-md-12 col-sm-12 col-xs-12<?php if ( autoshop_has_pledged( $review->autoshop_id ) ) echo ' pledged'; ?>">

			<blockquote>
				<p><?php echo nl2br( $review_body ); ?></p>

				<?php wak_display_users_autoshop_rating( $review->wheels, $review->autoshop_id ); ?>

				<footer><?php printf( 'Posted %s for %s', date_i18n( $date_format, $review->time ), '<a href="' . $autoshop_url . '">' . $autoshop_title . '</a>' ); ?></footer>
			</blockquote>

		</div>

	<?php $counter ++; endforeach; ?>

	<?php else : ?>

	<div class="row">
		<div class="col-md-12 col-sm-12 col-xs-12">
			<p><?php echo $nothing; ?></p>
		</div>
	</div>

	<?php endif; ?>

	</div>
</div>
<?php

	}
endif;

?>