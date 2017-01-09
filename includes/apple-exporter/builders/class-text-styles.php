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

use Apple_Exporter\Exporter_Content;
use Apple_Exporter\Exporter_Content_Settings;

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
	private $_styles = array();

	/**
	 * Constructor.
	 *
	 * @param Exporter_Content $content The content for this export.
	 * @param Exporter_Content_Settings $settings The settings for this export.
	 *
	 * @access public
	 */
	public function __construct( $content, $settings ) {
		parent::__construct( $content, $settings );

		// Determine whether to add the styles for HTML content.
		if ( 'yes' === $this->get_setting( 'html_support' ) ) {
			$this->_add_html_styles();
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
		return apply_filters( 'apple_news_text_styles', $this->_styles );
	}

	/**
	 * Register a style into the exporter.
	 *
	 * @param string $name The name of the style to register.
	 * @param array $values The values to register to the style.
	 *
	 * @access public
	 */
	public function register_style( $name, $values ) {

		// Only register once, since styles have unique names.
		if ( array_key_exists( $name, $this->_styles ) ) {
			return;
		}

		// Register the style.
		$this->_styles[ $name ] = $values;
	}

	/**
	 * Adds HTML styles to the list.
	 *
	 * @access private
	 */
	private function _add_html_styles() {

		// Add style for <code> tags.
		$this->register_style( 'default-tag-code', array(
			'fontName' => $this->get_setting( 'monospaced_font' ),
			'fontSize' => intval( $this->get_setting( 'monospaced_size' ) ),
			'tracking' => intval( $this->get_setting( 'monospaced_tracking' ) ) / 100,
			'lineHeight' => intval( $this->get_setting( 'monospaced_line_height' ) ),
			'textColor' => $this->get_setting( 'monospaced_color' ),
		) );

		// Add style for <pre> tags.
		$this->register_style( 'default-tag-pre', array(
			'textAlignment' => 'left',
			'fontName' => $this->get_setting( 'monospaced_font' ),
			'fontSize' => intval( $this->get_setting( 'monospaced_size' ) ),
			'tracking' => intval( $this->get_setting( 'monospaced_tracking' ) ) / 100,
			'lineHeight' => intval( $this->get_setting( 'monospaced_line_height' ) ),
			'textColor' => $this->get_setting( 'monospaced_color' ),
			'paragraphSpacingBefore' => 18,
			'paragraphSpacingAfter' => 18,
		) );

		// Add style for <samp> tags.
		$this->register_style( 'default-tag-samp', array(
			'fontName' => $this->get_setting( 'monospaced_font' ),
			'fontSize' => intval( $this->get_setting( 'monospaced_size' ) ),
			'tracking' => intval( $this->get_setting( 'monospaced_tracking' ) ) / 100,
			'lineHeight' => intval( $this->get_setting( 'monospaced_line_height' ) ),
			'textColor' => $this->get_setting( 'monospaced_color' ),
		) );
	}
}