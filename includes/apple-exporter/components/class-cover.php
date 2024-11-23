<?php
/**
 * Publish to Apple News: \Apple_Exporter\Components\Cover class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter\Components
 */

namespace Apple_Exporter\Components;

use Apple_Exporter\Theme;

/**
 * A cover is optional and displayed at the very top of the article. It's
 * loaded from the Exporter_Content's cover attribute, if present.
 * This component does not need a node so no need to implement match_node.
 *
 * In a WordPress context, the Exporter_Content's cover attribute is a post's
 * thumbnail, a.k.a featured image.
 *
 * @since 0.2.0
 */
class Cover extends Component {

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		$theme = \Apple_Exporter\Theme::get_used();
		$this->register_spec(
			'json',
			__( 'JSON', 'apple-news' ),
			[
				'role'       => 'header',
				'layout'     => 'headerPhotoLayout',
				'components' => [
					[
						'role'   => '#role#',
						'layout' => 'headerPhotoLayout',
						'URL'    => '#url#',
					],
				],
				'behavior'   => [
					'type'   => 'parallax',
					'factor' => 0.8,
				],
			]
		);

		$conditional = [];
		if ( ! empty( $theme->get_value( 'caption_color_dark' ) ) ) {
			$conditional = [
				'conditional' => [
					'textColor'  => '#caption_color_dark#',
					'conditions' => [
						'minSpecVersion'       => '1.14',
						'preferredColorScheme' => 'dark',
					],
				],
			];
		}

		$this->register_spec(
			'jsonWithCaption',
			__( 'JSON with Caption', 'apple-news' ),
			[
				'role'       => 'header',
				'layout'     => 'headerPhotoLayout',
				'components' => [
					[
						'role'    => '#role#',
						'layout'  => 'headerPhotoLayoutWithCaption',
						'URL'     => '#url#',
						'caption' => [
							'format'    => 'html',
							'text'      => '#caption#',
							'textStyle' => [
								'fontName' => '#caption_font#',
							],
						],
					],
					[
						'role'      => 'caption',
						'text'      => '#caption#',
						'format'    => 'html',
						'textStyle' => array_merge(
							[
								'textAlignment' => '#text_alignment#',
								'fontName'      => '#caption_font#',
								'fontSize'      => '#caption_size#',
								'tracking'      => '#caption_tracking#',
								'lineHeight'    => '#caption_line_height#',
								'textColor'     => '#caption_color#',
							],
							$conditional
						),
					],
				],
				'behavior'   => [
					'type'   => 'parallax',
					'factor' => 0.8,
				],
			]
		);

		$this->register_spec(
			'headerPhotoLayout',
			__( 'Layout', 'apple-news' ),
			[
				'ignoreDocumentMargin' => true,
				'columnStart'          => 0,
				'columnSpan'           => '#layout_columns#',
			]
		);

		$this->register_spec(
			'headerPhotoLayoutWithCaption',
			__( 'Layout with Caption', 'apple-news' ),
			[
				'ignoreDocumentMargin' => true,
				'columnStart'          => 0,
				'columnSpan'           => '#layout_columns#',
				'margin'               => [
					'bottom' => '#caption_line_height#',
				],
			]
		);

		$this->register_spec(
			'headerBelowTextPhotoLayout',
			__( 'Below Text Layout', 'apple-news' ),
			[
				'ignoreDocumentMargin' => true,
				'columnStart'          => 0,
				'columnSpan'           => '#layout_columns#',
				'margin'               => [
					'top'    => 30,
					'bottom' => 0,
				],
			]
		);
	}

	/**
	 * Build the component.
	 *
	 * @param array|string $html {
	 *    The options for the component. If a string is provided, assume it is an image URL.
	 *
	 *    @type string $caption  The caption for the media.
	 *    @type string $url      The URL to the media.
	 *    @type string $provider The provider of the media. Can be the name of a supported provider, e.g. 'youtube'. Can
	 *                           be a media type, e.g. 'video' or 'image'. Can be blank, implying 'image'.
	 * }
	 * @access protected
	 */
	protected function build( $html ) {
		$theme = Theme::get_used();

		// Handle case where options is a URL.
		if ( ! is_array( $html ) ) {
			$html = [
				'url' => $html,
			];
		}

		if ( empty( $html['provider'] ) ) {
			$html['provider'] = 'image';
		}

		$url = match ( $html['provider'] ) {
			'video_url', 'embedwebvideo' => $html['url'],
			default => $this->maybe_bundle_source( $html['url'] ),
		};

		// If we can't get a valid URL, bail.
		$check = trim( $url );
		if ( empty( $check ) ) {
			return;
		}

		switch ( $html['provider'] ) {
			case 'video_url':
			case 'video_id':
				$role = 'video';
				break;
			case 'embedwebvideo':
				$role = 'embedwebvideo';
				break;
			default:
				// Use postmeta to determine if component role should be registered as 'image' or 'photo'.
				$use_image = get_post_meta( $this->workspace->content_id, 'apple_news_use_image_component', true );
				$role      = $use_image ? 'image' : 'photo';
				break;
		}

		// Fork for caption vs. not.
		if (
			! empty( $html['caption'] )
			&& true === $theme->get_value( 'cover_caption' )
		) {
			$this->register_json(
				'jsonWithCaption',
				[
					'#caption#'          => $html['caption'],
					'#role#'             => $role,
					'#url#'              => $url,
					'#caption_tracking#' => intval( $theme->get_value( 'caption_tracking' ) ) / 100,
				]
			);
		} else {
			$this->register_json(
				'json',
				[
					'#role#' => $role,
					'#url#'  => $url,
				]
			);
		}

		$this->set_default_layout();
	}

	/**
	 * Set the default layout for the component.
	 *
	 * @access private
	 */
	private function set_default_layout() {

		// Get information about the currently loaded theme.
		$theme = \Apple_Exporter\Theme::get_used();

		$this->register_layout(
			'headerPhotoLayout',
			'headerPhotoLayout',
			[
				'#layout_columns#' => $theme->get_layout_columns(),
			]
		);

		$this->register_layout(
			'headerPhotoLayoutWithCaption',
			'headerPhotoLayoutWithCaption',
			[
				'#caption_line_height#' => $theme->get_value( 'caption_line_height' ),
				'#layout_columns#'      => $theme->get_layout_columns(),
			]
		);

		$this->register_layout(
			'headerBelowTextPhotoLayout',
			'headerBelowTextPhotoLayout',
			[
				'#layout_columns#' => $theme->get_layout_columns(),
			]
		);
	}
}
