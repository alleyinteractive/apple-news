<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Settings class
 *
 * Contains a class which is used to manage user-defined and computed settings.
 * Since version 1.2.2, formatting settings have been moved into themes.
 * A future plugin version may refactor this further, so use this class at your own risk.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.6.0
 */

use \Apple_Exporter\Settings;

/**
 * Describes a WordPress setting section
 *
 * @since 0.6.0
 */
class Admin_Apple_Settings_Section_Formatting extends Admin_Apple_Settings_Section {

	/**
	 * Slug of the formatting settings section.
	 *
	 * @var string
	 * @access protected
	 */
	protected $slug = 'formatting-options';

	/**
	 * Constructor.
	 *
	 * @param string $page
	 * @param boolean $hidden
	 * @param string $save_action
	 * @param string $section_option_name
	 */
	function __construct( $page, $hidden = false, $save_action = 'apple_news_options', $section_option_name = null ) {
		// Set the name
		$this->name =  __( 'Theme Settings', 'apple-news' );

		// Add the settings
		$this->settings = array(
		);

		// Add the groups
		$this->groups = array(
			'layout' => array(
				'label' => __( 'Layout Spacing', 'apple-news' ),
				'description' => __( 'The spacing for the base layout of the exported articles', 'apple-news' ),
				'settings' => array( 'layout_margin', 'layout_gutter' ),
			),
			'body' => array(
				'label' => __( 'Body', 'apple-news' ),
				'settings' => array(
					'body_font',
					'body_size',
					'body_line_height',
					'body_tracking',
					'body_color',
					'body_link_color',
					'body_background_color',
					'body_orientation'
				),
			),
			'dropcap' => array(
				'label' => __( 'Drop Cap', 'apple-news' ),
				'settings' => array(
					'initial_dropcap',
					'dropcap_background_color',
					'dropcap_color',
					'dropcap_font',
					'dropcap_number_of_characters',
					'dropcap_number_of_lines',
					'dropcap_number_of_raised_lines',
					'dropcap_padding',
				),
			),
			'byline' => array(
				'label' => __( 'Byline', 'apple-news' ),
				'description' => __( "The byline displays the article's author and publish date", 'apple-news' ),
				'settings' => array(
					'byline_font',
					'byline_size',
					'byline_line_height',
					'byline_tracking',
					'byline_color',
					'byline_format'
				),
			),
			'heading1' => array(
				'label' => __( 'Heading 1', 'apple-news' ),
				'settings' => array(
					'header1_font',
					'header1_color',
					'header1_size',
					'header1_line_height',
					'header1_tracking',
				),
			),
			'heading2' => array(
				'label' => __( 'Heading 2', 'apple-news' ),
				'settings' => array(
					'header2_font',
					'header2_color',
					'header2_size',
					'header2_line_height',
					'header2_tracking',
				),
			),
			'heading3' => array(
				'label' => __( 'Heading 3', 'apple-news' ),
				'settings' => array(
					'header3_font',
					'header3_color',
					'header3_size',
					'header3_line_height',
					'header3_tracking',
				),
			),
			'heading4' => array(
				'label' => __( 'Heading 4', 'apple-news' ),
				'settings' => array(
					'header4_font',
					'header4_color',
					'header4_size',
					'header4_line_height',
					'header4_tracking',
				),
			),
			'heading5' => array(
				'label' => __( 'Heading 5', 'apple-news' ),
				'settings' => array(
					'header5_font',
					'header5_color',
					'header5_size',
					'header5_line_height',
					'header5_tracking',
				),
			),
			'heading6' => array(
				'label' => __( 'Heading 6', 'apple-news' ),
				'settings' => array(
					'header6_font',
					'header6_color',
					'header6_size',
					'header6_line_height',
					'header6_tracking',
				),
			),
			'caption' => array(
				'label' => __( 'Image caption', 'apple-news' ),
				'settings' => array(
					'caption_font',
					'caption_size',
					'caption_line_height',
					'caption_tracking',
					'caption_color',
				),
			),
			'pullquote' => array(
				'label' => __( 'Pull quote', 'apple-news' ),
				'description' => sprintf(
					'%s <a href="https://en.wikipedia.org/wiki/Pull_quote">%s</a>.',
					__( 'Articles can have an optional', 'apple-news' ),
					__( 'Pull quote', 'apple-news' )
				),
				'settings' => array(
					'pullquote_font',
					'pullquote_size',
					'pullquote_line_height',
					'pullquote_tracking',
					'pullquote_color',
					'pullquote_hanging_punctuation',
					'pullquote_border_style',
					'pullquote_border_color',
					'pullquote_border_width',
					'pullquote_transform'
				),
			),
			'blockquote' => array(
				'label' => __( 'Blockquote', 'apple-news' ),
				'settings' => array(
					'blockquote_font',
					'blockquote_size',
					'blockquote_line_height',
					'blockquote_tracking',
					'blockquote_color',
					'blockquote_border_style',
					'blockquote_border_color',
					'blockquote_border_width',
					'blockquote_background_color',
				),
			),
			'monospaced' => array(
				'label' => __( 'Monospaced (<pre>, <code>, <samp>)', 'apple-news' ),
				'settings' => array(
					'monospaced_font',
					'monospaced_size',
					'monospaced_line_height',
					'monospaced_tracking',
					'monospaced_color',
				),
			),
			'gallery' => array(
				'label' => __( 'Gallery', 'apple-news' ),
				'description' => __( 'Can either be a standard gallery, or mosaic.', 'apple-news' ),
				'settings' => array( 'gallery_type' ),
			),
			'advertisement' => array(
				'label' => __( 'Advertisement', 'apple-news' ),
				'settings' => array(
					'enable_advertisement',
					'ad_frequency',
					'ad_margin'
				),
			),
			'component_order' => array(
				'label' => __( 'Component Order', 'apple-news' ),
				'settings' => array( 'meta_component_order' ),
			),
		);

		parent::__construct( $page, $hidden, $save_action, $section_option_name );
	}

