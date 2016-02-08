<div class="wrap">
	<h1>&ldquo;<?php echo esc_html( $post->post_title ); ?>&rdquo; <?php esc_html_e( 'Options', 'apple-news' ) ?></h1>

	<?php if ( isset( $message ) ): ?>
	<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
		<p><strong><?php echo wp_kses_post( $message ) ?></strong></p>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'apple-news' ) ?></span></button>
	</div>
	<?php endif; ?>

	<form method="post">
		<?php wp_nonce_field( 'export', 'apple-news-nonce' ); ?>
		<?php do_action( 'apple_news_before_single_settings' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Pull quote', 'apple-news' ) ?></th>
				<td>
				<textarea name="pullquote" placeholder="Lorem ipsum..." rows="10" class="large-text"><?php if ( ! empty( $post_meta[ 'apple_news_pullquote' ][0] ) ) { echo esc_textarea( $post_meta[ 'apple_news_pullquote' ][0] ); } ?></textarea>
					<p class="description"><?php esc_html_e( 'This is optional and can be left blank. A pull quote is a key phrase, quotation, or excerpt that has been pulled from an article and used as a graphic element, serving to entice readers into the article or to highlight a key topic.', 'apple-news' ) ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Pull quote position', 'apple-news' ) ?></th>
				<td>
					<?php $pullquote_position = ! empty( $post_meta['apple_news_pullquote_position'][0] ) ? $post_meta['apple_news_pullquote_position'][0] : false; ?>
					<select name="pullquote_position">
						<option <?php selected( $pullquote_position, 'top' ) ?> value="top"><?php esc_html_e( 'top', 'apple-news' ) ?></option>
						<option <?php selected( $pullquote_position, 'middle' ) ?> value="middle"><?php esc_html_e( 'middle', 'apple-news' ) ?></option>
						<option <?php selected( $pullquote_position, 'bottom' ) ?> value="bottom"><?php esc_html_e( 'bottom', 'apple-news' ) ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'The position in the article the pull quote will appear.', 'apple-news' ) ?></p>
				</td>
			</tr>
		</table>
		<?php do_action( 'apple_news_after_single_settings' ); ?>

		<p class="submit">
			<a href="<?php echo esc_url( Admin_Apple_Index_Page::action_query_params( '', admin_url( 'admin.php?page=apple_news_index' ) ) ) ?>" class="button"><?php esc_html_e( 'Back', 'apple-news' ) ?></a>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'apple-news' ) ?></button>
		</p>
	</form>
</div>
