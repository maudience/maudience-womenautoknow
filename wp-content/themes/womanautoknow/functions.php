<?php

/**
 * Include Custom Theme Functions
 * @since 1.0
 * @version 1.0
 */
require get_template_directory() . '/includes/theme-functions.php';

/**
 * Include Utility Functions
 * @since 1.0
 * @version 1.0
 */
require get_template_directory() . '/includes/utilities/utility-states.php';

/**
 * Include Theme Features
 * @since 1.0
 * @version 1.0
 */
require get_template_directory() . '/includes/feature-page-layouts.php';
require get_template_directory() . '/includes/feature-theme-profiles.php';
require get_template_directory() . '/includes/feature-theme-settings.php';
require get_template_directory() . '/includes/feature-user-badges.php';

/**
 * Set Content Width
 * @version 1.0
 */
if ( ! isset( $content_width ) ) {
	$content_width = 642;
}

/**
 * Excerpt Length
 * @version 1.0
 */
add_filter( 'excerpt_length', 'wak_custom_excerpt_length', 999 );
function wak_custom_excerpt_length( $length ) {
	return 40;
}

/**
 * Post Thumbnail HTML
 * @version 1.0
 */
add_filter( 'post_thumbnail_html', 'wak_post_image_html', 10, 3 );
function wak_post_image_html( $html, $post_id, $post_image_id ) {

	return '<a href="' . get_permalink( $post_id ) . '" title="' . esc_attr( get_post_field( 'post_title', $post_id ) ) . '">' . $html . '</a>';

}

/**
 * Theme Setup
 * @version 1.0
 */
add_action( 'after_setup_theme', 'wak_theme_setup' );
function wak_theme_setup() {

	add_theme_support( 'title-tag' );

	register_nav_menus( array(
		'primary'   => 'Menu visible for members',
		'secondary' => 'Menu visible for visitors',
		'profile'   => 'Profile page',
		'footer'    => 'Footer'
	) );

	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );

	if ( ! current_user_can( 'edit_posts' ) )
		show_admin_bar( false );

}

add_action( 'wp_head', 'wak_theme_wp_head' );
function wak_theme_wp_head() {

	if ( is_user_logged_in() ) return;

?>
<script type="text/javascript">
jQuery(function($) {

	$( 'form#wak-loginform' ).on( 'submit', function(e){

		e.preventDefault();

		var username_el = $( '#wak-username' );
		var password_el = $( '#wak-pass' );
		var submit_el   = $( '#wak-login-button' );

		if ( username_el.val() == '' ) {

			alert( 'Please provide your WAK username or email.' );
			return false;

		}

		if ( username_el.val() == '' ) {

			alert( 'Please provide your WAK account password.' );
			return false;

		}

		$.ajax({
			type       : "POST",
			data       : {
				action    : 'wak-login',
				uname     : username_el.val(),
				password  : password_el.val(),
				token     : '<?php echo wp_create_nonce( 'wak-login' ); ?>'
			},
			beforeSend : function() {

				submit_el.attr( 'disabled', 'disabled' ).val( 'Logging in...' );
				username_el.attr( 'disabled', 'disabled' );
				password_el.attr( 'disabled', 'disabled' );
	
			},
			dataType   : "JSON",
			url        : '<?php echo admin_url( 'admin-ajax.php' ); ?>',
			success    : function( response ) {

				if ( response.success )
					window.location.href = response.data;

				else {

					alert( response.data );

					submit_el.removeAttr( 'disabled' ).val( 'Login' );
					username_el.removeAttr( 'disabled' );
					password_el.removeAttr( 'disabled' ).val( '' );

				}

			}
		});

		return false;

	});

	$( 'form#wak-loginform-mobile' ).on( 'submit', function(e){

		e.preventDefault();

		var username_elm = $( '#wak-username-mobile' );
		var password_elm = $( '#wak-pass-mobile' );
		var submit_elm   = $( '#wak-login-button-mobile' );

		if ( username_elm.val() == '' ) {

			alert( 'Please provide your WAK username or email.' );
			return false;

		}

		if ( username_elm.val() == '' ) {

			alert( 'Please provide your WAK account password.' );
			return false;

		}

		$.ajax({
			type       : "POST",
			data       : {
				action    : 'wak-login',
				uname     : username_elm.val(),
				password  : password_elm.val(),
				token     : '<?php echo wp_create_nonce( 'wak-login' ); ?>'
			},
			beforeSend : function() {

				submit_elm.attr( 'disabled', 'disabled' ).val( 'Logging in...' );
				username_elm.attr( 'disabled', 'disabled' );
				password_elm.attr( 'disabled', 'disabled' );
	
			},
			dataType   : "JSON",
			url        : '<?php echo admin_url( 'admin-ajax.php' ); ?>',
			success    : function( response ) {

				if ( response.success )
					window.location.href = response.data;

				else {

					alert( response.data );

					submit_elm.removeAttr( 'disabled' ).val( 'Login' );
					username_elm.removeAttr( 'disabled' );
					password_elm.removeAttr( 'disabled' ).val( '' );

				}

			}
		});

		return false;

	});

});
</script>
<?php

}

