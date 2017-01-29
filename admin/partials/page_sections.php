<div class="wrap apple-news-sections">
	<h1 id="apple_news_sections_title"><?php esc_html_e( 'Manage Sections', 'apple-news' ) ?></h1>
    <form method="post" action="" id="apple-news-themes-form" enctype="multipart/form-data">
	    <?php wp_nonce_field( 'apple_news_sections', 'apple_news_sections' ); ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th scope="col" id="apple_news_section_name" class="manage-column column-apple-news-section-name column-primary"><?php esc_html_e( 'Section', 'apple-news' ); ?></th>
                <th scope="col" id="apple_news_section_taxonomy_mapping" class="manage-column column-apple-news-section-taxonomy-mapping"><?php esc_html( $taxonomy->label ); ?></th>
            </tr>
            </thead>
            <tbody id="sections-list">
            <?php foreach ( $sections as $section_id => $section_name ): ?>
                <tr>
                    <td><?php echo esc_html( $section_name ); ?></td>
                    <td>
                        <?php // TODO: Wire up autocomplete fields here. ?>
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