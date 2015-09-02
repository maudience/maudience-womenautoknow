jQuery(function($) {

	var autoshopid = '';

	$(document).ready(function(){

		/**
		 * New Review Load
		 * @version 1.0
		 */
		$( '#add-new-wak-review' ).on( 'show.bs.modal', function(e){

			$( '#add-new-wak-review' ).focus();
			autoshopid = $(e.relatedTarget).data( 'id' );

			if ( $(e.relatedTarget).data( 'pledged' ) == '1' )
				$( '#add-new-wak-review .modal-header' ).addClass( 'pledged' );
			else
				$( '#add-new-wak-review .modal-header' ).removeClass( 'pledged' );

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-new-autoshop-review',
					token     : WAKReviews.token,
					aid       : autoshopid
				},
				beforeSend : function() {
					
				},
				dataType   : "HTML",
				url        : WAKReviews.ajaxurl,
				success    : function( response ) {

					$( '#add-new-wak-review .modal-body' ).empty().html( response ).fadeIn( 'slow' ).show();

				}
			});

		});

		/**
		 * Review Close
		 * @version 1.0
		 */
		$( '#add-new-wak-review' ).on( 'hidden.bs.modal', function(){

			$( '#add-new-wak-review .modal-body' ).empty().html( WAKReviews.loading );

		});

		/**
		 * Review Submit
		 * @version 1.0
		 */
		$( '#add-new-wak-review' ).on( 'submit', 'form', function(e){

			e.preventDefault();

			var submitbutton = $( '#submit-new-review-button' );

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-submit-autoshop-review',
					form      : $(this).serialize()
				},
				beforeSend : function() {
					submitbutton.attr( 'disabled', 'disabled' ).val( WAKReviews.submitting );
				},
				dataType   : "HTML",
				url        : WAKReviews.ajaxurl,
				success    : function( response ) {

					$( '#add-new-wak-review .modal-body' ).slideUp().empty().html( response ).fadeIn( 'slow' ).show();

				}
			});

		});

		$('[data-toggle="tooltip"]').tooltip();

	});

});