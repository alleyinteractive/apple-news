<?php
/**
 * Publish to Apple News: Apple_News_Channels class
 *
 * Contains helper methods for multi-channel support.
 *
 * @since 2.10.0
 * @package Apple_News
 */

/**
 * Helper class for multi-channel functionality.
 *
 * Channels are identified by their Apple News channel ID. A null channel ID stands for the primary channel,
 * the one set in the plugin's API settings.
 *
 * @since 2.10.0
 */
class Apple_News_Channels {

	/**
	 * Whether posts can go to any channel besides the primary one.
	 *
	 * @return bool True when at least one extra channel is configured.
	 */
	public static function is_enabled(): bool {
		return ! empty( self::get_channels() );
	}

	/**
	 * Get the ID of the primary channel, the one set in the plugin's API settings.
	 *
	 * @param \Apple_Exporter\Settings|null $settings Optional. Settings object to read it from.
	 * @return string The channel ID, empty when the API settings have none.
	 */
	public static function get_primary_channel_id( $settings = null ): string {
		if ( null === $settings ) {
			$settings = ( new Admin_Apple_Settings() )->fetch_settings();
		}

		return (string) ( $settings->api_channel ?? '' );
	}

	/**
	 * Whether a channel ID refers to the primary channel.
	 *
	 * @param string|null                   $channel_id The channel ID, or null for the primary channel.
	 * @param \Apple_Exporter\Settings|null $settings   Optional. Settings object to read the primary channel from.
	 * @return bool
	 */
	public static function is_primary( ?string $channel_id, $settings = null ): bool {
		return null === $channel_id
			|| '' === $channel_id
			|| self::get_primary_channel_id( $settings ) === $channel_id;
	}

	/**
	 * Get the ID of the channel a post goes to: the one its article is on, if it has one, otherwise the filtered one.
	 *
	 * @param int $post_id The post ID.
	 * @return string The channel ID.
	 */
	public static function get_channel_for_post( int $post_id ): string {
		// An article can only be read, updated or deleted through the channel it was published to.
		$article_channel = get_post_meta( $post_id, 'apple_news_api_channel', true );
		if ( is_string( $article_channel ) && '' !== $article_channel && get_post_meta( $post_id, 'apple_news_api_id', true ) ) {
			return $article_channel;
		}

		/**
		 * Filters the channel a post goes to.
		 *
		 * @param string|null $channel_id The Apple News channel ID, or null for the primary channel.
		 * @param int         $post_id    The post ID.
		 */
		$channel_id = apply_filters( 'apple_news_channel_for_post', null, $post_id );

		return is_string( $channel_id ) && '' !== $channel_id ? $channel_id : self::get_primary_channel_id();
	}

	/**
	 * Get the API credentials for a specific channel.
	 *
	 * @param string|null                   $channel_id The channel ID, or null for the primary channel.
	 * @param \Apple_Exporter\Settings|null $settings   Optional. Settings object, used for the primary channel.
	 * @return array{channel: string, key: string, secret: string} The API credentials, empty for an unknown channel.
	 */
	public static function get_credentials( ?string $channel_id = null, $settings = null ): array {
		$channels = self::get_channels();
		if ( null !== $channel_id && isset( $channels[ $channel_id ] ) ) {
			return [
				'channel' => $channel_id,
				'key'     => $channels[ $channel_id ]['key'] ?? '',
				'secret'  => $channels[ $channel_id ]['secret'] ?? '',
			];
		}

		if ( null === $settings ) {
			$settings = ( new Admin_Apple_Settings() )->fetch_settings();
		}

		if ( ! self::is_primary( $channel_id, $settings ) ) {
			return [
				'channel' => '',
				'key'     => '',
				'secret'  => '',
			];
		}

		return [
			'channel' => $settings->api_channel ?? '',
			'key'     => $settings->api_key ?? '',
			'secret'  => $settings->api_secret ?? '',
		];
	}

	/**
	 * Get the channels a post can be sent to besides the primary one, keyed by channel ID.
	 *
	 * @return array<string, array{label: string, key: string, secret: string}>
	 */
	public static function get_channels(): array {
		$channels = get_option( 'apple_news_channels', [] );

		return is_array( $channels ) ? $channels : [];
	}

	/**
	 * Replace the extra channels with the rows submitted from the settings screen.
	 *
	 * @param array<int, array<string, string>> $rows Rows with label, channel, key and secret.
	 */
	public static function save_channels( array $rows ): void {
		$primary_id = self::get_primary_channel_id();
		$channels   = [];
		foreach ( $rows as $row ) {
			$channel_id = sanitize_text_field( $row['channel'] ?? '' );
			if ( '' === $channel_id || $primary_id === $channel_id ) {
				continue;
			}

			$channels[ $channel_id ] = [
				'label'  => sanitize_text_field( $row['label'] ?? '' ),
				'key'    => sanitize_text_field( $row['key'] ?? '' ),
				'secret' => sanitize_text_field( $row['secret'] ?? '' ),
			];
		}

		foreach ( array_keys( array_merge( self::get_channels(), $channels ) ) as $channel_id ) {
			delete_transient( self::get_sections_transient_key( (string) $channel_id ) );
			delete_transient( self::get_channel_transient_key( (string) $channel_id ) );
		}

		update_option( 'apple_news_channels', $channels, false );
	}

	/**
	 * Get the suffix that keeps a channel's cached data apart from other channels'.
	 *
	 * @param string|null $channel_id The channel ID, or null for the primary channel.
	 * @return string Empty for the primary channel, so its cache keys match single-channel sites.
	 */
	private static function get_cache_suffix( ?string $channel_id ): string {
		return self::is_primary( $channel_id ) ? '' : '_' . $channel_id;
	}

	/**
	 * Get the channel label for display.
	 *
	 * @param string|null $channel_id The channel ID, or null for the primary channel.
	 * @return string The human-readable label.
	 */
	public static function get_channel_label( ?string $channel_id ): string {
		$channels = self::get_channels();
		if ( null !== $channel_id && isset( $channels[ $channel_id ] ) ) {
			return '' !== ( $channels[ $channel_id ]['label'] ?? '' ) ? $channels[ $channel_id ]['label'] : $channel_id;
		}

		if ( self::is_primary( $channel_id ) ) {
			return __( 'Primary Channel', 'apple-news' );
		}

		return (string) $channel_id;
	}

	/**
	 * Get the transient key for sections cache.
	 *
	 * @param string|null $channel_id The channel ID, or null for the primary channel.
	 * @return string The transient key.
	 */
	public static function get_sections_transient_key( ?string $channel_id ): string {
		return 'apple_news_sections' . self::get_cache_suffix( $channel_id );
	}

	/**
	 * Get the transient key for channel cache.
	 *
	 * @param string|null $channel_id The channel ID, or null for the primary channel.
	 * @return string The transient key.
	 */
	public static function get_channel_transient_key( ?string $channel_id ): string {
		return 'apple_news_channel' . self::get_cache_suffix( $channel_id );
	}
}
