<?php
/**
 * Publish to Apple News tests: Apple_News_Table_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Exporter;
use Apple_Exporter\Exporter_Content;
use Apple_Exporter\Components\Table;

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Table class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Table_Test extends Apple_News_Component_TestCase {
	/**
	 * The HTML code to display.
	 *
	 * @var string $html
	 */
	private string $html;
	/**
	 * The caption for the HTML.
	 *
	 * @var string $html_caption
	 */
	private string $html_caption;

	/**
	 * A fixture containing operations to be run before each test.
	 */
	public function setUp(): void {
		parent::setup();

		// Create an example table to use in tests.
		$this->html         = '<table><thead><tr><th>Column Header 1</th><th>Column Header 2</th></tr></thead><tbody><tr><td>Column Data 1</td><td>Column Data 2</td></tr></tbody><tfoot><tr><td>Column Footer 1</td><td>Column Footer 2</td></tr></tfoot></table>';
		$this->html_caption = '<figure class="wp-block-table"><table><thead><tr><th>Column Header 1</th><th>Column Header 2</th></tr></thead><tbody><tr><td>Column Data 1</td><td>Column Data 2</td></tr></tbody><tfoot><tr><td>Column Footer 1</td><td>Column Footer 2</td></tr></tfoot></table><figcaption>Caption</figcaption></figure>';
	}

	/**
	 * Tests HTML formatting with captions.
	 */
	public function test_captions() {
		$component = new Table(
			$this->html_caption,
			$this->workspace,
			$this->settings,
			$this->styles,
			$this->layouts,
			null,
			$this->component_styles
		);

		// Test.
		$this->assertEquals(
			[
				'role'       => 'container',
				'components' => [
					[
						'role'   => 'htmltable',
						'html'   => '<table><thead><tr><th>Column Header 1</th><th>Column Header 2</th></tr></thead><tbody><tr><td>Column Data 1</td><td>Column Data 2</td></tr></tbody><tfoot><tr><td>Column Footer 1</td><td>Column Footer 2</td></tr></tfoot></table>',
						'layout' => 'table-layout',
						'style'  => 'default-table',
					],
					[
						'role'   => 'caption',
						'text'   => 'Caption',
						'format' => 'html',
					],
				],
			],
			$component->to_array()
		);
	}

	/**
	 * Tests HTML formatting.
	 */
	public function test_html() {
		// Create post, generate json.
		$post_id = self::factory()->post->create( [ 'post_content' => $this->html ] );
		$json    = $this->get_json_for_post( $post_id );
		$result  = $json['components'][3];

		// Remove newlines from table html.
		$result['html'] = str_replace( "\n", '', $json['components'][3]['html'] );

		// Test.
		$this->assertEquals(
			[
				'html'   => $this->html,
				'layout' => 'table-layout',
				'role'   => 'htmltable',
				'style'  => 'default-table',
			],
			$result
		);
	}


	/**
	 * Tests dark mode colors.
	 */
	public function test_dark_colors() {
		// Set table settings.
		$this->set_theme_settings(
			[
				'table_border_color_dark'            => '#abcdef',
				'table_body_background_color_dark'   => '#fedcba',
				'table_body_color_dark'              => '#123456',
				'table_header_background_color_dark' => '#654321',
				'table_header_color_dark'            => '#987654',
			]
		);

		// Create post, generate json.
		$post_id = self::factory()->post->create( [ 'post_content' => $this->html ] );
		$json    = $this->get_json_for_post( $post_id );

		// Ensure component level conditional is set.
		$this->assertEquals(
			'dark-table',
			$json['components'][3]['conditional'][0]['style']
		);

		// Ensure border color values match.
		$this->assertEquals(
			'#abcdef',
			$json['componentStyles']['dark-table']['border']['all']['color']
		);
		$this->assertEquals(
			'#abcdef',
			$json['componentStyles']['dark-table']['tableStyle']['columns']['divider']['color']
		);
		$this->assertEquals(
			'#abcdef',
			$json['componentStyles']['dark-table']['tableStyle']['rows']['divider']['color']
		);
		$this->assertEquals(
			'#abcdef',
			$json['componentStyles']['dark-table']['tableStyle']['headerRows']['divider']['color']
		);

		// Ensure cell background and text colors match.
		$this->assertEquals(
			'#fedcba',
			$json['componentStyles']['dark-table']['tableStyle']['cells']['backgroundColor']
		);
		$this->assertEquals(
			'#123456',
			$json['componentStyles']['dark-table']['tableStyle']['cells']['textStyle']['textColor']
		);

		// Ensure header background and text colors match.
		$this->assertEquals(
			'#654321',
			$json['componentStyles']['dark-table']['tableStyle']['headerCells']['backgroundColor']
		);
		$this->assertEquals(
			'#987654',
			$json['componentStyles']['dark-table']['tableStyle']['headerCells']['textStyle']['textColor']
		);

		// Test partial dark mode styles.
		// Set table settings.
		$this->set_theme_settings(
			[
				// Set default-table style.
				'table_border_color'               => '#111111',
				'table_body_background_color'      => '#000000',
				// Reset dark mode style.
				'table_body_background_color_dark' => '',
			]
		);

		// Regenerate json after theme setting changes.
		$json = $this->get_json_for_post( $post_id );

		// Ensure component level conditional is still set.
		$this->assertEquals(
			'dark-table',
			$json['components'][3]['conditional'][0]['style']
		);

		// Ensure unset dark mode style falls back to default-table style.
		$this->assertEquals(
			'#000000',
			$json['componentStyles']['dark-table']['tableStyle']['cells']['backgroundColor']
		);
		$this->assertEquals(
			'#000000',
			$json['componentStyles']['default-table']['tableStyle']['cells']['backgroundColor']
		);

		// Ensure dark mode styles still differentiated from default-styles for fields with set values.
		$this->assertEquals(
			'#abcdef',
			$json['componentStyles']['dark-table']['border']['all']['color']
		);
		$this->assertEquals(
			'#111111',
			$json['componentStyles']['default-table']['border']['all']['color']
		);
	}

	/**
	 * Tests table settings.
	 */
	public function test_settings() {
		// Set table settings.
		$this->set_theme_settings(
			[
				'table_border_color'                => '#abcdef',
				'table_border_style'                => 'dashed',
				'table_border_width'                => 5,
				'table_body_background_color'       => '#fedcba',
				'table_body_color'                  => '#123456',
				'table_body_font'                   => 'AmericanTypewriter',
				'table_body_horizontal_alignment'   => 'center',
				'table_body_line_height'            => 1,
				'table_body_padding'                => 2,
				'table_body_size'                   => 3,
				'table_body_tracking'               => 4,
				'table_body_vertical_alignment'     => 'bottom',
				'table_header_background_color'     => '#654321',
				'table_header_color'                => '#987654',
				'table_header_font'                 => 'Menlo-Regular',
				'table_header_horizontal_alignment' => 'right',
				'table_header_line_height'          => 5,
				'table_header_padding'              => 6,
				'table_header_size'                 => 7,
				'table_header_tracking'             => 8,
				'table_header_vertical_alignment'   => 'top',
			]
		);

		// Create post, generate json.
		$post_id = self::factory()->post->create( [ 'post_content' => $this->html ] );
		$json    = $this->get_json_for_post( $post_id );

		// Validate table layout in generated JSON.
		$this->assertEquals(
			[
				'margin' => [
					'bottom' => 1,
				],
			],
			$json['componentLayouts']['table-layout']
		);

		// Ensure conditionals are not set
		// Outer Table Border.
		$this->assertFalse( isset( $json['componentStyles']['default-table']['conditional'] ) );
		// Background Color, Text Color.
		$this->assertFalse( isset( $json['componentStyles']['default-table']['tableStyle']['cells']['conditional'] ) );
		// Column Border.
		$this->assertFalse( isset( $json['componentStyles']['default-table']['tableStyle']['columns']['conditional'] ) );
		// Row Border.
		$this->assertFalse( isset( $json['componentStyles']['default-table']['tableStyle']['rows']['conditional'] ) );
		// Header Border.
		$this->assertFalse( isset( $json['componentStyles']['default-table']['tableStyle']['headerRows']['conditional'] ) );
		// Header Background, Header Text Color.
		$this->assertFalse( isset( $json['componentStyles']['default-table']['tableStyle']['headerCells']['conditional'] ) );

		// Validate table settings in generated JSON.
		$this->assertEquals(
			[
				'border'     => [
					'all' => [
						'color' => '#abcdef',
						'style' => 'dashed',
						'width' => 5,
					],
				],
				'tableStyle' => [
					'cells'       => [
						'backgroundColor'     => '#fedcba',
						'horizontalAlignment' => 'center',
						'padding'             => 2,
						'textStyle'           => [
							'fontName'   => 'AmericanTypewriter',
							'fontSize'   => 3,
							'lineHeight' => 1,
							'textColor'  => '#123456',
							'tracking'   => 4,
						],
						'verticalAlignment'   => 'bottom',
					],
					'columns'     => [
						'divider' => [
							'color' => '#abcdef',
							'style' => 'dashed',
							'width' => 5,
						],
					],
					'headerCells' => [
						'backgroundColor'     => '#654321',
						'horizontalAlignment' => 'right',
						'padding'             => 6,
						'textStyle'           => [
							'fontName'   => 'Menlo-Regular',
							'fontSize'   => 7,
							'lineHeight' => 5,
							'textColor'  => '#987654',
							'tracking'   => 8,
						],
						'verticalAlignment'   => 'top',
					],
					'headerRows'  => [
						'divider' => [
							'color' => '#abcdef',
							'style' => 'dashed',
							'width' => 5,
						],
					],
					'rows'        => [
						'divider' => [
							'color' => '#abcdef',
							'style' => 'dashed',
							'width' => 5,
						],
					],
				],
			],
			$json['componentStyles']['default-table']
		);
	}
}
