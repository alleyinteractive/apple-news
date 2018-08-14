<?php
/**
 * Publish to Apple News Admin: Admin_Apple_Preview class
 *
 * Contains a class which is used to generate a theme preview.
 *
 * @package Apple_News
 */

/**
 * A class which is used to generate a theme preview.
 */
class Admin_Apple_Preview extends Apple_News {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Outputs the HTML used for preview.
	 *
	 * @param \Apple_Exporter\Theme $theme Optional. The theme to render.
	 *
	 * @access public
	 */
	public function get_preview_html( $theme = null ) {

		// Load a default theme, if the theme was not provided.
		if ( ! $theme instanceof \Apple_Exporter\Theme ) {
			$theme = new \Apple_Exporter\Theme();
		}

		// Merge plugin-level settings with theme-level settings.
		$admin_settings = new Admin_Apple_Settings();
		$settings       = $admin_settings->fetch_settings();

		// Determine if HTML support is enabled.
		$settings     = get_option( self::$option_name );
		$html_support = ( isset( $settings['html_support'] )
			&& 'yes' === $settings['html_support']
		);

		?>
		<div class="apple-news-preview">
			<?php
				// Build sample content.
				$title = sprintf(
					'<h1 class="apple-news-title apple-news-component apple-news-meta-component">%s</h1>',
					__( 'Sample Article', 'apple-news' )
				);

				$cover = sprintf(
					'<div class="apple-news-cover apple-news-meta-component">%s</div>',
					__( 'Cover', 'apple-news' )
				);

				// Build the byline.
				$author = __( 'John Doe', 'apple-news' );
				$date   = date( 'M j, Y g:i A' );
				$export = new Apple_Actions\Index\Export( $settings );
				$byline = sprintf(
					'<div class="apple-news-byline apple-news-component apple-news-meta-component">%s</div>',
					$export->format_byline( null, $author, $date )
				);

				// Get the order of the top components.
				$meta_component_order = $theme->get_value( 'meta_component_order' );
			if ( ! is_array( $meta_component_order ) ) {
				$meta_component_order = array();
			}
			foreach ( $meta_component_order as $component ) {
				if ( isset( $$component ) ) {
					echo wp_kses( $$component, Admin_Apple_Settings_Section::$allowed_html );
				}
			}
			?>
			<div class="apple-news-component">
			<p><span class="apple-news-dropcap">L</span>orem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sagittis, libero nulla pellentesque quam, non venenatis massa odio id dolor.</p>
			<p>Praesent eget odio vel sapien scelerisque euismod. Phasellus eros sapien, <a href="#">augue vitae iaculis euismod</a>, rutrum ac nibh nec, tristique commodo neque.</p>
			<?php
			printf(
				'<div class="apple-news-image">%s</div>',
				esc_html__( 'Image', 'apple-news' )
			);
			?>
			<?php
			printf(
				'<div class="apple-news-image-caption">%s</div>',
				esc_html__( 'Image caption', 'apple-news' )
			);
			?>
			<p>Maecenas tortor dui, pellentesque ac ullamcorper quis, malesuada sit amet turpis. Nunc in tellus et justo dapibus sollicitudin.</p>
			<h2>Quisque efficitur</h2>
			<p>Quisque efficitur sit amet ex et venenatis. Morbi nisi nisi, ornare id iaculis eget, pulvinar ac dolor.</p>
			<blockquote>Blockquote lorem ipsum dolor sit amet, efficitur sit amet aliquet id, aliquam placerat turpis.</blockquote>
			<p>In eu la	cus porttitor, pellentesque diam et, tristique elit. Mauris justo odio, efficitur sit amet aliquet id, aliquam placerat turpis.</p>
			<div class="apple-news-pull-quote">Pull quote lorem ipsum dolor sit amet.</div>
			<p>Sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque ipsum mi, sagittis eget sodales et, volutpat at felis.</p>
			<pre>
.code-sample {
	font-family: monospace;
	white-space: pre;
}
			</pre>
			<?php if ( $html_support ) : ?>
				<table>
					<thead>
					<tr>
						<th>Column 1</th>
						<th>Column 2</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>Cell 1</td>
						<td>Cell 2</td>
					</tr>
					<tr>
						<td>Cell 3</td>
						<td>Cell 4</td>
					</tr>
					</tbody>
				</table>
			<?php else : ?>
				<p><em>
					<?php esc_html_e( 'Support for tables requires HTML support to be turned on in Settings.', 'apple-news' ); ?>
				</em></p>
			<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Register assets for the options page.
	 *
	 * @param string $hook The hook that is firing in the current context.
	 *
	 * @access public
	 */
	public function register_assets( $hook ) {

		// Only fire on the theme edit page.
		if ( 'admin_page_apple-news-theme-edit' !== $hook ) {
			return;
		}

		// Add the theme preview stylesheet.
		wp_enqueue_style(
			'apple-news-preview-css',
			plugin_dir_url( __FILE__ ) . '../assets/css/preview.css',
			array(),
			self::$version
		);

		// Add the theme preview script.
		wp_enqueue_script(
			'apple-news-preview-js',
			plugin_dir_url( __FILE__ ) . '../assets/js/preview.js',
			array( 'jquery' ),
			self::$version,
			false
		);
	}
}
