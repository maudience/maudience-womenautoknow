<?php
/**
 * Plugin Name:  WAK - Registration
 * Plugin URI:   http://www.maudience.com/
 * Description:  Custom plugin that manages WAK registrations.
 * Version:      1.0.1
 * Author:       Gabriel S Merovingi
 * Author URI:   http://www.merovingi.com
 * Text Domain:  wakregister
 * Domain Path:  /lang
 */
define( 'WAK_REGISTER_VER',       '1.0.1' );
define( 'WAK_REGISTER',           __FILE__ );
define( 'WAK_REGISTER_ROOT',      plugin_dir_path( WAK_REGISTER ) );
define( 'WAK_REGISTER_CLASSES',   WAK_REGISTER_ROOT . 'classes/' );
define( 'WAK_REGISTER_INCLUDES',  WAK_REGISTER_ROOT . 'includes/' );
define( 'WAK_REGISTER_ASSETS',    WAK_REGISTER_ROOT . 'assets/' );
define( 'WAK_REGISTER_TEMPLATES', WAK_REGISTER_ROOT . 'templates/' );

require_once WAK_REGISTER_INCLUDES . 'wak-registration-functions.php';
require_once WAK_REGISTER_INCLUDES . 'wak-registration-plugin.php';

register_activation_hook(   WAK_REGISTER, 'wak_registration_plugin_activation' );
register_deactivation_hook( WAK_REGISTER, 'wak_registration_plugin_deactivation' );

/**
 * Start Up
 * @since 1.0
 * @version 1.0
 */
add_action( 'plugins_loaded', 'wak_registration_plugins_loaded' );
if ( ! function_exists( 'wak_registration_plugins_loaded' ) ) :
	function wak_registration_plugins_loaded() {

		// Load Translation
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wakregister' );
		load_textdomain( 'wakregister', WP_LANG_DIR . "/wak-registration/wakregister-$locale.mo" );
		load_plugin_textdomain( 'wakregister', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		// Set database table
		global $wpdb, $wak_pending_registrations_db;

		$wak_pending_registrations_db = $wpdb->prefix . 'pending_registrations';

		require_once WAK_REGISTER_CLASSES . 'class.query-registrations.php';

	}
endif;

/**
 * WordPress Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'init', 'wak_registration_init' );
if ( ! function_exists( 'wak_registration_init' ) ) :
	function wak_registration_init() {

		// Register Shortcodes
		require_once WAK_REGISTER_INCLUDES . 'wak-registration-shortcodes.php';
		add_shortcode( 'wak_registration', 'wak_registration_shortcode' );

		// Register Shortcodes with Visual Composer
		if ( function_exists( 'vc_map' ) ) {

			require_once WAK_REGISTER_INCLUDES . 'wak-registration-vc.php';
			wak_registration_vc_map_shortcodes();

		}

		global $wak_registration;

		$wak_registration          = new stdClass();
		$wak_registration->result  = false;
		$wak_registration->signup  = '';
		$wak_registration->errors  = array();

			//wp_die( '<pre>' . print_r( $_POST, true ) . '</pre>' );

		if ( isset( $_POST['wak_new_driver'] ) && count( $_POST['wak_new_driver'] ) > 1 ) {

			$wak_registration->signup = 'driver';
			$result = wak_register_new_driver( $_POST['wak_new_driver'] );

			if ( ! is_array( $result ) )
				$wak_registration->result = 'new-driver';
			else
				$wak_registration->errors = $result;

		}

		elseif ( isset( $_POST['wak_new_shop'] ) && count( $_POST['wak_new_shop'] ) > 1 ) {

			$wak_registration->signup = 'autoshop';
			$result = wak_register_new_shop( $_POST['wak_new_shop'] );

			if ( ! is_array( $result ) )
				$wak_registration->result = ( ( $_POST['wak_new_shop']['type'] == 0 ) ? 'new-shop' : 'new-paid-shop' );
			else
				$wak_registration->errors = $result;

		}

		global $wak_password_recovery;

		$wak_password_recovery = false;

		require_once WAK_REGISTER_INCLUDES . 'wak-registration-management.php';

		add_action( 'wak_login_form', 'wak_register_login_form_reset' );

		if ( isset( $_POST['wak-username-email'] ) && $_POST['wak-username-email'] != '' )
			wak_recover_password();

		add_filter( 'template_include',  'wak_registration_activation_template' );
		add_action( 'template_redirect', 'wak_registration_template_redirects' );

		if ( ! is_user_logged_in() ) return;

		add_action( 'admin_menu', 'wak_registration_admin_menu' );

	}
endif;

/**
 * WordPress Admin Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'wp_head', 'wak_registration_wp_head' );
if ( ! function_exists( 'wak_registration_wp_head' ) ) :
	function wak_registration_wp_head() {

?>
<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?onload=waksignup&render=explicit&ver=1.0.1" async defer></script>
<?php

	}
endif;

/**
 * WordPress Admin Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'admin_init', 'wak_registration_admin_init' );
if ( ! function_exists( 'wak_registration_admin_init' ) ) :
	function wak_registration_admin_init() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

		register_setting( 'wak-registration-prefs', 'wak_registration_plugin_prefs', 'wak_sanitize_registration_plugin_settings' );

	}
endif;

/**
 * Front Enqueue
 * @since 1.0
 * @version 1.0
 */
add_action( 'wp_enqueue_scripts', 'wak_registration_front_enqueue' );
if ( ! function_exists( 'wak_registration_front_enqueue' ) ) :
	function wak_registration_front_enqueue() {

		$prefs = wak_registration_plugin_settings();

		if ( $prefs['recover_page_id'] > 0 && is_page( $prefs['recover_page_id'] ) )
			wp_enqueue_script(
				'wak-captcha',
				'https://www.google.com/recaptcha/api.js',
				array(),
				WAK_REGISTER_VER . '.1'
			);

		if ( ( $prefs['signup_page_id'] > 0 && is_page( $prefs['signup_page_id'] ) ) || ( $prefs['recover_page_id'] > 0 && is_page( $prefs['recover_page_id'] ) ) ) {

			// Enqueue Styles first
			wp_enqueue_style(
				'wak-registration',
				plugins_url( 'assets/css/registration.css', WAK_REGISTER ),
				array(),
				WAK_REGISTER_VER . '.1'
			);

			wp_register_script(
				'wak-registration',
				plugins_url( 'assets/js/registration.js', WAK_REGISTER ),
				array( 'jquery' ),
				WAK_REGISTER_VER . '.1'
			);

			wp_localize_script(
				'wak-registration',
				'WAKRegistration',
				array(
					'ajaxurl'       => admin_url( 'admin-ajax.php' ),
					'loadtoken'     => wp_create_nonce( 'wak-load-payment-form' )
				)
			);

			wp_enqueue_script( 'wak-registration' );

		}

	}
endif;

?>