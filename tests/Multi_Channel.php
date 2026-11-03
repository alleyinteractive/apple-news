<?php
/**
 * Publish to Apple News Tests: Multi_Channel class
 *
 * @package Apple_News
 */

namespace Apple_News\Tests;

use Apple_Actions\Index\Delete;
use Apple_Actions\Index\Get;
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

		update_option(
			'apple_news_channels',
			[
				'secondary' => [
					'label'   => 'Secondary',
					'channel' => 'foo2',
					'key'     => 'bar2',
					'secret'  => 'baz2',
				],
			]
		);
	}

	/**
	 * The plugin registers no per-channel post meta, since articles keep one ID whatever their channel.
	 */
	public function test_no_per_channel_post_meta_is_registered(): void {
		new \Admin_Apple_News();

		foreach ( [ 'apple_news_channel', 'apple_news_api_id_2', 'apple_news_sections_2' ] as $meta_key ) {
			$this->assertFalse( registered_meta_key_exists( 'post', $meta_key, 'post' ), $meta_key );
		}
	}

	/**
	 * Each channel keeps its own cached sections list.
	 */
	public function test_sections_are_cached_per_channel(): void {
		$this->add_california_channel();
		set_transient( 'apple_news_sections', [ (object) [ 'id' => 'primary-section' ] ] );
		$this->add_http_response(
			'GET',
			'https://news-api.apple.com/channels/foo3/sections',
			wp_json_encode( [ 'data' => [ [ 'id' => 'california-section' ] ] ] )
		);

		$sections = \Admin_Apple_Sections::get_sections( 'california' );

		$this->assertSame( 'california-section', $sections[0]->id );
	}

	/**
	 * The automation page offers every configured channel, each with its own sections.
	 */
	public function test_automation_page_lists_channels_from_list(): void {
		$this->add_california_channel();
		set_transient( 'apple_news_sections', [ 'primary-sections' ] );
		set_transient( 'apple_news_sections_secondary', [ 'secondary-sections' ] );
		set_transient( 'apple_news_sections_california', [ 'california-sections' ] );

		ob_start();
		Automation::render_submenu_page();
		ob_end_clean();
		$data = wp_scripts()->get_data( 'apple-news-admin-settings', 'data' );

		$this->assertStringContainsString( '{"value":"california","label":"California"}', $data );
		$this->assertStringContainsString( '"california":["california-sections"]', $data );
		$this->assertStringContainsString( '"secondary":["secondary-sections"]', $data );
	}

	/**
	 * Adds a "california" channel next to the "secondary" one.
	 */
	private function add_california_channel(): void {
		update_option(
			'apple_news_channels',
			array_merge(
				get_option( 'apple_news_channels' ),
				[
					'california' => [
						'label'   => 'California',
						'channel' => 'foo3',
						'key'     => 'bar3',
						'secret'  => 'baz3',
					],
				]
			)
		);
	}

	/**
	 * A channel's label is the one saved with it.
	 */
	public function test_channel_label_comes_from_list(): void {
		update_option(
			'apple_news_channels',
			[
				'california' => [
					'label'   => 'California',
					'channel' => 'foo3',
					'key'     => 'bar3',
					'secret'  => 'baz3',
				],
			]
		);

		$this->assertSame( 'California', Apple_News_Channels::get_channel_label( 'california' ) );
	}

	/**
	 * An API client set from outside is the one the action uses.
	 */
	public function test_action_uses_injected_api(): void {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, 'apple_news_api_id', 'article-1' );
		$api = new class( 'https://news-api.apple.com', new \Apple_Push_API\Credentials( 'k', 's' ) ) extends \Apple_Push_API\API {
			/**
			 * Returns a canned article.
			 *
			 * @param string $article_id The article ID.
			 * @return object
			 */
			public function get_article( $article_id ) {
				return (object) [ 'data' => (object) [ 'id' => $article_id ] ];
			}
		};

		$action = new Get( $this->settings, $post_id );
		$action->set_api( $api );

		$this->assertSame( 'article-1', $action->perform()->data->id );
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
	 * Multi-channel is off on a site with only the primary channel.
	 */
	public function test_multi_channel_is_off_without_channels(): void {
		delete_option( 'apple_news_channels' );

		$this->assertFalse( Apple_News_Channels::is_enabled() );
	}

	/**
	 * Configuring a channel turns multi-channel on.
	 */
	public function test_multi_channel_is_on_with_a_channel(): void {
		$this->assertTrue( Apple_News_Channels::is_enabled() );
	}

	/**
	 * Any channel in the list with complete credentials counts as initialized.
	 */
	public function test_channel_in_list_is_initialized(): void {
		update_option(
			'apple_news_channels',
			[
				'california' => [
					'label'   => 'California',
					'channel' => 'foo3',
					'key'     => 'bar3',
					'secret'  => 'baz3',
				],
			]
		);

		$this->assertTrue( \Apple_News::is_channel_initialized( 'california' ) );
		$this->assertFalse( \Apple_News::is_channel_initialized( 'missing' ) );
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
	 * A push that never sets a channel key goes to the post's channel.
	 */
	public function test_push_without_channel_key_uses_post_channel(): void {
		$post_id = self::factory()->post->create();
		add_filter( 'apple_news_channel_for_post', fn () => Apple_News_Channels::SECONDARY );

		$args = $this->push_to_channel( $post_id, 'foo2' );

		$this->assertStringContainsString( 'key=bar2;', $args['headers']['Authorization'] );
	}

	/**
	 * Publishing a post records which channel its article lives on.
	 */
	public function test_push_records_article_channel(): void {
		$post_id = self::factory()->post->create();

		$this->push_to_channel( $post_id, 'foo2', Apple_News_Channels::SECONDARY );

		$this->assertSame( Apple_News_Channels::SECONDARY, get_post_meta( $post_id, 'apple_news_api_channel', true ) );
	}

	/**
	 * An article isn't updated through a different channel from the one it was published on.
	 */
	public function test_update_is_refused_when_channel_changed(): void {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, 'apple_news_api_id', 'article-1' );
		update_post_meta( $post_id, 'apple_news_api_channel', Apple_News_Channels::PRIMARY );
		add_filter( 'apple_news_channel_for_post', fn () => Apple_News_Channels::SECONDARY );

		$this->expectException( \Apple_Actions\Action_Exception::class );
		$this->expectExceptionMessage( 'was published to the Primary Channel' );

		( new Push( $this->settings, $post_id ) )->perform();
	}

	/**
	 * Deleting a post's article uses the post's channel credentials.
	 */
	public function test_delete_uses_post_channel(): void {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, 'apple_news_api_id', 'article-1' );
		add_filter( 'apple_news_channel_for_post', fn () => Apple_News_Channels::SECONDARY );
		$sent = [];
		add_filter(
			'apple_news_delete_args',
			function ( $args ) use ( &$sent ) {
				$sent = $args;
				return $args;
			}
		);
		$this->add_http_response( 'DELETE', 'https://news-api.apple.com/articles/article-1' );

		( new Delete( $this->settings, $post_id ) )->perform();

		$this->assertStringContainsString( 'key=bar2;', $sent['headers']['Authorization'] );
	}

	/**
	 * Fetching a post's article uses the post's channel credentials.
	 */
	public function test_get_uses_post_channel(): void {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, 'apple_news_api_id', 'article-1' );
		add_filter( 'apple_news_channel_for_post', fn () => Apple_News_Channels::SECONDARY );
		$sent = [];
		add_filter(
			'apple_news_get_args',
			function ( $args ) use ( &$sent ) {
				$sent = $args;
				return $args;
			}
		);
		$this->add_http_response( 'GET', 'https://news-api.apple.com/articles/article-1', wp_json_encode( $this->fake_article_response() ) );

		( new Get( $this->settings, $post_id ) )->perform();

		$this->assertStringContainsString( 'key=bar2;', $sent['headers']['Authorization'] );
	}

	/**
	 * Publishing through the REST API goes to the post's channel, whatever channel the request names.
	 */
	public function test_rest_publish_ignores_requested_channel(): void {
		$this->become_admin();
		$post_id = self::factory()->post->create();
		add_filter( 'apple_news_channel_for_post', fn () => Apple_News_Channels::SECONDARY );
		$this->add_http_response(
			'POST',
			'https://news-api.apple.com/channels/foo2/articles',
			wp_json_encode( $this->fake_article_response() ),
			[],
			[
				'code'    => 201,
				'message' => 'Created',
			]
		);

		$this->post(
			rest_url( '/apple-news/v1/publish' ),
			[
				'id'      => $post_id,
				'channel' => Apple_News_Channels::PRIMARY,
			]
		)->assertOk();

		$this->assertStringContainsString( 'key=bar2;', array_pop( $this->post_args )['headers']['Authorization'] );
	}

	/**
	 * A push to any channel in the list uses that channel's ID and key.
	 */
	public function test_push_uses_credentials_from_channel_list(): void {
		update_option(
			'apple_news_channels',
			[
				'california' => [
					'label'   => 'California',
					'channel' => 'foo3',
					'key'     => 'bar3',
					'secret'  => 'baz3',
				],
			]
		);
		$post_id = self::factory()->post->create();

		$args = $this->push_to_channel( $post_id, 'foo3', 'california' );

		$this->assertStringContainsString( 'key=bar3;', $args['headers']['Authorization'] );
	}

	/**
	 * A channel slug that isn't configured has no credentials.
	 */
	public function test_unknown_channel_has_no_credentials(): void {
		$this->assertSame(
			[
				'channel' => '',
				'key'     => '',
				'secret'  => '',
			],
			Apple_News_Channels::get_credentials( 'missing', $this->settings )
		);
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
