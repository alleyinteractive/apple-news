(function ( $, window, undefined ) {
	'use strict';

	var $assign_by_taxonomy = $( '#apple-news-sections-by-taxonomy' );

	$( '#apple-news-publish-submit' ).click(function ( e ) {
		$( '#apple-news-publish-action' ).val( apple_news_meta_boxes.publish_action );
		$( '#post' ).submit();
	});

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
