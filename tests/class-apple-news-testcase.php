<?php
/**
 * Publish to Apple News tests: Apple_News_Testcase class
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Actions\Action_Exception;

/**
 * A base class for Apple News tests.
 *
 * @package Apple_News
 */
abstract class Apple_News_Testcase extends WP_UnitTestCase {

	/**
	 * Contains an instance of the Apple_Exporter\Builders\Component_Styles class for use in tests.
	 *
	 * @var Apple_Exporter\Builders\Component_Styles
	 */
	protected $component_styles;

	/**
	 * Contains an instance of the Apple_Exporter\Exporter_Content class for use in tests.
	 *
	 * @var Apple_Exporter\Exporter_Content
	 */
	protected $content;

	/**
	 * Contains an instance of the Apple_Exporter\Exporter_Content_Settings class for use in tests.
	 *
	 * @var Apple_Exporter\Exporter_Content_Settings
	 */
	protected $content_settings;

	/**
	 * Contains an array of key-value pairs for responses for given verbs and URLs.
	 *
	 * @var array
	 */
	protected $http_responses = [];

	/**
	 * Contains an instance of the Apple_Exporter\Builders\Component_Layouts class for use in tests.
	 *
	 * @var Apple_Exporter\Builders\Component_Layouts
	 */
	protected $layouts;

	/**
	 * Stores a record of POST arguments sent to the Apple News API for examination in tests.
	 *
	 * @var array
	 */
	protected $post_args = [];

	/**
	 * Contains a Prophecy-wrapped instance of the Apple_Exporter\Workspace class.
	 *
	 * @var Prophecy\Prophecy\ObjectProphecy
	 */
	protected $prophecized_workspace;

	/**
	 * An instance of Prophet for use in tests.
	 *
	 * @var Prophecy\Prophet
	 */
	protected $prophet;

	/**
	 * Contains an instance of the Apple_Exporter\Settings class for use in tests.
	 *
	 * @var Apple_Exporter\Settings
	 */
	protected $settings;

	/**
	 * Contains an instance of the Apple_Exporter\Builders\Component_Text_Styles class for use in tests.
	 *
	 * @var Apple_Exporter\Builders\Component_Text_Styles
	 */
	protected $styles;

	/**
	 * Contains an instance of the Apple_Exporter\Theme class for use in tests.
	 *
	 * @var Apple_Exporter\Theme
	 */
	protected $theme;

	/**
	 * Contains an instance of the Apple_Exporter\Workspace class for use in tests.
	 *
	 * @var Apple_Exporter\Workspace
	 */
	protected $workspace;

	/**
	 * Intercepts and captures arguments for a POST request to the Apple News API.
	 *
	 * @param array $args Arguments to be filtered.
	 *
	 * @return array The args, unmodified.
	 */
	public function filter_apple_news_post_args( $args ) {
		$this->post_args[] = $args;
		return $args;
	}

	/**
	 * Preempts external HTTP requests in a unit test context.
	 *
	 * @param false|array|WP_Error $preempt     A preemptive return value of an HTTP request. Default false.
	 * @param array                $parsed_args HTTP request arguments.
	 * @param string               $url         The request URL.
	 *
	 * @return array|WP_Error An array containing 'headers', 'body', 'response', 'cookies', and 'filename' elements on success, or WP_Error on failure.
	 */
	public function filter_pre_http_request( $preempt, $parsed_args, $url ) {
		$verb = ! empty( $parsed_args['method'] ) ? $parsed_args['method'] : 'GET';
		if ( ! empty( $this->http_responses[ $verb ][ $url ] ) ) {
			return array_shift( $this->http_responses[ $verb ][ $url ] );
		}

		return new WP_Error( __( 'Invalid API request.', 'apple-news' ) );
	}

