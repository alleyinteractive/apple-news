<?php
/**
 * Publish to Apple News Tests: Apple_News_Component_Tests class
 *
 * Contains a class which is used to test \Apple_Exporter\Builders\Components.
 *
 * @package Apple_News
 * @subpackage Tests
 */

use Apple_Exporter\Builders\Components;

/**
 * A class which is used to test the \Apple_Exporter\Builders\Components class.
 */
class Apple_News_Component_Tests extends Apple_News_Testcase {

	/**
	 * A data provider for the full size image URL test.
	 *
	 * @return array
	 */
	public function data_image_full_size_url() {
		return [
			// An image without crops should return itself.
			[
				'https://www.example.org/wp-content/uploads/2020/07/sample-image.jpg',
				'https://www.example.org/wp-content/uploads/2020/07/sample-image.jpg',
			],

			// An image with a crop should return the original image without the crop.
			[
				'https://www.example.org/wp-content/uploads/2020/07/sample-image-150x150.jpg',
				'https://www.example.org/wp-content/uploads/2020/07/sample-image.jpg',
			],

			// Scaled images should return the un-scaled version.
			[
				'https://www.example.org/wp-content/uploads/2020/07/sample-image-scaled.jpg',
				'https://www.example.org/wp-content/uploads/2020/07/sample-image.jpg',
			],

			// Rotated images should return the un-rotated version.
			[
				'https://www.example.org/wp-content/uploads/2020/07/sample-image-rotated.jpg',
				'https://www.example.org/wp-content/uploads/2020/07/sample-image.jpg',
			],

			// Photon images should return the original.
			[
				'https://i1.wp.com/www.example.org/wp-content/uploads/2020/07/sample-image.jpg?w=234&crop=0%2C5px%2C100%2C134px&ssl=1',
				'https://www.example.org/wp-content/uploads/2020/07/sample-image.jpg',
			],
		];
	}

	/**
	 * A data provider for the meta component ordering test.
	 *
	 * @see self::test_meta_component_ordering()
	 *
	 * @return array An array of arguments to pass to the test function.
	 */
	public function data_meta_component_ordering() {
		return [
			[
				[ 'cover', 'title', 'byline' ],
				[ 'header', 'container' ],
				[ 'title', 'byline' ],
			],
			[
				[ 'byline', 'cover', 'title' ],
				[ 'byline', 'header', 'container' ],
				[ 'title' ],
			],
			[
				[ 'title', 'byline' ],
				[ 'title', 'byline' ],
				[],
			],
			[
				[ 'cover', 'byline' ],
				[ 'header', 'container' ],
				[ 'byline' ],
			],
			[
				[ 'cover', 'title' ],
				[ 'header', 'container' ],
				[ 'title' ],
			],
		];
	}

	/**
	 * Tests the ability to view captions below cover images.
	 */
	public function test_cover_image() {
		// Enable the cover caption option in the theme.
		$this->set_theme_settings( [ 'cover_caption' => true ] );

		// Create a new post and set an image with a caption as the featured image.
		$post_id = self::factory()->post->create();
		$image   = $this->get_new_attachment( $post_id, 'Test Caption', 'Test alt text' );
		set_post_thumbnail( $post_id, $image );

		// Ensure that the caption carries through to the export.
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'caption', $json['components'][0]['components'][1]['role'] );
		$this->assertEquals( 'Test Caption', $json['components'][0]['components'][1]['text'] );

		// Create a new post with an image with a caption in the content.
		$image_2   = $this->get_new_attachment( 0, 'Test Caption 2', 'Test alt text 2' );
		$post_id_2 = self::factory()->post->create( [ 'post_content' => $this->get_image_with_caption( $image_2 ) ] );

