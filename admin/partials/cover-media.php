<?php
/**
 * Publish to Apple News partials: Cover Media template
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
 *
 * @global WP_Post $post
 *
 * @package Apple_News
 */

$apple_cover_media_provider = get_post_meta( $post->ID, 'apple_news_cover_media_provider', true );
$apple_cover_image_id       = get_post_meta( $post->ID, 'apple_news_coverimage', true );
$apple_cover_image_caption  = get_post_meta( $post->ID, 'apple_news_coverimage_caption', true );
$apple_cover_video_id       = get_post_meta( $post->ID, 'apple_news_cover_video_id', true );

?>

<h4><?php esc_html_e( 'Source', 'apple-news' ); ?></h4>
<label>
	<input
		type="radio"
		name="apple_news_cover_media_provider"
		value="image"
		<?php checked( 'image', $apple_cover_media_provider ); ?>
	>
	<?php esc_html_e( 'Uploaded Image', 'apple-news' ); ?>
</label>
<label>
	<input
		type="radio"
		name="apple_news_cover_media_provider"
		value="video_id"
		<?php checked( 'video_id', $apple_cover_media_provider ); ?>
	>
	<?php esc_html_e( 'Uploaded MP4 Video', 'apple-news' ); ?>
</label>
<label>
	<input
		type="radio"
		name="apple_news_cover_media_provider"
		value="video_url"
		<?php checked( 'video_url', $apple_cover_media_provider ); ?>
	>
	<?php esc_html_e( 'External MP4 Video', 'apple-news' ); ?>
</label>
<label>
	<input
		type="radio"
		name="apple_news_cover_media_provider"
		value="embedwebvideo"
		<?php checked( 'embedwebvideo', $apple_cover_media_provider ); ?>
	>
	<?php esc_html_e( 'Embedded Video (YouTube, etc.)', 'apple-news' ); ?>
</label>

<div class="apple-news-coverimage-image-container apple-news-cover-media-provider-container" data-provider="image">
	<div class="apple-news-coverimage-image">
		<?php
		if ( ! empty( $apple_cover_image_id ) ) {
			echo wp_get_attachment_image( $apple_cover_image_id, 'medium' );
			$apple_add_image_hidden    = 'hidden';
			$apple_remove_image_hidden = '';
		} else {
			$apple_add_image_hidden    = '';
			$apple_remove_image_hidden = 'hidden';
		}
		?>
	</div>
	<input name="apple_news_coverimage"
		class="apple-news-coverimage-id"
		type="hidden"
		value="<?php echo esc_attr( $apple_cover_image_id ); ?>"
	/>
	<input type="button"
		class="button-primary apple-news-coverimage-add <?php echo esc_attr( $apple_add_image_hidden ); ?>"
		value="<?php esc_attr_e( 'Select image', 'apple-news' ); ?>"
	/>
	<input type="button"
		class="button-primary apple-news-coverimage-remove <?php echo esc_attr( $apple_remove_image_hidden ); ?>"
		value="<?php esc_attr_e( 'Remove image', 'apple-news' ); ?>"
	/>

	<div>
		<label for="apple-news-coverimage-caption"><?php esc_html_e( 'Cover Image Caption:', 'apple-news' ); ?></label>
		<textarea id="apple-news-coverimage-caption" name="apple_news_coverimage_caption"><?php echo esc_textarea( $apple_cover_image_caption ); ?></textarea>
	</div>
</div>

<div class="apple-news-cover-media-provider-container" data-provider="video_id">
	<div class="apple-news-cover-media-video">
		<?php
		if ( $apple_cover_video_id ) {
			printf( '<video controls src="%s"></video>', esc_url( wp_get_attachment_url( $apple_cover_video_id ) ) );
			$apple_add_video_hidden    = 'hidden';
			$apple_remove_video_hidden = '';
		} else {
			$apple_add_video_hidden    = '';
			$apple_remove_video_hidden = 'hidden';
		}
		?>
	</div>
	<input name="apple_news_cover_video_id"
		class="apple-news-cover-media-id"
		type="hidden"
		value="<?php echo esc_attr( $apple_cover_video_id ); ?>"
	/>
	<input type="button"
		class="button-primary apple-news-cover-video-id-add <?php echo esc_attr( $apple_add_video_hidden ); ?>"
		value="<?php esc_attr_e( 'Select video', 'apple-news' ); ?>"
	/>
	<input type="button"
		class="button-primary apple-news-cover-video-id-remove <?php echo esc_attr( $apple_remove_video_hidden ); ?>"
		value="<?php esc_attr_e( 'Remove video', 'apple-news' ); ?>"
	/>
</div>

<div class="apple-news-cover-media-provider-container" data-provider="video_url">
	<label for="apple-news-cover-media-video-url"><?php esc_html_e( 'Video URL', 'apple-news' ); ?></label>
	<input id="apple-news-cover-media-video-url"
		name="apple_news_cover_video_url"
		type="url"
		value="<?php echo esc_url( get_post_meta( $post->ID, 'apple_news_cover_video_url', true ) ); ?>"
	/>
	<p class="description"><?php esc_html_e( 'Enter an MP4 video URL.', 'apple-news' ); ?></p>
	<div class="notice error inline" style="display: none;">
		<p><?php esc_html_e( 'This URL is not supported. Only MP4 video URLs are supported.', 'apple-news' ); ?></p>
	</div>
</div>

<div class="apple-news-cover-media-provider-container" data-provider="embedwebvideo">
	<label for="apple-news-cover-media-embedwebvideo-url"><?php esc_html_e( 'Video URL', 'apple-news' ); ?></label>
	<input id="apple-news-cover-media-embedwebvideo-url"
		name="apple_news_cover_embedwebvideo_url"
		type="url"
		value="<?php echo esc_url( get_post_meta( $post->ID, 'apple_news_cover_embedwebvideo_url', true ) ); ?>"
	/>
	<p class="description"><?php esc_html_e( 'Enter a YouTube, Vimeo, or Dailymotion video URL.', 'apple-news' ); ?></p>
	<div class="notice error inline" style="display: none;">
		<p><?php esc_html_e( 'This URL is not supported. Only YouTube, Vimeo, and Dailymotion video URLs are supported.', 'apple-news' ); ?></p>
	</div>
</div>
