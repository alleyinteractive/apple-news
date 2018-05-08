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

use Apple_Exporter\Components\Table;
use Apple_Exporter\Exporter;
use Apple_Exporter\Exporter_Content;

/**
 * A class which is used to test the Apple_Exporter\Components\Table class.
 */
class Table_Test extends Component_TestCase {

	/**
	 * Tests HTML formatting.
	 *
	 * @access public
	 */
	public function testHTML() {

		// Setup.
		$this->settings->html_support = 'yes';
		$html = <<<HTML
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
		$component = new Table(
			$html,
			null,
			$this->settings,
			$this->styles,
			$this->layouts
		);

		// Test.
		$this->assertEquals(
			array(
				'html' => $html,
				'role' => 'htmltable',
			),
			$component->to_array()
		);
	}
}
