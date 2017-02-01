<div class="wrap apple-news-settings">
	<h1><?php esc_html_e( 'Manage Settings', 'apple-news' ) ?></h1>
	<form method="post" action="" id="apple-news-settings-form">
		<?php wp_nonce_field( 'apple_news_options' ); ?>
		<input type="hidden" name="action" value="apple_news_options" />
		<?php foreach ( $sections as $section ): ?>
			<?php $section->before_section() ?>
			<?php
				if ( $section->is_hidden() ) {
					include plugin_dir_path( __FILE__ ) . 'page_options_section_hidden.php';
				} else {
					include plugin_dir_path( __FILE__ ) . 'page_options_section.php';
				}
				$section->after_section();
			?>
		<?php endforeach; ?>

		<?php submit_button(); ?>
	</form>
</div>
