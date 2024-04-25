<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Builders\Text_Styles class
 *
 * Contains a class which is used to set top-level text styles.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 1.2.1
 */

namespace Apple_Exporter\Builders;

use Apple_Exporter\Component_Spec;
use Apple_Exporter\Components\Body;
use Apple_Exporter\Exporter_Content;
use Apple_Exporter\Exporter_Content_Settings;
use Apple_Exporter\Theme;

/**
 * A class which is used to set top-level text styles.
 *
 * @since 1.2.1
 */
class Text_Styles extends Builder {

	/**
	 * All styles.
	 *
	 * @access private
	 * @var array
	 */
	private $styles = [];

	/**
	 * Constructor.
	 *
	 * @param Exporter_Content          $content The content for this export.
	 * @param Exporter_Content_Settings $settings The settings for this export.
	 *
	 * @access public
	 */
	public function __construct( $content, $settings ) {
		parent::__construct( $content, $settings );

		// Determine whether to add the styles for HTML content.
		if ( 'yes' === $this->get_setting( 'html_support' ) ) {
			$this->add_html_styles();
		}
	}

	/**
	 * Returns all styles defined so far.
	 *
	 * @access public
	 * @return array The styles defined thus far.
	 */
	public function build() {

		/**
		 * Allows for filtering of global text styles.
		 *
		 * @since 1.2.1
		 *
		 * @param array $styles The styles to be filtered.
		 */
		return apply_filters( 'apple_news_text_styles', $this->styles );
	}

	/**
	 * Register a style into the exporter.
	 *
	 * @param string $name The name of the style to register.
	 * @param array  $values The values to register to the style.
	 *
	 * @access public
	 */
	public function register_style( $name, $values ) {

		// Only register once, since styles have unique names.
		if ( array_key_exists( $name, $this->styles ) ) {
			return;
		}

		// Register the style.
		$this->styles[ $name ] = $values;
	}

	/**
	 * Adds HTML styles to the list.
	 *
	 * @access private
	 */
	private function add_html_styles() {
		// Try to get the text styles spec from the body component.
		$body        = new Body();
		$specs       = $body->get_specs();
		$text_styles = $specs['default-text-styles'];
		if ( empty( $text_styles ) || ! $text_styles instanceof Component_Spec ) {
			return;
		}

		// Get the computed specs for the text styles including any overrides from the theme via Customize JSON and apply them.
		$computed_styles = $text_styles->substitute_values( [] );
		foreach ( $computed_styles as $name => $values ) {
			$this->register_style( $name, $values );
		}
	}
}