/**
 * Body Class
 * @version 1.0
 */
add_filter( 'body_class', 'wak_body_classes' );
function wak_body_classes( $classes ) {

	if ( is_page() && ! ( is_front_page() || is_home() ) )
		$classes[] = 'top-content-margin';

	return $classes;
}

/**
 * Front End Script & Style Enqueue
 * @version 1.0
 */
add_action( 'wp_enqueue_scripts', 'wak_theme_enqueue_scripts' );
function wak_theme_enqueue_scripts() {

	wp_enqueue_style(
		'bootstrap',
		get_template_directory_uri() . '/css/bootstrap.css',
		array( 'dashicons' ),
		'3.3.4'
	);

	wp_enqueue_style(
		'bootstrap-theme',
		get_template_directory_uri() . '/css/bootstrap-theme.css',
		array(),
		'3.3.4'
	);

	wp_enqueue_script(
		'bootstrap',
		get_template_directory_uri() . '/js/bootstrap.js',
		array( 'jquery' ),
		'3.3.4'
	);

	wp_enqueue_script(
		'wak-nav',
		get_template_directory_uri() . '/js/nav.js',
		array( 'jquery' ),
		'1.0.1'
	);

	wp_enqueue_style( 'wak-style', get_stylesheet_uri() );

}

/**
 * Widgets & Sidebars Setup
 * @version 1.0
 */
