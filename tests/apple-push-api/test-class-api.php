<?php

require_once __DIR__ . '/../../includes/apple-push-api/class-api.php';
require_once __DIR__ . '/../../includes/apple-push-api/class-credentials.php';

use Apple_Push_API\API as API;
use Apple_Push_API\Credentials as Credentials;

class API_Test extends WP_UnitTestCase {

	public function setup() {
		// Whether or not to set requests to debug mode, enabling the use or
		// reverse proxies such as Charles.
		$debug_mode = false;

		$key        = '12345';
		$secret     = '12345';
		$endpoint   = 'https://news-api.apple.com';
		$credentials = new Credentials( $key, $secret );
	}

	public function testInitializeAPI() {
		$this->api   = new API( $this->endpoint, $this->credentials, $this->debug_mode );
	}

}


