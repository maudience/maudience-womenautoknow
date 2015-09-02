<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class GFFormDisplay {

	public static $submission = array();
	private static $init_scripts = array();

	const ON_PAGE_RENDER       = 1;
	const ON_CONDITIONAL_LOGIC = 2;

	public static function process_form( $form_id ) {

		GFCommon::log_debug( "GFFormDisplay::process_form(): Starting to process form (#{$form_id}) submission." );

		//reading form metadata
		$form = GFAPI::get_form( $form_id );

		if ( ! $form['is_active'] || $form['is_trash'] ) {
			return;
		}

		if ( rgar( $form, 'requireLogin' ) ) {
			if ( ! is_user_logged_in() ) {
				return;
			}
			check_admin_referer( 'gform_submit_' . $form_id, '_gform_submit_nonce_' . $form_id );
		}

		//pre process action
		do_action( 'gform_pre_process', $form );
		do_action( "gform_pre_process_{$form['id']}", $form );

		$lead = array();

		$field_values = RGForms::post( 'gform_field_values' );

		$confirmation_message = '';

		$source_page_number = self::get_source_page( $form_id );
		$page_number        = $source_page_number;
		$target_page        = self::get_target_page( $form, $page_number, $field_values );

		GFCommon::log_debug( "GFFormDisplay::process_form(): Source page number: {$source_page_number}. Target page number: {$target_page}." );

		//Loading files that have been uploaded to temp folder
		$files = GFCommon::json_decode( stripslashes( RGForms::post( 'gform_uploaded_files' ) ) );
		if ( ! is_array( $files ) ) {
			$files = array();
		}

		RGFormsModel::$uploaded_files[ $form_id ] = $files;

		$saving_for_later = rgpost( 'gform_save' ) ? true : false;

		$is_valid = true;


		$failed_validation_page = $page_number;

		//don't validate when going to previous page or saving for later
		if ( ! $saving_for_later && ( empty( $target_page ) || $target_page >= $page_number ) ) {
			$is_valid = self::validate( $form, $field_values, $page_number, $failed_validation_page );
		}

		$log_is_valid = $is_valid ? 'Yes' : 'No';
		GFCommon::log_debug( "GFFormDisplay::process_form(): After validation. Is submission valid? {$log_is_valid}." );

		//Upload files to temp folder when saving for later, going to the next page or when submitting the form and it failed validation
		if ( $saving_for_later || $target_page >= $page_number || ( $target_page == 0 && ! $is_valid ) ) {
			if ( ! empty( $_FILES ) ) {
				GFCommon::log_debug( 'GFFormDisplay::process_form(): Uploading files...' );
				//Uploading files to temporary folder
				$files = self::upload_files( $form, $files );

				RGFormsModel::$uploaded_files[ $form_id ] = $files;
			}
		}

		// Load target page if it did not fail validation or if going to the previous page
		if ( ! $saving_for_later && $is_valid ) {
			$page_number = $target_page;
		} else {
			$page_number = $failed_validation_page;
		}

		$confirmation = '';
		if ( ( $is_valid && $page_number == 0 ) || $saving_for_later ) {

			$ajax = isset( $_POST['gform_ajax'] );

			//adds honeypot field if configured
			if ( rgar( $form, 'enableHoneypot' ) ) {
				$form['fields'][] = self::get_honeypot_field( $form );
			}

			$failed_honeypot = rgar( $form, 'enableHoneypot' ) && ! self::validate_honeypot( $form );

			if ( $failed_honeypot ) {

				GFCommon::log_debug( 'GFFormDisplay::process_form(): Failed Honeypot validation. Displaying confirmation and aborting.' );

				//display confirmation but doesn't process the form when honeypot fails
				$confirmation = self::handle_confirmation( $form, $lead, $ajax );
				$is_valid     = false;
			} elseif ( ! $saving_for_later ) {

				GFCommon::log_debug( 'GFFormDisplay::process_form(): Submission is valid. Moving forward.' );

				$form = self::update_confirmation( $form );

				//pre submission action
				do_action( 'gform_pre_submission', $form );
				do_action( "gform_pre_submission_{$form['id']}", $form );

				//pre submission filter
				$form = apply_filters( "gform_pre_submission_filter_{$form['id']}", apply_filters( 'gform_pre_submission_filter', $form ) );

				//handle submission
				$confirmation = self::handle_submission( $form, $lead, $ajax );

				//after submission hook
				do_action( 'gform_after_submission', $lead, $form );
				do_action( "gform_after_submission_{$form['id']}", $lead, $form );

			} elseif ( $saving_for_later ) {
				GFCommon::log_debug( 'GFFormDisplay::process_form(): Saving for later.' );
				$lead = GFFormsModel::get_current_lead();
				$form = self::update_confirmation( $form, $lead, 'form_saved' );

				$confirmation = rgar( $form['confirmation'], 'message' );
				$nl2br        = rgar( $form['confirmation'], 'disableAutoformat' ) ? false : true;
				$confirmation = GFCommon::replace_variables( $confirmation, $form, $lead, false, true, $nl2br );

				$form_unique_id = GFFormsModel::get_form_unique_id( $form_id );
				$ip             = GFFormsModel::get_ip();
				$source_url     = GFFormsModel::get_current_page_url();
				$resume_token   = rgpost( 'gform_resume_token' );
				$resume_token   = GFFormsModel::save_incomplete_submission( $form, $lead, $field_values, $page_number, $files, $form_unique_id, $ip, $source_url, $resume_token );

				$notifications_to_send = GFCommon::get_notifications_to_send( 'form_saved', $form, $lead );

				$log_notification_event = empty( $notifications_to_send ) ? 'No notifications to process' : 'Processing notifications';
				GFCommon::log_debug( "GFFormDisplay::process_form(): {$log_notification_event} for form_saved event." );

				foreach ( $notifications_to_send as $notification ) {
					if ( isset( $notification['isActive'] ) && ! $notification['isActive'] ) {
						GFCommon::log_debug( "GFFormDisplay::process_form(): Notification is inactive, not processing notification (#{$notification['id']} - {$notification['name']})." );
						continue;
					}
					$notification['message'] = self::replace_save_variables( $notification['message'], $form, $resume_token );
					GFCommon::send_notification( $notification, $form, $lead );
				}
				self::set_submission_if_null( $form_id, 'saved_for_later', true );
				self::set_submission_if_null( $form_id, 'resume_token', $resume_token );
				GFCommon::log_debug( 'GFFormDisplay::process_form(): Saved incomplete submission.' );

			}

			if ( is_array( $confirmation ) && isset( $confirmation['redirect'] ) ){
				header( "Location: {$confirmation["redirect"]}" );
				do_action( 'gform_post_submission', $lead, $form );
				do_action( "gform_post_submission_{$form["id"]}", $lead, $form );
				exit;
			}
		}



		if ( ! isset( self::$submission[ $form_id ] ) ) {
			self::$submission[ $form_id ] = array();
		}

		self::set_submission_if_null( $form_id, 'is_valid', $is_valid );
		self::set_submission_if_null( $form_id, 'form', $form );
		self::set_submission_if_null( $form_id, 'lead', $lead );
		self::set_submission_if_null( $form_id, 'confirmation_message', $confirmation );
		self::set_submission_if_null( $form_id, 'page_number', $page_number );
		self::set_submission_if_null( $form_id, 'source_page_number', $source_page_number );

		do_action( 'gform_post_process', $form, $page_number, $source_page_number );
		do_action( "gform_post_process_{$form['id']}", $form, $page_number, $source_page_number );

	}

	private static function set_submission_if_null( $form_id, $key, $val ) {
		if ( ! isset( self::$submission[ $form_id ][ $key ] ) ) {
			self::$submission[ $form_id ][ $key ] = $val;
		}
	}


	private static function upload_files( $form, $files ) {

		$form_upload_path = GFFormsModel::get_upload_path( $form['id'] );

		//Creating temp folder if it does not exist
		$target_path = $form_upload_path . '/tmp/';
		wp_mkdir_p( $target_path );
		GFCommon::recursive_add_index_file( $form_upload_path );

		foreach ( $form['fields'] as $field ) {
			$input_name = "input_{$field->id}";

			//skip fields that are not file upload fields or that don't have a file to be uploaded or that have failed