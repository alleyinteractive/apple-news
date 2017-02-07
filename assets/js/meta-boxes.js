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
		$( this ).next( '.apple-news-metabox-section-collapsable' )
			.addClass( 'apple-news-metabox-section-visible' )
			.removeClass( 'apple-news-metabox-section-collapsed' );
	} );

	// Set up listener for clicks on close controls.
	$( '.apple-news-metabox-section-close' ).on( 'click', function () {
		$( this ).parent()
			.addClass( 'apple-news-metabox-section-collapsed' )
			.removeClass( 'apple-news-metabox-section-visible' );
	} );

	// Set up add and remove image functionality.
	$( '.apple-news-metabox-coverart-image' ).each( function () {
		var $this = $( this ),
			$addImgButton = $this.find( '.apple-news-metabox-coverart-add' ),
			$delImgButton = $this.find( '.apple-news-metabox-coverart-remove' ),
			$imgContainer = $this.find( '.apple-news-metabox-coverart-image' ),
			$imgIdInput = $this.find( '.apple-news-metabox-coverart-id' ),
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
				title: apple_news_meta_boxes.media_modal_title,
				button: {
					text: apple_news_meta_boxes.media_modal_button
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
						minX = apple_news_meta_boxes.image_sizes.apple_news_ca_landscape_iphone.width;
						minY = apple_news_meta_boxes.image_sizes.apple_news_ca_landscape_iphone.height;
						recX = apple_news_meta_boxes.image_sizes.apple_news_ca_landscape_ipad.width;
						recY = apple_news_meta_boxes.image_sizes.apple_news_ca_landscape_ipad.height;
						break;
					case 'apple_news_coverart_vertical':
						minX = apple_news_meta_boxes.image_sizes.apple_news_ca_portrait_iphone.width;
						minY = apple_news_meta_boxes.image_sizes.apple_news_ca_portrait_iphone.height;
						recX = apple_news_meta_boxes.image_sizes.apple_news_ca_portrait_ipad.width;
						recY = apple_news_meta_boxes.image_sizes.apple_news_ca_portrait_ipad.height;
						break;
					case 'apple_news_coverart_square':
						minX = apple_news_meta_boxes.image_sizes.apple_news_ca_square_iphone.width;
						minY = apple_news_meta_boxes.image_sizes.apple_news_ca_square_iphone.height;
						recX = apple_news_meta_boxes.image_sizes.apple_news_ca_square_ipad.width;
						recY = apple_news_meta_boxes.image_sizes.apple_news_ca_square_ipad.height;
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
							+ apple_news_meta_boxes.image_too_small
							+ '</p></div>'
					);

					return;
				}

				// Check attachment size against recommended.
				if ( attachment.width < recX || attachment.height < recY ) {
					$imgContainer.append(
						'<div class="apple-news-notice apple-news-notice-warning"><p>'
						+ apple_news_meta_boxes.image_small
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
