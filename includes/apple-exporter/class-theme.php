<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Theme class
 *
 * Contains a class which is used to represent a theme.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 1.3.0
 */

namespace Apple_Exporter;

/**
 * A class that represents a theme.
 *
 * @since 1.3.0
 */
class Theme {

	/**
	 * Prefix for individual theme keys.
	 *
	 * @var string
	 */
	const THEME_KEY_PREFIX = 'apple_news_theme_';

	/**
	 * Option group configuration, to be used when printing fields.
	 *
	 * @var array
	 */
	private static $_groups = array();

	/**
	 * Theme options configuration.
	 *
	 * @var array
	 */
	private static $_options = array();

	/**
	 * The name of this theme.
	 *
	 * @var string
	 */
	private $_name = '';

	/**
	 * Values for theme options for this theme.
	 *
	 * @var array
	 */
	private $_values = array();

	/**
	 * Returns an array of groups of configurable options for themes.
	 *
	 * @access public
	 * @return array Groups of configurable options for themes.
	 */
	public function get_groups() {

		// If groups have not been initialized, initialize them now.
		if ( empty( self::$_groups ) ) {
			$this->_initialize_groups();
		}

		return self::$_groups;
	}

	/**
	 * Gets the name of this theme.
	 *
	 * @return string The name of the theme.
	 */
	public function get_name() {

		// If no name is set, use the default.
		if ( empty( $this->_name ) ) {
			$this->_name = __( 'Default', 'apple-news' );
		}

		return $this->_name;
	}

	/**
	 * Returns an array of configurable options for themes.
	 *
	 * @access public
	 * @return array Configurable options for themes.
	 */
	public function get_options() {

		// If options have not been initialized, initialize them now.
		if ( empty( self::$_options ) ) {
			$this->_initialize_options();
		}

		return self::$_options;
	}

	/**
	 * Gets a value for a theme option for this theme.
	 *
	 * @param string $option The option name for which to retrieve a value.
	 *
	 * @access public
	 * @return mixed The value for the option name provided.
	 */
	public function get_value( $option ) {

		// Attempt to return the value from the values array.
		if ( isset( $this->_values[ $option ] ) ) {
			return $this->_values[ $option ];
		}

		// Attempt to fall back to the default.
		if ( isset( self::$_options[ $option ]['default'] ) ) {
			return self::$_options[ $option ]['default'];
		}

		return null;
	}

	/**
	 * Loads theme information from the database.
	 *
	 * @access public
	 * @return bool True on success, false on failure.
	 */
	public function load() {

		// Attempt to load the theme from the database.
		$values = get_option( self::THEME_KEY_PREFIX . md5( $this->get_name() ) );
		if ( ! is_array( $values ) ) {
			return false;
		}

		// Loop over loaded values from the database and add to local values.
		foreach ( $values as $key => $value ) {

			// Skip any keys that don't exist in the options spec.
			if ( ! isset( self::$_options[ $key ] ) ) {
				continue;
			}

			// Skip any keys that use the default value.
			if ( self::$_options[ $key ] === $value ) {
				continue;
			}

			// Store the value in the list of overrides.
			$this->_values[ $key ] = $value;
		}

		return true;
	}

	/**
	 * Renders the meta component order field.
	 *
	 * @access public
	 */
	public static function render_meta_component_order( $theme ) {

		// Get the current order.
		$component_order = $theme->get_value( 'meta_component_order' );
		if ( empty( $component_order ) || ! is_array( $component_order ) ) {
			$component_order = array();
		}

		// Get inactive components.
		$inactive_components = array_diff(
			self::$_options['meta_component_order']['default'],
			$component_order
		);

		// Load the template.
		include dirname( dirname( plugin_dir_path( __FILE__ ) ) )
			. '/admin/partials/field_meta_component_order.php';
	}

