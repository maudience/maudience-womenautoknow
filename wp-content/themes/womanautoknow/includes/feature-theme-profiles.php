<?php

/**
 * Feature: Profiles
 * @version 1.0
 */
add_action( 'init', 'wak_theme_setup_profiles_feature' );
function wak_theme_setup_profiles_feature() {

	global $wp_rewrite;

	$wp_rewrite->author_base = 'drivers';

	$wak_vars = array(
		'notifications' => 'notifications',
		'profile'       => 'profile',
		'cars'          => 'cars',
		'recalls'       => 'recalls',
		'reviews'       => 'reviews',
		'shops'         => 'shops'
	);

	foreach ( $wak_vars as $key => $var )
		add_rewrite_endpoint( $var, EP_AUTHORS );

	add_filter( 'query_vars',        'wak_theme_add_query_vars' );
	add_action( 'parse_request',     'wak_theme_parse_request' );
	add_action( 'template_redirect', 'wak_theme_profile_redirects' );
	add_action( 'wp',                'wak_theme_front_end_profile_edit' );

	add_action( 'show_user_profile', 'wak_theme_admin_add_user_details' );
	add_action( 'edit_user_profile', 'wak_theme_admin_add_user_details' );

	add_action( 'personal_options_update',  'wak_theme_admin_save_user_details' );
	add_action( 'edit_user_profile_update', 'wak_theme_admin_save_user_details' );

}

/**
 * 
 * @version 1.0
 */
function wak_theme_add_query_vars( $vars ) {

	$wak_vars = array(
		'notifications' => 'notifications',
		'profile'       => 'profile',
		'cars'          => 'cars',
		'recalls'       => 'recalls',
		'reviews'       => 'reviews',
		'shops'         => 'shops'
	);

	foreach ( $wak_vars as $key => $var ) {
		if ( ! in_array( $var, $vars ) )
			$vars[] = $var;
	}
	return $vars;

}

/**
 * 
 * @version 1.0
 */
function wak_theme_front_end_profile_edit() {

	if ( ! is_user_logged_in() || ! is_author() ) return; 

	$user_id = get_current_user_id();

	if ( ! isset( $_POST['wak_profile'] ) || $_POST['wak_profile']['profile_id'] != $user_id ) return;

	$new_details = wp_parse_args( $_POST['wak_profile'], array(
		'profile_id'      => NULL,
		'token'           => '',
		'first_name'      => '',
		'last_name'       => '',
		'gender'          => 0,
		'age'             => '',
		'state'           => '',
		'zip'             => '',
		'newsletter'      => 0,
		'email'           => '',
		'new_pwd'         => '',
		'new_pwd2'        => '',
		'billing-address' => '',
		'billing-city'    => '',
		'billing-zip'     => '',
		'billing-state'   => ''
	) );

	if ( ! wp_verify_nonce( $new_details['token'], 'wak-edit-my-profile' . $new_details['profile_id'] ) ) return;

	$errors = array();

	// Validate the posted values
	$first_name = sanitize_text_field( $new_details['first_name'] );
	if ( $first_name == '' )
		$errors[] = 'First name can not be empty.';
	else
		$new_details['first_name'] = $first_name;

	$last_name = sanitize_text_field( $new_details['last_name'] );
	if ( $last_name == '' )
		$errors[] = 'Last name can not be empty.';
	else
		$new_details['last_name'] = $last_name;

	$gender = sanitize_text_field( $new_details['gender'] );
	if ( $gender == '' )
		$errors[] = 'Please select gender.';
	else
		$new_details['gender'] = $gender;

	$new_details['age']   = sanitize_text_field( $new_details['age'] );
	$new_details['state'] = sanitize_text_field( $new_details['state'] );
	$new_details['zip']   = sanitize_text_field( $new_details['zip'] );

	$email = sanitize_text_field( $new_details['email'] );
	if ( $email == '' || ! is_email( $email ) )
		$errors[] = 'Invalid email address.';
	else
		$new_details['email'] = $email;

	$change_password = false;
	$new_password1   = sanitize_text_field( $new_details['new_pwd'] );
	$new_password2   = sanitize_text_field( $new_details['new_pwd2'] );
	if ( $new_password1 != '' ) {

		if ( $new_password2 == '' || $new_password1 != $new_password2 )
			$errors[] = 'Please re-enter your new password twice if you wish to change it.';
		else
			$change_password = true;

	}

	$profile     = get_userdata( $new_details['profile_id'] );
	$profile_url = add_query_arg( array( 'do' => 'edit' ), wak_theme_get_profile_url( $profile ) );
	if ( empty( $errors ) ) {

		$args = array(
			'ID'           => $profile->ID,
			'user_email'   => $new_details['email'],
			'first_name'   => $new_details['first_name'],
			'last_name'    => $new_details['last_name'],
			'display_name' => $new_details['first_name']
		);

		if ( $change_password )
			$args['user_pass'] = $new_password1;

		$user_id = wp_update_user( $args );

		if ( ! is_wp_error( $user_id ) && $user_id !== NULL ) {

			update_user_meta( $user_id, 'gender', $new_details['gender'] );
			update_user_meta( $user_id, 'age', $new_details['age'] );
			update_user_meta( $user_id, 'state', $new_details['state'] );
			update_user_meta( $user_id, 'zip', $new_details['zip'] );
			update_user_meta( $user_id, 'newsletter', $new_details['newsletter'] );

			if ( function_exists( 'user_has_autoshops' ) && user_has_autoshops( $user_id ) ) {

				update_user_meta( $user_id, 'billing-address', sanitize_text_field( $new_details['billing-address'] ) );
				update_user_meta( $user_id, 'billing-city',    sanitize_text_field( $new_details['billing-city'] ) );
				update_user_meta( $user_id, 'billing-zip',     sanitize_text_field( $new_details['billing-zip'] ) );
				update_user_meta( $user_id, 'billing-state',   sanitize_text_field( $new_details['billing-state'] ) );

			}

			$url = add_query_arg( array( 'updated' => 1 ), $profile_url );
			wp_safe_redirect( $url );
			exit;

		}
		else {

			$errors[] = $user_id->get_error_message();

		}

	}

	if ( ! empty( $errors ) ) {

		$_POST['wak_profile'] = $errors;

	}

}

