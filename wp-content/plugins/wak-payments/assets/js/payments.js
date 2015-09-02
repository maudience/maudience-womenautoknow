jQuery(function($) {

	$(document).ready(function(){

		/**
		 * Upgrade Auto Shop Modal
		 * @version 1.0
		 */
		$( '#upgrade-autoshop-premium' ).on( 'show.bs.modal', function(e){

			$( '#upgrade-autoshop-premium' ).focus();

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-load-go-premium',
					token     : WAKPayments.loadtoken,
					shopid    : $(e.relatedTarget).data( 'shop' )
				},
				beforeSend : function() {
					
				},
				dataType   : "HTML",
				url        : WAKPayments.ajaxurl,
				success    : function( response ) {

					$( '#upgrade-autoshop-premium .modal-body' ).empty().html( response ).fadeIn( 'slow' ).show();

				}
			});

		});

		/**
		 * Close Auto Shop Upgrade
		 * @version 1.0
		 */
		$( '#upgrade-autoshop-premium' ).on( 'hidden.bs.modal', function(){

			$( '#upgrade-autoshop-premium .modal-body' ).empty().html( WAKPayments.loading );

		});

		/**
		 * Submit Upgrade
		 * @version 1.0
		 */
		$( '#upgrade-autoshop-premium' ).on( 'submit', 'form', function(e){

			e.preventDefault();

			var paymentform   = $(this);
			var submittingbox = $( '#submitting-payment' );
			$( '#upgrade-autoshop-premium form .alert' ).remove();

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-upgrade-autoshop',
					form      : $(this).serialize()
				},
				beforeSend : function() {
					paymentform.slideUp();
					submittingbox.slideDown();
				},
				dataType   : "JSON",
				url        : WAKPayments.ajaxurl,
				success    : function( response ) {

					submittingbox.slideUp();

					if ( response.success ) {
						paymentform.empty().html( response.data ).slideDown();
					}
					else {
						paymentform.prepend( response.data ).slideDown();
					}

				}
			});

			return false;

		});

	});

});