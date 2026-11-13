<?php
/**
 * Publish to Apple News Tests: Admin_Apple_JSON class
 *
 * Contains a class which is used to test Admin_Apple_JSON.
 *
 * @package Apple_News
 * @subpackage Tests
 */

namespace Apple_News\Tests\Admin;

use Apple_Exporter\Theme;
use Apple_News\Tests\TestCase;
use Mantle\Testing\Exceptions\WP_Die_Exception;

/**
 * A class to test the Admin_Apple_JSON class.
 */
class Admin_Apple_JSON extends TestCase {

	/**
	 * A valid custom spec used by the tests.
	 *
	 * @var string
	 */
	private const JSON = '{"role":"body","text":"#text#","format":"html","anchor":"abc123"}';

	/**
	 * Creates the default theme and posts a save JSON request to the router.
	 */
	private function route_save_json(): void {
		$settings = new \Admin_Apple_Settings();
		$settings->save_settings( $this->settings->all() );
		$theme = new Theme();
		$theme->set_name( Theme::get_active_theme_name() );
		if ( ! $theme->load() ) {
			$theme->save();
		}

		$nonce = wp_create_nonce( 'apple_news_json' );
		/* phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
		$_POST['apple_news_theme']     = Theme::get_active_theme_name();
		$_POST['apple_news_component'] = 'Body';
		$_POST['apple_news_action']    = 'apple_news_save_json';
		$_POST['apple_news_json_json'] = self::JSON;
		$_POST['page']                 = 'apple-news-json';
		$_POST['redirect']             = false;
		$_REQUEST['_wp_http_referer']  = '/wp-admin/admin.php?page=apple-news-json';
		$_REQUEST['_wpnonce']          = $nonce;
		$_REQUEST['apple_news_action'] = $_POST['apple_news_action'];
		/* phpcs:enable */

		$admin_json = new \Admin_Apple_JSON();
		$admin_json->action_router();
	}

	/**
	 * Returns the stored body JSON template of the active theme.
	 *
	 * @return mixed
	 */
	private function stored_body_json() {
		$theme = new Theme();
		$theme->set_name( Theme::get_active_theme_name() );
		$theme->load();
		$settings = $theme->all_settings();

		return $settings['json_templates']['body']['json'] ?? null;
	}

	/**
	 * Ensure that a user without the settings capability cannot save JSON.
	 */
	public function test_save_json_denied_without_capability() {
		$editor = $this->create_user_with_role( 'editor' );
		wp_set_current_user( $editor->ID );

		$died = false;
		ob_start();
		try {
			$this->route_save_json();
		} catch ( WP_Die_Exception $e ) {
			$died = true;
		}
		ob_end_clean();

		$this->assertTrue( $died, 'Expected wp_die to be triggered.' );
		$this->assertNotEquals( json_decode( self::JSON, true ), $this->stored_body_json() );
	}

	/**
	 * Ensure that the settings capability filter is honored by the router.
	 */
	public function test_save_json_allowed_with_filtered_capability() {
		$editor = $this->create_user_with_role( 'editor' );
		wp_set_current_user( $editor->ID );
		add_filter(
			'apple_news_settings_capability',
			function () {
				return 'edit_posts';
			}
		);

		$this->route_save_json();

		$this->assertEquals( json_decode( self::JSON, true ), $this->stored_body_json() );
	}

	/**
	 * Ensure that an administrator can still save JSON.
	 */
	public function test_save_json_allowed_for_administrator() {
		$this->become_admin();

		$this->route_save_json();

		$this->assertEquals( json_decode( self::JSON, true ), $this->stored_body_json() );
	}
}
