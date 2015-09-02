<?php
// No dirrect access
if ( ! defined( 'WAK_PAYMENTS_VER' ) ) exit;

/**
 * Plugin Activation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_payments_plugin_activation' ) ) :
	function wak_payments_plugin_activation() {

		wak_payments_install_db();

	}
endif;

/**
 * Plugin Deactivation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_payments_plugin_deactivation' ) ) :
	function wak_payments_plugin_deactivation() {

		//wp_clear_scheduled_hook( '' );

	}
endif;

/**
 * Plugin Database
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_payments_install_db' ) ) :
	function wak_payments_install_db() {

		if ( get_option( 'wak_payments_log_db', false ) === false ) {

			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$table = $wpdb->prefix . 'payments';

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
				payment_id          LONGTEXT DEFAULT '', 
				type                LONGTEXT DEFAULT '', 
				amount_paid         DECIMAL(32,2) DEFAULT 0.00,
				time                INT(11) DEFAULT 0, 
				user_id             INT(11) DEFAULT 0, 
				object_id           INT(11) DEFAULT 0, 
				subscription_id     LONGTEXT DEFAULT '', 
				transaction_id      LONGTEXT DEFAULT '', 
				charged             INT(11) DEFAULT 0, 
				first_name          LONGTEXT DEFAULT '', 
				last_name           LONGTEXT DEFAULT '', 
				IP                  LONGTEXT DEFAULT '', 
				PRIMARY KEY  (id), 
				UNIQUE KEY id (id)"; 

			// Insert table
			dbDelta( "CREATE TABLE IF NOT EXISTS {$table} ( " . $sql . " ) $collate;" );

			update_option( 'wak_payments_log_db', '1.0' );

		}

	}
endif;
?>