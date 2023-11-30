<?php
/**
 * Partial for the meta component order field in theme options configuration.
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
 *
 * @global array $component_order
 * @global array $inactive_components
 *
 * @package Apple_News
 */

use Apple_Exporter\Theme;

?>
<div class="apple-news-sortable-list">
	<h4><?php esc_html_e( 'Active', 'apple-news' ); ?></h4>
	<ul id="meta-component-order-sort" class="component-order ui-sortable">
		<?php foreach ( $component_order as $apple_component_name ) : ?>
			<?php
			printf(
				'<li id="%s" class="ui-sortable-handle">%s</li>',
				esc_attr( $apple_component_name ),
				esc_html( Theme::get_meta_component_name( $apple_component_name ) )
			);
			?>
		<?php endforeach; ?>
	</ul>
</div>
<div class="apple-news-sortable-list">
	<h4><?php esc_html_e( 'Inactive', 'apple-news' ); ?></h4>
	<ul id="meta-component-inactive" class="component-order ui-sortable">
		<?php foreach ( $inactive_components as $apple_component_name ) : ?>
			<?php
			printf(
				'<li id="%s" class="ui-sortable-handle">%s</li>',
				esc_attr( $apple_component_name ),
				esc_html( Theme::get_meta_component_name( $apple_component_name ) )
			);
			?>
		<?php endforeach; ?>
	</ul>
</div>
<p class="description"><?php esc_html_e( 'Drag to set the order of the meta components at the top of the article. Drag elements into the "Inactive" column to prevent them from being included in your articles.', 'apple-news' ); ?></p>
