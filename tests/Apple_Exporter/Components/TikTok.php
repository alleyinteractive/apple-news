<?php
/**
 * Publish to Apple News tests: TikTok class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

use Apple_News\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A class to test the behavior of the
 * \Apple_Exporter\Components\TikTok class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class TikTok extends TestCase {

	/**
	 * A data provider for the test_transform function.
	 *
	 * @return array An array of arrays representing function arguments.
	 */
	public static function data_transform() {
		return [
			[
				// Gutenberg block.
				<<<HTML
<!-- wp:embed {"url":"https://www.tiktok.com/@charliehunter67/video/7139291200539905326","type":"video","providerNameSlug":"tiktok","responsive":true} -->
<figure class="wp-block-embed is-type-video is-provider-tiktok wp-block-embed-tiktok"><div class="wp-block-embed__wrapper">
https://www.tiktok.com/@charliehunter67/video/7139291200539905326
</div></figure>
<!-- /wp:embed -->
HTML
				,
				'https://www.tiktok.com/@charliehunter67/video/7139291200539905326',
			],

			// oEmbed text.
			[
				<<<HTML
<p>https://www.tiktok.com/@charliehunter67/video/7139291200539905326</p>
HTML
				,
				'https://www.tiktok.com/@charliehunter67/video/7139291200539905326',
			],

			// Full html embed.
			[
				<<<HTML
<blockquote class="tiktok-embed" style="max-width: 605px; min-width: 325px;" cite="https://www.tiktok.com/@charliehunter67/video/7139291200539905326" data-video-id="7139291200539905326"><section><a title="@charliehunter67" href="https://www.tiktok.com/@charliehunter67?refer=embed" target="_blank" rel="noopener">@charliehunter67</a><a title="♬ original sound - Charlie Hunter" href="https://www.tiktok.com/music/original-sound-7139291194638519083?refer=embed" target="_blank" rel="noopener">♬ original sound - Charlie Hunter</a></section></blockquote>
HTML
				,
				'https://www.tiktok.com/@charliehunter67/video/7139291200539905326',
			],
		];
	}

	/**
	 * Tests transforming an embedded tiktok video into a TikTok component.
	 *
	 * @param string $post_content HTML with embedded TikTok video.
	 * @param string $expected     The expected URL rendered into the component.
	 */
	#[DataProvider( 'data_transform' )]
	public function test_transform( $post_content, $expected ) {
		$this->become_admin();
		$post_id = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			[
				'role' => 'tiktok',
				'URL'  => $expected,
			],
			$json['components'][3]
		);
	}
}
