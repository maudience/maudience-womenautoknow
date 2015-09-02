<?php
// No dirrect access
if ( ! defined( 'WAK_AUTOSHOPS_VER' ) ) exit;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_template_redirect' ) ) :
	function wak_autoshops_template_redirect( $template ) {

		if ( is_post_type_archive( 'autoshops' ) )
			return WAK_AUTOSHOPS_TEMPLATES . 'archive-autoshops.php';

		return $template;

	}
endif;

/**
 * 
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_wp_footer' ) ) :
	function wak_autoshops_wp_footer() {

?>
<div class="modal fade" role="dialog" aria-hidden="true" id="add-new-wak-autoshop">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title pink"><i class="fa fa-street-view"></i><?php _e( 'Add New Auto Shop', '' ); ?></h4>
			</div>
			<div class="modal-body">
				<h1 class="text-center pink"><i class="fa fa-spinner fa-spin"></i></h1>
				<p class="text-center"><?php _e( 'Loading form...', '' ); ?></p>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" role="dialog" aria-hidden="true" id="front-end-autoshop-edit">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title pink"><i class="fa fa-pencil-square-o"></i><?php _e( 'Edit Auto Shop', '' ); ?></h4>
			</div>
			<div class="modal-body">
				<h1 class="text-center pink"><i class="fa fa-spinner fa-spin"></i></h1>
				<p class="text-center"><?php _e( 'Loading form...', '' ); ?></p>
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
if ( ! function_exists( 'wak_autoshops_admin_filter_option' ) ) :
	function wak_autoshops_admin_filter_option() {

		if ( ! isset( $_GET['post_type'] ) || $_GET['post_type'] != 'autoshops' ) return;

		$shop_type = ( isset( $_GET['shop_type'] ) ) ? sanitize_key( $_GET['shop_type'] ) : '';

		$options = array(
			''        => 'All Types',
			'pledged' => 'Pledged Shops Only',
			'premium' => 'Premium Shops Only',
			'regular' => 'Regular Shops Only'
		);

?><select name="shop_type" id="shop_type"><?php

		foreach ( $options as $value => $label ) {

			echo '<option value="' . $value . '"';
			if ( $shop_type == $value ) echo ' selected="selected"';
			echo '>' . $label . '</option>';

		}

?></select>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_admin_filter_parse' ) ) :
	function wak_autoshops_admin_filter_parse( $query ) {

		if ( ! is_admin() || ! isset( $_GET['post_type'] ) || $_GET['post_type'] != 'autoshops' ) return;

		if ( isset( $_GET['shop_type'] ) && in_array( $_GET['shop_type'], array( 'pledged', 'premium', 'regular' ) ) ) {

			if ( $_GET['shop_type'] == 'pledged' ) {

				$query->query_vars['meta_key']   = 'pledged';
				$query->query_vars['meta_value'] = 1;

			}

			elseif ( $_GET['shop_type'] == 'premium' ) {

				$query->query_vars['meta_query'] = array(
					array(
						'key'     => 'premium_until',
						'value'   => '',
						'compare' => '!='
					)
				);

			}

			elseif ( $_GET['shop_type'] == 'regular' ) {

				$query->query_vars['meta_query'] = array(
					'relation' => 'OR',
					array(
						'key'     => 'pledged',
						'value'   => 1,
						'compare' => '!='
					),
					array(
						'key'     => 'pledged',
						'compare' => 'NOT EXISTS'
					)
				);

			}

		}

		elseif ( isset( $_GET['user_id'] ) && is_numeric( $_GET['user_id'] ) ) {

			$user_id = absint( $_GET['user_id'] );

			$query->query_vars['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => 'added_by',
					'value'   => $user_id
				),
				array(
					'key'     => 'owner_id',
					'value'   => $user_id
				)
			);

		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_excerpt_more' ) ) :
	function wak_autoshop_excerpt_more( $more ) {
		return ' - <a class="read-more" href="' . get_permalink( get_the_ID() ) . '">' . __( 'Read More', '' ) . '</a>';
	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_metaboxes' ) ) :
	function wak_autoshop_metaboxes() {

		add_meta_box(
			'wak-theme-autoshop-address',
			'Address',
			'wak_autoshop_address_metabox',
			'autoshops',
			'normal',
			'high'
		);

		add_meta_box(
			'wak-theme-autoshop-contact',
			'Contact Details',
			'wak_autoshop_contact_metabox',
			'autoshops',
			'normal',
			'high'
		);

		add_meta_box(
			'wak-theme-autoshop-pledges',
			'Pledged',
			'wak_autoshop_pledged_metabox',
			'autoshops',
			'side',
			'default'
		);

		add_meta_box(
			'wak-theme-autoshop-premium',
			'Premium',
			'wak_autoshop_premium_metabox',
			'autoshops',
			'side',
			'default'
		);

		add_meta_box(
			'wak-theme-autoshop-oh',
			'Opening Hours',
			'wak_autoshop_oh_metabox',
			'autoshops',
			'side',
			'default'
		);

		add_meta_box(
			'wak-theme-autoshop-owner',
			'Owner',
			'wak_autoshop_owner_metabox',
			'autoshops',
			'side',
			'default'
		);

		add_meta_box(
			'wak-theme-autoshop-addedby',
			'Added by',
			'wak_autoshop_addby_metabox',
			'autoshops',
			'side',
			'default'
		);

		remove_meta_box( 'mymetabox_revslider_0', 'autoshops', 'normal' );
		remove_meta_box( 'authordiv', 'autoshops', 'normal' );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_address_metabox' ) ) :
	function wak_autoshop_address_metabox( $post ) {

		$address1 = get_post_meta( $post->ID, 'address1', true );
		$address2 = get_post_meta( $post->ID, 'address2', true );
		$zip      = get_post_meta( $post->ID, 'zip', true );
		$city     = get_post_meta( $post->ID, 'city', true );
		$state    = get_post_meta( $post->ID, 'state', true );

?>
<input type="text" name="wak_autoshop_edit[address][line1]" id="wak-autoshop-line1" class="large-text code" placeholder="Address" value="<?php echo esc_attr( $address1 ); ?>" />
<input type="text" name="wak_autoshop_edit[address][line2]" id="wak-autoshop-line2" class="large-text code" placeholder="Line 2" value="<?php echo esc_attr( $address2 ); ?>" />
<input type="text" name="wak_autoshop_edit[address][zip]" id="wak-autoshop-zip" class="large-text code" placeholder="Zip Code" value="<?php echo esc_attr( $zip ); ?>" style="width: 30%;" />
<input type="text" name="wak_autoshop_edit[address][city]" id="wak-autoshop-city" class="large-text code" placeholder="City" value="<?php echo esc_attr( $city ); ?>" style="width: 30%;" />
<?php echo wak_states_dropdown( 'wak_autoshop_edit[address][state]', 'wak-autoshop-state', 'Select State', $state ); ?>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_contact_metabox' ) ) :
	function wak_autoshop_contact_metabox( $post ) {

		$phone    = get_post_meta( $post->ID, 'phone', true );
		$website  = get_post_meta( $post->ID, 'website', true );
		$name     = get_post_meta( $post->ID, 'contact_name', true );
		$email    = get_post_meta( $post->ID, 'contact_email', true );
		$facebook = get_post_meta( $post->ID, 'facebook', true );
		$twitter  = get_post_meta( $post->ID, 'twitter', true );

?>
<input type="text" name="wak_autoshop_edit[contact][phone]" id="wak-autoshop-phone" class="large-text code" placeholder="Public Phone Number" value="<?php echo esc_attr( $phone ); ?>" />
<input type="text" name="wak_autoshop_edit[contact][website]" id="wak-autoshop-website" class="large-text code" placeholder="Website URL" value="<?php echo esc_attr( $website ); ?>" />
<input type="text" name="wak_autoshop_edit[contact][contact_name]" id="wak-autoshop-contact-name" class="large-text code" placeholder="Contact Persons Name" value="<?php echo esc_attr( $name ); ?>" />
<input type="text" name="wak_autoshop_edit[contact][contact_email]" id="wak-autoshop-contact-email" class="large-text code" placeholder="Public Email" value="<?php echo esc_attr( $email ); ?>" />
<input type="text" name="wak_autoshop_edit[contact][facebook]" id="wak-autoshop-contact-facebook" class="large-text code" placeholder="Facebook URL" value="<?php echo esc_attr( $facebook ); ?>" />
<input type="text" name="wak_autoshop_edit[contact][twitter]" id="wak-autoshop-contact-twitter" class="large-text code" placeholder="Twitter URL" value="<?php echo esc_attr( $twitter ); ?>" />
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_pledged_metabox' ) ) :
	function wak_autoshop_pledged_metabox( $post ) {

		$service_cert  = get_post_meta( $post->ID, 'service_cert', true );
		$service_hours = get_post_meta( $post->ID, 'service_hours', true );
		$service_state = get_post_meta( $post->ID, 'service_state', true );
		$owner_id      = wak_get_autoshop_owner( $post->ID );
		$pledged       = get_post_meta( $post->ID, 'pledged', true );
		$pledged_date  = get_post_meta( $post->ID, 'pledged_date', true );

		$now = current_time( 'timestamp' );
		if ( $pledged_date != '' ) {

			if ( count( explode( '-', $pledged_date ) ) == 3 )
				$pledged_date = date( 'm/d/Y', strtotime( $pledged_date, $now ) );
			else
				$pledged_date = date( 'm/d/Y', $pledged_date );

		}

		$connect = '';

?>
<style type="text/css">
#wak-theme-autoshop-pledges .form-group { height: 30px; line-height: 30px; margin-bottom: 12px; }
#wak-theme-autoshop-pledges .form-groups label { font-weight: bold; display: block; }
</style>
<div class="form-group">
	<label for="wak-autoshop-pledged"><input type="checkbox" name="wak_autoshop_edit[service][pledged]" id="wak-autoshop-pledged" value="1"<?php checked( $pledged, 1 ); ?> /> Pledged Shop</label>
</div>
<div class="form-groups">
	<label>Pledge Date</label>
	<input type="text" name="wak_autoshop_edit[service][pledged_date]" placeholder="mm/dd/yyyy" id="wak-autoshop-pledged-date" class="large-text code" value="<?php echo esc_attr( $pledged_date ); ?>" style="width: 99%;" />
</div>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_premium_metabox' ) ) :
	function wak_autoshop_premium_metabox( $post ) {

		$premium_until = get_post_meta( $post->ID, 'premium_until', true );

?>
<style type="text/css">
#wak-theme-autoshop-premium .form-group { }
#wak-theme-autoshop-premium .form-group label { font-weight: bold; display: block; }
</style>
<div class="form-group">
	<label>End Date</label>
	<input type="text" name="wak_autoshop_edit[premium_until]" id="wak-autoshop-premium-date" class="large-text code" value="<?php echo esc_attr( $premium_until ); ?>" style="width: 99%;" placeholder="mm/dd/yyyy" />
	<small>To enable premium listing for this auto shop, an end date must be set.</small>
</div>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_oh_metabox' ) ) :
	function wak_autoshop_oh_metabox( $post ) {

		$opening_hours = wp_parse_args( get_post_meta( $post->ID, 'oh', true ), array(
			'monday'         => array(
				'from' => '',
				'to'   => ''
			),
			'tuesday'        => array(
				'from' => '',
				'to'   => ''
			),
			'wednesday'      => array(
				'from' => '',
				'to'   => ''
			),
			'thursday'       => array(
				'from' => '',
				'to'   => ''
			),
			'friday'         => array(
				'from' => '',
				'to'   => ''
			),
			'saturday'       => array(
				'from' => '',
				'to'   => ''
			),
			'sunday'         => array(
				'from' => '',
				'to'   => ''
			)
		) );

?>
<p><strong>Monday</strong><br /><input type="text" name="wak_autoshop_edit[oh][monday][from]" id="wak-auto-oh-monday-from" value="<?php echo esc_attr( $opening_hours['monday']['from'] ); ?>" size="14" placeholder="from" /> - <input type="text" name="wak_autoshop_edit[oh][monday][to]" id="wak-auto-oh-monday-to" value="<?php echo esc_attr( $opening_hours['monday']['to'] ); ?>" size="14" placeholder="to" /></p>
<p><strong>Tuesday</strong><br /><input type="text" name="wak_autoshop_edit[oh][tuesday][from]" id="wak-auto-oh-tuesday-from" value="<?php echo esc_attr( $opening_hours['tuesday']['from'] ); ?>" size="14" placeholder="from" /> - <input type="text" name="wak_autoshop_edit[oh][tuesday][to]" id="wak-auto-oh-tuesday-to" value="<?php echo esc_attr( $opening_hours['tuesday']['to'] ); ?>" size="14" placeholder="to" /></p>
<p><strong>Wednesday</strong><br /><input type="text" name="wak_autoshop_edit[oh][wednesday][from]" id="wak-auto-oh-wednesday-from" value="<?php echo esc_attr( $opening_hours['wednesday']['from'] ); ?>" size="14" placeholder="from" /> - <input type="text" name="wak_autoshop_edit[oh][wednesday][to]" id="wak-auto-oh-wednesday-to" value="<?php echo esc_attr( $opening_hours['wednesday']['to'] ); ?>" size="14" placeholder="to" /></p>
<p><strong>Thursday</strong><br /><input type="text" name="wak_autoshop_edit[oh][thursday][from]" id="wak-auto-oh-thursday-from" value="<?php echo esc_attr( $opening_hours['thursday']['from'] ); ?>" size="14" placeholder="from" /> - <input type="text" name="wak_autoshop_edit[oh][thursday][to]" id="wak-auto-oh-thursday-to" value="<?php echo esc_attr( $opening_hours['thursday']['to'] ); ?>" size="14" placeholder="to" /></p>
<p><strong>Friday</strong><br /><input type="text" name="wak_autoshop_edit[oh][friday][from]" id="wak-auto-oh-friday-from" value="<?php echo esc_attr( $opening_hours['friday']['from'] ); ?>" size="14" placeholder="from" /> - <input type="text" name="wak_autoshop_edit[oh][friday][to]" id="wak-auto-oh-friday-to" value="<?php echo esc_attr( $opening_hours['friday']['to'] ); ?>" size="14" placeholder="to" /></p>
<p><strong>Saturday</strong><br /><input type="text" name="wak_autoshop_edit[oh][saturday][from]" id="wak-auto-oh-saturday-from" value="<?php echo esc_attr( $opening_hours['saturday']['from'] ); ?>" size="14" placeholder="from" /> - <input type="text" name="wak_autoshop_edit[oh][saturday][to]" id="wak-auto-oh-saturday-to" value="<?php echo esc_attr( $opening_hours['saturday']['to'] ); ?>" size="14" placeholder="to" /></p>
<p><strong>Sunday</strong><br /><input type="text" name="wak_autoshop_edit[oh][sunday][from]" id="wak-auto-oh-sunday-from" value="<?php echo esc_attr( $opening_hours['sunday']['from'] ); ?>" size="14" placeholder="from" /> - <input type="text" name="wak_autoshop_edit[oh][sunday][to]" id="wak-auto-oh-sunday-to" value="<?php echo esc_attr( $opening_hours['sunday']['to'] ); ?>" size="14"  placeholder="to"/></p>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_owner_metabox' ) ) :
	function wak_autoshop_owner_metabox( $post ) {

		$owner_name = '';
		$owner_type = '';

		$owner_id = wak_get_autoshop_owner( $post->ID );
		if ( is_numeric( $owner_id ) ) {

			$user = get_userdata( $owner_id );
			$owner_name = $user->display_name;

		}

?>
<p><label>Owners Name:</label> <strong><?php echo esc_attr( $owner_name ); ?></strong><br /><label>Account Type:</label> <strong><?php echo wak_theme_display_users_account_type( $owner_id ); ?></strong></p>
<div class="form-group">
	<label for="wak-autoshop-owner-id">Owner ID:</label><br />
	<input type="text" name="wak_autoshop_edit[owner_id]" id="wak-autoshop-owner-id" class="large-text code" value="<?php echo esc_attr( $owner_id ); ?>" style="width: 136px;" />
</div>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_addby_metabox' ) ) :
	function wak_autoshop_addby_metabox( $post ) {

		$owner_name = '';
		$owner_type = '';
		$owner_id   = '';

		$addedby = get_post_meta( $post->ID, 'added_by', true );
		if ( is_numeric( $addedby ) ) {

			$user = get_userdata( $addedby );
			$owner_name = $user->display_name;
			$owner_id = $user->ID;

		}

?>
<p><label>Added by:</label> <strong><?php echo esc_attr( $owner_name ); ?></strong><br /><label>Account Type:</label> <strong><?php echo wak_theme_display_users_account_type( $owner_id ); ?></strong></p>
<div class="form-group">
	<label for="wak-autoshop-addedby-id">User ID:</label><br />
	<input type="text" name="wak_autoshop_edit[added_by]" id="wak-autoshop-addedby-id" class="large-text code" value="<?php echo esc_attr( $addedby ); ?>" style="width: 136px;" />
</div>
<?php

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_save_autoshop' ) ) :
	function wak_autoshops_save_autoshop( $post_id ) {

		if ( isset( $_POST['wak_autoshop_edit']['address'] ) ) {

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['address']['line1'] );
			update_post_meta( $post_id, 'address1', $value );

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['address']['line2'] );
			update_post_meta( $post_id, 'address2', $value );

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['address']['zip'] );
			update_post_meta( $post_id, 'zip', $value );

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['address']['city'] );
			update_post_meta( $post_id, 'city', $value );

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['address']['state'] );
			update_post_meta( $post_id, 'state', $value );

		}

		if ( isset( $_POST['wak_autoshop_edit']['contact'] ) ) {

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['contact']['phone'] );
			update_post_meta( $post_id, 'phone', $value );

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['contact']['website'] );
			update_post_meta( $post_id, 'website', $value );

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['contact']['contact_name'] );
			update_post_meta( $post_id, 'contact_name', $value );

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['contact']['contact_email'] );
			update_post_meta( $post_id, 'contact_email', $value );

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['contact']['facebook'] );
			update_post_meta( $post_id, 'facebook', $value );

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['contact']['twitter'] );
			update_post_meta( $post_id, 'twitter', $value );

		}

		if ( isset( $_POST['wak_autoshop_edit']['service'] ) ) {

			//$value = absint( $_POST['wak_autoshop_edit']['service']['service_cert'] );
			//update_post_meta( $post_id, 'service_cert', $value );

			//$value = sanitize_text_field( $_POST['wak_autoshop_edit']['service']['service_hours'] );
			//update_post_meta( $post_id, 'service_hours', $value );

			//$value = absint( $_POST['wak_autoshop_edit']['service']['service_state'] );
			//update_post_meta( $post_id, 'service_state', $value );

			$value = ( isset( $_POST['wak_autoshop_edit']['service']['pledged'] ) ) ? 1 : 0;
			update_post_meta( $post_id, 'pledged', $value );

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['service']['pledged_date'] );
			if ( $value != '' ) $value = strtotime( $value, current_time( 'timestamp' ) );

			update_post_meta( $post_id, 'pledged_date', $value );

		}

		if ( isset( $_POST['wak_autoshop_edit']['premium_until'] ) ) {

			$current = get_post_meta( $post_id, 'premium_until', true );
			$premium = sanitize_text_field( $_POST['wak_autoshop_edit']['premium_until'] );
			if ( strlen( $premium ) != 10 ) {

				delete_post_meta( $post_id, 'premium_until' );

				if ( strlen( $current ) == 10 )
					do_action( 'wak_cancel_autoshop_premium', $post_id, $current );

			}
			else
				update_post_meta( $post_id, 'premium_until', $premium );

		}

		if ( isset( $_POST['wak_autoshop_edit']['owner_id'] ) ) {

			$old_owner_id = get_post_meta( $post_id, 'premium_until', true );
			$value        = sanitize_text_field( $_POST['wak_autoshop_edit']['owner_id'] );
			if ( strlen( $value ) == 0 ) {

				delete_post_meta( $post_id, 'owner_id' );

				do_action( 'wak_remove_autoshop_owner', $post_id, $old_owner_id );

			}
			elseif ( strlen( $value ) > 0 && $old_owner_id != $value ) {

				update_post_meta( $post_id, 'owner_id', absint( $value ) );

				do_action( 'wak_change_autoshop_owner', $post_id, $old_owner_id, $value );

			}
			else
				update_post_meta( $post_id, 'owner_id', absint( $value ) );

		}

		if ( isset( $_POST['wak_autoshop_edit']['added_by'] ) ) {

			$value = sanitize_text_field( $_POST['wak_autoshop_edit']['added_by'] );
			if ( strlen( $value ) == 0 )
				delete_post_meta( $post_id, 'added_by' );
			else
				update_post_meta( $post_id, 'added_by', absint( $value ) );

		}

		if ( isset( $_POST['wak_autoshop_edit']['oh'] ) ) {

			$value = wp_parse_args( $_POST['wak_autoshop_edit']['oh'], array(
				'monday'         => array(
					'from' => '',
					'to'   => ''
				),
				'tuesday'        => array(
					'from' => '',
					'to'   => ''
				),
				'wednesday'      => array(
					'from' => '',
					'to'   => ''
				),
				'thursday'       => array(
					'from' => '',
					'to'   => ''
				),
				'friday'         => array(
					'from' => '',
					'to'   => ''
				),
				'saturday'       => array(
					'from' => '',
					'to'   => ''
				),
				'sunday'         => array(
					'from' => '',
					'to'   => ''
				)
			) );

			update_post_meta( $post_id, 'oh', $value );

		}

		delete_option( 'wak_autoshop_count' );

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_title_here' ) ) :
	function wak_autoshops_title_here( $title ) {

		global $post;

		if ( $post->post_type == 'autoshops' )
			$title = 'Company Name';

		return $title;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_column_headers' ) ) :
	function wak_autoshops_column_headers( $default ) {

		$columns            = array();
		$columns['cb']      = $default['cb'];
		$columns['title']   = $default['title'];
		$columns['address'] = __( 'Address', 'wakauto' );
		$columns['type']    = __( 'Type', 'wakauto' );
		$columns['owner']   = __( 'Owner', 'wakauto' );
		$columns['addedby'] = __( 'Added by', 'wakauto' );
		$columns['rating']  = __( 'Rating', 'wakauto' );
		$columns['reviews'] = __( 'Reviews', 'wakauto' );

		return $columns;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_column_content' ) ) :
	function wak_autoshops_column_content( $column, $post_id ) {

		$pending = false;
		if ( function_exists( 'autoshop_has_pending_registration' ) && autoshop_has_pending_registration( $post_id ) )
			$pending = true;

		switch ( $column ) {

			case 'address' :

				$address = show_autoshop_address( $post_id, false );
				if ( $address == '' )
					echo '-';
				else
					echo $address;

			break;

			case 'type' :

				if ( autoshop_has_pledged( $post_id ) )
					echo 'Pledged Auto Shop';
				elseif ( autoshop_is_premium( $post_id ) ) {
					echo 'Premium Auto Shop';
					echo '<br /><small>Expires: ' . get_post_meta( $post_id, 'premium_until', true ) . '</small>';
				}
				else
					echo 'Regular Auto Shop';

			break;

			case 'owner' :

				$user = wak_get_autoshop_owner( $post_id );
				if ( is_numeric( $user ) ) {

					$user = get_userdata( $user );
					if ( isset( $user->ID ) )
						echo $user->display_name . '<br /><small>ID: ' . $user->ID . '</small>';
					else
						echo '-';

				}
				else {

					if ( $pending )
						echo 'User Pending Registration';
					else
						echo '-';

				}

			break;

			case 'addedby' :

				$user = get_post_meta( $post_id, 'added_by', true );
				if ( is_numeric( $user ) ) {

					$user = get_userdata( $user );
					if ( isset( $user->ID ) )
						echo $user->display_name . '<br /><small>ID: ' . $user->ID . '</small>';
					else
						echo '-';

				}
				else {

					if ( $pending )
						echo 'User Pending Registration';
					else
						echo '-';

				}

			break;

		}

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshops_post_classes' ) ) :
	function wak_autoshops_post_classes( $classes ) {

		$post_id = get_the_ID();

		if ( autoshop_has_pledged( $post_id ) )
			$classes[] = 'pledged';

		if ( autoshop_is_premium( $post_id ) )
			$classes[] = 'premium';

		return $classes;

	}
endif;

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_settings_admin_screen' ) ) :
	function wak_autoshop_settings_admin_screen() {

		$prefs = wak_autoshops_plugin_settings();

		$mod_options = array(
			0 => 'No moderation - all reviews are approved automatically',
			1 => 'Partial moderation - only the users first review is moderated',
			2 => 'Full moderation - all reviews must be approved before published'
		);

?>
<div class="wrap">
	<h2><?php _e( 'Settings', '' ); ?></h2>
	<form method="post" action="options.php">

		<?php settings_fields( 'wak-autoshops-prefs' ); ?>

		<h3><?php _e( 'Reviews - Visitors', '' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wakautoshopprefscalltoact">Signup Information</label></th>
				<td>
					<p><span class="description">Information to show visitors trying to view an auto shops reviews. Also shown to members that have logged out.</span></p>
					<?php wp_editor( $prefs['visitors_viewing_review'], 'viewvisitorreview', array( 'textarea_name' => 'wak_autoshop_plugin_prefs[visitors_viewing_review]', 'textarea_rows' => 15 ) ); ?>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>
		<h3><?php _e( 'Reviews - General', '' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-autoshop-plugin-prefs-review_mod">Moderation</label></th>
				<td>
					<select name="wak_autoshop_plugin_prefs[review_mod]" id="wak-autoshop-plugin-prefs-review_mod"><?php

		foreach ( $mod_options as $value => $label ) {
			echo '<option value="' . $value . '"';
			if ( $prefs['review_mod'] == $value ) echo ' selected="selected"';
			echo '>' . $label . '</option>';
		}

?></select>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<h3><?php _e( 'Review Carousel', '' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-autoshop-plugin-prefs-carousel_number">Number of Reviews</label></th>
				<td>
					<input type="number" name="wak_autoshop_plugin_prefs[carousel_number]" id="wak-autoshop-plugin-prefs-carousel_number" value="<?php echo absint( $prefs['carousel_number'] ); ?>" /><br />
					<span class="description">The number of reviews to show in the carousel.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wak-autoshop-plugin-prefs-caoursel_review_length">Review Length</label></th>
				<td>
					<input type="number" name="wak_autoshop_plugin_prefs[caoursel_review_length]" id="wak-autoshop-plugin-prefs-caoursel_review_length" value="<?php echo absint( $prefs['caoursel_review_length'] ); ?>" /><br />
					<span class="description">The maximum length of a review in the carousel.</span>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>
		<h3><?php _e( 'Review Carousel - Call to Action', '' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wak-autoshop-plugin-prefs-carousel_frequency">Insert Frequency</label></th>
				<td>
					<input type="number" name="wak_autoshop_plugin_prefs[carousel_frequency]" id="wak-autoshop-plugin-prefs-carousel_frequency" value="<?php echo absint( $prefs['carousel_frequency'] ); ?>" /><br />
					<span class="description">Select how often the "Call to action" message is to be inserted into the carousel. If a user has not yet left a review, the frequency is doubled. So if you set to show the call to action content after every 6th review, then the user will see it after every third review. Use zero to disable.</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wakautoshopprefscalltoact">Content</label></th>
				<td>
					<p><span class="description">Optional content to insert between reviews in the carousel.</span></p>
					<?php wp_editor( $prefs['carousel_call_to_action'], 'wakautoshopprefscalltoact', array( 'textarea_name' => 'wak_autoshop_plugin_prefs[carousel_call_to_action]', 'textarea_rows' => 15 ) ); ?>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>

		<?php submit_button( __( 'Update Settings', '' ), 'primary large', 'submit' ); ?>

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
if ( ! function_exists( 'wak_autoshop_user_column_headers' ) ) :
	function wak_autoshop_user_column_headers( $columns ) {

		if ( array_key_exists( 'posts', $columns ) )
			unset( $columns['posts'] );

		$columns['autoshops'] = 'Auto Shops';

		return $columns;

	}
endif; 

/**
 *
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'wak_autoshop_user_column_content' ) ) :
	function wak_autoshop_user_column_content( $value, $column_name, $user_id ) {

		if ( $column_name == 'autoshops' ) {

			$autoshop_count = wak_count_users_autoshops( $user_id );
			$url   = add_query_arg( array( 'post_type' => 'autoshops', 'user_id' => $user_id ), admin_url( 'edit.php' ) );

			if ( $autoshop_count['count'] > 0 )
				return '<a href="' . $url . '">' . $autoshop_count['count'] . '</a>';
			else
				return $autoshop_count['count'];

		}

	    return $value;
	}
endif;

?>