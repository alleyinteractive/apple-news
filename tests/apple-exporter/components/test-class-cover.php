<?php
/**
 * Publish to Apple News tests: Cover_Test class
 *
 * @package Apple_News
 * @subpackage Tests
 */

/**
 * A class to test the behavior of the Apple_Exporter\Components\Cover class.
 *
 * @package Apple_News
 * @subpackage Tests
 */
class Apple_News_Cover_Test extends Apple_News_Testcase {

	/**
	 * A filter function to modify the text style in the generated JSON.
	 *
	 * @param array $json The JSON array to modify.
	 *
	 * @return array The modified JSON.
	 */
	public function filter_apple_news_cover_json( $json ) {
		$json['layout'] = 'fancy-layout';

		return $json;
	}

	/**
	 * Filters the attachment URL to remove the domain name and make the path root-relative.
	 *
	 * @param string $url The URL to filter.
	 *
	 * @return string The filtered URL, made root-relative.
	 */
	public function filter_wp_get_attachment_url( $url ) {
		return str_replace( home_url(), '', $url );
	}

	/**
	 * Test the `apple_news_cover_json` filter.
	 */
	public function test_filter() {
		$this->set_theme_settings( [ 'meta_component_order' => [ 'cover' ] ] );
		add_filter( 'apple_news_cover_json', [ $this, 'filter_apple_news_cover_json' ] );

		// Create a test post and get JSON for it.
		$post_id  = self::factory()->post->create();
		$image_id = $this->get_new_attachment( $post_id, 'Test Caption', 'Test alt text.' );
		set_post_thumbnail( $post_id, $image_id );
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'header', $json['components'][0]['role'] );
		$this->assertEquals( 'fancy-layout', $json['components'][0]['layout'] );

