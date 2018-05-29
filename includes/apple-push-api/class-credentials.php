<?php
/**
 * Publish to Apple News: \Apple_Push_API\Credentials class
 *
 * @package Apple_News
 * @subpackage Apple_Push_API
 */

namespace Apple_Push_API;

/**
 * A class to manage API credentials.
 *
 * @package Apple_News
 * @subpackage Apple_Push_API
 */
class Credentials {

	/**
	 * The key used in the authentication process, this is provided as part of
	 * the API credentials and should be safely stored in the server, do not
	 * hard-code it in the source code.
	 *
	 * @var string
	 * @since 0.2.0
	 * @access private
	 */
	private $key;

	/**
	 * The secret used in the authentication process, this is provided as part of
	 * the API credentials and should be safely stored in the server, do not
	 * hard-code it in the source code.
	 *
	 * @var string
	 * @since 0.2.0
	 * @access private
	 */
	private $secret;


	/**
	 * Constructor.
	 *
	 * @param string $key    The API key.
	 * @param string $secret The API secret.
	 * @access public
	 */
	public function __construct( $key, $secret ) {
		$this->secret = $secret;
		$this->key    = $key;
	}

	/**
	 * Get the key.
	 *
	 * @return string
	 * @access public
	 */
	public function key() {
		return $this->key;
	}

	/**
	 * Get the secret.
	 *
	 * @return string
	 * @access public
	 */
	public function secret() {
		return $this->secret;
	}

}
