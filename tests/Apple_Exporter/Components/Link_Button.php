<?php
/**
 * Publish to Apple News tests: Link_Button class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Link_Button class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Link_Button extends Component_TestCase {

	/**
	 * Tests the transformation process from a button to a Link_Button component.
	 */
	public function test_render() {
		$post_content = <<<HTML
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://www.example.org/">Test Button</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			[
				'role'      => 'link_button',
				'text'      => 'Test Button',
				'URL'       => 'https://www.example.org/',
				'style'     => 'default-link-button',
				'layout'    => 'link-button-layout',
				'textStyle' => 'default-link-button-text-style',
			],
			$json['components'][3]
		);
		$this->assertEquals(
			[
				'horizontalContentAlignment' => 'center',
				'padding'                    => [
					'bottom' => 15,
					'left'   => 15,
					'right'  => 15,
					'top'    => 15,
				],
			],
			$json['componentLayouts']['link-button-layout']
		);
		$this->assertEquals(
			[
				'backgroundColor' => '#ffffff',
				'border'          => [
					'all' => [
						'color' => '#000000',
						'width' => 1,
					],
				],
				'mask'            => [
					'radius' => 18,
					'type'   => 'corners',
				],
			],
			$json['componentStyles']['default-link-button']
		);
		$this->assertEquals(
			[
				'fontName'      => 'HelveticaNeue-Medium',
				'fontSize'      => 15,
				'hyphenation'   => false,
				'lineHeight'    => 18,
				'textAlignment' => 'center',
				'textColor'     => '#000000',
			],
			$json['componentTextStyles']['default-link-button-text-style']
		);
	}
}
