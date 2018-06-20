<?php
/**
 * Publish to Apple News partials: Options Section page template
 *
 * @package Apple_News
 */

?>
<h3><?php echo esc_html( $section->name() ); ?></h3>
<?php echo wp_kses_post( $section->get_section_info() ); ?>
<table class="form-table apple-news">
	<?php foreach ( $section->groups() as $group ) : ?>
		<?php do_action( 'apple_news_before_setting_group', $group, false ); ?>
	<tr>
		<th scope="row"><?php echo esc_html( $group['label'] ); ?></th>
		<td>
			<fieldset>
				<?php foreach ( $group['settings'] as $setting_name => $setting_meta ) : ?>
					<?php do_action( 'apple_news_before_setting', $setting_name, $setting_meta ); ?>
				<label class="setting-container">
					<?php if ( ! empty( $setting_meta['label'] ) ) : ?>
						<span class="label-name"><?php echo esc_html( $setting_meta['label'] ); ?></span>
					<?php endif; ?>
					<?php
						echo wp_kses(
							$section->render_field(
								array(
									$setting_name,
									$setting_meta['default'],
									$setting_meta['callback'],
								)
							),
							Admin_Apple_Settings_Section::$allowed_html
						);
					?>
				</label>
					<?php do_action( 'apple_news_after_setting', $setting_name, $setting_meta ); ?>
				<br />
				<?php endforeach; ?>

				<?php if ( $group['description'] ) : ?>
					<p class="description"><?php echo '(' . wp_kses_post( $group['description'] ) . ')'; ?></p>
				<?php endif; ?>
			</fieldset>
		</td>
	</tr>
		<?php do_action( 'apple_news_after_setting_group', $group, false ); ?>
	<?php endforeach; ?>
</table>
