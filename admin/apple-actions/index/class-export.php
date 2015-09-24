<?php

namespace Apple_Actions\Index;

require_once plugin_dir_path( __FILE__ ) . '../class-action.php';
require_once plugin_dir_path( __FILE__ ) . '../../../includes/apple-exporter/autoload.php';

use Apple_Actions\Action as Action;
use Apple_Exporter\Exporter as Exporter;
use Apple_Exporter\Exporter_Content as Exporter_Content;
use Apple_Exporter\Exporter_Content_Settings as Exporter_Content_Settings;

class Export extends Action {

	/**
	 * ID of the post being exported.
	 *
	 * @var int
	 * @access private
	 */
	private $id;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings
	 * @param int $id
	 */
	function __construct( $settings, $id ) {
		parent::__construct( $settings );
		$this->id = $id;
	}

	/**
	 * Perform the export and return the results.
	 *
	 * @return string The JSON data
	 * @access public
	 */
	public function perform() {
		$exporter = $this->fetch_exporter();
		return $exporter->export();
	}

	/**
	 * Fetches an instance of Exporter.
	 *
	 * @return Exporter
	 * @access public
	 */
	public function fetch_exporter() {
		// Fetch WP_Post object, and all required post information to fill up the
		// Exporter_Content instance.
		$post       = get_post( $this->id );
		$post_thumb = wp_get_attachment_url( get_post_thumbnail_id( $this->id ) ) ?: null;
		$author     = get_the_author_meta( 'display_name', $post->post_author );
		$date       = date( 'M j, Y | g:i A', strtotime( $post->post_date ) );
		$byline     = 'by ' . ucfirst( $author ) . ' | ' . $date ;

		do_action( 'apple_news_pre_base_content', $post );

		$base_content = new Exporter_Content(
			$post->ID,
			$post->post_title,
			// post_content is not raw HTML, as WordPress editor cleans up
			// paragraphs and new lines, so we need to transform the content to
			// HTML. We use 'the_content' filter for that.
			apply_filters( 'the_content', $post->post_content ),
			$post->post_excerpt,
			$post_thumb,
			$byline,
			$this->fetch_content_settings()
		);

		do_action( 'apple_news_post_base_content', $post );

		return new Exporter( $base_content, null, $this->settings );
	}

	/**
	 * Loads settings for the Exporter_Content from the WordPress post metadata.
	 *
	 * @since 0.4.0
	 * @return Settings
	 * @access private
	 */
	private function fetch_content_settings() {
		$settings = new Exporter_Content_Settings();
		foreach ( get_post_meta( $this->id ) as $name => $value ) {
			if ( 0 === strpos( $name, 'apple_news_' ) ) {
				$name  = str_replace( 'apple_news_', '', $name );
				$value = $value[0];
				$settings->set( $name, $value );
			}
		}
		return apply_filters( 'apple_news_content_settings', $settings );
	}

}
