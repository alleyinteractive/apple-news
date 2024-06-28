<?php
/**
 * Publish to Apple News partials: Options page template
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
 *
 * @global array $sections
 *
 * @package Apple_News
 */

?>
<div class="wrap apple-news-settings">
	<h1><?php esc_html_e( 'Manage Settings', 'apple-news' ); ?></h1>
	<?php if ( Apple_News::is_initialized() ) : ?>
		<div class="notice notice-success">
			<p><?php esc_html_e( 'The Apple News channel config has been successfully added.', 'apple-news' ); ?></p>
		</div>
	<?php endif; ?>
	<form method="post" action="" id="apple-news-settings-form">
		<?php wp_nonce_field( 'apple_news_options' ); ?>
		<input type="hidden" name="action" value="apple_news_options" />
		<?php foreach ( $sections as $apple_section ) : ?>
			<?php $apple_section->before_section(); ?>
			<?php
			if ( $apple_section->is_hidden() ) {
				include __DIR__ . '/page-options-section-hidden.php';
			} else {
				include __DIR__ . '/page-options-section.php';
			}
				$apple_section->after_section();
			?>
		<?php endforeach; ?>

		<?php if ( is_plugin_active( 'brightcove-video-connect/brightcove-video-connect.php' ) ) : ?>
			<h3><?php esc_html_e( 'Brightcove Support', 'apple-news' ); ?></h3>
			<p>
				<?php
					esc_html_e(
						'Brightcove support was added in version 2.1.0 for users of the Brightcove Video Connect plugin. However, you will need to contact Apple Support to connect your Brightcove account to your Apple News channel for this feature to work properly.',
						'apple-news'
					);
				?>
			</p>
		<?php endif; ?>

		<?php submit_button(); ?>
	</form>
</div>
