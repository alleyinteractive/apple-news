<?php
/**
 * Partial for the meta component order field in theme options configuration.
 *
 * @package Apple_News
 */

?>
<div class="apple-news-sortable-list">
	<h4><?php esc_html_e( 'Active', 'apple-news' ); ?></h4>
	<ul id="meta-component-order-sort" class="component-order ui-sortable">
		<?php foreach ( $component_order as $component_name ) : ?>
			<?php
			echo sprintf(
				'<li id="%s" class="ui-sortable-handle">%s</li>',
				esc_attr( $component_name ),
				esc_html( ucwords( $component_name ) )
			);
			?>
		<?php endforeach; ?>
	</ul>
</div>
<div class="apple-news-sortable-list">
	<h4><?php esc_html_e( 'Inactive', 'apple-news' ); ?></h4>
	<ul id="meta-component-inactive" class="component-order ui-sortable">
		<?php foreach ( $inactive_components as $component_name ) : ?>
			<?php
			echo sprintf(
				'<li id="%s" class="ui-sortable-handle">%s</li>',
				esc_attr( $component_name ),
				esc_html( ucwords( $component_name ) )
			);
			?>
		<?php endforeach; ?>
	</ul>
</div>
<p class="description"><?php esc_html_e( 'Drag to set the order of the meta components at the top of the article. These include the title, the cover (i.e. featured image) and byline which also includes the date. Drag elements into the "Inactive" column to prevent them from being included in your articles.', 'apple-news' ); ?></p>
