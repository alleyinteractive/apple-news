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
	 * Key for the active theme.
	 *
	 * @var string
	 */
	const ACTIVE_KEY = 'apple_news_active_theme';

	/**
	 * Key for the theme index.
	 *
	 * @var string
	 */
	const INDEX_KEY = 'apple_news_installed_themes';

	/**
	 * All available iOS fonts.
	 *
	 * @since 0.4.0
	 * @access private
	 * @var array
	 */
	private static $fonts = [
		'AcademyEngravedLetPlain',
		'AlNile',
		'AlNile-Bold',
		'AmericanTypewriter',
		'AmericanTypewriter-Bold',
		'AmericanTypewriter-Condensed',
		'AmericanTypewriter-CondensedBold',
		'AmericanTypewriter-CondensedLight',
		'AmericanTypewriter-Light',
		'AppleColorEmoji',
		'AppleSDGothicNeo-Bold',
		'AppleSDGothicNeo-Light',
		'AppleSDGothicNeo-Medium',
		'AppleSDGothicNeo-Medium',
		'AppleSDGothicNeo-Regular',
		'AppleSDGothicNeo-SemiBold',
		'AppleSDGothicNeo-Thin',
		'Arial-BoldItalicMT',
		'Arial-BoldMT',
		'Arial-ItalicMT',
		'ArialHebrew',
		'ArialHebrew-Bold',
		'ArialHebrew-Light',
		'ArialMT',
		'ArialRoundedMTBold',
		'Avenir-Black',
		'Avenir-BlackOblique',
		'Avenir-Book',
		'Avenir-BookOblique',
		'Avenir-Heavy',
		'Avenir-HeavyOblique',
		'Avenir-Light',
		'Avenir-LightOblique',
		'Avenir-Medium',
		'Avenir-MediumOblique',
		'Avenir-Oblique',
		'Avenir-Roman',
		'AvenirNext-Bold',
		'AvenirNext-Bold',
		'AvenirNext-BoldItalic',
		'AvenirNext-BoldItalic',
		'AvenirNext-DemiBold',
		'AvenirNext-DemiBold',
		'AvenirNext-DemiBoldItalic',
		'AvenirNext-DemiBoldItalic',
		'AvenirNext-Heavy',
		'AvenirNext-Heavy',
		'AvenirNext-HeavyItalic',
		'AvenirNext-HeavyItalic',
		'AvenirNext-Italic',
		'AvenirNext-Italic',
		'AvenirNext-Medium',
		'AvenirNext-Medium',
		'AvenirNext-MediumItalic',
		'AvenirNext-MediumItalic',
		'AvenirNext-Regular',
		'AvenirNext-Regular',
		'AvenirNext-UltraLight',
		'AvenirNext-UltraLight',
		'AvenirNext-UltraLightItalic',
		'AvenirNext-UltraLightItalic',
		'BanglaSangamMN',
		'BanglaSangamMN-Bold',
		'Baskerville',
		'Baskerville-Bold',
		'Baskerville-BoldItalic',
		'Baskerville-Italic',
		'Baskerville-SemiBold',
		'Baskerville-SemiBoldItalic',
		'BodoniSvtyTwoITCTT-Bold',
		'BodoniSvtyTwoITCTT-Book',
		'BodoniSvtyTwoITCTT-BookIta',
		'BodoniSvtyTwoOSITCTT-Bold',
		'BodoniSvtyTwoOSITCTT-Book',
		'BodoniSvtyTwoOSITCTT-BookIt',
		'BodoniSvtyTwoSCITCTT-Book',
		'BradleyHandITCTT-Bold',
		'ChalkboardSE-Bold',
		'ChalkboardSE-Light',
		'ChalkboardSE-Regular',
		'Chalkduster',
		'Cochin',
		'Cochin-Bold',
		'Cochin-BoldItalic',
		'Cochin-Italic',
		'Copperplate',
		'Copperplate-Bold',
		'Copperplate-Light',
		'Courier',
		'Courier-Bold',
		'Courier-BoldOblique',
		'Courier-Oblique',
		'CourierNewPS-BoldItalicMT',
		'CourierNewPS-BoldMT',
		'CourierNewPS-ItalicMT',
		'CourierNewPSMT',
		'Damascus',
		'DamascusBold',
		'DamascusLight',
		'DamascusMedium',
		'DamascusSemiBold',
		'DevanagariSangamMN',
		'DevanagariSangamMN-Bold',
		'Didot',
		'Didot-Bold',
		'Didot-Italic',
		'DINAlternate-Bold',
		'DINCondensed-Bold',
		'DiwanMishafi',
		'EuphemiaUCAS',
		'EuphemiaUCAS-Bold',
		'EuphemiaUCAS-Italic',
		'Farah',
		'Futura-CondensedExtraBold',
		'Futura-CondensedMedium',
		'Futura-Medium',
		'Futura-MediumItalic',
		'GeezaPro',
		'GeezaPro-Bold',
		'Georgia',
		'Georgia-Bold',
		'Georgia-BoldItalic',
		'Georgia-Italic',
		'GillSans',
		'GillSans-Bold',
		'GillSans-BoldItalic',
		'GillSans-Italic',
		'GillSans-Light',
		'GillSans-LightItalic',
		'GujaratiSangamMN',
		'GujaratiSangamMN-Bold',
		'GurmukhiMN',
		'GurmukhiMN-Bold',
		'Helvetica',
		'Helvetica-Bold',
		'Helvetica-BoldOblique',
		'Helvetica-Light',
		'Helvetica-LightOblique',
		'Helvetica-Oblique',
		'HelveticaNeue',
		'HelveticaNeue-Bold',
		'HelveticaNeue-BoldItalic',
		'HelveticaNeue-CondensedBlack',
		'HelveticaNeue-CondensedBold',
		'HelveticaNeue-Italic',
		'HelveticaNeue-Light',
		'HelveticaNeue-LightItalic',
		'HelveticaNeue-Medium',
		'HelveticaNeue-MediumItalic',
		'HelveticaNeue-Thin',
		'HelveticaNeue-ThinItalic',
		'HelveticaNeue-UltraLight',
		'HelveticaNeue-UltraLightItalic',
		'HiraginoSans-W3',
		'HiraginoSans-W6',
		'HiraMinProN-W3',
		'HiraMinProN-W6',
		'HoeflerText-Black',
		'HoeflerText-BlackItalic',
		'HoeflerText-Italic',
		'HoeflerText-Regular',
		'IowanOldStyle-Bold',
		'IowanOldStyle-BoldItalic',
		'IowanOldStyle-Italic',
		'IowanOldStyle-Roman',
		'Kailasa',
		'Kailasa-Bold',
		'KannadaSangamMN',
		'KannadaSangamMN-Bold',
		'KhmerSangamMN',
		'KohinoorDevanagari-Light',
		'LaoSangamMN',
		'MalayalamSangamMN',
		'MalayalamSangamMN-Bold',
		'Marion-Bold',
		'Marion-Italic',
		'Marion-Regular',
		'MarkerFelt-Thin',
		'MarkerFelt-Wide',
		'Menlo-Bold',
		'Menlo-BoldItalic',
		'Menlo-Italic',
		'Menlo-Regular',
		'Noteworthy-Bold',
		'Noteworthy-Light',
		'Optima-Bold',
		'Optima-BoldItalic',
		'Optima-ExtraBlack',
		'Optima-Italic',
		'Optima-Regular',
		'OriyaSangamMN',
		'OriyaSangamMN-Bold',
		'Palatino-Bold',
		'Palatino-BoldItalic',
		'Palatino-Italic',
		'Palatino-Roman',
		'Papyrus',
		'Papyrus-Condensed',
		'PartyLetPlain',
		'SavoyeLetPlain',
		'SinhalaSangamMN',
		'SinhalaSangamMN-Bold',
		'SnellRoundhand',
		'SnellRoundhand-Black',
		'SnellRoundhand-Bold',
		'STHeitiSC-Light',
		'STHeitiSC-Medium',
		'STHeitiTC-Light',
		'STHeitiTC-Medium',
		'Superclarendon-Black',
		'Superclarendon-BlackItalic',
		'Superclarendon-Bold',
		'Superclarendon-BoldItalic',
		'Superclarendon-Italic',
		'Superclarendon-Light',
		'Superclarendon-LightItalic',
		'Superclarendon-Regular',
		'Symbol',
		'TamilSangamMN',
		'TamilSangamMN-Bold',
		'TeluguSangamMN',
		'TeluguSangamMN-Bold',
		'Thonburi',
		'Thonburi-Bold',
		'Thonburi-Light',
		'TimesNewRomanPS-BoldItalicMT',
		'TimesNewRomanPS-BoldMT',
		'TimesNewRomanPS-ItalicMT',
		'TimesNewRomanPSMT',
		'Trebuchet-BoldItalic',
		'TrebuchetMS',
		'TrebuchetMS-Bold',
		'TrebuchetMS-Italic',
		'Verdana',
		'Verdana-Bold',
		'Verdana-BoldItalic',
		'Verdana-Italic',
		'ZapfDingbatsITC',
		'Zapfino',
	];

	/**
	 * Option group configuration, to be used when printing fields.
	 *
	 * @var array
	 */
	private static $groups = [];

	/**
	 * Theme options configuration.
	 *
	 * @var array
	 */
	private static $options = [];

	/**
	 * Theme in current usage.
	 *
	 * @access private
	 * @var self
	 */
	private static $used;

	/**
	 * Theme name in current usage.
	 *
	 * @access private
	 * @var string
	 */
	private static $used_name;

	/**
	 * Tracks whether or not a dropcap determination has been made.
	 * Used to differentiate the first paragraph while parsing body text.
	 *
	 * @access public
	 * @var bool
	 */
	public $dropcap_applied = false;

	/**
	 * Keeps track of the last error message generated.
	 *
	 * @access private
	 * @var string
	 */
	private $last_error = '';

	/**
	 * The name of this theme.
	 *
	 * @access private
	 * @var string
	 */
	private $name = '';

	/**
	 * Values for theme options for this theme.
	 *
	 * @access private
	 * @var array
	 */
	private $values = [];

	/**
	 * Gets the active theme name.
	 *
	 * @access public
	 * @return string The name of the active theme.
	 */
	public static function get_active_theme_name() {
		return get_option( self::ACTIVE_KEY, '' );
	}

	/**
	 * Gets the list of iOS fonts.
	 *
	 * @access public
	 * @return array The list of iOS fonts.
	 */
	public static function get_fonts() {
		/**
		 * Allows the font list to be filtered, so that any custom
		 * fonts that have been approved by Apple and added to your
		 * channel will be able to be added to the list for selection.
		 *
		 * Use this filter to add a custom font that has been loaded into your
		 * Apple News channel. The font must be available on your system. TrueType
		 * font names must be used (which may differ from the filename of the font
		 * itself).
		 *
		 * @since 1.4.0
		 *
		 * @param array $fonts An array of TrueType font names.
		 */
		return apply_filters( 'apple_news_fonts_list', self::$fonts );
	}

	/**
	 * Given a meta component slug, returns its human-readable name.
	 *
	 * @param string $slug The meta component slug to look up.
	 *
	 * @return string The human-readable component name.
	 */
	public static function get_meta_component_name( $slug ) {
		switch ( $slug ) {
			case 'author':
				return __( 'Author', 'apple-news' );
			case 'byline':
				return __( 'Combined Date and Author', 'apple-news' );
			case 'cover':
				return __( 'Cover', 'apple-news' );
			case 'date':
				return __( 'Date', 'apple-news' );
			case 'intro':
				return __( 'Intro', 'apple-news' );
			case 'slug':
				return __( 'Slug', 'apple-news' );
			case 'title':
				return __( 'Title', 'apple-news' );
			default:
				return ucwords( $slug );
		}
	}

	/**
	 * Returns an array of configurable options for themes.
	 *
	 * @access public
	 * @return array Configurable options for themes.
	 */
	public static function get_options() {

		// If options have not been initialized, initialize them now.
		if ( empty( self::$options ) ) {
			self::initialize_options();
		}

		return self::$options;
	}

	/**
	 * Gets a list of registered themes.
	 *
	 * @access public
	 * @return array The theme registry.
	 */
	public static function get_registry() {

		// Attempt to get the registry.
		$registry = get_option( self::INDEX_KEY );
		if ( empty( $registry ) || ! is_array( $registry ) ) {
			return [];
		}

		return self::sort_registry( $registry );
	}

	/**
	 * Gets the theme in use for this session.
	 *
	 * @access public
	 */
	public static function get_used() {

		// Determine if a theme is already set.
		if ( ! empty( self::$used ) && self::$used instanceof self ) {
			return self::$used;
		}

		// Set the default.
		$theme_name = self::get_active_theme_name();
		$theme      = new \Apple_Exporter\Theme();
		$theme->set_name( $theme_name );
		$theme->load();
		$theme->use_this();

		return self::$used;
	}

	/**
	 * Renders the meta component order field.
	 *
	 * @param \Apple_Exporter\Theme $theme The theme for which to render the order.
	 * @access public
	 */
	public static function render_meta_component_order( $theme ) {

		/* phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable */
		$options = self::get_options();

		// Get the current order.
		$component_order = $theme->get_value( 'meta_component_order' );
		if ( empty( $component_order ) || ! is_array( $component_order ) ) {
			$component_order = [];
		}

		// Get inactive components.
		$inactive_components = array_diff(
			$options['meta_component_order']['all_options'],
			$component_order
		);

		/* phpcs:enable */

		// Load the template.
		include dirname( dirname( plugin_dir_path( __FILE__ ) ) )
			. '/admin/partials/field-meta-component-order.php';
	}

	/**
	 * Determine if a theme with a given name exists.
	 *
	 * @param string $name The name of the theme to check.
	 *
	 * @access public
	 * @return bool True if the theme exists, false if not.
	 */
	public static function theme_exists( $name ) {
		return ( in_array( $name, self::get_registry(), true ) );
	}

	/**
	 * Gets the name of a theme key used in the options table based on a name.
	 *
	 * @param string $name The name to use when generating the key.
	 *
	 * @access public
	 * @return string The compiled key.
	 */
	public static function theme_key( $name ) {
		return 'apple_news_theme_' . md5( $name );
	}

	/**
	 * Sorts a registry array, ensuring that the active theme is first.
	 *
	 * @param array $registry The registry to sort.
	 *
	 * @return array The sorted registry array.
	 */
	private static function sort_registry( $registry ) {

		// Sort the regsitry.
		sort( $registry );

		// Ensure the active theme is first.
		$active_theme     = self::get_active_theme_name();
		$active_theme_key = array_search( $active_theme, $registry, true );
		if ( ! empty( $active_theme_key ) ) {
			unset( $registry[ $active_theme_key ] );
			array_unshift( $registry, $active_theme );
		}

		return $registry;
	}

	/**
	 * Initializes the options array with values.
	 *
	 * @access private
	 */
	private static function initialize_options() {
		self::$options = [
			'ad_frequency'                       => [
				'default'     => 5,
				'description' => __( 'A number between 1 and 10 defining the frequency for automatically inserting dynamic advertisements into articles. For more information, see the <a href="https://developer.apple.com/documentation/apple_news/advertisementautoplacement" target="_blank">Apple News Format Reference</a>.', 'apple-news' ),
				'label'       => __( 'Ad Frequency', 'apple-news' ),
				'type'        => 'integer',
			],
			'ad_margin'                          => [
				'default'     => 15,
				'description' => __( 'The margin to use above and below inserted ads.', 'apple-news' ),
				'label'       => __( 'Ad Margin', 'apple-news' ),
				'type'        => 'integer',
			],
			'author_color'                       => [
				'default' => '#7c7c7c',
				'label'   => __( 'Author font color', 'apple-news' ),
				'type'    => 'color',
			],
			'author_color_dark'                  => [
				'default' => '',
				'label'   => __( 'Author font color', 'apple-news' ),
				'type'    => 'color',
			],
			'author_font'                        => [
				'default' => 'AvenirNext-Medium',
				'label'   => __( 'Author font face', 'apple-news' ),
				'type'    => 'font',
			],
			'author_format'                      => [
				'default'     => 'By #author#',
				'description' => __( 'Set the byline format. #author# denotes the location of the author name. The default format is "By #author#. Note that byline format updates only preview on save.', 'apple-news' ),
				'label'       => __( 'Author format', 'apple-news' ),
				'type'        => 'text',
			],
			'author_line_height'                 => [
				'default' => 24.0,
				'label'   => __( 'Author line height', 'apple-news' ),
				'type'    => 'float',
			],
			'author_link_color'                  => [
				'default' => '#7c7c7c',
				'label'   => __( 'Author URL font color', 'apple-news' ),
				'type'    => 'color',
			],
			'author_link_color_dark'             => [
				'default' => '',
				'label'   => __( 'Author URL font color ', 'apple-news' ),
				'type'    => 'color',
			],
			'author_links'                       => [
				'default' => 'no',
				'label'   => __( 'Hyperlink author names?', 'apple-news' ),
				'options' => [ 'yes', 'no' ],
				'type'    => 'select',
			],
			'author_size'                        => [
				'default' => 13,
				'label'   => __( 'Author font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'author_tracking'                    => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Author tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'blockquote_background_color'        => [
				'default' => '#e1e1e1',
				'label'   => __( 'Blockquote background color', 'apple-news' ),
				'type'    => 'color',
			],
			'blockquote_background_color_dark'   => [
				'default' => '',
				'label'   => __( 'Blockquote background color', 'apple-news' ),
				'type'    => 'color',
			],
			'blockquote_border_color'            => [
				'default' => '#4f4f4f',
				'label'   => __( 'Blockquote border color', 'apple-news' ),
				'type'    => 'color',
			],
			'blockquote_border_color_dark'       => [
				'default' => '',
				'label'   => __( 'Blockquote border color', 'apple-news' ),
				'type'    => 'color',
			],
			'blockquote_border_style'            => [
				'default' => 'solid',
				'label'   => __( 'Blockquote border style', 'apple-news' ),
				'options' => [ 'solid', 'dashed', 'dotted', 'none' ],
				'type'    => 'select',
			],
			'blockquote_border_width'            => [
				'default' => 3,
				'label'   => __( 'Blockquote border width', 'apple-news' ),
				'type'    => 'integer',
			],
			'blockquote_color'                   => [
				'default' => '#4f4f4f',
				'label'   => __( 'Blockquote color', 'apple-news' ),
				'type'    => 'color',
			],
			'blockquote_color_dark'              => [
				'default' => '',
				'label'   => __( 'Blockquote color', 'apple-news' ),
				'type'    => 'color',
			],
			'blockquote_font'                    => [
				'default' => 'AvenirNext-Regular',
				'label'   => __( 'Blockquote font face', 'apple-news' ),
				'type'    => 'font',
			],
			'blockquote_line_height'             => [
				'default' => 24.0,
				'label'   => __( 'Blockquote line height', 'apple-news' ),
				'type'    => 'float',
			],
			'blockquote_size'                    => [
				'default' => 18,
				'label'   => __( 'Blockquote font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'blockquote_tracking'                => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Blockquote tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'body_background_color'              => [
				'default' => '#fafafa',
				'label'   => __( 'Body background color', 'apple-news' ),
				'type'    => 'color',
			],
			'body_background_color_dark'         => [
				'default' => '',
				'label'   => __( 'Body background color', 'apple-news' ),
				'type'    => 'color',
			],
			'body_color'                         => [
				'default' => '#4f4f4f',
				'label'   => __( 'Body font color', 'apple-news' ),
				'type'    => 'color',
			],
			'body_color_dark'                    => [
				'default' => '',
				'label'   => __( 'Body font color', 'apple-news' ),
				'type'    => 'color',
			],
			'body_font'                          => [
				'default' => 'AvenirNext-Regular',
				'label'   => __( 'Body font face', 'apple-news' ),
				'type'    => 'font',
			],
			'body_line_height'                   => [
				'default' => 24.0,
				'label'   => __( 'Body line height', 'apple-news' ),
				'type'    => 'float',
			],
			'body_link_color'                    => [
				'default' => '#428bca',
				'label'   => __( 'Body font hyperlink color', 'apple-news' ),
				'type'    => 'color',
			],
			'body_link_color_dark'               => [
				'default' => '',
				'label'   => __( 'Body font hyperlink color', 'apple-news' ),
				'type'    => 'color',
			],
			'body_orientation'                   => [
				'default'     => 'left',
				'description' => __( 'Controls margins on larger screens. Left orientation includes one column of margin on the right, right orientation includes one column of margin on the left, and center orientation includes one column of margin on either side.', 'apple-news' ),
				'label'       => __( 'Body orientation', 'apple-news' ),
				'options'     => [ 'left', 'center', 'right' ],
				'type'        => 'select',
			],
			'body_size'                          => [
				'default' => 18,
				'label'   => __( 'Body font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'body_tracking'                      => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Body tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'button_background_color'            => [
				'default' => '#ffffff',
				'label'   => __( 'Button background color', 'apple-news' ),
				'type'    => 'color',
			],
			'button_border_color'                => [
				'default' => '#000000',
				'label'   => __( 'Button border color', 'apple-news' ),
				'type'    => 'color',
			],
			'button_border_radius'               => [
				'default' => 18,
				'label'   => __( 'Button border radius', 'apple-news' ),
				'type'    => 'integer',
			],
			'button_border_width'                => [
				'default' => 1,
				'label'   => __( 'Button border width', 'apple-news' ),
				'type'    => 'integer',
			],
			'button_horizontal_alignment'        => [
				'default' => 'center',
				'label'   => __( 'Button alignment', 'apple-news' ),
				'options' => [ 'left', 'center', 'right' ],
				'type'    => 'select',
			],
			'button_text_color'                  => [
				'default' => '#000000',
				'label'   => __( 'Button text color', 'apple-news' ),
				'type'    => 'color',
			],
			'button_font_face'                   => [
				'default' => 'HelveticaNeue-Medium',
				'label'   => __( 'Button font face', 'apple-news' ),
				'type'    => 'font',
			],
			'button_font_size'                   => [
				'default' => 15,
				'label'   => __( 'Button font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'byline_color'                       => [
				'default' => '#7c7c7c',
				'label'   => __( 'Combined Date and Author font color', 'apple-news' ),
				'type'    => 'color',
			],
			'byline_color_dark'                  => [
				'default' => '',
				'label'   => __( 'Combined Date and Author font color', 'apple-news' ),
				'type'    => 'color',
			],
			'byline_font'                        => [
				'default' => 'AvenirNext-Medium',
				'label'   => __( 'Combined Date and Author font face', 'apple-news' ),
				'type'    => 'font',
			],
			'byline_format'                      => [
				'default'     => 'By #author# | #M j, Y | g:i A#',
				'description' => __( 'Set the byline format. Two tokens can be present, #author# to denote the location of the author name and a <a href="http://php.net/manual/en/function.date.php" target="blank">PHP date format</a> string also encapsulated by #. The default format is "by #author# | #M j, Y | g:i A#". Note that byline format updates only preview on save.', 'apple-news' ),
				'label'       => __( 'Combined Date and Author format', 'apple-news' ),
				'type'        => 'text',
			],
			'byline_line_height'                 => [
				'default' => 24.0,
				'label'   => __( 'Combined Date and Author line height', 'apple-news' ),
				'type'    => 'float',
			],
			'byline_size'                        => [
				'default' => 13,
				'label'   => __( 'Combined Date and Author font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'byline_tracking'                    => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Combined Date and Author tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'caption_color'                      => [
				'default' => '#4f4f4f',
				'label'   => __( 'Caption font color', 'apple-news' ),
				'type'    => 'color',
			],
			'caption_color_dark'                 => [
				'default' => '',
				'label'   => __( 'Caption font color', 'apple-news' ),
				'type'    => 'color',
			],
			'caption_font'                       => [
				'default' => 'AvenirNext-Italic',
				'label'   => __( 'Caption font face', 'apple-news' ),
				'type'    => 'font',
			],
			'caption_line_height'                => [
				'default' => 24.0,
				'label'   => __( 'Caption line height', 'apple-news' ),
				'type'    => 'float',
			],
			'caption_margin_bottom'              => [
				'default' => 25,
				'label'   => __( 'Margin below the caption', 'apple-news' ),
				'type'    => 'integer',
			],
			'caption_size'                       => [
				'default' => 16,
				'label'   => __( 'Caption font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'caption_tracking'                   => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Caption tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'cover_caption'                      => [
				'default' => false,
				'label'   => __( 'Enable caption on the Cover component', 'apple-news' ),
				'type'    => 'boolean',
			],
			'dark_mode_colors_heading'           => [
				'label'       => __( 'Dark Mode Colors', 'apple-news' ),
				'description' => __( 'Colors specific to Apple News Dark Mode', 'apple-news' ),
				'type'        => 'group_heading',
			],
			'dropcap_background_color'           => [
				'default' => '',
				'label'   => __( 'Drop cap background color', 'apple-news' ),
				'type'    => 'color',
			],
			'dropcap_background_color_dark'      => [
				'default' => '',
				'label'   => __( 'Drop cap background color', 'apple-news' ),
				'type'    => 'color',
			],
			'dropcap_color'                      => [
				'default' => '#4f4f4f',
				'label'   => __( 'Drop cap font color', 'apple-news' ),
				'type'    => 'color',
			],
			'dropcap_color_dark'                 => [
				'default' => '',
				'label'   => __( 'Drop cap font color', 'apple-news' ),
				'type'    => 'color',
			],
			'dropcap_font'                       => [
				'default' => 'AvenirNext-Bold',
				'label'   => __( 'Dropcap font face', 'apple-news' ),
				'type'    => 'font',
			],
			'dropcap_minimum'                    => [
				'default' => 100,
				'label'   => __( 'Minimum number of characters for dropcap to take effect.', 'apple-news' ),
				'type'    => 'integer',
			],
			'dropcap_minimum_opt_out'            => [
				'default' => 'no',
				'label'   => __( 'Opt out of conditional dropcap behavior.', 'apple-news' ),
				'options' => [ 'yes', 'no' ],
				'type'    => 'select',
			],
			'dropcap_number_of_characters'       => [
				'default' => 1,
				'label'   => __( 'Drop cap number of characters', 'apple-news' ),
				'type'    => 'integer',
			],
			'dropcap_number_of_lines'            => [
				'default'     => 4,
				'description' => __( 'Must be an integer between 2 and 10. Actual number of lines occupied will vary based on device size.', 'apple-news' ),
				'label'       => __( 'Drop cap number of lines', 'apple-news' ),
				'type'        => 'integer',
			],
			'dropcap_number_of_raised_lines'     => [
				'default' => 0,
				'label'   => __( 'Drop cap number of raised lines', 'apple-news' ),
				'type'    => 'integer',
			],
			'dropcap_padding'                    => [
				'default' => 5,
				'label'   => __( 'Drop cap padding', 'apple-news' ),
				'type'    => 'integer',
			],
			'enable_advertisement'               => [
				'default' => 'yes',
				'label'   => __( 'Enable advertisements', 'apple-news' ),
				'options' => [ 'yes', 'no' ],
				'type'    => 'select',
			],
			'gallery_type'                       => [
				'default' => 'gallery',
				'label'   => __( 'Gallery type', 'apple-news' ),
				'options' => [ 'gallery', 'mosaic' ],
				'type'    => 'select',
			],
			'header1_color'                      => [
				'default' => '#333333',
				'label'   => __( 'Header 1 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header1_color_dark'                 => [
				'default' => '',
				'label'   => __( 'Header 1 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header1_font'                       => [
				'default' => 'AvenirNext-Bold',
				'label'   => __( 'Header 1 font face', 'apple-news' ),
				'type'    => 'font',
			],
			'header1_line_height'                => [
				'default' => 52.0,
				'label'   => __( 'Header 1 line height', 'apple-news' ),
				'type'    => 'float',
			],
			'header1_size'                       => [
				'default' => 48,
				'label'   => __( 'Header 1 font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'header1_tracking'                   => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Header 1 tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'header2_color'                      => [
				'default' => '#333333',
				'label'   => __( 'Header 2 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header2_color_dark'                 => [
				'default' => '',
				'label'   => __( 'Header 2 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header2_font'                       => [
				'default' => 'AvenirNext-Bold',
				'label'   => __( 'Header 2 font face', 'apple-news' ),
				'type'    => 'font',
			],
			'header2_line_height'                => [
				'default' => 36.0,
				'label'   => __( 'Header 2 line height', 'apple-news' ),
				'type'    => 'float',
			],
			'header2_size'                       => [
				'default' => 32,
				'label'   => __( 'Header 2 font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'header2_tracking'                   => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Header 2 tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'header3_color'                      => [
				'default' => '#333333',
				'label'   => __( 'Header 3 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header3_color_dark'                 => [
				'default' => '',
				'label'   => __( 'Header 3 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header3_font'                       => [
				'default' => 'AvenirNext-Bold',
				'label'   => __( 'Header 3 font face', 'apple-news' ),
				'type'    => 'font',
			],
			'header3_line_height'                => [
				'default' => 28.0,
				'label'   => __( 'Header 3 line height', 'apple-news' ),
				'type'    => 'float',
			],
			'header3_size'                       => [
				'default' => 24,
				'label'   => __( 'Header 3 font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'header3_tracking'                   => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Header 3 tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'header4_color'                      => [
				'default' => '#333333',
				'label'   => __( 'Header 4 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header4_color_dark'                 => [
				'default' => '',
				'label'   => __( 'Header 4 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header4_font'                       => [
				'default' => 'AvenirNext-Bold',
				'label'   => __( 'Header 4 font face', 'apple-news' ),
				'type'    => 'font',
			],
			'header4_line_height'                => [
				'default' => 26.0,
				'label'   => __( 'Header 4 line height', 'apple-news' ),
				'type'    => 'float',
			],
			'header4_size'                       => [
				'default' => 21,
				'label'   => __( 'Header 4 font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'header4_tracking'                   => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Header 4 tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'header5_color'                      => [
				'default' => '#333333',
				'label'   => __( 'Header 5 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header5_color_dark'                 => [
				'default' => '',
				'label'   => __( 'Header 5 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header5_font'                       => [
				'default' => 'AvenirNext-Bold',
				'label'   => __( 'Header 5 font face', 'apple-news' ),
				'type'    => 'font',
			],
			'header5_line_height'                => [
				'default' => 24.0,
				'label'   => __( 'Header 5 line height', 'apple-news' ),
				'type'    => 'float',
			],
			'header5_size'                       => [
				'default' => 18,
				'label'   => __( 'Header 5 font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'header5_tracking'                   => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Header 5 tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'header6_color'                      => [
				'default' => '#333333',
				'label'   => __( 'Header 6 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header6_color_dark'                 => [
				'default' => '',
				'label'   => __( 'Header 6 font color', 'apple-news' ),
				'type'    => 'color',
			],
			'header6_font'                       => [
				'default' => 'AvenirNext-Bold',
				'label'   => __( 'Header 6 font face', 'apple-news' ),
				'type'    => 'font',
			],
			'header6_line_height'                => [
				'default' => 22.0,
				'label'   => __( 'Header 6 line height', 'apple-news' ),
				'type'    => 'float',
			],
			'header6_size'                       => [
				'default' => 16,
				'label'   => __( 'Header 6 font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'header6_tracking'                   => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Header 6 tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'initial_dropcap'                    => [
				'default' => 'yes',
				'label'   => __( 'Use initial drop cap', 'apple-news' ),
				'options' => [ 'yes', 'no' ],
				'type'    => 'select',
			],
			'json_templates'                     => [
				'default' => [],
				'hidden'  => true,
				'type'    => 'array',
			],
			'layout_columns'                     => [
				'default' => 0,
				'label'   => __( 'Layout columns', 'apple-news' ),
				'type'    => 'integer',
			],
			'layout_columns_override'            => [
				'default' => 'no',
				'label'   => __( 'Override computed value with user configured value set above?', 'apple-news' ),
				'options' => [ 'yes', 'no' ],
				'type'    => 'select',
			],
			'layout_gutter'                      => [
				'default' => 20,
				'label'   => __( 'Layout gutter', 'apple-news' ),
				'type'    => 'integer',
			],
			'layout_margin'                      => [
				'default' => 100,
				'label'   => __( 'Layout margin', 'apple-news' ),
				'type'    => 'integer',
			],
			'layout_width'                       => [
				'default' => 1024,
				'label'   => __( 'Layout width', 'apple-news' ),
				'type'    => 'integer',
			],
			'meta_component_order'               => [
				'default'     => [ 'cover', 'slug', 'title', 'author', 'date' ],
				'all_options' => [ 'cover', 'title', 'slug', 'byline', 'author', 'date', 'intro' ],
				'callback'    => [ get_called_class(), 'render_meta_component_order' ],
				'type'        => 'array',
			],
			'monospaced_color'                   => [
				'default' => '#4f4f4f',
				'label'   => __( 'Monospaced font color', 'apple-news' ),
				'type'    => 'color',
			],
			'monospaced_color_dark'              => [
				'default' => '',
				'label'   => __( 'Monospaced font color', 'apple-news' ),
				'type'    => 'color',
			],
			'monospaced_font'                    => [
				'default' => 'Menlo-Regular',
				'label'   => __( 'Monospaced font face', 'apple-news' ),
				'type'    => 'font',
			],
			'monospaced_line_height'             => [
				'default' => 20.0,
				'label'   => __( 'Monospaced line height', 'apple-news' ),
				'type'    => 'float',
			],
			'monospaced_size'                    => [
				'default' => 16,
				'label'   => __( 'Monospaced font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'monospaced_tracking'                => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Monospaced tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'date_color'                         => [
				'default' => '#7c7c7c',
				'label'   => __( 'Date font color', 'apple-news' ),
				'type'    => 'color',
			],
			'date_color_dark'                    => [
				'default' => '',
				'label'   => __( 'Date font color', 'apple-news' ),
				'type'    => 'color',
			],
			'date_font'                          => [
				'default' => 'AvenirNext-Medium',
				'label'   => __( 'Date font face', 'apple-news' ),
				'type'    => 'font',
			],
			'date_format'                        => [
				'default'     => '#M j, Y | g:i A#',
				'description' => __( 'Set the date format. <a href="http://php.net/manual/en/function.date.php" target="blank">PHP date format</a> string is encapsulated by #. The default format is "#M j, Y | g:i A#". Note that date format updates only preview on save.', 'apple-news' ),
				'label'       => __( 'Date format', 'apple-news' ),
				'type'        => 'text',
			],
			'date_line_height'                   => [
				'default' => 24.0,
				'label'   => __( 'Date line height', 'apple-news' ),
				'type'    => 'float',
			],
			'date_size'                          => [
				'default' => 13,
				'label'   => __( 'Date font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'date_tracking'                      => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Date tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'pullquote_border_color'             => [
				'default' => '#53585f',
				'label'   => __( 'Pull quote border color', 'apple-news' ),
				'type'    => 'color',
			],
			'pullquote_border_color_dark'        => [
				'default' => '',
				'label'   => __( 'Pull quote border color', 'apple-news' ),
				'type'    => 'color',
			],
			'pullquote_border_style'             => [
				'default' => 'solid',
				'label'   => __( 'Pull quote border style', 'apple-news' ),
				'options' => [ 'solid', 'dashed', 'dotted', 'none' ],
				'type'    => 'select',
			],
			'pullquote_border_width'             => [
				'default' => 3,
				'label'   => __( 'Pull quote border width', 'apple-news' ),
				'type'    => 'integer',
			],
			'pullquote_color'                    => [
				'default' => '#53585f',
				'label'   => __( 'Pull quote color', 'apple-news' ),
				'type'    => 'color',
			],
			'pullquote_color_dark'               => [
				'default' => '',
				'label'   => __( 'Pull quote color', 'apple-news' ),
				'type'    => 'color',
			],
			'pullquote_font'                     => [
				'default' => 'AvenirNext-Bold',
				'label'   => __( 'Pullquote font face', 'apple-news' ),
				'type'    => 'font',
			],
			'pullquote_hanging_punctuation'      => [
				'default'     => 'no',
				'description' => __( 'If set to "yes," adds smart quotes (if not already present) and sets the hanging punctuation option to true.', 'apple-news' ),
				'label'       => __( 'Pullquote hanging punctuation', 'apple-news' ),
				'options'     => [ 'no', 'yes' ],
				'type'        => 'select',
			],
			'pullquote_line_height'              => [
				'default' => 48.0,
				'label'   => __( 'Pull quote line height', 'apple-news' ),
				'type'    => 'float',
			],
			'pullquote_size'                     => [
				'default' => 48,
				'label'   => __( 'Pull quote font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'pullquote_tracking'                 => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Pullquote tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'pullquote_transform'                => [
				'default' => 'uppercase',
				'label'   => __( 'Pull quote transformation', 'apple-news' ),
				'options' => [ 'none', 'uppercase' ],
				'type'    => 'select',
			],
			'screenshot_url'                     => [
				'default'     => '',
				'description' => __( 'An optional URL to a screenshot of this theme. Should be a 1200x900 PNG.', 'apple-news' ),
				'label'       => __( 'Screenshot URL', 'apple-news' ),
				'type'        => 'text',
			],
			'slug_color'                         => [
				'default' => '#4f4f4f',
				'label'   => __( 'Slug font color', 'apple-news' ),
				'type'    => 'color',
			],
			'slug_color_dark'                    => [
				'default' => '',
				'label'   => __( 'Slug font color', 'apple-news' ),
				'type'    => 'color',
			],
			'slug_font'                          => [
				'default' => 'AvenirNext-Medium',
				'label'   => __( 'Slug font', 'apple-news' ),
				'type'    => 'font',
			],
			'slug_line_height'                   => [
				'default' => 26.0,
				'label'   => __( 'Slug line height', 'apple-news' ),
				'type'    => 'float',
			],
			'slug_size'                          => [
				'default' => 20,
				'label'   => __( 'Slug font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'slug_tracking'                      => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Slug tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'table_body_background_color'        => [
				'default' => '#fafafa',
				'label'   => __( 'Table body background color', 'apple-news' ),
				'type'    => 'color',
			],
			'table_body_background_color_dark'   => [
				'default' => '',
				'label'   => __( 'Table body background color', 'apple-news' ),
				'type'    => 'color',
			],
			'table_body_color'                   => [
				'default' => '#4f4f4f',
				'label'   => __( 'Table body font color', 'apple-news' ),
				'type'    => 'color',
			],
			'table_body_color_dark'              => [
				'default' => '',
				'label'   => __( 'Table body font color', 'apple-news' ),
				'type'    => 'color',
			],
			'table_body_font'                    => [
				'default' => 'AvenirNext-Regular',
				'label'   => __( 'Table body font face', 'apple-news' ),
				'type'    => 'font',
			],
			'table_body_horizontal_alignment'    => [
				'default' => 'left',
				'label'   => __( 'Table body horizontal alignment', 'apple-news' ),
				'options' => [ 'left', 'center', 'right' ],
				'type'    => 'select',
			],
			'table_body_line_height'             => [
				'default' => 24.0,
				'label'   => __( 'Table body line height', 'apple-news' ),
				'type'    => 'float',
			],
			'table_body_padding'                 => [
				'default' => 10.0,
				'label'   => __( 'Table body padding', 'apple-news' ),
				'type'    => 'float',
			],
			'table_body_size'                    => [
				'default' => 17,
				'label'   => __( 'Table body font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'table_body_tracking'                => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Table body tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'table_body_vertical_alignment'      => [
				'default' => 'center',
				'label'   => __( 'Table body vertical alignment', 'apple-news' ),
				'options' => [ 'top', 'center', 'bottom' ],
				'type'    => 'select',
			],
			'table_border_color'                 => [
				'default' => '#333333',
				'label'   => __( 'Table border color', 'apple-news' ),
				'type'    => 'color',
			],
			'table_border_color_dark'            => [
				'default' => '',
				'label'   => __( 'Table border color', 'apple-news' ),
				'type'    => 'color',
			],
			'table_border_style'                 => [
				'default' => 'solid',
				'label'   => __( 'Table border style', 'apple-news' ),
				'options' => [ 'solid', 'dashed', 'dotted', 'none' ],
				'type'    => 'select',
			],
			'table_border_width'                 => [
				'default' => 1.0,
				'label'   => __( 'Table border width', 'apple-news' ),
				'type'    => 'float',
			],
			'table_header_background_color'      => [
				'default' => '#e1e1e1',
				'label'   => __( 'Table header background color', 'apple-news' ),
				'type'    => 'color',
			],
			'table_header_background_color_dark' => [
				'default' => '',
				'label'   => __( 'Table header background color', 'apple-news' ),
				'type'    => 'color',
			],
			'table_header_color'                 => [
				'default' => '#4f4f4f',
				'label'   => __( 'Table header font color', 'apple-news' ),
				'type'    => 'color',
			],
			'table_header_color_dark'            => [
				'default' => '',
				'label'   => __( 'Table header font color', 'apple-news' ),
				'type'    => 'color',
			],
			'table_header_font'                  => [
				'default' => 'AvenirNext-Regular',
				'label'   => __( 'Table header font face', 'apple-news' ),
				'type'    => 'font',
			],
			'table_header_horizontal_alignment'  => [
				'default' => 'left',
				'label'   => __( 'Table header horizontal alignment', 'apple-news' ),
				'options' => [ 'left', 'center', 'right' ],
				'type'    => 'select',
			],
			'table_header_line_height'           => [
				'default' => 24.0,
				'label'   => __( 'Table header line height', 'apple-news' ),
				'type'    => 'float',
			],
			'table_header_padding'               => [
				'default' => 10.0,
				'label'   => __( 'Table header padding', 'apple-news' ),
				'type'    => 'float',
			],
			'table_header_size'                  => [
				'default' => 17,
				'label'   => __( 'Table header font size', 'apple-news' ),
				'type'    => 'integer',
			],
			'table_header_tracking'              => [
				'default'     => 0,
				'description' => __( '(Percentage of font size)', 'apple-news' ),
				'label'       => __( 'Table header tracking', 'apple-news' ),
				'type'        => 'integer',
			],
			'table_header_vertical_alignment'    => [
				'default' => 'center',
				'label'   => __( 'Table header vertical alignment', 'apple-news' ),
				'options' => [ 'top', 'center', 'bottom' ],
				'type'    => 'select',
			],
		];
	}

	/**
	 * Returns an array of all settings for this theme.
	 *
	 * @access public
	 * @return array
	 */
	public function all_settings() {

		// Loop through options and compile an array of all options with values.
		$all_settings = [];
		$options      = self::get_options();
		foreach ( array_keys( $options ) as $option_key ) {
			$all_settings[ $option_key ] = $this->get_value( $option_key );
		}

		return $all_settings;
	}

	/**
	 * Deletes this theme from the database and removes it from the theme registry.
	 *
	 * @access public
	 */
	public function delete() {

		// Delete the theme from the options table.
		delete_option( self::theme_key( $this->get_name() ) );

		// Remove the theme from the theme registry.
		$this->remove_from_registry( $this->get_name() );

		// Remove from used, if necessary.
		if ( self::$used_name === $this->get_name() ) {
			self::$used_name = null;
			self::$used      = null;
		}
	}

	/**
	 * When a component is displayed aligned relative to another one, slide the
	 * other component a few columns. This varies for centered and non-centered
	 * layouts, as centered layouts have more columns.
	 *
	 * @access public
	 * @return int The number of columns for aligned components to span.
	 */
	public function get_alignment_offset() {
		return ( 'center' === $this->get_value( 'body_orientation' ) ) ? 5 : 3;
	}

	/**
	 * Get the body column span.
	 *
	 * @access public
	 * @return int The number of columns for the body to span.
	 */
	public function get_body_column_span() {
		return ( 'center' === $this->get_value( 'body_orientation' ) ) ? 7 : 6;
	}

	/**
	 * Get the left margin column offset.
	 *
	 * @access public
	 * @return int The number of columns to offset on the left.
	 */
	public function get_body_offset() {
		switch ( $this->get_value( 'body_orientation' ) ) {
			case 'right':
				return $this->get_layout_columns() - $this->get_body_column_span();
			case 'center':
				return floor(
					( $this->get_layout_columns() - $this->get_body_column_span() ) / 2
				);
			default:
				return 0;
		}
	}

	/**
	 * Returns an array of groups of configurable options for themes.
	 *
	 * @access public
	 * @return array Groups of configurable options for themes.
	 */
	public function get_groups() {

		// If groups have not been initialized, initialize them now.
		if ( empty( self::$groups ) ) {
			$this->initialize_groups();
		}

		return self::$groups;
	}

	/**
	 * Retrieves the last error logged.
	 *
	 * @access public
	 * @return string The text of the last error.
	 */
	public function get_last_error() {
		return $this->last_error;
	}

	/**
	 * If user override enabled return layout_columns theme value.
	 * Else return the computed layout columns value.
	 *
	 * @access public
	 * @return int The number of layout columns to use.
	 */
	public function get_layout_columns() {
		if ( 'yes' === $this->get_value( 'layout_columns_override' ) && ! empty( $this->get_value( 'layout_columns' ) ) ) {
			return $this->get_value( 'layout_columns' );
		}
		return ( 'center' === $this->get_value( 'body_orientation' ) ) ? 9 : 7;
	}

	/**
	 * Gets the name of this theme.
	 *
	 * @return string The name of the theme.
	 */
	public function get_name() {

		// If no name is set, use the default.
		if ( empty( $this->name ) ) {
			$this->name = __( 'Default', 'apple-news' );
		}

		return $this->name;
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
		if ( isset( $this->values[ $option ] ) ) {
			return $this->values[ $option ];
		}

		// Attempt to fall back to the default.
		$options = self::get_options();
		if ( isset( $options[ $option ]['default'] ) ) {
			return $options[ $option ]['default'];
		}

		return null;
	}

	/**
	 * Returns true if this theme is using the default settings.
	 *
	 * @access public
	 * @return bool True if using the default settings, false if not.
	 */
	public function is_default() {

		// Loop over options and check values against defaults.
		foreach ( self::get_options() as $option => $option_config ) {

			// If the option is not specified, it is using the default.
			if ( ! isset( $this->values[ $option ] ) ) {
				continue;
			}

			// Ignore the screenshot URL. This gets set automatically on install but doesn't have a default value.
			if ( 'screenshot_url' === $option ) {
				continue;
			}

			// If the values don't match, it is not using the default.
			if ( $this->values[ $option ] !== $option_config['default'] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Loads theme information from provided values or the database.
	 *
	 * @param array $values Optional. Values to load. Defaults to a database load.
	 *
	 * @access public
	 * @return bool True on success, false on failure.
	 */
	public function load( $values = [] ) {

		// If no values were provided, attempt to load from the database.
		if ( empty( $values ) ) {
			$values = get_option( self::theme_key( $this->get_name() ) );
		}

		// Ensure that values are an array we can iterate over.
		if ( ! is_array( $values ) ) {
			return false;
		}

		// Loop over loaded values and add to local values.
		$options = self::get_options();
		foreach ( $values as $key => $value ) {

			// Skip any keys that don't exist in the options spec.
			if ( ! isset( $options[ $key ] ) ) {
				continue;
			}

			// Convert the format of the value based on type.
			switch ( $options[ $key ]['type'] ) {
				case 'boolean':
					$this->values[ $key ] = (bool) $value;
					break;
				case 'float':
					$this->values[ $key ] = (float) $value;
					break;
				case 'integer':
					$this->values[ $key ] = (int) $value;
					break;
				default:
					$this->values[ $key ] = $value;
					break;
			}
		}

		// Loop over local values and remove any that aren't present in loaded data.
		foreach ( $this->values as $key => $value ) {
			if ( ! isset( $values[ $key ] ) ) {
				unset( $this->values[ $key ] );
			}
		}

		return true;
	}

	/**
	 * Loads fields based on postdata.
	 *
	 * @access public
	 */
	public function load_postdata() {

		// Check the nonce.
		check_admin_referer( 'apple_news_save_edit_theme' );

		// Remove all configured values except for JSON templates.
		if ( ! empty( $this->values['json_templates'] )
			&& is_array( $this->values['json_templates'] )
		) {
			$this->values = [
				'json_templates' => $this->values['json_templates'],
			];
		} else {
			$this->values = [];
		}

		// Loop through options and extract each from postdata.
		$options = self::get_options();
		foreach ( $options as $option_key => $option ) {

			// If there is no value for this option key, skip it.
			if ( ! isset( $_POST[ $option_key ] ) ) {
				continue;
			}

			// Skip JSON templates, which will be validated separately.
			if ( 'json_templates' === $option_key ) {
				continue;
			}

			// Perform basic sanitization based on option type.
			switch ( $option['type'] ) {
				case 'array':
					if ( is_array( $_POST[ $option_key ] ) ) {
						$this->values[ $option_key ] = array_map(
							'sanitize_text_field',
							array_map(
								'wp_unslash',
								$_POST[ $option_key ] // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
							)
						);
					}

					break;

				case 'boolean':
					$this->values[ $option_key ] = (bool) $_POST[ $option_key ];

					break;

				case 'float':
					$this->values[ $option_key ] = (float) $_POST[ $option_key ];

					break;

				case 'integer':
					$this->values[ $option_key ] = (int) $_POST[ $option_key ];

					break;

				default:
					$this->values[ $option_key ] = sanitize_text_field(
						wp_unslash(
							$_POST[ $option_key ]
						)
					);

					break;
			}
		}

		// Handle empty meta_component_order.
		if ( ! isset( $_POST['meta_component_order'] ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
			&& ! empty( $_POST['meta_component_inactive'] ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		) {
			$this->values['meta_component_order'] = [];
		}
	}

	/**
	 * Rename this theme.
	 *
	 * @param string $name The name to rename to.
	 *
	 * @access public
	 * @return bool True on success, false on failure.
	 */
	public function rename( $name ) {

		// Get the list of installed themes and ensure the new name isn't taken.
		if ( self::theme_exists( $name ) ) {
			$this->log_error(
				sprintf(
					// translators: token is the theme name.
					__( 'Theme name %s is already in use.', 'apple-news' ),
					$name
				)
			);

			return false;
		}

		// Change the name of this theme and attempt to save.
		$old_name = $this->get_name();
		$this->set_name( $name );
		if ( ! $this->save() ) {
			return false;
		}

		// Remove the old theme.
		$old_theme = new self();
		$old_theme->set_name( $old_name );
		$old_theme->delete();

		// Refresh used if in active use.
		if ( $old_name === self::$used_name ) {
			$this->use_this();
		}

		// Update pointer to active theme, if this was the active theme.
		if ( self::get_active_theme_name() === $old_name ) {
			$this->set_active();
		}

		return true;
	}

	/**
	 * Saves the current theme.
	 *
	 * @access public
	 * @return bool True on success, false on failure.
	 */
	public function save() {

		// Ensure theme is valid before saving.
		if ( ! $this->validate() ) {
			return false;
		}

		// Save the theme.
		update_option( self::theme_key( $this->get_name() ), $this->values, false );

		// Add to the registry.
		$this->add_to_registry( $this->get_name() );

		// Refresh loaded theme, if currently in use.
		if ( self::$used_name === $this->get_name() ) {
			$this->use_this();
		}

		return true;
	}

	/**
	 * Sets the current theme as the active theme.
	 *
	 * @access public
	 * @return bool True on success, false on failure.
	 */
	public function set_active() {

		// Ensure that this theme is saved before setting it as active.
		$theme = new self();
		$theme->set_name( $this->get_name() );
		if ( ! $theme->load() ) {
			return false;
		}

		// Update the option that tracks the active theme to reference this theme.
		update_option( self::ACTIVE_KEY, $this->get_name(), false );

		return true;
	}

	/**
	 * Sets the theme name property.
	 *
	 * @param string $name The name to set.
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Sets a value for a theme option for this theme.
	 *
	 * @param string $option The option name for which to set a value.
	 * @param string $value The value to set.
	 *
	 * @access public
	 * @return bool True if successful, false if not.
	 */
	public function set_value( $option, $value ) {

		// Attempt to change the value.
		$starting_values         = $this->values;
		$this->values[ $option ] = $value;
		if ( $this->validate() ) {
			return true;
		}

		// Change was unsuccessful, so revert to old values.
		$this->values = $starting_values;

		return false;
	}

	/**
	 * Sets this theme as the theme to be used during current execution.
	 *
	 * @access public
	 */
	public function use_this() {
		self::$used_name = $this->get_name();
		self::$used      = $this;
	}

	/**
	 * Sanitizes and validates the values array.
	 *
	 * If an error is encountered, it will be saved in the $last_error property.
	 *
	 * @access public
	 * @return bool True if the values are valid, false if not.
	 */
	public function validate() {

		// If values is not an array, then the configuration is invalid.
		if ( ! is_array( $this->values ) ) {
			$this->log_error(
				__(
					'Theme values were not in array format.',
					'apple-news'
				)
			);

			return false;
		}

		// Loop through provided values and check each.
		$options = self::get_options();
		foreach ( $this->values as $key => &$value ) {

			// If the provided key is not in the valid options spec, mark invalid.
			if ( ! isset( $options[ $key ] ) ) {
				$this->log_error(
					sprintf(
						// translators: token is a setting key.
						__( 'An invalid setting was encountered: %s', 'apple-news' ),
						$key
					)
				);

				return false;
			}

			// Skip JSON templates for now, as they are validated separately.
			if ( 'json_templates' === $key ) {
				continue;
			}

			// Fork for sanitization type.
			switch ( $options[ $key ]['type'] ) {
				case 'array':
					// Ensure the provided value is actually an array.
					if ( ! is_array( $value ) ) {
						$this->log_error(
							sprintf(
								// translators: first token is the setting key, second token is the value type.
								__(
									'Array expected for setting %1$s, %2$s provided',
									'apple-news'
								),
								$key,
								gettype( $value )
							)
						);

						return false;
					}

					// Sanitize.
					$value = array_map( 'sanitize_text_field', $value );

					break;

				case 'boolean':
					$value = (bool) $value;

					break;

				case 'color':
					// Sanitize.
					$value = sanitize_text_field( $value );

					// Ensure the color value provided is valid.
					if ( false === preg_match( '/#([a-f0-9]{3}){1,2}\b/i', $value ) ) {
						$this->log_error(
							sprintf(
								// translators: first token is a value, second token is the setting key.
								__(
									'Invalid color value %1$s specified for setting %2$s',
									'apple-news'
								),
								$value,
								$key
							)
						);

						return false;
					}

					break;

				case 'float':
					$value = (float) $value;

					break;

				case 'font':
					// Sanitize.
					$value = sanitize_text_field( $value );

					// Ensure the named font is part of the allowlist.
					if ( ! in_array( $value, self::get_fonts(), true ) ) {
						$this->log_error(
							sprintf(
								// translators: first token is the value, second is the setting key.
								__(
									'Invalid font value %1$s specified for setting %2$s',
									'apple-news'
								),
								$value,
								$key
							)
						);

						return false;
					}

					break;

				case 'integer':
					$value = (int) $value;

					break;

				case 'select':
					// Sanitize.
					$value = sanitize_text_field( $value );

					// Ensure that the value is one of the allowed options.
					if ( ! in_array( $value, $options[ $key ]['options'], true ) ) {
						$this->log_error(
							sprintf(
								// translators: first token is the value, second token is the setting key.
								__( 'Invalid value %1$s specified for setting %2$s', 'apple-news' ),
								$value,
								$key
							)
						);

						return false;
					}

					break;

				default:
					$value = sanitize_text_field( $value );

					break;
			}
		}

		// Validate meta_component_order separately.
		if ( ! empty( $this->values['meta_component_order'] ) ) {

			// Ensure no values were provided other than what is permissible.
			foreach ( $this->values['meta_component_order'] as $component ) {
				if ( ! in_array( $component, $options['meta_component_order']['all_options'], true ) ) {
					$this->log_error(
						__( 'Invalid value for meta component order', 'apple-news' )
					);

					return false;
				}
			}
		}

		// Finally, validate JSON templates.
		return $this->validate_json_templates();
	}

	/**
	 * Adds a theme to the registry.
	 *
	 * @param string $name The name of the theme to add.
	 *
	 * @access private
	 */
	private function add_to_registry( $name ) {

		// Fetch the registry.
		$registry = self::get_registry();

		// Attempt to find the theme in the registry.
		$key = array_search( $name, $registry, true );
		if ( false !== $key ) {
			return;
		}

		// Add the theme from the registry.
		$registry[] = $name;

		// Sort the registry.
		$registry = self::sort_registry( $registry );

		// Update the registry.
		update_option( self::INDEX_KEY, $registry, false );
	}

	/**
	 * Initializes the groups array with values.
	 *
	 * @access private
	 */
	private function initialize_groups() {
		self::$groups = [
			'layout'          => [
				'label'       => __( 'Layout Spacing', 'apple-news' ),
				'description' => __( 'The spacing for the base layout of the exported articles', 'apple-news' ),
				'settings'    => [
					'layout_columns',
					'layout_columns_override',
					'layout_margin',
					'layout_gutter',
					'layout_width',
				],
			],
			'slug'            => [
				'label'       => __( 'Slug', 'apple-news' ),
				'description' => __( "The slug displays the article's section or topic", 'apple-news' ),
				'settings'    => [
					'slug_font',
					'slug_size',
					'slug_line_height',
					'slug_tracking',
					'slug_color',
					'dark_mode_colors_heading',
					'slug_color_dark',
				],
			],
			'body'            => [
				'label'    => __( 'Body', 'apple-news' ),
				'settings' => [
					'body_font',
					'body_size',
					'body_line_height',
					'body_tracking',
					'body_color',
					'body_link_color',
					'body_background_color',
					'body_orientation',
					'dark_mode_colors_heading',
					'body_color_dark',
					'body_link_color_dark',
					'body_background_color_dark',
				],
			],
			'dropcap'         => [
				'label'    => __( 'Drop Cap', 'apple-news' ),
				'settings' => [
					'initial_dropcap',
					'dropcap_minimum',
					'dropcap_minimum_opt_out',
					'dropcap_background_color',
					'dropcap_color',
					'dropcap_font',
					'dropcap_number_of_characters',
					'dropcap_number_of_lines',
					'dropcap_number_of_raised_lines',
					'dropcap_padding',
					'dark_mode_colors_heading',
					'dropcap_background_color_dark',
					'dropcap_color_dark',
				],
			],
			'date'            => [
				'label'       => __( 'Date', 'apple-news' ),
				'description' => __( "Displays the article's date", 'apple-news' ),
				'settings'    => [
					'date_font',
					'date_size',
					'date_line_height',
					'date_tracking',
					'date_color',
					'date_format',
					'dark_mode_colors_heading',
					'date_color_dark',
				],
			],
			'author'          => [
				'label'       => __( 'Author', 'apple-news' ),
				'description' => __( "Displays the article's author byline", 'apple-news' ),
				'settings'    => [
					'author_font',
					'author_size',
					'author_line_height',
					'author_tracking',
					'author_color',
					'author_format',
					'dark_mode_colors_heading',
					'author_color_dark',
				],
			],
			'button'          => [
				'label'    => __( 'Link Button', 'apple-news' ),
				'settings' => [
					'button_background_color',
					'button_text_color',
					'button_font_face',
					'button_font_size',
					'button_border_color',
					'button_border_radius',
					'button_border_width',
					'button_horizontal_alignment',
				],
			],
			'byline'          => [
				'label'       => __( 'Combined Date and Author', 'apple-news' ),
				'description' => __( "The Combined Date and Author displays both the article's author and date", 'apple-news' ),
				'settings'    => [
					'byline_font',
					'byline_size',
					'byline_line_height',
					'byline_tracking',
					'byline_color',
					'byline_format',
					'dark_mode_colors_heading',
					'byline_color_dark',
				],
			],
			'author_links'    => [
				'label'       => __( 'Author URL', 'apple-news' ),
				'description' => __( 'Displays links to authors archive.', 'apple-news' ),
				'settings'    => [
					'author_links',
					'author_link_color',
					'dark_mode_colors_heading',
					'author_link_color_dark',
				],
			],
			'heading1'        => [
				'label'    => __( 'Heading 1', 'apple-news' ),
				'settings' => [
					'header1_font',
					'header1_color',
					'header1_size',
					'header1_line_height',
					'header1_tracking',
					'dark_mode_colors_heading',
					'header1_color_dark',
				],
			],
			'heading2'        => [
				'label'    => __( 'Heading 2', 'apple-news' ),
				'settings' => [
					'header2_font',
					'header2_color',
					'header2_size',
					'header2_line_height',
					'header2_tracking',
					'dark_mode_colors_heading',
					'header2_color_dark',

				],
			],
			'heading3'        => [
				'label'    => __( 'Heading 3', 'apple-news' ),
				'settings' => [
					'header3_font',
					'header3_color',
					'header3_size',
					'header3_line_height',
					'header3_tracking',
					'dark_mode_colors_heading',
					'header3_color_dark',
				],
			],
			'heading4'        => [
				'label'    => __( 'Heading 4', 'apple-news' ),
				'settings' => [
					'header4_font',
					'header4_color',
					'header4_size',
					'header4_line_height',
					'header4_tracking',
					'dark_mode_colors_heading',
					'header4_color_dark',
				],
			],
			'heading5'        => [
				'label'    => __( 'Heading 5', 'apple-news' ),
				'settings' => [
					'header5_font',
					'header5_color',
					'header5_size',
					'header5_line_height',
					'header5_tracking',
					'dark_mode_colors_heading',
					'header5_color_dark',
				],
			],
			'heading6'        => [
				'label'    => __( 'Heading 6', 'apple-news' ),
				'settings' => [
					'header6_font',
					'header6_color',
					'header6_size',
					'header6_line_height',
					'header6_tracking',
					'dark_mode_colors_heading',
					'header6_color_dark',
				],
			],
			'caption'         => [
				'label'    => __( 'Image caption', 'apple-news' ),
				'settings' => [
					'cover_caption',
					'caption_font',
					'caption_size',
					'caption_line_height',
					'caption_tracking',
					'caption_color',
					'dark_mode_colors_heading',
					'caption_color_dark',
				],
			],
			'pullquote'       => [
				'label'       => __( 'Pull quote', 'apple-news' ),
				'description' => sprintf(
					'%s <a href="https://en.wikipedia.org/wiki/Pull_quote">%s</a>.',
					__( 'Articles can have an optional', 'apple-news' ),
					__( 'Pull quote', 'apple-news' )
				),
				'settings'    => [
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
					'dark_mode_colors_heading',
					'pullquote_color_dark',
					'pullquote_border_color_dark',
				],
			],
			'blockquote'      => [
				'label'    => __( 'Blockquote', 'apple-news' ),
				'settings' => [
					'blockquote_font',
					'blockquote_size',
					'blockquote_line_height',
					'blockquote_tracking',
					'blockquote_color',
					'blockquote_border_style',
					'blockquote_border_color',
					'blockquote_border_width',
					'blockquote_background_color',
					'dark_mode_colors_heading',
					'blockquote_color_dark',
					'blockquote_border_color_dark',
					'blockquote_background_color_dark',
				],
			],
			'table'           => [
				'label'    => __( 'Table (requires HTML support)', 'apple-news' ),
				'settings' => [
					'table_border_color',
					'table_border_style',
					'table_border_width',
					'table_body_background_color',
					'table_body_color',
					'table_body_font',
					'table_body_horizontal_alignment',
					'table_body_line_height',
					'table_body_padding',
					'table_body_size',
					'table_body_tracking',
					'table_body_vertical_alignment',
					'table_header_background_color',
					'table_header_color',
					'table_header_font',
					'table_header_horizontal_alignment',
					'table_header_line_height',
					'table_header_padding',
					'table_header_size',
					'table_header_tracking',
					'table_header_vertical_alignment',
					'dark_mode_colors_heading',
					'table_border_color_dark',
					'table_body_background_color_dark',
					'table_body_color_dark',
					'table_header_background_color_dark',
					'table_header_color_dark',
				],
			],
			'monospaced'      => [
				'label'    => __( 'Monospaced (<pre>, <code>, <samp>)', 'apple-news' ),
				'settings' => [
					'monospaced_font',
					'monospaced_size',
					'monospaced_line_height',
					'monospaced_tracking',
					'monospaced_color',
					'dark_mode_colors_heading',
					'monospaced_color_dark',
				],
			],
			'gallery'         => [
				'label'       => __( 'Gallery', 'apple-news' ),
				'description' => __( 'Can either be a standard gallery, or mosaic.', 'apple-news' ),
				'settings'    => [ 'gallery_type' ],
			],
			'advertisement'   => [
				'label'    => __( 'Advertisement', 'apple-news' ),
				'settings' => [
					'enable_advertisement',
					'ad_frequency',
					'ad_margin',
				],
			],
			'component_order' => [
				'label'    => __( 'Component Order', 'apple-news' ),
				'settings' => [ 'meta_component_order' ],
			],
			'screenshot'      => [
				'label'    => __( 'Screenshots', 'apple-news' ),
				'settings' => [ 'screenshot_url' ],
			],
		];
	}

	/**
	 * Sets the last error to the provided message.
	 *
	 * @param string $message The message to set.
	 *
	 * @access private
	 */
	private function log_error( $message ) {
		$this->last_error = $message;
	}

	/**
	 * Removes a theme from the registry.
	 *
	 * @param string $name The name of the theme to remove.
	 *
	 * @access private
	 */
	private function remove_from_registry( $name ) {

		// Fetch the registry.
		$registry = self::get_registry();

		// Attempt to find the theme in the registry.
		$key = array_search( $name, $registry, true );
		if ( false === $key ) {
			return;
		}

		// Remove the theme from the registry.
		unset( $registry[ $key ] );

		// Sort the registry.
		$registry = self::sort_registry( $registry );

		// Update the registry.
		update_option( self::INDEX_KEY, $registry, false );
	}

	/**
	 * Ensures that JSON templates defined in a theme spec are valid.
	 *
	 * @access private
	 * @return bool True on success, false on failure.
	 */
	private function validate_json_templates() {

		// If no JSON templates are defined, count as a success.
		if ( empty( $this->values['json_templates'] ) ) {
			return true;
		}

		// Get a list of components that may have customized JSON.
		$component_factory = new \Apple_Exporter\Component_Factory();
		$component_factory->initialize();
		$components = $component_factory::get_components();

		// Iterate over components and look for customized JSON for each.
		$invalid_components = $this->values['json_templates'];
		foreach ( $components as $component_class ) {

			// Negotiate the component key.
			$component     = new $component_class();
			$component_key = $component->get_component_name();

			// Determine if this component key is defined in this theme.
			if ( empty( $this->values['json_templates'][ $component_key ] )
				|| ! is_array( $this->values['json_templates'][ $component_key ] )
			) {
				continue;
			}

			// Loop through component key and validate.
			$current_component = &$this->values['json_templates'][ $component_key ];
			$specs             = $component->get_specs();
			foreach ( $specs as $spec_key => $spec ) {

				// Determine if the spec is defined as a JSON template in the theme.
				if ( empty( $current_component[ $spec_key ] )
					|| ! is_array( $current_component[ $spec_key ] )
				) {
					continue;
				}

				// Validate this spec.
				if ( ! $spec->validate( $current_component[ $spec_key ] ) ) {
					$this->log_error(
						sprintf(
							// translators: token is a combination of a component key and a spec key.
							__(
								'The spec for %s had invalid tokens and cannot be saved',
								'apple-news'
							),
							$component_key . '/' . $spec_key
						)
					);

					return false;
				}

				// Log this spec as valid.
				unset( $invalid_components[ $component_key ][ $spec_key ] );

				// Clean up array for this component key, if necessary.
				if ( isset( $invalid_components[ $component_key ] ) ) {
					$invalid_components[ $component_key ] = array_filter( $invalid_components[ $component_key ] );
				}

				// Clean up root-level components list.
				$invalid_components = array_filter( $invalid_components );
			}
		}

		// If there are any invalid components, fail.
		if ( ! empty( $invalid_components ) ) {
			$this->log_error(
				__( 'The theme file contained unsupported settings', 'apple-news' )
			);

			return false;
		}

		return true;
	}
}