/**
 * 
 * @version 1.0
 */
function wak_theme_parse_request() {

	if ( ! is_author() ) return;

	$wak_vars = array(
		'notifications' => 'notifications',
		'profile'       => 'profile',
		'cars'          => 'cars',
		'recalls'       => 'recalls',
		'reviews'       => 'reviews',
		'shops'         => 'shops'
	);

	global $wp;

	// Map query vars to their keys, or get them if endpoints are not supported
	foreach ( $wak_vars as $key => $var ) {

		if ( isset( $_GET[ $var ] ) && ! empty( $_GET[ $var ] ) ) {
			$wp->vars[ $key ] = $_GET[ $var ];
		}

		elseif ( isset( $wp->vars[ $var ] ) ) {
			$wp->vars[ $key ] = $wp->vars[ $var ];
		}

	}

}

/**
 * 
 * @version 1.0
 */
function wak_theme_admin_add_user_details( $user ) {

	if ( ! current_user_can( 'edit_users' ) ) return;

	$type = get_user_meta( $user->ID, 'type', true );
	if ( $type == '' )
		$type = 'driver';

	$old_id = get_user_meta( $user->ID, 'OldID', true );

?>
<h3>WAK Account Details</h3>
<table class="form-table">
	<tr>
		<th scope="row"><label for="wak-account-type">Account Type</label></th>
		<td>
			<select name="wak_account_type" id="wak-account-type"><?php

	$options = array(
		'driver'  => 'Driver',
		'owner'   => 'Auto Shop Owner',
		'premium' => 'Auto Shop Owner (Premium)',
	);

	foreach ( $options as $value => $label ) {

		echo '<option value="' . $value . '"';
		if ( $type == $value ) echo ' selected="selected"';
		echo '>' . $label . '</option>';

	}

?></select>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="wak-OldID">Old ID</label></th>
		<td>
			<input type="text" class="regular-text" name="wak_account_oldid" id="wak-OldID" value="<?php echo esc_attr( $old_id ); ?>" />
		</td>
	</tr>
</table>
<?php

}

function wak_theme_admin_save_user_details( $user_id ) {

	if ( ! current_user_can( 'edit_users' ) ) return;

	$value = sanitize_key( $_POST['wak_account_type'] );
	update_user_meta( $user_id, 'type', $value );

	$value = sanitize_key( $_POST['wak_account_oldid'] );
	update_user_meta( $user_id, 'OldID', $value );

}

/**
 * 
 * @version 1.0
 */
