jQuery(function($) {

	var variabletitle = '';

	$(document).ready(function(){

		$( '#wak-estimator form' ).submit(function(e){

			e.preventDefault();

			var modal   = $( '#wak-esitmator-results' );
			var state   = $( '#wak-estimate-state' );
			var service = $( '#wak-estimate-service' );

			if ( state.val() == '' ) {

				state.focus();
				return false;

			}

			if ( service.val() == '' ) {

				service.focus();
				return false;

			}

			$.ajax({
				type       : "POST",
				data       : {
					action     : 'wak-get-estimate',
					token      : WAKEstimator.token,
					es_state   : state.val(),
					es_service : service.val()
				},
				beforeSend : function() {

					modal.modal( 'show' );
					state.val( '' );
					service.val( '' );

				},
				dataType   : "HTML",
				url        : WAKEstimator.ajaxurl,
				success    : function( response ) {

					console.log( response );
					if ( response == '' || response == 0 || response == -1 ) {
						location.reload();
					}

					else {

						$( '#wak-esitmator-results .modal-body' ).empty().html( response ).fadeIn( 'slow' );

					}

				}

			});

			return false;

		});

		/**
		 * Close Estimator
		 * @version 1.0
		 */
		$( '#add-new-wak-review' ).on( 'hidden.bs.modal', function(){

			$( '#wak-esitmator-results .modal-body' ).empty().html( WAKEstimator.loading );

		});

	});

});