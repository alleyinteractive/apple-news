<?php
/**
 * Publish to Apple News tests: Intro class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

use Apple_News\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A class to test the behavior of the Apple_Exporter\Components\Intro class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Intro extends TestCase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_intro_json( $json ) {
		$json['layout'] = 'fancy-layout';

		return $json;
	}

		/**
		 * Returns an array of arrays representing function arguments to the
		 * test_filter function.
		 */
	public static function data_test_disabled_by_default() {
		return [
			[ [ 'title', 'byline' ] ],
			[ [ 'title', 'author' ] ],
		];
	}

	/**
	 * Ensures that the Intro component is disabled by default.
	 *
	 * @param string[] $meta_order The order of meta components to use.
	 */
	#[DataProvider( 'data_test_disabled_by_default' )]
	public function test_disabled_by_default( $meta_order ) {
		$this->set_theme_settings( [ 'meta_component_order' => $meta_order ] );
		$post_id = self::factory()->post->create(
			[
				'post_content' => 'Test content!',
				'post_excerpt' => 'Test excerpt!',
			]
		);
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'title', $json['components'][0]['role'] );

		$this->assertEquals( $meta_order[1], $json['components'][1]['role'] );
		$this->assertEquals( 'body', $json['components'][2]['role'] );
		$this->assertEquals( '<p>Test content!</p>', $json['components'][2]['text'] );
	}

	/**
	 * Test the `apple_news_intro_json` filter.
	 */
	public function test_filter() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'intro' ] ] );
		add_filter( 'apple_news_intro_json', [ $this, 'filter_apple_news_intro_json' ] );

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create( [ 'post_excerpt' => 'Test excerpt.' ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'intro', $json['components'][0]['role'] );
		$this->assertEquals( 'fancy-layout', $json['components'][0]['layout'] );

		// Teardown.
		remove_filter( 'apple_news_intro_json', [ $this, 'filter_apple_news_intro_json' ] );
	}

	/**
	 * Tests the render method for the component.
	 */
	public function test_render() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'intro' ] ] );

		// Create a test post and get JSON for it.
		$post_id = self::factory()->post->create( [ 'post_excerpt' => 'Test excerpt.' ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'intro', $json['components'][0]['role'] );
		$this->assertEquals( 'Test excerpt.', $json['components'][0]['text'] );
	}

	/**
	 * Ensures that the Intro component is skipped if there is no intro specified.
	 */
	public function test_skip() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'intro' ] ] );

		// Create an example post without a customized excerpt and verify that it is not included.
		$post_id_1 = self::factory()->post->create(
			[
				'post_content' => '<p>Lorem ipsum dolor sit amet.</p>',
				'post_excerpt' => '',
			]
		);
		$json      = $this->get_json_for_post( $post_id_1 );
		$this->assertEquals( 'body', $json['components'][0]['role'] );

		// Create an example post with a customized excerpt and verify that it is included.
		$post_id_2 = self::factory()->post->create(
			[
				'post_content' => '<p>Lorem ipsum dolor sit amet.</p>',
				'post_excerpt' => 'Test excerpt.',
			]
		);
		$json      = $this->get_json_for_post( $post_id_2 );
		$this->assertEquals( 'intro', $json['components'][0]['role'] );
		$this->assertEquals( 'Test excerpt.', $json['components'][0]['text'] );

		// Create an example post with an excerpt that is derivative of the main content.
		// Verify that it is skipped because it duplicates body content.
		$post_id_3 = self::factory()->post->create(
			[
				'post_content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis arcu risus, vestibulum non nulla a, mollis posuere lectus. Quisque lectus ex, viverra nec massa et, elementum sodales dui. Nam nec congue libero. Nunc eu lectus quis quam eleifend gravida. Nulla condimentum, nisl ornare rhoncus ultrices, ex ipsum luctus dolor, vitae iaculis metus magna vitae neque. Maecenas in risus id est hendrerit mattis. Curabitur pulvinar ante a ligula tincidunt, id porta ante ornare. Donec neque metus, hendrerit nec lectus in, consectetur porta dolor. Curabitur egestas orci eu tortor congue, eu varius ipsum finibus. In in faucibus mi. Donec odio leo, blandit non varius nec, cursus ac eros. Aenean sagittis mauris eget interdum elementum. Etiam hendrerit lectus at lacus pretium pretium. Vivamus eu egestas dolor. Nam a ultricies lectus.</p>',
				'post_excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis arcu risus, vestibulum non nulla a, mollis posuere lectus.',
			]
		);
		$json      = $this->get_json_for_post( $post_id_3 );
		$this->assertEquals( 'body', $json['components'][0]['role'] );
	}
}
