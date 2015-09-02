<?php
// No dirrect access
if ( ! defined( 'WAK_AUTOSHOPS_VER' ) ) exit;

/**
 * Plugin Activation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_plugin_activation' ) ) :
	function wak_autoshops_plugin_activation() {

		wak_autoshops_install_db();

	}
endif;

/**
 * Plugin Deactivation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_plugin_deactivation' ) ) :
	function wak_autoshops_plugin_deactivation() {

		//wp_clear_scheduled_hook( '' );

	}
endif;

/**
 * Plugin Database
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_install_db' ) ) :
	function wak_autoshops_install_db() {

		if ( get_option( 'wak_reviews_db', false ) === false ) {

			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$table = $wpdb->prefix . 'autoshop_reviews';

			$wpdb->hide_errors();

			$collate = '';
			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty( $wpdb->charset ) )
					$collate .= "DEFAULT CHARACTER SET {$wpdb->charset}";
				if ( ! empty( $wpdb->collate ) )
					$collate .= " COLLATE {$wpdb->collate}";
			}

			// Log structure
			$sql = "
				id                  INT(11) NOT NULL AUTO_INCREMENT, 
				status              INT(11) DEFAULT 0, 
				autoshop_id         INT(11) DEFAULT 0, 
				user_id             INT(11) DEFAULT 0, 
				time                INT(11) DEFAULT 0, 
				is_pro              INT(11) DEFAULT 0, 
				is_comf             INT(11) DEFAULT 0, 
				will_return         INT(11) DEFAULT 0, 
				recommended         INT(11) DEFAULT 0, 
				wheels              INT(11) DEFAULT 0, 
				review              LONGTEXT DEFAULT '', 
				edit                INT(11) DEFAULT 0,
				PRIMARY KEY  (id), 
				UNIQUE KEY id (id)"; 

			// Insert table
			dbDelta( "CREATE TABLE IF NOT EXISTS {$table} ( " . $sql . " ) $collate;" );

			update_option( 'wak_reviews_db', '1.0' );

		}

	}
endif;
?>