<?php
/**
 * Publish to Apple News tests: Embed_Web_Video_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Embed_Web_Video class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Embed_Web_Video_Test extends Apple_News_Testcase {

	/**
	 * A data provider for the test_transform function.
	 *
	 * @return array An array of arrays representing function arguments.
	 */
	public function data_transform() {
		return [
			'Vimeo standard watch URL'                     => [
				'vimeo',
				'https://vimeo.com/12819723',
				'https://player.vimeo.com/video/12819723',
			],
			'Vimeo standard watch URL, no https'           => [
				'vimeo',
				'http://vimeo.com/12819723',
				'https://player.vimeo.com/video/12819723',
			],
			'YouTube standard watch URL'                   => [
				'youtube',
				'https://www.youtube.com/watch?v=0qwALOOvUik',
				'https://www.youtube.com/embed/0qwALOOvUik',
			],
			'YouTube embed URL'                            => [
				'youtube',
				'https://www.youtube.com/embed/0qwALOOvUik',
				'https://www.youtube.com/embed/0qwALOOvUik',
			],
			'YouTube standard watch URL, no www'           => [
				'youtube',
				'https://youtube.com/watch?v=0qwALOOvUik',
				'https://www.youtube.com/embed/0qwALOOvUik',
			],
			'YouTube embed URL, no www'                    => [
				'youtube',
				'https://youtube.com/embed/0qwALOOvUik',
				'https://www.youtube.com/embed/0qwALOOvUik',
			],
			'YouTube standard watch URL, no https'         => [
				'youtube',
				'http://www.youtube.com/watch?v=0qwALOOvUik',
				'https://www.youtube.com/embed/0qwALOOvUik',
			],
			'YouTube embed URL, no https'                  => [
				'youtube',
				'http://www.youtube.com/embed/0qwALOOvUik',
				'https://www.youtube.com/embed/0qwALOOvUik',
			],
			'YouTube standard watch URL, no https, no www' => [
				'youtube',
				'http://youtube.com/watch?v=0qwALOOvUik',
				'https://www.youtube.com/embed/0qwALOOvUik',
			],
			'YouTube embed URL, no https, no www'          => [
				'youtube',
				'http://youtube.com/embed/0qwALOOvUik',
				'https://www.youtube.com/embed/0qwALOOvUik',
			],
			'YouTube shortlink'                            => [
				'youtube',
				'https://youtu.be/0qwALOOvUik',
				'https://www.youtube.com/embed/0qwALOOvUik',
			],
			'YouTube shortlink, no https'                  => [
				'youtube',
				'http://youtu.be/0qwALOOvUik',
				'https://www.youtube.com/embed/0qwALOOvUik',
			],
		];
	}

	/**
	 * A filter function to modify the aspect ratio.
	 *
	 * @param array $json An array representing JSON for the component.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_embed_web_video_json( $json ) {
		$json['aspectRatio'] = 1.4;

		return $json;
	}

	/**
	 * Tests the dynamic aspect ratio calculation.
	 */
	public function test_aspect_ratio() {
		$post_content = <<<HTML
<!-- wp:embed {"url":"https://www.youtube.com/watch?v=0qwALOOvUik","type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-4-3 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-4-3 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
		https://www.youtube.com/watch?v=0qwALOOvUik
</div></figure>
<!-- /wp:embed -->
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			[
				'role'        => 'embedwebvideo',
				'URL'         => 'https://www.youtube.com/embed/0qwALOOvUik',
				'aspectRatio' => 1.333,
				'layout'      => 'embed-web-video-layout',
			],
			$json['components'][3]
		);
		remove_filter( 'apple_news_embed_web_video_json', [ $this, 'filter_apple_news_embed_web_video_json' ] );
	}

	/**
	 * Test the `apple_news_embed_web_video_json` filter.
	 */
	public function test_filter() {
		add_filter( 'apple_news_embed_web_video_json', [ $this, 'filter_apple_news_embed_web_video_json' ] );
		$post_content = <<<HTML
<!-- wp:embed {"url":"https://www.youtube.com/watch?v=0qwALOOvUik","type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
		https://www.youtube.com/watch?v=0qwALOOvUik
</div></figure>
<!-- /wp:embed -->
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			[
				'role'        => 'embedwebvideo',
				'URL'         => 'https://www.youtube.com/embed/0qwALOOvUik',
				'aspectRatio' => 1.4,
				'layout'      => 'embed-web-video-layout',
			],
			$json['components'][3]
		);
		remove_filter( 'apple_news_embed_web_video_json', [ $this, 'filter_apple_news_embed_web_video_json' ] );
	}

	/**
	 * Tests transforming an embed to an Embed Web Video component.
	 *
	 * @dataProvider data_transform
	 *
	 * @param string $provider  The provider. One of 'youtube' or 'vimeo'.
	 * @param string $embed_url The user-entered oEmbed URL.
	 * @param string $expected  The expected embed URL that is rendered in the component.
	 */
	public function test_transform( $provider, $embed_url, $expected ) {
		// Test in Gutenberg.
		$post_content = <<<HTML
<!-- wp:embed {"url":"{$embed_url}","type":"video","providerNameSlug":"{$provider}","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed is-type-video is-provider-{$provider} wp-block-embed-{$provider} wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
		{$embed_url}
</div></figure>
<!-- /wp:embed -->
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			[
				'role'        => 'embedwebvideo',
				'URL'         => $expected,
				'aspectRatio' => 1.777,
				'layout'      => 'embed-web-video-layout',
			],
			$json['components'][3]
		);

		// Test in the classic editor.
		$post_content = <<<HTML
<p>{$embed_url}</p>
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			[
				'role'        => 'embedwebvideo',
				'URL'         => $expected,
				'aspectRatio' => 1.777,
				'layout'      => 'embed-web-video-layout',
			],
			$json['components'][3]
		);
	}

	/**
	 * Tests the transformation of a generic Embed Gutenberg block containing a
	 * web video embed into an EmbedWebVideo component.
	 */
	public function test_transform_embed_iframe() {
		$post_content = <<<HTML
<!-- wp:embed {"url":"https://www.youtube.com/embed/0qwALOOvUik","type":"rich","providerNameSlug":"embed-handler","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed is-type-rich is-provider-embed-handler wp-block-embed-embed-handler wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
https://www.youtube.com/embed/0qwALOOvUik
</div></figure>
<!-- /wp:embed -->
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			[
				'role'        => 'embedwebvideo',
				'URL'         => 'https://www.youtube.com/embed/0qwALOOvUik',
				'aspectRatio' => 1.777,
				'layout'      => 'embed-web-video-layout',
			],
			$json['components'][3]
		);
	}

	/**
	 * Tests the transformation of an iframe containing a Vimeo embed into an
	 * EmbedWebVideo component.
	 */
	public function test_transform_vimeo_iframe() {
		$this->become_admin();
		$post_content = <<<HTML
<iframe title="vimeo-player" src="https://player.vimeo.com/video/12819723?h=eafba1f705" width="640" height="360" frameborder="0" allowfullscreen></iframe>
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			[
				'role'        => 'embedwebvideo',
				'URL'         => 'https://player.vimeo.com/video/12819723',
				'aspectRatio' => 1.777,
				'layout'      => 'embed-web-video-layout',
			],
			$json['components'][3]
		);
	}

	/**
	 * Tests the transformation of an iframe containing a YouTube embed into an
	 * EmbedWebVideo component.
	 */
	public function test_transform_youtube_iframe() {
		$this->become_admin();
		$post_content = <<<HTML
<iframe width="560" height="315" src="https://www.youtube.com/embed/0qwALOOvUik" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			[
				'role'        => 'embedwebvideo',
				'URL'         => 'https://www.youtube.com/embed/0qwALOOvUik',
				'aspectRatio' => 1.777,
				'layout'      => 'embed-web-video-layout',
			],
			$json['components'][3]
		);
	}
}
