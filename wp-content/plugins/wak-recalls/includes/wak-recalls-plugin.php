<?php
// No dirrect access
if ( ! defined( 'WAK_RECALLS_VER' ) ) exit;

/**
 * Plugin Activation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_recalls_plugin_activation' ) ) :
	function wak_recalls_plugin_activation() {

		wak_recalls_install_db();

	}
endif;

/**
 * Plugin Deactivation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_recalls_plugin_deactivation' ) ) :
	function wak_recalls_plugin_deactivation() {

		//wp_clear_scheduled_hook( '' );

	}
endif;

/**
 * Plugin Database
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_recalls_install_db' ) ) :
	function wak_recalls_install_db() {

		if ( get_option( 'wak_recalls_log_db', false ) === false ) {

			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$table = $wpdb->prefix . 'recalls';

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
				campno              LONGTEXT DEFAULT '', 
				make                LONGTEXT DEFAULT '', 
				model               LONGTEXT DEFAULT '', 
				year                INT(4) DEFAULT 0,
				mfrcampno           LONGTEXT DEFAULT '', 
				component           LONGTEXT DEFAULT '', 
				manufacturer        LONGTEXT DEFAULT '', 
				start_date          LONGTEXT DEFAULT '', 
				end_date            LONGTEXT DEFAULT '', 
				vehicle             LONGTEXT DEFAULT '', 
				affected            INT(12) DEFAULT 0, 
				notification_date   LONGTEXT DEFAULT '', 
				initiator           LONGTEXT DEFAULT '', 
				manufacturer_v      LONGTEXT DEFAULT '', 
				report_date         LONGTEXT DEFAULT '', 
				record_date         LONGTEXT DEFAULT '', 
				partnumber          LONGTEXT DEFAULT '', 
				fmvss               LONGTEXT DEFAULT '', 
				defect              LONGTEXT DEFAULT '', 
				consequence         LONGTEXT DEFAULT '', 
				corrective          LONGTEXT DEFAULT '', 
				notes               LONGTEXT DEFAULT '', 
				component_id        LONGTEXT DEFAULT '', 
				PRIMARY KEY  (id), 
				UNIQUE KEY id (id)"; 

			// Insert table
			dbDelta( "CREATE TABLE IF NOT EXISTS {$table} ( " . $sql . " ) $collate;" );

			update_option( 'wak_recalls_log_db', '1.0' );

		}

	}
endif;
?>