function wak_theme_profile_redirects() {

	if ( is_page( 'drivers' ) ) {

		wp_redirect( home_url() );
		exit;

	}

	if ( ! is_author() ) return;

	if ( ! is_user_logged_in() ) {

		wp_redirect( home_url() );
		exit;

	}

	$user_id = get_current_user_id();

	if ( ! wak_theme_is_my_profile( $user_id ) && ! current_user_can( 'edit_users' ) ) {

		wp_redirect( wak_theme_get_profile_url() );
		exit;

	}

}

/**
 * Is My Profile
 * @version 1.0
 */
function wak_theme_is_my_profile( $user_id = NULL ) {

	if ( ! is_author() ) return false;

	$user = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

	if ( isset( $user->ID ) && $user->ID == $user_id )
		return true;

	return false;

}

/**
 * Get Profile URL
 * @version 1.0
 */
function wak_theme_get_profile_url( $user = NULL ) {

	if ( ! isset( $user->user_login ) ) {

		if ( ! is_user_logged_in() ) return home_url( '/drivers/' );

		$user = wp_get_current_user();

	}

	return home_url( 'drivers/' . urlencode( strtolower( $user->user_login ) ) . '/' );

}

/**
 * Get Profile URL
 * @version 1.0
 */
function wak_theme_get_users_tabs( $user ) {

	global $wp;

	$default = '';

	if ( $user->is_my_profile ) {

		$active = 'profile';
		if ( isset( $_GET['show'] ) ) {

			if ( $_GET['show'] == 'edit' )
				$active = 'edit';

			if ( $_GET['show'] == 'reviews' )
				$active = 'reviews';

			if ( $_GET['show'] == 'cars' )
				$active = 'profile';

			if ( $_GET['show'] == 'shops' )
				$active = 'shops';

			if ( $_GET['show'] == 'log' )
				$active = 'log';

			if ( $_GET['show'] == 'recalls' )
				$active = 'recalls';

		}

		$tabs = array(
			'cars' => array(
				'classes' => ( ( $active == 'profile' ) ? 'active' : '' ),
				'title'   => 'My Cars',
				'icon'    => 'fa-car'
			),
			'edit' => array(
				'classes' => ( ( $active == 'edit' ) ? 'active' : '' ),
				'title'   => 'Edit Profile',
				'icon'    => 'fa-pencil-square-o'
			),
			'reviews' => array(
				'classes' => ( ( $active == 'reviews' ) ? 'active' : '' ),
				'title'   => 'Reviews <span class="badge">' . ( ( function_exists( 'wak_count_users_reviews' ) ) ? wak_count_users_reviews( $user->ID ) : '0' ) . '</span>',
				'icon'    => 'fa-star'
			)
		);

		$autoshop_count = array( 'count' => 0, 'IDs' => array() );
		if ( function_exists( 'wak_count_users_autoshops' ) )
			$autoshop_count = wak_count_users_autoshops( $user->ID );

		if ( $autoshop_count['count'] > 0 ) {

			if ( $autoshop_count['count'] > 1 )
				$tabs['shops'] = array(
					'classes' => ( ( $active == 'shops' ) ? 'active' : '' ),
					'title'   => 'My Auto Shops <span class="badge">' . $autoshop_count['count'] . '</span>',
					'icon'    => 'fa-street-view'
				);

			else
				$tabs['shops'] = array(
					'classes' => '',
					'title'   => 'My Auto Shop',
					'icon'    => 'fa-street-view',
					'url'     => get_permalink( $autoshop_count['IDs'][0] )
				);

		}

		$tabs['log'] = array(
			'classes' => ( ( $active == 'log' ) ? 'active' : '' ),
			'title'   => 'Maintenance Log',
			'icon'    => 'fa-list'
		);

	}

	else {

		$active = 'profile';
		if ( isset( $_GET['show'] ) ) {

			if ( $_GET['show'] == 'reviews' )
				$active = 'reviews';

		}

		$tabs = array(
			'reviews' => array(
				'classes' => '',
				'title'   => 'Reviews <span class="badge">' . wak_count_users_reviews( $user->ID ) . '</span>',
				'icon'    => 'fa-star'
			)
		);

	}

	return apply_filters( 'wak_author_profile_tabs', $tabs, $user );

}

/**
 * 
 * @since 1.0
 * @version 1.0
 */
