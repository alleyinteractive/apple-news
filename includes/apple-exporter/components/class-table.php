<?php
/**
 * Contains a component class representing a table.
 *
 * @package Apple_News
 * @since 1.4.0
 */

namespace Apple_Exporter\Components;

/**
 * A component class representing a table.
 *
 * @since 1.4.0
 */
class Table extends Component {

	/**
	 * Whether dark mode table colors exist.
	 *
	 * @var boolean
	 */
	private $dark_table_colors_exist;

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {

		// In order to match, HTML support needs to be turned on globally.
		$settings = get_option( \Admin_Apple_Settings::$option_name );
		if ( ! empty( $settings['html_support'] ) && 'no' === $settings['html_support'] ) {
			return null;
		}

		// Check if node is a table, or a figure with a table class.
		if (
			(
				self::node_has_class( $node, 'wp-block-table' ) &&
				$node->hasChildNodes() &&
				'table' === $node->firstChild->nodeName // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			) ||
			'table' === $node->nodeName ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			return $node;
		}

		return null;
	}

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Get Dark Table Colors.
		$table_border_color_dark            = $theme->get_value( 'table_border_color_dark' );
		$table_body_background_color_dark   = $theme->get_value( 'table_body_background_color_dark' );
		$table_body_color_dark              = $theme->get_value( 'table_body_color_dark' );
		$table_header_background_color_dark = $theme->get_value( 'table_header_background_color_dark' );
		$table_header_color_dark            = $theme->get_value( 'table_header_color_dark' );

		// If all dark table styles are empty, do not add conditional styles.
		$this->dark_table_colors_exist =
			! empty( $table_border_color_dark ) ||
			! empty( $table_body_background_color_dark ) ||
			! empty( $table_body_color_dark ) ||
			! empty( $table_header_background_color_dark ) ||
			! empty( $table_header_color_dark );

		$dark_table_conditional = $this->dark_table_colors_exist ? [
			'conditional' => [
				[
					'style'      => 'dark-table',
					'conditions' => [
						'minSpecVersion'       => '1.14',
						'preferredColorScheme' => 'dark',
					],
				],
			],
		] : [];

		// Register the JSON for the table itself.
		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			array_merge(
				[
					'role'   => 'htmltable',
					'html'   => '#html#',
					'layout' => 'table-layout',
					'style'  => 'default-table',
				],
				$dark_table_conditional
			)
		);
		$this->register_spec(
			'json-with-caption-text',
			__( 'JSON With Caption Text', 'apple-news' ),
			[
				'role'       => 'container',
				'components' => [
					// Table Component.
					array_merge(
						[
							'role'   => 'htmltable',
							'html'   => '#html#',
							'layout' => 'table-layout',
							'style'  => 'default-table',
						],
						$dark_table_conditional
					),
					// Caption Component.
					[
						'role'   => 'caption',
						'text'   => '#caption_text#',
						'format' => 'html',
					],
				],
			]
		);

		// Register the JSON for the table layout.
		$this->register_spec(
			'table-layout',
			__( 'Table Layout', 'apple-news' ),
			[
				'margin' => [
					'bottom' => '#table_body_line_height#',
				],
			]
		);

		$default_table_styles = [
			'border'     => [
				'all' => [
					'color' => '#table_border_color#',
					'style' => '#table_border_style#',
					'width' => '#table_border_width#',
				],
			],
			'tableStyle' => [
				'cells'       => [
					'backgroundColor'     => '#table_body_background_color#',
					'horizontalAlignment' => '#table_body_horizontal_alignment#',
					'padding'             => '#table_body_padding#',
					'textStyle'           => [
						'fontName'   => '#table_body_font#',
						'fontSize'   => '#table_body_size#',
						'lineHeight' => '#table_body_line_height#',
						'textColor'  => '#table_body_color#',
						'tracking'   => '#table_body_tracking#',
					],
					'verticalAlignment'   => '#table_body_vertical_alignment#',
				],
				'columns'     => [
					'divider' => [
						'color' => '#table_border_color#',
						'style' => '#table_border_style#',
						'width' => '#table_border_width#',
					],
				],
				'headerCells' => [
					'backgroundColor'     => '#table_header_background_color#',
					'horizontalAlignment' => '#table_header_horizontal_alignment#',
					'padding'             => '#table_header_padding#',
					'textStyle'           => [
						'fontName'   => '#table_header_font#',
						'fontSize'   => '#table_header_size#',
						'lineHeight' => '#table_header_line_height#',
						'textColor'  => '#table_header_color#',
						'tracking'   => '#table_header_tracking#',
					],
					'verticalAlignment'   => '#table_header_vertical_alignment#',
				],
				'headerRows'  => [
					'divider' => [
						'color' => '#table_border_color#',
						'style' => '#table_border_style#',
						'width' => '#table_border_width#',
					],
				],
				'rows'        => [
					'divider' => [
						'color' => '#table_border_color#',
						'style' => '#table_border_style#',
						'width' => '#table_border_width#',
					],
				],
			],
		];

