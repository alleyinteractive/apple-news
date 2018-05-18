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

						// Create a unique ID on the input and map it to the label.
						$input = $item.find( 'input' );
						$input.uniqueId();
						$item.find( 'label' ).attr( 'for', $input.attr( 'id' ) );

						// Set the correct name on the field.
						$input.attr( 'name', 'taxonomy-mapping-' + $( this ).attr( 'data-section-id' ) + '[]' );

						// Add the item to the list.
						$( this ).siblings( '.apple-news-section-taxonomy-mapping-list' ).append( $item );

						// Activate autocomplete.
						apple_news_sections.enable_autocomplete();
					} );
				},

				/**
				 * A function to set up listeners for mapping deletions.
				 */
				listen_for_deletions: function () {
					$( '#apple-news-sections-list' ).on( 'click', '.apple-news-section-taxonomy-remove', function () {
						$( this ).parent().remove();
					} );
				},

				/**
				 * A function to set up a listener for a reset action.
				 */
				listen_for_reset: function () {
					document
						.getElementById('apple_news_refresh_section_list')
						.addEventListener('click', function () {
							document.getElementById('apple-news-section-form')
								.querySelector('input[name="action"]')
								.value = 'apple_news_refresh_section_list';
						})
				},

				/**
				 * A function that initializes functionality on the Settings admin screen.
				 */
				init: function () {
					this.enable_autocomplete();
					this.listen_for_additions();
					this.listen_for_deletions();
					this.listen_for_reset();
				}
			};

			// Initialize functionality.
			apple_news_sections.init();
		} );
	}( jQuery )
);
