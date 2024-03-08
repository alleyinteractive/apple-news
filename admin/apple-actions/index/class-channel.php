<?php
/**
 * Publish to Apple News: \Apple_Actions\Index\Channel class
 *
 * @package Apple_News
 */

namespace Apple_Actions\Index;

require_once plugin_dir_path( __FILE__ ) . '../class-api-action.php';

use Apple_Actions\API_Action;

/**
 * A class to handle a channel request from the admin.
 *
 * @package Apple_News
 */
class Channel extends API_Action {
	/**
	 * Get the channel data from Apple News.
	 *
	 * @return object An object containing the response from the API.
	 */
	public function perform() {
		return $this->get_api()->get_channel( $this->get_setting( 'api_channel' ) );
	}
}