	/**
	 * Sets the theme name property.
	 *
	 * @param string $name The name to set.
	 */
	public function set_name( $name ) {
		$this->_name = $name;
	}

	/**
	 * Initializes the groups array with values.
	 *
	 * @access private
	 */
	private function _initialize_groups() {
		self::$_groups = array(
			'layout' => array(
				'label' => __( 'Layout Spacing', 'apple-news' ),
				'description' => __( 'The spacing for the base layout of the exported articles', 'apple-news' ),
				'settings' => array( 'layout_margin', 'layout_gutter' ),
			),
			'body' => array(
				'label' => __( 'Body', 'apple-news' ),
				'settings' => array(
					'body_font',
					'body_size',
					'body_line_height',
					'body_tracking',
					'body_color',
					'body_link_color',
					'body_background_color',
					'body_orientation',
				),
			),
			'dropcap' => array(
				'label' => __( 'Drop Cap', 'apple-news' ),
				'settings' => array(
					'initial_dropcap',
					'dropcap_background_color',
					'dropcap_color',
					'dropcap_font',
					'dropcap_number_of_characters',
					'dropcap_number_of_lines',
					'dropcap_number_of_raised_lines',
					'dropcap_padding',
				),
			),
			'byline' => array(
				'label' => __( 'Byline', 'apple-news' ),
				'description' => __( "The byline displays the article's author and publish date", 'apple-news' ),
				'settings' => array(
					'byline_font',
					'byline_size',
					'byline_line_height',
					'byline_tracking',
					'byline_color',
					'byline_format',
				),
			),
			'heading1' => array(
				'label' => __( 'Heading 1', 'apple-news' ),
				'settings' => array(
					'header1_font',
					'header1_color',
					'header1_size',
					'header1_line_height',
					'header1_tracking',
				),
			),
			'heading2' => array(
				'label' => __( 'Heading 2', 'apple-news' ),
				'settings' => array(
					'header2_font',
					'header2_color',
					'header2_size',
					'header2_line_height',
					'header2_tracking',
				),
			),
			'heading3' => array(
				'label' => __( 'Heading 3', 'apple-news' ),
				'settings' => array(
					'header3_font',
					'header3_color',
					'header3_size',
					'header3_line_height',
					'header3_tracking',
				),
			),
			'heading4' => array(
				'label' => __( 'Heading 4', 'apple-news' ),
				'settings' => array(
					'header4_font',
					'header4_color',
					'header4_size',
					'header4_line_height',
					'header4_tracking',
				),
			),
			'heading5' => array(
				'label' => __( 'Heading 5', 'apple-news' ),
				'settings' => array(
					'header5_font',
					'header5_color',
					'header5_size',
					'header5_line_height',
					'header5_tracking',
				),
			),
			'heading6' => array(
				'label' => __( 'Heading 6', 'apple-news' ),
				'settings' => array(
					'header6_font',
					'header6_color',
					'header6_size',
					'header6_line_height',
					'header6_tracking',
				),
			),
			'caption' => array(
				'label' => __( 'Image caption', 'apple-news' ),
				'settings' => array(
					'caption_font',
					'caption_size',
					'caption_line_height',
					'caption_tracking',
					'caption_color',
				),
			),
			'pullquote' => array(
				'label' => __( 'Pull quote', 'apple-news' ),
				'description' => sprintf(
					'%s <a href="https://en.wikipedia.org/wiki/Pull_quote">%s</a>.',
					__( 'Articles can have an optional', 'apple-news' ),
					__( 'Pull quote', 'apple-news' )
				),
				'settings' => array(
					'pullquote_font',
					'pullquote_size',
					'pullquote_line_height',
					'pullquote_tracking',
					'pullquote_color',
					'pullquote_hanging_punctuation',
					'pullquote_border_style',
					'pullquote_border_color',
					'pullquote_border_width',
					'pullquote_transform',
				),
			),
			'blockquote' => array(
				'label' => __( 'Blockquote', 'apple-news' ),
				'settings' => array(
					'blockquote_font',
					'blockquote_size',
					'blockquote_line_height',
					'blockquote_tracking',
					'blockquote_color',
					'blockquote_border_style',
					'blockquote_border_color',
					'blockquote_border_width',
					'blockquote_background_color',
				),
			),
			'monospaced' => array(
				'label' => __( 'Monospaced (<pre>, <code>, <samp>)', 'apple-news' ),
				'settings' => array(
					'monospaced_font',
					'monospaced_size',
					'monospaced_line_height',
					'monospaced_tracking',
					'monospaced_color',
				),
			),
			'gallery' => array(
				'label' => __( 'Gallery', 'apple-news' ),
				'description' => __( 'Can either be a standard gallery, or mosaic.', 'apple-news' ),
				'settings' => array( 'gallery_type' ),
			),
			'advertisement' => array(
				'label' => __( 'Advertisement', 'apple-news' ),
				'settings' => array(
					'enable_advertisement',
					'ad_frequency',
					'ad_margin',
				),
			),
			'component_order' => array(
				'label' => __( 'Component Order', 'apple-news' ),
				'settings' => array( 'meta_component_order' ),
			),
		);
	}

