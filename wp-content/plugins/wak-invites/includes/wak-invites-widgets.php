<?php
// No dirrect access
if ( ! defined( 'WAK_INVITES_VER' ) ) exit;

/**
 * Widget: Invite
 * @since 1.0
 * @version 1.0
 */
if ( ! class_exists( 'WAK_Invite' ) ) :
	class WAK_Invite extends WP_Widget {

		/**
		 * Construct
		 */
		function WAK_Invite() {

			// Basic details about our widget
			$widget_ops = array( 
				'classname'   => 'widget-wak-invite',
				'description' => __( 'Allows members to invite others to join WAK.', 'wakinvites' )
			);

			$this->WP_Widget( 'wak_invite', __( '(WAK) Invite', 'wakinvites' ), $widget_ops );
			$this->alt_option_name = 'wak_invite';

		}

		/**
		 * Widget Output
		 */
		function widget( $args, $instance ) {

			// Only logged in users
			if ( ! is_user_logged_in() ) return;
			$user_id = get_current_user_id();

			// Make sure we can invite
			if ( ! wak_user_can_invite( $user_id ) ) return;

			extract( $args, EXTR_SKIP );

			echo $before_widget;

			if ( wak_invites_is_pledged_autoshop_owner( $user_id ) ) {

				// Title
				if ( ! empty( $instance['title_shop'] ) )
					echo $before_title . $instance['title_shop'] . $after_title;

				if ( ! empty( $instance['info_shop'] ) )
					echo '<p class="invite-instructions">' . esc_attr( $instance['info_shop'] ) . '</p>';

				$url = get_permalink( WAK_INVITES_LEAD_PAGE_ID );

?>
<div class="form-group">
	<input type="text" id="wak-invite-this-name" placeholder="Recipients Name" class="form-control" value="" />
</div>
<div class="form-group">
	<input type="text" id="wak-invite-this-email" placeholder="Email Address" class="form-control" value="" />
</div>
<div class="form-group">
	<button type="button" id="wak-run-invite" class="btn btn-danger btn-block">Send Invite</button>
</div>
<script type="text/javascript">
jQuery(function($) {

	$(document).ready(function(){

		$( '#wak-run-invite' ).click(function(e){

			e.preventDefault();

			var email_el  = $( '#wak-invite-this-email' );
			var name_el   = $( '#wak-invite-this-name' );
			var submit_el = $(this);

			$.ajax({
				type       : "POST",
				data       : {
					action      : 'wak-send-new-invite',
					token       : '<?php echo wp_create_nonce( 'wak-send-invite' ); ?>',
					type        : 'driver',
					emailinvite : email_el.val(),
					nameinvite  : name_el.val()
				},
				beforeSend : function() {
					submit_el.text( 'Sending...' );
				},
				dataType   : "JSON",
				url        : '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				success    : function( response ) {

					alert( response.data );
					email_el.val( '' );
					name_el.val( '' );
					submit_el.text( 'Send Invite' ).blur();

					if ( response.success )
						location.reload();

				}
			});

		});

	});

});
</script>
<?php

			}
			else {

				// Title
				if ( ! empty( $instance['title_member'] ) )
					echo $before_title . $instance['title_member'] . $after_title;

				if ( ! empty( $instance['info_member'] ) )
					echo '<p class="invite-instructions">' . esc_attr( $instance['info_member'] ) . '</p>';

				$url = get_permalink( WAK_INVITES_LEAD_PAGE_ID );

?>
<div class="form-group">
	<input type="text" id="wak-invite-this-name" placeholder="Recipients Name" class="form-control" value="" />
</div>
<div class="form-group">
	<input type="text" id="wak-invite-this-email" placeholder="Email Address" class="form-control" value="" />
</div>
<div class="form-group">
	<button type="button" id="wak-run-invite" class="btn btn-danger btn-block">Send Invite</button>
</div>
<script type="text/javascript">
jQuery(function($) {

	$(document).ready(function(){

		$( '#wak-run-invite' ).click(function(e){

			e.preventDefault();

			var email_el  = $( '#wak-invite-this-email' );
			var name_el   = $( '#wak-invite-this-name' );
			var submit_el = $(this);

			$.ajax({
				type       : "POST",
				data       : {
					action      : 'wak-send-new-invite',
					token       : '<?php echo wp_create_nonce( 'wak-send-invite' ); ?>',
					type        : 'shop',
					emailinvite : email_el.val(),
					nameinvite  : name_el.val()
				},
				beforeSend : function() {
					submit_el.text( 'Sending...' );
				},
				dataType   : "JSON",
				url        : '<?php echo admin_url( 'admin-ajax.php' ); ?>',
				success    : function( response ) {

					alert( response.data );
					email_el.val( '' );
					name_el.val( '' );
					submit_el.text( 'Send Invite' ).blur();

					if ( response.success )
						window.location.href = '<?php echo esc_js( $url ); ?>';

				}
			});

		});

	});

});
</script>
<?php

			}

			// Footer
			echo $after_widget;

		}

		/**
		 * Outputs the options form on admin
		 */
		function form( $instance ) {

			// Defaults
			$title_member = isset( $instance['title_member'] ) ? esc_attr( $instance['title_member'] ) : 'Invite Friends';
			$title_shop   = isset( $instance['title_shop'] ) ? esc_attr( $instance['title_shop'] ) : 'Invite Your Clients';

			$info_member = isset( $instance['info_member'] ) ? esc_attr( $instance['info_member'] ) : '';
			$info_shop   = isset( $instance['info_shop'] ) ? esc_attr( $instance['info_shop'] ) : '';

?>
<p class="wak-widget-field">
	<label for="<?php echo esc_attr( $this->get_field_id( 'title_member' ) ); ?>"><?php _e( 'Drivers Title', 'wakinvites' ); ?>:</label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title_member' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title_member' ) ); ?>" type="text" value="<?php echo esc_attr( $title_member ); ?>" /><br />
	<span class="description">Title shown for drivers.</span><br />
</p>
<p class="wak-widget-field">
	<label for="<?php echo esc_attr( $this->get_field_id( 'info_member' ) ); ?>">Instruction</label>
	<textarea rows="5" cols="40" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'info_member' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'info_member' ) ); ?>"><?php echo esc_attr( $info_member ); ?></textarea>
</p>
<p class="wak-widget-field">
	<label for="<?php echo esc_attr( $this->get_field_id( 'title_shop' ) ); ?>"><?php _e( 'Pledged Shops Title', 'wakinvites' ); ?>:</label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title_shop' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title_shop' ) ); ?>" type="text" value="<?php echo esc_attr( $title_shop ); ?>" /><br />
	<span class="description">Title shown for pledged auto shop owners.</span><br />
</p>
<p class="wak-widget-field">
	<label for="<?php echo esc_attr( $this->get_field_id( 'info_shop' ) ); ?>">Instruction</label>
	<textarea rows="5" cols="40" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'info_shop' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'info_shop' ) ); ?>"><?php echo esc_attr( $info_shop ); ?></textarea>
</p>
<?php

		}

		/**
		 * Processes widget options to be saved
		 */
		function update( $new_instance, $old_instance ) {

			$instance = $old_instance;

			$instance['title_member'] = sanitize_text_field( $new_instance['title_member'] );
			$instance['title_shop']   = sanitize_text_field( $new_instance['title_shop'] );

			$instance['info_member'] = sanitize_text_field( $new_instance['info_member'] );
			$instance['info_shop']   = sanitize_text_field( $new_instance['info_shop'] );

			return $instance;

		}

	}
endif;
?>