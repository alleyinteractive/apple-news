(function ( $, window, undefined ) {
	'use strict';

	var $assign_by_taxonomy = $( '#apple-news-sections-by-taxonomy' );

	// Listen for changes to the "assign by taxonomy" checkbox.
	if ( $assign_by_taxonomy.length ) {
		$assign_by_taxonomy.on( 'change', function () {
			if ( $( this ).is( ':checked' ) ) {
				$( '.apple-news-sections' ).hide();
			} else {
				$( '.apple-news-sections' ).show();
			}
		} ).change();
	}

})( jQuery, window );
