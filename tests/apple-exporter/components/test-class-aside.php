<?php
/**
 * Publish to Apple News tests: Aside_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Components\Footnotes;

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Aside class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Aside_Test extends Apple_News_TestCase {

	/**
	 * Confirms the functionality of the Aside component.
	 */
	public function test_transform_aside() {
		update_option(
			'apple_news_settings',
			array_merge(
				get_option( 'apple_news_settings' ) ?: [],
				[
					'aside_component_class' => 'test-aside-class',
				]
			)
		);
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
					'textStyle' => 'default-body',
					'layout'    => 'body-layout',
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
}
