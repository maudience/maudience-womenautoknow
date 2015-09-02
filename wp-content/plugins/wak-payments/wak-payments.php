<?php
/**
 * Plugin Name:  WAK - Payments
 * Plugin URI:   http://www.maudience.com/
 * Description:  Custom plugin that manages WAK premium listing payments.
 * Version:      1.0
 * Author:       Gabriel S Merovingi
 * Author URI:   http://www.merovingi.com
 * Text Domain:  wakpayments
 * Domain Path:  /lang
 */
define( 'WAK_PAYMENTS_VER',       '1.0' );
define( 'WAK_PAYMENTS',           __FILE__ );
define( 'WAK_PAYMENTS_ROOT',      plugin_dir_path( WAK_PAYMENTS ) );
define( 'WAK_PAYMENTS_CLASSES',   WAK_PAYMENTS_ROOT . 'classes/' );
define( 'WAK_PAYMENTS_INCLUDES',  WAK_PAYMENTS_ROOT . 'includes/' );
define( 'WAK_PAYMENTS_ASSETS',    WAK_PAYMENTS_ROOT . 'assets/' );

require_once WAK_PAYMENTS_INCLUDES . 'wak-payments-functions.php';
require_once WAK_PAYMENTS_INCLUDES . 'wak-payments-plugin.php';

register_activation_hook(   WAK_PAYMENTS, 'wak_payments_plugin_activation' );
register_deactivation_hook( WAK_PAYMENTS, 'wak_payments_plugin_deactivation' );

/**
 * Start Up
 * @since 1.0
 * @version 1.0
 */
add_action( 'plugins_loaded', 'wak_payments_plugins_loaded' );
if ( ! function_exists( 'wak_payments_plugins_loaded' ) ) :
	function wak_payments_plugins_loaded() {

		// Load Translation
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wakpayments' );
		load_textdomain( 'wakpayments', WP_LANG_DIR . "/wak-payments/wakpayments-$locale.mo" );
		load_plugin_textdomain( 'wakpayments', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		// Set payments database table
		global $wpdb, $wak_payments_db;

		$wak_payments_db = $wpdb->prefix . 'payments';

		require_once WAK_PAYMENTS_CLASSES . 'class.query-payments.php';

		add_filter( 'wak_autoshop_actions', 'wak_payments_autoshop_actions', 10, 5 );
		add_action( 'wak_my_autoshop_tabs', 'wak_autoshops_payments_tab', 10, 3 );
		add_action( 'wak_my_autoshop_tab_content', 'wak_autoshops_payment_tab_content', 10, 3 );
		add_action( 'wak_cancel_autoshop_premium', 'wak_cancel_autoshop_premium_by_admin' );

		require_once WAK_PAYMENTS_INCLUDES . 'gateways/authorize-net/wak-authorize-net.php';

	}
endif;

/**
 * WordPress Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'init', 'wak_payments_init' );
if ( ! function_exists( 'wak_payments_init' ) ) :
	function wak_payments_init() {

		//add_action( 'wak_my_cars_author_tab', 'wak_mycars_author_tab' );

		if ( ! is_user_logged_in() ) return;

		require_once WAK_PAYMENTS_INCLUDES . 'wak-payments-management.php';

		add_action( 'admin_menu', 'wak_payments_admin_menu' );
		add_action( 'wp_footer',  'wak_payments_wp_footer' );

		//add_filter( 'template_include', 'wak_mycars_car_log_template' );

		// AJAX
		require_once WAK_PAYMENTS_INCLUDES . 'wak-payments-ajax.php';

		add_action( 'wp_ajax_wak-load-go-premium',  'wak_ajax_load_go_premium' );
		add_action( 'wp_ajax_wak-upgrade-autoshop', 'wak_ajax_upgrade_autoshop' );

	}
endif;

/**
 * WordPress Admin Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'admin_init', 'wak_payments_admin_init' );
if ( ! function_exists( 'wak_payments_admin_init' ) ) :
	function wak_payments_admin_init() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

		register_setting( 'wak-payments-prefs', 'wak_payments_plugin_prefs', 'wak_sanitize_payment_plugin_settings' );
		register_setting( 'wak-payment-plans', 'wak_payment_plans_prefs', 'wak_sanitize_payment_plans_settings' );

	}
endif;

/**
 * Front Enqueue
 * @since 1.0
 * @version 1.0
 */
add_action( 'wp_enqueue_scripts', 'wak_payments_front_enqueue' );
if ( ! function_exists( 'wak_payments_front_enqueue' ) ) :
	function wak_payments_front_enqueue() {

		if ( ! is_user_logged_in() ) return;

		// Enqueue Styles first
		wp_enqueue_style(
			'wak-payments',
			plugins_url( 'assets/css/payments.css', WAK_PAYMENTS ),
			array(),
			WAK_PAYMENTS_VER . '.1'
		);

		wp_register_script(
			'wak-payments',
			plugins_url( 'assets/js/payments.js', WAK_PAYMENTS ),
			array( 'jquery' ),
			WAK_PAYMENTS_VER . '.1'
		);

		wp_localize_script(
			'wak-payments',
			'WAKPayments',
			array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'loadtoken'     => wp_create_nonce( 'wak-load-payment-form' ),
				'loading'       => esc_js( '<h1 class="text-center pink"><i class="fa fa-spinner fa-spin blue"></i></h1><p class="text-danger text-center">' . __( 'loading ...', 'wakpayments' ) . '</p>' ),
				'submitting'    => esc_js( '<h1 class="text-center pink"><i class="fa fa-spinner fa-spin blue"></i></h1><p class="text-danger text-center">' . __( 'processing payment ...', 'wakpayments' ) . '</p>' )
			)
		);

		wp_enqueue_script( 'wak-payments' );

	}
endif;

?>