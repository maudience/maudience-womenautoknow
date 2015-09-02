<?php
// No dirrect access
if ( ! defined( 'WAK_RECALLS_VER' ) ) exit;

/**
 * Register Importer
 * @version 1.0
 */
register_importer(
	'wak_recall_import',
	__( 'WAK Recalls', 'customquiz' ),
	__( 'Import the latest motorvehicle recall database.', 'customquiz' ),
	'wak_import_recalls_setup'
);

/**
 * Importer Setup
 * @version 1.0
 */
if ( ! function_exists( 'wak_import_recalls_setup' ) ) :
	function wak_import_recalls_setup() {

		require_once ABSPATH . 'wp-admin/includes/import.php';

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) )
				require $class_wp_importer;
		}

		require_once WAK_RECALLS_INCLUDES . 'recal-importer.class.php';

		$importer = new WAK_Import_Recalls();
		$importer->load();

	}
endif;

?>