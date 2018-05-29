<?php
/**
 * Publish to Apple News partials: Bulk Export page template
 *
 * @package Apple_News
 */

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Bulk Export Articles', 'apple-news' ); ?></h1>
	<p><?php esc_html_e( "The following articles will be published to Apple News. Once started, it might take a while, please don't close the browser window.", 'apple-news' ); ?></p>
	<?php do_action( 'apple_news_before_bulk_export_table' ); ?>
	<ul class="bulk-export-list" data-nonce="<?php echo esc_attr( wp_create_nonce( Admin_Apple_Bulk_Export_Page::ACTION ) ); ?>">
		<?php foreach ( $articles as $post ) : ?>
		<li class="bulk-export-list-item" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
			<span class="bulk-export-list-item-title">
				<?php echo esc_html( $post->post_title ); ?>
			</span>
			<span class="bulk-export-list-item-status pending">
				<?php esc_html_e( 'Pending', 'apple-news' ); ?>
			</span>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php do_action( 'apple_news_after_bulk_export_table' ); ?>

	<a class="button" href="<?php echo esc_url( menu_page_url( $this->plugin_slug . '_index', false ) ); ?>"><?php esc_html_e( 'Back', 'apple-news' ); ?></a>
	<a class="button button-primary bulk-export-submit" href="#"><?php esc_html_e( 'Publish All', 'apple-news' ); ?></a>
</div>
