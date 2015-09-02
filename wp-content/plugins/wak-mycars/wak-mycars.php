<?php
/**
 * Plugin Name:  WAK - My Cars
 * Plugin URI:   http://www.maudience.com/
 * Description:  Custom plugin that manages WAK members cars and car logs.
 * Version:      1.0
 * Author:       Gabriel S Merovingi
 * Author URI:   http://www.merovingi.com
 * Text Domain:  wakmycars
 * Domain Path:  /lang
 */
define( 'WAK_MYCARS_VER',       '1.0' );
define( 'WAK_MYCARS',           __FILE__ );
define( 'WAK_MYCARS_ROOT',      plugin_dir_path( WAK_MYCARS ) );
define( 'WAK_MYCARS_CLASSES',   WAK_MYCARS_ROOT . 'classes/' );
define( 'WAK_MYCARS_INCLUDES',  WAK_MYCARS_ROOT . 'includes/' );
define( 'WAK_MYCARS_ASSETS',    WAK_MYCARS_ROOT . 'assets/' );
define( 'WAK_MYCARS_TEMPLATES', WAK_MYCARS_ROOT . 'templates/' );
define( 'WAK_MYCARS_TABS',      WAK_MYCARS_ROOT . 'tabs/' );

require_once WAK_MYCARS_INCLUDES . 'wak-mycars-functions.php';
require_once WAK_MYCARS_INCLUDES . 'wak-mycars-plugin.php';

register_activation_hook(   WAK_MYCARS, 'wak_mycars_plugin_activation' );
register_deactivation_hook( WAK_MYCARS, 'wak_mycars_plugin_deactivation' );

/**
 * Start Up
 * @since 1.0
 * @version 1.0
 */
