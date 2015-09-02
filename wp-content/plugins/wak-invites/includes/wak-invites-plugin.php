<?php
// No dirrect access
if ( ! defined( 'WAK_INVITES_VER' ) ) exit;

/**
 * Plugin Activation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_plugin_activation' ) ) :
	function wak_invites_plugin_activation() {

		wak_invites_install_db();

	}
endif;

/**
 * Plugin Deactivation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_plugin_deactivation' ) ) :
	function wak_invites_plugin_deactivation() {

		//wp_clear_scheduled_hook( '' );

	}
endif;

/**
 * Plugin Database
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_install_db' ) ) :
	function wak_invites_install_db() {

		if ( get_option( 'wak_invites_db', false ) != '1.0.1' ) {

			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$table = $wpdb->prefix . 'pending_invites';

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
				email               LONGTEXT DEFAULT '', 
				invited_by          INT(11) DEFAULT 0, 
				PRIMARY KEY  (id), 
				UNIQUE KEY id (id)"; 

			// Insert table
			dbDelta( "CREATE TABLE IF NOT EXISTS {$table} ( " . $sql . " ) $collate;" );

			$table = $wpdb->prefix . 'blocked_invites';

			// Log structure
			$sql = "
				id                  INT(11) NOT NULL AUTO_INCREMENT, 
				email               LONGTEXT DEFAULT '', 
				invited_by          INT(11) DEFAULT 0, 
				PRIMARY KEY  (id), 
				UNIQUE KEY id (id)"; 

			// Insert table
			dbDelta( "CREATE TABLE IF NOT EXISTS {$table} ( " . $sql . " ) $collate;" );

			update_option( 'wak_invites_db', '1.0.1' );

		}

	}
endif;

?>