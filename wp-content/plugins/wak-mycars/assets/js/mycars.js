jQuery(function($) {

	var carid = 0;

	$(document).ready(function(){

		/**
		 * Edit Car
		 * @version 1.0
		 */
		$( '#wak-edit-car' ).on( 'show.bs.modal', function(e){

			$( '#wak-edit-car' ).focus();

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-edit-mycar',
					token     : WAKCars.token,
					car_id    : $(e.relatedTarget).data( 'car' )
				},
				beforeSend : function() {
					
				},
				dataType   : "HTML",
				url        : WAKCars.ajaxurl,
				success    : function( response ) {

					$( '#wak-edit-car .modal-body' ).empty().html( response ).fadeIn( 'slow' ).show();

				}
			});

		});

		/**
		 * Close Car Editor
		 * @version 1.0
		 */
		$( '#wak-edit-car' ).on( 'hidden.bs.modal', function(){

			$( '#wak-edit-car .modal-body' ).empty().html( WAKCars.loading );

		});

		/**
		 * Add Car
		 * @version 1.0
		 */
		$( '#wak-add-car' ).on( 'show.bs.modal', function(e){

			$( '#wak-add-car' ).focus();

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-add-new-car',
					token     : WAKCars.newtoken
				},
				beforeSend : function() {
					
				},
				dataType   : "HTML",
				url        : WAKCars.ajaxurl,
				success    : function( response ) {

					$( '#wak-add-car .modal-body' ).empty().html( response ).fadeIn( 'slow' ).show();

				}
			});

		});

		/**
		 * Close Add Car
		 * @version 1.0
		 */
		$( '#wak-add-car' ).on( 'hidden.bs.modal', function(){

			$( '#wak-add-car .modal-body' ).empty().html( WAKCars.newloading );

		});

		/**
		 * Submit New Car
		 * @version 1.0
		 */
		$( '#wak-add-car' ).on( 'submit', 'form', function(e){

			e.preventDefault();

			var submitbutton = $( '#submit-wak-car-button' );

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-submit-new-car',
					form      : $(this).serialize()
				},
				beforeSend : function() {
					submitbutton.attr( 'disabled', 'disabled' ).val( WAKCars.submitting );
				},
				dataType   : "HTML",
				url        : WAKCars.ajaxurl,
				success    : function( response ) {

					console.log( 'WAK Car Submission: ' + response );
					$( '#wak-add-car .modal-body' ).empty().html( response ).fadeIn( 'slow' ).show();

				}
			});

			return false;

		});

		/**
		 * Submit New Car
		 * @version 1.0
		 */
		$( '#wak-edit-car' ).on( 'submit', 'form', function(e){

			e.preventDefault();

			var submitbutton = $( '#submit-wak-car-button' );

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-submit-new-car',
					form      : $(this).serialize()
				},
				beforeSend : function() {
					submitbutton.attr( 'disabled', 'disabled' ).val( WAKCars.submitting );
				},
				dataType   : "HTML",
				url        : WAKCars.ajaxurl,
				success    : function( response ) {

					console.log( 'WAK Car Edit: ' + response );
					$( '#wak-edit-car .modal-body' ).empty().html( response ).fadeIn( 'slow' ).show();

				}
			});

			return false;

		});

	});

});