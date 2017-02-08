(function ( $, window, undefined ) {
	'use strict';

	// Set up add and remove image functionality.
	$( '.apple-news-coverart-image' ).each( function () {
		console.log( 'thingy' );
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
					imgUrl = attachment.url,
					minX,
					minY,
					recX,
					recY;
				if ( attachment.sizes.medium && attachment.sizes.medium.url ) {
					imgUrl = attachment.sizes.medium.url;
				}

				// Get target minimum and recommended sizes based on orientation.
				switch ( $imgIdInput.attr( 'name' ) ) {
					case 'apple_news_coverart_horizontal':
						minX = apple_news_cover_art.image_sizes.apple_news_ca_landscape_iphone.width;
						minY = apple_news_cover_art.image_sizes.apple_news_ca_landscape_iphone.height;
						recX = apple_news_cover_art.image_sizes.apple_news_ca_landscape_ipad.width;
						recY = apple_news_cover_art.image_sizes.apple_news_ca_landscape_ipad.height;
						break;
					case 'apple_news_coverart_vertical':
						minX = apple_news_cover_art.image_sizes.apple_news_ca_portrait_iphone.width;
						minY = apple_news_cover_art.image_sizes.apple_news_ca_portrait_iphone.height;
						recX = apple_news_cover_art.image_sizes.apple_news_ca_portrait_ipad.width;
						recY = apple_news_cover_art.image_sizes.apple_news_ca_portrait_ipad.height;
						break;
					case 'apple_news_coverart_square':
						minX = apple_news_cover_art.image_sizes.apple_news_ca_square_iphone.width;
						minY = apple_news_cover_art.image_sizes.apple_news_ca_square_iphone.height;
						recX = apple_news_cover_art.image_sizes.apple_news_ca_square_ipad.width;
						recY = apple_news_cover_art.image_sizes.apple_news_ca_square_ipad.height;
						break;
					default:
						return;
				}

				// Clear current values.
				$imgContainer.empty();
				$imgIdInput.val( '' );

				// Check attachment size against minimum.
				if ( attachment.width < minX || attachment.height < minY ) {
					$imgContainer.append(
						'<div class="apple-news-notice apple-news-notice-error"><p>'
						+ apple_news_cover_art.image_too_small
						+ '</p></div>'
					);

					return;
				}

				// Check attachment size against recommended.
				if ( attachment.width < recX || attachment.height < recY ) {
					$imgContainer.append(
						'<div class="apple-news-notice apple-news-notice-warning"><p>'
						+ apple_news_cover_art.image_small
						+ '</p></div>'
					);
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
