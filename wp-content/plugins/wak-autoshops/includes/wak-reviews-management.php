<?php
// No dirrect access
if ( ! defined( 'WAK_AUTOSHOPS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_reviews_wp_footer' ) ) :
	function wak_reviews_wp_footer() {

?>
<div class="modal fade" id="add-new-wak-review">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title pink"><?php _e( 'Add New Review', '' ); ?></h4>
			</div>
			<div class="modal-body">
				<h1 class="text-center pink"><i class="fa fa-spinner fa-spin"></i></h1>
				<p class="text-center"><?php _e( 'Loading review form...', '' ); ?></p>
			</div>
		</div>
	</div>
</div>
<?php

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_reviews_autoshops_column_content' ) ) :
	function wak_reviews_autoshops_column_content( $column_name, $post_id ) {

		if ( $column_name == 'rating' ) {

			echo wak_get_autoshop_rating( $post_id );

		}
		elseif ( $column_name == 'reviews' ) {

			echo wak_count_autoshop_reviews( $post_id );

		}

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_reviews_admin_menu' ) ) :
	function wak_reviews_admin_menu() {

		$pages = array();

		$pending = wak_count_reviews_by_status();
		$pages[] = add_submenu_page(
			'edit.php?post_type=autoshops',
			__( 'Reviews', '' ),
			sprintf( __( 'Reviews %s', '' ), '<span class="update-plugins count-' . $pending . '" style="float:right;"><span class="plugin-count">' . $pending . '</span></span>' ),
			'moderate_comments',
			'autoshop-reviews',
			'wak_reviews_admin_screen'
		);

		$pages[] = add_submenu_page(
			'edit.php?post_type=autoshops',
			__( 'Settings', '' ),
			__( 'Settings', '' ),
			'moderate_comments',
			'autoshop-settings',
			'wak_autoshop_settings_admin_screen'
		);

		foreach ( $pages as $page ) {
			add_action( 'admin_print_styles-' . $page, 'wak_reviews_admin_screen_styles' );
			add_action( 'load-' . $page,               'wak_reviews_admin_load' );
		}

	}
endif;

function wak_debug_admin_screen() {

	$known = $maybe = $unknown = array();

	global $wpdb;

	$table = 'wp_shops';
	$query = $wpdb->get_col( "
		SELECT DISTINCT ID 
		FROM {$wpdb->posts} 
		WHERE post_type = 'autoshops' 
		AND post_status = 'publish';" );

	$added = $skipped = 0;
	if ( ! empty( $query ) ) {
		foreach ( $query as $post_id ) {

			$post_id = absint( $post_id );

			if ( autoshop_has_pledged( $post_id ) ) {

				continue;

			}
			else {

				if ( autoshop_is_premium( $post_id ) ) {

					continue;

				}
				else {

					update_post_meta( $post_id, 'pledged', 0 );

				}

			}

		}
	}

?>
<div class="wrap">
	<h2>Debug</h2>
	<p>&nbsp;</p>
	<p><?php printf( '%d autoshops updated. %d were skipped.', $added, $skipped ); ?></p>
</div>
<?php

}


/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_reviews_admin_screen_styles' ) ) :
	function wak_reviews_admin_screen_styles() {

?>
<style type="text/css">
th#auto { width: 25%; }
th#user { width: 20%; }
th#date { width: 14%; }
th#status { width: 10%; }
th#rate { width: 6%; }
th#reviews { width: 25%; }
#wakactions label { display: block; float: left; width: 30%; height: 18px; }
</style>
<?php

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_reviews_admin_load' ) ) :
	function wak_reviews_admin_load() {

		// Handle review admin actions
		wak_process_review_admin_actions();

		$args = array(
			'label'   => __( 'Reviews', '' ),
			'default' => 10,
			'option'  => 'wak_reviews_per_page'
		);
		add_screen_option( 'per_page', $args );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_reviews_admin_screen' ) ) :
	function wak_reviews_admin_screen() {

		if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' )
			wak_reviews_admin_screen_edit();

		else
			wak_reviews_admin_screen_list();

	}
endif;

if ( ! function_exists( 'wak_reviews_admin_screen_list' ) ) :
	function wak_reviews_admin_screen_list() {

		$args = array();

		$number = get_user_meta( get_current_user_id(), 'wak_reviews_per_page', true );
		if ( $number != '' )
			$args['number'] = absint( $number );

		if ( isset( $_GET['status'] ) )
			$args['status'] = absint( $_GET['status'] );

		if ( isset( $_GET['autoshop_id'] ) )
			$args['autoshop_id'] = absint( $_GET['autoshop_id'] );

		if ( isset( $_GET['user_id'] ) )
			$args['user_id'] = absint( $_GET['user_id'] );

		if ( isset( $_GET['paged'] ) )
			$args['paged'] = absint( $_GET['paged'] );

		$reviews = new WAK_Query_Reviews( $args );

?>
<div class="wrap">
	<h2><?php _e( 'Auto Shop Reviews', '' ); ?></h2>
<?php

		if ( isset( $_GET['updated'] ) ) {

			if ( $_GET['updated'] == 1 )
				echo '<div id="message" class="updated"><p>' . ( ( isset( $_GET['multi'] ) ? sprintf( _n( 'Review published.', '%d Reviews published.', $_GET['multi'], '' ), $_GET['multi'] ) : 'Review published.' ) ) . '</p></div>';

			elseif ( $_GET['updated'] == 2 )
				echo '<div id="message" class="updated"><p>' . ( ( isset( $_GET['multi'] ) ? sprintf( _n( 'Review flagged.', '%d Reviews flagged.', $_GET['multi'], '' ), $_GET['multi'] ) : 'Review flagged.' ) ) . '</p></div>';

			elseif ( $_GET['updated'] == 3 )
				echo '<div id="message" class="updated"><p>' . ( ( isset( $_GET['multi'] ) ? sprintf( _n( 'Review marked as SPAM.', '%d Reviews marked as SPAM.', $_GET['multi'], '' ), $_GET['multi'] ) : 'Review marked as SPAM.' ) ) . '</p></div>';

			elseif ( $_GET['updated'] == 0 )
				echo '<div id="message" class="updated"><p>' . ( ( isset( $_GET['multi'] ) ? sprintf( _n( 'Review pending review.', '%d Reviews pending review.', $_GET['multi'], '' ), $_GET['multi'] ) : 'Review pending review.' ) ) . '</p></div>';

		}

		elseif ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 1 )
			echo '<div id="message" class="error"><p>' . ( ( isset( $_GET['multi'] ) ? sprintf( _n( 'Review was successfully deleted.', '%d Reviews were successfully deleted.', $_GET['multi'], '' ), $_GET['multi'] ) : 'Review was successfully deleted.' ) ) . '</p></div>';

		elseif ( isset( $_GET['edited'] ) && $_GET['edited'] == 1 )
			echo '<div id="message" class="updated"><p>Review saved.</p></div>';

?>
	<?php $reviews->status_filter(); ?>

	<form id="review-list" method="get" action="edit.php">
		<input type="hidden" name="post_type" value="autoshops" />
		<input type="hidden" name="page" value="autoshop-reviews" />
		<div class="tablenav top">

			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
				<select name="action" id="bulk-action-selector-top">
					<option value="-1">Bulk Actions</option>
					<option value="spam">Mark as SPAM</option>
					<option value="delete">Delete</option>
					<option value="publish">Approve</option>
				</select>
				<input type="submit" id="doaction" class="button action" value="Apply" />
			</div>

			<div class="tablenav-pages">
				<?php $reviews->pagination(); ?>
			</div>

			<br class="clear" />

		</div>
		<table class="wp-list-table widefat fixed striped posts">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>
					<th scope="col" id="auto" class="manage-column column-auto auto-column">Auto Shop</th>
					<?php if ( ! isset( $_GET['status'] ) ) : ?><th scope="col" id="status" class="manage-column column-status status-column">Status</th><?php endif; ?>
					<th scope="col" id="rate" class="manage-column column-rate rate-column">Rating</th>
					<th scope="col" id="reviews" class="manage-column column-review review-column">Review</th>
					<th scope="col" id="user" class="manage-column column-user user-column">Member</th>
					<th scope="col" id="date" class="manage-column column-date date-column">Date</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>
					<th scope="col" class="manage-column column-auto auto-column">Auto Shop</th>
					<?php if ( ! isset( $_GET['status'] ) ) : ?><th scope="col" class="manage-column column-status status-column">Status</th><?php endif; ?>
					<th scope="col" class="manage-column column-rate rate-column">Rating</th>
					<th scope="col" class="manage-column column-review review-column">Review</th>
					<th scope="col" class="manage-column column-user user-column">Member</th>
					<th scope="col" class="manage-column column-date date-column">Date</th>
				</tr>
			</tfoot>
			<tbody>
<?php

		if ( $reviews->have_entries() ) {

			$date_format = get_option( 'date_format' );
			foreach ( $reviews->results as $entry ) {

				$user = get_userdata( $entry->user_id );

?>
				<tr id="<?php echo $entry->id ?>">
					<th scope="row" class="check-column"><input type="checkbox" id="review-<?php echo $entry->id; ?>" name="reviews[]" value="<?php echo $entry->id; ?>" /></th>
					<td class="auto-column">
						<strong><?php echo get_the_title( $entry->autoshop_id ); ?></strong>

						<?php echo $reviews->row_actions( $entry ); ?>

					</td>
					<?php if ( ! isset( $_GET['status'] ) ) : ?><td class="status-column"><?php echo wak_get_review_status( $entry->status ); ?></td><?php endif; ?>
					<td class="rate-column"><?php echo $entry->wheels; ?></td>
					<td class="review-column"><small><?php echo esc_attr( nl2br( $entry->review ) ); ?></small></td>
					<td class="user-column"><?php if ( isset( $user->display_name ) ) echo $user->display_name; else echo '-'; ?></td>
					<td class="date-column"><?php echo date_i18n( $date_format, $entry->time ); ?></td>
				</tr>
<?php

			}

		}

		else {

?>
				<tr>
					<td colspan="<?php if ( ! isset( $_GET['status'] ) ) echo 7; else echo 6; ?>">No reviews found.</td>
				</tr>
<?php

		}

?>
			</tbody>
		</table>
	</form>
</div>
<?php

	}
endif;

if ( ! function_exists( 'wak_reviews_admin_screen_edit' ) ) :
	function wak_reviews_admin_screen_edit() {

		if ( ! current_user_can( 'moderate_comments' ) ) wp_die( 'You are not allowed to edit reviews.' );

		$act      = sanitize_key( $_GET['action'] );
		$entry_id = absint( $_GET['autoshop_id'] );

		global $wpdb, $wak_review_db;

		$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wak_review_db} WHERE id = %d;", $entry_id ) );

		

?>
<div class="wrap">
	<h2><?php _e( 'Edit Review', '' ); ?></h2>
<?php

		if ( ! isset( $entry->id ) ) {

			echo '<p>Could not find review.</p>';

		}
		else {

			$by = get_userdata( $entry->user_id );

?>
<form name="post" id="post" method="post" action="" autocomplete="off">
	<input type="hidden" name="wak_review[autoshop_id]" value="<?php echo absint( $entry->autoshop_id ); ?>" />
	<input type="hidden" name="wak_review[time]" value="<?php echo absint( $entry->time ); ?>" />
	<input type="hidden" name="wak_review[entry_id]" value="<?php echo $entry->id; ?>" />
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content" style="position: relative;">

				<div id="titlediv">
					<div id="titlewrap">
						<input type="text" disabled="disabled" id="title" value="<?php echo esc_attr( get_the_title( $entry->autoshop_id ) ); ?>" />
					</div>
				</div>

			</div>

			<div id="postbox-container-1" style="position: relative;">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">

					<div id="submitdiv" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle ui-sortable-handle"><span>Publish</span></h3>
						<div class="inside" id="wakactions">

							<div class="submitbox" id="submitpost">
								<div id="minor-publishing">
									<div id="misc-publishing-actions">
										<div class="misc-pub-section misc-pub-post-status">
											<label for="review_status">Status:</label> <?php echo wak_review_status_dropdown( 'wak_review[status]', 'review_status', $entry->status ); ?>
										</div>
										<div class="misc-pub-section misc-pub-post-rating">
											<label for="wheels">Rating:</label> <select name="wak_review[wheels]" id="wheels">
<?php

		$options = array(
			1 => __( '1 Wheel', '' ),
			2 => __( '2 Wheels', '' ),
			3 => __( '3 Wheels', '' ),
			4 => __( '4 Wheels', '' ),
			5 => __( '5 Wheels', '' ),
		);

		foreach ( $options as $value => $label ) {

			echo '<option value="' . $value . '"';
			if ( $entry->wheels == $value ) echo ' selected="selected"';
			echo '>' . $label . '</option>';

		}

?>
</select>
										</div>
										<div class="misc-pub-section misc-pub-post-by">
											<label>Author:</label> <input type="text" name="wak_review[user_id]" id="user-id" value="<?php echo absint( $entry->user_id ); ?>" size="8" /><br />
											<label>Date:</label> <strong><?php echo date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $entry->time ); ?></strong>
										</div>
									</div>
								</div>
								<div id="major-publishing-actions">
									<div id="publishing-action">
										<input type="submit" name="save" class="button button-primary button-large" value="Save" />
									</div>
									<div class="clear"></div>
								</div>
							</div>

						</div>
					</div>

				</div>
			</div>

			<div id="postbox-container-2" style="position: relative;">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable"></div>
				<div id="advanced-sortables" class="meta-box-sortables ui-sortable">

					<!--<div id="test" class="postbox">
						<h3 class="hndle ui-sortable-handle"><span>Test</span></h3>
						<div class="inside">
							<pre><?php print_r( $_POST ); ?></pre>
						</div>
					</div>-->

					<div id="reviewinfo" class="postbox">
						<h3 class="hndle ui-sortable-handle"><span>Review</span></h3>
						<div class="inside">
							<p>
								<label for="is_pro"><input type="checkbox" name="wak_review[is_pro]" id="is_pro"<?php checked( $entry->is_pro, 1 ); ?> value="1" /> Is Professional </label> 
								<label for="is_comf"><input type="checkbox" name="wak_review[is_comf]" id="is_comf"<?php checked( $entry->is_comf, 1 ); ?> value="1" /> Is Comfortable </label> 
								<label for="will_return"><input type="checkbox" name="wak_review[will_return]" id="will_return"<?php checked( $entry->will_return, 1 ); ?> value="1" /> Will Return </label> 
								<label for="recommended"><input type="checkbox" name="wak_review[recommended]" id="recommended"<?php checked( $entry->recommended, 1 ); ?> value="1" /> Recommended </label> 
							</p>
							<textarea name="wak_review[review]" id="thereview" cols="40" style="width: 99%;" rows="5"><?php echo esc_attr( $entry->review ); ?></textarea>
						</div>
					</div>

				</div>
			</div>

		</div>
		<div class="clear"></div>
	</div>
</form>
<div class="clear"></div>
<?php

		}

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
if ( ! function_exists( 'wak_reviews_user_column_headers' ) ) :
	function wak_reviews_user_column_headers( $columns ) {

		if ( array_key_exists( 'posts', $columns ) )
			unset( $columns['posts'] );

		$columns['reviews'] = 'Reviews';

		return $columns;

	}
endif; 

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_reviews_user_column_content' ) ) :
	function wak_reviews_user_column_content( $value, $column_name, $user_id ) {

		if ( $column_name == 'reviews' ) {

			$count = wak_count_users_reviews( $user_id );
			$url   = add_query_arg( array( 'post_type' => 'autoshops', 'page' => 'autoshop-reviews', 'user_id' => $user_id ), admin_url( 'edit.php' ) );

			if ( $count > 0 )
				return '<a href="' . $url . '">' . $count . '</a>';
			else
				return $count;

		}

	    return $value;
	}
endif;

?>