		$this->register_spec(
			'default-table',
			__( 'Table Style', 'apple-news' ),
			$default_table_styles
		);

		if ( $this->dark_table_colors_exist ) {

			// Start with default-table styles as a base.
			// Then modify dark styles where applicable.
			$dark_table_styles = $default_table_styles;

			// Set cell background color.
			if ( ! empty( $table_body_background_color_dark ) ) {
				$dark_table_styles['tableStyle']['cells']['backgroundColor'] = '#table_body_background_color_dark#';
			}

			// Set cell text color.
			if ( ! empty( $table_body_color_dark ) ) {
				$dark_table_styles['tableStyle']['cells']['textStyle']['textColor'] = '#table_body_color_dark#';
			}

			// Set header cell background color.
			if ( ! empty( $table_header_background_color_dark ) ) {
				$dark_table_styles['tableStyle']['headerCells']['backgroundColor'] = '#table_header_background_color_dark#';
			}

			// Set header text color.
			if ( ! empty( $table_header_color_dark ) ) {
				$dark_table_styles['tableStyle']['headerCells']['textStyle']['textColor'] = '#table_header_color_dark#';
			}

			// Set border colors.
			if ( ! empty( $table_border_color_dark ) ) {
				// Table outer border.
				$dark_table_styles['border']['all']['color'] = '#table_border_color_dark#';
				// Column borders.
				$dark_table_styles['tableStyle']['columns']['divider']['color'] = '#table_border_color_dark#';
				// Row borders.
				$dark_table_styles['tableStyle']['rows']['divider']['color'] = '#table_border_color_dark#';
				// Header row borders.
				$dark_table_styles['tableStyle']['headerRows']['divider']['color'] = '#table_border_color_dark#';
			}

			$this->register_spec(
				'dark-table',
				__( 'Dark Table Style', 'apple-news' ),
				$dark_table_styles
			);
		}
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 * @access protected
	 */
	protected function build( $html ) {

		// If HTML is not enabled for this component, bail.
		if ( ! $this->html_enabled() ) {
			return;
		}

		/**
		 * Allows for table HTML to be filtered before being applied.
		 *
		 * @param string $html The raw HTML for the table.
		 *
		 * @since 1.4.0
		 */
		$table_html = apply_filters(
			'apple_news_build_table_html',
			$this->parser->parse( $html )
		);

		// If we don't have any table HTML at this point, bail.
		if ( empty( $table_html ) ) {
			return;
		}

		$table_spec    = 'json';
		$table_caption = '';
		if ( preg_match( '/<figcaption>(.+?)<\/figcaption>/', $html, $caption_match ) ) {
			$table_caption = $caption_match[1];
			$table_spec    = 'json-with-caption-text';
		}
		$values = [
			'#html#'         => preg_replace( '/<\/table>.*/', '</table>', $table_html ),
			'#caption_text#' => $table_caption,
		];

		// Add the JSON for this component.
		$this->register_json( $table_spec, $values );

		// Register the layout for the table.
		$this->register_layout( 'table-layout', 'table-layout' );

		// Register the style for the table.
		$this->register_component_style(
			'default-table',
			'default-table'
		);

		// Register dark mode styles, if applicable.
		if ( $this->dark_table_colors_exist ) {
			$this->register_component_style(
				'dark-table',
				'dark-table'
			);
		}
	}

	/**
	 * Whether HTML format is enabled for this component type.
	 *
	 * @param bool $enabled Optional. Whether to enable HTML support for this component. Defaults to true.
	 *
	 * @access protected
	 * @return bool Whether HTML format is enabled for this component type.
	 */
	protected function html_enabled( $enabled = true ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::html_enabled( $enabled );
	}
}
