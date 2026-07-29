<?php
/**
 * Publish to Apple News tests: Podcast class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

use Apple_News\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Podcast class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Podcast extends TestCase {
	/**
	 * A data provider for the test_transform function.
	 *
	 * @return array An array of arrays representing function arguments.
	 */
	public static function data_transform() {
		return [
			[
				<<<HTML
<iframe src="https://embed.podcasts.apple.com/us/podcast/hiking-treks/id1620111298?itsct=podcast_box_player&amp;itscg=30200&amp;ls=1&amp;theme=light" height="450px" frameborder="0" sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-top-navigation-by-user-activation" allow="autoplay *; encrypted-media *; clipboard-write" style="width: 100%; max-width: 660px; overflow: hidden; border-radius: 10px; background-color: transparent;"></iframe>
HTML
				,
				'https://podcasts.apple.com/us/podcast/hiking-treks/id1620111298',
			],
			[
				<<<HTML
<iframe src="https://embed.podcasts.apple.com/us/podcast/bouldering-around-boulder/id1620111298?i=1000558312282&amp;itsct=podcast_box_player&amp;itscg=30200&amp;ls=1&amp;theme=light" height="175px" frameborder="0" sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-top-navigation-by-user-activation" allow="autoplay *; encrypted-media *; clipboard-write" style="width: 100%; max-width: 660px; overflow: hidden; border-radius: 10px; background-color: transparent;"></iframe>
HTML
				,
				'https://podcasts.apple.com/us/podcast/bouldering-around-boulder/id1620111298?i=1000558312282',
			],
		];
	}

	/**
	 * Tests transforming an embedded podcast to a Podcast component.
	 *
	 * @param string $post_content  HTML with embedded podcast iframe.
	 * @param string $expected  The expected URL that is rendered in the component.
	 */
	#[DataProvider( 'data_transform' )]
	public function test_transform( $post_content, $expected ) {
		$this->become_admin();
		$post_id = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			[
				'role' => 'podcast',
				'URL'  => $expected,
			],
			$json['components'][3]
		);
	}
}
