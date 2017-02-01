(function ($) {

	var componentKey = '';

	$(document).ready(function () {
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
		appleNewsSetCSS( '.apple-news-preview .apple-news-dropcap', 'dropcap_color', 'color', null, null );
		appleNewsSetCSS( '.apple-news-preview .apple-news-dropcap', 'dropcap_font', 'font-family', null, null );
		var bodySize = $( '#body_size' ).val();
		var bodyLineHeight = $( '#body_line_height' ).val();
		var dropcapSize = bodySize;
		var dropcapLineHeight = bodyLineHeight;

		if ( 'yes' === $( '#initial_dropcap' ).val() ) {
			dropcapSize = bodySize * 5;
			dropcapLineHeight = bodySize * 3.5;
			$( '.apple-news-preview .apple-news-dropcap' ).addClass( 'apple-news-dropcap-enabled' );
		} else {
			$( '.apple-news-preview .apple-news-dropcap' ).removeClass( 'apple-news-dropcap-enabled' );
		}
		$( '.apple-news-preview .apple-news-dropcap' ).css( 'font-size', dropcapSize + 'px' );
		$( '.apple-news-preview .apple-news-dropcap' ).css( 'line-height', dropcapLineHeight + 'px' );

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
		} else {
			return;
		}

		$.each( componentOrder.reverse(), function( index, value ) {
			// Remove the component
			var $detached = $( '.apple-news-' + value ).detach();

			// Build the component key.
			// Used for targeting certain styles in the preview that differ on component order.
			componentKey = value + '-' + componentKey;

			// Add back at the beginning
			$( '.apple-news-preview' ).prepend( $detached );
		} );

		if ( '' !== componentKey ) {
			componentKey = componentKey.substring( 0, componentKey.length - 1 );
			$( '.apple-news-meta-component' ).addClass( componentKey );
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
