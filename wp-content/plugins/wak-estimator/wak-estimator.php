<?php
/**
 * Plugin Name:  WAK - Estimator
 * Plugin URI:   http://www.maudience.com/
 * Description:  Custom plugin that manages the WAK Estimator widget and database.
 * Version:      1.0
 * Author:       Gabriel S Merovingi
 * Author URI:   http://www.merovingi.com
 * Text Domain:  wakestimator
 * Domain Path:  /lang
 */
define( 'WAK_ESTIMATOR_VER',      '1.0' );
define( 'WAK_ESTIMATOR',          __FILE__ );
define( 'WAK_ESTIMATOR_ROOT',     plugin_dir_path( WAK_ESTIMATOR ) );
define( 'WAK_ESTIMATOR_INCLUDES', WAK_ESTIMATOR_ROOT . 'includes/' );
define( 'WAK_ESTIMATOR_ASSETS',   WAK_ESTIMATOR_ROOT . 'assets/' );

require_once WAK_ESTIMATOR_INCLUDES . 'wak-estimator-plugin.php';
require_once WAK_ESTIMATOR_INCLUDES . 'wak-estimator-functions.php';

register_activation_hook(   WAK_ESTIMATOR, 'wak_estimator_plugin_activation' );
register_deactivation_hook( WAK_ESTIMATOR, 'wak_estimator_plugin_deactivation' );

/**
 * Start Up
 * @since 1.0
 * @version 1.0
 */
add_action( 'plugins_loaded', 'wak_estimator_plugins_loaded' );
if ( ! function_exists( 'wak_estimator_plugins_loaded' ) ) :
	function wak_estimator_plugins_loaded() {

		// Load Translation
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wakestimator' );
		load_textdomain( 'wakestimator', WP_LANG_DIR . "/wak-estimator/wakestimator-$locale.mo" );
		load_plugin_textdomain( 'wakestimator', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	}
endif;

/**
 * WordPress Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'init', 'wak_estimator_init' );
if ( ! function_exists( 'wak_estimator_init' ) ) :
	function wak_estimator_init() {

		// Register Shortcodes
		require_once WAK_ESTIMATOR_INCLUDES . 'wak-estimator-shortcodes.php';
		add_shortcode( 'wak_estimator', 'wak_estimator_shortcode' );

		// Register Shortcodes with Visual Composer
		if ( function_exists( 'vc_map' ) ) {

			require_once WAK_ESTIMATOR_INCLUDES . 'wak-estimator-vc.php';
			wak_estimator_vc_map_shortcodes();

		}

		// Register AJAX calls
		require_once WAK_ESTIMATOR_INCLUDES . 'wak-estimator-ajax.php';
		add_action( 'wp_ajax_nopriv_wak-get-estimate', 'wak_estimator_ajax_get_estimate' );
		add_action( 'wp_ajax_wak-get-estimate',        'wak_estimator_ajax_get_estimate' );

	}
endif;

/**
 * WordPress Widgets Init
 * @since 1.0
 * @version 1.0
 */
add_action( 'widgets_init', 'wak_estimator_widgets_init' );
if ( ! function_exists( 'wak_estimator_widgets_init' ) ) :
	function wak_estimator_widgets_init() {

		// Register Widgets
		require_once WAK_ESTIMATOR_INCLUDES . 'wak-estimator-widgets.php';
		register_widget( 'WAK_Estimator' );

	}
endif;

/**
 * Front Enqueue
 * @since 1.0
 * @version 1.0
 */
add_action( 'wp_enqueue_scripts', 'wak_estimator_front_enqueue' );
if ( ! function_exists( 'wak_estimator_front_enqueue' ) ) :
	function wak_estimator_front_enqueue() {

		// Enqueue Styles first
		wp_enqueue_style(
			'wak-estimator',
			plugins_url( 'assets/css/estimator.css', WAK_ESTIMATOR ),
			array(),
			WAK_ESTIMATOR_VER . '.1'
		);

		// Register, localize and enqueue scripts
		wp_register_script(
			'wak-estimator',
			plugins_url( 'assets/js/estimator.js', WAK_ESTIMATOR ),
			array( 'jquery' ),
			WAK_ESTIMATOR_VER . '.1'
		);

		wp_localize_script(
			'wak-estimator',
			'WAKEstimator',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'token'   => wp_create_nonce( 'wak-estimator-filter' ),
				'loading' => esc_js( '<h1 class="text-center pink"><i class="fa fa-spinner fa-spin"></i></h1><p class="text-center">loading results ...</p>' )
			)
		);

		wp_enqueue_script( 'wak-estimator' );

	}
endif;

?>