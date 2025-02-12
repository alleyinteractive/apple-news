(function ( $, window, undefined ) {
	'use strict';

	var started = false,
		searchParams = new URLSearchParams( window.location.search ),
		$submitButton = $( '.bulk-export-submit' );

	function done() {
		$submitButton.text( 'Done' );
		$submitButton.attr( 'disabled', 'disabled' );
	}

	function pushItem( item, next, nonce ) {
		var $item = $( item );
		var $status = $item.find( '.bulk-export-list-item-status' );
		var id = +$item.data( 'post-id' ); // fetch the post-id and cast to integer

		$status.removeClass( 'pending' ).addClass( 'in-progress' ).text( 'In Progress…' );

		// Send a GET request to ajaxurl, which is WordPress endpoint for AJAX
		// requests. Expects JSON as response.
		$.getJSON(
			ajaxurl,
			{
				action: searchParams.get( 'action' ),
				id: id,
				_ajax_nonce: nonce
			},
			function( res ) {
				if ( res.success ) {
					$status.removeClass( 'in-progress' ).addClass( 'success' ).text( 'Success' );
				} else {
					$status.removeClass( 'in-progress' ).addClass( 'failed' ).text( res.data );
				}
				next();
			},
			function( err ) {
				$status.removeClass( 'in-progress' ).addClass( 'failed' ).text( 'Server Error' );
				next();
			}
		);
	}

	function bulkPush() {
		// Fetch all the li's that must be exported
		var items = $( '.bulk-export-list-item' );
		// The next function will push the next item in queue
		var index = -1;
		var next = function () {
			index += 1;
			if ( index < items.length ) {
				pushItem( items.get( index ), next, $( '.bulk-export-list' ).data( 'nonce' ) );
			} else {
				done();
			}
		};

		// Initial push
		next();
	}

	$submitButton.click( function ( e ) {
		e.preventDefault();

		if ( started ) {
			return;
		}

		started = true;
		bulkPush();
	} );

})( jQuery, window );
