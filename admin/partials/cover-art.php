<?php
/**
 * Publish to Apple News partials: Cover Art template
 *
 * @package Apple_News
 */

$cover_art    = get_post_meta( $post->ID, 'apple_news_coverart', true );
$orientations = array(
	'landscape' => __( 'Landscape (4:3)', 'apple-news' ),
	'portrait'  => __( 'Portrait (3:4)', 'apple-news' ),
	'square'    => __( 'Square (1:1)', 'apple-news' ),
);
?>
<p class="description">
	<?php
	printf(
		// translators: first token is an opening <a> tag, second is </a>.
		esc_html__( '%1$sCover art%2$s will represent your article if editorially chosen for Featured Stories. Cover Art must include your channel logo with text at 24 pt minimum that is related to the headline. The image provided must match the dimensions listed. Limit submissions to 1-3 articles per day.', 'apple-news' ),
		'<a href="https://developer.apple.com/library/content/documentation/General/Conceptual/Apple_News_Format_Ref/CoverArt.html">',
		'</a>'
	);
	?>
</p>
<div>
	<label for="apple-news-coverart-orientation"><?php esc_html_e( 'Orientation:', 'apple-news' ); ?></label>
	<select id="apple-news-coverart-orientation" name="apple-news-coverart-orientation">
		<?php $orientation = ( ! empty( $cover_art['orientation'] ) ) ? $cover_art['orientation'] : 'landscape'; ?>
		<?php foreach ( $orientations as $key => $label ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $orientation, $key ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<p class="description"><?php esc_html_e( 'Note: You must provide the largest size (iPad Pro 12.9 in) in order for your submission to be considered.', 'apple-news' ); ?></p>
<?php $image_sizes = Admin_Apple_News::get_image_sizes(); ?>
<?php foreach ( $image_sizes as $key => $data ) : ?>
	<?php
	if ( 'coverArt' !== $data['type'] ) {
		continue;
	}
	?>
	<div class="apple-news-coverart-image-container apple-news-coverart-image-<?php echo esc_attr( $data['orientation'] ); ?>">
		<?php $image_id = ( ! empty( $cover_art[ $key ] ) ) ? absint( $cover_art[ $key ] ) : ''; ?>
		<h4><?php echo esc_html( $data['label'] ); ?></h4>
		<div class="apple-news-coverart-image">
			<?php
			if ( ! empty( $image_id ) ) {
				echo wp_get_attachment_image( $image_id, 'medium' );
				$add_hidden    = 'hidden';
				$remove_hidden = '';
			} else {
				$add_hidden    = '';
				$remove_hidden = 'hidden';
			}
			?>
		</div>
		<input name="<?php echo esc_attr( $key ); ?>"
			class="apple-news-coverart-id"
			type="hidden"
			value="<?php echo esc_attr( $image_id ); ?>"
			data-height="<?php echo esc_attr( $data['height'] ); ?>"
			data-width="<?php echo esc_attr( $data['width'] ); ?>"
		/>
		<input type="button"
			class="button-primary apple-news-coverart-add <?php echo esc_attr( $add_hidden ); ?>"
			value="<?php esc_attr_e( 'Add image', 'apple-news' ); ?>"
		/>
		<input type="button"
			class="button-primary apple-news-coverart-remove <?php echo esc_attr( $remove_hidden ); ?>"
			value="<?php esc_attr_e( 'Remove image', 'apple-news' ); ?>"
		/>
	</div>
<?php endforeach; ?>
