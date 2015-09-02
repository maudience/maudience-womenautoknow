<?php
// No dirrect access
if ( ! defined( 'WAK_PAYMENTS_VER' ) ) exit;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_payments_admin_menu' ) ) :
	function wak_payments_admin_menu() {

		$pages = array();

		$pages[] = add_menu_page(
			__( 'Payments', 'wakpayments' ),
			__( 'Payments', 'wakpayments' ),
			'moderate_comments',
			'premium-autoshops',
			'wak_payment_log_admin_screen',
			'dashicons-yes',
			7
		);

		$pages[] = add_submenu_page(
			'premium-autoshops',
			__( 'Payment Log', 'wakpayments' ),
			__( 'Payment Log', 'wakpayments' ),
			'moderate_comments',
			'premium-autoshops',
			'wak_payment_log_admin_screen'
		);

		$pages[] = add_submenu_page(
			'premium-autoshops',
			__( 'Payment Plans', 'wakpayments' ),
			__( 'Payment Plans', 'wakpayments' ),
			'moderate_comments',
			'payment-plans',
			'wak_payment_plans_admin_screen'
		);

		$pages[] = add_submenu_page(
			'premium-autoshops',
			__( 'Settings', 'wakpayments' ),
			__( 'Settings', 'wakpayments' ),
			'moderate_comments',
			'payment-settings',
			'wak_payment_settings_admin_screen'
		);

		foreach ( $pages as $page ) {
			add_action( 'admin_print_styles-' . $page, 'wak_payments_admin_screen_styles' );
			add_action( 'load-' . $page,               'wak_payments_admin_load' );
		}

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_payments_admin_screen_styles' ) ) :
	function wak_payments_admin_screen_styles() {

		

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_payments_admin_load' ) ) :
	function wak_payments_admin_load() {

		wak_process_payment_admin_actions();

		$args = array(
			'label'   => __( 'Payments', 'wakpayments' ),
			'default' => 10,
			'option'  => 'wak_payments_per_page'
		);
		add_screen_option( 'per_page', $args );

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_payment_plans_admin_screen' ) ) :
	function wak_payment_plans_admin_screen() {

		$prefs = wak_payment_plans();

?>
<div class="wrap">
	<h2><?php _e( 'Payment Plans', 'wakpayments' ); ?></h2>
	<form method="post" action="options.php">

		<?php settings_fields( 'wak-payment-plans' ); ?>

		<h3>Monthly Subscriptions</h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-payments-">Enable</label></th>
				<td>
					<label for=""><input type="checkbox" name="wak_payment_plans_prefs[monthly_subscription][enabled]" id=""<?php checked( $prefs['monthly_subscription']['enabled'], 1 ); ?> value="1" /></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments-">Cost</label></th>
				<td>
					$ <input type="text" size="12" placeholder="0.00" name="wak_payment_plans_prefs[monthly_subscription][cost]" id="" value="<?php echo esc_attr( $prefs['monthly_subscription']['cost'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments">Public Label</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payment_plans_prefs[monthly_subscription][label]" id="" value="<?php echo esc_attr( $prefs['monthly_subscription']['label'] ); ?>" /><br />
					<span class="description">This is what members see when they are asked to select a payment plan when making a payment.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments">Payment Description</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payment_plans_prefs[monthly_subscription][payment]" id="" value="<?php echo esc_attr( $prefs['monthly_subscription']['payment'] ); ?>" /><br />
					<span class="description">This is what is shown to the payment processor and credit card statements.</span>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<h3>One Time Payment</h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-payments-">Enable</label></th>
				<td>
					<label for=""><input type="checkbox" name="wak_payment_plans_prefs[one_time][enabled]" id=""<?php checked( $prefs['one_time']['enabled'], 1 ); ?> value="1" /></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments-">Cost</label></th>
				<td>
					$ <input type="text" size="12" placeholder="0.00" name="wak_payment_plans_prefs[one_time][cost]" id="" value="<?php echo esc_attr( $prefs['one_time']['cost'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments-">Length</label></th>
				<td>
					<input type="text" size="12" placeholder="1" name="wak_payment_plans_prefs[one_time][length]" id="" value="<?php echo absint( $prefs['one_time']['length'] ); ?>" /> days
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments">Label</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payment_plans_prefs[one_time][label]" id="" value="<?php echo esc_attr( $prefs['one_time']['label'] ); ?>" /><br />
					<span class="description">This is what members see when they are asked to select a payment plan when making a payment.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments">Payment Description</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payment_plans_prefs[one_time][payment]" id="" value="<?php echo esc_attr( $prefs['one_time']['payment'] ); ?>" /><br />
					<span class="description">This is what is shown to the payment processor and credit card statements.</span>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<h3>Pledged Auto Shop Payment</h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-payments-">Enable</label></th>
				<td>
					<label for=""><input type="checkbox" name="wak_payment_plans_prefs[pledged][enabled]" id=""<?php checked( $prefs['pledged']['enabled'], 1 ); ?> value="1" /></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments-">Cost</label></th>
				<td>
					$ <input type="text" size="12" placeholder="0.00" name="wak_payment_plans_prefs[pledged][cost]" id="" value="<?php echo esc_attr( $prefs['pledged']['cost'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments">Label</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payment_plans_prefs[pledged][label]" id="" value="<?php echo esc_attr( $prefs['pledged']['label'] ); ?>" /><br />
					<span class="description">This is what members see when they are asked to select a payment plan when making a payment.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments">Payment Description</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payment_plans_prefs[pledged][payment]" id="" value="<?php echo esc_attr( $prefs['pledged']['payment'] ); ?>" /><br />
					<span class="description">This is what is shown to the payment processor and credit card statements.</span>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<?php submit_button( __( 'Update Settings', 'wakpayments' ), 'primary large', 'submit' ); ?>

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
if ( ! function_exists( 'wak_payment_log_admin_screen' ) ) :
	function wak_payment_log_admin_screen() {

		$args = array();

		$number = get_user_meta( get_current_user_id(), 'wak_payments_per_page', true );
		if ( $number != '' )
			$args['number'] = absint( $number );

		if ( isset( $_GET['status'] ) )
			$args['status'] = absint( $_GET['status'] );

		if ( isset( $_GET['object_id'] ) )
			$args['object_id'] = absint( $_GET['object_id'] );

		if ( isset( $_GET['user_id'] ) )
			$args['user_id'] = absint( $_GET['user_id'] );

		if ( isset( $_GET['paged'] ) )
			$args['paged'] = absint( $_GET['paged'] );

		if ( isset( $_GET['s'] ) )
			$args['payment_id'] = sanitize_text_field( $_GET['s'] );

		$payments = new WAK_Query_Payments( $args );

?>
<div class="wrap">
	<h2><?php _e( 'Payment Log', 'wakpayments' ); ?></h2>
	<?php

		if ( isset( $_GET['updated'] ) ) {

			if ( $_GET['updated'] == 1 )
				echo '<div id="message" class="updated"><p>' . ( ( isset( $_GET['multi'] ) ? sprintf( _n( 'Payment Approved.', 'Approved %d Payments.', $_GET['multi'], '' ), $_GET['multi'] ) : 'Payment Approved.' ) ) . '</p></div>';

			elseif ( $_GET['updated'] == 3 )
				echo '<div id="message" class="updated"><p>Subscription Cancelled.</p></div>';

		}

		elseif ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 1 )
			echo '<div id="message" class="error"><p>' . ( ( isset( $_GET['multi'] ) ? sprintf( _n( 'Payment Deleted.', '%d Payments were successfully deleted.', $_GET['multi'], '' ), $_GET['multi'] ) : 'Payment Deleted.' ) ) . '</p></div>';

		elseif ( isset( $_GET['error'] ) ) {

			if ( $_GET['error'] == 1 )
				echo '<div id="message" class="error"><p>' . urldecode( $_GET['message'] ) . '</p></div>';

		}

?>
	<?php $payments->status_filter(); ?>
	<form id="review-list" method="get" action="admin.php">
		<input type="hidden" name="page" value="premium-autoshops" />
		<p class="search-box">
			<input type="search" name="s" placeholder="Payment ID" value="<?php if ( isset( $_GET['s'] ) ) echo esc_attr( $_GET['s'] ); ?>" />
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
				<?php $payments->pagination(); ?>
			</div>

			<br class="clear" />

		</div>
		<table class="wp-list-table widefat fixed striped posts">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>
					<th scope="col" id="auto" class="manage-column column-auto auto-column">Auto Shop</th>
					<?php if ( ! isset( $_GET['status'] ) ) : ?><th scope="col" id="status" class="manage-column column-status status-column">Status</th><?php endif; ?>
					<th scope="col" id="user" class="manage-column column-user user-column">User</th>
					<th scope="col" id="amount" class="manage-column column-amount amount-column">Amount Paid</th>
					<th scope="col" id="tid" class="manage-column column-tid tid-column">Transaction ID</th>
					<th scope="col" id="date" class="manage-column column-date date-column">Date</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>
					<th scope="col" class="manage-column column-auto auto-column">Auto Shop</th>
					<?php if ( ! isset( $_GET['status'] ) ) : ?><th scope="col" class="manage-column column-status status-column">Status</th><?php endif; ?>
					<th scope="col" class="manage-column column-user user-column">User</th>
					<th scope="col" class="manage-column column-amount amount-column">Amount Paid</th>
					<th scope="col" class="manage-column column-tid tid-column">Transaction ID</th>
					<th scope="col" class="manage-column column-date date-column">Date</th>
				</tr>
			</tfoot>
			<tbody>
<?php

		if ( $payments->have_entries() ) {

			$date_format = get_option( 'date_format' );
			foreach ( $payments->results as $entry ) {

				$user = get_userdata( $entry->user_id );

?>
				<tr id="<?php echo $entry->id ?>">
					<th scope="row" class="check-column"><input type="checkbox" id="review-<?php echo $entry->id; ?>"<?php if ( $entry->subscription_id != '' && $entry->status == 2 ) echo ' disabled="disabled"'; ?> name="payments[]" value="<?php echo $entry->id; ?>" /></th>
					<td class="auto-column">
						<strong><?php echo get_the_title( $entry->object_id ); ?></strong>

						<?php echo $payments->row_actions( $entry ); ?>

					</td>
					<?php if ( ! isset( $_GET['status'] ) ) : ?><td class="status-column"><?php echo wak_get_payment_status( $entry->status ); ?><?php if ( $entry->subscription_id != '' ) echo '<br /><small>Subscription ID: ' . $entry->subscription_id . '</small>'; elseif ( $entry->transaction_id != '' ) echo '<br /><small>Transaction ID: ' . $entry->transaction_id . '</small>'; ?></td><?php endif; ?>
					<td class="user-column"><?php if ( isset( $user->display_name ) ) echo $user->display_name; else echo '-'; ?></td>
					<td class="amount-column">$ <?php echo number_format( $entry->amount_paid, 2, '.', ',' ); ?></td>
					<td class="tid-column"><small><?php echo esc_attr( $entry->payment_id ); ?></small></td>
					<td class="date-column"><?php echo date_i18n( $date_format, $entry->time ); ?></td>
				</tr>
<?php

			}

		}

		else {

?>
				<tr>
					<td colspan="<?php if ( ! isset( $_GET['status'] ) ) echo 7; else echo 6; ?>">No payments found.</td>
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
if ( ! function_exists( 'wak_payment_settings_admin_screen' ) ) :
	function wak_payment_settings_admin_screen() {

		$prefs = wak_payments_plugin_settings();

?>
<div class="wrap">
	<h2><?php _e( 'Payment Settings', 'wakpayments' ); ?></h2>
	<form method="post" action="options.php">

		<?php settings_fields( 'wak-payments-prefs' ); ?>

		<h3><?php _e( 'General', '' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-payments-plugin-prefs-disable-pay">Free Signup Only</label></th>
				<td>
					<label for="wak-payments-plugin-prefs-disable-pay"><input type="checkbox" name="wak_payments_plugin_prefs[disable_pay_signup]" id="wak-payments-plugin-prefs-disable-pay"<?php checked( $prefs['disable_pay_signup'], 1 ); ?> value="1" /> Only offer free signups for new auto shops. Premium and Pledged signups are hidden.</label>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<h3><?php _e( 'Templates', '' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-payments-plugin-prefs-templates-button">Button Label</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payments_plugin_prefs[templates][button]" id="wak-payments-plugin-prefs-templates-button" value="<?php echo esc_attr( $prefs['templates']['button'] ); ?>" /><br />
					<span class="description">The button label to use for upgrading auto shops.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments-plugin-prefs-templates-title">Modal Title</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payments_plugin_prefs[templates][title]" id="wak-payments-plugin-prefs-templates-title" value="<?php echo esc_attr( $prefs['templates']['title'] ); ?>" /><br />
					<span class="description">The main title shown to users when they click on the button.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label>Info</label></th>
				<td>
					<p><span class="description">Information shown when the user clicks to upgrade their auto shop.</span></p>
					<?php wp_editor( $prefs['templates']['info'], 'wakpaymentstemplatesinfo', array( 'textarea_name' => 'wak_payments_plugin_prefs[templates][info]', 'textarea_rows' => 15 ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments-plugin-prefs-terms">Terms & Conditions</label></th>
				<td>
					<?php wp_dropdown_pages( array( 'name' => 'wak_payments_plugin_prefs[terms_page_id]', 'id' => 'wak-payments-plugin-prefs-terms', 'selected' => $prefs['terms_page_id'] ) ); ?><br />
					<span class="description">The page containing terms and conditions.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments-plugin-prefs-templates-submit">Submit Payment Button Label</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payments_plugin_prefs[templates][submit]" id="wak-payments-plugin-prefs-templates-submit" value="<?php echo esc_attr( $prefs['templates']['submit'] ); ?>" /><br />
					<span class="description">The button label to use on the bottom of the payment form.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments-plugin-prefs-templates-paid-signup">Paid Signup</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payments_plugin_prefs[templates][paid-signup]" id="wak-payments-plugin-prefs-templates-paid-signup" value="<?php echo esc_attr( $prefs['templates']['paid-signup'] ); ?>" /><br />
					<span class="description">Information shown to users when they signup and paid for their auto shop listing.</span>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>
		
		<h3>Authorize.net</h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-payments-plugin-prefs-authorize_net_api">API Login ID</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payments_plugin_prefs[authorize_net_api]" id="wak-payments-plugin-prefs-authorize_net_api" value="<?php echo esc_attr( $prefs['authorize_net_api'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments-plugin-prefs-authorize_net_key">Transaction Key</label></th>
				<td>
					<input type="text" class="regular-text" name="wak_payments_plugin_prefs[authorize_net_key]" id="wak-payments-plugin-prefs-authorize_net_key" value="<?php echo esc_attr( $prefs['authorize_net_key'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-payments-plugin-prefs-authorize_net_test">Test Mode</label></th>
				<td>
					<label for=""><input type="checkbox" name="wak_payments_plugin_prefs[authorize_net_test]" id=""<?php checked( $prefs['authorize_net_test'], 1 ); ?> value="1" /></label>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<?php submit_button( __( 'Update Settings', 'wakpayments' ), 'primary large', 'submit' ); ?>

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
if ( ! function_exists( 'wak_payments_wp_footer' ) ) :
	function wak_payments_wp_footer() {

		if ( ! is_user_logged_in() ) return;

		$prefs = wak_payments_plugin_settings();

?>
<div class="modal fade" role="dialog" aria-hidden="true" id="upgrade-autoshop-premium">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title blue"><?php echo esc_attr( $prefs['templates']['title'] ); ?></h4>
			</div>
			<div class="modal-body">
				<h1 class="text-center pink"><i class="fa fa-spinner fa-spin blue"></i></h1>
				<p class="text-center"><?php _e( 'loading ...', 'wakpayments' ); ?></p>
			</div>
		</div>
	</div>
</div>
<?php

	}
endif;


/**
 * 
 * @since 1.0
 * @version 1.0
 */


/**
 * 
 * @since 1.0
 * @version 1.0
 */



?>