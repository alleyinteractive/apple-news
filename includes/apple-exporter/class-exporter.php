<?php
/**
 * Publish to Apple News: \Apple_Exporter\Exporter class
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 */

namespace Apple_Exporter;

/**
 * Export a Exporter_Content instance to Apple format.
 *
 * NOTE: This class is designed to work outside of WordPress just fine, so it
 * can be a dependency. It can be used to create other plugins, for example, a
 * Joomla or Drupal plugin. Even though this is not a WordPress class it
 * follows its coding conventions.
 *
 * @author  Federico Ramirez
 * @since   0.2.0
 */
class Exporter {

	/**
	 * The content object to be exported.
	 *
	 * @var Exporter_Content
	 * @access private
	 * @since 0.2.0
	 */
	private $content;

	/**
	 * The workspace object, used to create the bundle.
	 *
	 * @var Workspace
	 * @access private
	 * @since 0.2.0
	 */
	private $workspace;

	/**
	 * The settings object which is used to configure the output of the exporter.
	 *
	 * @var Settings
	 * @access private
	 * @since 0.4.0
	 */
	private $settings;

	/**
	 * An ordered hash of builders. They will be executed in order when building
	 * the JSON array.
	 *
	 * @var array
	 * @access private
	 * @since 0.4.0
	 */
	private $builders;

	/**
	 * Constructor.
	 *
	 * @param Exporter_Content $content   The content to export.
	 * @param Workspace        $workspace Optional. The workspace to use.
	 * @param Settings         $settings  Optional. Settings to use.
	 * @access public
	 */
	public function __construct( $content, $workspace = null, $settings = null ) {
		$this->content   = $content;
		$this->workspace = $workspace ?: new Workspace( $this->content_id() );
		$this->settings  = $settings ?: new Settings();
		$this->builders  = array();
	}

	/**
	 * An ordered hash of builders. They will be executed in order when building
	 * the JSON array.
	 *
	 * @since 0.4.0
	 * @param array $builders Optional. Builders to use.
	 * @access public
	 */
	public function initialize_builders( $builders = null ) {
		if ( $builders ) {
			$this->builders = $builders;
		} else {
			$this->register_builder( 'layout', new Builders\Layout( $this->content, $this->settings ) );
			$this->register_builder( 'components', new Builders\Components( $this->content, $this->settings ) );
			$this->register_builder( 'componentTextStyles', new Builders\Component_Text_Styles( $this->content, $this->settings ) );
			$this->register_builder( 'componentStyles', new Builders\Component_Styles( $this->content, $this->settings ) );
			$this->register_builder( 'textStyles', new Builders\Text_Styles( $this->content, $this->settings ) );
			$this->register_builder( 'componentLayouts', new Builders\Component_Layouts( $this->content, $this->settings ) );
			$this->register_builder( 'metadata', new Builders\Metadata( $this->content, $this->settings ) );
			$this->register_builder( 'advertisingSettings', new Builders\Advertising_Settings( $this->content, $this->settings ) );
		}

		Component_Factory::initialize(
			$this->workspace,
			$this->settings,
			$this->get_builder( 'componentTextStyles' ),
			$this->get_builder( 'componentLayouts' ),
			$this->get_builder( 'componentStyles' )
		);
	}

	/**
	 * Register a builder.
	 *
	 * @param string                           $name The name of the builder to register.
	 * @param \Apple_Exporter\Builders\Builder $builder The builder to register.
	 * @access private
	 */
	private function register_builder( $name, $builder ) {
		$this->builders[ $name ] = $builder;
	}

	/**
	 * Get a builder.
	 *
	 * @param string $name The name of the builder to fetch.
	 * @access private
	 * @return \Apple_Exporter\Builders\Builder The builder matching the provided name.
	 */
	private function get_builder( $name ) {
		return $this->builders[ $name ];
	}

	/**
	 * Generates JSON for the article.json file. By doing this, all attachments
	 * get added to the workspace/tmp directory automatically.
	 *
	 * When called, `clean_workspace` must always be called before and
	 * afterwards.
	 *
	 * @since 0.4.0
	 * @access public
	 */
	public function generate() {
		if ( ! $this->builders ) {
			$this->initialize_builders();
		}

		$this->write_json( $this->generate_json() );
	}

	/**
	 * Gets the instance of the current workspace.
	 *
	 * @since 0.4.0
	 * @access public
	 * @return Workspace The current workspace.
	 */
	public function workspace() {
		return $this->workspace;
	}

