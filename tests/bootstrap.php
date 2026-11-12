<?php
/**
 * Publish to Apple News Tests: Bootstrap File
 *
 * @package Apple_News
 * @subpackage Tests
 */

/* phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingVariable */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

use function Mantle\Testing\manager;
use function Mantle\Testing\tests_add_filter;

/*
 * Unless otherwise specified, set WP_CORE_DIR to a directory specific to this project in the user's home directory.
 */
if ( ! getenv( 'WP_CORE_DIR' ) ) {
	putenv( sprintf( 'WP_CORE_DIR=%s/.cache/wordpress-apple-news', getenv( 'HOME' ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
}

$apple_news_install = (string) getenv( 'WP_CORE_DIR' );

// Disable CAP by default - make it opt-in in tests.
tests_add_filter( 'apple_news_use_coauthors', '__return_false' );

// Filter the list of allowed protocols to allow Apple News-specific ones.
tests_add_filter(
	'kses_allowed_protocols',
	function ( $protocols ) {
		return array_merge(
			(array) $protocols,
			[
				'music',
				'musics',
				'stocks',
			]
		);
	}
);

/*
 * Mantle assumes a checkout under a `wp-content` directory is already inside the WordPress install it should test
 * against, so it skips the rsync and the SQLite setup and uses that site's MySQL database instead. This checkout can
 * live inside a local site's `wp-content/plugins` directory, so always copy it into an isolated install at
 * WP_CORE_DIR and re-run PHPUnit from there. The copy is inside that install, which is how the re-run knows not to
 * copy again.
 */
$apple_news_manager = manager()->with_default_exclusions();

// The system temp directory can be a symlink (macOS), and __DIR__ is always resolved.
if ( ! str_starts_with( __DIR__, ( realpath( $apple_news_install ) ?: $apple_news_install ) . '/' ) ) {
	// Mirrors what Mantle's with_sqlite() sets up outside of an existing install; phpunit.xml requests SQLite.
	putenv( 'WP_SKIP_DB_CREATE=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

	$apple_news_manager->rsync( 'plugins/publish-to-apple-news', dirname( __DIR__ ) );
}

/**
 * Install WordPress and load the plugin.
 */
$apple_news_manager
	->with_option( 'home', 'https://www.example.org' )
	->with_option( 'permalink_structure', '/%postname%' )
	->with_option( 'siteurl', 'https://www.example.org' )
	->with_option(
		'apple_news_settings',
		[
			'api_channel' => 'foo',
			'api_key'     => 'bar',
			'api_secret'  => 'baz',
		]
	)
	->loaded(
		function () {
			// Disable VIP cache manager when testing against VIP Go integration.
			if ( method_exists( 'WPCOM_VIP_Cache_Manager', 'instance' ) ) {
				remove_action( 'init', [ WPCOM_VIP_Cache_Manager::instance(), 'init' ] );
			}

			// Force WP to treat URLs as HTTPS during testing so the home and siteurl option protocols are honored.
			$_SERVER['HTTPS'] = 1;

			// Load mocks for integration tests.
			require_once __DIR__ . '/mocks/class-bc-setup.php';
			if ( ! function_exists( 'coauthors' ) ) {
				require_once __DIR__ . '/mocks/function-coauthors.php';
			}

			// Activate mocked Brightcove functionality.
			$bc_setup = new BC_Setup();
			$bc_setup->action_init();

			// Pre-populate the channel transient to prevent Apple News from making a request to the API for channel data.
			$channel_api_response = <<<JSON
{
  "data": {
    "createdAt": "1970-01-01T00:00:00Z",
    "modifiedAt": "1970-01-01T00:00:00Z",
    "id": "abcdef12-3456-7890-abcd-ef1234567890",
    "type": "channel",
    "shareUrl": "https:\/\/apple.news\/TESTAPPLENEWSCHANNELXYZ",
    "links": {
      "defaultSection": "https:\/\/news-api.apple.com\/sections\/abcdef12-3456-7890-abcd-ef1234567890",
      "self": "https:\/\/news-api.apple.com\/channels\/abcdef12-3456-7890-abcd-ef1234567890"
    },
    "name": "Apple News Test Channel",
    "website": "https:\/\/github.com/alleyinteractive/apple-news",
    "fonts": []
  }
}
JSON;
			set_transient( 'apple_news_channel', wp_json_encode( $channel_api_response ) );

			// Load the plugin.
			require dirname( __DIR__ ) . '/apple-news.php';
		}
	)->install();
