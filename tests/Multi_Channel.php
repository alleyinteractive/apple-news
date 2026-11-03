<?php
/**
 * Publish to Apple News Tests: Multi_Channel class
 *
 * @package Apple_News
 */

namespace Apple_News\Tests;

use Apple_Actions\Index\Push;
use Apple_News\Admin\Automation;
use Apple_News_Channels;

/**
 * Pins how publishing to more than one channel behaves.
 */
class Multi_Channel extends TestCase {
	/**
	 * Adds credentials for a second channel to the test settings.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->settings->api_channel_2 = 'foo2';
		$this->settings->api_key_2     = 'bar2';
		$this->settings->api_secret_2  = 'baz2';
	}

	/**
	 * Fakes a successful article creation on the given channel and runs the push.
	 *
	 * @param int         $post_id     The post to push.
	 * @param string      $channel_id  The Apple channel ID the request is expected to target.
	 * @param string|null $channel_key The channel key to set on the action, or null to leave the default.
	 *
	 * @return array The arguments of the request sent to Apple.
	 */
	private function push_to_channel( int $post_id, string $channel_id, ?string $channel_key = null ): array {
		$this->add_http_response(
			'POST',
			'https://news-api.apple.com/channels/' . $channel_id . '/articles',
			wp_json_encode( $this->fake_article_response( [ 'title' => get_the_title( $post_id ) ] ) ),
			[],
			[
				'code'    => 201,
				'message' => 'Created',
			]
		);

		$action = new Push( $this->settings, $post_id );
		if ( null !== $channel_key ) {
			$action->set_channel_key( $channel_key );
		}
		$action->perform();

		return array_pop( $this->post_args );
	}

	/**
	 * Posts go to the primary channel unless a filter says otherwise.
	 */
	public function test_channel_for_post_defaults_to_primary(): void {
		$post_id = self::factory()->post->create();

		$this->assertSame( Apple_News_Channels::PRIMARY, Apple_News_Channels::get_channel_for_post( $post_id ) );
	}

	/**
	 * The apple_news_channel_for_post filter decides a post's channel.
	 */
	public function test_channel_for_post_follows_filter(): void {
		$post_id = self::factory()->post->create();
		add_filter( 'apple_news_channel_for_post', fn () => Apple_News_Channels::SECONDARY );

		$this->assertSame( Apple_News_Channels::SECONDARY, Apple_News_Channels::get_channel_for_post( $post_id ) );
	}

	/**
	 * A push with the secondary channel key uses the secondary channel ID and key.
	 */
	public function test_push_with_secondary_key_uses_secondary_credentials(): void {
		$post_id = self::factory()->post->create();

		$args = $this->push_to_channel( $post_id, 'foo2', Apple_News_Channels::SECONDARY );

		$this->assertStringContainsString( 'key=bar2;', $args['headers']['Authorization'] );
	}

	/**
	 * A push that never sets a channel key uses primary, even when the post resolves to secondary.
	 */
	public function test_push_without_channel_key_uses_primary(): void {
		$post_id = self::factory()->post->create();
		add_filter( 'apple_news_channel_for_post', fn () => Apple_News_Channels::SECONDARY );

		$args = $this->push_to_channel( $post_id, 'foo' );

		$this->assertStringContainsString( 'key=bar;', $args['headers']['Authorization'] );
	}

	/**
	 * Automation rules apply only to posts on the rule's channel.
	 */
	public function test_automation_rule_applies_only_on_its_channel(): void {
		$term    = wp_insert_term( 'Channel Test', 'category' );
		$post_id = self::factory()->post->create( [ 'post_category' => [ $term['term_id'] ] ] );
		update_option(
			'apple_news_automation',
			[
				[
					'channel'  => Apple_News_Channels::SECONDARY,
					'field'    => 'isHidden',
					'taxonomy' => 'category',
					'term_id'  => $term['term_id'],
					'value'    => 'true',
				],
			]
		);

		$this->assertSame( [], Automation::get_automation_for_post( $post_id ) );

		add_filter( 'apple_news_channel_for_post', fn () => Apple_News_Channels::SECONDARY );
		$this->assertCount( 1, Automation::get_automation_for_post( $post_id ) );
	}

	/**
	 * Rules saved before channels existed apply to the primary channel.
	 */
	public function test_automation_rule_without_channel_applies_to_primary(): void {
		$term    = wp_insert_term( 'Legacy Rule', 'category' );
		$post_id = self::factory()->post->create( [ 'post_category' => [ $term['term_id'] ] ] );
		update_option(
			'apple_news_automation',
			[
				[
					'field'    => 'isHidden',
					'taxonomy' => 'category',
					'term_id'  => $term['term_id'],
					'value'    => 'true',
				],
			]
		);

		$this->assertCount( 1, Automation::get_automation_for_post( $post_id ) );
	}
}
