<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\Recipe class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 2.7.0
 */

namespace Apple_Exporter\Components;

use Apple_Exporter\Component_Factory;
use Apple_Exporter\Component_Spec;
use Apple_Exporter\Exporter_Content;
use DOMElement;
use DOMNodeList;

/**
 * A class to transform a recipe from JSON schema to an Apple News Recipe component.
 *
 * @since 2.7.0
 */
class Recipe extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param DOMElement $node The node to examine for matches.
	 *
	 * @access public
	 * @return DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {
		// Note: we can't use the component get_setting method or settings array here, because this is a static class.
		$class = get_option( 'apple_news_settings' )['recipe_component_class'] ?? '';

		if ( $class && self::node_has_class( $node, $class ) ) {
			return $node;
		}

		return null;
	}

	/**
	 * Register all specs for the component.
	 */
	public function register_specs() {
		$theme                = \Apple_Exporter\Theme::get_used();
		$dark_mode_conditions = [
			'minSpecVersion'       => '1.14',
			'preferredColorScheme' => 'dark',
		];

		$component_style = [
			'backgroundColor' => '#recipe_background_color#',
		];
		if ( $theme->get_value( 'recipe_background_color_dark' ) ) {
			$component_style['conditional'] = [
				'backgroundColor' => '#recipe_background_color_dark#',
				'conditions'      => $dark_mode_conditions,
			];
		}

		$caption_textstyle             = [
			'textAlignment' => 'left',
			'fontName'      => '#recipe_caption_font#',
			'fontSize'      => '#recipe_caption_size#',
			'tracking'      => '#recipe_caption_tracking#',
			'lineHeight'    => '#recipe_caption_line_height#',
			'textColor'     => '#recipe_caption_color#',
			'linkStyle'     => [
				'textColor' => '#recipe_caption_link_color#',
			],
		];
		$caption_textstyle_conditional = [];
		if ( $theme->get_value( 'recipe_caption_color_dark' ) ) {
			$caption_textstyle_conditional['textColor'] = '#recipe_caption_color_dark#';
		}
		if ( $theme->get_value( 'recipe_caption_link_color_dark' ) ) {
			$caption_textstyle_conditional['linkStyle'] = [
				'textColor' => '#recipe_caption_link_color_dark#',
			];
		}
		if ( $caption_textstyle_conditional ) {
			$caption_textstyle_conditional['conditions'] = $dark_mode_conditions;
			$caption_textstyle['conditional']            = $caption_textstyle_conditional;
		}

		$title_textstyle             = [
			'textAlignment' => 'left',
			'fontName'      => '#recipe_title_font#',
			'fontSize'      => '#recipe_title_size#',
			'tracking'      => '#recipe_title_tracking#',
			'lineHeight'    => '#recipe_title_line_height#',
			'textColor'     => '#recipe_title_color#',
		];
		$title_textstyle_conditional = [];
		if ( $theme->get_value( 'recipe_title_color_dark' ) ) {
			$title_textstyle_conditional['textColor'] = '#recipe_title_color_dark#';
		}
		if ( $title_textstyle_conditional ) {
			$title_textstyle_conditional['conditions'] = $dark_mode_conditions;
			$title_textstyle['conditional']            = $title_textstyle_conditional;
		}

		$body_textstyle             = [
			'textAlignment'   => 'left',
			'fontName'        => '#recipe_body_font#',
			'fontSize'        => '#recipe_body_size#',
			'tracking'        => '#recipe_body_tracking#',
			'lineHeight'      => '#recipe_body_line_height#',
			'textColor'       => '#recipe_body_color#',
			'backgroundColor' => '#recipe_body_background_color#',
			'linkStyle'       => [
				'textColor' => '#recipe_body_link_color#',
			],
		];
		$body_textstyle_conditional = [];
		if ( $theme->get_value( 'recipe_body_color_dark' ) ) {
			$body_textstyle_conditional['textColor'] = '#recipe_body_color_dark#';
		}
		if ( $theme->get_value( 'recipe_body_background_color_dark' ) ) {
			$body_textstyle_conditional['backgroundColor'] = '#recipe_body_background_color_dark#';
		}
		if ( $theme->get_value( 'recipe_body_link_color_dark' ) ) {
			$body_textstyle_conditional['linkStyle'] = [
				'textColor' => '#recipe_body_link_color_dark#',
			];
		}
		if ( $body_textstyle_conditional ) {
			$body_textstyle_conditional['conditions'] = $dark_mode_conditions;
			$body_textstyle['conditional']            = $body_textstyle_conditional;
		}

		$header2_textstyle             = [
			'textAlignment' => 'left',
			'fontName'      => '#recipe_header2_font#',
			'fontSize'      => '#recipe_header2_size#',
			'tracking'      => '#recipe_header2_tracking#',
			'lineHeight'    => '#recipe_header2_line_height#',
			'textColor'     => '#recipe_header2_color#',
		];
		$header2_textstyle_conditional = [];
		if ( $theme->get_value( 'recipe_header2_color_dark' ) ) {
			$header2_textstyle_conditional['textColor'] = '#recipe_header2_color_dark#';
		}
		if ( $header2_textstyle_conditional ) {
			$header2_textstyle_conditional['conditions'] = $dark_mode_conditions;
			$header2_textstyle['conditional']            = $header2_textstyle_conditional;
		}

		$header3_textstyle             = [
			'textAlignment' => 'left',
			'fontName'      => '#recipe_header3_font#',
			'fontSize'      => '#recipe_header3_size#',
			'tracking'      => '#recipe_header3_tracking#',
			'lineHeight'    => '#recipe_header3_line_height#',
			'textColor'     => '#recipe_header3_color#',
		];
		$header3_textstyle_conditional = [];
		if ( $theme->get_value( 'recipe_header3_color_dark' ) ) {
			$header3_textstyle_conditional['textColor'] = '#recipe_header3_color_dark#';
		}
		if ( $header3_textstyle_conditional ) {
			$header3_textstyle_conditional['conditions'] = $dark_mode_conditions;
			$header3_textstyle['conditional']            = $header3_textstyle_conditional;
		}

		$header4_textstyle             = [
			'textAlignment' => 'left',
			'fontName'      => '#recipe_header4_font#',
			'fontSize'      => '#recipe_header4_size#',
			'tracking'      => '#recipe_header4_tracking#',
			'lineHeight'    => '#recipe_header4_line_height#',
			'textColor'     => '#recipe_header4_color#',
		];
		$header4_textstyle_conditional = [];
		if ( $theme->get_value( 'recipe_header4_color_dark' ) ) {
			$header4_textstyle_conditional['textColor'] = '#recipe_header4_color_dark#';
		}
		if ( $header4_textstyle_conditional ) {
			$header4_textstyle_conditional['conditions'] = $dark_mode_conditions;
			$header4_textstyle['conditional']            = $header4_textstyle_conditional;
		}

		$details_textstyle             = [
			'textAlignment' => 'left',
			'fontName'      => '#recipe_details_font#',
			'fontSize'      => '#recipe_details_size#',
			'tracking'      => '#recipe_details_tracking#',
			'lineHeight'    => '#recipe_details_line_height#',
			'textColor'     => '#recipe_details_color#',
			'linkStyle'     => [
				'textColor' => '#recipe_details_link_color#',
			],
		];
		$details_textstyle_conditional = [];
		if ( $theme->get_value( 'recipe_details_color_dark' ) ) {
			$details_textstyle_conditional['textColor'] = '#recipe_details_color_dark#';
		}
		if ( $theme->get_value( 'recipe_details_link_color_dark' ) ) {
			$details_textstyle_conditional['linkStyle'] = [
				'textColor' => '#recipe_details_link_color_dark#',
			];
		}
		if ( $details_textstyle_conditional ) {
			$details_textstyle_conditional['conditions'] = $dark_mode_conditions;
			$details_textstyle['conditional']            = $details_textstyle_conditional;
		}

		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role'       => 'recipe',
				'URL'        => '#url#',
				'layout'     => [
					'margin' => [
						'top'    => 5,
						'bottom' => 5,
					],
				],
				'style'      => $component_style,
				'components' => [
					[
						'role'    => 'photo',
						'layout'  => [
							'margin' => [
								'top'    => 0,
								'bottom' => 12,
							],
						],
						'URL'     => '#recipe_photo_url#',
						'caption' => [
							'text'      => '#recipe_photo_caption#',
							'format'    => 'html',
							'textStyle' => $caption_textstyle,
						],
					],
					[
						'role'       => 'container',
						'layout'     => [
							'padding' => [
								'left'  => 12,
								'right' => 12,
							],
						],
						'components' => [
							[
								'role'      => 'title',
								'layout'    => [
									'margin' => [
										'top'    => 8,
										'bottom' => 12,
									],
								],
								'textStyle' => $title_textstyle,
								'text'      => '#recipe_title#',
								'format'    => 'html',
							],
							[
								'role'      => 'heading2',
								'layout'    => 'recipe-header2-layout',
								'textStyle' => 'recipe-header2-style',
								'text'      => __( 'Recipe Information', 'apple-news' ),
								'format'    => 'html',
							],
							[
								'role'           => 'container',
								'layout'         => [
									'columnSpan'  => 4,
									'columnStart' => 0,
									'margin'      => [
										'bottom' => 10,
										'top'    => 24,
									],
									'padding'     => [
										'left'   => 0,
										'right'  => 0,
										'top'    => 10,
										'bottom' => 0,
									],
								],
								'contentDisplay' => [
									'type'         => 'collection',
									'gutter'       => '20',
									'rowSpacing'   => '10',
									'alignment'    => 'left',
									'minimumWidth' => 200,
								],
								'components'     => [
									[
										'role'      => 'body',
										'layout'    => 'recipe-details-layout',
										'textStyle' => 'recipe-details-style',
										'text'      => '#recipe_yield#',
										'format'    => 'html',
									],
									[
										'role'      => 'body',
										'layout'    => 'recipe-details-layout',
										'textStyle' => 'recipe-details-style',
										'text'      => '#recipe_prep_time#',
										'format'    => 'html',
									],
									[
										'role'      => 'body',
										'layout'    => 'recipe-details-layout',
										'textStyle' => 'recipe-details-style',
										'text'      => '#recipe_cook_time#',
										'format'    => 'html',
									],
									[
										'role'      => 'body',
										'layout'    => 'recipe-details-layout',
										'textStyle' => 'recipe-details-style',
										'text'      => '#recipe_total_time#',
										'format'    => 'html',
									],
									[
										'role'      => 'body',
										'layout'    => 'recipe-details-layout',
										'textStyle' => 'recipe-details-style',
										'text'      => '#recipe_calories_per_serving#',
										'format'    => 'html',
									],
								],
							],
							[
								'role'  => 'divider',
								'style' => [
									'border' => [
										'all'   => [
											'width' => 1,
											'style' => 'solid',
											'color' => '#000',
										],
										'left'  => false,
										'right' => false,
										'top'   => false,
									],
									'layout' => [
										'margin' => [
											'top'    => 10,
											'bottom' => 30,
										],
									],
								],
							],
							[
								'role'       => 'section',
								'components' => [
									[
										'role'       => 'container',
										'components' => [
											[
												'role'   => 'container',
												'layout' => [
													'margin' => [
														'bottom' => 20,
														'top'    => 20,
													],
												],
												'components' => [
													[
														'role'   => 'heading2',
														'layout' => 'recipe-header2-layout',
														'textStyle' => 'recipe-header2-style',
														'text'   => __( 'Ingredients', 'apple-news' ),
														'format' => 'html',
													],
													[
														'role'   => 'body',
														'layout' => 'recipe-body-layout',
														'textStyle' => 'recipe-body-style',
														'text'   => '#recipe_ingredients#',
														'format' => 'html',
													],
												],
											],
											[
												'role' => 'container',
												'components' => [
													[
														'role'   => 'heading2',
														'layout' => 'recipe-header2-layout',
														'textStyle' => 'recipe-header2-style',
														'text'   => __( 'Directions', 'apple-news' ),
														'format' => 'html',
													],
													[
														'role' => 'container',
														'components' => '#recipe_instructions#',
													],
												],
											],
										],
									],
								],
							],
						],
					],
				],
			],
		);

		$this->register_spec(
			'recipe-body-layout',
			__( 'Recipe Body Layout', 'apple-news' ),
			[
				'margin' => [
					'bottom' => 10,
				],
			],
		);
		$this->register_spec(
			'recipe-body-style',
			__( 'Recipe Body Text Style', 'apple-news' ),
			$body_textstyle,
		);

		$this->register_spec(
			'recipe-header2-layout',
			__( 'Recipe Header 2 Layout', 'apple-news' ),
			[
				'margin' => [
					'top'    => 6,
					'bottom' => 6,
				],
			],
		);
		$this->register_spec(
			'recipe-header2-style',
			__( 'Recipe Header 2 Text Style', 'apple-news' ),
			$header2_textstyle,
		);

		$this->register_spec(
			'recipe-header3-layout',
			__( 'Recipe Header 3 Layout', 'apple-news' ),
			[
				'margin' => [
					'top'    => 6,
					'bottom' => 6,
				],
			],
		);
		$this->register_spec(
			'recipe-header3-style',
			__( 'Recipe Header 3 Text Style', 'apple-news' ),
			$header3_textstyle,
		);

		$this->register_spec(
			'recipe-header4-layout',
			__( 'Recipe Header 4 Layout', 'apple-news' ),
			[
				'margin' => [
					'top'    => 6,
					'bottom' => 6,
				],
			],
		);
		$this->register_spec(
			'recipe-header4-style',
			__( 'Recipe Header 4 Text Style', 'apple-news' ),
			$header4_textstyle,
		);

		$this->register_spec(
			'recipe-details-layout',
			__( 'Recipe Details Layout', 'apple-news' ),
			[
				'margin' => [
					'bottom' => 10,
				],
			],
		);
		$this->register_spec(
			'recipe-details-style',
			__( 'Recipe Details Text Style', 'apple-news' ),
			$details_textstyle,
		);

		$this->register_spec(
			'recipe-section-json',
			__( 'JSON for a Section of Recipe Instructions', 'apple-news' ),
			[
				'role'       => 'container',
				'components' => [
					[
						'role'      => 'heading3',
						'layout'    => 'recipe-header3-layout',
						'textStyle' => 'recipe-header3-style',
						'text'      => '#recipe_section_name#',
						'format'    => 'html',
					],
					[
						'role'       => 'container',
						'components' => '#components#',
					],
				],
			],
		);

		$this->register_spec(
			'recipe-section-step-json',
			__( 'JSON for a Step Within a Section of Recipe Instructions', 'apple-news' ),
			[
				'role'       => 'container',
				'components' => [
					[
						'role'      => 'heading4',
						'layout'    => 'recipe-header4-layout',
						'textStyle' => 'recipe-header4-style',
						'text'      => '#recipe_step_name#',
						'format'    => 'html',
					],
					[
						'role'   => 'photo',
						'layout' => [
							'margin' => [
								'top'    => 6,
								'bottom' => 12,
							],
						],
						'URL'    => '#recipe_step_photo_url#',
					],
					[
						'role'      => 'body',
						'layout'    => 'recipe-body-layout',
						'textStyle' => 'recipe-body-style',
						'text'      => '#recipe_step_text#',
						'format'    => 'html',
					],
				],
			],
		);

		$this->register_spec(
			'recipe-step-json',
			__( 'JSON for a Standalone Step in Recipe Instructions', 'apple-news' ),
			[
				'role'       => 'container',
				'components' => [
					[
						'role'      => 'heading3',
						'layout'    => 'recipe-header3-layout',
						'textStyle' => 'recipe-header3-style',
						'text'      => '#recipe_step_name#',
						'format'    => 'html',
					],
					[
						'role'   => 'photo',
						'layout' => [
							'margin' => [
								'top'    => 6,
								'bottom' => 12,
							],
						],
						'URL'    => '#recipe_step_photo_url#',
					],
					[
						'role'      => 'body',
						'layout'    => 'recipe-body-layout',
						'textStyle' => 'recipe-body-style',
						'text'      => '#recipe_step_text#',
						'format'    => 'html',
					],
				],
			],
		);

		$this->register_spec(
			'wrapper-only-json',
			__( 'JSON for a Wrapper Only', 'apple-news' ),
			[
				'role'       => 'recipe',
				'URL'        => '#url#',
				'components' => '#components#',
			],
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 */
	protected function build( $html ) {
		$theme  = \Apple_Exporter\Theme::get_used();
		$schema = $this->matching_schema( $html );

		if ( ! $schema ) {
			return;
		}

		if ( $this->get_setting( 'recipe_component_use_schema' ) === 'no' ) {
			$export     = new Exporter_Content( $this->workspace->content_id, '', $html );
			$components = [];

			foreach ( $export->nodes() as $node ) {
				// $node is the original recipe element. We want the HTML within that element, otherwise we'd match the recipe node again.
				if ( $node->hasChildNodes() ) {
					foreach ( $node->childNodes as $child ) {
						$components = array_merge( $components, Component_Factory::get_components_from_node( $child, $this ) );
					}
				}
			}

			$this->register_json(
				'wrapper-only-json',
				[
					'#url#'        => $this->value_for_token( '#url#', $schema ),
					'#components#' => array_map( fn ( Component $component ) => $component->to_array(), $components ),
				],
			);

			return;
		}

		// Set up values for the tokens.
		$recipe_photo_url = $this->value_for_token( '#recipe_photo_url#', $schema );
		if ( $recipe_photo_url ) {
			$recipe_photo_url = $this->maybe_bundle_source( $recipe_photo_url );
		}

		$recipe_instructions = $this->value_for_token( '#recipe_instructions#', $schema );
		if ( is_array( $recipe_instructions ) ) {
			$recipe_instructions = $this->without_objects_containing_tokens( $recipe_instructions, $this->get_spec( 'recipe-step-json' ) );
		}

		$this->register_json(
			'json',
			[
				'#url#'                         => $this->value_for_token( '#url#', $schema ),
				'#recipe_photo_url#'            => $recipe_photo_url,
				'#recipe_photo_caption#'        => $this->value_for_token( '#recipe_photo_caption#', $schema ),
				'#recipe_title#'                => $this->value_for_token( '#recipe_title#', $schema ),
				'#recipe_yield#'                => $this->value_for_token( '#recipe_yield#', $schema ),
				'#recipe_prep_time#'            => $this->value_for_token( '#recipe_prep_time#', $schema ),
				'#recipe_cook_time#'            => $this->value_for_token( '#recipe_cook_time#', $schema ),
				'#recipe_total_time#'           => $this->value_for_token( '#recipe_total_time#', $schema ),
				'#recipe_calories_per_serving#' => $this->value_for_token( '#recipe_calories_per_serving#', $schema ),
				'#recipe_ingredients#'          => $this->value_for_token( '#recipe_ingredients#', $schema ),
				'#recipe_instructions#'         => $recipe_instructions,
			],
		);

		$this->register_layout(
			'recipe-body-layout',
			'recipe-body-layout',
		);
		$this->register_style(
			'recipe-body-style',
			'recipe-body-style',
			[
				'#recipe_body_font#'                  => $theme->get_value( 'recipe_body_font' ),
				'#recipe_body_size#'                  => (int) $theme->get_value( 'recipe_body_size' ),
				'#recipe_body_line_height#'           => (int) $theme->get_value( 'recipe_body_line_height' ),
				'#recipe_body_tracking#'              => (int) $theme->get_value( 'recipe_body_tracking' ) / 100,
				'#recipe_body_color#'                 => $theme->get_value( 'recipe_body_color' ),
				'#recipe_body_color_dark#'            => $theme->get_value( 'recipe_body_color_dark' ),
				'#recipe_body_link_color#'            => $theme->get_value( 'recipe_body_link_color' ),
				'#recipe_body_link_color_dark#'       => $theme->get_value( 'recipe_body_link_color_dark' ),
				'#recipe_body_background_color#'      => $theme->get_value( 'recipe_body_background_color' ),
				'#recipe_body_background_color_dark#' => $theme->get_value( 'recipe_body_background_color_dark' ),
			],
			'textStyle',
		);

		$this->register_layout(
			'recipe-header2-layout',
			'recipe-header2-layout',
		);
		$this->register_style(
			'recipe-header2-style',
			'recipe-header2-style',
			[
				'#recipe_header2_font#'        => $theme->get_value( 'recipe_header2_font' ),
				'#recipe_header2_size#'        => (int) $theme->get_value( 'recipe_header2_size' ),
				'#recipe_header2_line_height#' => (int) $theme->get_value( 'recipe_header2_line_height' ),
				'#recipe_header2_tracking#'    => (int) $theme->get_value( 'recipe_header2_tracking' ) / 100,
				'#recipe_header2_color#'       => $theme->get_value( 'recipe_header2_color' ),
				'#recipe_header2_color_dark#'  => $theme->get_value( 'recipe_header2_color_dark' ),
			],
			'textStyle',
		);

		$this->register_layout(
			'recipe-header3-layout',
			'recipe-header3-layout',
		);
		$this->register_style(
			'recipe-header3-style',
			'recipe-header3-style',
			[
				'#recipe_header3_font#'        => $theme->get_value( 'recipe_header3_font' ),
				'#recipe_header3_size#'        => (int) $theme->get_value( 'recipe_header3_size' ),
				'#recipe_header3_line_height#' => (int) $theme->get_value( 'recipe_header3_line_height' ),
				'#recipe_header3_tracking#'    => (int) $theme->get_value( 'recipe_header3_tracking' ) / 100,
				'#recipe_header3_color#'       => $theme->get_value( 'recipe_header3_color' ),
				'#recipe_header3_color_dark#'  => $theme->get_value( 'recipe_header3_color_dark' ),
			],
			'textStyle',
		);

		$this->register_layout(
			'recipe-header4-layout',
			'recipe-header4-layout',
		);
		$this->register_style(
			'recipe-header4-style',
			'recipe-header4-style',
			[
				'#recipe_header4_font#'        => $theme->get_value( 'recipe_header4_font' ),
				'#recipe_header4_size#'        => (int) $theme->get_value( 'recipe_header4_size' ),
				'#recipe_header4_line_height#' => (int) $theme->get_value( 'recipe_header4_line_height' ),
				'#recipe_header4_tracking#'    => (int) $theme->get_value( 'recipe_header4_tracking' ) / 100,
				'#recipe_header4_color#'       => $theme->get_value( 'recipe_header4_color' ),
				'#recipe_header4_color_dark#'  => $theme->get_value( 'recipe_header4_color_dark' ),
			],
			'textStyle',
		);

		$this->register_layout(
			'recipe-details-layout',
			'recipe-details-layout',
		);
		$this->register_style(
			'recipe-details-style',
			'recipe-details-style',
			[
				'#recipe_details_font#'                  => $theme->get_value( 'recipe_details_font' ),
				'#recipe_details_size#'                  => (int) $theme->get_value( 'recipe_details_size' ),
				'#recipe_details_line_height#'           => (int) $theme->get_value( 'recipe_details_line_height' ),
				'#recipe_details_tracking#'              => (int) $theme->get_value( 'recipe_details_tracking' ) / 100,
				'#recipe_details_color#'                 => $theme->get_value( 'recipe_details_color' ),
				'#recipe_details_color_dark#'            => $theme->get_value( 'recipe_details_color_dark' ),
				'#recipe_details_link_color#'            => $theme->get_value( 'recipe_details_link_color' ),
				'#recipe_details_link_color_dark#'       => $theme->get_value( 'recipe_details_link_color_dark' ),
				'#recipe_details_background_color#'      => $theme->get_value( 'recipe_details_background_color' ),
				'#recipe_details_background_color_dark#' => $theme->get_value( 'recipe_details_background_color_dark' ),
			],
			'textStyle',
		);
	}

	/**
	 * Set the JSON for the component.
	 *
	 * @param string $spec_name The spec to use for defining the JSON.
	 * @param array  $values    Values to substitute for placeholders in the spec.
	 */
	protected function register_json( $spec_name, $values = [] ) {
		if ( 'json' === $spec_name ) {
			$values = $this->prepare_tokens( $values );
		}

		// Go through normal token replacement.
		parent::register_json( $spec_name, $values );

		if ( 'json' === $spec_name ) {
			if ( is_array( $this->json ) ) {
				$spec = $this->get_spec( $spec_name );

				if ( $spec instanceof Component_Spec ) {
					// Update the JSON to remove objects with recipe tokens that weren't replaced.
					$this->json = $this->without_objects_containing_tokens( $this->json, $spec );
				}
			}
		}
	}

	/**
	 * Find JSON-LD Recipe items for the given recipe HTML.
	 *
	 * @param string $recipe The recipe HTML found in the matching node for this component instance.
	 * @return array|null The matching schema, or null if none found.
	 */
	private function matching_schema( $recipe ): ?array {
		// First, check the post content for a JSON-LD Recipe.
		$post = get_post( $this->workspace->content_id );

		/** This filter is documented in admin/apple-actions/index/class-export.php */
		$post_content = apply_filters( 'apple_news_exporter_content_pre', $post->post_content, $post->ID );

		/** This filter is documented in wp-includes/post-template.php */
		$post_content = apply_filters( 'the_content', $post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		$items = self::recipe_items( $post_content, 'body' );

		foreach ( $items as $item ) {
			if ( self::is_recipe_item_for_recipe_html( $item, $recipe ) ) {
				return $item;
			}
		}

		// Second, try to find schema in the HEAD of the post on the frontend.
		// Could check BODY as well, but the post content is already being scanned. That might be enough.

		/**
		 * Filters the URL for an article that will be fetched and searched for JSON-LD Recipe items.
		 *
		 * @param string $permalink  The URL to fetch.
		 * @param int    $content_id The ID of the content being processed.
		 */
		$permalink = apply_filters( 'apple_news_recipe_schema_permalink', get_permalink( $this->workspace->content_id ), $this->workspace->content_id );

		if ( $permalink ) {
			$resp = wp_remote_get( $permalink ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
			$code = wp_remote_retrieve_response_code( $resp );
			$html = ( $code >= 200 && $code < 300 ) ? wp_remote_retrieve_body( $resp ) : '';

			$items = self::recipe_items( $html, 'head' );

			foreach ( $items as $item ) {
				if ( self::is_recipe_item_for_recipe_html( $item, $recipe ) ) {
					return $item;
				}
			}
		}

		return null;
	}

	/**
	 * All JSON-LD Recipe items in the given HTML.
	 *
	 * @param string $document The document to search for recipe items.
	 * @param string $tag_name The tag name to search for recipe items.
	 * @return array The recipe items found in the document.
	 */
	private static function recipe_items( string $document, string $tag_name ): array {
		$out = [];

		// Do some naive checks before the intensive processing to see if the HTML contains JSON-LD and a Recipe.
		if ( str_contains( $document, 'application/ld+json' ) && str_contains( $document, '"Recipe"' ) ) {
			$dom = new \DOMDocument();
			libxml_use_internal_errors( true );
			$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $document );
			libxml_clear_errors();

			$body  = $dom->getElementsByTagName( $tag_name )->item( 0 );
			$nodes = $body ? $body->childNodes : new DOMNodeList();
			$out   = self::recipe_items_in_nodes( [], $nodes );
		}

		return $out;
	}

	/**
	 * Recursively search for JSON-LD Recipe items in the given nodes.
	 *
	 * @param array       $carry The array to accumulate recipe items.
	 * @param DOMNodeList $nodes The nodes to search for recipe items.
	 * @return array The recipe items found in the nodes.
	 */
	private static function recipe_items_in_nodes( array $carry, DOMNodeList $nodes ): array {
		foreach ( $nodes as $node ) {
			if ( 'script' === $node->nodeName && 'application/ld+json' === $node->getAttribute( 'type' ) ) {
				try {
					$json = json_decode( $node->textContent, true, 512, JSON_THROW_ON_ERROR ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

					if ( isset( $json['@type'] ) && 'Recipe' === $json['@type'] ) {
						$carry[] = $json;
					}
				} catch ( \JsonException $e ) {
					// Do nothing.
					unset( $e );
				}
			}

			if ( $node->hasChildNodes() ) {
				$carry = self::recipe_items_in_nodes( $carry, $node->childNodes );
			}
		}

		return $carry;
	}

	/**
	 * Best-guess if the given schema item is for the given recipe HTML.
	 *
	 * @param array  $schema The schema item to check.
	 * @param string $html   The recipe HTML to check.
	 * @return bool True if the schema item is for the recipe HTML, false otherwise.
	 */
	private static function is_recipe_item_for_recipe_html( array $schema, string $html ) {
		return (
			isset( $schema['name'] )
			&& is_string( $schema['name'] )
			&& strlen( $schema['name'] ) > 0
			&& str_contains( $html, $schema['name'] )
		);
	}

	/**
	 * Value for the given Apple News theme token in the given JSON-LD Recipe item.
	 *
	 * @param string $token  The token to replace.
	 * @param array  $schema The item to search.
	 * @return string|array|null
	 */
	private function value_for_token( string $token, array $schema ) {
		switch ( $token ) {
			case '#url#':
				return $schema['url'] ?? get_permalink( $this->workspace->content_id );

			case '#recipe_photo_url#':
				// The `image` property appears to be predominant in actual uses, e.g. the example at https://schema.org/Recipe. Can be an ImageObject or URL.
				$url = $schema['image']['contentUrl'] ?? $schema['image']['url'] ?? $schema['image'][0] ?? $schema['image'] ?? null;

				return is_string( $url ) ? $url : null;

			case '#recipe_photo_caption#':
				// Available when the `image` is an ImageObject. Can be MediaObject or Text, but MediaObject use case ("closed caption, subtitles etc.") doesn't apply here.
				return $schema['image']['caption'] ?? null;

			case '#recipe_title#':
				// Text type.
				return $schema['name'] ?? null;

			case '#recipe_yield#':
				// Can be QuantitativeValue or Text. It isn't clear from the examples how the properties of QuantitativeValue would be compiled into a final value, so not yet implementing.
				if ( ! isset( $schema['recipeYield'] ) || ! is_string( $schema['recipeYield'] ) ) {
					return null;
				}

				return sprintf(
					'<p><strong>%s</strong> %s</p>',
					esc_html__( 'Yields:', 'apple-news' ),
					esc_html( $schema['recipeYield'] )
				);

			case '#recipe_prep_time#':
				// ISO 8601 duration format.
				if ( ! isset( $schema['prepTime'] ) || ! is_string( $schema['prepTime'] ) ) {
					return null;
				}

				try {
					$di = new \DateInterval( $schema['prepTime'] );
				} catch ( \Exception $e ) {
					return null;
				}

				return sprintf(
					'<p><strong>%s</strong> %s</p>',
					esc_html__( 'Prep Time:', 'apple-news' ),
					esc_html( $this->duration( $di ) )
				);

			case '#recipe_cook_time#':
				// ISO 8601 duration format.
				if ( ! isset( $schema['cookTime'] ) || ! is_string( $schema['cookTime'] ) ) {
					return null;
				}

				try {
					$di = new \DateInterval( $schema['cookTime'] );
				} catch ( \Exception $e ) {
					return null;
				}

				return sprintf(
					'<p><strong>%s</strong> %s</p>',
					esc_html__( 'Cook Time:', 'apple-news' ),
					esc_html( $this->duration( $di ) )
				);

			case '#recipe_total_time#':
				// ISO 8601 duration format.
				if ( ! isset( $schema['totalTime'] ) || ! is_string( $schema['totalTime'] ) ) {
					return null;
				}

				try {
					$di = new \DateInterval( $schema['totalTime'] );
				} catch ( \Exception $e ) {
					return null;
				}

				return sprintf(
					'<p><strong>%s</strong> %s</p>',
					esc_html__( 'Total Time:', 'apple-news' ),
					esc_html( $this->duration( $di ) )
				);

			case '#recipe_calories_per_serving#':
				// NutritionInformation type.
				if ( ! isset( $schema['nutrition']['calories'] ) || ! is_string( $schema['nutrition']['calories'] ) ) {
					return null;
				}

				return sprintf(
					'<p><strong>%s</strong> %s</p>',
					esc_html__( 'Calories/Serving:', 'apple-news' ),
					// `calories` is of type Energy, which is of the form '<Number> <Energy unit of measure>'.
					esc_html( preg_replace( '/\D/', '', $schema['nutrition']['calories'] ) )
				);

			case '#recipe_ingredients#':
				// Of type Text. All examples show an array of strings.
				if ( ! isset( $schema['recipeIngredient'] ) ) {
					return null;
				}

				$out  = '<ul>';
				$out .= is_array( $schema['recipeIngredient'] )
					? implode( '', array_map( fn ( $ingredient ) => '<li>' . esc_html( $ingredient ) . '</li>', $schema['recipeIngredient'] ) )
					: '<li>' . esc_html( $schema['recipeIngredient'] ) . '</li>';
				$out .= '</ul>';

				return $out;

			case '#recipe_instructions#':
				if ( ! isset( $schema['recipeInstructions'] ) ) {
					return null;
				}

				$step_spec = $this->get_spec( 'recipe-step-json' );

				if ( ! $step_spec ) {
					return null;
				}

				// CreativeWork or ItemList or Text.
				$instructions = $schema['recipeInstructions'];

				if ( is_string( $instructions ) ) {
					$instructions = [ $instructions ];
				}

				if ( wp_is_numeric_array( $instructions ) && count( $instructions ) > 0 ) {
					if ( is_string( $instructions[0] ) ) {
						return array_reduce(
							$instructions,
							function ( $carry, $value ) use ( $step_spec ) {
								$carry[] = $step_spec->substitute_values(
									$this->prepare_tokens(
										[
											'#recipe_step_name#'      => null,
											'#recipe_step_photo_url#' => null,
											'#recipe_step_text#'      => $value,
										],
									),
									(int) $this->workspace->content_id,
								);

								return $carry;
							},
							[],
						);
					}

					if ( is_array( $instructions[0] ) ) {
						return $this->how_to_steps( [], $instructions, $step_spec );
					}
				}

				return null;

			default:
				return null;
		}
	}

	/**
	 * Duration in readable format for recipe component.
	 *
	 * @param \DateInterval $di The duration to format.
	 * @return string The formatted duration.
	 */
	private function duration( \DateInterval $di ) {
		$from = new \DateTimeImmutable();
		$diff = $from->add( $di )->getTimestamp() - $from->getTimestamp();

		$minutes = $diff % HOUR_IN_SECONDS;
		$hours   = $diff - $minutes;

		$out = [];

		if ( $hours > 0 ) {
			/* translators: %d: number of hours */
			$out[] = sprintf( __( '%d hr', 'apple-news' ), $hours / HOUR_IN_SECONDS );
		}

		if ( $minutes > 0 ) {
			/* translators: %d: number of minutes */
			$out[] = sprintf( __( '%d mins', 'apple-news' ), (int) round( $minutes / MINUTE_IN_SECONDS ) );
		}

		return implode( ' ', $out );
	}

	/**
	 * Recursively process HowToStep objects, possibly in HowToSection objects.
	 *
	 * @param array          $carry      The array to accumulate the steps.
	 * @param mixed          $schema     The schema to process.
	 * @param Component_Spec $step_spec  The spec to use for the steps.
	 * @return array The steps in the schema.
	 */
	private function how_to_steps( array $carry, mixed $schema, Component_Spec $step_spec ) {
		foreach ( $schema as $item ) {
			if ( isset( $item['@type'] ) ) {
				if ( 'HowToSection' === $item['@type'] ) {
					// Steps in the section are contained here.
					if ( isset( $item['itemListElement'] ) && is_array( $item['itemListElement'] ) ) {
						$section_spec      = $this->get_spec( 'recipe-section-json' );
						$section_step_spec = $this->get_spec( 'recipe-section-step-json' );

						if ( $section_spec instanceof Component_Spec && $section_step_spec instanceof Component_Spec ) {
							$carry[] = $section_spec->substitute_values(
								$this->prepare_tokens(
									[
										'#recipe_section_name#' => $item['name'] ?? null,
										'#components#' => $this->how_to_steps( [], $item['itemListElement'], $section_step_spec ),
									],
								),
								(int) $this->workspace->content_id
							);
						}
					}
				}

				if ( 'HowToStep' === $item['@type'] ) {
					$name  = $item['name'] ?? null;
					$text  = $item['text'] ?? null;
					$image = $item['image']['contentUrl'] ?? $item['image']['url'] ?? $item['image'][0] ?? $item['image'] ?? null;

					/*
					 * We've seen examples of recipe plugins that generate JSON with identical strings for
					 * name and text in some scenarios. In that case, use just body text and not a heading.
					 */
					if ( $name === $text ) {
						$name = null;
					}

					$carry[] = $step_spec->substitute_values(
						$this->prepare_tokens(
							[
								'#recipe_step_name#'      => $name,
								'#recipe_step_photo_url#' => is_string( $image ) && $image ? $this->maybe_bundle_source( $image ) : null,
								'#recipe_step_text#'      => $text,
							],
						),
						(int) $this->workspace->content_id,
					);
				}
			}
		}

		return $carry;
	}

	/**
	 * Mark recipe-specific tokens that had no value so that they can be detected after value substitution and removed if unused.
	 *
	 * @param array $tokens The tokens to prepare.
	 * @return array The prepared tokens.
	 */
	private function prepare_tokens( array $tokens ) {
		foreach ( $tokens as $key => $value ) {
			if ( 0 === strpos( $key, '#recipe_' ) && null === $value ) {
				$tokens[ $key ] = $key;
			}
		}

		return $tokens;
	}

	/**
	 * Remove objects still containing tokens from the given JSON.
	 *
	 * @param array          $json The JSON to process.
	 * @param Component_Spec $spec The spec to use for checking tokens.
	 * @return array|null The JSON with objects containing tokens removed, or null if the entire JSON should be removed.
	 */
	private function without_objects_containing_tokens( array $json, Component_Spec $spec ) {
		$is_numeric = wp_is_numeric_array( $json );

		foreach ( $json as $key => $value ) {
			if ( is_array( $value ) ) {
				$check = $this->without_objects_containing_tokens( $value, $spec );

				if ( null === $check ) {
					unset( $json[ $key ] );
				} else {
					$json[ $key ] = $check;
				}
			}

			if ( is_string( $value ) && $spec->is_token( $value ) ) {
				return null;
			}
		}

		if ( $is_numeric ) {
			$json = array_values( $json );
		}

		return $json;
	}
}
