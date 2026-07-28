<?php
/**
 * Publish to Apple News tests: Divider class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

/**
 * A class to test the behavior of the
 * \Apple_Exporter\Components\Divider class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Divider extends Component_TestCase {

	/**
	 * Ensures that an <hr/> tag gets converted to a Divider component.
	 */
	public function test_building_removes_tags() {
		$component = new \Apple_Exporter\Components\Divider(
			'<hr/>',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);
		$result    = $component->to_array();

		$this->assertEquals( 'divider', $result['role'] );
		$this->assertEquals( 'divider-layout', $result['layout'] );
		$this->assertNotNull( $result['stroke'] );
	}

	/**
	 * Tests the behavior of the apple_news_divider_json filter.
	 */
	public function test_filter() {
		$component = new \Apple_Exporter\Components\Divider(
			'<hr/>',
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		add_filter(
			'apple_news_divider_json',
			function ( $json ) {
				$json['layout'] = 'fancy-layout';
				return $json;
			}
		);

		$result = $component->to_array();
		$this->assertEquals( 'fancy-layout', $result['layout'] );
	}
}
