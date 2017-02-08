(function ( $, window, undefined ) {
	'use strict';

	var $assign_by_taxonomy = $( '#apple-news-sections-by-taxonomy' );

	// Listen for clicks on the submit button.
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

	// Set up initial state of collapsable blocks.
	$( '.apple-news-metabox-section-collapsable' ).each( function () {
		var $this = $( this );

		// Set up initial collapsed state.
		$this.addClass( 'apple-news-metabox-section-collapsed' );

		// Add the expand controls.
		var $heading = $this.find( 'h3' ).first().clone();
		$heading.addClass( 'apple-news-metabox-section-control' );
		$heading.insertBefore( $this );

		// Add the close controls.
		$this.prepend(
			$( '<div></div>' ).addClass( 'apple-news-metabox-section-close' )
		);
	} );

	// Set up listener for clicks on expand controls.
	$( '.apple-news-metabox-section-control' ).on( 'click', function () {
		$( this )
			.next( '.apple-news-metabox-section-collapsable' )
			.addClass( 'apple-news-metabox-section-visible' )
			.removeClass( 'apple-news-metabox-section-collapsed' );
	} );

	// Set up listener for clicks on close controls.
	$( '.apple-news-metabox-section-close' ).on( 'click', function () {
		$( this )
			.parent()
			.addClass( 'apple-news-metabox-section-collapsed' )
			.removeClass( 'apple-news-metabox-section-visible' );
	} );
})( jQuery, window );