add_action( 'widgets_init', 'wak_theme_widgets_init' );
function wak_theme_widgets_init() {

	register_sidebar( array(
		'name'          => 'Page',
		'id'            => 'sidebar',
		'description'   => 'Main site widget area.',
		'before_widget' => '<aside id="%1$s" class="widget light-bg %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => 'Profile',
		'id'            => 'profile',
		'description'   => 'Widgets to show when viewing a profile.',
		'before_widget' => '<aside id="%1$s" class="widget light-bg %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => 'Autoshops',
		'id'            => 'autoshop',
		'description'   => 'Widgets to show when viewing Autoshops.',
		'before_widget' => '<aside id="%1$s" class="widget light-bg %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => 'Footer',
		'id'            => 'footer',
		'description'   => 'Widgets to show on the bottom of the website.',
		'before_widget' => '<aside id="%1$s" class="col-md-6 col-xs-12 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

    register_sidebar( array(
		'name'          => 'Blog',
		'id'            => 'blog',
		'description'   => 'Widgets to show on the main blog page.',
		'before_widget' => '<aside id="%1$s" class="col-md-6 col-xs-12 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => 'Posts',
		'id'            => 'posts',
		'description'   => 'Widgets to show when viewing a single blog post entry.',
		'before_widget' => '<aside id="%1$s" class="col-md-6 col-xs-12 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => 'Resources',
		'id'            => 'resources',
		'description'   => 'Widgets to show when viewing the Resources page or any child page.',
		'before_widget' => '<aside id="%1$s" class="col-md-6 col-xs-12 %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );

}

/**
 * 
 * @version 1.0
 */
add_filter( 'avatar_defaults', 'wak_theme_default_avatar' );
function wak_theme_default_avatar( $avatar_defaults ) {

	$myavatar = get_template_directory_uri() . '/images/default-avatar.png';
	$avatar_defaults[ $myavatar ] = "WAK";

	return $avatar_defaults;

}

/**
 * Restrict Admin Access
 * @version 1.0
 */
add_action( 'admin_init', 'wak_theme_restrict_admin_access' );
function wak_theme_restrict_admin_access() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

	if ( ! current_user_can( 'edit_posts' ) ) {

		wp_redirect( home_url( '/' ) );
		exit;

	}

}

/**
 * Adjust Main Menu
 * @version 1.1
 */
add_filter( 'wp_nav_menu_items', 'wak_theme_top_menu_items', 10, 2 );
function wak_theme_top_menu_items( $items, $args ) {

	// Menu for members
	if ( $args->container_id == 'wak-members-nav' ) {

		$items .= '<li class="right"><a href="' . wak_theme_get_profile_url() . '" class="click" title="My Account">My Account</a></li>';
		$items .= '<li><a href="' . esc_url( wp_logout_url( home_url( '/' ) ) ) . '">Logout</a></li>';

	}

	elseif ( $args->container_id == 'wak-visitors-nav' ) {

		$prefs = wak_registration_plugin_settings();

		$form = '<form class="form form-inline wak-loginform" id="wak-loginform" method="post" action="">';

		$form .= '<div class="form-group"><input type="text" class="form-control" name="email" id="wak-username" value="" placeholder="Email" /><small class="hidden-xs">&nbsp;</small></div>';
		$form .= '<div class="form-group"><input type="password" class="form-control" name="pwd" id="wak-pass" value="" placeholder="Password" /><small><a href="' . get_permalink( $prefs['recover_page_id'] ) . '">Forgot password</a></small></div>';
		$form .= '<div class="form-group"><input type="submit" class="btn btn-danger" id="wak-login-button" value="LOG IN" /><small class="hidden-xs">&nbsp;</small></div>';

		$form .= '</form>';

		$items .= '<li class="right">' . $form . '</li>';

	}

	return $items;

}

/**
 * Remove Unused Admin Menu Items
 * @version 1.0
 */
add_action( 'admin_menu', 'wak_theme_remove_admin_menu_items', 999 );
function wak_theme_remove_admin_menu_items() {

	//remove_menu_page( 'plugins.php' );
	//remove_menu_page( 'tools.php' );

	remove_menu_page( 'edit.php?post_type=vc_grid_item' );
	remove_menu_page( 'admin.php?page=themepunch-google-fonts' );

	remove_submenu_page( 'themes.php', 'customize.php?return=' . urlencode( $_SERVER['REQUEST_URI'] ) );
	remove_submenu_page( 'themes.php', 'theme-editor.php' );
	//remove_submenu_page( 'themes.php', 'nav-menus.php' );
	//remove_submenu_page( 'themes.php', '' );

}

/**
 * 
 * @version 1.0
 */
add_action( 'admin_head', 'wak_theme_adjust_admin_style' );
function wak_theme_adjust_admin_style() {

?>
<style type="text/css">
table.wp-list-table.users th#autoshops { width: 120px; }
table.wp-list-table.users th#reviews { width: 100px; }
table.wp-list-table.users th#cars { width: 80px; }
</style>
<?php

}

/**
 * Adjust WP Login page
 * @version 1.0
 */
add_action( 'login_head', 'wak_theme_wp_login_css' );
function wak_theme_wp_login_css() {

?>
<style type="text/css">
body.login {
	background-color: white !important;
}
#login h1 a {
	background-image: url('<?php echo esc_url( get_template_directory_uri() . '/images/large-logo-full.png' ); ?>');
	background-size: 200px;
	height: 126px;
	width: 215px;
	margin-bottom: -1px;
}
#login #loginform {
	margin-top: 0;
	border-top: 1px solid #dedede;
	box-shadow: none;
}
body #backtoblog, body #nav { display: none; }
#wp-submit, #wp-submit:hover, #wp-submit:active { background-color: #EC008B; box-shadow: none; border-color: #EC008B; }
</style>
<?php

}

/**
 * Custom WP Login Processor
 * @version 1.0
 */
add_action( 'wp_ajax_nopriv_wak-login', 'wak_theme_login_processor' );
function wak_theme_login_processor() {

	// Security
	//check_ajax_referer( 'wak-login', 'token' );

	$username = sanitize_text_field( $_POST['uname'] );
	$password = sanitize_text_field( $_POST['password'] );

	if ( $username == '' )
		wp_send_json_error( 'Please provide your WAK username or email.' );

	if ( $password == '' )
		wp_send_json_error( 'Please provide your WAK account password.' );

	$requested_user = get_user_by( 'login', $username );

	if ( ! isset( $requested_user->ID ) )
		$requested_user = get_user_by( 'email', $username );

	if ( ! isset( $requested_user->ID ) )
		wp_send_json_error( 'Incorrect login details. Please check your details and try again.' );

	// Begin Authentication
	$credentials = array(
		'user_login'    => $requested_user->user_login,
		'user_password' => $password,
		'remember'      => false
	);

	do_action_ref_array( 'wp_authenticate', array( &$credentials['user_login'], &$credentials['user_password'] ) );

	$secure_cookie = is_ssl();
	$secure_cookie = apply_filters( 'secure_signon_cookie', $secure_cookie, $credentials );

	global $auth_secure_cookie;

	$auth_secure_cookie = $secure_cookie;

	add_filter( 'authenticate', 'wp_authenticate_cookie', 30, 3 );

	$user = wp_authenticate( $credentials['user_login'], $credentials['user_password'] );

	// Incorrect password
	if ( is_wp_error( $user ) )
		wp_send_json_error( 'Incorrect login details. Please check your details and try again.' );

	// Authenticated
	wp_set_auth_cookie( $user->ID, $credentials['remember'], $secure_cookie );
	do_action( 'wp_login', $user->user_login, $user );

	wp_send_json_success( wak_theme_get_profile_url( $user ) );

}

/**
 * 
 * @version 1.0
 */
add_action( 'login_redirect', 'wak_theme_adjust_login_url', 10, 3 );
function wak_theme_adjust_login_url( $redirect_to, $request, $user ) {

	return wak_theme_get_profile_url( $user );

}

/**
 * Allow Email Login
 * @version 1.0
 */
add_filter( 'authenticate', 'wak_theme_allow_email_login', 20, 3 );
function wak_theme_allow_email_login( $user, $username, $password ) {
    if ( is_email( $username ) ) {
        $user = get_user_by( 'email', $username );
        if ( $user ) $username = $user->user_login;
    }
    return wp_authenticate_username_password( null, $username, $password );
}

add_action( 'wp_logout', 'wak_theme_redirect_after_logout' );
function wak_theme_redirect_after_logout() {

	return home_url( '/' );

}

/**
 * Display an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index views, or a div
 * element when on single views.
 *
 * @since Twenty Fifteen 1.0
 */
function wak_post_thumbnail() {
    if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
		return;
	}

	if ( is_singular() ) :
	?>

	<div class="post-thumbnail">
		<?php the_post_thumbnail(); ?>
	</div><!-- .post-thumbnail -->

	<?php else : ?>

	<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true">
		<?php
			the_post_thumbnail( 'post-thumbnail', array( 'alt' => get_the_title() ) );
		?>
	</a>

	<?php endif; // End is_singular()
}

if ( ! function_exists( 'is_tree' ) ) :
	function is_tree( $page_id ) {

		global $post;

		if ( is_page() && ( $post->post_parent == $page_id || is_page( $page_id ) ) ) 
			return true;

		return false;

	}
endif;

/**
 * 
 * @version 1.0
 */
add_filter( 'manage_users_sortable_columns', 'wak_user_sortable_columns' );
function wak_user_sortable_columns( $columns ) {
	$columns['registered'] = 'registered';
	return $columns;
}

/**
 * 
 * @version 1.0
 */
add_filter( 'manage_users_columns', 'wak_add_user_columns' );
function wak_add_user_columns( $defaults ) {
	$defaults['registered'] = 'Registered';
	return $defaults;
}

/**
 * 
 * @version 1.0
 */
add_action( 'manage_users_custom_column', 'wak_add_custom_user_columns', 15, 3 );
function wak_add_custom_user_columns( $value, $column_name, $user_id ) {
	if ( $column_name == 'registered' ) {
		$user = get_userdata( $user_id );
		return date( 'm/d/Y', strtotime( $user->user_registered ) );
	}
	return $value;
}

?>