	/**
	 * Based on the content this instance holds, create an Article Format bundle.
	 * and return the path.
	 * This function builds the article and cleans up after.
	 *
	 * @access public
	 * @return string The exported content in JSON format.
	 */
	public function export() {
		// If an export or push was cancelled, the workspace might be polluted.
		// Clean beforehand.
		$this->clean_workspace();

		// Build the bundle content.
		$this->generate();

		// Some use cases for this function expect it to return the JSON.
		$json = $this->get_json();

		// Clean after the export action.
		$this->clean_workspace();

		return $json;
	}

	/**
	 * Generate article.json contents. It does so by looping though all data,
	 * generating valid JSON and adding attachments to workspace/tmp directory.
	 *
	 * @access private
	 * @return string The generated JSON for article.json
	 */
	private function generate_json() {
		/**
		 * Allows the exporter language to be filtered.
		 *
		 * @since 1.4.0
		 *
		 * @param string $language The language value to be filtered.
		 * @param int    $post_id  The ID of the post being exported.
		 */
		$language = apply_filters(
			'apple_news_exporter_language',
			get_bloginfo( 'language' ),
			$this->content_id()
		);

		// Base JSON.
		$json = array(
			'version'    => '1.7',
			'identifier' => 'post-' . $this->content_id(),
			'language'   => $language,
			'title'      => wp_strip_all_tags( $this->content_title() ),
		);

		// Builders.
		$json['documentStyle'] = $this->build_article_style();
		foreach ( $this->builders as $name => $builder ) {
			$arr = $builder->to_array();
			if ( $arr ) {
				$json[ $name ] = $arr;
			}
		}

		$json = apply_filters( 'apple_news_generate_json', $json, $this->content_id() );

		// Clean up the data array and convert to JSON format.
		self::prepare_for_encoding( $json );
		$json = wp_json_encode( $json );

		return $json;
	}

	/**
	 * Write the JSON output to the Workspace.
	 *
	 * @param string $content The content to be written to the workspace.
	 * @access private
	 */
	private function write_json( $content ) {
		$this->workspace->write_json( $content );
	}

	/**
	 * Get the JSON output from the workspace.
	 *
	 * @access public
	 * @return string The JSON from the workspace.
	 */
	public function get_json() {
		return $this->workspace->get_json();
	}

	/**
	 * Get the bundles from the workspace.
	 *
	 * @access public
	 * @return array An array of bundles from the workspace.
	 */
	public function get_bundles() {
		return $this->workspace->get_bundles();
	}

	/**
	 * Get the errors from the workspace.
	 *
	 * @access public
	 * @return array An array of errors from the workspace.
	 */
	public function get_errors() {
		return $this->workspace->get_errors();
	}

	/**
	 * Clean up the current workspace.
	 *
	 * @access private
	 */
	private function clean_workspace() {
		$this->workspace->clean_up();
	}

	/**
	 * Build the article style.
	 *
	 * @access private
	 * @return array An array containing the background color for the article.
	 */
	private function build_article_style() {

		// Get information about the currently used theme.
		$theme = \Apple_Exporter\Theme::get_used();

		return array(
			'backgroundColor' => $theme->get_value( 'body_background_color' ),
		);
	}

	/**
	 * Get the Exporter_Content object
	 *
	 * @access public
	 * @return Exporter_Content The content from the exporter.
	 */
	public function get_content() {
		return $this->content;
	}

	/**
	 * Get the content ID.
	 *
	 * @access private
	 * @return int The ID of the post being exported.
	 */
	private function content_id() {
		return $this->content->id();
	}

	/**
	 * Get the content title.
	 *
	 * @access private
	 * @return string The title of the content being exported.
	 */
	private function content_title() {
		return $this->content->title() ?: 'Untitled Article';
	}

	/**
	 * Cleans up data destined for JSON conversion.
	 *
	 * @param mixed $data The data to clean up.
	 *
	 * @access private
	 */
	public static function prepare_for_encoding( &$data ) {

		// If the value is an array, loop through it and process each element.
		if ( is_array( $data ) ) {
			foreach ( $data as &$datum ) {
				self::prepare_for_encoding( $datum );
			}
		}

		// If the value is a string, clean it up.
		if ( is_string( $data ) ) {
			// Strip hidden white space.
			$data = preg_replace( '/\h/u', ' ', $data );

			return;
		}
	}
}
