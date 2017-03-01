(function ( $, window, undefined ) {
	'use strict';

	// Set up orientation change functionality.
	$( '#apple-news-coverart-orientation' ).on( 'change', function () {
		$( '.apple-news-coverart-image-container' ).addClass( 'hidden' );
		$( '.apple-news-coverart-image-' + $( this ).find( ':selected' ).val() ).removeClass( 'hidden' );
	} ).change();

	// Set up add and remove image functionality.
	$( '.apple-news-coverart-image-container' ).each( function () {
		var $this = $( this ),
			$addImgButton = $this.find( '.apple-news-coverart-add' ),
			$delImgButton = $this.find( '.apple-news-coverart-remove' ),
			$imgContainer = $this.find( '.apple-news-coverart-image' ),
			$imgIdInput = $this.find( '.apple-news-coverart-id' ),
			frame;

		// Set up handler for remove image functionality.
		$delImgButton.on( 'click', function() {
			$imgContainer.empty();
			$addImgButton.removeClass( 'hidden' );
			$delImgButton.addClass( 'hidden' );
			$imgIdInput.val( '' );
		} );

		// Set up handler for add image functionality.
		$addImgButton.on( 'click', function () {

			// Open frame, if it already exists.
			if ( frame ) {
				frame.open();
				return;
			}

			// Set configuration for media frame.
			frame = wp.media( {
				title: apple_news_cover_art.media_modal_title,
				button: {
					text: apple_news_cover_art.media_modal_button
				},
				multiple: false
			} );

			// Set up handler for image selection.
			frame.on( 'select', function () {

				// Get information about the attachment.
				var attachment = frame.state().get( 'selection' ).first().toJSON(),
					imgUrl = attachment.url;

				// Set image URL to medium size, if available.
				if ( attachment.sizes.medium && attachment.sizes.medium.url ) {
					imgUrl = attachment.sizes.medium.url;
				}

				// Clear current values.
				$imgContainer.empty();
				$imgIdInput.val( '' );

				// Check attachment size against minimum.
				if ( attachment.width < parseInt( $imgIdInput.attr( 'data-width' ) )
					|| attachment.height < parseInt( $imgIdInput.attr( 'data-height' ) )
				) {
					$imgContainer.append(
						'<div class="apple-news-notice apple-news-notice-error"><p>'
						+ apple_news_cover_art.image_too_small
						+ '</p></div>'
					);

					return;
				}

				// Add the image and ID, swap visibility of add and remove buttons.
				$imgContainer.append( '<img src="' + imgUrl + '" alt="" />' );
				$imgIdInput.val( attachment.id );
				$addImgButton.addClass( 'hidden' );
				$delImgButton.removeClass( 'hidden' );
			} );

			// Open the media frame.
			frame.open();
		} );
	} );
})( jQuery, window );
