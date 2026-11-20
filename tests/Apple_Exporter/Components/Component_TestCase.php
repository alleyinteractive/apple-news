<?php
/**
 * Publish to Apple News tests: Component_TestCase class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

use Apple_News\Tests\TestCase;

/**
 * A base component class for Apple News tests.
 *
 * @package Apple_News
 */
abstract class Component_TestCase extends TestCase {

	/**
	 * Parses HTML into a DOMNode.
	 *
	 * @param string $html The HTML to parse.
	 *
	 * @return DOMNode|null A DOMNode on success, or null on failure.
	 */
	protected function build_node( $html ) {
		return ( new \Apple_Exporter\Exporter_Content( 1, '', $html ) )->nodes()->item( 0 );
	}

	/**
	 * A function to ensure that tokens are replaced in a JSON string.
	 *
	 * @param string $json The JSON to check for unreplaced tokens.
	 */
	protected function ensure_tokens_replaced( $json ) {
		preg_match( '/"#[^"#]+#"/', $json, $matches );
		$this->assertEmpty( $matches );
	}
}
