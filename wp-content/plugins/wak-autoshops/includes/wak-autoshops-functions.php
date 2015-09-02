<?php
// No dirrect access
if ( ! defined( 'WAK_AUTOSHOPS_VER' ) ) exit;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_plugin_settings' ) ) :
	function wak_autoshops_plugin_settings() {

		$default = array(
			'visitors_viewing_review' => '',
			'review_mod'                   => 3,
			'carousel_number'              => 10,
			'caoursel_review_length'       => 128,
			'carousel_frequency'           => 5,
			'carousel_call_to_action'      => ''
		);

		$saved = get_option( 'wak_autoshop_plugin_prefs', $default );
		
		return wp_parse_args( $saved, $default );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_sanitize_autoshop_plugin_settings' ) ) :
	function wak_sanitize_autoshop_plugin_settings( $new ) {

		$saved = wak_autoshops_plugin_settings();

		return wp_parse_args( $new, $saved );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_setup_autoshop_object' ) ) :
	function wak_setup_autoshop_object() {

		$labels = array(
			'name'                 => 'Auto Shops',
			'singular_name'        => 'Autoshop',
			'menu_name'            => 'Auto Shops',
			'name_admin_bar'       => 'Autoshop',
			'parent_item_colon'    => 'Parent Autoshop:',
			'all_items'            => 'All Auto Shops',
			'add_new_item'         => 'Add New Autoshop',
			'add_new'              => 'Add New',
			'new_item'             => 'New Autoshop',
			'edit_item'            => 'Edit Autoshop',
			'update_item'          => 'Update Autoshop',
			'view_item'            => 'View Autoshop',
			'search_items'         => 'Search Autoshops',
			'not_found'            => 'Not found',
			'not_found_in_trash'   => 'Not found in Trash',
		);
		$args = array(
			'label'                => 'autoshops',
			'description'          => 'WAK Pledged Autoshops',
			'labels'               => $labels,
			'supports'             => array( 'title', 'thumbnail', 'editor', 'custom-fields' ),
			'hierarchical'         => false,
			'public'               => true,
			'show_ui'              => true,
			'show_in_menu'         => true,
			'menu_position'        => 5,
			'menu_icon'            => 'dashicons-businessman',
			'show_in_admin_bar'    => true,
			'show_in_nav_menus'    => false,
			'can_export'           => true,
			'has_archive'          => true,
			'exclude_from_search'  => false,
			'publicly_queryable'   => true,
			'capability_type'      => 'post',
			'register_meta_box_cb' => 'wak_autoshop_metaboxes'
		);
		register_post_type( 'autoshops', $args );

		//flush_rewrite_rules( true );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'autoshop_has_pledged' ) ) :
	function autoshop_has_pledged( $post_id = NULL ) {

		if ( $post_id === 0 || $post_id === NULL ) return false;

		$pledge = get_post_meta( $post_id, 'pledged', true );
		if ( strlen( $pledge ) == 1 && (int) $pledge === 1 ) return true;

		return false;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'autoshop_is_premium' ) ) :
	function autoshop_is_premium( $post_id = NULL ) {

		if ( $post_id === 0 || $post_id === NULL ) return false;

		$now     = current_time( 'timestamp' );
		$premium = get_post_meta( $post_id, 'premium_until', true );
		if ( $premium == '' ) return false;

		if ( strlen( $premium ) == 10 && strtotime( $premium, $now ) > $now ) return true;

		return false;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'autoshop_opening_hours' ) ) :
	function get_autoshop_opening_hours( $post_id ) {

		return wp_parse_args( (array) get_post_meta( $post_id, 'oh', true ), array(
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
		) );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'show_autoshop_address' ) ) :
	function show_autoshop_address( $autoshop_id = NULL, $inline = true, $account = false ) {

		if ( $autoshop_id === NULL || $autoshop_id == 0 ) return '';

		$details = array( '<address>' );
		$address = get_autoshops_address( $autoshop_id, $account );

		$pledged = autoshop_has_pledged( $autoshop_id );
		$premium = autoshop_is_premium( $autoshop_id );

		$sep = '' . "\n";
		if ( $inline )
			$sep = ',';

		if ( isset( $address['address2'] ) ) {

			if ( $address['address2'] != '' )
				$details[] = $address['address1'] . ' ' . $address['address2'] . $sep;
			else
				$details[] = $address['address1'] . $sep;

		}

		if ( isset( $address['city'] ) ) {

			if ( $address['city'] != '' )
				$details[] = $address['city'];

		}

		if ( isset( $address['zip'] ) ) {

			if ( $address['zip'] != '' )
				$details[] = $address['zip'];

		}

		if ( isset( $address['state'] ) ) {

			if ( $address['state'] != '' && ! is_admin() )
				$details[] = $address['state'];
			elseif ( is_admin() )
				$details[] = $address['state_code'];

		}

		$details[] = '</address>';

		if ( $account && ! isset( $address['phone'] ) ) return implode( ' ', $details );

		if ( ! $pledged && ! $premium ) return implode( ' ', $details ); 

		if ( isset( $address['phone'] ) ) {

			if ( $address['phone'] != '' && ! is_admin() )
				$details[] = '<p class="phone"><i class="fa fa-phone"></i> <a href="tel://' . wak_clean_phone_number( $address['phone'] ) . '">' . wak_clean_phone_number( $address['phone'] ) . '</a></p>';
			elseif ( is_admin() )
				$details[] = '<div class="phone"><i class="fa fa-phone"></i> ' . wak_clean_phone_number( $address['phone'] ) . '</div>';

		}

		$oh = array();

		$opening_hours = get_autoshop_opening_hours( $autoshop_id );

		if ( $opening_hours['monday']['from'] != '' && $opening_hours['monday']['to'] != '' )
			$oh['monday'] = '<span>Monday</span> <strong>' . $opening_hours['monday']['from'] . '</strong> to <strong>' . $opening_hours['monday']['to'] . '</strong>';

		if ( $opening_hours['tuesday']['from'] != '' && $opening_hours['tuesday']['to'] != '' )
			$oh['tuesday'] = '<span>Tuesday</span> <strong>' . $opening_hours['tuesday']['from'] . '</strong> to <strong>' . $opening_hours['tuesday']['to'] . '</strong>';

		if ( $opening_hours['wednesday']['from'] != '' && $opening_hours['wednesday']['to'] != '' )
			$oh['wednesday'] = '<span>Wednesday</span> <strong>' . $opening_hours['wednesday']['from'] . '</strong> to <strong>' . $opening_hours['wednesday']['to'] . '</strong>';

		if ( $opening_hours['thursday']['from'] != '' && $opening_hours['thursday']['to'] != '' )
			$oh['thursday'] = '<span>Thursday</span> <strong>' . $opening_hours['thursday']['from'] . '</strong> to <strong>' . $opening_hours['thursday']['to'] . '</strong>';

		if ( $opening_hours['friday']['from'] != '' && $opening_hours['friday']['to'] != '' )
			$oh['friday'] = '<span>Friday</span> <strong>' . $opening_hours['friday']['from'] . '</strong> to <strong>' . $opening_hours['friday']['to'] . '</strong>';

		if ( $opening_hours['saturday']['from'] != '' && $opening_hours['saturday']['to'] != '' )
			$oh['saturday'] = '<span>Saturday</span> <strong>' . $opening_hours['saturday']['from'] . '</strong> to <strong>' . $opening_hours['saturday']['to'] . '</strong>';
		elseif ( ( $opening_hours['saturday']['from'] == '' || strtolower( $opening_hours['saturday']['from'] ) == 'closed' ) && $opening_hours['saturday']['to'] == '' )
			$oh['saturday'] = '<span>Saturday</span> <strong>Closed</strong>';

		if ( $opening_hours['sunday']['from'] != '' && $opening_hours['sunday']['to'] != '' )
			$oh['sunday'] = '<span>Sunday</span> <strong>' . $opening_hours['sunday']['from'] . '</strong> to <strong>' . $opening_hours['sunday']['to'] . '</strong>';
		elseif ( ( $opening_hours['sunday']['from'] == '' || strtolower( $opening_hours['sunday']['from'] ) == 'closed' ) && $opening_hours['sunday']['to'] == '' )
			$oh['sunday'] = '<span>Sunday</span> <strong>Closed</strong>';

		if ( count( $oh ) > 3 ) {

			$list  = array();
			$today = strtolower( date( 'l', current_time( 'timestamp' ) ) );
			foreach ( $oh as $day => $display ) {

				if ( $today == $day )
					$list[] = '<li class="pink">' . $display . '</li>';
				else
					$list[] = '<li>' . $display . '</li>';

			}

			$details[] = '</div><h4 class="widget-title">Opening Hours</h4><div class="autoshop-opening-hours"><ul>' . implode( '', $list ) . '</ul>';

		}

		$social = array();

		if ( isset( $address['website'] ) && $address['website'] != '' )
			$social[] = '<a href="' . $address['website'] . '" class="website" target="_blank"><i class="fa fa-globe"></i></a>';

		if ( isset( $address['facebook'] ) && $address['facebook'] != '' )
			$social[] = '<a href="' . $address['facebook'] . '" class="facebook" target="_blank"><i class="fa fa-facebook"></i></a>';

		if ( isset( $address['twitter'] ) && $address['twitter'] != '' )
			$social[] = '<a href="' . $address['twitter'] . '" class="twitter" target="_blank"><i class="fa fa-twitter"></i></a>';

		if ( ! empty( $social ) && ! is_admin() )
			$details[] = '<div class="wak-autoshop-links">' . implode( '', $social ) . '</div>';

		return implode( ' ', $details );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'get_autoshops_address' ) ) :
	function get_autoshops_address( $autoshop_id, $account = false ) {

		$details = array(
			'address1'   => get_post_meta( $autoshop_id, 'address1', true ),
			'address2'   => get_post_meta( $autoshop_id, 'address2', true ),
			'city'       => get_post_meta( $autoshop_id, 'city', true ),
			'zip'        => get_post_meta( $autoshop_id, 'zip', true ),
			'state'      => get_post_meta( $autoshop_id, 'state', true ),
			'state_code' => get_post_meta( $autoshop_id, 'state', true ),
			'phone'      => get_post_meta( $autoshop_id, 'phone', true ),
			'website'    => get_post_meta( $autoshop_id, 'website', true ),
			'facebook'   => get_post_meta( $autoshop_id, 'facebook', true ),
			'twitter'    => get_post_meta( $autoshop_id, 'twitter', true )
		);

		$states = wak_get_states();
		if ( isset( $states[ $details['state'] ] ) )
			$details['state'] = $states[ $details['state'] ];

		if ( ! $account ) return $details;

		if ( autoshop_has_pledged( $autoshop_id ) || autoshop_is_premium( $autoshop_id ) )
			return $details;

		return array(
			'city'  => $details['city'],
			'state' => $details['state']
		);

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_yes_or_no_dropdown' ) ) :
	function wak_yes_or_no_dropdown( $name = '', $id = '', $selected = '' ) {

		$options = array( 1 => 'Yes', 0 => 'No' );

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
if ( ! function_exists( 'wak_is_autoshop_owner' ) ) :
	function wak_is_autoshop_owner( $autoshop_id = NULL, $user_id = NULL ) {

		if ( $autoshop_id === NULL || $user_id === NULL ) return false;

		$owner = wak_get_autoshop_owner( $autoshop_id );

		if ( $owner == $user_id )
			return true;

		return false;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'user_has_autoshops' ) ) :
	function user_has_autoshops( $user_id = NULL ) {

		if ( $user_id === NULL ) return false;

		$autoshops = new WP_Query( array(
			'post_type'      => 'autoshops',
			'posts_per_page' => '-1',
			'post_status'    => 'publish',
			'fields'         => 'ID',
			'meta_key'       => 'owner_id',
			'meta_value'     => $user_id
		) );

		$result = false;
		if ( $autoshops->have_posts() )
			$result = true;

		wp_reset_postdata();

		return $result;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_autoshop_owner' ) ) :
	function wak_get_autoshop_owner( $autoshop_id = NULL ) {

		$owner_id = get_post_meta( $autoshop_id, 'owner_id', true );
		if ( $owner_id == '' ) {

			$old_id = get_post_meta( $autoshop_id, 'OID', true );

			if ( $old_id != '' ) {

				global $wpdb;

				$user_id = $wpdb->get_var( $wpdb->prepare( "
					SELECT user_id 
					FROM {$wpdb->usermeta} 
					WHERE meta_key = 'OldID' 
					AND meta_value = %d;", $old_id ) );

				if ( $user_id !== NULL )
					return $user_id;

			}

		}

		return $owner_id;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_my_autoshop_id' ) ) :
	function wak_get_my_autoshop_id( $user_id = NULL ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "
			SELECT post_id 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = 'OID' 
			AND meta_value = %d;", get_user_meta( $user_id, 'OldID', true ) ) );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_get_users_autoshops' ) ) :
	function wak_get_users_autoshops( $user_id = NULL ) {

		global $wpdb;

		return $wpdb->get_col( $wpdb->prepare( "
			SELECT post_id 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = 'owner_id' 
			AND meta_value = %d;", $user_id ) );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_count_users_autoshops' ) ) :
	function wak_count_users_autoshops( $user_id = NULL, $count = 'all' ) {

		$autoshops = new WP_Query( array(
			'post_type'      => 'autoshops',
			'posts_per_page' => 6,
			'post_status'    => 'publish',
			'fields'         => 'ID',
			'meta_key'       => 'owner_id',
			'meta_value'     => $user_id
		) );

		$count = 0;
		$ids   = array();
		if ( $autoshops->have_posts() ) {

			foreach ( $autoshops->posts as $post_id ) {

				$count ++;
				$ids[] = $post_id;

			}

		}

		wp_reset_postdata();

		return array(
			'count' => $count,
			'IDs'   => $ids
		);

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_profile_pagination' ) ) :
	function wak_autoshop_profile_pagination( $numpages = '', $pagerange = '', $paged = '' ) {

		if ( empty( $pagerange ) ) $pagerange = 2;

		global $paged;

		if ( empty( $paged ) )
			$paged = 1;

		if ( $numpages == '' ) {

			global $wp_query;
			$numpages = $wp_query->max_num_pages;
			if ( ! $numpages )
				$numpages = 1;
		}

		$pagination_args = array(
			'base'            => get_pagenum_link( 1 ) . '%_%',
			'format'          => 'page/%#%',
			'total'           => $numpages,
			'current'         => $paged,
			'show_all'        => false,
			'end_size'        => 1,
			'mid_size'        => $pagerange,
			'prev_next'       => true,
			'prev_text'       => __( '&laquo;' ),
			'next_text'       => __( '&raquo;' ),
			'type'            => 'array',
			'add_args'        => false,
			'add_fragment'    => ''
		);

		$paginate_links = paginate_links( $pagination_args );

		if ( $paginate_links ) {

			echo '<nav class="custom-pagination"><ul class="pagination"><li>';

			echo join( '</li><li>', $paginate_links );

			echo '</li></ul></nav>';

		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_update_autoshop_stats' ) ) :
	function wak_update_autoshop_stats( $post_id = NULL ) {

		if ( ! is_user_logged_in() ) {

			$count = (int) get_post_meta( $post_id, 'stat_visitors', true );
			$count ++;
			update_post_meta( $post_id, 'stat_visitors', $count );

		}
		else {

			$user_id  = get_current_user_id();
			$owner_id = (int) get_post_meta( $post_id, 'owner_id', true );
			$added_by = (int) get_post_meta( $post_id, 'added_by', true );

			if ( $user_id == $owner_id || $user_id == $added_by || current_user_can( 'manage_comments' ) ) return;

			$count = (int) get_post_meta( $post_id, 'stat_members', true );
			$count ++;
			update_post_meta( $post_id, 'stat_members', $count );

			$zip = get_user_meta( $user_id, 'zip', true );
			if ( strlen( $zip ) == 5 ) {
			
				$stats = wp_parse_args( (array) get_post_meta( $post_id, 'stat_zip', true ), array( 'local' => 0, 'external' => 0 ) );

				if ( get_post_meta( $post_id, 'zip', true ) == $zip )
					$stats['local'] ++;
				else
					$stats['external'] ++;

				update_post_meta( $post_id, 'stat_zip', $stats );

			}

			$states = wak_get_states();
			$state  = get_user_meta( $user_id, 'state', true );
			if ( strlen( $state ) == 2 && isset( $states[ $state ] ) ) {
			
				$stats = wp_parse_args( (array) get_post_meta( $post_id, 'stat_state', true ), array( 'local' => 0, 'external' => 0 ) );

				if ( get_post_meta( $post_id, 'state', true ) == $state )
					$stats['local'] ++;
				else
					$stats['external'] ++;

				update_post_meta( $post_id, 'stat_state', $stats );

			}

		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_my_shops_tab' ) ) :
	function wak_autoshops_my_shops_tab() {

		$wak_profile = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

		$counter     = 0;
		$date_format = get_option( 'date_format' );

		$wak_profile->is_my_profile = wak_theme_is_my_profile( get_current_user_id() );

		$name = $wak_profile->first_name;
		if ( strlen( $name ) == 0 )
			$name = $wak_profile->user_login;

		$title   = sprintf( '%s\'s Auto Shops', $name );
		$nothing = sprintf( '%s has not yet added any auto shops.', $name );

		if ( $wak_profile->is_my_profile ) {
			$title   = 'My Auto Shops';
			$nothing = 'You have not yet added any auto shops.';
		}

		$paged = ( get_query_var('page') ) ? get_query_var('page') : 1;

		$autoshops = new WP_Query( array(
			'post_type'      => 'autoshops',
			'posts_per_page' => 6,
			'post_status'    => 'publish',
			'paged'          => $paged,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'   => 'owner_id',
					'value' => $wak_profile->ID
				),
				array(
					'key'   => 'added_by',
					'value' => $wak_profile->ID
				)
			)
		) );

?>
<div class="row">
	<div class="col-md-12 col-xs-12">
		<div id="wak-my-autoshops">
			<?php if ( $wak_profile->is_my_profile ) : ?><p><button type="button" data-backdrop="static" class="new-autoshop-button btn btn-danger" data-toggle="modal" data-target="#add-new-wak-autoshop">Add Auto Shop</button></p><?php endif; ?>
			<div class="widget"><h4 class="widget-title"><?php echo $title; ?></h4></div>
<?php

		if ( $wak_profile->is_my_profile )
			echo '<p style="font-size:12px; line-height:18px;">Here you can find Auto Shops you own and auto shops you have submitted to WAK.</p>';

		if ( $autoshops->have_posts() ) :

?>
			<div class="row">
<?php

			while ( $autoshops->have_posts() ) :

				$counter ++;

				$autoshops->the_post();
				$post_id = get_the_ID();

?>
				<div class="col-md-12 col-sm-12 col-xs-12">
<?php

				if ( autoshop_has_pledged( $post_id ) )
					get_template_part( 'autoshop', 'pledged' );

				elseif ( autoshop_is_premium( $post_id ) )
					get_template_part( 'autoshop', 'premium' );

				else
					get_template_part( 'autoshop', 'default' );

?>

				</div>
<?php

			endwhile;

?>
			</div>
			<div class="row">
				<div class="col-md-12 col-xs-12">

					<?php wak_autoshop_profile_pagination( $autoshops->max_num_pages, '', $paged ); ?>

				</div>
			</div>
<?php

		else :

?>
			<div class="row">
				<div class="col-md-12 col-xs-12 text-center">
					<p>No shops found.</p>
					<?php if ( $wak_profile->is_my_profile ) { ?><p><button type="button" data-backdrop="static" class="new-autoshop-button btn btn-danger btn-lg" data-toggle="modal" data-target="#add-new-wak-autoshop">Add Auto Shop</button></p><?php } ?>
				</div>
			</div>
<?php

		endif;

		wp_reset_postdata();

?>
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
if ( ! function_exists( 'wak_new_autoshop_form' ) ) :
	function wak_new_autoshop_form( $data = array() ) {

		$user_id = get_current_user_id();

?>
<form id="wak-add-new-autoshop-form" method="post" action="" style="padding: 0 24px;">
	<input type="hidden" name="wak_new_autoshop[user_id]" value="<?php echo $user_id; ?>" />
	<input type="hidden" name="wak_new_autoshop[token]" value="<?php echo wp_create_nonce( 'submit-new-wak-autoshop' . $user_id ); ?>" />
	<div role="tabpanel">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#autoshopaddress" aria-controls="autoshopaddress" role="tab" data-toggle="tab">Business Address</a></li>
			<li role="presentation"><a href="#autoshopcontact" aria-controls="autoshopcontact" role="tab" data-toggle="tab">Contact Details</a></li>
			<li role="presentation"><a href="#openinghours" aria-controls="openinghours" role="tab" data-toggle="tab">Opening Hours</a></li>

			<?php if ( isset( $data['post_id'] ) ) do_action( 'wak_my_autoshop_tabs', $data['post_id'], $user_id, $data ); ?>

		</ul>
	</div>
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="autoshopaddress">
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-shop-name">Auto Shop Name</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control" name="wak_new_autoshop[name]" id="wak-new-auto-shop-name" value="<?php if ( isset( $data['name'] ) ) echo esc_attr( $data['name'] ); ?>" placeholder="Required" />
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-shop-address1">Street Address</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control" name="wak_new_autoshop[address1]" id="wak-new-auto-shop-address1" value="<?php if ( isset( $data['address1'] ) ) echo esc_attr( $data['address1'] ); ?>" />
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-shop-city">City</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control" name="wak_new_autoshop[city]" id="wak-new-auto-shop-city" value="<?php if ( isset( $data['city'] ) ) echo esc_attr( $data['city'] ); ?>" />
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-shop-zip">Zip</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control short" placeholder="xxxxx" name="wak_new_autoshop[zip]" id="wak-new-auto-shop-zip" value="<?php if ( isset( $data['zip'] ) ) echo esc_attr( $data['zip'] ); ?>" />
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-shop-state">State</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<?php echo wak_states_dropdown( 'wak_new_autoshop[state]', 'wak-new-auto-shop-state', '', ( ( isset( $data['state'] ) ) ? esc_attr( $data['state'] ) : '' ) ); ?>
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane" id="autoshopcontact">
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-shop-contact_person">Contact Person</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control" name="wak_new_autoshop[contact_person]" placeholder="<?php _e( 'Name', '' ); ?>" id="wak-new-auto-shop-contact_person" value="<?php if ( isset( $data['contact_person'] ) ) echo esc_attr( $data['contact_person'] ); ?>" />
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-shop-contact_phone">Phone</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control" name="wak_new_autoshop[phone]" placeholder="xxx xxx xxxx" id="wak-new-auto-shop-contact_phone" value="<?php if ( isset( $data['phone'] ) ) echo esc_attr( $data['phone'] ); ?>" />
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-shop-website">Website</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control" name="wak_new_autoshop[facebook]" placeholder="http://" id="wak-new-auto-shop-website" value="<?php if ( isset( $data['website'] ) ) echo esc_attr( $data['website'] ); ?>" />
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-shop-facebook">Facebook</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control" name="wak_new_autoshop[facebook]" placeholder="http://" id="wak-new-auto-shop-facebook" value="<?php if ( isset( $data['facebook'] ) ) echo esc_attr( $data['facebook'] ); ?>" />
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-shop-twitter">Twitter</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<input type="text" class="form-control" name="wak_new_autoshop[twitter]" placeholder="http://" id="wak-new-auto-shop-twitter" value="<?php if ( isset( $data['twitter'] ) ) echo esc_attr( $data['twitter'] ); ?>" />
				</div>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane" id="openinghours">
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-oh-monday-from">Monday</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<div class="row">
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][monday][from]" id="wak-new-auto-oh-monday-from" value="<?php if ( isset( $data['oh']['monday']['from'] ) ) echo esc_attr( $data['oh']['monday']['from'] ); ?>" placeholder="from" />
						</div>
						<div class="col-md-2 col-sm-2 col-xs-2 text-center">-</div>
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][monday][to]" id="wak-new-auto-oh-monday-to" value="<?php if ( isset( $data['oh']['monday']['to'] ) ) echo esc_attr( $data['oh']['monday']['to'] ); ?>" placeholder="to" />
						</div>
					</div>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-oh-tuesday-from">Tuesday</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<div class="row">
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][tuesday][from]" id="wak-new-auto-oh-tuesday-from" value="<?php if ( isset( $data['oh']['tuesday']['from'] ) ) echo esc_attr( $data['oh']['tuesday']['from'] ); ?>" placeholder="from" />
						</div>
						<div class="col-md-2 col-sm-2 col-xs-2 text-center">-</div>
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][tuesday][to]" id="wak-new-auto-oh-tuesday-to" value="<?php if ( isset( $data['oh']['tuesday']['to'] ) ) echo esc_attr( $data['oh']['tuesday']['to'] ); ?>" placeholder="to" />
						</div>
					</div>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-oh-wednesday-from">Wednesday</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<div class="row">
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][wednesday][from]" id="wak-new-auto-oh-wednesday-from" value="<?php if ( isset( $data['oh']['wednesday']['from'] ) ) echo esc_attr( $data['oh']['wednesday']['from'] ); ?>" placeholder="from" />
						</div>
						<div class="col-md-2 col-sm-2 col-xs-2 text-center">-</div>
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][wednesday][to]" id="wak-new-auto-oh-wednesday-to" value="<?php if ( isset( $data['oh']['wednesday']['to'] ) ) echo esc_attr( $data['oh']['wednesday']['to'] ); ?>" placeholder="to" />
						</div>
					</div>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-oh-thursday-from">Thursday</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<div class="row">
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][thursday][from]" id="wak-new-auto-oh-thursday-from" value="<?php if ( isset( $data['oh']['thursday']['from'] ) ) echo esc_attr( $data['oh']['thursday']['from'] ); ?>" placeholder="from" />
						</div>
						<div class="col-md-2 col-sm-2 col-xs-2 text-center">-</div>
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][thursday][to]" id="wak-new-auto-oh-thursday-to" value="<?php if ( isset( $data['oh']['thursday']['to'] ) ) echo esc_attr( $data['oh']['thursday']['to'] ); ?>" placeholder="to" />
						</div>
					</div>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-oh-friday-from">Friday</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<div class="row">
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][friday][from]" id="wak-new-auto-oh-friday-from" value="<?php if ( isset( $data['oh']['friday']['from'] ) ) echo esc_attr( $data['oh']['friday']['from'] ); ?>" placeholder="from" />
						</div>
						<div class="col-md-2 col-sm-2 col-xs-2 text-center">-</div>
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][friday][to]" id="wak-new-auto-oh-friday-to" value="<?php if ( isset( $data['oh']['friday']['to'] ) ) echo esc_attr( $data['oh']['friday']['to'] ); ?>" placeholder="to" />
						</div>
					</div>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-oh-saturday-from">Saturday</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<div class="row">
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][saturday][from]" id="wak-new-auto-oh-saturday-from" value="<?php if ( isset( $data['oh']['saturday']['from'] ) ) echo esc_attr( $data['oh']['saturday']['from'] ); ?>" placeholder="from" />
						</div>
						<div class="col-md-2 col-sm-2 col-xs-2 text-center">-</div>
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][saturday][to]" id="wak-new-auto-oh-saturday-to" value="<?php if ( isset( $data['oh']['saturday']['to'] ) ) echo esc_attr( $data['oh']['saturday']['to'] ); ?>" placeholder="to" />
						</div>
					</div>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-4 col-sm-4 col-xs-12"><label for="wak-new-auto-oh-sunday-from">Sunday</label></div>
				<div class="col-md-8 col-sm-8 col-xs-12">
					<div class="row">
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][sunday][from]" id="wak-new-auto-oh-sunday-from" value="<?php if ( isset( $data['oh']['sunday']['from'] ) ) echo esc_attr( $data['oh']['sunday']['from'] ); ?>" placeholder="from" />
						</div>
						<div class="col-md-2 col-sm-2 col-xs-2 text-center">-</div>
						<div class="col-md-5 col-sm-5 col-xs-5">
							<input type="text" class="form-control" name="wak_new_autoshop[oh][sunday][to]" id="wak-new-auto-oh-sunday-to" value="<?php if ( isset( $data['oh']['sunday']['to'] ) ) echo esc_attr( $data['oh']['sunday']['to'] ); ?>" placeholder="to" />
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php if ( isset( $data['post_id'] ) ) do_action( 'wak_my_autoshop_tab_content', $data['post_id'], $user_id, $data ); ?>

	</div>
	<div class="row">
		<div class="col-md-12 text-right" style="padding-top: 12px;">
			<input type="submit" class="btn btn-danger" id="submit-new-autoshop-button" value="<?php if ( empty( $data ) ) _e( 'Submit Auto Shop', '' ); else _e( 'Update Auto Shop', '' ); ?>" />
		</div>
	</div>
</form>
<?php

	}
endif;

?>