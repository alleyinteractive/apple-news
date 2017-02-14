<?php
$orientations = array(
    'landscape' => __( 'Landscape Image', 'apple-news' ),
    'portrait' => __( 'Portrait Image', 'apple-news' ),
    'square' => __( 'Square Image', 'apple-news' ),
);
?>
<p class="description">
	<?php printf(
		wp_kses(
			__( 'You can set one or more <a href="%s">cover art</a> images below. Only one image is required in order to enable cover art functionality. The image you provide will be cropped and/or resized to the minimum dimensions listed, and smaller versions will be created by Apple News as necessary.', 'apple-news' ),
			array( 'a' => array( 'href' => array() ) )
		),
		'https://developer.apple.com/library/content/documentation/General/Conceptual/Apple_News_Format_Ref/CoverArt.html'
	); ?>
</p>
<?php foreach ( $orientations as $key => $label ) : ?>
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
<?php endforeach; ?>
