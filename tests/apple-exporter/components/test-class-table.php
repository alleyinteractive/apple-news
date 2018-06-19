<?php
/**
 * Publish to Apple News Tests: Table_Test class
 *
 * Contains a class which is used to test Apple_Exporter\Components\Table.
 *
 * @package Apple_News
 * @subpackage Tests
 */

require_once __DIR__ . '/class-component-testcase.php';

use Apple_Exporter\Exporter;
use Apple_Exporter\Exporter_Content;
use Apple_Exporter\Components\Table;

/**
 * A class which is used to test the Apple_Exporter\Components\Table class.
 */
class Table_Test extends Component_TestCase {

	/**
	 * Instructions to be executed before each test.
	 *
	 * @access public
	 */
	public function setUp() {

		// Run the parent setup function (not done automatically).
		parent::setup();

		// Turn on HTML support globally in the

		// Create an example table to use in tests.
		$this->html = <<<HTML
<table>
	<thead>
		<tr>
			<th>Column Header 1</th>
			<th>Column Header 2</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Column Data 1</td>
			<td>Column Data 2</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td>Column Footer 1</td>
			<td>Column Footer 2</td>
		</tr>
	</tfoot>
</table>
HTML;

	}

	/**
	 * Tests HTML formatting.
	 *
	 * @access public
	 */
	public function testHTML() {

		// Setup.
		$component = new Table(
			$this->html,
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			array(
				'html' => $this->html,
				'layout' => 'table-layout',
				'role' => 'htmltable',
				'style' => 'default-table',
			),
			$component->to_array()
		);
	}

	/**
	 * Tests table settings.
	 *
	 * @access public
	 */
	public function testSettings() {

		// Setup.
		$content = new Exporter_Content(
			3,
			'Title',
			$this->html
		);

		// Set table settings.
		$theme = \Apple_Exporter\Theme::get_used();
		$settings = $theme->all_settings();
		$settings['table_border_color'] = '#abcdef';
		$settings['table_border_style'] = 'dashed';
		$settings['table_border_width'] = 5;
		$settings['table_body_background_color'] = '#fedcba';
		$settings['table_body_color'] = '#123456';
		$settings['table_body_font'] = 'AmericanTypewriter';
		$settings['table_body_horizontal_alignment'] = 'center';
		$settings['table_body_line_height'] = 1;
		$settings['table_body_padding'] = 2;
		$settings['table_body_size'] = 3;
		$settings['table_body_tracking'] = 4;
		$settings['table_body_vertical_alignment'] = 'bottom';
		$settings['table_header_background_color'] = '#654321';
		$settings['table_header_color'] = '#987654';
		$settings['table_header_font'] = 'Menlo-Regular';
		$settings['table_header_horizontal_alignment'] = 'right';
		$settings['table_header_line_height'] = 5;
		$settings['table_header_padding'] = 6;
		$settings['table_header_size'] = 7;
		$settings['table_header_tracking'] = 8;
		$settings['table_header_vertical_alignment'] = 'top';
		$theme->load( $settings );
		$this->assertTrue( $theme->save() );

		// Run the export.
		$exporter = new Exporter( $content, null, $this->settings );
		$json = $exporter->export();
		$this->ensure_tokens_replaced( $json );
		$json = json_decode( $json, true );

		// Validate table layout in generated JSON.
		$this->assertEquals(
			array(
				'margin' => array(
					'bottom' => 1,
				),
			),
			$json['componentLayouts']['table-layout']
		);

		// Validate table settings in generated JSON.
		$this->assertEquals(
			array(
				'border' => array(
					'all' => array(
						'color' => '#4f4f4f',
						'style' => 'solid',
						'width' => 1.0,
					),
				),
				'tableStyle' => array(
					'cells' => array(
						'backgroundColor' => '#fafafa',
						'horizontalAlignment' => 'left',
						'padding' => 5.0,
						'textStyle' => array(
							'fontName' => 'AvenirNext-Regular',
							'fontSize' => 16,
							'lineHeight' => 20.0,
							'textColor' => '#4f4f4f',
							'tracking' => 0,
						),
						'verticalAlignment' => 'center',
					),
					'columns' => array(
						'divider' => array(
							'color' => '#4f4f4f',
							'style' => 'solid',
							'width' => 1.0,
						),
					),
					'headerCells' => array(
						'backgroundColor' => '#fafafa',
						'horizontalAlignment' => 'center',
						'padding' => 10.0,
						'textStyle' => array(
							'fontName' => 'AvenirNext-Regular',
							'fontSize' => 16,
							'lineHeight' => 20.0,
							'textColor' => '#4f4f4f',
							'tracking' => 0,
						),
						'verticalAlignment' => 'center',
					),
					'headerRows' => array(
						'divider' => array(
							'color' => '#4f4f4f',
							'style' => 'solid',
							'width' => 1.0,
						),
					),
					'rows' => array(
						'divider' => array(
							'color' => '#4f4f4f',
							'style' => 'solid',
							'width' => 1.0,
						),
					),
				),
			),
			$json['componentStyles']['default-table']
		);
	}
}
