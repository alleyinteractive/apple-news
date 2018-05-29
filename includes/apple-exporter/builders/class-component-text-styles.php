<?php
/**
 * Publish to Apple News: \Apple_Exporter\Builders\Component_Text_Styles class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Builders
 */

namespace Apple_Exporter\Builders;

/**
 * Exporter and components can register styles. This class manages the styles
 * the final JSON will contain.
 *
 * @since 0.4.0
 */
class Component_Text_Styles extends Builder {

	/**
	 * All styles.
	 *
	 * @var array
	 * @access private
	 */
	private $styles;

	/**
	 * Constructor.
	 *
	 * @param \Apple_Exporter\Exporter_Content          $content The content object to load.
	 * @param \Apple_Exporter\Exporter_Content_Settings $settings The settings object to load.
	 * @access public
	 */
	public function __construct( $content, $settings ) {
		parent::__construct( $content, $settings );
		$this->styles = array();
	}

	/**
	 * Register a style into the exporter.
	 *
	 * @since 0.4.0
	 * @param string $name The name of the style to register.
	 * @param array  $spec The spec for the style.
	 * @access public
	 */
	public function register_style( $name, $spec ) {
		// Only register once, styles have unique names.
		if ( array_key_exists( $name, $this->styles ) ) {
			return;
		}

		$this->styles[ $name ] = $spec;
	}

	/**
	 * Returns all styles defined so far.
	 *
	 * @since 0.4.0
	 * @return array
	 * @access protected
	 */
	protected function build() {
		return apply_filters( 'apple_news_component_text_styles', $this->styles );
	}

}