function wak_theme_profile_side_nav( $user = NULL ) {

	if ( ! isset( $user->ID ) )
		$user = wp_get_current_user();

	if ( ! isset( $user->ID ) ) return;

	global $wp;

	$profile_url = wak_theme_get_profile_url( $user );

	$nav = array();

	$nav[] = array(
		'label' => 'View Profile',
		'url'   => $profile_url,
		'class' => ( ! isset( $wp->query_vars['my-cars'] ) && ! isset( $wp->query_vars['my-reviews'] ) && ! isset( $_GET['do'] ) ) ? 'btn btn-default disabled' : 'btn btn-default'
	);

	$nav[] = array(
		'label' => 'Edit Profile',
		'url'   => $profile_url . '?do=edit',
		'class' => ( isset( $_GET['do'] ) && $_GET['do'] == 'edit' ) ? 'btn btn-default disabled' : 'btn btn-default'
	);

	$type = wak_theme_get_users_account_type( $user->ID );

	if ( $type != 'owner' ) {

		$nav[] = array(
			'label' => 'View My Cars',
			'url'   => $profile_url . 'my-cars/',
			'class' => ( isset( $wp->query_vars['my-cars'] ) ) ? 'btn btn-default disabled' : 'btn btn-default'
		);

		if ( isset( $wp->query_vars['my-cars'] ) )
			$nav[] = array(
				'label' => 'Add Car',
				'url'   => $profile_url . 'my-cars/?do=add-car',
				'class' => 'btn btn-default'
			);

	}

	elseif ( $type == 'owner' && function_exists( 'wak_get_my_autoshop_id' ) ) {

		$autoshop_id = wak_get_my_autoshop_id( $user->ID );

		if ( $autoshop_id !== NULL )
			$nav[] = array(
				'label' => 'View My Auto Shop',
				'url'   => get_permalink( $autoshop_id ),
				'class' => 'btn btn-default'
			);

	}

	$nav[] = array(
		'label' => 'View My Reviews',
		'url'   => $profile_url . 'my-reviews/',
		'class' => ( isset( $wp->query_vars['my-reviews'] ) ) ? 'btn btn-default disabled' : 'btn btn-default'
	);

	$nav[] = array(
		'label' => 'Logout',
		'url'   => $profile_url . 'my-reviews/',
		'class' => 'btn btn-danger'
	);

	$profile_nav = '';
	foreach ( $nav as $nav_item ) {

		$class = '';
		if ( $nav_item['class'] != '' )
			$class .= $nav_item['class'];

		$profile_nav .= '<a href="' . $nav_item['url'] . '" class="' . $class . '">' . $nav_item['label'] . '</a>';

	}

	echo $profile_nav;

}

/**
 * 
 * @since 1.0
 * @version 1.0
 */
function wak_theme_display_users_account_type( $user_id = NULL ) {

	if ( $user_id === NULL ) return '-';

	$type = wak_theme_get_users_account_type( $user_id );

	if ( $type == 'admin' )
		return 'Administrator';

	elseif ( $type == 'staff' )
		return 'Staff';

	elseif ( $type == 'owner' || $type == 'premium' )
		return 'Shop Owner';

	elseif ( $type == 'driver' )
		return 'Driver';

	return '-';

}

/**
 * 
 * @since 1.0
 * @version 1.0
 */
function wak_theme_get_users_account_type( $user_id = NULL ) {

	if ( $user_id === NULL ) return false;

	if ( user_can( $user_id, 'edit_users' ) ) return 'admin';

	if ( user_can( $user_id, 'manage_comments' ) ) return 'staff';

	$type = get_user_meta( $user_id, 'type', true );

	if ( $type == '' || $type == 'Shop Owner' || $type == 'Customer' ) {

		if ( $type == 'Shop Owner' ) {
			$type = 'owner';
			update_user_meta( $user_id, 'type', $type );
		}
		else {
			$type = 'driver';
			update_user_meta( $user_id, 'type', $type );
		}

	}

	elseif ( $type == 'premium' )
		$type = 'premium';

	return $type;

}

/**
 * Display Autoshop Rating
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_display_users_iq' ) ) :
	function wak_display_users_iq( $user_id = NULL ) {

		if ( ! defined( 'WAK_AUTOSHOPS' ) ) return '';

		$pledged = true;
		$premium = false;
		$rating = 3;

		if ( $rating > 0.00 ) {

			$image = absint( $rating );

			if ( $pledged )
				$image .= 'p';
			elseif ( $premium )
				$image .= 'n';
			else
				$image .= 's';

			$title = 'WAK IQ';

		}
		else {
			$image = 'default';
			$title = 'WAK IQ';
		}

		$image .= '.png';

		echo '<div class="wak-autoshop-rating"><img src="' . plugins_url( 'assets/images/' . $image, WAK_AUTOSHOPS ) . '" alt="' . $title . '" title="' . $title . '" /></div>';

	}
endif;

?>