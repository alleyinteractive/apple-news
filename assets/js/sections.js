(
	function ( $ ) {
		$( document ).ready( function () {

			// An object to contain configuration and functionality for the Sections settings page.
			var apple_news_sections = {

				/**
				 * A function that enables autocomplete on taxonomy mapping fields.
				 */
				enable_autocomplete: function () {
					$( '.apple-news-section-taxonomy-autocomplete' ).autocomplete( {
						delay: 500,
						minLength: 3,
						source: ajaxurl + '?action=apple_news_section_taxonomy_autocomplete'
					} );
				},

				/**
				 * A function that initializes functionality on the Settings admin screen.
				 */
				init: function () {
					this.enable_autocomplete();
				}
			};

			// Initialize functionality.
			apple_news_sections.init();
		} );
	}( jQuery )
);