(function ($) {

	$(document).ready(function () {
		$( '#apple_news_create_theme' ).on( 'click', function( e ) {
			e.preventDefault();
			appleNewsThemesSubmit( $( this ), 'apple_news_create_theme' );
		});

		$( '#apple_news_start_import' ).on( 'click', function( e ) {
			e.preventDefault();
			$( '.apple-news-theme-form' ).hide();
			$( '#apple_news_import_theme' ).show();
		});

		$( '#apple_news_upload_theme' ).on( 'click', function( e ) {
			e.preventDefault();
			appleNewsThemesSubmit( $( this ), 'apple_news_upload_theme' );
		});

		$( '#apple_news_cancel_upload_theme' ).on( 'click', function( e ) {
			e.preventDefault();
			$( '#apple_news_import_theme' ).hide();
		});

		$( '.apple-news-delete-theme' ).on( 'click', function( e ) {
			e.preventDefault();
			if ( confirm( appleNewsThemes.deleteWarning + ' ' + $( this ).data( 'theme' ) + '?' ) ) {
				$( '#apple_news_theme' ).val( $( this ).data( 'theme' ) );
				appleNewsThemesSubmit( $( this ), 'apple_news_delete_theme' );
			}
		});

		$( '.apple-news-export-theme' ).on( 'click', function( e ) {
			e.preventDefault();
			$( '#apple_news_theme' ).val( $( this ).data( 'theme' ) );
			appleNewsThemesSubmit( $( this ), 'apple_news_export_theme' );
		});

		$( '#apple_news_set_theme' ).on( 'click', function( e ) {
			e.preventDefault();
			appleNewsThemesSubmit( $( this ), 'apple_news_set_theme' );
		});
	});

	function appleNewsThemesShowError( selector, message ) {
		$( selector ).append( $( '<p>' )
			.attr( 'id', 'apple_news_theme_error' )
			.addClass( 'error-message' )
			.text( message )
		);
	}

	function appleNewsThemesSubmit( $el, action ) {
		$( '#apple_news_action' ).val( action );
		$el.parents( 'form' ).submit();
	}

}( jQuery ) );
