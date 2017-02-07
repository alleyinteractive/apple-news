<div id="apple-news-publish">
	<?php wp_nonce_field( $publish_action, 'apple_news_nonce' ); ?>
	<div id="apple-news-metabox-sections" class="apple-news-metabox-section">
        <h3><?php esc_html_e( 'Sections', 'apple-news' ) ?></h3>
		<?php Admin_Apple_Meta_Boxes::build_sections_override( $post->ID ); ?>
		<div class="apple-news-sections">
			<?php Admin_Apple_Meta_Boxes::build_sections_field( $post->ID ); ?>
			<p class="description"><?php esc_html_e( 'Select the sections in which to publish this article. Uncheck them all for a standalone article.', 'apple-news' ); ?></p>
		</div>
	</div>
	<div id="apple-news-metabox-is-preview" class="apple-news-metabox-section">
        <h3><?php esc_html_e( 'Preview?', 'apple-news' ); ?></h3>
		<label for="apple-news-is-preview">
			<input id="apple-news-is-preview" name="apple_news_is_preview" type="checkbox" value="1" <?php checked( $is_preview ) ?>>
			<?php esc_html_e( 'Check this to publish the article as a draft.', 'apple-news' ); ?>
		</label>
	</div>
	<div id="apple-news-metabox-is-sponsored" class="apple-news-metabox-section">
        <h3><?php esc_html_e( 'Sponsored?', 'apple-news' ) ?></h3>
		<label for="apple-news-is-sponsored">
			<input id="apple-news-is-sponsored" name="apple_news_is_sponsored" type="checkbox" value="1" <?php checked( $is_sponsored ) ?>>
			<?php esc_html_e( 'Check this to indicate this article is sponsored content.', 'apple-news' ); ?>
		</label>
	</div>
	<div id="apple-news-metabox-pullquote" class="apple-news-metabox-section apple-news-metabox-section-collapsable">
        <h3><?php esc_html_e( 'Pull quote', 'apple-news' ) ?></h3>
		<label for="apple-news-pullquote" class="screen-reader-text"><?php esc_html_e( 'Pull quote', 'apple-news' ) ?></label>
		<textarea id="apple-news-pullquote" name="apple_news_pullquote" placeholder="<?php esc_attr_e( 'A pull quote is a key phrase, quotation, or excerpt that has been pulled from an article and used as a graphic element, serving to entice readers into the article or to highlight a key topic.', 'apple-news' ) ?>" rows="6" class="large-text"><?php echo esc_textarea( $pullquote ) ?></textarea>
		<p class="description"><?php esc_html_e( 'This is optional and can be left blank.', 'apple-news' ) ?></p>
		<h4><?php esc_html_e( 'Pull quote position', 'apple-news' ) ?></h4>
		<select name="apple_news_pullquote_position">
			<option <?php selected( $pullquote_position, 'top' ) ?> value="top"><?php esc_html_e( 'top', 'apple-news' ) ?></option>
			<option <?php selected( $pullquote_position, 'middle' ) ?> value="middle"><?php esc_html_e( 'middle', 'apple-news' ) ?></option>
			<option <?php selected( $pullquote_position, 'bottom' ) ?> value="bottom"><?php esc_html_e( 'bottom', 'apple-news' ) ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'The position in the article where the pull quote will appear.', 'apple-news' ) ?></p>
	</div>
    <div id="apple-news-metabox-coverart" class="apple-news-metabox-section apple-news-metabox-section-collapsable">
        <h3><?php esc_html_e( 'Cover art', 'apple-news' ) ?></h3>
        <p class="description">
            <?php printf(
                wp_kses(
                    __( 'You can set one or more <a href="%s">cover art</a> images below.', 'apple-news' ),
                    array( 'a' => array( 'href' => array() ) )
                ),
                esc_url( __( 'https://developer.apple.com/library/content/documentation/General/Conceptual/Apple_News_Format_Ref/CoverArt.html', 'apple-news' ) )
            ); ?></p>
        <div id="apple-news-metabox-coverart-horizontal" class="apple-news-metabox-coverart-image">
            <?php $horizontal_image_id = absint( get_post_meta( $post->ID, 'apple_news_coverart_horizontal', true ) ); ?>
            <h4>Horizontal Image</h4>
            <p class="description">
                <?php printf(
                    esc_html__( 'The image must be a minimum of %1$dx%2$d, but should be at least %3$dx%4$d for large screens.', 'apple-news' ),
                    Admin_Apple_News::$image_sizes['apple_news_ca_landscape_iphone']['width'],
	                Admin_Apple_News::$image_sizes['apple_news_ca_landscape_iphone']['height'],
	                Admin_Apple_News::$image_sizes['apple_news_ca_landscape_ipad']['width'],
	                Admin_Apple_News::$image_sizes['apple_news_ca_landscape_ipad']['height']
                ); ?>
            </p>
            <div class="apple-news-metabox-coverart-image">
                <?php if ( ! empty( $horizontal_image_id ) ) {
	                echo wp_get_attachment_image( $horizontal_image_id, 'medium' );
	                $add_hidden = 'hidden';
	                $remove_hidden = '';
                } else {
	                $add_hidden = '';
	                $remove_hidden = 'hidden';
                } ?>
            </div>
            <input name="apple_news_coverart_horizontal" class="apple-news-metabox-coverart-id" type="hidden" value="<?php echo $horizontal_image_id; ?>" />
            <input type="button" class="button-primary apple-news-metabox-coverart-add <?php echo $add_hidden; ?>" value="<?php echo esc_attr( __( 'Add image', 'apple-news' ) ); ?>" />
            <input type="button" class="button-primary apple-news-metabox-coverart-remove <?php echo $remove_hidden; ?>" value="<?php echo esc_attr( __( 'Remove image', 'apple-news' ) ); ?>" />
        </div>
        <div id="apple-news-metabox-coverart-vertical" class="apple-news-metabox-coverart-image">
	        <?php $vertical_image_id = absint( get_post_meta( $post->ID, 'apple_news_coverart_vertical', true ) ); ?>
            <h4>Vertical Image</h4>
            <p class="description">
		        <?php printf(
			        esc_html__( 'The image must be a minimum of %1$dx%2$d, but should be at least %3$dx%4$d for large screens.', 'apple-news' ),
			        Admin_Apple_News::$image_sizes['apple_news_ca_portrait_iphone']['width'],
			        Admin_Apple_News::$image_sizes['apple_news_ca_portrait_iphone']['height'],
			        Admin_Apple_News::$image_sizes['apple_news_ca_portrait_ipad']['width'],
			        Admin_Apple_News::$image_sizes['apple_news_ca_portrait_ipad']['height']
		        ); ?>
            </p>
            <div class="apple-news-metabox-coverart-image">
	            <?php if ( ! empty( $vertical_image_id ) ) {
		            echo wp_get_attachment_image( $vertical_image_id, 'medium' );
		            $add_hidden = 'hidden';
		            $remove_hidden = '';
	            } else {
		            $add_hidden = '';
		            $remove_hidden = 'hidden';
	            } ?>
            </div>
            <input name="apple_news_coverart_vertical" class="apple-news-metabox-coverart-id" type="hidden" value="<?php echo $vertical_image_id; ?>" />
            <input type="button" class="button-primary apple-news-metabox-coverart-add <?php echo $add_hidden; ?>" value="<?php echo esc_attr( __( 'Add image', 'apple-news' ) ); ?>" />
            <input type="button" class="button-primary apple-news-metabox-coverart-remove <?php echo $remove_hidden; ?>" value="<?php echo esc_attr( __( 'Remove image', 'apple-news' ) ); ?>" />
        </div>
        <div id="apple-news-metabox-coverart-square" class="apple-news-metabox-coverart-image">
	        <?php $square_image_id = absint( get_post_meta( $post->ID, 'apple_news_coverart_square', true ) ); ?>
            <h4>Square Image</h4>
            <p class="description">
		        <?php printf(
			        esc_html__( 'The image must be a minimum of %1$dx%2$d, but should be at least %3$dx%4$d for large screens.', 'apple-news' ),
			        Admin_Apple_News::$image_sizes['apple_news_ca_square_iphone']['width'],
			        Admin_Apple_News::$image_sizes['apple_news_ca_square_iphone']['height'],
			        Admin_Apple_News::$image_sizes['apple_news_ca_square_ipad']['width'],
			        Admin_Apple_News::$image_sizes['apple_news_ca_square_ipad']['height']
		        ); ?>
            </p>
            <div class="apple-news-metabox-coverart-image">
	            <?php if ( ! empty( $square_image_id ) ) {
		            echo wp_get_attachment_image( $square_image_id, 'medium' );
		            $add_hidden = 'hidden';
		            $remove_hidden = '';
	            } else {
		            $add_hidden = '';
		            $remove_hidden = 'hidden';
	            } ?>
            </div>
            <input name="apple_news_coverart_square" class="apple-news-metabox-coverart-id" type="hidden" value="<?php echo $square_image_id; ?>" />
            <input type="button" class="button-primary apple-news-metabox-coverart-add <?php echo $add_hidden; ?>" value="<?php echo esc_attr( __( 'Add image', 'apple-news' ) ); ?>" />
            <input type="button" class="button-primary apple-news-metabox-coverart-remove <?php echo $remove_hidden; ?>" value="<?php echo esc_attr( __( 'Remove image', 'apple-news' ) ); ?>" />
        </div>
    </div>
	<?php if ( 'yes' !== $this->settings->get( 'api_autosync' )
	     && current_user_can( apply_filters( 'apple_news_publish_capability', 'manage_options' ) )
	     && 'publish' === $post->post_status
	     && empty( $api_id )
	     && empty( $deleted )
	     && empty( $pending )
	) : ?>
		<input type="hidden" id="apple-news-publish-action" name="apple_news_publish_action" value="">
		<input type="button" id="apple-news-publish-submit" name="apple_news_publish_submit" value="<?php esc_attr_e( 'Publish to Apple News', 'apple-news' ) ?>" class="button-primary" />
	<?php elseif ( 'yes' === $this->settings->get( 'api_autosync' )
         && empty( $api_id )
         && empty( $deleted )
         && empty( $pending )
	) : ?>
		<p><?php esc_html_e( 'This post will be automatically sent to Apple News on publish.', 'apple-news' ); ?></p>
	<?php elseif ( 'yes' === $this->settings->get( 'api_async' ) && ! empty( $pending ) ) : ?>
		<p><?php esc_html_e( 'This post is currently pending publishing to Apple News.', 'apple-news' ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $deleted ) ) : ?>
		<p><b><?php esc_html_e( 'This post has been deleted from Apple News', 'apple-news' ) ?></b></p>
	<?php endif; ?>

	<?php if ( ! empty( $api_id ) ) : ?>
	<?php
	// Add data about the article if it exists.
	$state = \Admin_Apple_News::get_post_status( $post->ID );
	$share_url = get_post_meta( $post->ID, 'apple_news_api_share_url', true );
	$created_at = get_post_meta( $post->ID, 'apple_news_api_created_at', true );
	$created_at = empty( $created_at ) ? __( 'None', 'apple-news' ) : get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $created_at ) ), 'F j, h:i a' );
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
