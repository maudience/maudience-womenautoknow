<?php
// No dirrect access
if ( ! defined( 'WAK_REGISTER_VER' ) ) exit;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_activation_template' ) ) :
	function wak_registration_activation_template( $template ) {

		if ( isset( $_GET['do'] ) && $_GET['do'] == 'verify-email' && isset( $_GET['token'] ) && strlen( $_GET['token'] ) == 12 )
			return WAK_REGISTER_TEMPLATES . 'activate-account.php';

		$prefs = wak_registration_plugin_settings();

		if ( is_page( $prefs['recover_page_id'] ) )
			return WAK_REGISTER_TEMPLATES . 'recover-password.php';

		return $template;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_template_redirects' ) ) :
	function wak_registration_template_redirects() {

		$prefs  = wak_registration_plugin_settings();
		$member = is_user_logged_in();

		if ( $prefs['signup_page_id'] > 0 && is_page( $prefs['signup_page_id'] ) ) {

			if ( $member && ! current_user_can( 'moderate_comments' ) ) {

				wp_redirect( home_url( '/' ) );
				exit;

			}

			

		}

		elseif ( $member && is_front_page() && ! current_user_can( 'edit_users' ) ) {

			wp_redirect( home_url( '/autoshops/' ) );
			exit;

		}

		elseif ( $prefs['recover_page_id'] > 0 && is_page( $prefs['recover_page_id'] ) && $member ) {

			wp_redirect( home_url( '/' ) );
			exit;

		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_admin_menu' ) ) :
	function wak_registration_admin_menu() {

		$pages = array();

		$pages[] = add_users_page(
			__( 'Pending Registrations', 'wakregister' ),
			__( 'Pending Registrations', 'wakregister' ),
			'promote_users',
			'wak-registration',
			'wak_pending_registrations_admin_screen'
		);

		$pages[] = add_users_page(
			__( 'Settings', 'wakregister' ),
			__( 'Settings', 'wakregister' ),
			'promote_users',
			'wak-registration-settings',
			'wak_registration_settings_admin_screen'
		);

		foreach ( $pages as $page ) {
			add_action( 'admin_print_styles-' . $page, 'wak_registration_admin_screen_styles' );
			add_action( 'load-' . $page,               'wak_registration_admin_load' );
		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_admin_screen_styles' ) ) :
	function wak_registration_admin_screen_styles() {

?>
<style type="text/css">
th#email { width: auto; }
th#atype, th#emails-sent, th#username { width: 100px; }
th#first-name, th#last-name, th#date { width: 120px; }
</style>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_admin_load' ) ) :
	function wak_registration_admin_load() {

		// Handle registration admin actions
		wak_process_registrations_admin_actions();

		if ( $_GET['page'] == 'wak-registration' ) {

			$args = array(
				'label'   => __( 'Pending Registrations', 'wakregister' ),
				'default' => 10,
				'option'  => 'wak_pending_reg_per_page'
			);
			add_screen_option( 'per_page', $args );

		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_pending_registrations_admin_screen' ) ) :
	function wak_pending_registrations_admin_screen() {

		if ( ! current_user_can( 'promote_users' ) ) wp_die( 'Access Denied' );

		$args = array();

		$number = get_user_meta( get_current_user_id(), 'wak_pending_reg_per_page', true );
		if ( $number != '' )
			$args['number'] = absint( $number );

		if ( isset( $_GET['type'] ) )
			$args['type'] = sanitize_text_field( $_GET['type'] );

		if ( isset( $_GET['first_name'] ) )
			$args['first_name'] = sanitize_text_field( $_GET['first_name'] );

		if ( isset( $_GET['last_name'] ) )
			$args['last_name'] = sanitize_text_field( $_GET['last_name'] );

		if ( isset( $_GET['email'] ) )
			$args['email'] = sanitize_text_field( $_GET['email'] );

		if ( isset( $_GET['username'] ) )
			$args['username'] = sanitize_text_field( $_GET['username'] );

		if ( isset( $_GET['paged'] ) )
			$args['paged'] = absint( $_GET['paged'] );

		if ( isset( $_GET['zip'] ) )
			$args['zip'] = absint( $_GET['zip'] );

		$registrations = new WAK_Query_Registrations( $args );

?>
<div class="wrap">
	<h2><?php _e( 'Pending Registrations', 'wakregister' ); ?></h2>
	<p>The following users have registered but not yet verified their email. Until they do, their account will only be visible here.</p>
	<?php

		if ( isset( $_GET['updated'] ) ) {

			if ( $_GET['updated'] == 1 )
				echo '<div id="message" class="updated"><p>' . ( ( isset( $_GET['multi'] ) ? sprintf( _n( 'Registration Approved.', 'Approved %d Registration.', $_GET['multi'], '' ), $_GET['multi'] ) : 'Registration Approved.' ) ) . '</p></div>';

		}

		elseif ( isset( $_GET['resent'] ) && $_GET['resent'] == 1 )
			echo '<div id="message" class="updated"><p>Activation email re-sent.</p></div>';

		elseif ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 1 )
			echo '<div id="message" class="error"><p>' . ( ( isset( $_GET['multi'] ) ? sprintf( _n( 'Registration Deleted.', '%d Registration were successfully deleted.', $_GET['multi'], '' ), $_GET['multi'] ) : 'Registration Deleted.' ) ) . '</p></div>';

		elseif ( isset( $_GET['error'] ) ) {

			if ( $_GET['error'] == 1 )
				echo '<div id="message" class="error"><p>' . urldecode( $_GET['message'] ) . '</p></div>';

		}

?>
	<form id="pending-registrations-list" method="get" action="users.php">
		<input type="hidden" name="page" value="wak-registration" />
		<p class="search-box">
			<input type="search" name="s" placeholder="Email" value="<?php if ( isset( $_GET['email'] ) ) echo esc_attr( $_GET['email'] ); ?>" />
			<input type="submit" class="button" value="Search" />
		</p>
		<div class="tablenav top">

			<div class="alignleft actions bulkactions">
				<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
				<select name="action" id="bulk-action-selector-top">
					<option value="-1">Bulk Actions</option>
					<option value="approve">Approve</option>
					<option value="delete">Delete</option>
				</select>
				<input type="submit" id="doaction" class="button action" value="Apply" />
			</div>

			<div class="tablenav-pages">
				<?php $registrations->pagination(); ?>
			</div>

			<br class="clear" />

		</div>
		<table class="wp-list-table widefat fixed striped posts">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>
					<th scope="col" id="email" class="manage-column column-email email-column">Email</th>
					<th scope="col" id="atype" class="manage-column column-atype atype-column">Account Type</th>
					<th scope="col" id="first-name" class="manage-column column-first-name first-name-column">First Name</th>
					<th scope="col" id="last-name" class="manage-column column-last-name last-name-column">Last Name</th>
					<th scope="col" id="username" class="manage-column column-username username-column">Username</th>
					<th scope="col" id="date" class="manage-column column-date date-column">Date</th>
					<th scope="col" id="emails-sent" class="manage-column column-emails-sent emails-sent-column">Emails Sent</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>
					<th scope="col" class="manage-column column-email email-column">Email</th>
					<th scope="col" class="manage-column column-atype atype-column">Account Type</th>
					<th scope="col" class="manage-column column-first-name first-name-column">First Name</th>
					<th scope="col" class="manage-column column-last-name last-name-column">Last Name</th>
					<th scope="col" class="manage-column column-username username-column">Username</th>
					<th scope="col" class="manage-column column-date date-column">Date</th>
					<th scope="col" class="manage-column column-emails-sent emails-sent-column">Emails Sent</th>
				</tr>
			</tfoot>
			<tbody>
<?php

		if ( $registrations->have_entries() ) {

			$date_format = get_option( 'date_format' );
			foreach ( $registrations->results as $entry ) {

?>
				<tr id="<?php echo $entry->id ?>">
					<th scope="row" class="check-column"><input type="checkbox" id="review-<?php echo $entry->id; ?>" name="registrations[]" value="<?php echo $entry->id; ?>" /></th>
					<td class="email-column">
						<strong><?php echo esc_attr( $entry->email ); ?></strong>
						<?php echo $registrations->row_actions( $entry ); ?>
					</td>
					<td class="atype-column"><?php echo esc_attr( $entry->type ); ?></td>
					<td class="first-name-column"><?php echo esc_attr( $entry->first_name ); ?></td>
					<td class="last-name-column"><?php echo esc_attr( $entry->last_name ); ?></td>
					<td class="username-column"><?php echo esc_attr( $entry->username ); ?></td>
					<td class="date-column"><?php echo date_i18n( $date_format, $entry->time ); ?></td>
					<td class="emails-sent-column"><?php echo absint( $entry->emails_sent ); ?></td>
				</tr>
<?php

			}

		}

		else {

?>
				<tr>
					<td colspan="8">No pending registrations found.</td>
				</tr>
<?php

		}

?>
			</tbody>
		</table>
	</form>
</div>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_registration_settings_admin_screen' ) ) :
	function wak_registration_settings_admin_screen() {

		if ( ! current_user_can( 'promote_users' ) ) wp_die( 'Access Denied' );

		$prefs = wak_registration_plugin_settings();

?>
<div class="wrap">
	<h2><?php _e( 'Registration Settings', 'wakregister' ); ?></h2>
	<form method="post" action="options.php">

		<?php settings_fields( 'wak-registration-prefs' ); ?>

		<h3><?php _e( 'Title', 'wakregister' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-registration-prefs-signup-page-id"><?php _e( 'Signup Page', 'wakregister' ); ?></label></th>
				<td>
					<?php wp_dropdown_pages( array(
						'name'              => 'wak_registration_plugin_prefs[signup_page_id]',
						'id'                => 'wak-registration-prefs-signup-page-id',
						'selected'          => $prefs['signup_page_id'],
						'show_option_none'  => __( 'Select the page used for signups.', 'wakregister' ),
						'option_none_value' => 0
					) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-registration-prefs-recover-page-id"><?php _e( 'Password Recovery Page', 'wakregister' ); ?></label></th>
				<td>
					<?php wp_dropdown_pages( array(
						'name'              => 'wak_registration_plugin_prefs[recover_page_id]',
						'id'                => 'wak-registration-prefs-recover-page-id',
						'selected'          => $prefs['recover_page_id'],
						'show_option_none'  => __( 'Select the page used for signups.', 'wakregister' ),
						'option_none_value' => 0
					) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-register-captcha-key">ReCaptcha Site Key</label></th>
				<td>
					<input type="text" name="wak_registration_plugin_prefs[captcha_sitekey]" id="wak-register-captcha-key" value="<?php echo esc_attr( $prefs['captcha_sitekey'] ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-register-captcha-secret">ReCaptcha Secret</label></th>
				<td>
					<input type="text" name="wak_registration_plugin_prefs[captcha_secret]" id="wak-register-captcha-secret" value="<?php echo esc_attr( $prefs['captcha_secret'] ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-registration-prefs-success">Success Message</label></th>
				<td>
					<textarea class="large-text code" rows="4" name="wak_registration_plugin_prefs[success_signup]" id="wak-registration-prefs-success"><?php echo esc_attr( $prefs['success_signup'] ); ?></textarea>
					<span class="description">This text should inform the user about the email verification.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-registration-prefs-verify-email">Verify Email</label></th>
				<td>
					<label for="wak-registration-prefs-verify-email"><input type="checkbox" name="wak_registration_plugin_prefs[verify_email]" id="wak-registration-prefs-verify-email"<?php checked( $prefs['verify_email'], 1 ); ?> value="1" /> New members must verify their email before registering.</label>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<h3><?php _e( 'Email Templates', 'wakregister' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wakregisteractivate"><?php _e( 'Activation Email', 'wakregister' ); ?></label></th>
				<td>
					<p><span class="description">Use the <code>%ACTIVATIONLINK%</code> template tag where you want to insert the activation link.</span></p>
					<?php wp_editor( $prefs['emails']['activate'], 'wakregisteractivate', array( 'textarea_name' => 'wak_registration_plugin_prefs[emails][activate]', 'textarea_rows' => 15 ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wakregisternewpassword"><?php _e( 'Password Reset', 'wakregister' ); ?></label></th>
				<td>
					<p><span class="description">Use the <code>%NEWPASSWORD%</code> template tag to show the users new password.</span></p>
					<?php wp_editor( $prefs['emails']['resetpass'], 'wakregisternewpassword', array( 'textarea_name' => 'wak_registration_plugin_prefs[emails][resetpass]', 'textarea_rows' => 15 ) ); ?>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<?php submit_button( __( 'Update Settings', 'wakregister' ), 'primary large', 'submit' ); ?>

	</form>
</div>
<?php

	}
endif;

?>