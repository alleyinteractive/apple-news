<div class="wrap apple-news-sections">
	<h1 id="apple_news_sections_title"><?php esc_html_e( 'Manage Sections', 'apple-news' ) ?></h1>
    <form method="post" action="" id="apple-news-themes-form" enctype="multipart/form-data">
	    <?php wp_nonce_field( 'apple_news_sections', 'apple_news_sections' ); ?>
        <input name="action" type="hidden" value="apple_news_set_section_taxonomy_mappings" />
        <div id="apple-news-section-taxonomy-mapping-template">
            <label class="screen-reader-text"><?php echo esc_html( $taxonomy->labels->singular_name ); ?></label>
            <input type="text" class="apple-news-section-taxonomy-autocomplete" />
            <button type="button" class="apple-news-section-taxonomy-remove"><span class="apple-news-section-taxonomy-remove-icon" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Remove mapping', 'apple-news' ); ?></span></button>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th scope="col" id="apple_news_section_name" class="manage-column column-apple-news-section-name column-primary"><?php esc_html_e( 'Section', 'apple-news' ); ?></th>
                <th scope="col" id="apple_news_section_taxonomy_mapping" class="manage-column column-apple-news-section-taxonomy-mapping"><?php esc_html( $taxonomy->label ); ?></th>
            </tr>
            </thead>
            <tbody id="apple-news-sections-list">
            <?php foreach ( $sections as $section_id => $section_name ): ?>
                <tr id="apple-news-section-<?php echo esc_attr( $section_id ); ?>">
                    <td><?php echo esc_html( $section_name ); ?></td>
                    <td>
                        <ul class="apple-news-section-taxonomy-mapping-list">
	                        <?php // TODO: Wire up autocomplete fields here. ?>
                        </ul>
                        <button type="button" class="apple-news-add-section-taxonomy-mapping" data-section-id="<?php echo esc_attr( $section_id ); ?>"><?php esc_html_e( 'Add Mapping', 'apple-news' ); ?></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
	    <?php submit_button(
		    __( 'Save Changes', 'apple-news' ),
		    'primary',
		    'apple_news_set_section_taxonomy_mappings'
	    ); ?>
    </form>
</div>