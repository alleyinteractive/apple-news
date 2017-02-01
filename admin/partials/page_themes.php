<?php $themes = new \Admin_Apple_Themes(); ?>
<div class="wrap apple-news-themes">
	<h1 id="apple_news_themes_title"><?php esc_html_e( 'Manage Themes', 'apple-news' ) ?></h1>

	<form method="post" action="" id="apple-news-themes-form" enctype="multipart/form-data">
		<?php wp_nonce_field( 'apple_news_themes' ); ?>
		<input type="hidden" id="apple_news_action" name="action" value="apple_news_set_theme" />
		<input type="hidden" id="apple_news_theme" name="apple_news_theme" value="" />

		<a class="button" href="<?php echo esc_url( $themes->theme_edit_url() ) ?>"><?php esc_html_e( 'Create New Theme', 'apple-news' ) ?></a>
		<?php submit_button(
			__( 'Import Theme', 'apple-news' ),
			'secondary',
			'apple_news_start_import',
			false
		); ?>

		<div class="apple-news-theme-form" id="apple_news_import_theme">
			<p>
				<b><?php esc_html_e( 'Choose a file to upload', 'apple-news' ) ?>:</b> <input type="file" id="apple_news_import_file" name="import" size="25" />
				<br /><?php esc_html_e( '(max size 1MB)', 'apple-news' ) ?>
			</p>
			<p><?php esc_html_e( 'This will upload a new theme with Apple News formatting settings. If a theme by the same name exists, it will be overwritten.', 'apple-news' ) ?></p>
			<?php submit_button(
				__( 'Upload', 'apple-news' ),
				'primary',
				'apple_news_upload_theme',
				false
			); ?>
			<?php submit_button(
				__( 'Cancel', 'apple-news' ),
				'secondary',
				'apple_news_cancel_upload_theme',
				false
			); ?>
			<input type="hidden" name="max_file_size" value="1000000" />
		</div>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<td id="radio" class="manage-column column-cb check-column"></td>
					<th scope="col" id="apple_news_theme_name" class="manage-column column-apple-news-theme-name column-primary"><?php esc_html_e( 'Name', 'apple-news' ) ?></th>
					<th scope="col" id="apple_news_theme_actions" class="manage-column column-apple-news-theme-actions"><?php esc_html_e( 'Actions', 'apple-news' ) ?></th>
				</tr>
			</thead>
			<tbody id="theme-list">
			<?php
				$all_themes = $themes->list_themes();
				$active_theme = $themes->get_active_theme();
				if ( empty( $all_themes ) ) :
				?>
					<tr>
						<td colspan="3"><?php esc_html_e( 'No themes were found', 'apple-news' ) ?></td>
					</tr>
				<?php else :
					foreach ( $all_themes as $theme ) :
						?>
						<tr id="theme-<?php echo sanitize_html_class( $theme ) ?>" class="iedit level-0 format-standard hentry">
							<th class="active column-apple-news-active" data-colname="Active">
								<input type="radio" id="apple_news_active_theme" name="apple_news_active_theme" value="<?php echo esc_attr( $theme ) ?>" <?php checked( $theme, $active_theme ) ?> />
							</th>
							<td class="name column-apple-news-theme-name column-primary" data-colname="Name">
								<?php echo esc_html( $theme ) ?>
								<button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'apple-news' ) ?></span></button>
							</td>
							<td class="column-apple-news-theme-actions" data-colname="Actions">
								<a href="#" class="apple-news-row-action apple-news-export-theme" data-theme="<?php echo esc_attr( $theme ) ?>"><?php esc_html_e( 'Export', 'apple-news' ) ?></a>
								<a href="<?php echo esc_url( $themes->theme_edit_url( $theme ) ) ?>" class="apple-news-row-action apple-news-edit-theme" data-theme="<?php echo esc_attr( $theme ) ?>"><?php esc_html_e( 'Edit', 'apple-news' ) ?></a>
								<?php if ( $theme !== $active_theme ) : ?>
									<a href="#" class="apple-news-row-action apple-news-delete-theme" data-theme="<?php echo esc_attr( $theme ) ?>"><?php esc_html_e( 'Delete', 'apple-news' ) ?></a>
								<?php endif; ?>
							</td>
						</tr>
						<?php
					endforeach;
				endif;
			?>
			</tbody>
		</table>

		<?php submit_button(
			__( 'Set Theme', 'apple-news' ),
			'primary',
			'apple_news_set_theme'
		); ?>
	</form>
</div>
