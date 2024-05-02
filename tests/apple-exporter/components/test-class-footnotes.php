<?php
/**
 * Publish to Apple News tests: Footnotes_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Components\Footnotes;

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Footnotes class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Footnotes_Test extends Apple_News_Component_TestCase {

	/**
	 * Ensures that a wp:footnotes block gets converted to a Footnotes component.
	 */
	public function test_building_removes_tags() {
		$component = new Footnotes(
			'<ol class="wp-block-footnotes"><li id="577590df-3757-486a-ad8b-d0cf6434ac6e">Aptent id ligula aliquet montes et per. Faucibus velit nulla dapibus ipsum habitant etiam nec parturient eros ex. <a href="#577590df-3757-486a-ad8b-d0cf6434ac6e-link" aria-label="Jump to footnote reference 1">↩︎</a></li><li id="3bb8f8d3-ce79-474c-a60e-8e3cd8b8e620">Praesent quam commodo nostra molestie sollicitudin bibendum dignissim. Taciti morbi quis suscipit nam ornare mollis eget fringilla. <a href="#3bb8f8d3-ce79-474c-a60e-8e3cd8b8e620-link" aria-label="Jump to footnote reference 2">↩︎</a></li></ol>',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result    = $component->to_array();

		$this->assertEquals( 'container', $result['role'] );
		$this->assertEquals( 'body-layout', $result['layout'] );
		$components = $result['components'];
		$this->assertCount( 2, $components );
		$this->assertEquals( 'body', $components[0]['role'] );
		$this->assertEquals( 'body', $components[1]['role'] );
		$this->assertEquals( '<p id="577590df-3757-486a-ad8b-d0cf6434ac6e">1. Aptent id ligula aliquet montes et per. Faucibus velit nulla dapibus ipsum habitant etiam nec parturient eros ex. <a href="#577590df-3757-486a-ad8b-d0cf6434ac6e-link" aria-label="Jump to footnote reference 1">↩︎</a></p>', $components[0]['text'] );
		$this->assertEquals( '<p id="3bb8f8d3-ce79-474c-a60e-8e3cd8b8e620">2. Praesent quam commodo nostra molestie sollicitudin bibendum dignissim. Taciti morbi quis suscipit nam ornare mollis eget fringilla. <a href="#3bb8f8d3-ce79-474c-a60e-8e3cd8b8e620-link" aria-label="Jump to footnote reference 2">↩︎</a></p>', $components[1]['text'] );
		$this->assertEquals( '577590df-3757-486a-ad8b-d0cf6434ac6e', $components[0]['identifier'] );
		$this->assertEquals( '3bb8f8d3-ce79-474c-a60e-8e3cd8b8e620', $components[1]['identifier'] );
	}
}
