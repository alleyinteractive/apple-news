(
	function ( $, window, undefined ) {
		'use strict';

		// Listen for changes to the debugging settings.
		$( '#apple_news_enable_debugging' ).on( 'change', function () {
			var $email = $( '#apple_news_admin_email' );
			if ( 'yes' === $( this ).val() ) {
				$email.attr( 'required', 'required' );
			} else {
				$email.removeAttr( 'required' );
			}
		} ).change();
	}
)( jQuery, window );
