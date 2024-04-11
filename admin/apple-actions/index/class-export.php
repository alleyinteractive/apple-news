<?php
/**
 * Publish to Apple News: \Apple_Actions\Index\Export class
 *
 * @package Apple_News
 * @subpackage Apple_Actions\Index
 */

namespace Apple_Actions\Index;

require_once plugin_dir_path( __FILE__ ) . '../class-action.php';
require_once plugin_dir_path( __FILE__ ) . '../class-action-exception.php';
require_once plugin_dir_path( __FILE__ ) . '../../../includes/apple-exporter/autoload.php';

use Apple_Actions\Action;
use Apple_Exporter\Exporter;
use Apple_Exporter\Exporter_Content;
use Apple_Exporter\Exporter_Content_Settings;
use Apple_Exporter\Theme;
use Apple_Exporter\Third_Party\Jetpack_Tiled_Gallery;
use Apple_News;
use BC_Setup;

/**
 * A class to handle an export request from the admin.
 *
 * @package Apple_News
 * @subpackage Apple_Actions\Index
 */
class Export extends Action {

	/**
	 * A variable to keep track of whether we are in the middle of an export.
	 *
	 * @var bool
	 * @access private
	 */
	private static $exporting = false;

	/**
	 * ID of the post being exported.
	 *
	 * @var int
	 * @access private
	 */
	private $id;

	/**
	 * Constructor.
	 *
	 * @param \Apple_Exporter\Settings $settings Settings in use during the current run.
	 * @param int|null                 $id       Optional. The ID of the content to export.
	 * @param array|null               $sections Optional. Sections to map for this post.
	 * @access public
	 */
	public function __construct( $settings, $id = null, $sections = null ) {
		parent::__construct( $settings );
		$this->id = $id;
		$this->set_theme();
		Jetpack_Tiled_Gallery::instance();
	}

	/**
	 * A function to determine whether an export is currently in progress.
	 *
	 * @access public
	 * @return bool
	 */
	public static function is_exporting() {
		return self::$exporting;
	}

	/**
	 * Sets the exporting flag.
	 *
	 * @param bool $exporting The new value of the exporting flag.
	 */
	public static function set_exporting( $exporting ) {
		self::$exporting = (bool) $exporting;
	}

	/**
	 * Perform the export and return the results.
	 *
	 * @return string The JSON data
	 * @access public
	 */
	public function perform() {
		self::set_exporting( true );
		$exporter = $this->fetch_exporter();
		$json     = $exporter->export();
		self::set_exporting( false );

		return $json;
	}

