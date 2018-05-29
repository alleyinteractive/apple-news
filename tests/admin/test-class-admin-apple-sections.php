<?php
/**
 * Publish to Apple News Tests: Admin_Apple_Sections_Test class
 *
 * Contains a class to test the Admin_Apple_Sections class.
 *
 * @since 1.2.2
 */

use \Apple_Exporter\Settings;

/**
 * A class to test the Admin_Apple_Sections class.
 *
 * @since 1.2.2
 */
class Admin_Apple_Sections_Test extends WP_UnitTestCase {

	/**
	 * Actions to be run before each test in this class.
	 *
	 * @access public
	 */
	public function setUp() {
		parent::setup();

		// Cache default settings for future use.
		$this->settings = new Settings();

		// Create some dummy categories to use in mapping testing.
		wp_insert_term( 'Category 1', 'category' );
		wp_insert_term( 'Category 2', 'category' );
		wp_insert_term( 'Category 3', 'category' );

		// Pre-cache a transient for sections using dummy data to bypass API call.
		set_transient(
			'apple_news_sections',
			array(
				(object) array(
					'createdAt' => '2017-01-01T00:00:00Z',
					'id' => 'abcdef01-2345-6789-abcd-ef012356789a',
					'isDefault' => true,
					'links' => (object) array(
						'channel' => 'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef0123567890',
						'self' => 'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef012356789a',
					),
					'modifiedAt' => '2017-01-01T00:00:00Z',
					'name' => 'Main',
					'shareUrl' => 'https://apple.news/AbCdEfGhIj-KlMnOpQrStUv',
					'type' => 'section',
				),
				(object) array(
					'createdAt' => '2017-01-01T00:00:00Z',
					'id' => 'abcdef01-2345-6789-abcd-ef012356789b',
					'isDefault' => false,
					'links' => (object) array(
						'channel' => 'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef0123567890',
						'self' => 'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef012356789b',
					),
					'modifiedAt' => '2017-01-01T00:00:00Z',
					'name' => 'Secondary Section',
					'shareUrl' => 'https://apple.news/AbCdEfGhIj-KlMnOpQrStUw',
					'type' => 'section',
				)
			)
		);

		// Create some themes
		$this->createThemes();

		// Set up post data for creating taxonomy and theme mappings.
		$_POST = array(
			'action' => 'apple_news_set_section_mappings',
			'page' => 'apple_news_sections',
			'taxonomy-mapping-abcdef01-2345-6789-abcd-ef012356789a' => array(
				'Category 1',
			),
			'taxonomy-mapping-abcdef01-2345-6789-abcd-ef012356789b' => array(
				'Category 2',
				'Category 3',
			),
			'theme-mapping-abcdef01-2345-6789-abcd-ef012356789a' => 'Default',
			'theme-mapping-abcdef01-2345-6789-abcd-ef012356789b' => 'Test Theme',
		);

		$_REQUEST = array(
			'_wp_http_referer' => '/wp-admin/admin.php?page=apple-news-sections',
			'_wpnonce' => wp_create_nonce( 'apple_news_sections' ),
			'action' => 'apple_news_set_section_mappings',
		);

		// Run the request to set up taxonomy mappings.
		$sections = new Admin_Apple_Sections();
		$sections->action_router();
	}

	/**
	 * Create some themes for testing
	 *
	 * @access private
	 */
	private function createThemes() {

		// Create the default theme.
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( 'Default' );
		$this->assertTrue( $theme->save() );
		unset( $theme );

		// Create a test theme.
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( 'Test Theme' );
		$this->assertTrue( $theme->save() );
		unset( $theme );
	}

	/**
	 * Ensures that automatic section/category mappings function properly.
	 *
	 * @access public
	 */
	public function testAutomaticCategoryMapping() {

		// Create a post with Category 2 to trigger second section membership.
		$category2 = get_term_by( 'name', 'Category 2', 'category' );
		$post_id = $this->factory->post->create();
		wp_set_post_categories( $post_id, $category2->term_id );

		// Validate automatic section assignment.
		$this->assertEquals(
			array(
				'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef012356789b',
			),
			Admin_Apple_Sections::get_sections_for_post( $post_id )
		);
	}

	/**
	 * Ensures that the apple_news_section_taxonomy filter is working properly.
	 *
	 * @access public
	 */
	public function testMappingTaxonomyFilter() {

		// Test default behavior.
		$taxonomy = Admin_Apple_Sections::get_mapping_taxonomy();
		$this->assertEquals( 'category', $taxonomy->name );

		// Switch to post tag.
		add_filter( 'apple_news_section_taxonomy', function () {
			return 'post_tag';
		} );

		// Test filtered value.
		$taxonomy = Admin_Apple_Sections::get_mapping_taxonomy();
		$this->assertEquals( 'post_tag', $taxonomy->name );
	}

	/**
	 * Ensures that the category mapping override is respected.
	 *
	 * @access public
	 */
	public function testOverrideCategoryMapping() {

		// Create a post with Category 2 to trigger second section membership.
		$category2 = get_term_by( 'name', 'Category 2', 'category' );
		$post_id = $this->factory->post->create();
		wp_set_post_categories( $post_id, $category2->term_id );

		// Manually set the first section to override automatic mapping.
		update_post_meta(
			$post_id,
			'apple_news_sections',
			array(
				'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef012356789a',
			)
		);

		// Validate manual section assignment.
		$this->assertEquals(
			array(
				'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef012356789a',
			),
			Admin_Apple_Sections::get_sections_for_post( $post_id )
		);
	}

	/**
	 * Ensures that the category mapping fields save properly.
	 *
	 * @access public
	 */
	public function testSaveCategoryMapping() {

		// Get info about our categories.
		$category1 = get_term_by( 'name', 'Category 1', 'category' );
		$category2 = get_term_by( 'name', 'Category 2', 'category' );
		$category3 = get_term_by( 'name', 'Category 3', 'category' );

		// Validate the response.
		$this->assertEquals(
			array(
				'abcdef01-2345-6789-abcd-ef012356789a' => array(
					$category1->term_id
				),
				'abcdef01-2345-6789-abcd-ef012356789b' => array(
					$category2->term_id,
					$category3->term_id,
				),
			),
			get_option( Admin_Apple_Sections::TAXONOMY_MAPPING_KEY )
		);
	}

	/**
	 * Ensures that the theme mapping fields save properly.
	 *
	 * @access public
	 */
	public function testSaveThemeMapping() {
		// Validate the response.
		$this->assertEquals(
			array(
				'abcdef01-2345-6789-abcd-ef012356789a' => 'Default',
				'abcdef01-2345-6789-abcd-ef012356789b' => 'Test Theme',
			),
			get_option( Admin_Apple_Sections::THEME_MAPPING_KEY )
		);
	}
}
