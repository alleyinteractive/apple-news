<?php
/**
 * Publish to Apple News partials: Publish Metabox template
 *
 * @package Apple_News
 */

if ( ! \Apple_News::is_initialized() ) : ?>
	<div id="apple-news-publish">
		<?php
		printf(
			/* translators: First token is opening a tag, second is closing a tag */
			esc_html__( 'You must enter your API information on the %1$ssettings page%2$s before using Publish to Apple News.', 'apple-news' ),
			'<a href="' . esc_url( admin_url( 'admin.php?page=apple-news-options' ) ) . '">',
			'</a>'
		);
		?>
	</div>
	<?php return; ?>
<?php endif;  // phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable ?>
<div id="apple-news-publish">
	<?php wp_nonce_field( $publish_action, 'apple_news_nonce' ); ?>
	<div id="apple-news-metabox-sections" class="apple-news-metabox-section">
		<h3><?php esc_html_e( 'Sections', 'apple-news' ); ?></h3>
		<?php Admin_Apple_Meta_Boxes::build_sections_override( $post->ID ); ?>
		<div class="apple-news-sections">
			<?php Admin_Apple_Meta_Boxes::build_sections_field( $post->ID ); ?>
			<p class="description"><?php esc_html_e( 'Select the sections in which to publish this article. If none are selected, it will be published to the default section.', 'apple-news' ); ?></p>
		</div>
	</div>
	<div id="apple-news-metabox-is-paid" class="apple-news-metabox-section">
		<h3><?php esc_html_e( 'Paid?', 'apple-news' ); ?></h3>
		<label for="apple-news-is-paid">
			<input id="apple-news-is-paid" name="apple_news_is_paid" type="checkbox" value="1" <?php checked( $is_paid ); ?>>
			<?php esc_html_e( 'Check this to indicate that viewing the article requires a paid subscription. Note that Apple must approve your channel for paid content before using this feature.', 'apple-news' ); ?>
		</label>
	</div>
	<div id="apple-news-metabox-is-preview" class="apple-news-metabox-section">
		<h3><?php esc_html_e( 'Preview?', 'apple-news' ); ?></h3>
		<label for="apple-news-is-preview">
			<input id="apple-news-is-preview" name="apple_news_is_preview" type="checkbox" value="1" <?php checked( $is_preview ); ?>>
			<?php esc_html_e( 'Check this to publish the article as a draft.', 'apple-news' ); ?>
		</label>
	</div>
	<div id="apple-news-metabox-is-hidden" class="apple-news-metabox-section">
		<h3><?php esc_html_e( 'Hidden?', 'apple-news' ); ?></h3>
		<label for="apple-news-is-hidden">
			<input id="apple-news-is-hidden" name="apple_news_is_hidden" type="checkbox" value="1" <?php checked( $is_hidden ); ?>>
			<?php esc_html_e( 'Hidden articles are visible to users who have a link to the article, but do not appear in feeds.', 'apple-news' ); ?>
		</label>
	</div>
	<div id="apple-news-metabox-is-sponsored" class="apple-news-metabox-section">
		<h3><?php esc_html_e( 'Sponsored?', 'apple-news' ); ?></h3>
		<label for="apple-news-is-sponsored">
			<input id="apple-news-is-sponsored" name="apple_news_is_sponsored" type="checkbox" value="1" <?php checked( $is_sponsored ); ?>>
			<?php esc_html_e( 'Check this to indicate this article is sponsored content.', 'apple-news' ); ?>
		</label>
	</div>
	<div id="apple-news-metabox-maturity-rating" class="apple-news-metabox-section apple-news-metabox-section-collapsable">
		<h3><?php esc_html_e( 'Maturity Rating', 'apple-news' ); ?></h3>
		<label for="apple-news-maturity-rating">
			<select id="apple-news-maturity-rating" name="apple_news_maturity_rating">
				<option value=""></option>
				<?php foreach ( self::$maturity_ratings as $rating ) : ?>
					<option value="<?php echo esc_attr( $rating ); ?>" <?php selected( $maturity_rating, $rating ); ?>><?php echo esc_html( ucwords( strtolower( $rating ) ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Select the optional maturity rating for this post.', 'apple-news' ); ?></p>
		</label>
	</div>
	<div id="apple-news-metabox-pullquote" class="apple-news-metabox-section apple-news-metabox-section-collapsable">
		<h3><?php esc_html_e( 'Pull quote', 'apple-news' ); ?></h3>
		<label for="apple-news-pullquote" class="screen-reader-text"><?php esc_html_e( 'Pull quote', 'apple-news' ); ?></label>
		<textarea id="apple-news-pullquote" name="apple_news_pullquote" placeholder="<?php esc_attr_e( 'A pull quote is a key phrase, quotation, or excerpt that has been pulled from an article and used as a graphic element, serving to entice readers into the article or to highlight a key topic.', 'apple-news' ); ?>" rows="6" class="large-text"><?php echo esc_textarea( $pullquote ); ?></textarea>
		<p class="description"><?php esc_html_e( 'This is optional and can be left blank.', 'apple-news' ); ?></p>
		<h4><?php esc_html_e( 'Pull quote position', 'apple-news' ); ?></h4>
		<select name="apple_news_pullquote_position">
			<option <?php selected( $pullquote_position, 'top' ); ?> value="top"><?php esc_html_e( 'top', 'apple-news' ); ?></option>
			<option <?php selected( $pullquote_position, 'middle' ); ?> value="middle"><?php esc_html_e( 'middle', 'apple-news' ); ?></option>
			<option <?php selected( $pullquote_position, 'bottom' ); ?> value="bottom"><?php esc_html_e( 'bottom', 'apple-news' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'The position in the article where the pull quote will appear.', 'apple-news' ); ?></p>
	</div>
	<div id="apple-news-metabox-coverart" class="apple-news-metabox-section apple-news-metabox-section-collapsable">
		<h3><?php esc_html_e( 'Cover art', 'apple-news' ); ?></h3>
		<?php if ( 'yes' === $this->settings->get( 'enable_cover_art' ) ) : ?>
			<?php include plugin_dir_path( __FILE__ ) . 'cover-art.php'; ?>
		<?php else : ?>
			<p>
			<?php
				printf(
					/* translators: First token is opening a tag, second is closing a tag */
					esc_html__( 'Cover Art must be enabled on the %1$ssettings page%2$s.', 'apple-news' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=apple-news-options' ) ) . '">',
					'</a>'
				);
			?>
			</p>
		<?php endif; ?>
	</div>
	<?php
	if ( 'yes' !== $this->settings->get( 'api_autosync' )
		&& current_user_can( apply_filters( 'apple_news_publish_capability', Apple_News::get_capability_for_post_type( 'publish_posts', $post->post_type ) ) )
		&& 'publish' === $post->post_status
		&& empty( $api_id )
		&& empty( $deleted )
		&& empty( $pending )
	) :
		?>
		<input type="hidden" id="apple-news-publish-action" name="apple_news_publish_action" value="">
		<input type="button" id="apple-news-publish-submit" name="apple_news_publish_submit" value="<?php esc_attr_e( 'Publish to Apple News', 'apple-news' ); ?>" class="button-primary" />
		<?php
	elseif ( 'yes' === $this->settings->get( 'api_autosync' )
		&& empty( $api_id )
		&& empty( $deleted )
		&& empty( $pending )
	) :
		?>
		<p><?php esc_html_e( 'This post will be automatically sent to Apple News on publish.', 'apple-news' ); ?></p>
	<?php elseif ( 'yes' === $this->settings->get( 'api_async' ) && ! empty( $pending ) ) : ?>
		<p><?php esc_html_e( 'This post is currently pending publishing to Apple News.', 'apple-news' ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $deleted ) ) : ?>
		<p><b><?php esc_html_e( 'This post has been deleted from Apple News', 'apple-news' ); ?></b></p>
	<?php endif; ?>

	<?php if ( ! empty( $api_id ) ) : ?>
		<?php
		// Add data about the article if it exists.
		$state       = \Admin_Apple_News::get_post_status( $post->ID );
		$share_url   = get_post_meta( $post->ID, 'apple_news_api_share_url', true );
		$created_at  = get_post_meta( $post->ID, 'apple_news_api_created_at', true );
		$created_at  = empty( $created_at ) ? __( 'None', 'apple-news' ) : get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $created_at ) ), 'F j, h:i a' );
		$modified_at = get_post_meta( $post->ID, 'apple_news_api_modified_at', true );
		$modified_at = empty( $modified_at ) ? __( 'None', 'apple-news' ) : get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $modified_at ) ), 'F j, h:i a' );
		?>
	<div id="apple-news-metabox-pullquote" class="apple-news-metabox-section apple-news-metabox-section-collapsable">
		<h3><?php esc_html_e( 'Apple News Publish Information', 'apple-news' ); ?></h3>
		<ul>
			<li><strong><?php esc_html_e( 'ID', 'apple-news' ); ?>:</strong> <?php echo esc_html( $api_id ); ?></li>
			<li><strong><?php esc_html_e( 'Created at', 'apple-news' ); ?>:</strong> <?php echo esc_html( $created_at ); ?></li>
			<li><strong><?php esc_html_e( 'Modified at', 'apple-news' ); ?>:</strong> <?php echo esc_html( $modified_at ); ?></li>
			<li><strong><?php esc_html_e( 'Share URL', 'apple-news' ); ?>:</strong> <a href="<?php echo esc_url( $share_url ); ?>" target="_blank"><?php echo esc_html( $share_url ); ?></a></li>
			<li><strong><?php esc_html_e( 'Revision', 'apple-news' ); ?>:</strong> <?php echo esc_html( get_post_meta( $post->ID, 'apple_news_api_revision', true ) ); ?></li>
			<li><strong><?php esc_html_e( 'State', 'apple-news' ); ?>:</strong> <?php echo esc_html( $state ); ?></li>
		</ul>
	</div>
	<?php endif; ?>
</div>
