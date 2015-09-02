<?php
// No dirrect access
if ( ! defined( 'WAK_REGISTER_VER' ) ) exit;

/**
 * Plugin Activation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_plugin_activation' ) ) :
	function wak_registration_plugin_activation() {

		wak_registration_install_db();

	}
endif;

/**
 * Plugin Deactivation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_plugin_deactivation' ) ) :
	function wak_registration_plugin_deactivation() {

		//wp_clear_scheduled_hook( '' );

	}
endif;

/**
 * Plugin Database
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_install_db' ) ) :
	function wak_registration_install_db() {

		if ( get_option( 'wak_registration_log_db', false ) === false ) {

			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$table = $wpdb->prefix . 'pending_registrations';

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
				type                LONGTEXT DEFAULT '', 
				activation_code     LONGTEXT DEFAULT '', 
				username            LONGTEXT DEFAULT '', 
				email               LONGTEXT DEFAULT '', 
				password            LONGTEXT DEFAULT '', 
				first_name          LONGTEXT DEFAULT '', 
				last_name           LONGTEXT DEFAULT '', 
				zip                 INT(11) DEFAULT 0, 
				state               LONGTEXT DEFAULT '', 
				newsletter          INT(11) DEFAULT 0, 
				IP                  LONGTEXT DEFAULT '', 
				time                INT(11) DEFAULT 0, 
				emails_sent         INT(11) DEFAULT 1, 
				autoshop_id         INT(11) DEFAULT 0, 
				PRIMARY KEY  (id), 
				UNIQUE KEY id (id)"; 

			// Insert table
			dbDelta( "CREATE TABLE IF NOT EXISTS {$table} ( " . $sql . " ) $collate;" );

			update_option( 'wak_registration_log_db', '1.0' );

		}

	}
endif;
?>