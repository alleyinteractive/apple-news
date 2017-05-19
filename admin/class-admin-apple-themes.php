<?php
/**
 * Publish to Apple News Admin: Admin_Apple_Themes class
 *
 * Contains a class which is used to manage themes.
 *
 * @package Apple_News
 */

/**
 * This class is in charge of handling the management of Apple News themes.
 */
class Admin_Apple_Themes extends Apple_News {

	/**
	 * Theme edit page name.
	 *
	 * @access public
	 * @var string
	 */
	public $theme_edit_page_name;

	/**
	 * Theme management page name.
	 *
	 * @access public
	 * @var string
	 */
	public $theme_page_name;

	/**
	 * Valid actions handled by this class and their callback functions.
	 *
	 * @var array
	 * @access private
	 */
	private $_valid_actions;

	/**
	 * Renders a theme option field for use in a form.
	 *
	 * @param \Apple_Exporter\Theme $theme The Theme object to use.
	 * @param string $option_name The option name to process.
	 *
	 * @access public
	 * @return string The HTML for the field.
	 */
	public static function render_field( $theme, $option_name ) {

		// Ensure we were given a valid theme.
		if ( ! $theme instanceof \Apple_Exporter\Theme ) {
			return '';
		}

		// Ensure the option exists.
		$options = $theme->get_options();
		if ( ! isset( $options[ $option_name ] ) ) {
			return '';
		}

		// Ensure the option is not hidden. Hidden options should not be used.
		$option = $options[ $option_name ];
		if ( ! empty( $option['hidden'] ) ) {
			return '';
		}

		// If the field has its own render callback, use that instead.
		if ( ! empty( $option['callback'] ) ) {
			return call_user_func( $option['callback'], $theme );
		}

		// Build the field, forking for option type.
		$field = '';
		$value = $theme->get_value( $option_name );
		switch ( $option['type'] ) {
			case 'color':
				$field = '<input type="text" id="%s" name="%s" value="%s" class="apple-news-color-picker">';

				break;
			case 'float':
				$field = '<input class="input-float" placeholder="' . esc_attr( $option['default'] ) . '" type="text" step="any" id="%s" name="%s" value="%s">';

				break;
			case 'font':

				// Build the options list.
				$fonts = \Apple_Exporter\Theme::get_fonts();
				foreach ( $fonts as $font_name ) {
					$field .= sprintf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $font_name ),
						selected( $font_name, $value, false ),
						esc_html( $font_name )
					);
				}

				// Wrap the options in the select.
				$field = '<select class="select2 font" id="%s" name="%s">' . $field
					. '</select>';

				break;
			case 'integer':
				$field = '<input type="number" id="%s" name="%s" value="%s">';

				break;
			case 'select':

