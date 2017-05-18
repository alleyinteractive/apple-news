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