	/**
	 * Gets section info.
	 *
	 * @return string
	 * @access public
	 */
	public function get_section_info() {
		return __( 'Configuration for the visual appearance of the theme. Updates to these settings will not change the appearance of any articles previously published to your channel in Apple News using this theme unless you republish them.', 'apple-news' );
	}

	/**
	 * HTML to display before the section.
	 *
	 * @return string
	 * @access public
	 */
	public function before_section() {
		if ( $this->hidden ) {
			return;
		}
		?>
		<div id="apple-news-formatting">
			<div class="apple-news-settings-left">
		<?php
	}

	/**
	 * HTML to display after the section.
	 *
	 * @return string
	 * @access public
	 */
	public function after_section() {
		if ( $this->hidden ) {
			return;
		}
		?>
			</div>
			<?php
				$preview = new Admin_Apple_Preview();
				$preview->get_preview_html();
			?>
		</div>
		<?php
	}

	/**
	 * Renders the component order field.
	 *
	 * @param string $type
	 *
	 * @access public
	 */
	public static function render_meta_component_order( $type ) {

		// Get the current order.
		$component_order = self::get_value( 'meta_component_order' );
		if ( empty( $component_order ) || ! is_array( $component_order ) ) {
			$component_order = array();
		}

		// Get inactive components.
		$default_settings = new Settings;
		$inactive_components = array_diff(
			$default_settings->meta_component_order,
			$component_order
		);

		// Use the correct output format.
		if ( 'hidden' === $type ) {
			foreach ( $component_order as $component_name ) {
				echo sprintf(
					'<input type="hidden" name="meta_component_order[]" value="%s">',
					esc_attr( $component_name )
				);
			}
			foreach ( $inactive_components as $component_name ) {
				echo sprintf(
					'<input type="hidden" name="meta_component_inactive[]" value="%s">',
					esc_attr( $component_name )
				);
			}
		} else {
			?>
			<div class="apple-news-sortable-list">
				<h4><?php esc_html_e( 'Active', 'apple-news' ); ?></h4>
				<ul id="meta-component-order-sort"
				    class="component-order ui-sortable">
					<?php foreach ( $component_order as $component_name ) : ?>
						<?php echo sprintf(
							'<li id="%s" class="ui-sortable-handle">%s</li>',
							esc_attr( $component_name ),
							esc_html( ucwords( $component_name ) )
						); ?>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="apple-news-sortable-list">
				<h4><?php esc_html_e( 'Inactive', 'apple-news' ); ?></h4>
				<ul id="meta-component-inactive" class="component-order ui-sortable">
					<?php foreach ( $inactive_components as $component_name ) : ?>
						<?php echo sprintf(
							'<li id="%s" class="ui-sortable-handle">%s</li>',
							esc_attr( $component_name ),
							esc_html( ucwords( $component_name ) )
						); ?>
					<?php endforeach; ?>
				</ul>
			</div>
			<p class="description"><?php esc_html_e( 'Drag to set the order of the meta components at the top of the article. These include the title, the cover (i.e. featured image) and byline which also includes the date. Drag elements into the "Inactive" column to prevent them from being included in your articles.', 'apple-news' ) ?></p>
			<?php
		}
	}
}
