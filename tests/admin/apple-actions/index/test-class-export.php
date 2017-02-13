<?php

use \Apple_Actions\Index\Export as Export;
use \Apple_Exporter\Settings as Settings;

class Admin_Action_Index_Export_Test extends WP_UnitTestCase {

	public function setup() {
		$this->settings = new Settings();
	}

	/**
	 * Gets formatting settings for themes.
	 *
	 * @access private
	 */
	private function getFormattingSettings( $all_settings ) {
		// Get only formatting settings
		$formatting = new Admin_Apple_Settings_Section_Formatting( '' );
		$formatting_settings = $formatting->get_settings();

		$formatting_settings_keys = array_keys( $formatting_settings );
		$filtered_settings = array();

		foreach ( $formatting_settings_keys as $key ) {
			if ( isset( $all_settings[ $key ] ) ) {
				$filtered_settings[ $key ] = $all_settings[ $key ];
			}
		}

		return $filtered_settings;
	}

	public function testAutoExcerpt() {
		$title = 'My Title';
		$content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';

		$post_id = $this->factory->post->create( array(
			'post_title' => $title,
			'post_content' => $content,
			'post_excerpt' => '',
		) );

		$export = new Export( $this->settings, $post_id );
		$exporter = $export->fetch_exporter();
		$exporter_content = $exporter->get_content();

		$this->assertEquals( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue...', $exporter_content->intro() );
	}

	public function testShortcodeInExcerpt() {
		$title = 'My Title';
		$content = '<p>[caption id="attachment_12345" align="aligncenter" width="500"]Test[/caption]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';

		$post_id = $this->factory->post->create( array(
			'post_title' => $title,
			'post_content' => $content,
			'post_excerpt' => '',
		) );

		$export = new Export( $this->settings, $post_id );
		$exporter = $export->fetch_exporter();
		$exporter_content = $exporter->get_content();

		$this->assertEquals( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue...', $exporter_content->intro() );
	}

	public function testBylineFormat() {
		$user_id = $this->factory->user->create( array(
			'role' => 'administrator',
			'display_name' => 'Testuser',
		) );

		$title = 'My Title';
		$content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';

		$post_id = $this->factory->post->create( array(
			'post_title' => $title,
			'post_content' => $content,
			'post_excerpt' => '',
			'post_author' => $user_id,
			'post_date' => '2016-08-26 12:00',
		) );

		$export = new Export( $this->settings, $post_id );
		$exporter = $export->fetch_exporter();
		$exporter_content = $exporter->get_content();

		$this->assertEquals( 'by Testuser | Aug 26, 2016 | 12:00 PM', $exporter_content->byline() );
	}

	public function testBylineFormatWithHashtag() {
		$user_id = $this->factory->user->create( array(
			'role' => 'administrator',
			'display_name' => '#Testuser',
		) );

		$title = 'My Title';
		$content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';

		$post_id = $this->factory->post->create( array(
			'post_title' => $title,
			'post_content' => $content,
			'post_excerpt' => '',
			'post_author' => $user_id,
			'post_date' => '2016-08-26 12:00',
		) );

		$export = new Export( $this->settings, $post_id );
		$exporter = $export->fetch_exporter();
		$exporter_content = $exporter->get_content();

		$this->assertEquals( 'by #Testuser | Aug 26, 2016 | 12:00 PM', $exporter_content->byline() );
	}

	public function testSectionMapping() {
		// Create a post
		$title = 'My Title';
		$content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';

		$post_id = $this->factory->post->create( array(
			'post_title' => $title,
			'post_content' => $content,
		) );

		// Create a term and add it to the post
		$term_id = $this->factory->term->create( array(
			'taxonomy' => 'category',
			'name' => 'news',
		) );
		wp_set_post_terms( $post_id, array( $term_id ), 'category' );

		// Create a taxonomy map
		update_option( \Admin_Apple_Sections::TAXONOMY_MAPPING_KEY, array(
			'abcdef01-2345-6789-abcd-ef012356789a' => array( $term_id ),
		) );

		// Cache as a transient to bypass the API call
		$self = 'https://u48r14.digitalhub.com/channels/abcdef01-2345-6789-abcd-ef012356789a';
		set_transient(
			'apple_news_sections',
			array(
				(object) array(
					'createdAt' => '2017-01-01T00:00:00Z',
					'id' => 'abcdef01-2345-6789-abcd-ef012356789a',
					'isDefault' => true,
					'links' => (object) array(
						'channel' => 'https://u48r14.digitalhub.com/channels/abcdef01-2345-6789-abcd-ef0123567890',
						'self' => $self,
					),
					'modifiedAt' => '2017-01-01T00:00:00Z',
					'name' => 'Main',
					'shareUrl' => 'https://apple.news/AbCdEfGhIj-KlMnOpQrStUv',
					'type' => 'section',
				),
			)
		);

		// Get sections for the post
		$sections = \Admin_Apple_Sections::get_sections_for_post( $post_id );

		// Check that the correct mapping was returned
		$this->assertEquals(
			$sections,
			array( $self )
		);

		// Remove the transient and the map
		delete_option( \Admin_Apple_Sections::TAXONOMY_MAPPING_KEY );
		delete_transient( 'apple_news_sections' );
	}

	public function testThemeMapping() {
		$themes = new \Admin_Apple_Themes();
		$defaults = $this->getFormattingSettings( $this->settings->all() );
		update_option( $themes->theme_key_from_name( 'Default' ), $defaults );

		// Make the settings different for this theme to differentiate
		$new_theme = $defaults;
		$new_theme['body_color'] = '#123456';
		update_option( $themes->theme_key_from_name( 'Test Theme' ), $new_theme );

		// Create a post
		$title = 'My Title';
		$content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras tristique quis justo sit amet eleifend. Praesent id metus semper, fermentum nibh at, malesuada enim. Mauris eget faucibus lectus. Vivamus iaculis eget urna non porttitor. Donec in dignissim neque. Vivamus ut ornare magna. Nulla eros nisi, maximus nec neque at, condimentum lobortis leo. Fusce in augue arcu. Curabitur lacus elit, venenatis a laoreet sit amet, imperdiet ac lorem. Curabitur sed leo sed ligula tempor feugiat. Cras in tellus et elit volutpat.</p>';

		$post_id = $this->factory->post->create( array(
			'post_title' => $title,
			'post_content' => $content,
		) );

		// Set it to a fake section
		$section_id = 'https://u48r14.digitalhub.com/channels/abcdef01-2345-6789-abcd-ef012356789a';
		update_post_meta( $post_id, 'apple_news_sections', array( $section_id ) );

		// Create a mapping from that section to the test theme
		update_option( \Admin_Apple_Sections::THEME_MAPPING_KEY, array(
			basename( $section_id ) => 'Test Theme',
		) );

		$sections = \Admin_Apple_Sections::get_sections_for_post( $post_id );
		$export = new Export( $this->settings, $post_id, $sections );
		$exporter = $export->fetch_exporter();
		$exporter->generate();
		$json = $exporter->get_json();
		$settings = json_decode( $json );

		$this->assertEquals( $settings->componentTextStyles->dropcapBodyStyle->textColor, $new_theme['body_color'] );

		// Clean up
		delete_option( $themes->theme_key_from_name( 'Default' ) );
		delete_option( $themes->theme_key_from_name( 'Test Theme' ) );

	}

}

