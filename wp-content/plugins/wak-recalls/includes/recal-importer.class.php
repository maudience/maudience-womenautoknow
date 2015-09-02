<?php

/**
 * Importer Class
 * @version 1.0
 */
if ( ! class_exists( 'WAK_Import_Recalls' ) ) :
	class WAK_Import_Recalls extends WP_Importer {

		var $id;
		var $file_url;
		var $import_page;
		var $delimiter;
		var $posts = array();
		var $imported;
		var $skipped;

		/**
		 * Construct
		 */
		public function __construct() {
			$this->import_page = 'wak_recall_import';
		}

		/**
		 * Registered callback function for the WordPress Importer
		 * Manages the three separate stages of the CSV import process
		 */
		function load() {

			$this->header();

			if ( ! empty( $_POST['delimiter'] ) )
				$this->delimiter = stripslashes( trim( $_POST['delimiter'] ) );

			if ( ! $this->delimiter )
				$this->delimiter = ';';

			$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];

			switch ( $step ) {

				case 0:

					$this->greet();

				break;
				case 1:

					check_admin_referer( 'import-upload' );
					if ( $this->handle_upload() ) {

						if ( $this->id )
							$file = get_attached_file( $this->id );
						else
							$file = ABSPATH . $this->file_url;

						add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );

						if ( function_exists( 'gc_enable' ) )
							gc_enable();

						@set_time_limit(0);
						@ob_flush();
						@flush();

						$this->import( $file );

					}

				break;

			}

			$this->footer();

		}

		/**
		 * format_data_from_csv function.
		 */
		function format_data_from_csv( $data, $enc ) {

			return ( $enc == 'UTF-8' ) ? $data : utf8_encode( $data );
		}

		/**
		 * import function.
		 */
		function import( $file ) {

			global $wpdb, $wak_recalls_db;

			$this->imported = $this->skipped = 0;

			if ( ! is_file( $file ) ) {

				echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wakrecalls' ) . '</strong><br />';
				echo __( 'The file does not exist, please try again.', 'wakrecalls' ) . '</p>';
				$this->footer();
				die;

			}

			ini_set( 'auto_detect_line_endings', '1' );

			$this->delimiter = "\t";

			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wak_recalls_db};" );
			if ( $count !== NULL && $count > 0 ) {

				$wpdb->query( "TRUNCATE TABLE {$wak_recalls_db}" );

			}

			if ( ( $handle = fopen( $file, "r" ) ) !== false ) {

				$header        = fgetcsv( $handle, 0, $this->delimiter );
				$no_of_columns = sizeof( $header );

				$loop = 0;
				while ( ( $row = fgetcsv( $handle, 0, $this->delimiter ) ) !== false ) {

					list ( $record_id, $campno, $make, $model, $year, $mfrcampno, $component, $manufacturer, $start_date, $end_date, $vehicle, $affected, $notification_date, $initiator, $manufacturer_v, $report_date, $record_date, $partnumber, $fmvss, $defect, $consequence, $corrective, $notes, $component_id ) = $row;

					$wpdb->insert(
						$wak_recalls_db,
						array(
							'id'                => $record_id,
							'campno'            => $campno,
							'make'              => $make,
							'model'             => $model,
							'year'              => $year,
							'mfrcampno'         => $mfrcampno,
							'component'         => $component,
							'manufacturer'      => $manufacturer,
							'start_date'        => $start_date,
							'end_date'          => $end_date,
							'vehicle'           => $vehicle,
							'affected'          => $affected,
							'notification_date' => $notification_date,
							'initiator'         => $initiator,
							'manufacturer_v'    => $manufacturer_v,
							'report_date'       => $report_date,
							'record_date'       => $record_date,
							'partnumber'        => $partnumber,
							'fmvss'             => $fmvss,
							'defect'            => $defect,
							'consequence'       => $consequence,
							'corrective'        => $corrective,
							'notes'             => $notes,
							'component_id'      => $component_id
						),
						array( '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
					);

					$loop ++;
					$this->imported++;
			    }

			    fclose( $handle );
			}

			// Show Result
			echo '<div class="updated settings-error below-h2"><p>
				'.sprintf( __( 'Import complete - A total of <strong>%d</strong> entries were imported. <strong>%d</strong> was skipped.', 'wakrecalls' ), $this->imported, $this->skipped ).'
			</p></div>';

			$this->import_end();

		}

		/**
		 * Performs post-import cleanup of files and the cache
		 */
		function import_end() {

			echo '<p><a href="' . admin_url( 'import.php' ) . '" class="button button-large button-primary">' . __( 'Import More', 'mycred' ) . '</a></p>';

			do_action( 'import_end' );

		}

		/**
		 * Handles the CSV upload and initial parsing of the file to prepare for
		 * displaying author import options
		 * @return bool False if error uploading or invalid file, true otherwise
		 */
		function handle_upload() {

			if ( empty( $_POST['file_url'] ) ) {

				$file = wp_import_handle_upload();

				if ( isset( $file['error'] ) ) {
					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wakrecalls' ) . '</strong><br />';
					echo esc_html( $file['error'] ) . '</p>';
					return false;
				}

				$this->id = (int) $file['id'];

			} else {

				if ( file_exists( ABSPATH . $_POST['file_url'] ) ) {

					$this->file_url = esc_attr( $_POST['file_url'] );

				} else {

					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wakrecalls' ) . '</strong></p>';
					return false;

				}

			}

			return true;

		}

		/**
		 * header function.
		 */
		function header() {

			echo '<div class="wrap"><h2>' . __( 'Import Recalls', 'wakrecalls' ) . '</h2>';

		}

		/**
		 * footer function.
		 */
		function footer() {

			echo '</div>';

		}

		/**
		 * greet function.
		 */
		function greet() {

			echo '<div class="narrow">';
			echo '<p>' . __( 'Import Safecar.gov recall database.', 'wakrecalls' ).'</p>';

			$action = 'admin.php?import=wak_recall_import&step=1';

			$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
			$size = size_format( $bytes );
			$upload_dir = wp_upload_dir();

			if ( ! empty( $upload_dir['error'] ) ) :

?>
<div class="error">
	<p><?php _e( 'Before you can upload your import file, you will need to fix the following error:', 'wakrecalls' ); ?></p>
	<p><strong><?php echo $upload_dir['error']; ?></strong></p>
</div>
<?php

			else :

				global $wpdb;

				$changed = 0;
				$badposts = array();
				//$wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE guid LIKE 'http://wak/%';" );

				if ( ! empty( $badposts ) ) {
					foreach ( $badposts as $post ) {
						$wpdb->update(
							$wpdb->posts,
							array( 'guid' => str_replace( 'http://wak/', 'http://womenautoknow.com/', $post->guid ) ),
							array( 'ID' => $post->ID )
						);
						$changed ++;
					}
				}

?>
<pre><?php print_r( count( $changed ) ); ?></pre>
<form enctype="multipart/form-data" id="import-upload-form" method="post" action="<?php echo esc_attr( wp_nonce_url( $action, 'import-upload' ) ); ?>">
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<label for="upload"><?php _e( 'Choose a file from your computer:', 'wakrecalls' ); ?></label>
				</th>
				<td>
					<input type="file" id="upload" name="import" size="25" />
					<input type="hidden" name="action" value="save" />
					<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
					<small><?php printf( __( 'Maximum size: %s', 'wakrecalls' ), $size ); ?></small>
				</td>
			</tr>
			<tr>
				<th>
					<label for="file_url"><?php _e( 'OR enter path to file:', 'wakrecalls' ); ?></label>
				</th>
				<td>
					<?php echo ' ' . ABSPATH . ' '; ?><input type="text" id="file_url" name="file_url" size="25" />
				</td>
			</tr>
			<tr>
				<th><label><?php _e( 'Delimiter', 'mycred' ); ?></label><br/></th>
				<td><input type="text" name="delimiter" placeholder=";" size="2" /></td>
			</tr>
			<tr>
				<th>Max Execution Time</th>
				<td>
					<?php $max_time = ini_get("max_execution_time");
echo $max_time; ?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="submit" id="run-wak-recall-import" class="button" value="<?php esc_attr_e( 'Upload file and import' ); ?>" />
	</p>
</form>
<script type="text/javascript">
jQuery(function($) {

	$( '#run-wak-recall-import' ).click(function(){

		$(this).val( 'Importing. DO NOT leave this page until done!' );

	});

});
</script>
<?php

			endif;

		}

		/**
		 * Added to http_request_timeout filter to force timeout at 60 seconds during import
		 * @return int 60
		 */
		function bump_request_timeout( $val ) {

			return 400;

		}

	}
endif;

?>