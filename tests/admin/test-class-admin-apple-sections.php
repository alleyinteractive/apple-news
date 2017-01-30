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
						'channel' => 'https://u48r14.digitalhub.com/channels/abcdef01-2345-6789-abcd-ef0123567890',
						'self' => 'https://u48r14.digitalhub.com/channels/abcdef01-2345-6789-abcd-ef012356789a',
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
						'channel' => 'https://u48r14.digitalhub.com/channels/abcdef01-2345-6789-abcd-ef0123567890',
						'self' => 'https://u48r14.digitalhub.com/channels/abcdef01-2345-6789-abcd-ef012356789b',
					),
					'modifiedAt' => '2017-01-01T00:00:00Z',
					'name' => 'Secondary Section',
					'shareUrl' => 'https://apple.news/AbCdEfGhIj-KlMnOpQrStUw',
					'type' => 'section',
				)
			)
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
	 * Ensures that the category mapping form saves properly.
	 *
	 * @access public
	 */
	public function testSaveCategoryMapping() {

		// Get info about our categories.
		$category1 = get_term_by( 'name', 'Category 1', 'category' );
		$category2 = get_term_by( 'name', 'Category 2', 'category' );
		$category3 = get_term_by( 'name', 'Category 3', 'category' );

		// Set up post data.
		$_POST = array(
			'action' => 'apple_news_set_section_taxonomy_mappings',
			'page' => 'apple_news_sections',
			'taxonomy-mapping-abcdef01-2345-6789-abcd-ef012356789a' => array(
				'Category 1',
			),
			'taxonomy-mapping-abcdef01-2345-6789-abcd-ef012356789b' => array(
				'Category 2',
			),
		);
		$_REQUEST = array(
			'_wp_http_referer' => '/wp-admin/admin.php?page=apple-news-sections',
			'_wpnonce' => wp_create_nonce( 'apple_news_sections' ),
		);

		// Run the request.
		$sections = new Admin_Apple_Sections();
		$sections->action_router();

		// Validate the response.
		$this->assertEquals(
			array(
				'abcdef01-2345-6789-abcd-ef012356789a' => array(
					$category1->term_id
				),
				'abcdef01-2345-6789-abcd-ef012356789b' => array(
					$category2->term_id
				),
			),
			get_option( Admin_Apple_Sections::TAXONOMY_MAPPING_KEY )
		);
	}

	// TODO: Test create post with categories in category mappings and ensure sections are properly set
	// TODO: Test override functionality for manual settings selection
}