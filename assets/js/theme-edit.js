(function ($) {

	$(document).ready(function () {
		appleNewsSelectInit();
		appleNewsThemeEditSortInit(
			'#meta-component-order-sort',
			'meta_component_order',
			'#meta-component-inactive',
			'meta_component_inactive',
			'.apple-news-sortable-list ul.component-order'
		);
		appleNewsThemeEditBorderInit();
		appleNewsColorPickerInit();
		$( 'body' ).trigger( 'apple-news-settings-loaded' );
	});

	function appleNewsFontSelectTemplate( font ) {
		var $fontOption = $( '<span>' )
			.attr( 'style', 'font-family: ' + font.text )
			.text( font.text );

		return $fontOption;
	}

	function appleNewsSelectInit() {
		$( '.select2.standard' ).select2();
		$( '.select2.font' ).select2({
			templateResult: appleNewsFontSelectTemplate,
			templateSelection: appleNewsFontSelectTemplate
		}).on('select2:select', async function (e) {
			// Check if font preview is available.
			const selectedFont = e.params.data.text;
			const isCustomFont = appleNewsThemeEdit.customFonts.includes(selectedFont);
			const localFontInstalled = await appleNewsLocalFontInstalled( selectedFont );
			const $fontNotice = $( 'span.select2' ).next( '.font-notice' );
			let noticeText = '';

			if ( localFontInstalled ) {
				// Local font is installed. Remove any warnings.
				noticeText = '';
			} else if ( isCustomFont ) {
				// Local font is not installed or cannot be determined.
				noticeText = appleNewsThemeEdit.customFontNotice;
			} else if ( ! appleNewsSupportsMacFeatures() ) {
				// MacOS system font.
				noticeText = appleNewsThemeEdit.fontNotice;
			}

			if ( $fontNotice.length > 0 ) {
				// Update existing notice.
				$fontNotice.text( noticeText );
			} else if ( noticeText ) {
				// Append new notice if it doesn't exist.
				$( 'span.select2' ).after(
					$('<div>')
						.addClass( 'font-notice' )
						.text( noticeText )
				);
			}
		});
	}

	function appleNewsThemeEditBorderInit() {
		$( '#blockquote_border_style' ).on( 'change', function () {
			if ( 'none' === $( this ).val() ) {
				$( '#blockquote_border_color, #blockquote_border_width' ).parent().hide().next( 'br' ).hide();
			} else {
				$( '#blockquote_border_color, #blockquote_border_width' ).parent().show().next( 'br' ).show();
			}
		} ).change();

		$( '#pullquote_border_style' ).on( 'change', function () {
			if ( 'none' === $( this ).val() ) {
				$( '#pullquote_border_color, #pullquote_border_width' ).parent().hide().next( 'br' ).hide();
			} else {
				$( '#pullquote_border_color, #pullquote_border_width' ).parent().show().next( 'br' ).show();
			}
		} ).change();
	}

	function appleNewsThemeEditSortInit( activeSelector, activeKey, inactiveSelector, inactiveKey, connectWith ) {
		$( activeSelector + ', ' + inactiveSelector ).sortable( {
			'connectWith': connectWith,
			'stop': function ( event, ui ) {
				appleNewsThemeEditSortUpdate( $( activeSelector ), activeKey );
				appleNewsThemeEditSortUpdate( $( inactiveSelector ), inactiveKey );
			},
		} ).disableSelection();
		appleNewsThemeEditSortUpdate( $( activeSelector ), activeKey );
		appleNewsThemeEditSortUpdate( $( inactiveSelector ), inactiveKey );
	}

	function appleNewsThemeEditSortUpdate( $sortableElement, keyPrefix ) {
		// Build the key for field
		var key = keyPrefix + '[]';

		// Remove any current values
		$( 'input[name="' + key + '"]' ).remove();

		// Create a hidden form field with the values of the sortable element
		var values = $sortableElement.sortable( 'toArray' );
		if ( values.length > 0 ) {
			$.each( values.reverse(), function( index, value ) {
				$hidden = $( '<input>' )
					.attr( 'type', 'hidden' )
					.attr( 'name', key )
					.attr( 'value', value );

				$sortableElement.after( $hidden ); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.after
			} );
		}

		// Update the preview
		appleNewsThemeEditUpdated();
	}

	function appleNewsThemeEditUpdated() {
		$( 'body' ).trigger( 'apple-news-settings-updated' );
	}

	function appleNewsColorPickerInit() {
		$( '.apple-news-color-picker' ).iris({
			palettes: true,
			width: 320,
			change: appleNewsColorPickerChange,
			clear: appleNewsColorPickerChange
		});

		$( '.apple-news-color-picker' ).on( 'click', function() {
			$( '.apple-news-color-picker' ).iris( 'hide' );
			$( this ).iris( 'show' );
		});
	}

	function appleNewsColorPickerChange( event, ui ) {
		$( event.target ).val( ui.color.toString() );
		appleNewsThemeEditUpdated();
	}

}( jQuery ) );

/**
 * Checks if a local font is installed.
 *
 * @async
 * @param {string} selectedFont The PostScript name of the font to check.
 * @returns {Promise<boolean>} A promise that resolves to true if it can be
 *                             determined that the font is installed locally,
 *                             false otherwise.
 */
async function appleNewsLocalFontInstalled( selectedFont ) {
	let localFonts = [];
	if ( 'queryLocalFonts' in window ) {
		localFonts = await window.queryLocalFonts({
			postscriptNames: [selectedFont],
		});
	}
	return localFonts.length !== 0;
}