		// Ensure that the caption carries through to the export.
		$json_2 = $this->get_json_for_post( $post_id_2 );
		$this->assertEquals( 'caption', $json_2['components'][0]['components'][1]['role'] );
		$this->assertEquals( 'Test Caption 2', $json_2['components'][0]['components'][1]['text'] );
	}

	/**
	 * Tests the image deduping functionality of the Components class.
	 *
	 * Ensures that a featured image with the same source URL (minus any crops)
	 * as the first image in the post does not result in the same image
	 * appearing twice in a row. This is accomplished by ignoring the featured
	 * image and instead extracting the first image from the post to use as the
	 * cover image.
	 */
	public function test_featured_image_deduping() {
		$this->set_theme_settings(
			[
				'cover_caption'        => true,
				'meta_component_order' => [ 'cover', 'slug', 'title', 'byline' ],
			]
		);

		// Get two images.
		$image_1 = $this->get_new_attachment();
		$image_2 = $this->get_new_attachment();

		/*
		 * Scenario 1:
		 * - No featured image is set.
		 * - No images in the content.
		 * Expected: No cover image is set.
		 */
		$post_1 = self::factory()->post->create();
		$json_1 = $this->get_json_for_post( $post_1 );
		$this->assertNotEquals( 'headerPhotoLayout', $json_1['components'][0]['layout'] );

		/*
		 * Scenario 2:
		 * - A featured image is set.
		 * - No images in the content.
		 * Expected: The featured image is set as the cover image.
		 */
		$post_2 = self::factory()->post->create();
		set_post_thumbnail( $post_2, $image_1 );
		$json_2 = $this->get_json_for_post( $post_2 );
		$this->assertEquals( 'header', $json_2['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_2['components'][0]['layout'] );
		$this->assertEquals( 'photo', $json_2['components'][0]['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_2['components'][0]['components'][0]['layout'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_1, 'full' ), $json_2['components'][0]['components'][0]['URL'] );

		/*
		 * Scenario 3:
		 * - A featured image is set.
		 * - Image in the content, but not the same one as the featured image.
		 * Expected: The featured image is set as the cover image and the body image is still in the body.
		 */
		$post_3 = self::factory()->post->create( [ 'post_content' => wp_get_attachment_image( $image_2, 'full' ) ] );
		set_post_thumbnail( $post_3, $image_1 );
		$json_3 = $this->get_json_for_post( $post_3 );
		$this->assertEquals( 'header', $json_3['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_3['components'][0]['layout'] );
		$this->assertEquals( 'photo', $json_3['components'][0]['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_3['components'][0]['components'][0]['layout'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_1, 'full' ), $json_3['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 'photo', $json_3['components'][1]['components'][2]['role'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_2, 'full' ), $json_3['components'][1]['components'][2]['URL'] );

		/*
		 * Scenario 4:
		 * - A featured image is set.
		 * - Images in the content, including the same one as the featured image, but the featured image is not first.
		 * Expected: The featured image is set as the cover image and the body image is still in the body.
		 */
		$post_4 = self::factory()->post->create( [ 'post_content' => wp_get_attachment_image( $image_2, 'full' ) . wp_get_attachment_image( $image_1, 'full' ) ] );
		set_post_thumbnail( $post_4, $image_1 );
		$json_4 = $this->get_json_for_post( $post_4 );
		$this->assertEquals( 'header', $json_4['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_4['components'][0]['layout'] );
		$this->assertEquals( 'photo', $json_4['components'][0]['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_4['components'][0]['components'][0]['layout'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_1, 'full' ), $json_4['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 'photo', $json_4['components'][1]['components'][2]['role'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_2, 'full' ), $json_4['components'][1]['components'][2]['URL'] );
		$this->assertEquals( 'photo', $json_4['components'][1]['components'][3]['role'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_1, 'full' ), $json_4['components'][1]['components'][3]['URL'] );

		/*
		 * Scenario 5:
		 * - A featured image is set.
		 * - Images in the content, including the same one as the featured image, and the featured image is first.
		 * Expected: The first image from the content is set as the cover image and the first image from the content has been removed. The featured image is ignored.
		 */
		$post_5 = self::factory()->post->create( [ 'post_content' => wp_get_attachment_image( $image_1, 'full' ) . wp_get_attachment_image( $image_2, 'full' ) ] );
		set_post_thumbnail( $post_5, $image_1 );
		$json_5 = $this->get_json_for_post( $post_5 );
		$this->assertEquals( 'header', $json_5['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_5['components'][0]['layout'] );
		$this->assertEquals( 'photo', $json_5['components'][0]['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_5['components'][0]['components'][0]['layout'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_1, 'full' ), $json_5['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 'photo', $json_5['components'][1]['components'][2]['role'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_2, 'full' ), $json_5['components'][1]['components'][2]['URL'] );
		$this->assertEquals( 3, count( $json_5['components'][1]['components'] ) );

		/*
		 * Scenario 6:
		 * - No featured image is set.
		 * - Images in the content.
		 * Expected: The first image from the content is set as the cover image and the first image from the content has been removed.
		 */
		$post_6 = self::factory()->post->create( [ 'post_content' => wp_get_attachment_image( $image_1, 'full' ) . wp_get_attachment_image( $image_2, 'full' ) ] );
		$json_6 = $this->get_json_for_post( $post_6 );
		$this->assertEquals( 'header', $json_6['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_6['components'][0]['layout'] );
		$this->assertEquals( 'photo', $json_6['components'][0]['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_6['components'][0]['components'][0]['layout'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_1, 'full' ), $json_6['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 'photo', $json_6['components'][1]['components'][2]['role'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_2, 'full' ), $json_6['components'][1]['components'][2]['URL'] );
		$this->assertEquals( 3, count( $json_6['components'][1]['components'] ) );

		/*
		 * Scenario 7:
		 * - No featured image is set.
		 * - Images in the content.
		 * - Cover image set via postmeta.
		 * Expected: The cover image is used from postmeta and the first image from the content is removed.
		 */
		$post_7 = self::factory()->post->create( [ 'post_content' => wp_get_attachment_image( $image_1, 'full' ) . wp_get_attachment_image( $image_2, 'full' ) ] );
		add_post_meta( $post_7, 'apple_news_coverimage', $image_1 );
		$json_7 = $this->get_json_for_post( $post_7 );
		$this->assertEquals( 'header', $json_7['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_7['components'][0]['layout'] );
		$this->assertEquals( 'photo', $json_7['components'][0]['components'][0]['role'] );
		$this->assertEquals( 'headerPhotoLayout', $json_7['components'][0]['components'][0]['layout'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_1, 'full' ), $json_7['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 'photo', $json_7['components'][1]['components'][2]['role'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_2, 'full' ), $json_7['components'][1]['components'][2]['URL'] );
		$this->assertEquals( 3, count( $json_7['components'][1]['components'] ) );
	}

	/**
	 * Tests the functionality of the get_image_full_size_url function.
	 *
	 * @dataProvider data_image_full_size_url
	 *
	 * @param string $original The original URL to test.
	 * @param string $expected The expected result.
	 *
	 * @throws ReflectionException If the reflection fails.
	 */
	public function test_get_image_full_size_url( $original, $expected ) {
		$class  = new ReflectionClass( 'Apple_Exporter\Builders\Components' );
		$method = $class->getMethod( 'get_image_full_size_url' );
		$method->setAccessible( true );
		$builder = new Components( $this->content, $this->content_settings );
		$this->assertEquals( $expected, $method->invokeArgs( $builder, [ $original ] ) );
	}

	/**
	 * Tests the functionality of the maybe_bundle_source function.
	 */
	public function test_image_bundling() {
		// Ensure remote images are turned off for this test.
		$use_remote_images                 = $this->settings->use_remote_images;
		$this->settings->use_remote_images = 'no';

		// Make a post with multiple images with the same filename.
		$post_content = <<<HTML
<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="https://www.example.org/wp-content/2021/12/filename.jpg" alt="Sample Image 1"/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="https://www.example.org/wp-content/2021/11/filename.jpg" alt="Sample Image 2"/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="https://www.example.org/wp-content/2021/10/filename.jpg" alt="Sample Image 3"/></figure>
<!-- /wp:image -->
HTML;
		$post_id      = self::factory()->post->create( [ 'post_content' => $post_content ] );
		$image        = $this->get_new_attachment( $post_id );
		set_post_thumbnail( $post_id, $image );
		$json = $this->get_json_for_post( $post_id );

		// Reset the use remote images setting.
		$this->settings->use_remote_images = $use_remote_images;

		// Ensure the images are saved with different bundle filenames.
		$this->assertEquals( 'bundle://filename.jpg', $json['components'][1]['components'][3]['URL'] );
		$this->assertEquals( 'bundle://filename-1.jpg', $json['components'][1]['components'][4]['URL'] );
		$this->assertEquals( 'bundle://filename-2.jpg', $json['components'][1]['components'][5]['URL'] );
	}

	/**
	 * Ensures that the specified component order is respected.
	 *
	 * @dataProvider data_meta_component_ordering
	 *
	 * @param array $order The meta component order setting to use.
	 * @param array $expected The expected component order after compilation.
	 * @param array $components The expected container components, in order.
	 */
	public function test_meta_component_ordering( $order, $expected, $components ) {
		$this->set_theme_settings(
			[
				'enable_advertisement' => 'no',
				'meta_component_order' => $order,
			]
		);

		// Make a post with a featured image and get the JSON for it.
		$post_id = self::factory()->post->create();
		$image   = $this->get_new_attachment( $post_id );
		set_post_thumbnail( $post_id, $image );
		$json = $this->get_json_for_post( $post_id );

		// Test.
		$expected_total = count( $expected );
		for ( $i = 0; $i < $expected_total; $i++ ) {
			$this->assertEquals( $expected[ $i ], $json['components'][ $i ]['role'] );
			if ( 'container' === $json['components'][ $i ]['role'] ) {
				$components_total = count( $components );
				for ( $j = 0; $j < $components_total; $j++ ) {
					$this->assertEquals(
						$components[ $j ],
						$json['components'][ $i ]['components'][ $j ]['role']
					);
				}
			}
		}
	}
}
