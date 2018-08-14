<?php
/**
 * Publish to Apple News partials: Themes page template
 *
 * @package Apple_News
 */

$themes = new \Admin_Apple_Themes(); ?>
<div class="wrap apple-news-themes">
	<h1 id="apple_news_themes_title"><?php esc_html_e( 'Manage Themes', 'apple-news' ); ?></h1>

	<p><?php esc_html_e( 'As of version 1.3.0, a number of example themes are available. These example themes come bundled with a fresh installation of the plugin. To add them to an existing installation, or to restore them if they have been deleted, use the Load Example Themes button below.', 'apple-news' ); ?></p>

	<form method="post" action="" id="apple-news-themes-form" enctype="multipart/form-data">
		<?php wp_nonce_field( 'apple_news_themes' ); ?>
		<input type="hidden" id="apple_news_action" name="action" value="apple_news_set_theme" />
		<input type="hidden" id="apple_news_theme" name="apple_news_theme" value="" />

		<a class="button" href="<?php echo esc_url( $themes->theme_edit_url() ); ?>"><?php esc_html_e( 'Create New Theme', 'apple-news' ); ?></a>
		<?php
		submit_button(
			__( 'Import Theme', 'apple-news' ),
			'secondary',
			'apple_news_start_import',
			false
		);
		?>
		<?php
		submit_button(
			__( 'Load Example Themes', 'apple-news' ),
			'secondary',
			'apple_news_load_example_themes',
			false
		);
		?>

		<div class="apple-news-theme-form" id="apple_news_import_theme">
			<p>
				<b><?php esc_html_e( 'Choose a file to upload', 'apple-news' ); ?>:</b> <input type="file" id="apple_news_import_file" name="import" size="25" />
				<br /><?php esc_html_e( '(max size 1MB)', 'apple-news' ); ?>
			</p>
			<p><?php esc_html_e( 'This will upload a new theme with Apple News formatting settings. If a theme by the same name exists, it will be overwritten.', 'apple-news' ); ?></p>
			<?php
			submit_button(
				__( 'Upload', 'apple-news' ),
				'primary',
				'apple_news_upload_theme',
				false
			);
			?>
			<?php
			submit_button(
				__( 'Cancel', 'apple-news' ),
				'secondary',
				'apple_news_cancel_upload_theme',
				false
			);
			?>
			<input type="hidden" name="max_file_size" value="1000000" />
		</div>

		<?php // Modeled after wp-admin/themes.php. ?>

		<div class="theme-browser">
			<div class="themes wp-clearfix">
				<?php
				$all_themes   = \Apple_Exporter\Theme::get_registry();
				$active_theme = \Apple_Exporter\Theme::get_active_theme_name();
				if ( empty( $all_themes ) ) :
					?>
					<h2><?php esc_html_e( 'No themes were found', 'apple-news' ); ?></h2>
				<?php else : ?>
					<?php foreach ( $all_themes as $theme ) : ?>
						<?php
						$active       = ( $theme === $active_theme ) ? 'active' : '';
						$aria_name    = 'apple-news-theme-' . $theme . '-name';
						$theme_object = new \Apple_Exporter\Theme();
						$theme_object->set_name( $theme );
						$theme_object->load();
						$theme_screenshot = $theme_object->get_value( 'screenshot_url' );
						?>
						<div class="theme <?php echo sanitize_html_class( $active ); ?>" tabindex="0" aria-describedby="<?php echo esc_attr( $aria_name ); ?>">
							<?php if ( ! empty( $theme_screenshot ) ) : ?>
								<div class="theme-screenshot">
									<img src="<?php echo esc_url( $theme_screenshot ); ?>" alt="" />
								</div>
							<?php else : ?>
								<div class="theme-screenshot blank"></div>
							<?php endif; ?>

							<?php if ( ! empty( $active ) ) : ?>
								<h2 class="theme-name" id="<?php echo esc_attr( $aria_name ); ?>">
									<span><?php esc_html_e( 'Active', 'apple-news' ); ?>:</span>
									<?php echo esc_html( $theme ); ?>
								</h2>
							<?php else : ?>
								<h2 class="theme-name" id="<?php echo esc_attr( $aria_name ); ?>">
									<?php echo esc_html( $theme ); ?>
								</h2>
							<?php endif; ?>

							<div class="theme-actions">
								<input type="radio" name="apple_news_active_theme" value="<?php echo esc_attr( $theme ); ?>" <?php checked( $theme, $active_theme ); ?> />
								<?php if ( $theme !== $active_theme ) : ?>
									<a class="button button-primary apple-news-activate-theme" href="#"><?php esc_html_e( 'Activate', 'apple-news' ); ?></a>
								<?php endif; ?>
								<a class="button" href="<?php echo esc_url( $themes->theme_edit_url( $theme ) ); ?>" data-theme="<?php echo esc_attr( $theme ); ?>"><?php esc_html_e( 'Edit', 'apple-news' ); ?></a>
								<a class="button apple-news-export-theme" href="#" data-theme="<?php echo esc_attr( $theme ); ?>"><?php esc_html_e( 'Export', 'apple-news' ); ?></a>
								<?php if ( $theme !== $active_theme ) : ?>
									<a class="button button-danger apple-news-delete-theme" href="#" data-theme="<?php echo esc_attr( $theme ); ?>"><?php esc_html_e( 'Delete', 'apple-news' ); ?></a>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</form>
</div>
