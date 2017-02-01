<div class="wrap apple-news-theme-edit">
	<form method="post" action="<?php echo esc_url( $theme_admin_url ) ?>" id="apple-news-theme-edit-form">
		<?php wp_nonce_field( 'apple_news_save_edit_theme' ); ?>
		<input type="hidden" name="action" value="apple_news_save_edit_theme" />
		<p>
			<label id="apple_news_theme_name_label" for="apple_news_theme_name"><?php esc_html_e( 'Theme Name', 'apple-news' ) ?></label>
			<br />
			<input type="text" id="apple_news_theme_name" name="apple_news_theme_name" value="<?php echo esc_attr( $theme_name ) ?>" />
			<input type="hidden" id="apple_news_theme_name_previous" name="apple_news_theme_name_previous" value="<?php echo esc_attr( $theme_name ) ?>" />
		</p>
		<?php
			// Get formatting settings
			$section->before_section();
			include plugin_dir_path( __FILE__ ) . 'page_options_section.php';
			$section->after_section();
		?>
		<p class="apple-news-theme-edit-buttons">
			<?php
				submit_button(
					__( 'Save Theme Settings', 'apple-news' ),
					'primary',
					'submit',
					false
				);
			?>
			<a class="button" href="<?php echo esc_url( $theme_admin_url ) ?>"><?php esc_html_e( 'Cancel', 'apple-news' ) ?></a>
		</p>
	</form>
</div>
