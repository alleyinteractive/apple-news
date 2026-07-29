<?php
/**
 * Publish to Apple News tests: Embed_Generic class
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Apple_Exporter\Components;

use Apple_News\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A class to test the behavior of the
 * Apple_Exporter\Components\Embed_Generic class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Embed_Generic extends TestCase {

	/**
	 * A data provider for the test_transform function.
	 *
	 * We will test a sample of embed signatures that the plugin supports,
	 * including a handful of Gutenberg embeds other than the ones that Apple News
	 * supports explicitly (Facebook, Instagram, Twitter, Vimeo, YouTube) as well
	 * as any iframe embeds that are either handled through the standard WordPress
	 * oEmbed system, in which the iframe is embedded directly inside of a
	 * paragraph tag, or where an iframe is the root-level element. Any other
	 * embed signatures are not supported out of the box due to the difficulty of
	 * matching against the HTML, and handling will fall back to standard element
	 * processing (images, links, etc).
	 *
	 * @return array An array of test data.
	 */
	public static function data_transform() {
		/* phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript */
		return [
			// Gutenberg: Generic embed.
			[
				<<<HTML
<!-- wp:embed {"url":"https://wordpress.org/plugins/publish-to-apple-news/","type":"wp-embed","providerNameSlug":"plugin-directory"} -->
<figure class="wp-block-embed is-type-wp-embed is-provider-plugin-directory wp-block-embed-plugin-directory"><div class="wp-block-embed__wrapper">
https://wordpress.org/plugins/publish-to-apple-news/
</div></figure>
<!-- /wp:embed -->
HTML
				,
				'https://wordpress.org/plugins/publish-to-apple-news/',
				'the original site',
			],

			// Gutenberg: Flickr embed.
			[
				<<<HTML
<!-- wp:embed {"url":"https://flickr.com/photos/bees/2362225867/","type":"photo","providerNameSlug":"flickr","responsive":true} -->
<figure class="wp-block-embed is-type-photo is-provider-flickr wp-block-embed-flickr"><div class="wp-block-embed__wrapper">
https://flickr.com/photos/bees/2362225867/
</div></figure>
<!-- /wp:embed -->
HTML
				,
				'https://flickr.com/photos/bees/2362225867/',
				'Flickr',
			],

			// Gutenberg: Imgur embed.
			[
				<<<HTML
<!-- wp:embed {"url":"https://imgur.com/a/CxNMoZy","type":"rich","providerNameSlug":"imgur","responsive":true} -->
<figure class="wp-block-embed is-type-rich is-provider-imgur wp-block-embed-imgur"><div class="wp-block-embed__wrapper">
https://imgur.com/a/CxNMoZy
</div></figure>
<!-- /wp:embed -->
HTML
				,
				'https://imgur.com/a/CxNMoZy',
				'Imgur',
			],

			// Gutenberg: Issuu embed.
			[
				<<<HTML
<!-- wp:embed {"url":"https://issuu.com/iscience/docs/issue23","type":"rich","providerNameSlug":"issuu","responsive":true} -->
<figure class="wp-block-embed is-type-rich is-provider-issuu wp-block-embed-issuu"><div class="wp-block-embed__wrapper">
https://issuu.com/iscience/docs/issue23
</div></figure>
<!-- /wp:embed -->
HTML
				,
				'https://issuu.com/iscience/docs/issue23',
				'Issuu',
			],

			// Gutenberg: Reddit embed.
			[
				<<<HTML
<!-- wp:embed {"url":"https://www.reddit.com/r/aww/comments/4lwccv/someone_came_to_visit_woodchips_for_scale/","type":"rich","providerNameSlug":"reddit","responsive":true} -->
<figure class="wp-block-embed is-type-rich is-provider-reddit wp-block-embed-reddit"><div class="wp-block-embed__wrapper">
https://www.reddit.com/r/aww/comments/4lwccv/someone_came_to_visit_woodchips_for_scale/
</div></figure>
<!-- /wp:embed -->
HTML
				,
				'https://www.reddit.com/r/aww/comments/4lwccv/someone_came_to_visit_woodchips_for_scale/',
				'Reddit',
			],

			// Gutenberg: SoundCloud embed.
			[
				<<<HTML
<!-- wp:embed {"url":"https://soundcloud.com/robinsofficial/robin-s-show-me-love","type":"rich","providerNameSlug":"soundcloud","responsive":true} -->
<figure class="wp-block-embed is-type-rich is-provider-soundcloud wp-block-embed-soundcloud"><div class="wp-block-embed__wrapper">
https://soundcloud.com/robinsofficial/robin-s-show-me-love
</div></figure>
<!-- /wp:embed -->
HTML
				,
				'https://soundcloud.com/robinsofficial/robin-s-show-me-love',
				'SoundCloud',
			],

			// Gutenberg: Spotify embed.
			[
				<<<HTML
<!-- wp:embed {"url":"https://open.spotify.com/track/2XKsHHNCtKqk9cF35TRFyC?si=8f5b540ce9754431","type":"rich","providerNameSlug":"spotify","responsive":true,"className":"wp-embed-aspect-21-9 wp-has-aspect-ratio"} -->
<figure class="wp-block-embed is-type-rich is-provider-spotify wp-block-embed-spotify wp-embed-aspect-21-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
https://open.spotify.com/track/2XKsHHNCtKqk9cF35TRFyC?si=8f5b540ce9754431
</div></figure>
<!-- /wp:embed -->
HTML
				,
				'https://open.spotify.com/track/2XKsHHNCtKqk9cF35TRFyC?si=8f5b540ce9754431',
				'Spotify',
			],

			// Classic: Amazon Kindle embed.
			[
				<<<HTML
<p><iframe title="The Design of Everyday Things: Revised and Expanded Edition" type="text/html" width="640" height="550" frameborder="0" allowfullscreen style="max-width:100%" src="https://read.amazon.com/kp/card?preview=inline&#038;linkCode=kpd&#038;ref_=k4w_oembed_fQJVU4T7hZhNqQ&#038;asin=B00E257T6C&#038;tag=kpembed-20"></iframe></p>
HTML
				,
				'https://read.amazon.com/kp/card?preview=inline&#038;linkCode=kpd&#038;ref_=k4w_oembed_fQJVU4T7hZhNqQ&#038;asin=B00E257T6C&#038;tag=kpembed-20',
				'Amazon',
				'The Design of Everyday Things: Revised and Expanded Edition',
			],

			// Classic: Animoto embed.
			[
				<<<HTML
<p><iframe title="Video Player" id="vp1WmGs0" width="640" height="360" frameborder="0" src="https://s3.amazonaws.com/embed.animoto.com/play.html?w=swf/production/vp1&#038;e=1565903296&#038;f=WmGs0SgMeHvBMur0fL68rw&#038;d=0&#038;m=b&#038;r=360p+480p+720p&#038;i=m&#038;asset_domain=s3-p.animoto.com&#038;animoto_domain=animoto.com&#038;options=" allowfullscreen></iframe></p>
HTML
				,
				'https://s3.amazonaws.com/embed.animoto.com/play.html?w=swf/production/vp1&#038;e=1565903296&#038;f=WmGs0SgMeHvBMur0fL68rw&#038;d=0&#038;m=b&#038;r=360p+480p+720p&#038;i=m&#038;asset_domain=s3-p.animoto.com&#038;animoto_domain=animoto.com&#038;options=',
				'Animoto',
				'Video Player',
			],

			// Classic: Cloudup embed.
			[
				<<<HTML
<p><iframe title="Video Stream - share clips and home movies" src="https://cloudup.com/cjZ6QGIsErH?chromeless" data-uid="cjZ6QGIsErH" data-aspect-ratio='1.3704496788008564' width="640" height="467" scrolling="no" frameborder="0" mozallowfullscreen="true" webkitallowfullscreen="true" allowfullscreen="true" class="cloudup_iframe_embed"></iframe></p>
HTML
				,
				'https://cloudup.com/cjZ6QGIsErH?chromeless',
				'Cloudup',
				'Video Stream - share clips and home movies',
			],

			// Classic: CollegeHumor embed.
			[
				<<<HTML
<p><iframe title="Prank War 7: The Half Million Dollar Shot" src="https://www.collegehumor.com/e/3922232" width="640" height="360" frameborder="0" webkitAllowFullScreen allowFullScreen></iframe></p>
HTML
				,
				'https://www.collegehumor.com/e/3922232',
				'CollegeHumor',
				'Prank War 7: The Half Million Dollar Shot',
			],

			// Classic: Dailymotion embed.
			[
				<<<HTML
<p><iframe title="Babysitter!" frameborder="0" width="640" height="480" src="https://www.dailymotion.com/embed/video/xoxulz" allowfullscreen allow="autoplay"></iframe></p>
HTML
				,
				'https://www.dailymotion.com/embed/video/xoxulz',
				'Dailymotion',
				'Babysitter!',
			],

			// Classic: Hulu embed.
			[
				<<<HTML
<p><iframe title="Wed, May 21, 2008 (Late Night With Conan O&#039;Brien)" width="640" height="370" src="//www.hulu.com/embed.html?eid=0-njKp22bl4GivFXH0lh5w" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowfullscreen> </iframe></p>
HTML
				,
				'https://www.hulu.com/embed.html?eid=0-njKp22bl4GivFXH0lh5w',
				'Hulu',
				'Wed, May 21, 2008 (Late Night With Conan O\'Brien)',
			],

			// Classic: Kickstarter embed.
			[
				<<<HTML
<p><iframe title="Help Support The Kiggins Theatre to go Digital!" src="https://www.kickstarter.com/projects/1115015686/help-support-the-kiggins-theatre-to-go-digital/widget/video.html" height="360.0" width="640" frameborder="0" scrolling="no"></iframe></p>
HTML
				,
				'https://www.kickstarter.com/projects/1115015686/help-support-the-kiggins-theatre-to-go-digital/widget/video.html',
				'Kickstarter',
				'Help Support The Kiggins Theatre to go Digital!',
			],

			// Classic: Mixcloud embed.
			[
				<<<HTML
<p><iframe title="Moving Sounds With James Heather (11/08/2019)" width="100%" height="120" src="https://www.mixcloud.com/widget/iframe/?feed=https%3A%2F%2Fwww.mixcloud.com%2Fsohoradio%2Fmoving-sounds-with-james-heather-11082019%2F&amp;hide_cover=1" frameborder="0"></iframe></p>
HTML
				,
				'https://www.mixcloud.com/widget/iframe/?feed=https%3A%2F%2Fwww.mixcloud.com%2Fsohoradio%2Fmoving-sounds-with-james-heather-11082019%2F&#038;hide_cover=1',
				'Mixcloud',
				'Moving Sounds With James Heather (11/08/2019)',
			],

			// Classic: ReverbNation embed.
			[
				<<<HTML
<p><iframe title="Easy by Dyaphonic" width="640" height="960" scrolling="no" frameborder="no" src="https://www.reverbnation.com/widget_code/html_widget/artist_3796072?widget_id=55&#038;pwc[song_ids]=30572216"></iframe></p>
HTML
				,
				'https://www.reverbnation.com/widget_code/html_widget/artist_3796072?widget_id=55&#038;pwc%5Bsong_ids%5D=30572216',
				'ReverbNation',
				'Easy by Dyaphonic',
			],

			// Classic: Scribd embed.
			[
				<<<HTML
<p><iframe title="Synthesis of Knowledge: Effects of Fire and Thinning Treatments on Understory Vegetation in Dry U.S. Forests" class="scribd_iframe_embed" src="https://www.scribd.com/embeds/110799637/content" scrolling="no" id="110799637" width="640" height="960" frameborder="0"></iframe><script type="text/javascript">          (function() { var scribd = document.createElement("script"); scribd.type = "text/javascript"; scribd.async = true; scribd.src = "https://www.scribd.com/javascripts/embed_code/inject.js"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(scribd, s); })()        </script></p>
HTML
				,
				'https://www.scribd.com/embeds/110799637/content',
				'Scribd',
				'Synthesis of Knowledge: Effects of Fire and Thinning Treatments on Understory Vegetation in Dry U.S. Forests',
			],

			// Classic: Slideshare embed.
			[
				<<<HTML
<p><iframe title="Business Quotes for 2011" src="https://www.slideshare.net/slideshow/embed_code/key/6PCWPGFw9SwsAY" width="427" height="356" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="border:1px solid #CCC; border-width:1px; margin-bottom:5px; max-width: 100%;" allowfullscreen> </iframe> </p>
<div style="margin-bottom:5px"> <strong> <a href="https://www.slideshare.net/haraldf/business-quotes-for-2011" title="Business Quotes for 2011" target="_blank">Business Quotes for 2011</a> </strong> from <strong><a href="https://www.slideshare.net/haraldf" target="_blank">Harald Felgner, PhD</a></strong> </div>
HTML
				,
				'https://www.slideshare.net/slideshow/embed_code/key/6PCWPGFw9SwsAY',
				'Slideshare',
				'Business Quotes for 2011',
			],

			// Classic: SoundCloud embed.
			[
				<<<HTML
<p><iframe title="Flickermood by Forss" width="640" height="400" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?visual=true&#038;url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F293&#038;show_artwork=true&#038;maxwidth=640&#038;maxheight=960&#038;dnt=1"></iframe></p>
HTML
				,
				'https://w.soundcloud.com/player/?visual=true&#038;url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F293&#038;show_artwork=true&#038;maxwidth=640&#038;maxheight=960&#038;dnt=1',
				'SoundCloud',
				'Flickermood by Forss',
			],

			// Classic: Speaker Deck embed.
			[
				<<<HTML
<p><iframe title="Why Backbone" id="talk_frame_48643" src="//speakerdeck.com/player/4648d440a3230130452522b217532879" width="640" height="480" style="border:0; padding:0; margin:0; background:transparent;" frameborder="0" allowtransparency="true" allowfullscreen="allowfullscreen" mozallowfullscreen="true" webkitallowfullscreen="true"></iframe></p>
HTML
				,
				'https://speakerdeck.com/player/4648d440a3230130452522b217532879',
				'Speaker Deck',
				'Why Backbone',
			],

			// Classic: Spotify embed.
			[
				<<<HTML
<p><iframe title="Spotify Embed: M1 A1" width="300" height="380" allowtransparency="true" frameborder="0" allow="encrypted-media" src="https://open.spotify.com/embed/track/2qToAcex0ruZfbEbAy9OhW"></iframe></p>
HTML
				,
				'https://open.spotify.com/embed/track/2qToAcex0ruZfbEbAy9OhW',
				'Spotify',
				'Spotify Embed: M1 A1',
			],

			// Classic: TED embed.
			[
				<<<HTML
<p><iframe title="Jill Bolte Taylor: My stroke of insight" src="https://embed.ted.com/talks/jill_bolte_taylor_s_powerful_stroke_of_insight" width="640" height="361" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></p>
HTML
				,
				'https://embed.ted.com/talks/jill_bolte_taylor_s_powerful_stroke_of_insight',
				'TED',
				'Jill Bolte Taylor: My stroke of insight',
			],

			// Classic: VideoPress embed.
			[
				<<<HTML
<p><iframe title="Matt Mullenweg: Matt on WordPress" width='640' height='360' src='https://videopress.com/embed/bd2G0c0g?hd=0' frameborder='0' allowfullscreen></iframe><script src='https://v0.wordpress.com/js/next/videopress-iframe.js?m=1435166243'></script></p>
HTML
				,
				'https://videopress.com/embed/bd2G0c0g?hd=0',
				'VideoPress',
				'Matt Mullenweg: Matt on WordPress',
			],

			// Classic: WordPress.tv embed. WordPress.tv will present as VideoPress when embedded this way.
			[
				<<<HTML
<p><iframe width='640' height='360' src='https://videopress.com/embed/DK5mLrbr?hd=0' frameborder='0' allowfullscreen></iframe><script src='https://v0.wordpress.com/js/next/videopress-iframe.js?m=1435166243'></script></p>
HTML
				,
				'https://videopress.com/embed/DK5mLrbr?hd=0',
				'VideoPress',
			],
		];
		/* phpcs:enable */
	}

	/**
	 * A filter function to modify the URL in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_embed_generic_json( $json ) {
		$json['layout'] = 'my-cool-layout';

		return $json;
	}

	/**
	 * Test the `apple_news_embed_generic_json` filter.
	 */
	public function test_filter() {
		add_filter( 'apple_news_embed_generic_json', [ $this, 'filter_apple_news_embed_generic_json' ] );

		// Create a test post and get JSON for it.
		$post_content = <<<HTML
<!-- wp:embed {"url":"https://wordpress.org/plugins/publish-to-apple-news/","type":"wp-embed","providerNameSlug":"plugin-directory"} -->
<figure class="wp-block-embed is-type-wp-embed is-provider-plugin-directory wp-block-embed-plugin-directory"><div class="wp-block-embed__wrapper">
https://wordpress.org/plugins/publish-to-apple-news/
</div></figure>
<!-- /wp:embed -->
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json         = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'my-cool-layout', $json['components'][3]['layout'] );

		// Teardown.
		remove_filter( 'apple_news_embed_generic_json', [ $this, 'filter_apple_news_embed_generic_json' ] );
	}

	/**
	 * Tests the transformation process from oEmbed HTML to an Embed Generic component.
	 *
	 * @param string $post_content The post content to set for the post.
	 * @param string $url          The URL of the embedded content.
	 * @param string $provider     The embed provider name.
	 * @param string $title        The title of the embed.
	 */
	#[DataProvider( 'data_transform' )]
	public function test_transform( $post_content, $url = '', $provider = '', $title = '' ) {
		// Become an administrator so we can save post_content with iframes in it.
		$this->become_admin();
		$post_id = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$json    = $this->get_json_for_post( $post_id );

		// Set up expected components result.
		$components = [
			[
				'role'      => 'body',
				'text'      => '<a href="' . $url . '">View on ' . $provider . '.</a>',
				'format'    => 'html',
				'textStyle' => [
					'fontSize' => 14,
				],
			],
		];

		// If there is a provided title, push it on to the front of the components array.
		if ( ! empty( $title ) ) {
			array_unshift(
				$components,
				[
					'role'   => 'heading2',
					'text'   => $title,
					'format' => 'html',
				]
			);
		}

		// Test.
		$this->assertEquals(
			[
				'layout'     => 'embed-generic-layout',
				'role'       => 'container',
				'components' => $components,
			],
			$json['components'][3]
		);
	}
}
