<?php
/**
 * This class is in charge of handling the management of custom JSON.
 */
class Admin_Apple_JSON extends Apple_News {

	/**
	 * JSON management page name.
	 *
	 * @var string
	 * @access public
	 */
	public $json_page_name;

	/**
	 * Namespace for component classes.
	 *
	 * @var string
	 * @access public
	 */
	private $namespace = '\\Apple_Exporter\\Components\\';

	/**
	 * Valid actions handled by this class and their callback functions.
	 *
	 * @var array
	 * @access private
	 */
	private $valid_actions;

	/**
	 * Constructor.
	 */
	function __construct() {
		$this->json_page_name = $this->plugin_domain . '-json';

		$this->valid_actions = array(
			'apple_news_reset_json' => array(
				'callback' => array( $this, 'reset_json' ),
			),
			'apple_news_save_json' => array(
				'callback' => array( $this, 'save_json' ),
			),
		);

		add_action( 'admin_menu', array( $this, 'setup_json_page' ), 99 );
		add_action( 'admin_init', array( $this, 'action_router' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_filter( 'admin_title', array( $this, 'set_title' ), 10, 2 );
	}

	/**
	 * Route all possible theme actions to the right place.
	 *
	 * @access public
	 */
	public function action_router() {
		// Check for a valid action
		$action	= isset( $_POST['apple_news_action'] ) ? sanitize_text_field( $_POST['apple_news_action'] ) : null;
		if ( ( empty( $action ) || ! array_key_exists( $action, $this->valid_actions ) ) ) {
			return;
		}

		// Check the nonce
		check_admin_referer( 'apple_news_json' );

		// Call the callback for the action for further processing
		call_user_func( $this->valid_actions[ $action ]['callback'] );
	}

	/**
	 * Fix the title since WordPress doesn't set one.
	 *
	 * @param string $admin_title
	 * @param string $title
	 * @return string
	 * @access public
	 */
	public function set_title( $admin_title, $title ) {
		$screen = get_current_screen();
		if ( 'admin_page_' . $this->json_page_name === $screen->base ) {
			$admin_title = sprintf(
				__( 'Customize JSON %s', 'apple-news' ),
				trim( $admin_title )
			);
		}

		return $admin_title;
	}

	/**
	 * Options page setup.
	 *
	 * @access public
	 */
	public function setup_json_page() {

		// Don't add the submenu page if the settings aren't initialized.
		if ( ! self::is_initialized() ) {
			return;
		}

		add_submenu_page(
			'apple_news_index',
			__( 'Customize Apple News JSON', 'apple-news' ),
			__( 'Customize JSON', 'apple-news' ),
			apply_filters( 'apple_news_settings_capability', 'manage_options' ),
			$this->json_page_name,
			array( $this, 'page_json_render' )
		);
	}

	/**
	 * JSON page render.
	 *
	 * @access public
	 */
	public function page_json_render() {
		if ( ! current_user_can( apply_filters( 'apple_news_settings_capability', 'manage_options' ) ) ) {
			wp_die( esc_html__( 'You do not have permissions to access this page.', 'apple-news' ) );
		}

		// Get components for the dropdown.
		$components = $this->list_components();

		// Get theme info for reference purposes.
		$themes = new Admin_Apple_Themes();
		$theme_admin_url = $themes->theme_admin_url();
		$all_themes = \Apple_Exporter\Theme::get_registry();

		// Negotiate selected theme.
		$selected_theme = '';
		if ( ! empty( $_POST['apple_news_theme'] ) ) {
			$selected_theme = sanitize_text_field( $_POST['apple_news_theme'] );
		} elseif ( ! empty( $_GET['theme'] ) ) {
			$selected_theme = sanitize_text_field( $_GET['theme'] );
		}

		// Check if there is a valid selected component.
		$selected_component = ( ! empty( $selected_theme ) )
			? $this->get_selected_component()
			: '';

		// If we have a class, get its specs.
		$specs = ( ! empty( $selected_component ) )
			? $this->get_specs( $selected_component )
			: array();

		// Load the template.
		include plugin_dir_path( __FILE__ ) . 'partials/page_json.php';
	}

	/**
	 * Register assets for the options page.
	 *
	 * @param string $hook
	 * @access public
	 */
	public function register_assets( $hook ) {
		if ( 'apple-news_page_apple-news-json' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'apple-news-json-css',
			plugin_dir_url( __FILE__ ) . '../assets/css/json.css',
			array(),
			self::$version
		);

		wp_enqueue_script(
			'apple-news-json-js',
			plugin_dir_url( __FILE__ ) . '../assets/js/json.js',
			array( 'jquery' ),
			self::$version
		);

		wp_enqueue_script(
			'ace-js',
			'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/ace.js',
			array( 'jquery' ),
			'1.2.6'
		);
	}

	/**
	 * Resets the JSON snippets for the component.
	 *
	 * @access private
	 */
	private function reset_json() {

		// Ensure a theme was selected.
		if ( empty( $_POST['apple_news_theme'] ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to reset JSON since no theme was selected.', 'apple-news' )
			);

			return;
		}

		// Get the selected component.
		$component = $this->get_selected_component();
		if ( empty( $component ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to reset JSON since no component was provided', 'apple-news' )
			);

			return;
		}

		// Get the specs for the component.
		$specs = $this->get_specs( $component );
		if ( empty( $specs ) ) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'The component %s has no specs and cannot be reset', 'apple-news' ),
				$component
			) );

