<?php
/**
 * Publish to Apple News tests: Aside_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Aside class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Aside_Test extends Apple_News_TestCase {

	/**
	 * Set the setting for the aside class before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		update_option(
			'apple_news_settings',
			array_merge(
				get_option( 'apple_news_settings' ) ?: [],
				[
					'aside_component_class' => 'test-aside-class',
				]
			)
		);
	}

	/**
	 * Confirms the functionality of the Aside component.
	 */
	public function test_transform_aside(): void {
		$post_content = <<<HTML
<!-- wp:paragraph -->
<p>Lorem ipsum dolor sit amet.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"test-aside-class"} -->
<p class="test-aside-class">This is an aside.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Consectetur adipiscing elit.</p>
<!-- /wp:paragraph -->
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'body', $json['components'][3]['role'], 'Expected the paragraph before the aside to have a role of body.' );
		$this->assertEquals( '<p>Lorem ipsum dolor sit amet.</p>', $json['components'][3]['text'], 'Expected the paragraph before the aside to have the correct text.' );
		$this->assertEquals( 'aside', $json['components'][4]['role'], 'Expected the aside component to have a role of aside.' );
		$this->assertEquals( 'aside-layout-right', $json['components'][4]['layout'], 'Expected the aside component to be aligned to the right.' );
		$this->assertEquals(
			[
				[
					'role'      => 'body',
					'text'      => '<p>This is an aside.</p>',
					'format'    => 'html',
					'textStyle' => 'aside-subcomponent-default-body',
					'layout'    => 'aside-subcomponent-body-layout',
				],
			],
			$json['components'][4]['components'],
			'Expected the aside component list to include the correct text in a body component.'
		);
		$this->assertEquals( 'center', $json['components'][4]['anchor']['targetAnchorPosition'], 'Expected the aside component to have the proper anchor position.' );
		$this->assertEquals( 0, $json['components'][4]['anchor']['rangeStart'], 'Expected the aside component to have the proper range start value.' );
		$this->assertEquals( 1, $json['components'][4]['anchor']['rangeLength'], 'Expected the aside component to have the proper range length value.' );
		$this->assertNotEmpty( $json['components'][4]['anchor']['targetComponentIdentifier'], 'Expected the aside component to have a non-empty target component identifier.' );
		$this->assertEquals( $json['components'][5]['identifier'], $json['components'][4]['anchor']['targetComponentIdentifier'], 'Expected the aside component to be anchored to the following paragraph.' );
		$this->assertEquals( 'body', $json['components'][5]['role'], 'Expected the paragraph after the aside to have a role of body.' );
		$this->assertEquals( '<p>Consectetur adipiscing elit.</p>', $json['components'][5]['text'], 'Expected the paragraph after the aside to have the correct text.' );
	}

	/**
	 * Tests the behavior of customizing layouts and styles for subcomponents within an aside.
	 */
	public function test_subcomponents(): void {
		// Set up a custom layout and text style for an h2 within the aside component.
		$this->set_theme_settings(
			[
				'json_templates' => [
					'aside' => [
						'subcomponents' => [
							'heading' => [
								'default-heading-2' => [
									'fontName'      => 'Copperplate',
									'fontSize'      => 16,
									'lineHeight'    => 20,
									'textColor'     => '#abc123',
									'textAlignment' => 'left',
									'tracking'      => 0.1,
								],
								'heading-layout-2'  => [
									'columnStart' => 1,
									'columnSpan'  => 3,
									'margin'      => [
										'bottom' => 5,
										'top'    => 5,
									],
								],
							],
						],
					],
				],
			]
		);

		// Create a post with an aside containing an h2.
		$post_content = <<<HTML
<!-- wp:paragraph -->
<p>Lorem ipsum dolor sit amet.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"test-aside-class"} -->
<div class="wp-block-group test-aside-class"><!-- wp:heading -->
<h2 class="wp-block-heading">Test Heading in Aside</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:paragraph -->
<p>Consectetur adipiscing elit.</p>
<!-- /wp:paragraph -->
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );

		// Ensure the h2 within the aside has the custom layout and text style.
		$this->assertEquals( 'aside', $json['components'][4]['role'], 'Expected the aside component to have a role of aside.' );
		$this->assertEquals( 'heading2', $json['components'][4]['components'][0]['role'], 'Expected the subcomponent to have a role of heading2.' );
		$this->assertEquals( 'Test Heading in Aside', $json['components'][4]['components'][0]['text'], 'Expected the subcomponent to have the correct text.' );
		$this->assertEquals( 'aside-subcomponent-default-heading-2', $json['components'][4]['components'][0]['textStyle'], 'Expected the subcomponent to have the correct text style.' );
		$this->assertEquals( 'aside-subcomponent-heading-layout-2', $json['components'][4]['components'][0]['layout'], 'Expected the subcomponent to have the correct layout.' );
		$this->assertEquals( 'Copperplate', $json['componentTextStyles']['aside-subcomponent-default-heading-2']['fontName'], 'Expected the text style to have the correct font name.' );
		$this->assertEquals( 16, $json['componentTextStyles']['aside-subcomponent-default-heading-2']['fontSize'], 'Expected the text style to have the correct font size.' );
		$this->assertEquals( 20, $json['componentTextStyles']['aside-subcomponent-default-heading-2']['lineHeight'], 'Expected the text style to have the correct line height.' );
		$this->assertEquals( '#abc123', $json['componentTextStyles']['aside-subcomponent-default-heading-2']['textColor'], 'Expected the text style to have the correct text color.' );
		$this->assertEquals( 'left', $json['componentTextStyles']['aside-subcomponent-default-heading-2']['textAlignment'], 'Expected the text style to have the correct text alignment.' );
		$this->assertEquals( 0.1, $json['componentTextStyles']['aside-subcomponent-default-heading-2']['tracking'], 'Expected the text style to have the correct tracking.' );
		$this->assertEquals( 1, $json['componentLayouts']['aside-subcomponent-heading-layout-2']['columnStart'], 'Expected the subcomponent layout to have the correct column start.' );
		$this->assertEquals( 3, $json['componentLayouts']['aside-subcomponent-heading-layout-2']['columnSpan'], 'Expected the subcomponent layout to have the correct column span.' );
		$this->assertEquals( 5, $json['componentLayouts']['aside-subcomponent-heading-layout-2']['margin']['bottom'], 'Expected the subcomponent layout to have the correct bottom margin.' );
		$this->assertEquals( 5, $json['componentLayouts']['aside-subcomponent-heading-layout-2']['margin']['top'], 'Expected the subcomponent layout to have the correct top margin.' );
	}
}
