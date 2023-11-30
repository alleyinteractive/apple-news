<?php
/**
 * Publish to Apple News Tests: Bootstrap File
 *
 * @package Apple_News
 * @subpackage Tests
 */

/* phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingVariable */

/**
 * Includes a PHP file if it exists.
 *
 * @param string $file The path to the PHP file to include.
 *
 * @return void
 * @throws Exception If the file does not exist.
 */
function apple_news_require_file( string $file ) {
	if ( ! file_exists( $file ) ) {
		throw new Exception( 'File not found: ' . esc_html( $file ) );
	}
	require_once $file;
}

// Autoloading for prophecy.
apple_news_require_file( dirname( __DIR__, 1 ) . '/vendor/autoload.php' );

/**
 * Install WordPress and load the plugin.
 */
\Mantle\Testing\manager()
	->maybe_rsync_plugin()
	->loaded(
		function () {
			// Disable VIP cache manager when testing against VIP Go integration.
			if ( method_exists( 'WPCOM_VIP_Cache_Manager', 'instance' ) ) {
				remove_action( 'init', [ WPCOM_VIP_Cache_Manager::instance(), 'init' ] );
			}

			// Set the permalink structure and domain options.
			update_option( 'home', 'https://www.example.org' );
			update_option( 'permalink_structure', '/%postname%' );
			update_option( 'siteurl', 'https://www.example.org' );

			// Apple News reads in the channel/key/secret values on load.
			update_option(
				'apple_news_settings',
				[
					'api_channel' => 'foo',
					'api_key'     => 'bar',
					'api_secret'  => 'baz',
				]
			);

			// Force WP to treat URLs as HTTPS during testing so the home and siteurl option protocols are honored.
			$_SERVER['HTTPS'] = 1;

			// Load mocks for integration tests.
			apple_news_require_file( __DIR__ . '/mocks/class-bc-setup.php' );
			if ( ! function_exists( 'coauthors' ) ) {
				apple_news_require_file( __DIR__ . '/mocks/function-coauthors.php' );
			}

			// Activate mocked Brightcove functionality.
			$bc_setup = new BC_Setup();
			$bc_setup->action_init();


			// Disable CAP by default - make it opt-in in tests.
			add_filter( 'apple_news_use_coauthors', '__return_false' );

			// Filter the list of allowed protocols to allow Apple News-specific ones.
			add_filter(
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

			// Load the plugin.
			require dirname( __DIR__, 1 ) . '/apple-news.php';
		}
	)->install();

apple_news_require_file( __DIR__ . '/class-apple-news-testcase.php' );
apple_news_require_file( __DIR__ . '/apple-exporter/components/class-component-testcase.php' );