		// Teardown.
		remove_filter( 'apple_news_cover_json', [ $this, 'filter_apple_news_cover_json' ] );
	}

	/**
	 * Test cover component role ('image' or 'photo') based on postmeta selection.
	 */
	public function test_cover_role() {
		// Create test post.
		$post_id = self::factory()->post->create();

		// Create a new attachment and assign it as the featured image for the cover component.
		set_post_thumbnail(
			$post_id,
			$this->get_new_attachment( 0 )
		);

		// Check that the cover component's default role is 'photo'.
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'photo', $json['components'][0]['components'][0]['role'] );

		// Toggle component type meta.
		update_post_meta( $post_id, 'apple_news_use_image_component', true );

		// Check that the cover component's role is now 'image' after meta toggle.
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'image', $json['components'][0]['components'][0]['role'] );
	}

	/**
	 * Ensures that the lightbox font is set to the same font face as the image caption.
	 */
	public function test_lightbox_font() {
		$this->set_theme_settings(
			[
				'caption_font'         => 'Menlo-Regular',
				'cover_caption'        => true,
				'meta_component_order' => [ 'cover' ],
			]
		);

		// Create a new post and set the featured image with a caption.
		$post_id  = self::factory()->post->create();
		$image_id = $this->get_new_attachment( $post_id, 'Test Caption', 'Test alt text.' );
		set_post_thumbnail( $post_id, $image_id );
		$json = $this->get_json_for_post( $post_id );

		// Ensure that the font set on the lightbox is the same as the font set on the caption above.
		$this->assertEquals(
			'Menlo-Regular',
			$json['components'][0]['components'][0]['caption']['textStyle']['fontName']
		);
	}

	/**
	 * Tests the behavior of root-relative URLs on the Cover component, to ensure
	 * that they are properly converted to full URLs on export.
	 */
	public function test_relative_url() {
		$this->set_theme_settings(
			[
				'meta_component_order' => [ 'cover' ],
			]
		);

		// Create a post with an image with a root-relative src property and ensure its URL is expanded fully when converted to a Cover component.
		$image_id_1 = $this->get_new_attachment( 0, 'Test Caption', 'Test alt text.' );
		$post_id_1  = self::factory()->post->create( [ 'post_content' => str_replace( home_url(), '', $this->get_image_with_caption( $image_id_1 ) ) ] );
		$json_1     = $this->get_json_for_post( $post_id_1 );
		$this->assertEquals( wp_get_attachment_image_url( $image_id_1, 'full' ), $json_1['components'][0]['components'][0]['URL'] );

		// Create a post with an image set as the featured image, and add a filter to set the asset URL to root-relative, and ensure its URL is expanded fully when converted to a Cover component.
		$image_id_2 = $this->get_new_attachment( 0, 'Test Caption', 'Test alt text.' );
		$post_id_2  = self::factory()->post->create();
		set_post_thumbnail( $post_id_2, $image_id_2 );
		$image_url = wp_get_attachment_image_url( $image_id_2, 'full' );
		add_filter( 'wp_get_attachment_url', [ $this, 'filter_wp_get_attachment_url' ] );
		$json_2 = $this->get_json_for_post( $post_id_2 );
		remove_filter( 'wp_get_attachment_url', [ $this, 'filter_wp_get_attachment_url' ] );
		$this->assertEquals( $image_url, $json_2['components'][0]['components'][0]['URL'] );
	}

	/**
	 * Tests the render method for the component.
	 */
	public function test_render() {
		$this->set_theme_settings(
			[
				'cover_caption'        => true,
				'meta_component_order' => [ 'cover' ],
			]
		);

		// Create a test post with an image in the content and get the JSON for it.
		// The image from the content should be the cover image, and the image should be removed from the content.
		$image_id = $this->get_new_attachment( 0, 'Test Caption', 'Test alt text.' );
		$post_id  = self::factory()->post->create( [ 'post_content' => $this->get_image_with_caption( $image_id ) ] );
		$json     = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'header', $json['components'][0]['role'] );
		$this->assertEquals( 'photo', $json['components'][0]['components'][0]['role'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_id, 'full' ), $json['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 'caption', $json['components'][0]['components'][1]['role'] );
		$this->assertEquals( 'Test Caption', $json['components'][0]['components'][1]['text'] );
		$this->assertEquals( 1, count( $json['components'] ) );

		// Create an image and attach it as the featured image to the previously created test post.
		// The featured image should be used as the cover, and the original image should not be removed from the content.
		$featured_image_id = $this->get_new_attachment( $post_id, 'Test Featured Image Caption', 'Test featured image alt text.' );
		set_post_thumbnail( $post_id, $featured_image_id );
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'header', $json['components'][0]['role'] );
		$this->assertEquals( 'photo', $json['components'][0]['components'][0]['role'] );
		$this->assertEquals( wp_get_attachment_image_url( $featured_image_id, 'full' ), $json['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 'caption', $json['components'][0]['components'][1]['role'] );
		$this->assertEquals( 'Test Featured Image Caption', $json['components'][0]['components'][1]['text'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_id, 'full' ), $json['components'][1]['components'][0]['components'][0]['URL'] );

		// Create an image and set it as the cover in postmeta.
		// The custom cover image should be used as the cover, and the original image should not be removed from the content.
		$cover_image_id = $this->get_new_attachment( $post_id, 'Test Cover Image Caption', 'Test cover image alt text.' );
		add_post_meta( $post_id, 'apple_news_coverimage', $cover_image_id );
		add_post_meta( $post_id, 'apple_news_coverimage_caption', 'Test Cover Image Postmeta Caption' );
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'header', $json['components'][0]['role'] );
		$this->assertEquals( 'photo', $json['components'][0]['components'][0]['role'] );
		$this->assertEquals( wp_get_attachment_image_url( $cover_image_id, 'full' ), $json['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 'caption', $json['components'][0]['components'][1]['role'] );
		$this->assertEquals( 'Test Cover Image Postmeta Caption', $json['components'][0]['components'][1]['text'] );
		$this->assertEquals( wp_get_attachment_image_url( $image_id, 'full' ), $json['components'][1]['components'][0]['components'][0]['URL'] );

		// Turn off the cover caption feature and ensure that the caption is removed.
		$this->set_theme_settings( [ 'cover_caption' => false ] );
		$json = $this->get_json_for_post( $post_id );
		$this->assertEquals( 'header', $json['components'][0]['role'] );
		$this->assertEquals( 'photo', $json['components'][0]['components'][0]['role'] );
		$this->assertEquals( wp_get_attachment_image_url( $cover_image_id, 'full' ), $json['components'][0]['components'][0]['URL'] );
		$this->assertEquals( 1, count( $json['components'][0]['components'] ) );
	}
}
