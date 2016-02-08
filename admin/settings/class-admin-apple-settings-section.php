<?php

/**
 * Describes a WordPress setting section
 *
 * @since 0.6.0
 */
class Admin_Apple_Settings_Section extends Apple_News {

	/**
	 * All available iOS fonts.
	 *
	 * @since 0.4.0
	 * @var array
	 * @access protected
	 */
	protected static $fonts = array(
		'AcademyEngravedLetPlain',
		'AlNile-Bold',
		'AlNile',
		'AmericanTypewriter',
		'AmericanTypewriter-Bold',
		'AmericanTypewriter-Condensed',
		'AmericanTypewriter-CondensedBold',
		'AmericanTypewriter-CondensedLight',
		'AmericanTypewriter-Light',
		'AppleColorEmoji',
		'AppleSDGothicNeo-Thin',
		'AppleSDGothicNeo-Light',
		'AppleSDGothicNeo-Regular',
		'AppleSDGothicNeo-Medium',
		'AppleSDGothicNeo-SemiBold',
		'AppleSDGothicNeo-Bold',
		'AppleSDGothicNeo-Medium',
		'ArialMT',
		'Arial-BoldItalicMT',
		'Arial-BoldMT',
		'Arial-ItalicMT',
		'ArialHebrew',
		'ArialHebrew-Bold',
		'ArialHebrew-Light',
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
		'AvenirNext-BoldItalic',
		'AvenirNext-DemiBold',
		'AvenirNext-DemiBoldItalic',
		'AvenirNext-Heavy',
		'AvenirNext-HeavyItalic',
		'AvenirNext-Italic',
		'AvenirNext-Medium',
		'AvenirNext-MediumItalic',
		'AvenirNext-Regular',
		'AvenirNext-UltraLight',
		'AvenirNext-UltraLightItalic',
		'AvenirNext-Bold',
		'AvenirNext-BoldItalic',
		'AvenirNext-DemiBold',
		'AvenirNext-DemiBoldItalic',
		'AvenirNext-Heavy',
		'AvenirNext-HeavyItalic',
		'AvenirNext-Italic',
		'AvenirNext-Medium',
		'AvenirNext-MediumItalic',
		'AvenirNext-Regular',
		'AvenirNext-UltraLight',
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
		'DBLCDTempBlack',
		'DINAlternate-Bold',
		'DINCondensed-Bold',
		'DamascusBold',
		'Damascus',
		'DamascusLight',
		'DamascusMedium',
		'DamascusSemiBold',
		'DevanagariSangamMN',
		'DevanagariSangamMN-Bold',
		'Didot',
		'Didot-Bold',
		'Didot-Italic',
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
		'STHeitiSC-Light',
		'STHeitiSC-Medium',
		'STHeitiTC-Light',
		'STHeitiTC-Medium',
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
		'HelveticaNeue-UltraLight',
		'HelveticaNeue-UltraLightItalic',
		'HelveticaNeue-Thin',
		'HelveticaNeue-ThinItalic',
		'HiraKakuProN-W3',
		'HiraKakuProN-W6',
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
		'KohinoorDevanagari-Book',
		'KohinoorDevanagari-Light',
		'KohinoorDevanagari-Medium',
		'LaoSangamMN',
		'MalayalamSangamMN',
		'MalayalamSangamMN-Bold',
		'Marion-Bold',
		'Marion-Italic',
		'Marion-Regular',
		'Menlo-BoldItalic',
		'Menlo-Regular',
		'Menlo-Bold',
		'Menlo-Italic',
		'MarkerFelt-Thin',
		'MarkerFelt-Wide',
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
		'SanFranciscoDisplay-Black',
		'SanFranciscoDisplay-Bold',
		'SanFranciscoDisplay-Heavy',
		'SanFranciscoDisplay-Light',
		'SanFranciscoDisplay-Medium',
		'SanFranciscoDisplay-Regular',
		'SanFranciscoDisplay-Semibold',
		'SanFranciscoDisplay-Thin',
		'SanFranciscoDisplay-Ultralight',
		'SanFranciscoRounded-Black',
		'SanFranciscoRounded-Bold',
		'SanFranciscoRounded-Heavy',
		'SanFranciscoRounded-Light',
		'SanFranciscoRounded-Medium',
		'SanFranciscoRounded-Regular',
		'SanFranciscoRounded-Semibold',
		'SanFranciscoRounded-Thin',
		'SanFranciscoRounded-Ultralight',
		'SanFranciscoText-Bold',
		'SanFranciscoText-BoldG1',
		'SanFranciscoText-BoldG2',
		'SanFranciscoText-BoldG3',
		'SanFranciscoText-BoldItalic',
		'SanFranciscoText-BoldItalicG1',
		'SanFranciscoText-BoldItalicG2',
		'SanFranciscoText-BoldItalicG3',
		'SanFranciscoText-Heavy',
		'SanFranciscoText-HeavyItalic',
		'SanFranciscoText-Light',
		'SanFranciscoText-LightItalic',
		'SanFranciscoText-Medium',
		'SanFranciscoText-MediumItalic',
		'SanFranciscoText-Regular',
		'SanFranciscoText-RegularG1',
		'SanFranciscoText-RegularG2',
		'SanFranciscoText-RegularG3',
		'SanFranciscoText-RegularItalic',
		'SanFranciscoText-RegularItalicG1',
		'SanFranciscoText-RegularItalicG2',
		'SanFranciscoText-RegularItalicG3',
		'SanFranciscoText-Semibold',
		'SanFranciscoText-SemiboldItalic',
		'SanFranciscoText-Thin',
		'SanFranciscoText-ThinItalic',
		'SavoyeLetPlain',
		'SinhalaSangamMN',
		'SinhalaSangamMN-Bold',
		'SnellRoundhand',
		'SnellRoundhand-Black',
		'SnellRoundhand-Bold',
		'Superclarendon-Regular',
		'Superclarendon-BoldItalic',
		'Superclarendon-Light',
		'Superclarendon-BlackItalic',
		'Superclarendon-Italic',
		'Superclarendon-LightItalic',
		'Superclarendon-Bold',
		'Superclarendon-Black',
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
	);

