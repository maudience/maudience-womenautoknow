<?php
/**
 * Plugin Name:  WAK - Auto Shops
 * Plugin URI:   http://www.maudience.com/
 * Description:  Custom plugin that manages Auto Shops, reviews and ratings. Do not disable!
 * Version:      1.0
 * Author:       Gabriel S Merovingi
 * Author URI:   http://www.merovingi.com
 * Text Domain:  wakauto
 * Domain Path:  /lang
 */
define( 'WAK_AUTOSHOPS_VER',      '1.0' );
define( 'WAK_AUTOSHOPS',          __FILE__ );
define( 'WAK_AUTOSHOPS_ROOT',     plugin_dir_path( WAK_AUTOSHOPS ) );
define( 'WAK_AUTOSHOPS_CLASSES',  WAK_AUTOSHOPS_ROOT . 'classes/' );
define( 'WAK_AUTOSHOPS_INCLUDES', WAK_AUTOSHOPS_ROOT . 'includes/' );
define( 'WAK_AUTOSHOPS_ASSETS',   WAK_AUTOSHOPS_ROOT . 'assets/' );
define( 'WAK_AUTOSHOPS_TEMPLATES', WAK_AUTOSHOPS_ROOT . 'templates/' );

require_once WAK_AUTOSHOPS_INCLUDES . 'wak-autoshops-plugin.php';

require_once WAK_AUTOSHOPS_INCLUDES . 'wak-autoshops-functions.php';
require_once WAK_AUTOSHOPS_INCLUDES . 'wak-reviews-functions.php';

register_activation_hook(   WAK_AUTOSHOPS, 'wak_autoshops_plugin_activation' );
register_deactivation_hook( WAK_AUTOSHOPS, 'wak_autoshops_plugin_deactivation' );

/**
 * Start Up
 * @since 1.0
 * @version 1.0
 */
add_action( 'plugins_loaded', 'wak_autoshops_plugins_loaded' );
if ( ! function_exists( 'wak_autoshops_plugins_loaded' ) ) :
	function wak_autoshops_plugins_loaded() {

		// Load Translation
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wakauto' );
		load_textdomain( 'wakauto', WP_LANG_DIR . "/wak-autoshops/wakauto-$locale.mo" );
		load_plugin_textdomain( 'wakauto', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		// Set review database table
		global $wpdb, $wak_review_db;

		$wak_review_db = $wpdb->prefix . 'autoshop_reviews';

		require_once WAK_AUTOSHOPS_CLASSES . 'class.query-autoshops.php';
		require_once WAK_AUTOSHOPS_CLASSES . 'class.query-reviews.php';

		add_action( 'wak_my_shops_author_tab',   'wak_autoshops_my_shops_tab' );
		add_action( 'wak_my_reviews_author_tab', 'wak_myreviews_my_shops_tab' );

	}
endif;

/**
 * WordPress Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'init', 'wak_autoshops_init' );
if ( ! function_exists( 'wak_autoshops_init' ) ) :
	function wak_autoshops_init() {

		// Register custom post type
		wak_setup_autoshop_object();

		// Autoshops Management
		require_once WAK_AUTOSHOPS_INCLUDES . 'wak-autoshops-management.php';

		add_filter( 'excerpt_more',          'wak_autoshop_excerpt_more' );
		add_filter( 'post_class',            'wak_autoshops_post_classes' );
		add_action( 'restrict_manage_posts', 'wak_autoshops_admin_filter_option' );
		add_action( 'parse_query',           'wak_autoshops_admin_filter_parse' );
		add_filter( 'template_include',      'wak_autoshops_template_redirect' );

		// AJAX
		require_once WAK_AUTOSHOPS_INCLUDES . 'wak-autoshops-ajax.php';

		add_action( 'wp_ajax_no_priv_wak-load-more-autoshops', 'wak_ajax_load_more_autoshops' );

		if ( ! is_user_logged_in() ) return;

		add_action( 'wp_ajax_wak-load-more-autoshops',         'wak_ajax_load_more_autoshops' );

		add_action( 'wp_ajax_wak-add-new-autoshop',            'wak_ajax_load_new_autoshop_form' );
		add_action( 'wp_ajax_wak-edit-autoshop',               'wak_ajax_edit_autoshop' );
		add_action( 'wp_ajax_wak-update-autoshop',             'wak_ajax_update_autoshop' );
		add_action( 'wp_ajax_wak-submit-new-autoshop',         'wak_ajax_submit_autoshop' );

		add_action( 'wp_footer',             'wak_autoshops_wp_footer' );

		// AJAX
		require_once WAK_AUTOSHOPS_INCLUDES . 'wak-reviews-ajax.php';

		add_action( 'wp_ajax_wak-new-autoshop-review',    'wak_ajax_new_review' );
		add_action( 'wp_ajax_wak-submit-autoshop-review', 'wak_ajax_submit_review' );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

		// Review Management
		require_once WAK_AUTOSHOPS_INCLUDES . 'wak-reviews-management.php';

		add_action( 'wp_footer', 'wak_reviews_wp_footer' );

		add_action( 'admin_menu', 'wak_reviews_admin_menu' );

	}
endif;

/**
 * WordPress Post Sort
 * @since 1.0
 * @version 1.0
 */
add_action( 'pre_get_posts', 'wak_autoshops_pre_get_posts' );
if ( ! function_exists( 'wak_autoshops_pre_get_posts' ) ) :
	function wak_autoshops_pre_get_posts( $query ) {

		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || is_admin() ) return;

		if ( ! $query->is_main_query() || ! $query->is_post_type_archive( 'autoshops' ) ) return;

		$query->set( 'meta_key', 'pledged_date' );
		$query->set( 'orderby', array(
			'meta_value' => 'DESC',
			'title'      => 'ASC'
		) );

	}
