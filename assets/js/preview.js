(function ($) {

	var componentKey = '';

	$(document).ready(function () {
		appleNewsSettingsToggleInit();

		$( 'body' ).on( 'apple-news-settings-loaded', function( e ) {
			appleNewsPreviewInit();
		} );

		$( 'body' ).on( 'apple-news-settings-updated', function( e ) {
			appleNewsUpdatePreview();
		} );

		$( '.apple-news-preview a' ).on( 'click', function( e ) {
			e.preventDefault();
		} );
	} );

	/**
	 * Sets up settings toggle by collapsing all settings initially and
	 * setting up click listeners to hide and show settings.
	 */
	function appleNewsSettingsToggleInit() {
		var tableRows = document
			.querySelector( '.form-table.apple-news' )
			.querySelectorAll( 'tr' );
		for ( var i = 0; i < tableRows.length; i += 1 ) {
			tableRows[i].classList.add( 'collapsed' );
			tableRows[i]
				.querySelector( 'th' )
				.addEventListener( 'click', appleNewsSettingsToggleVisibility );
		}
	}

	/**
	 * A click handler for a settings visibility toggle event.
	 * @param {Event} e - The click event.
	 */
	function appleNewsSettingsToggleVisibility(e) {
		e.preventDefault();
		e.stopPropagation();
		e.target.parentNode.classList.toggle('collapsed');
	}

	function appleNewsPreviewInit() {
		// Do an initial update
		appleNewsUpdatePreview();

		// Check that we are fully compatible
		if ( ! appleNewsSupportsMacFeatures() ) {
			$( '.apple-news-preview' ).prepend(
				$( '<div>' )
					.addClass( 'font-notice' )
					.text( appleNewsSettings.fontNotice )
			);
		}

		// Ensure all further updates also affect the preview
		$( '#apple-news-theme-edit-form :input' ).on( 'change', appleNewsUpdatePreview );

		// Show the preview
		$( '.apple-news-preview' ).show();
	}

	function appleNewsUpdatePreview() {
		// Create a map of the form values to the preview elements
		// Layout spacing
		appleNewsSetCSS( '.apple-news-component', 'layout_margin', 'padding-left', 'px', .3 );
		appleNewsSetCSS( '.apple-news-component', 'layout_margin', 'padding-right', 'px', .3 );

		// Body
		appleNewsSetCSS( '.apple-news-preview p', 'body_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview p', 'body_size', 'font-size', 'px', null );
		appleNewsSetCSS( '.apple-news-preview p', 'body_tracking', 'letter-spacing', 'px', $( '#body_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview p', 'body_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview a', 'body_link_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview', 'body_background_color', 'background-color', null, null );
		appleNewsSetCSS( '.apple-news-preview p', 'body_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview p', 'body_line_height', 'margin-bottom', 'px', null );
		appleNewsSetCSS( '.apple-news-image', 'body_line_height', 'margin-bottom', 'px', null );

		// Dropcap
		var bodyLineHeight = $( '#body_line_height' ).val(),
			dropcapCharacters = parseInt( $( '#dropcap_number_of_characters' ).val() ),
			dropcapNumberOfLines = parseInt( $( '#dropcap_number_of_lines' ).val() ),
			dropcapNumberOfRaisedLines = parseInt( $( '#dropcap_number_of_raised_lines' ).val() ),
			dropcapPadding = parseInt( $( '#dropcap_padding' ).val() ),
			dropcapParagraph = $( '.apple-news-component p' ).first();

		// Adjust number of lines to remain within tolerance.
		if ( dropcapNumberOfLines < 2 ) {
			dropcapNumberOfLines = 2;
			$( '#dropcap_number_of_lines' ).val( 2 )
		} else if ( dropcapNumberOfLines > 10 ) {
			dropcapNumberOfLines = 10;
			$( '#dropcap_number_of_lines' ).val( 10 )
		}

		// Adjust number of raised lines to remain within tolerance.
		if ( dropcapNumberOfRaisedLines < 0 ) {
			dropcapNumberOfRaisedLines = 0;
			$( '#dropcap_number_of_raised_lines' ).val( 0 )
		} else if ( dropcapNumberOfRaisedLines >= dropcapNumberOfLines ) {
			dropcapNumberOfRaisedLines = dropcapNumberOfLines - 1;
			$( '#dropcap_number_of_raised_lines' ).val( dropcapNumberOfRaisedLines )
		}

		// Remove existing dropcap.
		dropcapParagraph.html(
			dropcapParagraph.html().replace( /<span[^>]*>([^<]+)<\/span>/, '$1' )
		);

		// If enabled, add it back.
		if ( 'yes' === $( '#initial_dropcap' ).val() ) {

			// Create the dropcap span with the specified number of characters.
			dropcapParagraph.html(
				'<span class="apple-news-dropcap">' +
				dropcapParagraph.html().substr( 0, dropcapCharacters ) +
				'</span>' +
				dropcapParagraph.html().substr( dropcapCharacters )
			);

			// Set the size based on the specified number of lines.
			// There is not an actual 1:1 relationship between this setting and
			// what renders in Apple News, so we need to adjust this by a coefficient
			// to roughly match the actual behavior.
			var targetLines = Math.ceil( dropcapNumberOfLines * 0.56 );
			dropcapSize = bodyLineHeight * targetLines * 1.2 - dropcapPadding * 2;
			dropcapLineHeight = dropcapSize;

			// Compute the adjusted number of target lines based on raised lines.
			var adjustedLines = Math.round( - 0.6 * dropcapNumberOfRaisedLines + targetLines );
			dropcapParagraph.css( 'margin-top', ( 20 + bodyLineHeight * ( targetLines - adjustedLines ) / 2 ) + 'px' );

			// Apply computed styles.
			$( '.apple-news-preview .apple-news-dropcap' )
				.css( 'font-size', dropcapSize + 'px' )
				.css( 'line-height', ( dropcapLineHeight * .66 ) + 'px' )
				.css( 'margin-bottom', ( - 1 * bodyLineHeight * .33 ) + 'px' )
				.css( 'margin-top', ( - 1 * bodyLineHeight * ( targetLines - adjustedLines ) * .9 + bodyLineHeight * .33 ) + 'px' )
				.css( 'padding', ( 5 + dropcapPadding ) + 'px ' + ( 10 + dropcapPadding ) + 'px ' + dropcapPadding + 'px ' + ( 5 + dropcapPadding ) + 'px' );

			// Apply direct styles.
			appleNewsSetCSS( '.apple-news-preview .apple-news-dropcap', 'dropcap_background_color', 'background', null, null );
			appleNewsSetCSS( '.apple-news-preview .apple-news-dropcap', 'dropcap_color', 'color', null, null );
			appleNewsSetCSS( '.apple-news-preview .apple-news-dropcap', 'dropcap_font', 'font-family', null, null );
		}

		// Byline
		appleNewsSetCSS( '.apple-news-preview div.apple-news-byline', 'byline_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-byline', 'byline_size', 'font-size', 'px', null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-byline', 'byline_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-byline', 'byline_tracking', 'letter-spacing', 'px', $( '#byline_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-byline', 'byline_color', 'color', null, null );

		// Headings
		appleNewsSetCSS( '.apple-news-preview h1', 'header1_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview h1', 'header1_size', 'font-size', 'px', .75 );
		appleNewsSetCSS( '.apple-news-preview h1', 'header1_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview h1', 'header1_tracking', 'letter-spacing', 'px', $( '#header1_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview h1', 'header1_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview h2', 'header2_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview h2', 'header2_size', 'font-size', 'px', .75 );
		appleNewsSetCSS( '.apple-news-preview h2', 'header2_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview h2', 'header2_tracking', 'letter-spacing', 'px', $( '#header2_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview h2', 'header2_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview h3', 'header3_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview h3', 'header3_size', 'font-size', 'px', .75 );
		appleNewsSetCSS( '.apple-news-preview h3', 'header3_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview h3', 'header3_tracking', 'letter-spacing', 'px', $( '#header3_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview h3', 'header3_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview h4', 'header4_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview h4', 'header4_size', 'font-size', 'px', .75 );
		appleNewsSetCSS( '.apple-news-preview h4', 'header4_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview h4', 'header4_tracking', 'letter-spacing', 'px', $( '#header4_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview h4', 'header4_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview h5', 'header5_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview h5', 'header5_size', 'font-size', 'px', .75 );
		appleNewsSetCSS( '.apple-news-preview h5', 'header5_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview h5', 'header5_tracking', 'letter-spacing', 'px', $( '#header5_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview h5', 'header5_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview h6', 'header6_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview h6', 'header6_size', 'font-size', 'px', .75 );
		appleNewsSetCSS( '.apple-news-preview h6', 'header6_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview h6', 'header6_tracking', 'letter-spacing', 'px', $( '#header6_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview h6', 'header6_color', 'color', null, null );

		// Image Caption
		appleNewsSetCSS( '.apple-news-preview div.apple-news-image-caption', 'caption_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-image-caption', 'caption_size', 'font-size', 'px', null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-image-caption', 'caption_tracking', 'letter-spacing', 'px', $( '#body_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-image-caption', 'caption_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-image-caption', 'caption_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-image-caption', 'caption_line_height', 'padding-bottom', 'px', null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-image-caption', 'caption_line_height', 'padding-top', 'px', null );

		// Pull quote
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_size', 'font-size', 'px', .75 );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_tracking', 'letter-spacing', 'px', $( '#pullquote_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_transform', 'text-transform', null, null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_border_color', 'border-top-color', null, null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_border_style', 'border-top-style', null, null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_border_width', 'border-top-width', 'px', null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_border_color', 'border-bottom-color', null, null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_border_style', 'border-bottom-style', null, null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_border_width', 'border-bottom-width', 'px', null );
		appleNewsSetCSS( '.apple-news-preview div.apple-news-pull-quote', 'pullquote_line_height', 'line-height', 'px', .75 );
		if ( 'yes' === $( '#pullquote_hanging_punctuation' ).val() ) {
			$( '.apple-news-preview div.apple-news-pull-quote' ).addClass( 'hanging-punctuation' );
		} else {
			$( '.apple-news-preview div.apple-news-pull-quote' ).removeClass( 'hanging-punctuation' );
		}

		// Blockquote
		appleNewsSetCSS( '.apple-news-preview blockquote', 'blockquote_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview blockquote', 'blockquote_size', 'font-size', 'px', null );
		appleNewsSetCSS( '.apple-news-preview blockquote', 'blockquote_tracking', 'letter-spacing', 'px', $( '#blockquote_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview blockquote', 'blockquote_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview blockquote', 'blockquote_border_color', 'border-left-color', null, null );
		appleNewsSetCSS( '.apple-news-preview blockquote', 'blockquote_border_style', 'border-left-style', null, null );
		appleNewsSetCSS( '.apple-news-preview blockquote', 'blockquote_border_width', 'border-left-width', 'px', null );
		appleNewsSetCSS( '.apple-news-preview blockquote', 'blockquote_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview blockquote', 'blockquote_background_color', 'background-color', null, null );

		// Monospaced
		appleNewsSetCSS( '.apple-news-preview pre', 'monospaced_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview pre', 'monospaced_size', 'font-size', 'px', null );
		appleNewsSetCSS( '.apple-news-preview pre', 'monospaced_tracking', 'letter-spacing', 'px', $( '#monospaced_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview pre', 'monospaced_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview pre', 'monospaced_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview pre', 'monospaced_line_height', 'margin-bottom', 'px', null );

		// Tables
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_background_color', 'background-color', null, null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_border_color', 'border-color', null, null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_border_style', 'border-style', null, null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_border_width', 'border-width', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_horizontal_alignment', 'text-align', null, null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_padding', 'padding-bottom', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_padding', 'padding-left', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_padding', 'padding-right', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_padding', 'padding-top', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_size', 'font-size', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_tracking', 'letter-spacing', 'px', $( '#table_body_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview table tbody td', 'table_body_vertical_alignment', 'vertical-align', null, null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_background_color', 'background-color', null, null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_border_color', 'border-color', null, null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_border_style', 'border-style', null, null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_border_width', 'border-width', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_font', 'font-family', null, null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_horizontal_alignment', 'text-align', null, null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_line_height', 'line-height', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_padding', 'padding-bottom', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_padding', 'padding-left', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_padding', 'padding-right', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_padding', 'padding-top', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_size', 'font-size', 'px', null );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_tracking', 'letter-spacing', 'px', $( '#table_header_size' ).val() / 100 );
		appleNewsSetCSS( '.apple-news-preview table thead th', 'table_header_vertical_alignment', 'vertical-align', null, null );

		// Component order
		// This can either be defined as a sortable form element or a simple hidden element
		var componentOrder;
		if ( 0 === $( '#meta-component-order-sort' ).length && $( '#meta_component_order' ).length > 0 ) {
			componentOrder = $( '#meta_component_order' ).val().split( ',' );
		} else if ( $( '#meta-component-order-sort' ).length ) {
			componentOrder = $( '#meta-component-order-sort' ).sortable( 'toArray' );
			if ( '' !== componentKey ) {
				$( '.apple-news-meta-component' ).removeClass( componentKey );
				componentKey = '';
			}
		}

		if ( componentOrder.length ) {
			$.each( componentOrder.reverse(), function( index, value ) {
				// Remove the component
				var $detached = $( '.apple-news-' + value ).detach();

				// Build the component key.
				// Used for targeting certain styles in the preview that differ on component order.
				componentKey = value + '-' + componentKey;

				// Add back at the beginning
				$( '.apple-news-preview' ).prepend( $detached );

				// Ensure element is visible.
				$detached.show();
			} );

			if ( '' !== componentKey ) {
				componentKey = componentKey.substring( 0, componentKey.length - 1 );
				$( '.apple-news-meta-component' ).addClass( componentKey );
			}
		}

		// Get the inactive components and ensure they are hidden.
		var removedElements;
		if ( 0 === $( '#meta-component-inactive' ).length && $( '#meta_component_inactive' ).length > 0 ) {
			removedElements = $( '#meta_component_inactive' ).val().split( ',' );
		} else if ( $( '#meta-component-inactive' ).length ) {
			removedElements = $( '#meta-component-inactive' ).sortable( 'toArray' );
		}

		// Loop over removed elements and hide them.
		if ( removedElements.length ) {
			$.each( removedElements, function( index, value ) {
				$( '.apple-news-' + value ).hide();
			} );
		}
	}

	function appleNewsSetCSS( displayElement, formElement, property, units, scale ) {
		// Get the form value
		var value = $( '#' + formElement ).val();

		// If the value is 'none', make it empty
		if ( 'none' === value ) {
			value = '';
		}

		// Some values need to be scaled
		if ( scale && value ) {
			value = parseInt( value ) * scale;
		}

		// Add units if set and we got a value
		if ( units && value ) {
			value = value + units;
		}

		$( displayElement ).css( property, value );
	}

}( jQuery ) );

function appleNewsSupportsMacFeatures() {
	if ( 'MacIntel' === navigator.platform ) {
		return true;
	} else {
		return false;
	}
}