	/**
	 * Initializes the options array with values.
	 *
	 * @access private
	 */
	private function _initialize_options() {
		self::$_options = array(
			'ad_frequency' => array(
				'default' => 1,
				'description' => __( 'A number between 1 and 10 defining the frequency for automatically inserting Banner Advertisement components into articles. For more information, see the <a href="https://developer.apple.com/library/ios/documentation/General/Conceptual/Apple_News_Format_Ref/AdvertisingSettings.html#//apple_ref/doc/uid/TP40015408-CH93-SW1" target="_blank">Apple News Format Reference</a>.', 'apple-news' ),
				'label' => __( 'Ad Frequency', 'apple-news' ),
				'type' => 'integer',
			),
			'ad_margin' => array(
				'default' => 15,
				'description' => __( 'The margin to use above and below inserted ads.', 'apple-news' ),
				'label' => __( 'Ad Margin', 'apple-news' ),
				'type' => 'integer',
			),
			'blockquote_background_color' => array(
				'default' => '#e1e1e1',
				'label' => __( 'Blockquote background color', 'apple-news' ),
				'type' => 'color',
			),
			'blockquote_border_color' => array(
				'default' => '#4f4f4f',
				'label' => __( 'Blockquote border color', 'apple-news' ),
				'type' => 'color',
			),
			'blockquote_border_style' => array(
				'default' => 'solid',
				'label' => __( 'Blockquote border style', 'apple-news' ),
				'type' => array( 'solid', 'dashed', 'dotted', 'none' ),
			),
			'blockquote_border_width' => array(
				'default' => 3,
				'label' => __( 'Blockquote border width', 'apple-news' ),
				'type' => 'integer',
			),
			'blockquote_color' => array(
				'default' => '#4f4f4f',
				'label' => __( 'Blockquote color', 'apple-news' ),
				'type' => 'color',
			),
			'blockquote_font' => array(
				'default' => 'AvenirNext-Regular',
				'label' => __( 'Blockquote font face', 'apple-news' ),
				'type' => 'font',
			),
			'blockquote_line_height' => array(
				'default' => 24,
				'label' => __( 'Blockquote line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'blockquote_size' => array(
				'default' => 18,
				'label' => __( 'Blockquote font size', 'apple-news' ),
				'type' => 'integer',
			),
			'blockquote_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Blockquote tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'body_background_color' => array(
				'default' => '#fafafa',
				'label' => __( 'Body background color', 'apple-news' ),
				'type' => 'color',
			),
			'body_color' => array(
				'default' => '#4f4f4f',
				'label' => __( 'Body font color', 'apple-news' ),
				'type' => 'color',
			),
			'body_font' => array(
				'default' => 'AvenirNext-Regular',
				'label' => __( 'Body font face', 'apple-news' ),
				'type' => 'font',
			),
			'body_line_height' => array(
				'default' => 24,
				'label' => __( 'Body line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'body_link_color' => array(
				'default' => '#428bca',
				'label' => __( 'Body font hyperlink color', 'apple-news' ),
				'type' => 'color',
			),
			'body_orientation' => array(
				'default' => 'left',
				'description' => __( 'Controls margins on larger screens. Left orientation includes one column of margin on the right, right orientation includes one column of margin on the left, and center orientation includes one column of margin on either side.', 'apple-news' ),
				'label' => __( 'Body orientation', 'apple-news' ),
				'type' => array( 'left', 'center', 'right' ),
			),
			'body_size' => array(
				'default' => 18,
				'label' => __( 'Body font size', 'apple-news' ),
				'type' => 'integer',
			),
			'body_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Body tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'byline_color' => array(
				'default' => '#7c7c7c',
				'label' => __( 'Byline font color', 'apple-news' ),
				'type' => 'color',
			),
			'byline_font' => array(
				'default' => 'AvenirNext-Medium',
				'label' => __( 'Byline font face', 'apple-news' ),
				'type' => 'font',
			),
			'byline_format' => array(
				'default' => 'by #author# | #M j, Y | g:i A#',
				'description' => __( 'Set the byline format. Two tokens can be present, #author# to denote the location of the author name and a <a href="http://php.net/manual/en/function.date.php" target="blank">PHP date format</a> string also encapsulated by #. The default format is "by #author# | #M j, Y | g:i A#". Note that byline format updates only preview on save.', 'apple-news' ),
				'label' => __( 'Byline format', 'apple-news' ),
				'type' => 'text',
			),
			'byline_line_height' => array(
				'default' => 24,
				'label' => __( 'Byline line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'byline_size' => array(
				'default' => 13,
				'label' => __( 'Byline font size', 'apple-news' ),
				'type' => 'integer',
			),
			'byline_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Byline tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'caption_color' => array(
				'default' => '#4f4f4f',
				'label' => __( 'Caption font color', 'apple-news' ),
				'type' => 'color',
			),
			'caption_font' => array(
				'default' => 'AvenirNext-Italic',
				'label' => __( 'Caption font face', 'apple-news' ),
				'type' => 'font',
			),
			'caption_line_height' => array(
				'default' => 24,
				'label' => __( 'Caption line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'caption_size' => array(
				'default' => 16,
				'label' => __( 'Caption font size', 'apple-news' ),
				'type' => 'integer',
			),
			'caption_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Caption tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'dropcap_background_color' => array(
				'default' => '',
				'label' => __( 'Drop cap background color', 'apple-news' ),
				'type' => 'color',
			),
			'dropcap_color' => array(
				'default' => '#4f4f4f',
				'label' => __( 'Drop cap font color', 'apple-news' ),
				'type' => 'color',
			),
			'dropcap_font' => array(
				'default' => 'AvenirNext-Bold',
				'label' => __( 'Dropcap font face', 'apple-news' ),
				'type' => 'font',
			),
			'dropcap_number_of_characters' => array(
				'default' => 1,
				'label' => __( 'Drop cap number of characters', 'apple-news' ),
				'type' => 'integer',
			),
			'dropcap_number_of_lines' => array(
				'default' => 4,
				'description' => __( 'Must be an integer between 2 and 10. Actual number of lines occupied will vary based on device size.', 'apple-news' ),
				'label' => __( 'Drop cap number of lines', 'apple-news' ),
				'type' => 'integer',
			),
			'dropcap_number_of_raised_lines' => array(
				'default' => 0,
				'label' => __( 'Drop cap number of raised lines', 'apple-news' ),
				'type' => 'integer',
			),
			'dropcap_padding' => array(
				'default' => 5,
				'label' => __( 'Drop cap padding', 'apple-news' ),
				'type' => 'integer',
			),
			'enable_advertisement' => array(
				'default' => 'yes',
				'label' => __( 'Enable advertisements', 'apple-news' ),
				'type' => array( 'yes', 'no' ),
			),
			'gallery_type' => array(
				'default' => 'gallery',
				'label' => __( 'Gallery type', 'apple-news' ),
				'type' => array( 'gallery', 'mosaic' ),
			),
			'header1_color' => array(
				'default' => '#333333',
				'label' => __( 'Header 1 font color', 'apple-news' ),
				'type' => 'color',
			),
			'header1_font' => array(
				'default' => 'AvenirNext-Bold',
				'label' => __( 'Header 1 font face', 'apple-news' ),
				'type' => 'font',
			),
			'header1_line_height' => array(
				'default' => 52,
				'label' => __( 'Header 1 line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'header1_size' => array(
				'default' => 48,
				'label' => __( 'Header 1 font size', 'apple-news' ),
				'type' => 'integer',
			),
			'header1_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Header 1 tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'header2_color' => array(
				'default' => '#333333',
				'label' => __( 'Header 2 font color', 'apple-news' ),
				'type' => 'color',
			),
			'header2_font' => array(
				'default' => 'AvenirNext-Bold',
				'label' => __( 'Header 2 font face', 'apple-news' ),
				'type' => 'font',
			),
			'header2_line_height' => array(
				'default' => 36,
				'label' => __( 'Header 2 line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'header2_size' => array(
				'default' => 32,
				'label' => __( 'Header 2 font size', 'apple-news' ),
				'type' => 'integer',
			),
			'header2_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Header 2 tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'header3_color' => array(
				'default' => '#333333',
				'label' => __( 'Header 3 font color', 'apple-news' ),
				'type' => 'color',
			),
			'header3_font' => array(
				'default' => 'AvenirNext-Bold',
				'label' => __( 'Header 3 font face', 'apple-news' ),
				'type' => 'font',
			),
			'header3_line_height' => array(
				'default' => 28,
				'label' => __( 'Header 3 line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'header3_size' => array(
				'default' => 24,
				'label' => __( 'Header 3 font size', 'apple-news' ),
				'type' => 'integer',
			),
			'header3_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Header 3 tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'header4_color' => array(
				'default' => '#333333',
				'label' => __( 'Header 4 font color', 'apple-news' ),
				'type' => 'color',
			),
			'header4_font' => array(
				'default' => 'AvenirNext-Bold',
				'label' => __( 'Header 4 font face', 'apple-news' ),
				'type' => 'font',
			),
			'header4_line_height' => array(
				'default' => 26,
				'label' => __( 'Header 4 line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'header4_size' => array(
				'default' => 21,
				'label' => __( 'Header 4 font size', 'apple-news' ),
				'type' => 'integer',
			),
			'header4_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Header 4 tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'header5_color' => array(
				'default' => '#333333',
				'label' => __( 'Header 5 font color', 'apple-news' ),
				'type' => 'color',
			),
			'header5_font' => array(
				'default' => 'AvenirNext-Bold',
				'label' => __( 'Header 5 font face', 'apple-news' ),
				'type' => 'font',
			),
			'header5_line_height' => array(
				'default' => 24,
				'label' => __( 'Header 5 line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'header5_size' => array(
				'default' => 18,
				'label' => __( 'Header 5 font size', 'apple-news' ),
				'type' => 'integer',
			),
			'header5_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Header 5 tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'header6_color' => array(
				'default' => '#333333',
				'label' => __( 'Header 6 font color', 'apple-news' ),
				'type' => 'color',
			),
			'header6_font' => array(
				'default' => 'AvenirNext-Bold',
				'label' => __( 'Header 6 font face', 'apple-news' ),
				'type' => 'font',
			),
			'header6_line_height' => array(
				'default' => 22,
				'label' => __( 'Header 6 line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'header6_size' => array(
				'default' => 16,
				'label' => __( 'Header 6 font size', 'apple-news' ),
				'type' => 'integer',
			),
			'header6_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Header 6 tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'initial_dropcap' => array(
				'default' => 'yes',
				'label' => __( 'Use initial drop cap', 'apple-news' ),
				'type' => array( 'yes', 'no' ),
			),
			'json_templates' => array(
				'default' => array(),
				'type' => 'hidden',
			),
			'layout_gutter' => array(
				'default' => 20,
				'label' => __( 'Layout gutter', 'apple-news' ),
				'type' => 'integer',
			),
			'layout_margin' => array(
				'default' => 100,
				'label' => __( 'Layout margin', 'apple-news' ),
				'type' => 'integer',
			),
			'layout_width' => array(
				'default' => 1024,
				'type' => 'hidden',
			),
			'meta_component_order' => array(
				'default' => array( 'cover', 'title', 'byline' ),
				'callback' => array( get_class( $this ), 'render_meta_component_order' ),
				'sanitize' => function ( $value ) {
					return array_map( 'sanitize_text_field', $value );
				},
			),
			'monospaced_color' => array(
				'default' => '#4f4f4f',
				'label' => __( 'Monospaced font color', 'apple-news' ),
				'type' => 'color',
			),
			'monospaced_font' => array(
				'default' => 'Menlo-Regular',
				'label' => __( 'Monospaced font face', 'apple-news' ),
				'type' => 'font',
			),
			'monospaced_line_height' => array(
				'default' => 20,
				'label' => __( 'Monospaced line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'monospaced_size' => array(
				'default' => 16,
				'label' => __( 'Monospaced font size', 'apple-news' ),
				'type' => 'integer',
			),
			'monospaced_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Monospaced tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'pullquote_border_color' => array(
				'default' => '#53585f',
				'label' => __( 'Pull quote border color', 'apple-news' ),
				'type' => 'color',
			),
			'pullquote_border_style' => array(
				'default' => 'solid',
				'label' => __( 'Pull quote border style', 'apple-news' ),
				'type' => array( 'solid', 'dashed', 'dotted', 'none' ),
			),
			'pullquote_border_width' => array(
				'default' => 3,
				'label' => __( 'Pull quote border width', 'apple-news' ),
				'type' => 'integer',
			),
			'pullquote_color' => array(
				'default' => '#53585f',
				'label' => __( 'Pull quote color', 'apple-news' ),
				'type' => 'color',
			),
			'pullquote_font' => array(
				'default' => 'AvenirNext-Bold',
				'label' => __( 'Pullquote font face', 'apple-news' ),
				'type' => 'font',
			),
			'pullquote_hanging_punctuation' => array(
				'default' => 'no',
				'description' => __( 'If set to "yes," adds smart quotes (if not already present) and sets the hanging punctuation option to true.', 'apple-news' ),
				'label' => __( 'Pullquote hanging punctuation', 'apple-news' ),
				'type' => array( 'no', 'yes' ),
			),
			'pullquote_line_height' => array(
				'default' => 48,
				'label' => __( 'Pull quote line height', 'apple-news' ),
				'sanitize' => 'floatval',
				'type' => 'float',
			),
			'pullquote_size' => array(
				'default' => 48,
				'label' => __( 'Pull quote font size', 'apple-news' ),
				'type' => 'integer',
			),
			'pullquote_tracking' => array(
				'default' => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label' => __( 'Pullquote tracking', 'apple-news' ),
				'type' => 'integer',
			),
			'pullquote_transform' => array(
				'default' => 'uppercase',
				'label' => __( 'Pull quote transformation', 'apple-news' ),
				'type' => array( 'none', 'uppercase' ),
			),
		);
	}
}
