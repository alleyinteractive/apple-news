<?php
/**
 * Publish to Apple News partials: Index page template
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
 *
 * @global Admin_Apple_News_List_Table $table
 *
 * @package Apple_News
 */

$apple_current_screen = get_current_screen(); ?>
<div class="wrap">
	<h1><?php esc_html_e( 'Apple News', 'apple-news' ); ?></h1>
	<?php if ( ! \Apple_News::is_initialized() ) : ?>
		<div id="apple-news-publish">
			<?php
			printf(
				/* translators: First token is opening a tag, second is closing a tag */
				esc_html__( 'You must fill in the fields API Channel, API Key and API Secret on the section "Apple News Key for Lower Environments" on %1$s"Network > Settings > Network Settings"%2$s page before using Publish to Apple News.', 'apple-news' ),
				'<a href="' . esc_url( network_admin_url( 'settings.php?page=network_models' ) ) . '">',
				'</a>'
			);
			?>
		</div>
	<?php else : ?>
		<form method="get">
			<?php
			/**
			 * Allows for custom HTML to be printed before the article list table.
			 *
			 * This is called the "index table" because it is the table that is
			 * printed on the main (or "index") page of the plugin, which is accessed
			 * by clicking on Apple News in the WordPress sidebar.
			 */
			do_action( 'apple_news_before_index_table' );
			?>
			<?php if ( ! empty( $apple_current_screen->parent_base ) ) : ?>
			<input type="hidden" name="page" value="<?php echo esc_attr( $apple_current_screen->parent_base ); ?>">
			<?php endif; ?>
			<?php
				$table->search_box( __( 'Search', 'apple-news' ), 'apple-news-search' );
				$table->display();
			?>
			<?php
			/**
			 * Allows for custom HTML to be printed after the article list table.
			 *
			 * This is called the "index table" because it is the table that is
			 * printed on the main (or "index") page of the plugin, which is accessed
			 * by clicking on Apple News in the WordPress sidebar.
			 */
			do_action( 'apple_news_after_index_table' );
			?>
		</form>
	<?php endif; ?>
</div>
