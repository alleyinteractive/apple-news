<?php
/**
 * Publish to Apple News tests: Apple_News_Admin_Action_Index_Export_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Actions\Index\Export;

/**
 * A class to test the functionality of the Apple_Actions\Index\Export class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Admin_Action_Index_Export_Test extends Apple_News_Testcase {

	/**
	 * Returns an array of arrays representing function arguments to the
	 * test_brightcove_video function.
	 */
	public function data_provider_brightcove_video() {
		$editor_content    = '<!-- wp:bc/brightcove {"account_id":"1234567890","player_id":"abcd1234-ef56-ab78-cd90-efa1234567890","video_id":"1234567890123","playlist_id":"","experience_id":"","video_ids":"","embed":"in-page","autoplay":"","playsinline":"","picture_in_picture":"","height":"100%","width":"100%","min_width":"0px","max_width":"640px","padding_top":"56%"} /-->';
		$shortcode_content = '[bc_video video_id="1234567890123" account_id="1234567890" player_id="abcd1234-ef56-ab78-cd90-efa1234567890" embed="in-page" padding_top="56%" autoplay="" min_width="0px" playsinline="" picture_in_picture="" max_width="640px" mute="" width="100%" height="100%" ]';

		return [
			[ [ 'title' ], $editor_content ],
			[ [ 'title' ], $shortcode_content ],
		];
	}

	/**
	 * A filter to ensure that the is_exporting flag is set during export.
	 *
	 * @return string The filtered content.
	 */
	public function filter_the_content_test_is_exporting() {
		return apple_news_is_exporting() ? 'is exporting' : 'is not exporting';
	}

	/**
	 * Tests Brightcove video support.
	 *
	 * @param string[] $meta_order   The order of meta components to use.
	 * @param string   $post_content The post content to load for the test.
	 *
	 * @dataProvider data_provider_brightcove_video
	 */
	public function test_brightcove_video( $meta_order, $post_content ) {
		$this->set_theme_settings( [ 'meta_component_order' => $meta_order ] );
		$post_id = self::factory()->post->create(
			[
				'post_content' => $post_content,
			]
		);
		$json    = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'video', $json['components'][1]['role'] );
		$this->assertEquals( 'https://edge.api.brightcove.com/playback/v1/accounts/1234567890/videos/1234567890123', $json['components'][1]['URL'] );
		$this->assertEquals( 'https://cf-images.us-east-1.prod.boltdns.net/v1/jit/1234567890/abcd1234-ef56-ab78-cd90-efabcd123456/main/1280x720/1s234ms/match/image.jpg', $json['components'][1]['stillURL'] );
	}

	/**
	 * Tests the ability to include a caption with a cover image.
	 */
	public function test_cover_with_caption() {
		$this->set_theme_settings( [ 'cover_caption' => true ] );

		// Create example post and attachment.
		$post_id = self::factory()->post->create();
		$image   = $this->get_new_attachment( $post_id, 'Test Caption' );

		// Set the image as the featured image for the post.
		set_post_thumbnail( $post_id, $image );

		// Run the export and check the result.
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'photo', $json['components'][0]['components'][0]['role'] );
		$this->assertEquals( wp_get_attachment_url( $image ), $json['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 'Test Caption', $json['components'][0]['components'][0]['caption']['text'] );
		$this->assertEquals( 'caption', $json['components'][0]['components'][1]['role'] );
		$this->assertEquals( 'Test Caption', $json['components'][0]['components'][1]['text'] );

		// Set cover image and caption via postmeta and ensure it takes priority.
		$image2 = $this->get_new_attachment();
		update_post_meta( $post_id, 'apple_news_coverimage', $image2 );
		update_post_meta( $post_id, 'apple_news_coverimage_caption', 'Test Caption 2' );
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'photo', $json['components'][0]['components'][0]['role'] );
		$this->assertEquals( wp_get_attachment_url( $image2 ), $json['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 'Test Caption 2', $json['components'][0]['components'][0]['caption']['text'] );
		$this->assertEquals( 'caption', $json['components'][0]['components'][1]['role'] );
		$this->assertEquals( 'Test Caption 2', $json['components'][0]['components'][1]['text'] );
	}

	/**
	 * Tests the behavior of an export when an excerpt is manually defined.
	 */
	public function test_has_excerpt() {
		$title   = 'My Title';
		$excerpt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue...';

		$post_id = $this->factory->post->create(
			[
				'post_title'   => $title,
				'post_content' => '',
				'post_excerpt' => $excerpt,
			]
		);

		$export           = new Export( $this->settings, $post_id );
		$exporter         = $export->fetch_exporter();
		$exporter_content = $exporter->get_content();

		$this->assertEquals( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue...', $exporter_content->intro() );
	}

	/**
	 * Tests the behavior of an export when no excerpt is defined.
	 */
	public function test_no_excerpt() {
		$title   = 'My Title';
		$content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';

		$post_id = $this->factory->post->create(
			[
				'post_title'   => $title,
				'post_content' => $content,
				'post_excerpt' => '',
			]
		);

		$export           = new Export( $this->settings, $post_id );
		$exporter         = $export->fetch_exporter();
		$exporter_content = $exporter->get_content();

		$this->assertEquals( '', $exporter_content->intro() );
	}

	/**
	 * Tests the behavior of a shortcode in the excerpt.
	 */
	public function test_shortcode_in_excerpt() {
		$title   = 'My Title';
		$content = '<p>[caption id="attachment_12345" align="aligncenter" width="500"]Test[/caption]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';

		$post_id = $this->factory->post->create(
			[
				'post_title'   => $title,
				'post_content' => $content,
				'post_excerpt' => '',
			]
		);

		$export           = new Export( $this->settings, $post_id );
		$exporter         = $export->fetch_exporter();
		$exporter_content = $exporter->get_content();

		$this->assertEquals( '', $exporter_content->intro() );
	}

	/**
	 * Tests generic byline formatting.
	 */
	public function test_byline_format() {
		// Temporarily set the timezone Manaus (UTC-4 and does not have DST).
		$this->with_timezone('America/Manaus', function() {
			$this->set_theme_settings( [ 'meta_component_order' => [ 'byline' ] ] );
			$user_id = $this->factory->user->create(
				[
					'role'         => 'administrator',
					'display_name' => 'Testuser',
				]
			);

			$title   = 'My Title';
			$content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';

			$post_id = $this->factory->post->create(
				[
					'post_title'   => $title,
					'post_content' => $content,
					'post_excerpt' => '',
					'post_author'  => $user_id,
					'post_date_gmt'    => '2016-08-26 16:00', // 4 hours ahead of Manaus time.
				]
			);

			$export           = new Export( $this->settings, $post_id );
			$exporter         = $export->fetch_exporter();
			$exporter_content = $exporter->get_content();

			$this->assertEquals( 'By Testuser | Aug 26, 2016 | 12:00 PM', $exporter_content->byline() );
		});

	}

	/**
	 * Tests byline formatting when a hash is used in the author's display name.
	 */
	public function test_byline_format_with_hashtag() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'byline' ] ] );
		$user_id = $this->factory->user->create(
			[
				'role'         => 'administrator',
				'display_name' => '#Testuser',
			]
		);

		$title   = 'My Title';
		$content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';

		$post_id = $this->factory->post->create(
			[
				'post_title'   => $title,
				'post_content' => $content,
				'post_excerpt' => '',
				'post_author'  => $user_id,
				'post_date'    => '2016-08-26 12:00',
			]
		);

		$export           = new Export( $this->settings, $post_id );
		$exporter         = $export->fetch_exporter();
		$exporter_content = $exporter->get_content();

		$this->assertEquals( 'By #Testuser | Aug 26, 2016 | 12:00 PM', $exporter_content->byline() );
	}

	/**
	 * Tests conversion of HTML entities.
	 */
	public function test_remove_entities() {
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Test Title',
				'post_content' => '<p>&amp;Lorem ipsum dolor sit amet &amp; consectetur adipiscing elit.&amp;</p>',
				'post_date'    => '2016-08-26 12:00',
			]
		);

		// Set HTML content format.
		$this->settings->html_support = 'yes';

		$export           = new Export( $this->settings, $post_id );
		$exporter         = $export->fetch_exporter();
		$exporter_content = $exporter->get_content();

		$this->assertEquals(
			'<p>&amp;Lorem ipsum dolor sit amet &amp; consectetur adipiscing elit.&amp;</p>',
			str_replace( [ "\n", "\r" ], '', $exporter_content->content() )
		);

		// Set Markdown content format.
		$this->settings->html_support = 'no';

		$markdown_export           = new Export( $this->settings, $post_id );
		$markdown_exporter         = $markdown_export->fetch_exporter();
		$markdown_exporter_content = $markdown_exporter->get_content();
		$this->assertEquals(
			'<p>&Lorem ipsum dolor sit amet & consectetur adipiscing elit.&</p>',
			str_replace( [ "\n", "\r" ], '', $markdown_exporter_content->content() )
		);
	}

	/**
	 * Tests the ability to remove selectors from the content.
	 */
	public function test_remove_selectors() {
		$post_id = $this->factory->post->create(
			[
				'post_content' => <<<HTML
<p class="foo">First paragraph</p>
<p class="bar">Second paragraph</p>
<p class="foo bar">Third paragraph</p>
<p class="foobar">Fourth paragraph</p>
<p id="foo">Fifth paragraph</p>
<p id="bar">Sixth paragraph</p>
HTML,
			]
		);

		$this->settings->excluded_selectors = '.foo, #foo';

		$export   = new Export( $this->settings, $post_id );
		$exporter = $export->fetch_exporter();
		$content  = $exporter->get_content()->content();

		$this->assertStringNotContainsString( 'First paragraph', $content );
		$this->assertStringNotContainsString( 'Third paragraph', $content );
		$this->assertStringNotContainsString( 'Fifth paragraph', $content );
	}

	/**
	 * Tests the behavior of the apple_news_is_exporting() function.
	 */
	public function test_is_exporting() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'title' ] ] );

		// Setup.
		$title   = 'My Title';
		$content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';
		$post_id = $this->factory->post->create(
			[
				'post_title'   => $title,
				'post_content' => $content,
			]
		);
		add_filter(
			'the_content',
			[ $this, 'filter_the_content_test_is_exporting' ]
		);

		// Ensure is_exporting returns false before exporting.
		$this->assertEquals(
			'is not exporting',
			apply_filters( 'the_content', 'Lorem ipsum dolor sit amet' ) // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		);

		// Get sections for the post.
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals(
			'<p>is exporting</p>',
			$json['components'][1]['text']
		);

		// Ensure is_exporting returns false after exporting.
		$this->assertEquals(
			'is not exporting',
			apply_filters( 'the_content', 'Lorem ipsum dolor sit amet' ) // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		);

		// Teardown.
		remove_filter(
			'the_content',
			[ $this, 'filter_the_content_test_is_exporting' ]
		);
	}
}
