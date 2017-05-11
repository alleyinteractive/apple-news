(function ($) {

	$(document).ready(function () {
		$( '#apple_news_theme' ).on( 'change', function( e ) {
			e.preventDefault();
			appleNewsJSONSubmit( $( this ), 'apple_news_get_json' );
		});
		$( '#apple_news_component' ).on( 'change', function( e ) {
			e.preventDefault();
			appleNewsJSONSubmit( $( this ), 'apple_news_get_json' );
		});
		$( '#apple_news_reset_json' ).on( 'click', function( e ) {
			e.preventDefault();
			appleNewsJSONSubmit( $( this ), 'apple_news_reset_json' );
		});
	});

	function appleNewsJSONSubmit( $el, action ) {
		$( '#apple_news_action' ).val( action );
		$el.parents( 'form' ).submit();
	}

}( jQuery ) );
