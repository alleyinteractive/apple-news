<?php
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
	 * @param Exporter_Content $content
	 * @param Workspace $workspace
	 * @param Settings $settings
	 */
	function __construct( $content, $workspace = null, $settings = null ) {
		$this->content   = $content;
		$this->workspace = $workspace ?: new Workspace( $this->content_id() );
		$this->settings  = $settings  ?: new Settings();
		$this->builders  = array();
	}

	/**
	 * An ordered hash of builders. They will be executed in order when building
	 * the JSON array.
	 *
	 * @since 0.4.0
	 * @param array $builders
	 * @access public
	 */
	public function initialize_builders( $builders = null ) {
		if ( $builders ) {
			$this->builders = $builders;
		} else {
			$this->register_builder( 'layout'             , new Builders\Layout( $this->content, $this->settings ) );
			$this->register_builder( 'components'         , new Builders\Components( $this->content, $this->settings ) );
			$this->register_builder( 'componentTextStyles', new Builders\Component_Text_Styles( $this->content, $this->settings ) );
			$this->register_builder( 'componentLayouts'   , new Builders\Component_Layouts( $this->content, $this->settings ) );
			$this->register_builder( 'metadata'           , new Builders\Metadata( $this->content, $this->settings ) );
		}

		Component_Factory::initialize( $this->workspace, $this->settings, $this->get_builder( 'componentTextStyles' ), $this->get_builder( 'componentLayouts' ) );
	}

	/**
	 * Register a builder.
	 *
	 * @param string $name
	 * @param Builder $builder
	 * @access private
	 */
	private function register_builder( $name, $builder ) {
		$this->builders[ $name ] = $builder;
	}

	/**
	 * Get a builder.
	 *
	 * @param string $name
	 * @return Builder
	 * @access private
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
	 * @return Workspace
	 * @access public
	 */
	public function workspace() {
		return $this->workspace;
	}

	/**
	 * Based on the content this instance holds, create an Article Format bundle.
	 * and return the path.
	 * This function builds the article and cleans up after.
	 *
	 * @return string
	 * @access public
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
	 * @return string The generated JSON for article.json
	 * @access private
	 */
	private function generate_json() {
		// Base JSON
		$json = array(
			'version'    => '0.10',
			'identifier' => 'post-' . $this->content_id(),
			'language'   => 'en',
			'title'      => $this->content_title(),
		);

		// Builders
		$json['documentStyle'] = $this->build_article_style();
		foreach ( $this->builders as $name => $builder ) {
			$arr = $builder->to_array();
			if ( $arr ) {
				$json[ $name ] = $arr;
			}
		}

		$json = apply_filters( 'apple_news_generate_json', $json, $this->content_id() );

		return json_encode( $json );
	}

	/**
	 * Write the JSON output to the Workspace.
	 *
	 * @access private
	 */
	private function write_json( $content ) {
		$this->workspace->write_json( $content );
	}

	/**
	 * Get the JSON output from the workspace.
	 *
	 * @return string
	 * @access public
	 */
	public function get_json() {
		return $this->workspace->get_json();
	}

	/**
	 * Get the bundles from the workspace.
	 *
	 * @return array
	 * @access public
	 */
	public function get_bundles() {
		return $this->workspace->get_bundles();
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
	 * @return array
	 * @access private
	 */
	private function build_article_style() {
		return array(
			'backgroundColor' => '#FFFFFF',
		);
	}

	/**
	 * Get the content ID.
	 *
	 * @return int
	 * @access private
	 */
	private function content_id() {
		return $this->content->id();
	}

	/**
	 * Get the content title.
	 *
	 * @return string
	 * @access private
	 */
	private function content_title() {
		return $this->content->title() ?: 'Untitled Article';
	}

}
