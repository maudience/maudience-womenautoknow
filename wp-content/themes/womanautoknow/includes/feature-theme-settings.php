<?php

/**
 * Feature: Theme Settings
 * @version 1.0
 */
add_action( 'init', 'wak_theme_feature_theme_settings' );
function wak_theme_feature_theme_settings() {

	add_action( 'admin_menu', 'wak_theme_add_settings_page' );
	add_action( 'admin_init', 'wak_theme_register_settings' );

}

/**
 * Add Admin Page
 * @version 1.0
 */
function wak_theme_add_settings_page() {

	$page = add_theme_page(
		'Settings',
		'Settings',
		'manage_options',
		'wak-theme-settings',
		'wak_theme_settings_page_screen'
	);

	add_action( 'admin_print_styles-' . $page, 'wak_theme_settings_page_header' );

}

/**
 * Register Settings
 * @version 1.0
 */
function wak_theme_register_settings() {

	register_setting( 'wak-theme-settings', 'wak_theme_prefs', 'wak_theme_sanitize_prefs' );

}

/**
 * Sanitize Settings
 * @version 1.0
 */
function wak_theme_sanitize_prefs( $prefs ) {

	return $prefs;

}

/**
 * Admin Screen Header
 * @version 1.0
 */
function wak_theme_settings_page_header() {

}

/**
 * Admin Screen
 * @version 1.0
 */
function wak_theme_settings_page_screen() {

	$prefs = wak_theme_prefs();

?>
<div class="wrap">
	<h2>Theme Settings</h2>
	<form method="post" action="options.php">

		<?php settings_fields( 'wak-theme-settings' ); ?>

		<h3>Contact Details</h3>
		<p>The following contact details are visible to everyone in the footer of the website. You can leave a field empty to hide it.</p>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-theme-prefs-address">Address</label></th>
				<td>
					<textarea class="large-text code" id="wak-theme-prefs-address" name="wak_theme_prefs[address]" cols="50" rows="5"><?php echo esc_attr( $prefs['address'] ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-theme-prefs-phone">Phone Number</label></th>
				<td>
					<input type="text" class="regular-text" id="wak-theme-prefs-phone" name="wak_theme_prefs[phone]" value="<?php echo esc_attr( $prefs['phone'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-theme-prefs-email">Public Email</label></th>
				<td>
					<input type="text" class="regular-text" id="wak-theme-prefs-email" name="wak_theme_prefs[email]" value="<?php echo esc_attr( $prefs['email'] ); ?>" />
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<h3>Social Media Links</h3>
		<p>The URL to redirect users to when they click on a social media icon.</p>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-theme-prefs-facebook-link">Facebook</label></th>
				<td>
					<input type="text" class="regular-text" id="wak-theme-prefs-facebook-link" name="wak_theme_prefs[facebook_link]" value="<?php echo esc_attr( $prefs['facebook_link'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-theme-prefs-twitter-link">Twitter</label></th>
				<td>
					<input type="text" class="regular-text" id="wak-theme-prefs-twitter-link" name="wak_theme_prefs[twitter_link]" value="<?php echo esc_attr( $prefs['twitter_link'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-theme-prefs-twitter-link">Instagram</label></th>
				<td>
					<input type="text" class="regular-text" id="wak-theme-prefs-instagram-link" name="wak_theme_prefs[instagram_link]" value="<?php echo esc_attr( $prefs['instagram_link'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-theme-prefs-pinterest-link">Pinterest</label></th>
				<td>
					<input type="text" class="regular-text" id="wak-theme-prefs-pinterest-link" name="wak_theme_prefs[pinterest_link]" value="<?php echo esc_attr( $prefs['pinterest_link'] ); ?>" />
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<h3>Twitter Feed</h3>
		<p>In order to show your twitter feed in the footer of the website, you must create a widget in your Twitter account.</p>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-theme-prefs-twitter-id">Widget ID</label></th>
				<td>
					<input type="text" class="regular-text" id="wak-theme-prefs-twitter-id" name="wak_theme_prefs[twitter_id]" value="<?php echo esc_attr( $prefs['twitter_id'] ); ?>" />
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<?php submit_button( 'Save', 'primary', 'save' ); ?>

	</form>
</div>
<?php

}

/**
 * Get Theme Preferences
 * @version 1.0
 */
function wak_theme_prefs() {

	$default = array(
		'address'        => '',
		'phone'          => '',
		'email'          => '',
		'facebook_link'  => '',
		'twitter_link'   => '',
		'instagram_link' => '',
		'pinterest_link' => '',
		'twitter_id'     => ''
	);

	$settings = get_option( 'wak_theme_prefs', $default );

	return wp_parse_args( $settings, $default );

}

/**
 * WAK Contact Details
 * @version 1.0
 */
function wak_theme_contact_details() {

	$prefs = wak_theme_prefs();

	$details = array();

	if ( $prefs['address'] != '' )
		$details[] = '<address>' . nl2br( $prefs['address'] ) . '</address>';

	if ( $prefs['phone'] != '' )
		$details[] = '<p><strong>Phone:</strong> ' . esc_attr( $prefs['phone'] ) . '</p>';

	if ( $prefs['email'] != '' )
		$details[] = '<p>' . esc_attr( $prefs['email'] ) . '</p>';

	$social = array();
	$base_url = get_template_directory_uri() . '/images/';

	if ( $prefs['facebook_link'] != '' )
		$social[] = '<a href="' . esc_url( $prefs['facebook_link'] ) . '" target="_blank" class="pink"><i class="fa fa-facebook"></i></a>';

	if ( $prefs['twitter_link'] != '' )
		$social[] = '<a href="' . esc_url( $prefs['twitter_link'] ) . '" target="_blank" class="pink"><i class="fa fa-twitter"></i></a>';

	if ( $prefs['instagram_link'] != '' )
		$social[] = '<a href="' . esc_url( $prefs['instagram_link'] ) . '" target="_blank" class="pink"><i class="fa fa-instagram"></i></a>';

	if ( $prefs['pinterest_link'] != '' )
		$social[] = '<a href="' . esc_url( $prefs['pinterest_link'] ) . '" target="_blank" class="pink"><i class="fa fa-pinterest-p"></i></a>';

	if ( ! empty( $social ) )
		$details[] = '<p id="footer-social-links">' . implode( '', $social ) . '</p>';

	if ( ! empty( $details ) )
		echo '<div id="company-details"><h5>Women Auto Know</h5>' . implode( '', $details ) . '</div>';

}

/**
 * WAK Twitter Feed
 * @version 1.0
 */
function wak_theme_twitter_feed() {

	$prefs = wak_theme_prefs();
	if ( $prefs['twitter_id'] == '' ) return;

?>
<div class="widget" id="wak-twitter-widget">
	<h4 class="widget-title">Follow Us on Twitter</h4>
	<a class="twitter-timeline" href="<?php echo esc_url( $prefs['twitter_link'] ); ?>" data-border-color="#E9E9E9" data-chrome="noheader nofooter noborders noscrollbar transparent" data-widget-id="<?php echo esc_attr( $prefs['twitter_id'] ); ?>">Tweets by WAK</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</div>
<?php

}

?>