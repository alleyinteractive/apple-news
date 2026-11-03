<?php
/**
 * Publish to Apple News: Apple_News_Channels class
 *
 * Contains helper methods for multi-channel support.
 *
 * @since 2.7.0
 * @package Apple_News
 */

/**
 * Helper class for multi-channel functionality.
 *
 * @since 2.7.0
 */
class Apple_News_Channels {

	/**
	 * Primary channel key.
	 *
	 * @var string
	 */
	const string PRIMARY = 'primary';

	/**
	 * Secondary channel key.
	 *
	 * @var string
	 */
	const string SECONDARY = 'secondary';

	/**
	 * Whether posts can go to any channel besides the primary one.
	 *
	 * @return bool True when at least one extra channel is configured.
	 */
	public static function is_enabled(): bool {
		return ! empty( self::get_channels() );
	}

	/**
	 * Check if the secondary channel is configured.
	 *
	 * @param \Apple_Exporter\Settings|null $settings Optional. Settings object.
	 * @return bool
	 */
	public static function is_secondary_configured( $settings = null ): bool {
		// Use the centralized check if no settings object is passed.
		if ( null === $settings ) {
			return \Apple_News::is_channel_initialized( self::SECONDARY );
		}

		return ! empty( $settings->api_channel_2 )
			&& ! empty( $settings->api_key_2 )
			&& ! empty( $settings->api_secret_2 );
	}

	/**
	 * Get the channel key for a post ('primary' or 'secondary').
	 *
	 * @param int $post_id The post ID.
	 * @return string
	 */
	public static function get_channel_for_post( int $post_id ): string {
		$channel = self::PRIMARY;

		/**
		 * Filters the channel key for a specific post.
		 *
		 * @param string $channel The channel key ('primary' or 'secondary'). Defaults to 'primary'.
		 * @param int    $post_id The post ID.
		 */
		return apply_filters( 'apple_news_channel_for_post', $channel, $post_id );
	}

	/**
	 * Get the API credentials for a specific channel.
	 *
	 * @param string                        $channel_key The channel slug: 'primary' or a slug from get_channels().
	 * @param \Apple_Exporter\Settings|null $settings    Optional. Settings object, used for the primary channel.
	 * @return array{channel: string, key: string, secret: string} The API credentials, empty for an unknown slug.
	 */
	public static function get_credentials( string $channel_key, $settings = null ): array {
		if ( self::PRIMARY !== $channel_key ) {
			$channel = self::get_channels()[ $channel_key ] ?? [];

			return [
				'channel' => $channel['channel'] ?? '',
				'key'     => $channel['key'] ?? '',
				'secret'  => $channel['secret'] ?? '',
			];
		}

		if ( null === $settings ) {
			$admin_settings = new Admin_Apple_Settings();
			$settings       = $admin_settings->fetch_settings();
		}

		return [
			'channel' => $settings->api_channel ?? '',
			'key'     => $settings->api_key ?? '',
			'secret'  => $settings->api_secret ?? '',
		];
	}

	/**
	 * Get the channels a post can be sent to besides the primary one, keyed by slug.
	 *
	 * @return array<string, array{label: string, channel: string, key: string, secret: string}>
	 */
	public static function get_channels(): array {
		$channels = get_option( 'apple_news_channels', [] );

		return is_array( $channels ) ? $channels : [];
	}

	/**
	 * Get the key suffix for a channel (empty for primary, '_2' for secondary).
	 *
	 * @param string $channel_key The channel key.
	 * @return string
	 */
	public static function get_meta_suffix( string $channel_key ): string {
		return self::SECONDARY === $channel_key ? '_2' : '';
	}

	/**
	 * Get the channel label for display.
	 *
	 * @param string $channel_key The channel key.
	 * @return string The human-readable label.
	 */
	public static function get_channel_label( string $channel_key ): string {
		if ( self::PRIMARY === $channel_key ) {
			return __( 'Primary Channel', 'apple-news' );
		}

		return self::get_channels()[ $channel_key ]['label'] ?? $channel_key;
	}

	/**
	 * Get the transient key for sections cache.
	 *
	 * @param string $channel_key The channel key.
	 * @return string The transient key.
	 */
	public static function get_sections_transient_key( string $channel_key ): string {
		$suffix = static::get_meta_suffix( $channel_key );

		return 'apple_news_sections' . $suffix;
	}

	/**
	 * Get the transient key for channel cache.
	 *
	 * @param string $channel_key The channel key.
	 * @return string The transient key.
	 */
	public static function get_channel_transient_key( string $channel_key ): string {
		$suffix = static::get_meta_suffix( $channel_key );

		return 'apple_news_channel' . $suffix;
	}
}