endif;

/**
 * WordPress Admin Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'admin_init', 'wak_autoshops_admin_init' );
if ( ! function_exists( 'wak_autoshops_admin_init' ) ) :
	function wak_autoshops_admin_init() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

		add_filter( 'enter_title_here',                     'wak_autoshops_title_here' );
		add_filter( 'manage_autoshops_posts_columns',       'wak_autoshops_column_headers' );
		add_action( 'manage_autoshops_posts_custom_column', 'wak_autoshops_column_content', 10, 2 );
		add_action( 'save_post_autoshops',                  'wak_autoshops_save_autoshop' );

		add_action( 'manage_autoshops_posts_custom_column', 'wak_reviews_autoshops_column_content', 10, 2 );

		register_setting( 'wak-autoshops-prefs', 'wak_autoshop_plugin_prefs', 'wak_sanitize_autoshop_plugin_settings' );

		add_filter( 'manage_users_columns',       'wak_autoshop_user_column_headers', 10 );
		add_action( 'manage_users_custom_column', 'wak_autoshop_user_column_content', 10, 3 );

		add_filter( 'manage_users_columns',       'wak_reviews_user_column_headers', 20 );
		add_action( 'manage_users_custom_column', 'wak_reviews_user_column_content', 20, 3 );

	}
endif;

/**
 * Front Enqueue
 * @since 1.0
 * @version 1.0
 */
add_action( 'wp_enqueue_scripts', 'wak_autoshops_front_enqueue' );
if ( ! function_exists( 'wak_autoshops_front_enqueue' ) ) :
	function wak_autoshops_front_enqueue() {

		// Enqueue Styles first
		wp_enqueue_style(
			'wak-autoshops',
			plugins_url( 'assets/css/autoshops.css', WAK_AUTOSHOPS ),
			array(),
			WAK_AUTOSHOPS_VER . '.2'
		);

		wp_enqueue_style(
			'wak-reviews',
			plugins_url( 'assets/css/reviews.css', WAK_AUTOSHOPS ),
			array(),
			WAK_AUTOSHOPS_VER . '.1'
		);

		// Register, localize and enqueue scripts
		wp_register_script(
			'wak-reviews',
			plugins_url( 'assets/js/reviews.js', WAK_AUTOSHOPS ),
			array( 'jquery' ),
			WAK_AUTOSHOPS_VER . '.1'
		);

		wp_localize_script(
			'wak-reviews',
			'WAKReviews',
			array(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'token'      => wp_create_nonce( 'wak-reviews-new' ),
				'submitting' => esc_js( __( 'Submitting...', '' ) ),
				'loading'    => esc_js( '<h1 class="text-center pink"><i class="fa fa-spinner fa-spin"></i></h1><p class="text-center">' . __( 'Loading review form...', '' ) . '</p>' )
			)
		);

		wp_enqueue_script( 'wak-reviews' );

		wp_register_script(
			'wak-autoshops',
			plugins_url( 'assets/js/autoshops.js', WAK_AUTOSHOPS ),
			array( 'jquery' ),
			WAK_AUTOSHOPS_VER . '.1'
		);

		wp_localize_script(
			'wak-autoshops',
			'WAKAutoshop',
			array(
				'ajaxurl'        => admin_url( 'admin-ajax.php' ),
				'token'          => wp_create_nonce( 'wak-more-autoshops' ),
				'label'          => esc_js( __( 'Load More Auto Shops', '' ) ),
				'loading'        => esc_js( __( 'loading autoshops ...', '' ) ),
				'end'            => esc_js( '<p class="text-center no-more-entries">All auto shops listed.</p>' ),
				'newtoken'       => wp_create_nonce( 'wak-add-new-autoshop' ),
				'newloading'     => esc_js( '<h1 class="text-center pink"><i class="fa fa-spinner fa-spin"></i></h1><p class="text-center">' . __( 'Loading form...', '' ) . '</p>' ),
				'submitting'     => esc_js( __( 'Submitting...', '' ) ),
				'edittoken'      => wp_create_nonce( 'wak-edit-autoshop' ),
				'goptoken'       => wp_create_nonce( 'wak-load-premium-form' ),
				'premiumloading' => esc_js( '<h1 class="text-center pink"><i class="fa fa-spinner fa-spin blue"></i></h1><p class="text-center">' . __( 'Loading form...', '' ) . '</p>' )
			)
		);

		wp_enqueue_script( 'wak-autoshops' );

	}
endif;

if ( ! function_exists( 'wak_clean_phone_number' ) ) :
	function wak_clean_phone_number( $string ) {

		if ( strlen( $string ) < 10 ) return $string;

		$string = str_replace( array( ' ', '-', '(', ')', '_' ), '', $string );
		$number = str_split( $string, 3 );

		$count = count( $number );
		if ( $count == 4 )
			return '(' . $number[0] . ') ' . $number[1] . '-' . $number[2] . $number[3];

		elseif ( $count == 3 )
			return '(' . $number[0] . ') ' . $number[1] . '-' . $number[2];

		return $string;

	}
endif;

?>