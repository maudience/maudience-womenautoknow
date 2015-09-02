<?php
// No dirrect access
if ( ! defined( 'WAK_ESTIMATOR_VER' ) ) exit;

/**
 * Plugin Activation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_plugin_activation' ) ) :
	function wak_estimator_plugin_activation() {

		wak_estimator_install_db();

	}
endif;

/**
 * Plugin Deactivation
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_plugin_deactivation' ) ) :
	function wak_estimator_plugin_deactivation() {

		//wp_clear_scheduled_hook( '' );

	}
endif;

/**
 * Plugin Database
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_estimator_install_db' ) ) :
	function wak_estimator_install_db() {

		if ( get_option( 'wak_estimator_db', false ) === false ) {

			global $wpdb;

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$table = $wpdb->prefix . 'wak_estimator';

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
				state               LONGTEXT DEFAULT '', 
				part_cost           DECIMAL(24,2) DEFAULT 0.00, 
				labor               DECIMAL(24,2) DEFAULT 0.00, 
				PRIMARY KEY  (id), 
				UNIQUE KEY id (id)"; 

			// Insert table
			dbDelta( "CREATE TABLE IF NOT EXISTS {$table} ( " . $sql . " ) $collate;" );

			update_option( 'wak_estimator_db', '1.0' );

			$check = $wpdb->get_var( "SELECT COUNT(*) FROM {$table};" );
			if ( $check == 0 || $check === NULL ) {

				$default = array(
					'AL' => array( 'part_cost' => 215.71, 'labor' => 151.19 ),
					'AK' => array( 'part_cost' => 207.14, 'labor' => 165.16 ),
					'AZ' => array( 'part_cost' => 218.14, 'labor' => 155.39 ),
					'AR' => array( 'part_cost' => 223.26, 'labor' => 160.01 ),
					'CA' => array( 'part_cost' => 259.15, 'labor' => 159.29 ),
					'CO' => array( 'part_cost' => 230.62, 'labor' => 164.24 ),
					'CT' => array( 'part_cost' => 249.50, 'labor' => 157.75 ),
					'DE' => array( 'part_cost' => 266.58, 'labor' => 157.33 ),
					'FL' => array( 'part_cost' => 239.93, 'labor' => 154.98 ),
					'GA' => array( 'part_cost' => 257.39, 'labor' => 160.84 ),
					'HI' => array( 'part_cost' => 239.00, 'labor' => 150.03 ),
					'ID' => array( 'part_cost' => 220.53, 'labor' => 160.74 ),
					'IL' => array( 'part_cost' => 239.14, 'labor' => 154.18 ),
					'IN' => array( 'part_cost' => 225.28, 'labor' => 156.30 ),
					'IA' => array( 'part_cost' => 189.27, 'labor' => 165.77 ),
					'KS' => array( 'part_cost' => 202.50, 'labor' => 152.55 ),
					'KY' => array( 'part_cost' => 247.56, 'labor' => 157.73 ),
					'LA' => array( 'part_cost' => 224.20, 'labor' => 162.60 ),
					'ME' => array( 'part_cost' => 223.95, 'labor' => 158.05 ),
					'MD' => array( 'part_cost' => 243.90, 'labor' => 154.68 ),
					'MA' => array( 'part_cost' => 273.16, 'labor' => 151.39 ),
					'MI' => array( 'part_cost' => 188.53, 'labor' => 151.10 ),
					'MN' => array( 'part_cost' => 225.54, 'labor' => 152.20 ),
					'MS' => array( 'part_cost' => 211.54, 'labor' => 161.45 ),
					'MO' => array( 'part_cost' => 211.09, 'labor' => 159.49 ),
					'MT' => array( 'part_cost' => 225.32, 'labor' => 162.86 ),
					'NE' => array( 'part_cost' => 163.24, 'labor' => 160.33 ),
					'NV' => array( 'part_cost' => 243.01, 'labor' => 156.43 ),
					'NH' => array( 'part_cost' => 193.32, 'labor' => 143.53 ),
					'NJ' => array( 'part_cost' => 267.71, 'labor' => 154.19 ),
					'NM' => array( 'part_cost' => 155.73, 'labor' => 196.13 ),
					'NY' => array( 'part_cost' => 251.20, 'labor' => 152.41 ),
					'NC' => array( 'part_cost' => 261.21, 'labor' => 162.24 ),
					'ND' => array( 'part_cost' => 218.57, 'labor' => 174.14 ),
					'OH' => array( 'part_cost' => 211.71, 'labor' => 151.35 ),
					'OK' => array( 'part_cost' => 208.76, 'labor' => 157.16 ),
					'OR' => array( 'part_cost' => 218.94, 'labor' => 156.31 ),
					'PA' => array( 'part_cost' => 228.04, 'labor' => 154.21 ),
					'RI' => array( 'part_cost' => 262.02, 'labor' => 149.52 ),
					'SC' => array( 'part_cost' => 223.93, 'labor' => 164.18 ),
					'SD' => array( 'part_cost' => 196.89, 'labor' => 183.46 ),
					'TN' => array( 'part_cost' => 214.52, 'labor' => 158.74 ),
					'TX' => array( 'part_cost' => 232.70, 'labor' => 157.97 ),
					'UT' => array( 'part_cost' => 249.91, 'labor' => 163.00 ),
					'VT' => array( 'part_cost' => 250.69, 'labor' => 148.21 ),
					'VA' => array( 'part_cost' => 263.20, 'labor' => 158.29 ),
					'WA' => array( 'part_cost' => 225.76, 'labor' => 156.54 ),
					'WV' => array( 'part_cost' => 186.93, 'labor' => 152.57 ),
					'WI' => array( 'part_cost' => 205.28, 'labor' => 155.93 ),
					'WY' => array( 'part_cost' => 224.96, 'labor' => 174.71 )
				);

				foreach ( $default as $state => $data ) {

					$wpdb->insert(
						$table,
						array(
							'state'     => $state,
							'part_cost' => $data['part_cost'],
							'labor'     => $data['labor']
						),
						array( '%s', '%f', '%f' )
					);

				}

			}

			$table = $wpdb->prefix . 'wak_estimator_user';

			// Log structure
			$sql = "
				id                  INT(11) NOT NULL AUTO_INCREMENT, 
				user_id             INT(11) DEFAULT 0, 
				state               LONGTEXT DEFAULT '', 
				part_cost           DECIMAL(24,2) DEFAULT 0.00, 
				labor               DECIMAL(24,2) DEFAULT 0.00, 
				PRIMARY KEY  (id), 
				UNIQUE KEY id (id)"; 

			// Insert table
			dbDelta( "CREATE TABLE IF NOT EXISTS {$table} ( " . $sql . " ) $collate;" );

		}

	}
endif;
?>