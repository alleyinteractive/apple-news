<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Builders\Builder abstract class
 *
 * Contains an abstract class to form the foundation of component builders.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.4.0
 */

namespace Apple_Exporter\Builders;

use Apple_Exporter\Exporter_Content;
use Apple_Exporter\Exporter_Content_Settings;

/**
 * A base abstract builder from which all other builders inherit.
 *
 * All builders must implement a build method, in which they return an array to
 * represent a part of the final article.
 *
 * @since 0.4.0
 */
abstract class Builder {

	/**
	 * The content object to be exported.
	 *
	 * @var Exporter_Content
	 * @access private
	 * @since 0.4.0
	 */
	private $content;

	/**
	 * Exporter settings object.
	 *
	 * @var Exporter_Content_Settings
	 * @access private
	 * @since 0.4.0
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param Exporter_Content $content The content object to load.
	 * @param Exporter_Content_Settings $settings The settings object to load.
	 *
	 * @access public
	 */
	public function __construct( $content, $settings ) {
		$this->content = $content;
		$this->settings = $settings;
	}

	/**
	 * Returns an array of the content.
	 *
	 * @access public
	 * @return array The content in array format.
	 */
	public function to_array() {
		return $this->build();
	}

	/**
	 * Builds the content.
	 *
	 * @access protected
	 */
	protected abstract function build();

	/**
	 * Gets the content byline.
	 *
	 * @access protected
	 * @return string The byline from the content object.
	 */
	protected function content_byline() {
		return $this->content->byline();
	}

	/**
	 * Gets the content cover.
	 *
	 * @access protected
	 * @return string The URL for the cover image from the content object.
	 */
	protected function content_cover() {
		return $this->content->cover();
	}

	/**
	 * Gets the content ID.
	 *
	 * @access protected
	 * @return int The ID from the content object.
	 */
	protected function content_id() {
		return $this->content->id();
	}

	/**
	 * Gets the content intro.
	 *
	 * @access protected
	 * @return string The intro from the content object.
	 */
	protected function content_intro() {
		return $this->content->intro();
	}

	/**
	 * Gets the content nodes.
	 *
	 * @access protected
	 * @return array The nodes from the content object.
	 */
	protected function content_nodes() {
		return $this->content->nodes();
	}

	/**
	 * Gets a content setting.
	 *
	 * @param string $name The setting name to retrieve.
	 *
	 * @access protected
	 * @return mixed The value for the setting.
	 */
	protected function content_setting( $name ) {
		return $this->content->get_setting( $name );
	}

	/**
	 * Gets the content body.
	 *
	 * @access protected
	 * @return string The body text from the content object.
	 */
	protected function content_text() {
		return $this->content->content();
	}

	/**
	 * Gets the content title.
	 *
	 * @access protected
	 * @return string The title from the content object, or a fallback title.
	 */
	protected function content_title() {
		return $this->content->title()
			? $this->content->title()
			: __( 'Untitled Article', 'apple-news' );
	}

	/**
	 * Gets a content setting by key.
	 *
	 * @param string $name The setting name to retrieve.
	 *
	 * @access protected
	 * @return mixed The value of the setting.
	 */
	protected function get_setting( $name ) {
		return $this->settings->$name;
	}

	/**
	 * Updates a content property.
	 *
	 * @param string $name The setting key to modify.
	 * @param mixed $value The new value for the setting.
	 *
	 * @access protected
	 */
	protected function set_content_property( $name, $value ) {
		$this->content->set_property( $name, $value );
	}
}
