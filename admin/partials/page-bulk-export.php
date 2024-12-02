<?php
/**
 * Publish to Apple News partials: Bulk Export page template
 *
 * @package Apple_News
 */

// Expect these variables to be defined in Admin_Apple_Bulk_Export_Page::build_page() but make sure they're set.
$apple_page_title       ??= __( 'Bulk Export', 'apple-news' );
$apple_page_description ??= __( 'The following articles will be affected.', 'apple-news' );
$apple_posts            ??= [];
$apple_submit_text      ??= __( 'Go', 'apple-news' );

?>
<div class="wrap">
	<h1><?php echo esc_html( $apple_page_title ); ?></h1>
	<p><?php echo esc_html( $apple_page_description ); ?></p>
	<p><?php esc_html_e( "Once started, it might take a while. Please don't close the browser window.", 'apple-news' ); ?></p>
	<?php
	/**
	 * Allows for custom HTML to be printed before the bulk export table.
	 */
	do_action( 'apple_news_before_bulk_export_table' );
	?>
	<ul class="bulk-export-list" data-nonce="<?php echo esc_attr( wp_create_nonce( Admin_Apple_Bulk_Export_Page::ACTION ) ); ?>">
		<?php foreach ( $apple_posts as $apple_post ) : ?>
		<li class="bulk-export-list-item" data-post-id="<?php echo esc_attr( $apple_post->ID ); ?>">
			<span class="bulk-export-list-item-title">
				<?php echo esc_html( $apple_post->post_title ); ?>
			</span>
			<span class="bulk-export-list-item-status pending">
				<?php esc_html_e( 'Pending', 'apple-news' ); ?>
			</span>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php
	/**
	 * Allows for custom HTML to be printed after the bulk export table.
	 */
	do_action( 'apple_news_after_bulk_export_table' );
	?>

	<a class="button" href="<?php echo esc_url( menu_page_url( $this->plugin_slug . '_index', false ) ); ?>"><?php esc_html_e( 'Back', 'apple-news' ); ?></a>
	<a class="button button-primary bulk-export-submit" href="#"><?php echo esc_html( $apple_submit_text ); ?></a>
</div>