	/**
	 * A fixture containing operations to be run before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Capture arguments sent to the Apple News API in POST requests.
		add_filter( 'apple_news_post_args', [ $this, 'filter_apple_news_post_args' ] );

		// Prevent external HTTP calls from being made in a test context.
		add_filter( 'pre_http_request', [ $this, 'filter_pre_http_request' ], 10, 3 );

		// Ensure HTML5 image captions are supported.
		add_theme_support( 'html5', [ 'caption' ] );

		// Create some example content and save it for future use.
		$this->content = new Apple_Exporter\Exporter_Content(
			1,
			'My Title',
			'<p>Hello, World!</p>'
		);

		// Create a new instance of the Settings object and save it for future use.
		$this->settings              = new Apple_Exporter\Settings();
		$this->settings->api_channel = 'foo';
		$this->settings->api_key     = 'bar';
		$this->settings->api_secret  = 'baz';

		// Create a new instance of the Exporter_Content_Settings object and save it for future use.
		$this->content_settings = new Apple_Exporter\Exporter_Content_Settings();

		// Create a new instance of Prophet for future use and create a prophecized workspace.
		$this->prophet               = new Prophecy\Prophet();
		$this->prophecized_workspace = $this->prophet->prophesize( '\Apple_Exporter\Workspace' );

		// Load the Default theme from config and save it for future use.
		$this->load_example_theme( 'default' );

		// Create styles for future use.
		$this->styles = new Apple_Exporter\Builders\Component_Text_Styles(
			$this->content,
			$this->content_settings
		);

		// Create layouts for future use.
		$this->layouts = new Apple_Exporter\Builders\Component_Layouts(
			$this->content,
			$this->content_settings
		);

		// Create component styles for future use.
		$this->component_styles = new Apple_Exporter\Builders\Component_Styles(
			$this->content,
			$this->content_settings
		);

		// Create a workspace for future use. Default it to use post ID 1, but this can be overridden at the test level.
		$this->set_workspace_post_id( 1 );

		// Pre-cache a transient for sections using sample data to bypass API call.
		set_transient(
			'apple_news_sections',
			[
				(object) [
					'createdAt'  => '2017-01-01T00:00:00Z',
					'id'         => 'abcdef01-2345-6789-abcd-ef012356789a',
					'isDefault'  => true,
					'links'      => (object) [
						'channel' => 'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef0123567890',
						'self'    => 'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef012356789a',
					],
					'modifiedAt' => '2017-01-01T00:00:00Z',
					'name'       => 'Main',
					'shareUrl'   => 'https://apple.news/AbCdEfGhIj-KlMnOpQrStUv',
					'type'       => 'section',
				],
				(object) [
					'createdAt'  => '2017-01-01T00:00:00Z',
					'id'         => 'abcdef01-2345-6789-abcd-ef012356789b',
					'isDefault'  => false,
					'links'      => (object) [
						'channel' => 'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef0123567890',
						'self'    => 'https://news-api.apple.com/channels/abcdef01-2345-6789-abcd-ef012356789b',
					],
					'modifiedAt' => '2017-01-01T00:00:00Z',
					'name'       => 'Secondary Section',
					'shareUrl'   => 'https://apple.news/AbCdEfGhIj-KlMnOpQrStUw',
					'type'       => 'section',
				],
			]
		);
	}

	/**
	 * A fixture containing operations to be run after each test.
	 */
	public function tearDown(): void {
		$this->prophet->checkPredictions();
		remove_filter( 'apple_news_post_args', [ $this, 'filter_apple_news_post_args' ] );
		remove_filter( 'pre_http_request', [ $this, 'filter_pre_http_request' ] );
		wp_set_current_user( 0 );
	}

	/**
	 * Given an endpoint URL and a response object, adds the response object to
	 * the queue for that URL. Used to fake HTTP responses from the Apple News
	 * API.
	 *
	 * @param string $verb     The HTTP verb to respond to.
	 * @param string $url      The API endpoint to fake the response for.
	 * @param string $body     The faked response body.
	 * @param array  $headers  Optional. Faked response headers. Defaults to empty array.
	 * @param array  $response Optional. Faked response array. Defaults to empty array.
	 * @param array  $cookies  Optional. Faked response cookies. Defaults to empty array.
	 * @param string $filename Optional. Faked uploaded filename. Defaults to null.
	 */
	protected function add_http_response(
		$verb,
		$url,
		$body = '',
		$headers = [],
		$response = [
			'code'    => 200,
			'message' => 'OK',
		],
		$cookies = [],
		$filename = null
	) {
		// Handle null for DELETE.
		$this->http_responses[ $verb ][ $url ][] = 'DELETE' !== $verb
			? [
				'body'     => $body,
				'cookies'  => $cookies,
				'filename' => $filename,
				'headers'  => class_exists( \WpOrg\Requests\Utility\CaseInsensitiveDictionary::class )
					? new \WpOrg\Requests\Utility\CaseInsensitiveDictionary( $headers )
					: new Requests_Utility_CaseInsensitiveDictionary( $headers ),
				'response' => $response,
			] : null;
	}

