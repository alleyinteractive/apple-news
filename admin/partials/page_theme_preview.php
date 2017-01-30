<div class="wrap apple-news-theme-preview">
	<?php if ( ! empty( $error ) ) : ?>
			<p class="error-message"><?php echo esc_html( $error ) ?></p>
		<?php else : ?>
			<h1 id="apple_news_themes_title"><?php esc_html_e( 'Previewing', 'apple-news' ) ?> <?php echo esc_html( $theme_name ) ?></h1>
			<a class="button" href="<?php echo esc_url( $theme_admin_url ) ?>"><?php esc_html_e( 'Back to all themes', 'apple-news' ) ?></a>
			<?php
				// Load the markup for theme preview from the formatting settings
				$preview = new Admin_Apple_Preview();
				$preview->get_preview_html( $theme_name, true );
			?>
	<?php endif; ?>
</div>