	/**
	 * Name of the settings section.
	 *
	 * @var string
	 * @access protected
	 */
	protected $name;

	/**
	 * Slug of the settings section.
	 *
	 * @var string
	 * @access protected
	 */
	protected $slug;

	/**
	 * Settings page.
	 *
	 * @var string
	 * @access protected
	 */
	protected $page;

	/**
	 * Base settings.
	 *
	 * @var Settings
	 * @access protected
	 */
	protected $base_settings;

	/**
	 * Settings for the section.
	 *
	 * @var array
	 * @access protected
	 */
	protected $settings = array();

	/**
	 * Groups for the section.
	 *
	 * @var array
	 * @access protected
	 */
	protected $groups = array();

	/**
	 * Allowed HTML for settings pages.
	 *
	 * @var array
	 * @access public
	 */
	public static $allowed_html = array(
		'select' => array(
			'class' => array(),
			'name' => array(),
			'multiple' => array(),
		),
		'option' => array(
			'value' => array(),
			'selected' => array(),
		),
		'input' => array(
			'class' => array(),
			'name' => array(),
			'value' => array(),
			'placeholder' => array(),
			'step' => array(),
			'type' => array(),
			'required' => array(),
			'size' => array(),
		),
		'br' => array(),
		'b' => array(),
		'strong' => array(),
		'i' => array(),
		'em' => array(),
		'a' => array(
			'href' => array(),
			'target' => array(),
		),
	);

	/**
	 * Constructor.
	 *
	 * @param string $page
	 */
	function __construct( $page ) {
		$this->page             = $page;
		$base_settings          = new \Apple_Exporter\Settings;
		$this->base_settings    = $base_settings->all();
		$this->settings         = apply_filters( 'apple_news_section_settings', $this->settings, $page );
		$this->groups           = apply_filters( 'apple_news_section_groups', $this->groups, $page );
		self::$fonts            = apply_filters( 'apple_news_fonts_list', self::$fonts );
	}

	/**
	 * Get the settings section name.
	 *
	 * @return string
	 * @access public
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * Return an array which contains all groups and their related settings,
	 * embedded.
	 *
	 * @return array
	 * @access public
	 */
	public function groups() {
		$result = array();
		foreach ( $this->groups as $name => $info ) {
			$settings = array();
			foreach ( $info['settings'] as $name ) {
				$settings[ $name ] = $this->settings[ $name ];
				$settings[ $name ]['default'] = $this->get_default_for( $name );
			}

			$result[ $name ] = array(
				'label'       => $info['label'],
				'description' => empty( $info['description'] ) ? null : $info['description'],
				'settings'    => $settings,
			);
		}

		return $result;
	}

	/**
	 * Get the ID of the settings section.
	 *
	 * @return string
	 * @access public
	 */
	public function id() {
		return $this->plugin_slug . '_options_section_' . $this->slug;
	}

	/**
	 * Register the settings section.
	 *
	 * @access public
	 */
	public function register() {
		add_settings_section(
			$this->id(),
			$this->name,
			array( $this, 'print_section_info' ),
			$this->page
	 	);

		foreach ( $this->settings as $name => $options ) {
			// Register setting
			$callback = ( isset( $options['sanitize'] ) && function_exists( $options['sanitize'] ) ) ? $options['sanitize'] : '';
			register_setting( $this->page, $name, $callback );

			// Add to settings section
			add_settings_field(
				$name,                                          // ID
				$options['label'],                              // Title
				array( $this, 'render_field' ),                 // Render calback
				$this->page,                                    // Page
				$this->id(),                                    // Section
				array( $name, $this->get_default_for( $name ) ) // Args passed to the render callback
		 	);
		}
	}