	/**
	 * Creates a new admin (or super admin, on multisite) and sets the current
	 * user ID to the new user. Useful when testing functionality that requires
	 * an administrator's credentials, such as adding unfiltered HTML to a post.
	 */
	protected function become_admin() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		if ( function_exists( 'grant_super_admin' ) ) {
			grant_super_admin( $user_id );
		}
		wp_set_current_user( $user_id );
	}

	/**
	 * A helper function for removing Co-Authors Plus support in a test context.
	 */
	protected function disable_coauthors_support() {
		remove_filter( 'apple_news_use_coauthors', '__return_true', 99 );
	}

	/**
	 * A helper function for adding Co-Authors Plus support in a test context.
	 */
	protected function enable_coauthors_support() {
		add_filter( 'apple_news_use_coauthors', '__return_true', 99 );
	}

	/**
	 * Creates a fake article response from the API given optional overrides for
	 * data properties.
	 *
	 * @param array $data Optional. Overrides for data properties.
	 *
	 * @return array The fake API response.
	 */
	protected function fake_article_response( $data = [] ) {
		// Build the basic response.
		$response = [
			'data' => wp_parse_args(
				$data,
				[
					'createdAt'                   => '2020-01-02T03:04:05Z',
					'modifiedAt'                  => '2020-01-02T03:04:05Z',
					'id'                          => 'abcd1234-ef56-ab78-cd90-efabcdef123456',
					'type'                        => 'article',
					'shareUrl'                    => 'https://apple.news/ABCDEFGHIJKLMNOPQRSTUVW',
					'links'                       => [
						'channel'  => 'https://news-api.apple.com/channels/' . $this->settings->api_channel,
						'self'     => 'https://news-api.apple.com/articles/abcd1234-ef56-ab78-cd90-efabcdef123456',
						'sections' => [
							'https://news-api.apple.com/sections/abcd1234-ef56-ab78-cd90-efabcdef1234',
						],
					],
					'document'                    => [],
					'revision'                    => 'AAAAAAAAAAAAAAAAAAAAAAAA',
					'state'                       => 'PROCESSING',
					'accessoryText'               => null,
					'title'                       => 'Test Article',
					'maturityRating'              => null,
					'warnings'                    => [],
					'targetTerritoryCountryCodes' => [ 'US' ],
					'isCandidateToBeFeatured'     => false,
					'isSponsored'                 => false,
					'isPreview'                   => false,
					'isDevelopingStory'           => false,
					'isHidden'                    => false,
				]
			),
			'meta' => [
				'throttling' => [
					'isThrottled'             => false,
					'queueSize'               => 0,
					'estimatedDelayInSeconds' => 0,
					'quotaAvailable'          => 200,
				],
			],
		];

		// Apply targeted overrides to links, since wp_parse_args only works on one level.
		if ( isset( $data['links'] ) ) {
			$response['data']['links'] = wp_parse_args( $data['links'], $response['data']['links'] );
		}

		return $response;
	}

	/**
	 * Given a request body from a POST request for an article to the Apple News
	 * API, parses and extracts the article body portion of the request and
	 * returns it as a JSON-decoded associative array.
	 *
	 * @param array $request The request to analyze.
	 *
	 * @return array An associative array representing the article body.
	 */
	protected function get_body_from_request( $request ) {
		preg_match( '/Content-Disposition: form-data; name=my_article; filename=article.json; size=[0-9]+\s+(\{[^\r\n]+)/', $request['body'], $matches );
		return ! empty( $matches[1] ) ? json_decode( $matches[1], true ) : [];
	}

	/**
	 * Given an image ID, returns the HTML5 markup for an image with a caption.
	 *
	 * Extracts the caption from the database entry for the image (stored in post_excerpt).
	 *
	 * @param int $image_id The image ID to use when generating the <figure>.
	 *
	 * @return string HTML for the image and the caption.
	 */
	protected function get_image_with_caption( $image_id ) {
		return img_caption_shortcode(
			[
				'caption' => wp_get_attachment_caption( $image_id ),
				'width'   => 640,
			],
			wp_get_attachment_image( $image_id, 'full' )
		);
	}

	/**
	 * A helper function that generates JSON for a given post ID.
	 *
	 * @param int $post_id The post ID for which to perform the export.
	 *
	 * @return array The JSON for the post, converted to an associative array.
	 */
	protected function get_json_for_post( $post_id ) {
		$export = new Apple_Actions\Index\Export(
			$this->settings,
			$post_id,
			Admin_Apple_Sections::get_sections_for_post( $post_id )
		);

		return json_decode( $export->perform(), true );
	}

	/**
	 * Given a request body from a POST request for an article to the Apple News
	 * API, parses and extracts the metadata portion of the request and returns it
	 * as a JSON-decoded associative array.
	 *
	 * @param array $request The request to analyze.
	 *
	 * @return array An associative array representing the article metadata.
	 */
	protected function get_metadata_from_request( $request ) {
		preg_match( '/Content-Disposition: form-data; name=metadata\s+(\{[^\r\n]+)/', $request['body'], $matches );
		return ! empty( $matches[1] ) ? json_decode( $matches[1], true ) : [];
	}

	/**
	 * Runs create_upload_object using a test image and returns the image ID.
	 *
	 * @param int    $parent  Optional. The parent post ID. Defaults to no parent.
	 * @param string $caption Optional. The caption to set on the image.
	 * @param string $alt     Optional. The alt text to set on the image.
	 *
	 * @return int The post ID of the attachment image that was created.
	 */
	protected function get_new_attachment( $parent = 0, $caption = '', $alt = '' ) {
		$image_id = self::factory()->attachment->create_upload_object( __DIR__ . '/data/test-image.jpg', $parent );

		if ( ! empty( $caption ) ) {
			$image               = get_post( $image_id );
			$image->post_excerpt = $caption;
			wp_update_post( $image );
		}

		if ( ! empty( $alt ) ) {
			update_post_meta( $image_id, '_wp_attachment_image_alt', $alt );
		}

		return $image_id;
	}

	/**
	 * A helper function that performs a sample push operation for a given post ID
	 * and returns the request data that would be sent to Apple.
	 *
	 * @param int   $post_id The post ID for which to perform the push.
	 * @param array $data Optional. Overrides for default faked values in the data.
	 *
	 * @return array The request data for the post.
	 * @throws Action_Exception If the Push action fails.
	 */
	protected function get_request_for_post( $post_id, $data = [] ) {
		// Fake the API response.
		$this->add_http_response(
			'POST',
			'https://news-api.apple.com/channels/' . $this->settings->api_channel . '/articles',
			wp_json_encode(
				$this->fake_article_response(
					wp_parse_args(
						$data,
						[
							'document' => $this->get_json_for_post( $post_id ),
							'title'    => get_the_title( $post_id ),
						]
					)
				)
			),
			[],
			[
				'code'    => 201,
				'message' => 'Created',
			]
		);

		// Perform the push.
		$action = new Apple_Actions\Index\Push( $this->settings, $post_id );
		$action->perform();

		// Return the request arguments sent with the push.
		return ! empty( $this->post_args ) ? array_pop( $this->post_args ) : [];
	}

	/**
	 * A helper function that performs a sample update operation for a given post
	 * ID and returns the request data that would be sent to Apple.
	 *
	 * @param int   $post_id The post ID for which to perform the update.
	 * @param array $data Optional. Overrides for default faked values in the data.
	 *
	 * @return array The request data for the post.
	 * @throws Action_Exception If the Push action fails.
	 */
	protected function get_request_for_update( $post_id, $data = [] ) {
		$article_id = isset( $data['id'] ) ? $data['id'] : 'abcd1234-ef56-ab78-cd90-efabcdef123456';

		// Fake the API response for the GET request that is performed for article data before the update.
		$this->add_http_response(
			'GET',
			'https://news-api.apple.com/articles/' . $article_id,
			wp_json_encode(
				$this->fake_article_response(
					wp_parse_args(
						$data,
						[
							'document' => $this->get_json_for_post( $post_id ),
							'title'    => get_the_title( $post_id ),
						]
					)
				)
			)
		);

		// Fake the API response.
		$this->add_http_response(
			'POST',
			'https://news-api.apple.com/articles/' . $article_id,
			wp_json_encode(
				$this->fake_article_response(
					wp_parse_args(
						$data,
						[
							'document' => $this->get_json_for_post( $post_id ),
							'title'    => get_the_title( $post_id ),
						]
					)
				)
			)
		);

		// Perform the push.
		$action = new Apple_Actions\Index\Push( $this->settings, $post_id );
		$action->perform();

		// Return the request arguments sent with the push.
		return ! empty( $this->post_args ) ? array_pop( $this->post_args ) : [];
	}

	/**
	 * Loads an example theme given a slug.
	 *
	 * @param string $slug The slug of the example theme to load.
	 */
	protected function load_example_theme( $slug ) {
		// Load the theme data from the JSON configuration file.
		$options = json_decode( file_get_contents( dirname( __DIR__ ) . '/assets/themes/' . $slug . '.json' ), true );
		if ( empty( $options ) ) {
			return;
		}

		// Negotiate screenshot URL.
		$options['screenshot_url'] = plugins_url(
			'/assets/screenshots/' . $slug . '.png',
			__DIR__
		);

		// Create a new instance of the Theme class and set the theme name.
		$this->theme = new Apple_Exporter\Theme();
		$this->theme->set_name( $options['theme_name'] );

		// Save the theme.
		$this->theme->load( $options );
		$this->theme->save();

		// Make this theme the active theme.
		$this->theme->set_active();
		$this->theme->use_this();
	}

	/**
	 * Given an array of theme settings, applies them to the currently active theme.
	 *
	 * @param array $settings The settings to apply to the theme.
	 */
	protected function set_theme_settings( $settings ) {
		$settings = wp_parse_args( $settings, $this->theme->all_settings() );
		$this->theme->load( $settings );
		$this->theme->save();
	}

	/**
	 * Sets the workspace post ID to the ID provided.
	 *
	 * @param int $post_id The post ID to set for the workspace.
	 */
	protected function set_workspace_post_id( $post_id ) {
		$this->workspace = new Apple_Exporter\Workspace( $post_id );
	}
}
