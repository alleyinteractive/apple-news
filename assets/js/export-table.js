(function ( $, window, undefined ) {
	'use strict';

	$( '.share-url-button' ).click(function ( e ) {
		e.preventDefault();
		$( this ).siblings( '.apple-share-url' ).toggle();
	});

	$( '.reset-button' ).click(function ( e ) {
		return confirm( apple_news_export_table.reset_confirmation );
	});

	$( '.delete-button' ).click(function ( e ) {
		return confirm( apple_news_export_table.delete_confirmation );
	});

	$( '.row-actions' ).mouseenter (function () {
		$( this ).addClass( 'is-active' );
	});

	$( '.row-actions' ).mouseleave(function () {
		$( this ).removeClass( 'is-active' );
	});

	$( "#apple_news_date_from, #apple_news_date_to" ).datepicker();

})( jQuery, window );
