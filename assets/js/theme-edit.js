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
		// Only show fonts on Macs since they're system fonts
		if ( appleNewsSupportsMacFeatures() ) {
			$( '.select2.standard' ).select2();
			$( '.select2.font' ).select2({
				templateResult: appleNewsFontSelectTemplate,
				templateSelection: appleNewsFontSelectTemplate
			});
		} else {
			$( '.select2' ).select2();
			$( 'span.select2' ).after(
				$( '<div>' )
					.addClass( 'font-notice' )
					.text( appleNewsThemeEdit.fontNotice )
			)
		}
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

				$sortableElement.after( $hidden );
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