	/**
	 * Fetches an instance of Exporter.
	 *
	 * @return Exporter
	 * @access public
	 */
	public function fetch_exporter() {

		global $post;

		/**
		 * Actions to be fired before the Exporter class is created and returned.
		 *
		 * @param int $post_id The ID of the post being exported.
		 */
		do_action( 'apple_news_do_fetch_exporter', $this->id );

		/**
		 * Fetch WP_Post object, and all required post information to fill up the
		 * Exporter_Content instance.
		 */
		$post = get_post( $this->id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Only include excerpt if exists.
		$excerpt = has_excerpt( $post ) ? wp_strip_all_tags( $post->post_excerpt ) : '';

		// Get the cover configuration.
		$post_thumb    = null;
		$cover_meta_id = get_post_meta( $this->id, 'apple_news_coverimage', true );
		$cover_caption = get_post_meta( $this->id, 'apple_news_coverimage_caption', true );
		if ( ! empty( $cover_meta_id ) ) {
			if ( empty( $cover_caption ) ) {
				$cover_caption = wp_get_attachment_caption( $cover_meta_id );
			}
			$post_thumb = [
				'caption' => ! empty( $cover_caption ) ? $cover_caption : '',
				'url'     => wp_get_attachment_url( $cover_meta_id ),
			];
		} else {
			$thumb_id       = get_post_thumbnail_id( $this->id );
			$post_thumb_url = wp_get_attachment_url( $thumb_id );
			if ( empty( $cover_caption ) ) {
				$cover_caption = wp_get_attachment_caption( $thumb_id );
			}
			if ( ! empty( $post_thumb_url ) ) {
				// If the post thumb URL is root-relative, convert it to fully-qualified.
				if ( str_starts_with( $post_thumb_url, '/' ) ) {
					$post_thumb_url = site_url( $post_thumb_url );
				}

				// Compile the post_thumb object using the URL and caption from the featured image.
				$post_thumb = [
					'caption' => ! empty( $cover_caption ) ? $cover_caption : '',
					'url'     => $post_thumb_url,
				];
			}
		}

		// If there is a cover caption but not a cover image URL, preserve it, so it can take precedence later.
		if ( empty( $post_thumb ) && ! empty( $cover_caption ) ) {
			$post_thumb = [
				'caption' => $cover_caption,
				'url'     => '',
			];
		}

		// Build the byline.
		$byline = $this->format_byline( $post );

		// Build the author.
		$author = $this->format_author( $post );

		// Build the publication date.
		$date = $this->format_date( $post );

		// Get the content.
		$content = $this->get_content( $post );

		// Get the slug.
		$slug = get_post_meta( $post->ID, 'apple_news_slug', true );

		/*
		 * If the excerpt looks too similar to the content, remove it.
		 * We do this before the filter, to allow overrides for the final value.
		 * This essentially prevents the case where someone intentionally copies
		 * the first paragraph of content into the `post_excerpt` field and
		 * unintentionally introduces a duplicate content issue.
		 */
		if ( ! empty( $excerpt ) ) {
			$content_normalized = strtolower( str_replace( ' ', '', wp_strip_all_tags( $content ) ) );
			$excerpt_normalized = strtolower( str_replace( ' ', '', wp_strip_all_tags( $excerpt ) ) );
			if ( str_contains( $content_normalized, $excerpt_normalized ) ) {
				$excerpt = '';
			}
		}

		/**
		 * Filters the title of an article before it is sent to Apple News.
		 *
		 * @param string $title   The title of the post.
		 * @param int    $post_id The ID of the post.
		 */
		$title = apply_filters( 'apple_news_exporter_title', $post->post_title, $post->ID );

		/**
		 * Filters the excerpt of an article before it is sent to Apple News.
		 *
		 * The excerpt is used for the Intro component, if it is active.
		 *
		 * @param string $excerpt The excerpt of the post.
		 * @param int    $post_id The ID of the post.
		 */
		$excerpt = apply_filters( 'apple_news_exporter_excerpt', $excerpt, $post->ID );

		/**
		 * Filters the cover image URL of an article before it is sent to Apple News.
		 *
		 * The cover image URL is used for the Cover component, if it is active.
		 *
		 * @param string|null $url     The cover image URL for the post.
		 * @param int         $post_id The ID of the post.
		 */
		$cover_url = apply_filters( 'apple_news_exporter_post_thumb', ! empty( $post_thumb['url'] ) ? $post_thumb['url'] : null, $post->ID );

		/**
		 * Filters the byline of an article before it is sent to Apple News.
		 *
		 * The byline is used for the Byline component, if it is active.
		 *
		 * @param string $byline  The byline for the post.
		 * @param int    $post_id The ID of the post.
		 */
		$byline = apply_filters( 'apple_news_exporter_byline', $byline, $post->ID );

		/**
		 * Filters the author of an article before it is sent to Apple News.
		 *
		 * The author is used for the Author component, if it is active.
		 *
		 * @since 2.3.0
		 *
		 * @param string $author  The author for the post.
		 * @param int    $post_id The ID of the post.
		 */
		$author = apply_filters( 'apple_news_exporter_author', $author, $post->ID );

		/**
		 * Filters the date of an article before it is sent to Apple News.
		 *
		 * The date is used for the Date component, if it is active.
		 *
		 * @since 2.3.0
		 *
		 * @param string $date The date for the post.
		 * @param int    $post_id The ID of the post.
		 */
		$date = apply_filters( 'apple_news_exporter_date', $date, $post->ID );

		/**
		 * Filters the slug of an article before it is sent to Apple News.
		 *
		 * The slug is used for the Slug component, if it is active.
		 *
		 * @since 2.2.0
		 *
		 * @param string $slug    The slug for the post.
		 * @param int    $post_id The ID of the post.
		 */
		$slug = apply_filters( 'apple_news_exporter_slug', $slug, $post->ID );

		/**
		 * Filters the HTML of a post after `the_content` filter is called, but
		 * before the HTML is parsed into Apple News Format.
		 *
		 * This filter could be useful to remove content known to be incompatible
		 * with Apple News, or to add content stored in other areas of the
		 * database, such as postmeta or custom database tables.
		 *
		 * @param string $content The HTML content of the post, after the_content filter has been run.
		 * @param int    $post_id The ID of the post.
		 */
		$content = apply_filters( 'apple_news_exporter_content', $content, $post->ID );

		// Re-apply the cover URL after filtering.
		if ( ! empty( $cover_url ) ) {
			$cover_caption = ! empty( $post_thumb['caption'] ) ? $post_thumb['caption'] : '';

			/**
			 * Filters the cover image caption of an article before it is sent to Apple News.
			 *
			 * The cover image caption is used for the Cover component, if it is
			 * active, and if support for cover image captions is turned on in theme
			 * settings.
			 *
			 * @param string $caption The caption to use for the cover image.
			 * @param int    $post_id The ID of the post.
			 *
			 * @since 2.1.0
			 */
			$cover_caption = apply_filters( 'apple_news_exporter_cover_caption', $cover_caption, $post->ID );

			$post_thumb = [
				'caption' => $cover_caption,
				'url'     => $cover_url,
			];
		} else {
			$post_thumb = null;
		}

		// Now pass all the variables into the Exporter_Content array.
		$base_content = new Exporter_Content(
			$post->ID,
			$title,
			$content,
			$excerpt,
			$post_thumb,
			$byline,
			$this->fetch_content_settings(),
			$slug,
			$author,
			$date
		);

		return new Exporter( $base_content, null, $this->settings );
	}

	/**
	 * Formats the byline
	 *
	 * @since 1.2.0
	 *
	 * @param \WP_Post $post   The post to use.
	 * @param string   $author Optional. Overrides author information. Defaults to author of the post.
	 * @param string   $date   Optional. Overrides the date. Defaults to the date of the post.
	 * @access public
	 * @return string
	 */
	public function format_byline( $post, $author = '', $date = '' ) {
		// Get information about the currently used theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Get the author.
		if ( empty( $author ) ) {
			$author = Apple_News::get_authors();
		}

		// Get the date.
		if ( empty( $date ) && ! empty( $post->post_date_gmt ) ) {
			$date = $post->post_date_gmt;
		}

		// Set the default date format.
		$date_format = 'M j, Y | g:i A';

		// Check for a custom byline format.
		$byline_format = $theme->get_value( 'byline_format' );
		if ( ! empty( $byline_format ) ) {
			/**
			 * Find and replace the author format placeholder name with a temporary placeholder.
			 * This is because some bylines could contain hashtags!
			 */
			$temp_byline_placeholder = 'AUTHOR' . time();
			$byline                  = str_replace( '#author#', $temp_byline_placeholder, $byline_format );

			// Attempt to parse the date format from the remaining string.
			$matches = [];
			preg_match( '/#(.*?)#/', $byline, $matches );
			if ( ! empty( $matches[1] ) && ! empty( $date ) ) {
				// Set the date using the custom format.
				$byline = str_replace( $matches[0], apple_news_date( $matches[1], strtotime( $date ) ), $byline );
			}

			// Replace the temporary placeholder with the actual byline.
			$byline = str_replace( $temp_byline_placeholder, $author, $byline );

		} else {
			// Use the default format.
			$byline = sprintf(
				'by %1$s | %2$s',
				$author,
				apple_news_date( $date_format, strtotime( $date ) )
			);
		}

		return $byline;
	}

	/**
	 * Formats the author.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Post $post   The post to use.
	 * @param string   $author Optional. Overrides author information. Defaults to author of the post.
	 * @access public
	 * @return string
	 */
	public function format_author( $post, $author = '' ) {
		// Get information about the currently used theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Get the author.
		if ( empty( $author ) ) {
			$author = Apple_News::get_authors();
		}

		// Check for a custom byline format.
		$byline_format = $theme->get_value( 'author_format' );
		if ( ! empty( $byline_format ) ) {
			/**
			 * Find and replace the author format placeholder name with a temporary placeholder.
			 * This is because some bylines could contain hashtags!
			 */
			$temp_byline_placeholder = 'AUTHOR';
			$byline                  = str_replace( '#author#', $temp_byline_placeholder, $byline_format );

			// Replace the temporary placeholder with the actual byline.
			$byline = str_replace( $temp_byline_placeholder, $author, $byline );

		} else {
			// Use the default format.
			$byline = sprintf(
				'by %1$s',
				$author
			);
		}

		return $byline;
	}

	/**
	 * Formats the publication date
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Post $post   The post to use.
	 * @param string   $date   Optional. Overrides the date. Defaults to the date of the post.
	 * @access public
	 * @return string
	 */
	public function format_date( $post, $date = '' ) {
		// Get information about the currently used theme.
		$theme = \Apple_Exporter\Theme::get_used();

		// Get the date.
		if ( empty( $date ) && ! empty( $post->post_date ) ) {
			$date = $post->post_date;
		}

		// Check for a custom byline format.
		$date_format = $theme->get_value( 'date_format' );

		if ( ! empty( $date_format ) ) {
			// Attempt to parse the date format from the remaining string.
			$matches = [];
			preg_match( '/#(.*?)#/', $date_format, $matches );
			if ( ! empty( $matches[1] ) ) {
				// Set the date using the custom format.
				$date = apple_news_date( $matches[1], strtotime( $date ) );
			}
		} else {
			// Use the default format.
			$date = sprintf(
				'%1$s',
				apple_news_date( $date_format, strtotime( $date ) )
			);
		}

		return $date;
	}

	/**
	 * Converts Brightcove Gutenberg blocks and shortcodes to video tags that
	 * can be handled by Apple News. Requires that Apple connect the Brightcove
	 * account to the Apple News channel.
	 *
	 * @since 2.1.0
	 *
	 * @param string $content The post content to filter.
	 *
	 * @return string The modified content.
	 */
	private function format_brightcove( $content ) {

		// Replace Brightcove Gutenberg blocks with Gutenberg video blocks with a specially-formatted Brightcove source URL.
		if ( preg_match_all( '/<!-- wp:bc\/brightcove ({.+?}) \/-->/', $content, $matches ) ) {
			foreach ( $matches[0] as $index => $match ) {
				$atts = json_decode( $matches[1][ $index ], true );
				if ( ! empty( $atts['account_id'] ) && ! empty( $atts['video_id'] ) ) {
					$content = str_replace(
						$match,
						sprintf(
							'<!-- wp:video -->' . "\n" . '<figure class="wp-block-video"><video controls src="https://edge.api.brightcove.com/playback/v1/accounts/%s/videos/%s" poster="%s"></video></figure>' . "\n" . '<!-- /wp:video -->',
							$atts['account_id'],
							$atts['video_id'],
							$this->get_brightcove_stillurl( $atts['account_id'], $atts['video_id'] )
						),
						$content
					);
				}
			}
		}

		// Replace Brightcove shortcodes with plain video tags with a specially-formatted Brightcove source URL.
		$bc_video_regex = '/' . get_shortcode_regex( [ 'bc_video' ] ) . '/';
		if ( preg_match_all( $bc_video_regex, $content, $matches ) ) {
			foreach ( $matches[0] as $match ) {
				$atts = shortcode_parse_atts( $match );
				if ( ! empty( $atts['account_id'] ) && ! empty( $atts['video_id'] ) ) {
					$content = str_replace(
						$match,
						sprintf(
							'<video controls src="https://edge.api.brightcove.com/playback/v1/accounts/%s/videos/%s" poster="%s"></video>',
							$atts['account_id'],
							$atts['video_id'],
							$this->get_brightcove_stillurl( $atts['account_id'], $atts['video_id'] )
						),
						$content
					);
				}
			}
		}

		return $content;
	}

	/**
	 * Given an account ID and video ID, gets the Brightcove still image URL.
	 *
	 * @param string $account_id The Brightcove account ID to use.
	 * @param string $video_id   The Brightcove video ID to use.
	 *
	 * @return string The URL to the still image. Empty string on failure.
	 */
	private function get_brightcove_stillurl( $account_id, $video_id ) {
		global $bc_accounts;

		// If the $bc_accounts global doesn't exist, bail.
		if ( empty( $bc_accounts ) ) {
			return '';
		}

		/*
		 * BC_Setup only runs if is_admin returns true, which won't be if the
		 * publish was triggered from a REST request (which it will be if the
		 * user is using Gutenberg to publish manually, or on publish of the
		 * post with auto-publish turned on). Therefore, we need to bootstrap
		 * the functionality ourselves by mimicing the behavior of the init
		 * hook.
		 */
		if ( ! class_exists( '\BC_CMS_API' ) && class_exists( '\BC_Setup' ) ) {
			( new BC_Setup() )->action_init();
		}

		// Ensure the account ID and video IDs are strings.
		$account_id = (string) $account_id;
		$video_id   = (string) $video_id;

		// Get the current account ID and switch accounts if necessary.
		$old_account_id = (string) $bc_accounts->get_account_id();
		if ( $old_account_id !== $account_id ) {
			$bc_accounts->set_current_account_by_id( $account_id );
		}

		// Initialize a new BC_CMS_API instance and fetch the video images.
		$bc_cms_api = new \BC_CMS_API();
		$response   = $bc_cms_api->video_get_images( $video_id );
		$image      = ! empty( $response['poster']['src'] ) ? $response['poster']['src'] : '';

		// Switch accounts back, if necessary.
		if ( $old_account_id !== $account_id ) {
			$bc_accounts->set_current_account_by_id( $old_account_id );
		}

		return $image;
	}

	/**
	 * Gets the content
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_Post $post The post object to extract content from.
	 * @access private
	 * @return string
	 */
	private function get_content( $post ) {
		/**
		 * Filters the HTML of a post before `the_content` filter is called, and
		 * before the HTML is parsed into Apple News Format.
		 *
		 * This filter could be useful to remove content known to be incompatible
		 * with Apple News, or to add content stored in other areas of the
		 * database which should be run through `the_content` filter, such as
		 * shortcodes stored in postmeta or custom database tables.
		 *
		 * @param string $content The post_content for the post, before the_content filter has been run.
		 * @param int    $post_id The ID of the post.
		 */
		$content = apply_filters( 'apple_news_exporter_content_pre', $post->post_content, $post->ID );

		// Replace Brightcove shortcodes and Gutenberg blocks with video tags.
		$content = $this->format_brightcove( $content );

		/**
		 * The post_content is not raw HTML, as WordPress editor cleans up
		 * paragraphs and new lines, so we need to transform the content to
		 * HTML. We use 'the_content' filter for that.
		 */
		$content = apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		// Clean up the HTML a little.
		$content = $this->remove_tags( $content );
		$content = $this->remove_entities( $content );

		return $content;
	}

	/**
	 * Remove tags incompatible with Apple News format.
	 *
	 * @since 1.4.0
	 *
	 * @param string $html The HTML to be filtered.
	 * @access private
	 * @return string
	 */
	private function remove_tags( $html ) {
		$html = preg_replace( '/<style[^>]*>.*?<\/style>/i', '', $html );
		return $html;
	}

	/**
	 * Filter the content for markdown format.
	 *
	 * @param string $content The content to be filtered.
	 *
	 * @access private
	 * @return string
	 */
	private function remove_entities( $content ) {
		if ( 'yes' === $this->get_setting( 'html_support' ) ) {
			return $content;
		}

		// Correct ampersand output.
		return str_replace( '&amp;', '&', $content );
	}

	/**
	 * Loads settings for the Exporter_Content from the WordPress post metadata.
	 *
	 * @return Exporter_Content_Settings
	 * @access private
	 * @since 0.4.0
	 */
	private function fetch_content_settings() {
		$settings = new Exporter_Content_Settings();
		foreach ( get_post_meta( $this->id ) as $name => $value ) {
			if ( str_starts_with( $name, 'apple_news_' ) ) {
				$name  = str_replace( 'apple_news_', '', $name );
				$value = $value[0];
				$settings->set( $name, $value );
			}
		}

		/**
		 * Filters the Exporter_Content_Settings object for this article.
		 *
		 * Before this filter is called, the Exporter_Content_Settings object is
		 * initialized and merged with settings stored in postmeta for this post.
		 *
		 * @param Exporter_Content_Settings $settings The content settings for this article.
		 */
		return apply_filters( 'apple_news_content_settings', $settings );
	}

	/**
	 * Sets the active theme for this session if explicitly set or mapped.
	 */
	private function set_theme(): void {
		$active_theme = Theme::get_active_theme_name();

		/**
		 * Allows the active theme to be filtered during export on a per-post basis.
		 *
		 * @since 2.4.0
		 *
		 * @param string $theme_name The name of the theme to use.
		 * @param int    $post_id    The ID of the post being exported.
		 */
		$theme_name = apply_filters( 'apple_news_active_theme', Theme::get_active_theme_name(), $this->id );
		if ( ! empty( $theme_name ) && $active_theme !== $theme_name ) {
			$theme = new Theme();
			$theme->set_name( $theme_name );
			if ( $theme->load() ) {
				$theme->use_this();
			}
		}
	}
}