				// Build the options list.
				foreach ( $option['type'] as $option_value ) {
					$field .= sprintf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $option_value ),
						selected( $value, $option_value, false ),
						esc_html( $option_value )
					);
				}

				// Wrap the options in the select.
				$field = '<select id="%s" name="%s">' . $field . '</select>';

				break;
			default:
				$field = '<input type="text" id="%s" name="%s" value="%s">';

				break;
		}

		// Add a description, if set.
		if ( ! empty( $option['description'] ) ) {
			$field .= apply_filters(
				'apple_news_field_description_output_html',
				'<br/><i>' . $option['description'] . '</i>',
				$option_name
			);
		}

		// Use a different template for selects.
		if ( 'select' === $option['type'] || 'font' === $option['type'] ) {
			return sprintf(
				$field,
				esc_attr( $option_name ),
				esc_attr( $option_name )
			);
		}

		return sprintf(
			$field,
			esc_attr( $option_name ),
			esc_attr( $option_name ),
			esc_attr( $value )
		);
	}

	/**
	 * Constructor. Sets page names dynamically and registers actions.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->theme_page_name = $this->plugin_domain . '-themes';
		$this->theme_edit_page_name = $this->plugin_domain . '-theme-edit';

		$this->_valid_actions = array(
			'apple_news_upload_theme' => array(
				'callback' => array( $this, 'upload_theme' ),
				'nonce' => 'apple_news_themes',
			),
			'apple_news_export_theme' => array(
				'callback' => array( $this, 'export_theme' ),
				'nonce' => 'apple_news_themes',
			),
			'apple_news_delete_theme' => array(
				'callback' => array( $this, '_delete_theme' ),
				'nonce' => 'apple_news_themes',
			),
			'apple_news_save_edit_theme' => array(
				'callback' => array( $this, 'save_edit_theme' ),
				'nonce' => 'apple_news_save_edit_theme',
			),
			'apple_news_set_theme' => array(
				'callback' => array( $this, '_set_theme' ),
				'nonce' => 'apple_news_themes',
			),
		);

		add_action( 'admin_menu', array( $this, 'setup_theme_pages' ), 99 );
		add_action( 'admin_init', array( $this, 'action_router' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_filter( 'admin_title', array( $this, 'set_title' ), 10, 1 );
	}

	/**
	 * Route all possible theme actions to the right place.
	 *
	 * @access public
	 */
	public function action_router() {

		// Determine if an action was specified.
		if ( ! isset( $_POST['action'] ) ) {
			return;
		}

		// Determine if a valid action was specified.
		$action = sanitize_text_field( $_POST['action'] );
		if ( ( empty( $action )
			|| ! array_key_exists( $action, $this->_valid_actions ) )
		) {
			return;
		}

		// Check the nonce.
		check_admin_referer( $this->_valid_actions[ $action ]['nonce'] );

		// Call the callback for the action for further processing.
		call_user_func( $this->_valid_actions[ $action ]['callback'] );
	}

	/**
	 * Attempts to import a theme, given an associative array of theme properties.
	 *
	 * @param array $settings An associative array of theme settings to import.
	 *
	 * @access public
	 * @return bool|string True on success, or an error message on failure.
	 */
	public function import_theme( $settings ) {

		// Ensure that a theme name was provided.
		if ( empty( $settings['theme_name'] ) ) {
			return __( 'The theme file did not include a name', 'apple-news' );
		}

		// Extract and remove the name since it doesn't need to be stored.
		$name = $settings['theme_name'];
		unset( $settings['theme_name'] );

		// Create a new theme object and attempt to save it.
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( $name );
		$theme->load( $settings );
		if ( ! $theme->save() ) {
			return sprintf(
				__(
					'The theme file was invalid and cannot be imported: %s',
					'apple-news'
				),
				$theme->get_last_error()
			);
		}

		return true;
	}

	/**
	 * Theme edit page render.
	 *
	 * @access public
	 */
	public function page_theme_edit_render() {

		// Ensure the user has permission to load this screen.
		if ( ! current_user_can( apply_filters( 'apple_news_settings_capability', 'manage_options' ) ) ) {
			wp_die( esc_html__( 'You do not have permissions to access this page.', 'apple-news' ) );
		}

		// Negotiate theme object.
		$error = '';
		$theme = new \Apple_Exporter\Theme;
		if ( isset( $_GET['theme'] ) ) {
			$theme_name = sanitize_text_field( $_GET['theme'] );
			$theme->set_name( $theme_name );
			if ( false === $theme->load() ) {
				$error = sprintf(
					__( 'The theme %s does not exist', 'apple-news' ),
					$theme_name
				);
			}
		}

		// Set the URL for the back button and form action.
		$theme_admin_url = $this->theme_admin_url();

		// Get information about theme options.
		$theme_options = $theme->get_options();

		// Load the edit page.
		include plugin_dir_path( __FILE__ ) . 'partials/page_theme_edit.php';
	}

	/**
	 * Themes page render.
	 *
	 * @access public
	 */
	public function page_themes_render() {
		if ( ! current_user_can( apply_filters( 'apple_news_settings_capability', 'manage_options' ) ) ) {
			wp_die( esc_html__( 'You do not have permissions to access this page.', 'apple-news' ) );
		}

		include plugin_dir_path( __FILE__ ) . 'partials/page_themes.php';
	}

	/**
	 * Register assets for the options page.
	 *
	 * @param string $hook
	 * @access public
	 */
	public function register_assets( $hook ) {
		if ( ! in_array( $hook, array(
			'apple-news_page_apple-news-themes',
			'admin_page_apple-news-theme-edit',
		), true ) ) {
			return;
		}

		wp_enqueue_style(
			'apple-news-themes-css',
			plugin_dir_url( __FILE__ ) . '../assets/css/themes.css',
			array(),
			self::$version
		);

		wp_enqueue_script(
			'apple-news-themes-js',
			plugin_dir_url( __FILE__ ) . '../assets/js/themes.js',
			array( 'jquery' ),
			self::$version
		);

		wp_localize_script( 'apple-news-themes-js', 'appleNewsThemes', array(
			'deleteWarning' => __( 'Are you sure you want to delete the theme', 'apple-news' ),
			'noNameError' => __( 'Please enter a name for the new theme.', 'apple-news' ),
			'tooLongError' => __( 'Theme names must be 45 characters or less.', 'apple-news' ),
		) );

		if ( 'admin_page_apple-news-theme-edit' === $hook ) {
			wp_enqueue_style(
				'apple-news-select2-css',
				plugin_dir_url( __FILE__ ) . '../vendor/select2/select2.min.css',
				array(),
				self::$version
			);
			wp_enqueue_style(
				'apple-news-theme-edit-css',
				plugin_dir_url( __FILE__ ) . '../assets/css/theme-edit.css',
				array(),
				self::$version
			);

			wp_enqueue_script( 'iris' );
			wp_enqueue_script(
				'apple-news-select2-js',
				plugin_dir_url( __FILE__ ) . '../vendor/select2/select2.full.min.js',
				array( 'jquery' ),
				self::$version
			);
			wp_enqueue_script(
				'apple-news-theme-edit-js',
				plugin_dir_url( __FILE__ ) . '../assets/js/theme-edit.js',
				array(
					'jquery',
					'jquery-ui-draggable',
					'jquery-ui-sortable',
					'apple-news-select2-js',
					'iris',
					'apple-news-preview-js'
				),
				self::$version
			);

			wp_localize_script( 'apple-news-theme-edit-js', 'appleNewsThemeEdit', array(
				'fontNotice' => __( 'Font preview is only available on macOS', 'apple-news' ),
			) );
		}
	}

	/**
	 * Fix the title since WordPress doesn't set one.
	 *
	 * @param string $admin_title The title to be filtered.
	 *
	 * @access public
	 * @return string
	 */
	public function set_title( $admin_title ) {
		$screen = get_current_screen();
		if ( 'admin_page_' . $this->theme_edit_page_name === $screen->base ) {
			$admin_title = sprintf(
				__( 'Edit Theme %s', 'apple-news' ),
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
	public function setup_theme_pages() {
		add_submenu_page(
			'apple_news_index',
			__( 'Apple News Themes', 'apple-news' ),
			__( 'Themes', 'apple-news' ),
			apply_filters( 'apple_news_settings_capability', 'manage_options' ),
			$this->theme_page_name,
			array( $this, 'page_themes_render' )
		);

		add_submenu_page(
			null,
			__( 'Apple News Edit Theme', 'apple-news' ),
			__( 'Edit Theme', 'apple-news' ),
			apply_filters( 'apple_news_settings_capability', 'manage_options' ),
			$this->theme_edit_page_name,
			array( $this, 'page_theme_edit_render' )
		);
	}

	/**
	 * Handles deleting a theme.
	 *
	 * @access private
	 */
	private function _delete_theme() {

		// Attempt to get the name of the theme from postdata.
		if ( empty( $name ) && ! empty( $_POST['apple_news_theme'] ) ) {
			$name = sanitize_text_field( $_POST['apple_news_theme'] );
		}

		// Ensure a name was provided.
		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to delete the theme because no name was provided', 'apple-news' )
			);
			return;
		}

		// Remove the theme.
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( $name );
		$theme->delete();

		// Indicate success.
		\Admin_Apple_Notice::success( sprintf(
			__( 'Successfully deleted theme %s', 'apple-news' ),
			$name
		) );
	}

	/**
	 * Handles setting the active theme.
	 *
	 * @access private
	 */
	private function _set_theme() {

		// Get the theme name from postdata.
		if ( ! empty( $_POST['apple_news_active_theme'] ) ) {
			$name = sanitize_text_field( $_POST['apple_news_active_theme'] );
		}

		// Ensure we have a theme name.
		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to set the theme because no name was provided', 'apple-news' )
			);

			return;
		}

		// Set the theme as active.
		$theme = new \Apple_Exporter\Theme;
		$theme->set_name( $name );
		$theme->set_active();

		// Indicate success.
		\Admin_Apple_Notice::success( sprintf(
			__( 'Successfully switched to theme %s', 'apple-news' ),
			$name
		) );
	}

	// TODO: REFACTOR FROM HERE

	/**
	 * Handles uploading a new theme from a JSON file.
	 *
	 * @access private
	 */
	private function upload_theme() {
		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			\Admin_Apple_Notice::error(
				__( 'There was an error uploading the theme file', 'apple-news' )
			);
			return;
		}

		if ( ! isset( $file['file'], $file['id'] ) ) {
			\Admin_Apple_Notice::error(
				__( 'The file did not upload properly. Please try again.', 'apple-news' )
			);
			return;
		}

		$this->file_id = absint( $file['id'] );

		if ( ! file_exists( $file['file'] ) ) {
			wp_import_cleanup( $this->file_id );
			\Admin_Apple_Notice::error( sprintf(
				__( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'wp-options-importer' ),
				esc_html( $file['file'] )
			) );
			return;
		}

		if ( ! is_file( $file['file'] ) ) {
			wp_import_cleanup( $this->file_id );
			\Admin_Apple_Notice::error(
				__( 'The path is not a file, please try again.', 'apple-news' )
			);
			return;
		}

		$file_contents = file_get_contents( $file['file'] );
		$import_data = json_decode( $file_contents, true );

		wp_import_cleanup( $this->file_id );

		// Try to get the theme name prior to import.
		$name = ( ! empty( $import_data['theme_name'] ) )
			? $import_data['theme_name']
			: '';

		// Try to import the theme.
		$result = $this->import_theme( $import_data );
		if ( true !== $result ) {
			\Admin_Apple_Notice::error( $result );

			return;
		}

		// Indicate success
		\Admin_Apple_Notice::success( sprintf(
			__( 'Successfully uploaded theme %s', 'apple-news' ),
			$name
		) );
	}

	/**
	 * Handles exporting a new theme to a JSON file.
	 *
	 * @param string $name
	 * @access private
	 */
	private function export_theme( $name = null ) {
		// If no name was provided, attempt to get it from POST data
		if ( empty( $name ) && ! empty( $_POST['apple_news_theme'] ) ) {
			$name = sanitize_text_field( $_POST['apple_news_theme'] );
		}

		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to export the theme because no name was provided', 'apple-news' )
			);
			return;
		}

		$key = $this->theme_key_from_name( $name );
		$theme = get_option( $key );
		if ( empty( $theme ) ) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'The theme $s could not be found', 'apple-news' ),
				$name
			) );
			return;
		}

		// Add the theme name
		$theme['theme_name'] = $name;

		// Generate the filename
		$filename = $key . '.json';

		// Start the download
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );

		$JSON_PRETTY_PRINT = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;
		echo wp_json_encode( $theme, $JSON_PRETTY_PRINT );

		exit;
	}

	/**
	 * Handle saving theme settings from the edit form.
	 *
	 * @access private
	 */
	private function save_edit_theme() {

		// Create a theme object.
		$theme = new \Apple_Exporter\Theme;

		// Get the theme name.
		if ( ! isset( $_POST['apple_news_theme_name'] ) ) {
			\Admin_Apple_Notice::error(
				__( 'No theme name was set', 'apple-news' )
			);

			return;
		}

		// Ensure the theme name is valid.
		$name = sanitize_text_field( $_POST['apple_news_theme_name'] );
		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'The theme name was empty', 'apple-news' )
			);
		}

		// Negotiate previous theme name.
		$previous_name = ( ! empty( $_POST['apple_news_theme_name_previous'] ) )
			? sanitize_text_field( $_POST['apple_news_theme_name_previous'] )
			: '';

		// Determine whether this theme is new, is an update, or is being renamed.
		$action = 'update';
		if ( empty( $previous_name ) ) {
			$action = 'new';
		} elseif ( $name !== $previous_name ) {
			$action = 'rename';
		}

		// If the theme is new or renamed, ensure the name isn't taken.
		if ( ( 'new' === $action || 'rename' === $action )
			&& \Apple_Exporter\Theme::theme_exists( $name )
		) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'Theme name %s is already in use.', 'apple-news' ),
				$name
			) );

			return;
		}

		// Set the theme name.
		if ( 'rename' === $action ) {
			$theme->set_name( $previous_name );
		} else {
			$theme->set_name( $name );
		}

		// If the theme isn't new, load existing configuration from the database.
		if ( 'new' !== $action ) {
			$theme->load();
		}

		// Load postdata into the theme and try to save.
		$theme->load_postdata();
		if ( ! $theme->save() ) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'Could not save theme %1$s: %2$s', 'apple-news' ),
				$name,
				$theme->get_last_error()
			) );

			return;
		}

		// Process rename, if requested.
		if ( 'rename' === $action ) {
			$theme->rename( $name );
		}

		// Indicate success.
		\Admin_Apple_Notice::success( sprintf(
			__( 'The theme %s was saved successfully', 'apple-news' ),
			$name
		) );
	}

	/**
	 * Filter the current settings down to only formatting settings.
	 *
	 * @return array
	 * @access private
	 */
	private function get_formatting_settings( $name = null ) {
		// Determine what to do based on if the name is set
		if ( ! empty( $name ) ) {
			return $this->get_formatting_object( $name )->get_loaded_settings();
		} else {
			// Get the keys of all formatting settings
			$formatting = $this->get_formatting_object();
			$formatting_settings = $formatting->get_settings();
			if ( empty( $formatting_settings ) ) {
				return array();
			}

			$formatting_settings_keys = array_keys( $formatting_settings );

			// Get all current settings
			$settings = new Admin_Apple_Settings();
			$all_settings = $settings->fetch_settings()->all();

			// Retrieve values only for formatting settings
			$theme_settings = array();
			foreach ( $formatting_settings_keys as $key ) {
				if ( isset( $all_settings[ $key ] ) ) {
					$theme_settings[ $key ] = $all_settings[ $key ];
				}
			}

			return $theme_settings;
		}
	}

	/**
	 * Updates global settings with the active theme settings.
	 *
	 * @param string $name
	 * @return boolean
	 * @access private
	 */
	private function update_global_settings( $name ) {
		// Attempt to load the theme settings
		$key = $this->theme_key_from_name( $name );
		$new_settings = get_option( $key );
		if ( empty( $new_settings ) ) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'There was an error loading settings for the theme %s', 'apple-news' ),
				$key
			) );
			return false;
		}

		// Preserve API settings since these are not part of the theme
		$settings = new \Admin_Apple_Settings();
		$current_settings = $settings->fetch_settings()->all();
		$new_settings = wp_parse_args( $new_settings, $current_settings );

		// Load the settings from the theme
		$settings->save_settings( $new_settings );

		return true;
	}

	/**
	 * Generates the edit URL for a theme
	 *
	 * @param string $name
	 * @return string
	 * @access public
	 */
	public function theme_edit_url( $name = null ) {
		$url = add_query_arg( 'page', $this->theme_edit_page_name, admin_url( 'admin.php' ) );

		if ( ! empty( $name ) ) {
			$url = add_query_arg( 'theme', $name, $url );
		}

		return $url;
	}

	/**
	 * Returns the URL of the themes admin page
	 *
	 * @param string $name
	 * @return string
	 * @access public
	 */
	public function theme_admin_url() {
		return add_query_arg( 'page', $this->theme_page_name, admin_url( 'admin.php' ) );
	}
}
