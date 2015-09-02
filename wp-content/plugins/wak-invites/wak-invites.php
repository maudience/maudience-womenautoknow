<?php
/**
 * Plugin Name:  WAK - Invites
 * Plugin URI:   http://www.maudience.com/
 * Description:  Custom plugin that manages WAK invites by drivers and auto shops.
 * Version:      1.0.1
 * Author:       Gabriel S Merovingi
 * Author URI:   http://www.merovingi.com
 * Text Domain:  wakinvites
 * Domain Path:  /lang
 */
define( 'WAK_INVITES_VER',          '1.0.1' );
define( 'WAK_INVITES',              __FILE__ );
define( 'WAK_INVITES_ROOT',         plugin_dir_path( WAK_INVITES ) );
define( 'WAK_INVITES_CLASSES',      WAK_INVITES_ROOT . 'classes/' );
define( 'WAK_INVITES_INCLUDES',     WAK_INVITES_ROOT . 'includes/' );
define( 'WAK_INVITES_ASSETS',       WAK_INVITES_ROOT . 'assets/' );
define( 'WAK_INVITES_TEMPLATES',    WAK_INVITES_ROOT . 'templates/' );
define( 'WAK_INVITES_LEAD_PAGE_ID', 1850 );
define( 'WAK_INVITES_CF7_ID',       1851 );

require_once WAK_INVITES_INCLUDES . 'wak-invites-functions.php';
require_once WAK_INVITES_INCLUDES . 'wak-invites-plugin.php';

register_activation_hook(   WAK_INVITES, 'wak_invites_plugin_activation' );
register_deactivation_hook( WAK_INVITES, 'wak_invites_plugin_deactivation' );

/**
 * Start Up
 * @since 1.0
 * @version 1.0
 */
add_action( 'plugins_loaded', 'wak_invites_plugins_loaded' );
if ( ! function_exists( 'wak_invites_plugins_loaded' ) ) :
	function wak_invites_plugins_loaded() {

		// Load Translation
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wakinvites' );
		load_textdomain( 'wakinvites', WP_LANG_DIR . "/wak-invites/wakinvites-$locale.mo" );
		load_plugin_textdomain( 'wakinvites', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		global $wpdb, $wak_pending_invites, $wak_blocked_invites;

		$wak_pending_invites = $wpdb->prefix . 'pending_invites';
		$wak_blocked_invites = $wpdb->prefix . 'blocked_invites';

		add_action( 'template_redirect',      'wak_invites_lead_page_access' );
		add_action( 'wpcf7_before_send_mail', 'wak_invites_cf7_recipient' );

	}
endif;

/**
 * WordPress Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'init', 'wak_invites_init' );
if ( ! function_exists( 'wak_invites_init' ) ) :
	function wak_invites_init() {

		if ( isset( $_GET['unsubscribe'] ) && sanitize_email( $_GET['unsubscribe'] ) != '' && isset( $_GET['do'] ) && $_GET['do'] == 'no-more-invites' ) {

			$email  = urldecode( $_GET['unsubscribe'] );
			$result = block_email_from_invites( $email );
			if ( $result )
				wp_die( '<h1>Email Successfully Unsubscribed</h1><p>Your email will not receive any more invites.</p>' );

		}

		if ( ! is_user_logged_in() ) return;

		require_once WAK_INVITES_INCLUDES . 'wak-invites-management.php';

		add_action( 'admin_menu', 'wak_invites_admin_menu' );

		require_once WAK_INVITES_INCLUDES . 'wak-invites-ajax.php';

		add_action( 'wp_ajax_wak-send-new-invite', 'wak_ajax_send_invite' );

	}
endif;

/**
 * WordPress Widgets Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'widgets_init', 'wak_invites_widgets_init' );
if ( ! function_exists( 'wak_invites_widgets_init' ) ) :
	function wak_invites_widgets_init() {

		// Register Widgets
		require_once WAK_INVITES_INCLUDES . 'wak-invites-widgets.php';
		register_widget( 'WAK_Invite' );

	}
endif;

/**
 * WordPress Admin Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'admin_init', 'wak_invites_admin_init' );
if ( ! function_exists( 'wak_invites_admin_init' ) ) :
	function wak_invites_admin_init() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

		register_setting( 'wak-invites-prefs', 'wak_invites_plugin_prefs', 'wak_sanitize_invites_plugin_settings' );

	}
endif;

/**
 * Front Enqueue
 * @since 1.0
 * @version 1.0
 */
add_action( 'wp_enqueue_scripts', 'wak_rinvites_front_enqueue' );
if ( ! function_exists( 'wak_rinvites_front_enqueue' ) ) :
	function wak_rinvites_front_enqueue() {

		

	}
endif;

?>