			return;
		}

		// Iterate over the specs and reset each one.
		foreach ( $specs as $spec ) {
			$spec->delete();
		}

		\Admin_Apple_Notice::success( sprintf(
			__( 'Reset the custom specs for %s.', 'apple-news' ),
			$component
		) );
	}

	/**
	 * Saves the JSON snippets for the component
	 *
	 * @access private
	 */
	private function save_json() {

		// Ensure a theme was selected.
		if ( empty( $_POST['apple_news_theme'] ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to save JSON since no theme was selected.', 'apple-news' )
			);

			return;
		}

		// Get the selected component.
		$component = $this->get_selected_component();
		if ( empty( $component ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to save JSON since no component was provided', 'apple-news' )
			);

			return;
		}

		// Get the specs for the component and theme.
		$theme = stripslashes( sanitize_text_field( $_POST['apple_news_theme'] ) );
		$specs = $this->get_specs( $component, $theme );
		if ( empty( $specs ) ) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'The component %s has no specs and cannot be saved', 'apple-news' ),
				$component
			) );

			return;
		}

		// Iterate over the specs and save each one.
		// Keep track of which ones were updated.
		$updates = array();
		foreach ( $specs as $spec ) {
			// Ensure the value exists
			$key = 'apple_news_json_' . $spec->key_from_name( $spec->name );
			if ( isset( $_POST[ $key ] ) ) {
				$custom_spec = stripslashes( sanitize_text_field( $_POST[ $key ] ) );
				$result = $spec->save( $custom_spec, $theme );
				if ( true === $result ) {
					$updates[] = $spec->label;
				}
			}
		}

		if ( empty( $updates ) ) {
			\Admin_Apple_Notice::info( sprintf(
				__( 'No spec updates were found for %s', 'apple-news' ),
				$component
			) );
		} else {
			\Admin_Apple_Notice::success( sprintf(
				__( 'Saved the following custom specs for %1$s: %2$s', 'apple-news' ),
				$component,
				implode( ', ', $updates )
			) );
		}
	}

	/**
	 * Loads the JSON specs that can be customized for the component
	 *
	 * @param string $component
	 * @return array
	 * @access private
	 */
	private function get_specs( $component ) {
		if ( empty( $component ) ) {
			return array();
		}

		$classname = $this->namespace . $component;
		$component_class = new $classname();
		return $component_class->get_specs();
	}

	/**
	 * Lists all components that can be customized
	 *
	 * @return array
	 * @access private
	 */
	private function list_components() {
		$component_factory = new \Apple_Exporter\Component_Factory();
		$component_factory->initialize();
		$components = $component_factory::get_components();

		// Make this alphabetized and pretty
		$components_sanitized = array();
		foreach ( $components as $component ) {
			$component_key = str_replace( $this->namespace, '', $component );
			$component_name = str_replace( '_', ' ', $component_key );
			$components_sanitized[ $component_key ] = $component_name;
		}
		ksort( $components_sanitized );
		return $components_sanitized;
	}

	/**
	 * Checks for a valid selected component
	 *
	 * @return string
	 * @access public
	 */
	public function get_selected_component() {
		$selected_component = '';

		if ( isset( $_POST['apple_news_component'] ) ) {
			$selected_component = sanitize_text_field( $_POST['apple_news_component'] );
			if ( ! array_key_exists( $selected_component, $this->list_components() ) ) {
				$selected_component = '';
			}
		}

		return $selected_component;
	}

	/**
	 * Returns the URL of the JSON admin page
	 *
	 * @return string
	 * @access public
	 */
	public function json_admin_url() {
		return add_query_arg( 'page', $this->json_page_name, admin_url( 'admin.php' ) );
	}
}
