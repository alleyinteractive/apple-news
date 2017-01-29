(
	function ( $ ) {
		$( document ).ready( function () {

			// An object to contain configuration and functionality for the Sections settings page.
			var apple_news_sections = {

				/**
				 * A function that enables autocomplete on taxonomy mapping fields.
				 */
				enable_autocomplete: function () {
					$( '#apple-news-sections-list' ).find( '.apple-news-section-taxonomy-autocomplete' ).autocomplete( {
						delay: 500,
						minLength: 3,
						source: ajaxurl + '?action=apple_news_section_taxonomy_autocomplete'
					} );
				},

				/**
				 * A function to set up listeners for additions to mappings.
				 */
				listen_for_additions: function () {
					$( '.apple-news-add-section-taxonomy-mapping' ).on( 'click', function () {
						var $template = $( '#apple-news-section-taxonomy-mapping-template' ),
							$item = $( '<li>' ),
							$input;

						// Copy the HTML from the template.
						$item.html( $template.html() );

						// Set a unique ID on the input field that we just created.
						$input = $item.find( 'input' );
						$input.uniqueId();

						// Wire up the label using the unique ID.
						$item.find( 'label' ).attr( 'for', $input.attr( 'id' ) );

						// Add the item to the list.
						$( this ).siblings( '.apple-news-section-taxonomy-mapping-list' ).append( $item );

						// Activate autocomplete.
						apple_news_sections.enable_autocomplete();
					} );
				},

				/**
				 * A function that initializes functionality on the Settings admin screen.
				 */
				init: function () {
					this.enable_autocomplete();
					this.listen_for_additions();
				}
			};

			// Initialize functionality.
			apple_news_sections.init();
		} );
	}( jQuery )
);