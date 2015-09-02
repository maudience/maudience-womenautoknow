<?php
/**
 * Plugin Name:  WAK - Recalls
 * Plugin URI:   http://www.maudience.com/
 * Description:  Custom plugin that manages car recalls.
 * Version:      1.0
 * Author:       Gabriel S Merovingi
 * Author URI:   http://www.merovingi.com
 * Text Domain:  wakrecalls
 * Domain Path:  /lang
 */
define( 'WAK_RECALLS_VER',       '1.0' );
define( 'WAK_RECALLS',           __FILE__ );
define( 'WAK_RECALLS_ROOT',      plugin_dir_path( WAK_RECALLS ) );
define( 'WAK_RECALLS_CLASSES',   WAK_RECALLS_ROOT . 'classes/' );
define( 'WAK_RECALLS_INCLUDES',  WAK_RECALLS_ROOT . 'includes/' );
define( 'WAK_RECALLS_ASSETS',    WAK_RECALLS_ROOT . 'assets/' );

require_once WAK_RECALLS_INCLUDES . 'wak-recalls-functions.php';
require_once WAK_RECALLS_INCLUDES . 'wak-recalls-plugin.php';

register_activation_hook(   WAK_RECALLS, 'wak_recalls_plugin_activation' );
register_deactivation_hook( WAK_RECALLS, 'wak_recalls_plugin_deactivation' );

/**
 * Start Up
 * @since 1.0
 * @version 1.0
 */
add_action( 'plugins_loaded', 'wak_recalls_plugins_loaded' );
if ( ! function_exists( 'wak_recalls_plugins_loaded' ) ) :
	function wak_recalls_plugins_loaded() {

		// Load Translation
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wakrecalls' );
		load_textdomain( 'wakrecalls', WP_LANG_DIR . "/wak-recalls/wakrecalls-$locale.mo" );
		load_plugin_textdomain( 'wakrecalls', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		// Set review database table
		global $wpdb, $wak_recalls_db;

		$wak_recalls_db = $wpdb->prefix . 'recalls';

		add_filter( 'wak_author_profile_tabs', 'wak_add_recalls_author_tab', 10, 2 );
		add_action( 'wak_my_recalls_tab',      'wak_recalls_my_tab' );

	}
endif;

/**
 * WordPress Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'init', 'wak_recalls_init' );
if ( ! function_exists( 'wak_recalls_init' ) ) :
	function wak_recalls_init() {

		//require_once WAK_RECALLS_INCLUDES . 'wak-recalls-management.php';

		//add_action( 'admin_menu', 'wak_recalls_admin_menu' );

	}
endif;

/**
 * WordPress Widgets Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'widgets_init', 'wak_recalls_widgets_init' );
if ( ! function_exists( 'wak_recalls_widgets_init' ) ) :
	function wak_recalls_widgets_init() {

		// Register Widgets
		require_once WAK_RECALLS_INCLUDES . 'wak-recalls-widgets.php';
		register_widget( 'WAK_My_Recalls' );

	}
endif;

/**
 * WordPress Admin Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'admin_init', 'wak_recalls_admin_init' );
if ( ! function_exists( 'wak_recalls_admin_init' ) ) :
	function wak_recalls_admin_init() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

		if ( defined( 'WP_LOAD_IMPORTERS' ) )
			require_once WAK_RECALLS_INCLUDES . 'wak-recalls-importer.php';

	}
endif;

/**
 * Front Enqueue
 * @since 1.0
 * @version 1.0
 */
add_action( 'wp_enqueue_scripts', 'wak_recalls_front_enqueue' );
if ( ! function_exists( 'wak_recalls_front_enqueue' ) ) :
	function wak_recalls_front_enqueue() {

		if ( ! is_user_logged_in() ) return;

		// Enqueue Styles first
		wp_enqueue_style(
			'wak-recalls',
			plugins_url( 'assets/css/recalls.css', WAK_RECALLS ),
			array(),
			WAK_RECALLS_VER . '.1'
		);

	}
endif;

?>