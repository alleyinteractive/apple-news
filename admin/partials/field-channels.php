<?php
/**
 * Publish to Apple News partials: Additional channels on the API settings section
 *
 * @global array<string, array<string, string>> $channels Saved extra channels, keyed by slug.
 *
 * @package Apple_News
 */

/**
 * Renders one channel row.
 *
 * @param string                $index   The row's index in the submitted array.
 * @param string                $slug    The channel slug, or empty for a new row.
 * @param array<string, string> $channel The saved channel, or an empty array for a new row.
 */
$apple_news_channel_row = static function ( string $index, string $slug, array $channel ): void {
	$name = 'apple_news_channels[' . $index . ']';
	?>
	<tr class="apple-news-channel-row">
		<td>
			<input type="text" name="<?php echo esc_attr( $name . '[label]' ); ?>" value="<?php echo esc_attr( $channel['label'] ?? '' ); ?>" aria-label="<?php esc_attr_e( 'Label', 'apple-news' ); ?>" />
		</td>
		<td>
			<input type="text" name="<?php echo esc_attr( $name . '[slug]' ); ?>" value="<?php echo esc_attr( $slug ); ?>"<?php echo '' !== $slug ? ' readonly' : ''; ?> aria-label="<?php esc_attr_e( 'Slug', 'apple-news' ); ?>" />
		</td>
		<td>
			<input type="file" class="apple-news-channel-papi-file" accept=".papi" aria-label="<?php esc_attr_e( 'Channel configuration file', 'apple-news' ); ?>" />
			<textarea class="apple-news-channel-papi-input" aria-label="<?php esc_attr_e( 'Channel configuration file contents', 'apple-news' ); ?>"></textarea>
			<input type="hidden" class="apple-news-channel-id" name="<?php echo esc_attr( $name . '[channel]' ); ?>" value="<?php echo esc_attr( $channel['channel'] ?? '' ); ?>" />
			<input type="hidden" class="apple-news-channel-key" name="<?php echo esc_attr( $name . '[key]' ); ?>" value="<?php echo esc_attr( $channel['key'] ?? '' ); ?>" />
			<input type="hidden" class="apple-news-channel-secret" name="<?php echo esc_attr( $name . '[secret]' ); ?>" value="<?php echo esc_attr( $channel['secret'] ?? '' ); ?>" />
			<?php if ( ! empty( $channel['channel'] ) ) : ?>
				<p class="description">
					<?php
					// translators: %s is the Apple News channel ID.
					echo esc_html( sprintf( __( 'Channel ID: %s', 'apple-news' ), $channel['channel'] ) );
					?>
				</p>
			<?php endif; ?>
		</td>
		<td>
			<button type="button" class="button-secondary apple-news-channel-remove"><?php esc_html_e( 'Remove', 'apple-news' ); ?></button>
		</td>
	</tr>
	<?php
};
?>
<h3><?php esc_html_e( 'Additional Channels', 'apple-news' ); ?></h3>
<p class="description"><?php esc_html_e( 'Posts go to the channel above unless code using the apple_news_channel_for_post filter sends them to one of these. The slug is what that code refers to, and cannot change once saved.', 'apple-news' ); ?></p>
<input type="hidden" name="apple_news_channels_submitted" value="1" />
<table class="widefat apple-news-channels">
	<thead>
		<tr>
			<th scope="col"><?php esc_html_e( 'Label', 'apple-news' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Slug', 'apple-news' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Configuration File', 'apple-news' ); ?></th>
			<th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'apple-news' ); ?></span></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$apple_news_channel_index = 0;
		foreach ( $channels as $apple_news_channel_slug => $apple_news_channel ) {
			$apple_news_channel_row( (string) $apple_news_channel_index++, $apple_news_channel_slug, $apple_news_channel );
		}
		?>
	</tbody>
</table>
<template id="apple-news-channel-row-template">
	<?php $apple_news_channel_row( '__INDEX__', '', [] ); ?>
</template>
<p><button type="button" class="button-secondary apple-news-channel-add"><?php esc_html_e( 'Add Channel', 'apple-news' ); ?></button></p>