add_action( 'plugins_loaded', 'wak_mycars_plugins_loaded' );
if ( ! function_exists( 'wak_mycars_plugins_loaded' ) ) :
	function wak_mycars_plugins_loaded() {

		// Load Translation
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wakmycars' );
		load_textdomain( 'wakmycars', WP_LANG_DIR . "/wak-mycars/wakmycars-$locale.mo" );
		load_plugin_textdomain( 'wakmycars', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		// Set review database table
		global $wpdb, $wak_mycars_db, $wak_mycar_log_db, $wak_mycar_reminder_db;

		$wak_mycars_db         = $wpdb->prefix . 'my_cars';
		$wak_mycar_log_db      = $wpdb->prefix . 'my_car_log';
		$wak_mycar_reminder_db = $wpdb->prefix . 'my_car_reminders';

		require_once WAK_MYCARS_CLASSES . 'class.query-cars.php';
		require_once WAK_MYCARS_CLASSES . 'class.query-maintenance-log.php';

		// Profile Tabs
		require_once WAK_MYCARS_TABS . 'wak-mycars-log-tab.php';
		require_once WAK_MYCARS_TABS . 'wak-mycars-tab.php';

		add_action( 'wak_my_car_log_tab', 'wak_mycars_profile_log_tab' );

	}
endif;

/**
 * WordPress Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'init', 'wak_mycars_init' );
if ( ! function_exists( 'wak_mycars_init' ) ) :
	function wak_mycars_init() {

		add_action( 'wak_my_cars_author_tab', 'wak_mycars_author_tab' );

		require_once WAK_MYCARS_INCLUDES . 'wak-mycars-management.php';

		add_action( 'admin_menu', 'wak_mycars_admin_menu' );

		if ( ! is_user_logged_in() ) return;

		wak_update_maintenance_log();

		add_filter( 'template_include', 'wak_mycars_car_log_template' );

		// AJAX
		require_once WAK_MYCARS_INCLUDES . 'wak-mycars-ajax.php';

		add_action( 'wp_ajax_wak-edit-mycar',     'wak_ajax_load_edit_car_form' );
		add_action( 'wp_ajax_wak-add-new-car',    'wak_ajax_load_add_new_car' );
		add_action( 'wp_ajax_wak-submit-new-car', 'wak_ajax_submit_new_car' );
		add_action( 'wp_ajax_wak-delete-my-car',  'wak_ajax_delete_my_car' );

		//add_action( 'wp_ajax_wak-get-car-log',    'wak_ajax_get_car_log' );
		//add_action( 'wp_ajax_wak-add-to-car-log', 'wak_ajax_add_to_car_log' );
		//add_action( 'wp_ajax_wak-delete-car-log-entry', 'wak_ajax_delete_car_log_entry' );

		add_action( 'wp_ajax_wak-get-car-model',  'wak_ajax_get_car_models' );
		add_action( 'wp_ajax_wak-get-car-year',   'wak_ajax_get_car_years' );

	}
endif;

/**
 * WordPress Widgets Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'widgets_init', 'wak_mycars_widgets_init' );
if ( ! function_exists( 'wak_mycars_widgets_init' ) ) :
	function wak_mycars_widgets_init() {

		// Register Widgets
		require_once WAK_MYCARS_INCLUDES . 'wak-mycars-widgets.php';
		register_widget( 'WAK_Add_To_Maintenance_Log' );

	}
endif;

/**
 * WordPress Admin Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'admin_init', 'wak_mycars_admin_init' );
if ( ! function_exists( 'wak_mycars_admin_init' ) ) :
	function wak_mycars_admin_init() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

		add_filter( 'manage_users_columns',       'wak_mycars_user_column_headers', 30 );
		add_action( 'manage_users_custom_column', 'wak_mycars_user_column_content', 30, 3 );

	}
endif;

/**
 * Front Enqueue
 * @since 1.0
 * @version 1.0
 */
add_action( 'wp_enqueue_scripts', 'wak_mycars_front_enqueue' );
if ( ! function_exists( 'wak_mycars_front_enqueue' ) ) :
	function wak_mycars_front_enqueue() {

		if ( ! is_user_logged_in() ) return;

		// Enqueue Styles first
		wp_enqueue_style(
			'wak-mycars',
			plugins_url( 'assets/css/mycars.css', WAK_MYCARS ),
			array(),
			WAK_MYCARS_VER . '.1'
		);

		wp_register_script(
			'wak-mycars',
			plugins_url( 'assets/js/mycars.js', WAK_MYCARS ),
			array( 'jquery' ),
			WAK_MYCARS_VER . '.1'
		);

		wp_localize_script(
			'wak-mycars',
			'WAKCars',
			array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'token'         => wp_create_nonce( 'wak-mycar-edit' ),
				'loading'       => esc_js( '<h1 class="text-center pink"><i class="fa fa-refresh fa-spin"></i></h1><p class="text-danger text-center">' . __( 'loading car details ...', '' ) . '</p>' ),
				'newtoken'      => wp_create_nonce( 'wak-add-new-car' ),
				'newloading'    => esc_js( '<h1 class="text-center pink"><i class="fa fa-refresh fa-spin"></i></h1><p class="text-danger text-center">' . __( 'loading form ...', '' ) . '</p>' ),
				'confirmdelete' => esc_js( __( 'Are you sure you want to delete this car? This can not be undone!', '' ) ),
				'deleting'      => esc_js( __( 'Deleting', '' ) ),
				'faileddelete'  => esc_js( __( 'Failed to delete car. Please reload this page and try again.', '' ) ),
				'submitting'    => esc_js( __( 'Submitting', '' ) ),
				'logtoken'      => wp_create_nonce( 'wak-mycar-get-log' ),
				'logloading'    => esc_js( '<h1 class="text-center pink"><i class="fa fa-refresh fa-spin"></i></h1><p class="text-danger text-center">' . __( 'loading car log ...', '' ) . '</p>' ),
				'addlogtoken'   => wp_create_nonce( 'wak-mycar-add-to-log' ),
				'deletetoken'   => wp_create_nonce( 'wak-delete-car' ),
				'addingentry'   => esc_js( __( 'Updating Log ...', '' ) ),
				'adddone'       => esc_js( __( 'Done!', '' ) ),
				'dcletoken'     => wp_create_nonce( 'wak-delete-car-log-entry' ),
				'deletingentry' => esc_js( '<i class="fa fa-spinner fa-spin"></i>' )
			)
		);

		wp_enqueue_script( 'wak-mycars' );

	}
endif;

?>