<p class="description">
	<?php printf(
		wp_kses(
			__( 'You can set one or more <a href="%s">cover art</a> images below.', 'apple-news' ),
			array( 'a' => array( 'href' => array() ) )
		),
		esc_url( __( 'https://developer.apple.com/library/content/documentation/General/Conceptual/Apple_News_Format_Ref/CoverArt.html', 'apple-news' ) )
	); ?></p>
<div id="apple-news-coverart-landscape" class="apple-news-coverart-image">
	<?php $landscape_image_id = absint( get_post_meta( $post->ID, 'apple_news_coverart_landscape', true ) ); ?>
	<h4><?php esc_html_e( 'Landscape Image', 'apple-news' ); ?></h4>
	<p class="description">
		<?php printf(
			esc_html__( 'Minimum dimensions: %1$dx%2$d', 'apple-news' ),
			Admin_Apple_News::$image_sizes['apple_news_ca_landscape_ipad_pro']['width'],
			Admin_Apple_News::$image_sizes['apple_news_ca_landscape_ipad_pro']['height']
		); ?>
	</p>
	<div class="apple-news-coverart-image">
		<?php if ( ! empty( $landscape_image_id ) ) {
			echo wp_get_attachment_image( $landscape_image_id, 'medium' );
			$add_hidden = 'hidden';
			$remove_hidden = '';
		} else {
			$add_hidden = '';
			$remove_hidden = 'hidden';
		} ?>
	</div>
	<input name="apple_news_coverart_landscape" class="apple-news-coverart-id" type="hidden" value="<?php echo $landscape_image_id; ?>" />
	<input type="button" class="button-primary apple-news-coverart-add <?php echo $add_hidden; ?>" value="<?php echo esc_attr( __( 'Add image', 'apple-news' ) ); ?>" />
	<input type="button" class="button-primary apple-news-coverart-remove <?php echo $remove_hidden; ?>" value="<?php echo esc_attr( __( 'Remove image', 'apple-news' ) ); ?>" />
</div>
<div id="apple-news-coverart-portrait" class="apple-news-coverart-image">
	<?php $portrait_image_id = absint( get_post_meta( $post->ID, 'apple_news_coverart_portrait', true ) ); ?>
    <h4><?php esc_html_e( 'Portrait Image', 'apple-news' ); ?></h4>
	<p class="description">
		<?php printf(
			esc_html__( 'Minimum dimensions: %1$dx%2$d', 'apple-news' ),
			Admin_Apple_News::$image_sizes['apple_news_ca_portrait_ipad_pro']['width'],
			Admin_Apple_News::$image_sizes['apple_news_ca_portrait_ipad_pro']['height']
		); ?>
	</p>
	<div class="apple-news-coverart-image">
		<?php if ( ! empty( $portrait_image_id ) ) {
			echo wp_get_attachment_image( $portrait_image_id, 'medium' );
			$add_hidden = 'hidden';
			$remove_hidden = '';
		} else {
			$add_hidden = '';
			$remove_hidden = 'hidden';
		} ?>
	</div>
	<input name="apple_news_coverart_portrait" class="apple-news-coverart-id" type="hidden" value="<?php echo $portrait_image_id; ?>" />
	<input type="button" class="button-primary apple-news-coverart-add <?php echo $add_hidden; ?>" value="<?php echo esc_attr( __( 'Add image', 'apple-news' ) ); ?>" />
	<input type="button" class="button-primary apple-news-coverart-remove <?php echo $remove_hidden; ?>" value="<?php echo esc_attr( __( 'Remove image', 'apple-news' ) ); ?>" />
</div>
<div id="apple-news-coverart-square" class="apple-news-coverart-image">
	<?php $square_image_id = absint( get_post_meta( $post->ID, 'apple_news_coverart_square', true ) ); ?>
    <h4><?php esc_html_e( 'Square Image', 'apple-news' ); ?></h4>
	<p class="description">
		<?php printf(
			esc_html__( 'Minimum dimensions: %1$dx%2$d', 'apple-news' ),
			Admin_Apple_News::$image_sizes['apple_news_ca_square_ipad_pro']['width'],
			Admin_Apple_News::$image_sizes['apple_news_ca_square_ipad_pro']['height']
		); ?>
	</p>
	<div class="apple-news-coverart-image">
		<?php if ( ! empty( $square_image_id ) ) {
			echo wp_get_attachment_image( $square_image_id, 'medium' );
			$add_hidden = 'hidden';
			$remove_hidden = '';
		} else {
			$add_hidden = '';
			$remove_hidden = 'hidden';
		} ?>
	</div>
	<input name="apple_news_coverart_square" class="apple-news-coverart-id" type="hidden" value="<?php echo $square_image_id; ?>" />
	<input type="button" class="button-primary apple-news-coverart-add <?php echo $add_hidden; ?>" value="<?php echo esc_attr( __( 'Add image', 'apple-news' ) ); ?>" />
	<input type="button" class="button-primary apple-news-coverart-remove <?php echo $remove_hidden; ?>" value="<?php echo esc_attr( __( 'Remove image', 'apple-news' ) ); ?>" />
</div>