	/**
	 * Render a settings field.
	 *
	 * @param array $args
	 * @access public
	 */
	public function render_field( $args ) {
		list( $name, $default_value ) = $args;
		$type  = $this->get_type_for( $name );
		$value = get_option( $name ) ?: $default_value;
		$field = null;

		// Get the field size
		$size = $this->get_size_for( $name );

		// FIXME: A cleaner object-oriented solution would create Input objects
		// and instantiate them according to their type.
		if ( is_array( $type ) ) {
			// Check if this is a multiple select
			$multiple_name = $multiple_attr = '';
			if ( $this->is_multiple( $name ) ) {
				$multiple_name = '[]';
				$multiple_attr = 'multiple="multiple"';
			}

			// Check if we're using names as values
			$keys = array_keys( $type );
			$use_name_as_value = ( array_keys( $keys ) === $keys );

			// Use select2 only when there is a considerable ammount of options available
			if ( count( $type ) > 10 ) {
				$field = '<select class="select2" name="%s' . $multiple_name . '" ' . $multiple_attr . '>';
			} else {
				$field = '<select name="%s' . $multiple_name . '" ' . $multiple_attr . '>';
			}

			foreach ( $type as $key => $option ) {
				$store_value = $use_name_as_value ? $option : $key;
				$field .= "<option value='" . esc_attr( $store_value ) . "' ";
				if ( $this->is_multiple( $name ) ) {
					if ( in_array( $store_value, $value ) ) {
						$field .= 'selected="selected"';
					}
				} else {
					$field .= selected( $value, $store_value, false );
				}
				$field .= ">" . esc_html( $option ) . "</option>";
			}
			$field .= '</select>';
		} else if ( 'font' == $type ) {
			$field = '<select class="select2" name="%s">';
			foreach ( self::$fonts as $option ) {
				$field .= "<option value='" . esc_attr( $option ) . "'";
				if ( $option == $value ) {
					$field .= ' selected ';
				}
				$field .= ">" . esc_html( $option ) . "</option>";
			}
			$field .= '</select>';
		} else if ( 'boolean' == $type ) {
			$field = '<select name="%s">';

			$field .= '<option value="yes"';
			if ( 'yes' == $value ) {
				$field .= ' selected ';
			}
			$field .= '>Yes</option>';

			$field .= '<option value="no"';
			if ( 'yes' != $value ) {
				$field .= ' selected ';
			}
			$field .= '>No</option>';

			$field .= '</select>';
		} else if ( 'integer' == $type ) {
			$field = '<input type="number" name="%s" value="%s" size="%s" %s>';
		} else if ( 'float' == $type ) {
			$field = '<input class="input-float" placeholder="' . esc_attr( $default_value ) . '" type="text" step="any" name="%s" value="%s" size="%s">';
		} else if ( 'color' == $type ) {
			$field = '<input type="color" name="%s" value="%s" %s>';
		} else if ( 'password' == $type ) {
			$field = '<input type="password" name="%s" value="%s" size="%s" %s>';
		} else {
			// If nothing else matches, it's a string.
			$field = '<input type="text" name="%s" value="%s" size="%s" %s>';
		}

		// Add a description, if set.
		$description = $this->get_description_for( $name );
		if ( ! empty( $description ) ) {
			$field .= apply_filters( 'apple_news_field_description_output_html', '<br/><i>' . $description . '</i>', $name );
		}

		return sprintf(
			$field,
			esc_attr( $name ),
			esc_attr( $value ),
			intval( $size ),
			$this->is_required( $name )
		);
	}

	/**
	 * Get the type for a field.
	 *
	 * @param string $name
	 * @return string
	 * @access private
	 */
	private function get_type_for( $name ) {
		return empty( $this->settings[ $name ]['type'] ) ? 'string' : $this->settings[ $name ]['type'];
	}

	/**
	 * Get the description for a field.
	 *
	 * @param string $name
	 * @return string
	 * @access private
	 */
	private function get_description_for( $name ) {
		return empty( $this->settings[ $name ]['description'] ) ? '' : $this->settings[ $name ]['description'];
	}

	/**
	 * Get the size for a field.
	 *
	 * @param string $name
	 * @return int
	 * @access private
	 */
	private function get_size_for( $name ) {
		return empty( $this->settings[ $name ]['size'] ) ? 20 : $this->settings[ $name ]['size'];
	}

	/**
	 * Check if a field is required.
	 *
	 * @param string $name
	 * @return int
	 * @access private
	 */
	private function is_required( $name ) {
		$required = ! isset( $this->settings[ $name ]['required'] ) ? true : $this->settings[ $name ]['required'];
		return ( $required ) ? 'required' : '';
	}

	/**
	 * Check if the field can hold multiple values.
	 *
	 * @param string $name
	 * @return boolean
	 * @access private
	 */
	private function is_multiple( $name ) {
		return ! empty( $this->settings[ $name ]['multiple'] );
	}

	/**
	 * Get the default for a field.
	 *
	 * @param string $name
	 * @return string
	 * @access private
	 */
	private function get_default_for( $name ) {
		return isset( $this->base_settings[ $name ] ) ? $this->base_settings[ $name ] : '';
	}

	/**
	 * Gets section info.
	 *
	 * @return string
	 * @access public
	 */
	public function get_section_info() {
		return '';
	}

}
