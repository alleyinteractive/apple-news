<?php
/**
 * Apple News Tests Mocks: BC_CMS_API class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/* phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound */

/**
 * A mock for the BC_CMS_API class from the Brightcove Video Connect plugin.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class BC_CMS_API {
	/**
	 * Mocks the response from the Brightcove API for a single video's images.
	 *
	 * @param string $video_id Not used. The ID of the requested video. This is used in the actual function.
	 *
	 * @return array Array of the video's images retrieved.
	 */
	public function video_get_images( $video_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return [
			'poster'    => [
				'src'     => 'https://cf-images.us-east-1.prod.boltdns.net/v1/jit/1234567890/abcd1234-ef56-ab78-cd90-efabcd123456/main/1280x720/1s234ms/match/image.jpg',
				'sources' => [
					[
						'src'    => 'https://cf-images.us-east-1.prod.boltdns.net/v1/jit/1234567890/abcd1234-ef56-ab78-cd90-efabcd123456/main/1280x720/1s234ms/match/image.jpg',
						'height' => 720,
						'width'  => 1280,
					],
				],
			],
			'thumbnail' => [
				'src'     => 'https://cf-images.us-east-1.prod.boltdns.net/v1/jit/1234567890/abcd1234-ef56-ab78-cd90-efabcd123456/main/1690x90/1s234ms/match/image.jpg',
				'sources' => [
					[
						'src'    => 'https://cf-images.us-east-1.prod.boltdns.net/v1/jit/1234567890/abcd1234-ef56-ab78-cd90-efabcd123456/main/160x90/1s234ms/match/image.jpg',
						'height' => 90,
						'width'  => 160,
					],
				],
			],
		];
	}
}
