<?php
/**
 * Publish to Apple News tests: Test_In_Article class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the behavior of the Apple_Exporter\Components\In_Article class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_In_Article_Test extends Apple_News_Component_TestCase {
	/**
	 * Ensures that an in-article component is inserted at the proper place if configured.
	 */
	public function test_in_article_insertion() {
		// Create some sample content with five paragraphs so we can test insertion between them.
		$post_id = self::factory()->post->create(
			[
				'post_content' => <<<HTML
<!-- wp:paragraph -->
<p>Paragraph 1</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Paragraph 2</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Paragraph 3</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Paragraph 4</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Paragraph 5</p>
<!-- /wp:paragraph -->
HTML,
			]
		);

		// Ensure that an in-article component does not exist if it isn't configured.
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 8, count( $json['components'] ) );
		$this->assertEquals( '<p>Paragraph 1</p>', $json['components'][3]['text'] );
		$this->assertEquals( '<p>Paragraph 5</p>', $json['components'][7]['text'] );

		// Configure an in-article component in theme settings.
		$this->set_theme_settings(
			[
				'json_templates' => [
					'in_article' => [
						'json'   => [
							'role'      => 'heading',
							'text'      => 'In Article Module Test',
							'format'    => 'html',
							'textStyle' => 'default-heading-1',
							'layout'    => 'heading-layout-1',
						],
						'layout' => [],
					],
				],
			]
		);

		// Fetch the JSON for the article again and confirm that the in-article module is inserted at the default position (after the third item).
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 9, count( $json['components'] ) );
		$this->assertEquals( '<p>Paragraph 1</p>', $json['components'][3]['text'] );
		$this->assertEquals( 'In Article Module Test', $json['components'][6]['text'] );
		$this->assertEquals( '<p>Paragraph 5</p>', $json['components'][8]['text'] );

		// Test insertion at the beginning.
		$this->settings->in_article_position = 0;
		$json                                = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'In Article Module Test', $json['components'][3]['text'] );

		// Test insertion at the end.
		$this->settings->in_article_position = 5;
		$json                                = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'In Article Module Test', $json['components'][8]['text'] );

		// Test overflow insertion.
		$this->settings->in_article_position = 99;
		$json                                = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'In Article Module Test', $json['components'][8]['text'] );
	}
}
