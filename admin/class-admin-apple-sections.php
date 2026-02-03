<?php
/**
 * Publish to Apple News Admin: Admin_Apple_Sections class
 *
 * Contains a class which is used to manage Sections.
 *
 * @package Apple_News
 * @since 1.2.2
 */

use Apple_Actions\Index\Section;
use Apple_News\Admin\Automation;

/**
 * This class is in charge of handling the management of Apple News sections.
 *
 * @since 1.2.2
 */
class Admin_Apple_Sections extends Apple_News {

	/**
	 * Returns an array of section data without requiring an instance of the object.
	 *
	 * @param string $channel_key Optional. The channel key ('primary' or 'secondary'). Defaults to 'primary'.
	 * @return array An array of section data.
	 */
	public static function get_sections( $channel_key = 'primary' ) {

		// Try to load from cache.
		$transient_key = Apple_News_Channels::get_sections_transient_key( $channel_key );
		$sections      = get_transient( $transient_key );
		if ( false !== $sections ) {
			return $sections;
		}

		// Try to get sections. The get_sections call sets the transient.
		$admin_settings = new Admin_Apple_Settings();
		$section_api    = new Section( $admin_settings->fetch_settings() );
		$section_api->set_channel_key( $channel_key );
		$sections = $section_api->get_sections();
		if ( empty( $sections ) || ! is_array( $sections ) ) {
			$sections = [];
			Admin_Apple_Notice::error(
				__( 'Unable to fetch a list of sections.', 'apple-news' )
			);
		}

		return $sections;
	}

	/**
	 * Given a post ID, returns an array of section URLs based on applied taxonomy.
	 *
	 * Supports overrides for manual section selection and fallback to postmeta
	 * when no mappings are set.
	 *
	 * @param int    $post_id     The ID of the post to query.
	 * @param string $format      The return format to use. Can be 'url' or 'raw'.
	 * @param string $channel_key Optional. The channel key ('primary' or 'secondary'). Defaults to post's channel.
	 *
	 * @return array An array of section data according to the requested format.
	 */
	public static function get_sections_for_post( $post_id, $format = 'url', $channel_key = null ) {

		// Determine the channel key to use.
		if ( null === $channel_key ) {
			$channel_key = Apple_News_Channels::get_channel_for_post( $post_id );
		}

		$meta_suffix = Apple_News_Channels::get_meta_suffix( $channel_key );

		// Try to load sections from postmeta.
		$meta_value = get_post_meta( $post_id, 'apple_news_sections' . $meta_suffix, true );
		if ( ! empty( $meta_value ) && is_array( $meta_value ) ) {
			return $meta_value;
		}

		// Determine if there are automation rules defined for this post.
		$rules = Automation::get_automation_for_post( $post_id );
		if ( empty( $rules ) ) {
			return [];
		}

		// Convert sections returned from the API into the requested format.
		$sections     = [];
		$sections_raw = self::get_sections( $channel_key );
		foreach ( $sections_raw as $section ) {

			// Ensure we have an ID to key off of.
			if ( empty( $section->id ) ) {
				continue;
			}

			// Fork for format.
			switch ( $format ) {
				case 'raw':
					$sections[ $section->id ] = $section;
					break;
				case 'url':
					if ( ! empty( $section->links->self ) ) {
						$sections[ $section->id ] = $section->links->self;
					}
					break;
			}
		}

		// Loop through automation rules to determine sections.
		$post_sections = [];
		foreach ( $rules as $rule ) {
			if ( 'links.sections' === ( $rule['field'] ?? '' )
				&& ! empty( $rule['value'] )
				&& ! empty( $sections[ $rule['value'] ] )
			) {
				$post_sections[] = $sections[ $rule['value'] ];
			}
		}

		// Eliminate duplicates.
		$post_sections = array_unique( $post_sections );

		// If we get here and no sections are specified, fall back to Main.
		if ( empty( $post_sections ) ) {
			$post_sections[] = reset( $sections );
		}

		/**
		 * Filters the sections for a post.
		 *
		 * @since 2.1.0
		 *
		 * @param array  $post_sections The sections for the post.
		 * @param int    $post_id       The post ID.
		 * @param string $format        The section format (e.g., 'url').
		 */
		return apply_filters( 'apple_news_get_sections_for_post', $post_sections, $post_id, $format );
	}
}
