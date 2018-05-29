<?php
/**
 * Publish to Apple News partials: Index page template
 *
 * @package Apple_News
 */

$current_screen = get_current_screen(); ?>
<div class="wrap">
	<h1><?php esc_html_e( 'Apple News', 'apple-news' ); ?></h1>
	<?php if ( ! \Apple_News::is_initialized() ) : ?>
		<div id="apple-news-publish">
			<?php
			printf(
				/* translators: First token is opening a tag, second is closing a tag */
				esc_html__( 'You must enter your API information on the %1$ssettings page%2$s before using Publish to Apple News.', 'apple-news' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=apple-news-options' ) ) . '">',
				'</a>'
			);
			?>
		</div>
	<?php else : ?>
		<form method="get">
			<?php do_action( 'apple_news_before_index_table' ); ?>
			<?php if ( ! empty( $current_screen->parent_base ) ) : ?>
			<input type="hidden" name="page" value="<?php echo esc_attr( $current_screen->parent_base ); ?>">
			<?php endif; ?>
			<?php
				$table->search_box( __( 'Search', 'apple-news' ), 'apple-news-search' );
				$table->display();
			?>
			<?php do_action( 'apple_news_after_index_table' ); ?>
		</form>
	<?php endif; ?>
</div>
