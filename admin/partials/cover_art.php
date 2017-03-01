<?php
$cover_art = get_post_meta( $post->ID, 'apple_news_coverart', true );
$orientations = array(
    'landscape' => __( 'Landscape (4:3)', 'apple-news' ),
    'portrait' => __( 'Portrait (3:4)', 'apple-news' ),
    'square' => __( 'Square (1:1)', 'apple-news' ),
);
$orientation = ( ! empty( $cover_art['orientation'] ) ) ? $cover_art['orientation'] : 'landscape';
?>
<p class="description">
	<?php printf(
		wp_kses(
			__( '<a href="%s">Cover art</a> will represent your article if editorially chosen for Featured Stories. Cover Art must include your channel logo with text at 24 pt minimum that is related to the headline. The image provided must match the dimensions listed. Limit submissions to 1-3 articles per day.', 'apple-news' ),
			array( 'a' => array( 'href' => array() ) )
		),
		'https://developer.apple.com/library/content/documentation/General/Conceptual/Apple_News_Format_Ref/CoverArt.html'
	); ?>
</p>
<div>
	<label for="apple-news-coverart-orientation"><?php esc_html_e( 'Orientation:', 'apple-news' ); ?></label>
	<select id="apple-news-coverart-orientation" name="apple-news-coverart-orientation">
		<?php foreach ( $orientations as $key => $label ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $orientation, $key ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
<?php /*
    <div id="apple-news-coverart-<?php echo esc_attr( $key ); ?>" class="apple-news-coverart-image">
		<?php $image_id = absint( get_post_meta( $post->ID, 'apple_news_coverart_' . $key, true ) ); ?>
        <h4><?php echo esc_html( $label ); ?></h4>
        <p class="description">
			<?php printf(
				esc_html__( 'Minimum dimensions: %1$dx%2$d', 'apple-news' ),
				absint( Admin_Apple_News::$image_sizes[ 'apple_news_ca_' . $key ]['width'] ),
				absint( Admin_Apple_News::$image_sizes[ 'apple_news_ca_' . $key ]['height'] )
			); ?>
        </p>
        <div class="apple-news-coverart-image">
			<?php if ( ! empty( $image_id ) ) {
				echo wp_get_attachment_image( $image_id, 'medium' );
				$add_hidden = 'hidden';
				$remove_hidden = '';
			} else {
				$add_hidden = '';
				$remove_hidden = 'hidden';
			} ?>
        </div>
        <input name="apple_news_coverart_<?php echo esc_attr( $key ); ?>" class="apple-news-coverart-id" type="hidden" value="<?php echo esc_attr( $image_id ); ?>" />
        <input type="button" class="button-primary apple-news-coverart-add <?php echo esc_attr( $add_hidden ); ?>" value="<?php echo esc_attr( __( 'Add image', 'apple-news' ) ); ?>" />
        <input type="button" class="button-primary apple-news-coverart-remove <?php echo esc_attr( $remove_hidden ); ?>" value="<?php echo esc_attr( __( 'Remove image', 'apple-news' ) ); ?>" />
    </div>
*/ ?>
