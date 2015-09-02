jQuery(function($) {

	var autoshoppage = 2;

	$(document).ready(function(){

		/**
		 * Autoshop Search
		 * @version 1.0
		 */
		$( '#search-wak-autoshops-form' ).on( 'submit', function(e){

			$( '#loading-wak-autoshop-search' ).show();

		});

		/**
		 * Autoshop Sort Option
		 * @version 1.0
		 */
		$( '#wak-locate-auto-orderby' ).on( 'change', function(e){

			var orderby = $(this).find( ':selected' );

			$( '#wak-order-auto-by' ).val( orderby.val() );
			$( '#search-wak-autoshops-form' ).submit();

		});

		/**
		 * Load More Auto Shops
		 * @version 1.0
		 */
		$( '#load-more-autoshops' ).on( 'submit', 'form', function(e){

			e.preventDefault();

			var morebutton = $( '#load-more-autoshops .btn' );

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-load-more-autoshops',
					token     : WAKAutoshop.token,
					form      : $(this).serialize(),
					page      : autoshoppage
				},
				beforeSend : function() {
					morebutton.val( WAKAutoshop.loading ).attr( 'disabled', 'disabled' );
				},
				dataType   : "HTML",
				url        : WAKAutoshop.ajaxurl,
				success    : function( response ) {

					if ( response == '' || response == 0 || response == -1 ) {

						$( '#load-more-autoshops' ).empty().html( WAKAutoshop.end );
						$( '#load-more-autoshops input[name="paged"]' ).val( autoshoppage );

					}
					else {

						$( '#list-of-autoshops' ).append( response ).fadeIn( 'slow' );
						morebutton.val( WAKAutoshop.label ).removeAttr( 'disabled' );

						autoshoppage++;
						console.log( autoshoppage );

					}

				}
			});

			return false;

		});

		/**
		 * Add New Autoshop Modal
		 * @version 1.0
		 */
		$( '#add-new-wak-autoshop' ).on( 'show.bs.modal', function(e){

			$( '#add-new-wak-autoshop' ).focus();

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-add-new-autoshop',
					token     : WAKAutoshop.newtoken
				},
				beforeSend : function() {
					
				},
				dataType   : "HTML",
				url        : WAKAutoshop.ajaxurl,
				success    : function( response ) {

					$( '#add-new-wak-autoshop .modal-body' ).empty().html( response ).fadeIn( 'slow' ).show();

				}
			});

		});

		/**
		 * Close Add New Auto Shop Modal
		 * @version 1.0
		 */
		$( '#add-new-wak-autoshop' ).on( 'hidden.bs.modal', function(){

			$( '#add-new-wak-autoshop .modal-body' ).empty().html( WAKAutoshop.newloading );

		});

		/**
		 * Autoshop Submit
		 * @version 1.0
		 */
		$( '#add-new-wak-autoshop' ).on( 'submit', 'form', function(e){

			e.preventDefault();

			var submitbutton = $( '#submit-new-autoshop-button' );

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-submit-new-autoshop',
					form      : $(this).serialize()
				},
				beforeSend : function() {
					submitbutton.attr( 'disabled', 'disabled' ).val( WAKAutoshop.submitting );
				},
				dataType   : "HTML",
				url        : WAKAutoshop.ajaxurl,
				success    : function( response ) {

					$( '#add-new-wak-autoshop .modal-body' ).empty().html( response );

				}
			});

			return false;

		});

		var autoshopbeingedited = 0;

		/**
		 * Edit Autoshop Modal
		 * @version 1.0
		 */
		$( '#front-end-autoshop-edit' ).on( 'show.bs.modal', function(e){

			$( '#front-end-autoshop-edit' ).focus();

			autoshopbeingedited = $(e.relatedTarget).data( 'shop' );

			console.log( autoshopbeingedited );
			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-edit-autoshop',
					token     : WAKAutoshop.edittoken,
					shopid    : autoshopbeingedited
				},
				dataType   : "HTML",
				url        : WAKAutoshop.ajaxurl,
				success    : function( response ) {

					$( '#front-end-autoshop-edit .modal-body' ).empty().html( response ).fadeIn( 'slow' ).show();

				}
			});

		});

		/**
		 * Close Edit Autoshop Modal
		 * @version 1.0
		 */
		$( '#front-end-autoshop-edit' ).on( 'hidden.bs.modal', function(){

			$( '#front-end-autoshop-edit .modal-body' ).empty().html( WAKAutoshop.newloading );

		});

		$( '#front-end-autoshop-edit' ).on( 'submit', 'form', function(e){

			e.preventDefault();

			var submitbutton = $( '#submit-new-autoshop-button' );

			$.ajax({
				type       : "POST",
				data       : {
					action    : 'wak-update-autoshop',
					form      : $(this).serialize(),
					shopid    : autoshopbeingedited
				},
				beforeSend : function() {
					submitbutton.attr( 'disabled', 'disabled' ).val( WAKAutoshop.submitting );
				},
				dataType   : "HTML",
				url        : WAKAutoshop.ajaxurl,
				success    : function( response ) {

					$( '#front-end-autoshop-edit .modal-body' ).empty().html( response );

				}
			});

			return false;

		});

		$( '#show-advanced-wak-search' ).click(function(){

			$( '#wak-advanced-search' ).toggle();

		});

		$( '#show-advanced-wak-search-all' ).click(function(){

			$( '#wak-advanced-search-all' ).toggle();

		});

	});

});