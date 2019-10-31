<?php
/**
 * Publish to Apple News partials: Single Push page template
 *
 * @package Apple_News
 */

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Publish', 'apple-news' ); ?> &ldquo;<?php echo esc_html( $post->post_title ); ?>&rdquo;</h1>

	<?php if ( isset( $message ) ) : ?>
	<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
		<p><strong><?php echo wp_kses_post( $message ); ?></strong></p>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'apple-news' ); ?></span></button>
	</div>
	<?php endif; ?>

	<form method="post">
		<?php wp_nonce_field( 'publish', 'apple_news_nonce' ); ?>
		<?php do_action( 'apple_news_before_single_settings' ); ?>
		<table class="form-table">
			<?php if ( ! empty( $sections ) ) : ?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Sections', 'apple-news' ); ?></th>
				<td>
					<?php \Admin_Apple_Meta_Boxes::build_sections_override( $post->ID ); ?>
					<div class="apple-news-sections">
						<?php \Admin_Apple_Meta_Boxes::build_sections_field( $post->ID ); ?>
						<p class="description"><?php esc_html_e( 'Select the sections in which to publish this article. If none are selected, it will be published to the default section.', 'apple-news' ); ?></p>
					</div>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Paid?', 'apple-news' ); ?></th>
				<td>
					<label for="apple-news-is-paid">
						<input id="apple-news-is-paid" name="apple_news_is_paid" type="checkbox" value="1" <?php checked( $post_meta['apple_news_is_paid'][0] ); ?>>
						<?php esc_html_e( 'Check this to indicate that viewing the article requires a paid subscription. Note that Apple must approve your channel for paid content before using this feature.', 'apple-news' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Preview?', 'apple-news' ); ?></th>
				<td>
					<label for="apple-news-is-preview">
						<input id="apple-news-is-preview" name="apple_news_is_preview" type="checkbox" value="1" <?php checked( $post_meta['apple_news_is_preview'][0] ); ?>>
						<?php esc_html_e( 'Check this to publish the article as a draft.', 'apple-news' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Hidden?', 'apple-news' ); ?></th>
				<td>
					<label for="apple-news-is-hidden">
						<input id="apple-news-is-hidden" name="apple_news_is_hidden" type="checkbox" value="1" <?php checked( $post_meta['apple_news_is_hidden'][0] ); ?>>
						<?php esc_html_e( 'Hidden articles are visible to users who have a link to the article, but do not appear in feeds.', 'apple-news' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Sponsored?', 'apple-news' ); ?></th>
				<td>
					<label for="apple-news-is-sponsored">
						<input id="apple-news-is-sponsored" name="apple_news_is_sponsored" type="checkbox" value="1" <?php checked( $post_meta['apple_news_is_sponsored'][0] ); ?>>
						<?php esc_html_e( 'Check this to indicate this article is sponsored content.', 'apple-news' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Maturity Rating', 'apple-news' ); ?></th>
				<td>
					<select id="apple-news-maturity-rating" name="apple_news_maturity_rating">
						<option value=""></option>
						<?php foreach ( \Apple_News::$maturity_ratings as $rating ) : ?>
							<option value="<?php echo esc_attr( $rating ); ?>" <?php selected( isset( $post_meta['apple_news_maturity_rating'][0] ) ? $post_meta['apple_news_maturity_rating'][0] : '', $rating ); ?>><?php echo esc_html( ucwords( strtolower( $rating ) ) ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Select the optional maturity rating for this post.', 'apple-news' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Pull quote', 'apple-news' ); ?></th>
				<td>
					<textarea name="apple_news_pullquote" placeholder="<?php esc_attr_e( 'A pull quote is a key phrase, quotation, or excerpt that has been pulled from an article and used as a graphic element, serving to entice readers into the article or to highlight a key topic.', 'apple-news' ); ?>" rows="10" class="large-text"><?php echo ( ! empty( $post_meta['apple_news_pullquote'][0] ) ) ? esc_textarea( $post_meta['apple_news_pullquote'][0] ) : ''; ?></textarea>
					<p class="description"><?php esc_html_e( 'This is optional and can be left blank.', 'apple-news' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Pull quote position', 'apple-news' ); ?></th>
				<td>
					<?php $pullquote_position = ! empty( $post_meta['apple_news_pullquote_position'][0] ) ? $post_meta['apple_news_pullquote_position'][0] : 'middle'; ?>
					<select name="apple_news_pullquote_position">
						<option <?php selected( $pullquote_position, 'top' ); ?> value="top"><?php esc_html_e( 'top', 'apple-news' ); ?></option>
						<option <?php selected( $pullquote_position, 'middle' ); ?> value="middle"><?php esc_html_e( 'middle', 'apple-news' ); ?></option>
						<option <?php selected( $pullquote_position, 'bottom' ); ?> value="bottom"><?php esc_html_e( 'bottom', 'apple-news' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'The position in the article where the pull quote will appear.', 'apple-news' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Cover art', 'apple-news' ); ?></th>
				<td>
					<?php if ( $enable_cover_art ) : ?>
						<?php include plugin_dir_path( __FILE__ ) . 'cover-art.php'; ?>
					<?php else : ?>
						<?php
							printf(
								/* translators: First token is opening a tag, second is closing a tag */
								esc_html__( 'Cover Art must be enabled on the %1$ssettings page%2$s.', 'apple-news' ),
								'<a href="' . esc_url( admin_url( 'admin.php?page=apple-news-options' ) ) . '">',
								'</a>'
							);
						?>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php do_action( 'apple_news_after_single_settings' ); ?>

		<p class="submit">
			<a href="<?php echo esc_url( Admin_Apple_Index_Page::action_query_params( '', admin_url( 'admin.php?page=apple_news_index' ) ) ); ?>" class="button"><?php esc_html_e( 'Back', 'apple-news' ); ?></a>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings and Publish to Apple News', 'apple-news' ); ?></button>
		</p>
	</form>
</div>
