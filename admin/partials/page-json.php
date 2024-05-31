<?php
/**
 * Publish to Apple News partials: JSON page template
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
 *
 * @global array  $all_themes
 * @global bool   $component_can_be_parent
 * @global array  $components
 * @global string $selected_component
 * @global string $selected_subcomponent
 * @global string $selected_theme
 * @global array  $specs
 * @global string $theme_admin_url
 *
 * @package Apple_News
 */

?>
<div class="wrap apple-news-json">
	<h1 id="apple_news_json_title"><?php esc_html_e( 'Customize Component JSON', 'apple-news' ); ?></h1>

	<form method="post" action="" id="apple-news-json-form">
		<input type="hidden" id="apple_news_action" name="apple_news_action" value="apple_news_save_json" />
		<?php wp_nonce_field( 'apple_news_json' ); ?>

		<?php if ( empty( $components ) ) : ?>
		<h2><?php esc_html_e( 'No components are available for customizing JSON', 'apple-news' ); ?></h2>
		<?php else : ?>
			<p>
			<?php
			echo wp_kses(
				sprintf(
					// translators: first token is an opening <a> tag, second is </a>.
					__( 'Select a component to customize any of the specs for its JSON snippets. This will enable to you create advanced templates beyond what is supported by %1$sthemes%2$s.', 'apple-news' ),
					'<a href="' . esc_url( $theme_admin_url ) . '">',
					'</a>'
				),
				[
					'a' => [
						'href' => [],
					],
				]
			)
			?>
			</p>
			<p><?php esc_html_e( 'Tokens that will be replaced by dynamic values based on theme or post settings are denoted as #token#. You may remove tokens to suit your custom JSON, or add tokens referencing theme settings or postmeta.', 'apple-news' ); ?></p>
			<p><?php esc_html_e( 'You can add postmeta values using the syntax #postmeta.meta_field_name# where meta_field_name is the name of the meta field that you want to include. Meta fields must be singular—the plugin does not support multiple values for the same key.', 'apple-news' ); ?></p>
			<p>
			<?php
				printf(
					/* translators: First token is an opening a tag, second is the closing a tag */
					esc_html__( 'For more information on how to configure custom JSON, including a list of supported settings tokens, please visit our %1$swiki page%2$s.', 'apple-news' ),
					'<a href="https://github.com/alleyinteractive/apple-news/wiki/customizing-json">',
					'</a>'
				)
			?>
			</p>
			<p>
			<?php
			echo wp_kses(
				sprintf(
					// translators: first argument is an opening <a> tag, second argument is </a>.
					__( 'For more information on the Apple News format options for each component, please read the %1$sApple News Format Reference%2$s.', 'apple-news' ),
					'<a href="https://developer.apple.com/documentation/apple_news/apple_news_format">',
					'</a>'
				),
				[
					'a' => [
						'href' => [],
					],
				]
			)
			?>
			</p>
			<div>
				<label for="apple_news_theme">
					<?php esc_html_e( 'Theme', 'apple-news' ); ?>:
					<select id="apple_news_theme" name="apple_news_theme">
						<option value="""><?php esc_html_e( 'Select a theme', 'apple-news' ); ?></option>
						<?php foreach ( $all_themes as $apple_theme_name ) : ?>
							<option value="<?php echo esc_attr( $apple_theme_name ); ?>"
								<?php selected( $apple_theme_name, $selected_theme ); ?>>
									<?php echo esc_html( $apple_theme_name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<?php if ( ! empty( $selected_theme ) ) : ?>
				<div>
					<label for="apple_news_component">
						<?php esc_html_e( 'Component', 'apple-news' ); ?>:
						<select id="apple_news_component" name="apple_news_component">
							<option value=""><?php esc_html_e( 'Select a component', 'apple-news' ); ?></option>
							<?php foreach ( $components as $apple_component_key => $apple_component_name ) : ?>
								<option value="<?php echo esc_attr( $apple_component_key ); ?>"
									<?php selected( $apple_component_key, $selected_component ); ?>>
										<?php echo esc_html( $apple_component_name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>
				</div>
			<?php endif; ?>

			<?php if ( $component_can_be_parent ) : ?>
			<div>
				<label for="apple_news_subcomponent">
					<?php esc_html_e( 'Subcomponent', 'apple-news' ); ?>:
					<select id="apple_news_subcomponent" name="apple_news_subcomponent">
						<option value=""><?php esc_html_e( 'None', 'apple-news' ); ?></option>
						<?php foreach ( $components as $apple_component_key => $apple_component_name ) : ?>
							<option value="<?php echo esc_attr( $apple_component_key ); ?>"
								<?php selected( $apple_component_key, $selected_subcomponent ); ?>>
								<?php echo esc_html( $apple_component_name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
		<?php endif; ?>

			<?php if ( ! empty( $specs ) ) : ?>
				<?php
				foreach ( $specs as $apple_spec ) :
					$apple_field_name   = 'apple_news_json_' . $apple_spec->key_from_name( $apple_spec->name );
					$apple_json_display = $apple_spec->format_json( $apple_spec->get_spec( $selected_theme ) );
					$apple_rows         = max( substr_count( $apple_json_display, "\n" ) + 1, 5 );
					$apple_editor_name  = 'editor_' . str_replace( '-', '_', $apple_field_name );
					$apple_editor_style = sprintf(
						'width: %spx; height: %spx',
						500,
						absint( 17 * $apple_rows )
					);

					/**
					 * Modifies the JSON editor theme.
					 *
					 * @param string $theme The ACE theme to use.
					 * @param string $component The component currently being edited.
					 * @param string $field_name The field currently being edited.
					 */
					$apple_ace_theme = apply_filters( 'apple_news_json_editor_ace_theme', 'ace/theme/textmate', $selected_component, $apple_field_name );
					?>
					<p>
						<label for="<?php echo esc_attr( $apple_field_name ); ?>"><?php echo esc_html( $apple_spec->label ); ?></label>
						<div id="<?php echo esc_attr( $apple_editor_name ); ?>" style="<?php echo esc_attr( $apple_editor_style ); ?>"></div>
						<textarea id="<?php echo esc_attr( $apple_field_name ); ?>" name="<?php echo esc_attr( $apple_field_name ); ?>"><?php echo esc_textarea( $apple_json_display ); ?></textarea>
						<script type="text/javascript">
							var <?php echo esc_js( $apple_editor_name ); ?> = ace.edit( '<?php echo esc_js( $apple_editor_name ); ?>' );
							jQuery( function() {
								jQuery( '#<?php echo esc_js( $apple_field_name ); ?>' ).hide();
								<?php echo esc_js( $apple_editor_name ); ?>.setTheme( '<?php echo esc_js( $apple_ace_theme ); ?>' );
								<?php echo esc_js( $apple_editor_name ); ?>.getSession().setMode( 'ace/mode/json' );
								<?php echo esc_js( $apple_editor_name ); ?>.getSession().setTabSize( 2 );
								<?php echo esc_js( $apple_editor_name ); ?>.getSession().setUseSoftTabs( false );
								<?php echo esc_js( $apple_editor_name ); ?>.setReadOnly( false );
								<?php echo esc_js( $apple_editor_name ); ?>.getSession().setUseWrapMode( true );
								<?php echo esc_js( $apple_editor_name ); ?>.getSession().setValue( jQuery( '#<?php echo esc_js( $apple_field_name ); ?>' ).val() );
								<?php echo esc_js( $apple_editor_name ); ?>.getSession().on( 'change', function() {
									jQuery( '#<?php echo esc_js( $apple_field_name ); ?>' ).val( <?php echo esc_js( $apple_editor_name ); ?>.getSession().getValue() );
								} );
							} );
						</script>
					</p>
				<?php endforeach; ?>
			<?php endif; ?>

		<?php endif; ?>

		<?php
		if ( ! empty( $selected_component ) ) {
			submit_button(
				__( 'Save JSON', 'apple-news' ),
				'primary',
				'apple_news_save_json',
				false
			);
			submit_button(
				__( 'Reset JSON', 'apple-news' ),
				'delete',
				'apple_news_reset_json',
				false
			);
		}
		?>
	</form>
</div>
