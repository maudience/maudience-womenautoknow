<?php
// No dirrect access
if ( ! defined( 'WAK_INVITES_VER' ) ) exit;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_admin_menu' ) ) :
	function wak_invites_admin_menu() {

		$pages = array();

		$pages[] = add_users_page(
			__( 'Invites', 'wakinvites' ),
			__( 'Invites', 'wakinvites' ),
			'moderate_comments',
			'wak-invites-settings',
			'wak_invites_settings_admin_screen'
		);

		foreach ( $pages as $page ) {
			add_action( 'admin_print_styles-' . $page, 'wak_invites_admin_screen_styles' );
			add_action( 'load-' . $page,               'wak_invites_admin_load' );
		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_admin_screen_styles' ) ) :
	function wak_invites_admin_screen_styles() {

		

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_admin_load' ) ) :
	function wak_invites_admin_load() {

		

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_invites_settings_admin_screen' ) ) :
	function wak_invites_settings_admin_screen() {

		$prefs = wak_invites_plugin_settings();

?>
<div class="wrap">
	<h2>Invite Settings</h2>
	<form method="post" action="options.php">

		<?php settings_fields( 'wak-invites-prefs' ); ?>

		<h3><?php _e( 'Email Templates', 'wakinvites' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wakinvitedriveremail"><?php _e( 'Driver Invites', 'wakinvites' ); ?></label></th>
				<td>
					<p>This template is used when premium auto shop owners are inviting their customers to join WAK.</p>
					<p><span class="description">Use the <code>%INVITERSNAME%</code> template tag to show the name of the user that made the invite,  %WEBSITEURL% to insert the websites URL and %NAME% for the name of the person being invited.</span></p>
					<?php wp_editor( $prefs['emails_driver'], 'wakinvitedriveremail', array( 'textarea_name' => 'wak_invites_plugin_prefs[emails_driver]', 'textarea_rows' => 15 ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wakinviteshopemail"><?php _e( 'Invite for Shops', 'wakinvites' ); ?></label></th>
				<td>
					<p>This template is used when drivers invite auto shops.</p>
					<p><span class="description">Use the <code>%INVITERSNAME%</code> template tag to show the name of the user that made the invite,  %WEBSITEURL% to insert the websites URL and %NAME% for the name of the person being invited.</span></p>
					<?php wp_editor( $prefs['emails_shop'], 'wakinviteshopemail', array( 'textarea_name' => 'wak_invites_plugin_prefs[emails_shop]', 'textarea_rows' => 15 ) ); ?>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<?php submit_button( __( 'Update Settings', 'wakinvites' ), 'primary large', 'submit' ); ?>

	</form>
</div>
<?php

	}
endif;

?>