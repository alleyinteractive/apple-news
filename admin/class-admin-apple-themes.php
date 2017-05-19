<?php
/**
 * This class is in charge of handling the management of Apple News themes.
 */
class Admin_Apple_Themes extends Apple_News {

	/**
	 * Theme management page name.
	 *
	 * @var string
	 * @access public
	 */
	public $theme_page_name;

	/**
	 * Theme edit page name.
	 *
	 * @var string
	 * @access public
	 */
	public $theme_edit_page_name;

	/**
	 * Key for the theme index.
	 *
	 * @var string
	 */
	const THEME_INDEX_KEY = 'apple_news_installed_themes';

	/**
	 * Key for the active theme.
	 *
	 * @var string
	 */
	const THEME_ACTIVE_KEY = 'apple_news_active_theme';

	/**
	 * Valid actions handled by this class and their callback functions.
	 *
	 * @var array
	 * @access private
	 */
	private $valid_actions;

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
		if ( 'hidden' === $option['type'] ) {
			return '';
		}

		// If the field has its own render callback, use that instead.
		if ( ! empty( $option['callback'] ) ) {
			return call_user_func( $option['callback'] );
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
				$fonts = Admin_Apple_Settings_Section::list_fonts();
				foreach ( $fonts as $option ) {
					$field .= sprintf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $option ),
						selected( $option, $value, false ),
						esc_html( $option )
					);
				}

				// Wrap the options in the select.
				$field = '<select class="select2 font" id="%s" name="%s">' . $field
					. '</select>';

				break;
			case 'integer':
				$field = '<input type="number" id="%s" name="%s" value="%s">';

				break;
			default:

				// Handle array types differently.
				if ( is_array( $option['type'] ) ) {

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
				}

				// If nothing else matches, it's a string.
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
		if ( is_array( $option['type'] ) || 'font' === $option['type'] ) {
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
	 * Constructor.
	 */
	function __construct() {
		$this->theme_page_name = $this->plugin_domain . '-themes';
		$this->theme_edit_page_name = $this->plugin_domain . '-theme-edit';

		$this->valid_actions = array(
			'apple_news_upload_theme' => array(
				'callback' => array( $this, 'upload_theme' ),
				'nonce' => 'apple_news_themes',
			),
			'apple_news_export_theme' => array(
				'callback' =>  array( $this, 'export_theme' ),
				'nonce' => 'apple_news_themes',
			),
			'apple_news_delete_theme' => array(
				'callback' =>  array( $this, 'delete_theme' ),
				'nonce' => 'apple_news_themes',
			),
			'apple_news_save_edit_theme' => array(
				'callback' =>  array( $this, 'save_edit_theme' ),
				'nonce' => 'apple_news_save_edit_theme',
			),
			'apple_news_set_theme' => array(
				'callback' =>  array( $this, 'set_theme' ),
				'nonce' => 'apple_news_themes',
			),
		);

		add_action( 'admin_menu', array( $this, 'setup_theme_pages' ), 99 );
		add_action( 'admin_init', array( $this, 'action_router' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_filter( 'admin_title', array( $this, 'set_title' ), 10, 2 );
	}

	/**
	 * Fix the title since WordPress doesn't set one.
	 *
	 * @param string $admin_title
	 * @param string $title
	 * @return strign
	 * @access public
	 */
	public function set_title( $admin_title, $title ) {
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
	 * Check for a valid theme setup on the site.
	 *
	 * @access private
	 */
	private function validate_themes() {
		$themes = $this->list_themes();
		if ( empty( $themes ) ) {
			$name = __( 'Default', 'apple-news' );
			$this->save_theme( $name, $this->get_formatting_settings() );
			$this->set_theme( $name, true );
		}
	}

	/**
	 * Options page setup.
	 *
	 * @access public
	 */
	public function setup_theme_pages() {
		$this->validate_themes();

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
	 * List all available themes
	 *
	 * @access public
	 * @return array
	 */
	public function list_themes() {
		return get_option( self::THEME_INDEX_KEY, array() );
	}

	/**
	 * Get the active theme
	 *
	 * @access public
	 * @return string
	 */
	public function get_active_theme() {
		return get_option( self::THEME_ACTIVE_KEY );
	}

	/**
	 * Get a specific theme
	 *
	 * @param string $name
	 * @access public
	 * @return array
	 */
	public function get_theme( $name ) {
		return get_option( $this->theme_key_from_name( $name ), array() );
	}

	/**
	 * Attempts to import a theme, given an associative array of theme properties.
	 *
	 * @param array $theme An associative array of theme properties to import.
	 *
	 * @access public
	 * @return bool|string True on success, or an error message on failure.
	 */
	public function import_theme( $theme ) {

		// Validate the theme before proceeding.
		$result = $this->validate_data( $theme );
		if ( ! is_array( $result ) ) {
			return sprintf(
				__(
					'The theme file was invalid and cannot be imported: %s',
					'apple-news'
				),
				$result
			);
		}

		// Extract and remove the name since it doesn't need to be stored.
		$name = $result['theme_name'];
		unset( $result['theme_name'] );

		// Process the save operation.
		$this->save_theme( $name, $result, true );

		return true;
	}

	/**
	 * Saves the theme JSON for the key provided.
	 *
	 * @param string $name
	 * @param array $settings
	 * @param boolean $silent We don't always want this to display a message if it's behind the scenes
	 * @access private
	 */
	public function save_theme( $name, $settings, $silent = false ) {
		// Save the theme settings
		update_option( $this->theme_key_from_name( $name ), $settings, false );

		// Update the index
		$this->index_theme( $name );

		// Indicate success
		if ( true !== $silent ) {
			\Admin_Apple_Notice::success( sprintf(
				__( 'The theme %s was saved successfully', 'apple-news' ),
				$name
			) );
		}
	}

	/**
	 * Saves the theme to the theme index.
	 *
	 * @param string $name
	 * @access private
	 */
	private function index_theme( $name ) {
		// Get the index
		$index = self::list_themes();
		if ( ! is_array( $index ) ) {
			$index = array();
		}

		$key = $this->theme_key_from_name( $name );

		// Add the key to the index
		$index[] = $name;

		// If a duplicate was added, it's just going to overwrite.
		// The user has been warned by this point.
		$index = array_unique( $index );

		// Save the theme index
		update_option( self::THEME_INDEX_KEY, $index, false );
	}

	/**
	 * Saves the theme to the theme index.
	 *
	 * @param string $name
	 * @access private
	 */
	private function unindex_theme( $name ) {
		$themes = $this->list_themes();
		$index = array_search( $name, $themes );
		if ( false === $index ) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'The theme %s to be deleted does not exist', 'apple-news' ),
				$name
			) );
			return;
		}

		// Remove from the index and delete settings
		unset( $themes[ $index ] );
		update_option( self::THEME_INDEX_KEY, $themes, false );
		delete_option( $this->theme_key_from_name( $name ) );
	}

	/**
	 * Route all possible theme actions to the right place.
	 *
	 * @param string $hook
	 * @access public
	 */
	public function action_router() {
		// Check for a valid action
		$action	= isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : null;
		if ( ( empty( $action ) || ! array_key_exists( $action, $this->valid_actions ) ) ) {
			return;
		}

		// Check the nonce
		check_admin_referer( $this->valid_actions[ $action ]['nonce'] );

		// Call the callback for the action for further processing
		call_user_func( $this->valid_actions[ $action ]['callback'] );
	}

	/**
	 * Handles setting the active theme.
	 *
	 * @param string $name
	 * @param boolean $silent We don't always want this to display a message if it's behind the scenes
	 * @access public
	 */
	public function set_theme( $name = null, $silent = false ) {
		// If no name was provided, attempt to get it from POST data
		if ( empty( $name ) && ! empty( $_POST['apple_news_active_theme'] ) ) {
			$name = sanitize_text_field( $_POST['apple_news_active_theme'] );
		}

		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to set the theme because no name was provided', 'apple-news' )
			);
			return;
		}

		// Update global formatting settings with the theme settings
		$result = $this->update_global_settings( $name );
		if ( false === $result ) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'There was an error updating global settings with the theme %s', 'apple-news' ),
				$name
			) );
			return;
		}

		// Set the theme active
		update_option( self::THEME_ACTIVE_KEY, $name, false );

		// Indicate success
		if ( true !== $silent ) {
			\Admin_Apple_Notice::success( sprintf(
				__( 'Successfully switched to theme %s', 'apple-news' ),
				$name
			) );
		}
	}

	/**
	 * Handles deleting a theme.
	 *
	 * @param string $name
	 * @access private
	 */
	private function delete_theme( $name = null ) {
		// If no name was provided, attempt to get it from POST data
		if ( empty( $name ) && ! empty( $_POST['apple_news_theme'] ) ) {
			$name = sanitize_text_field( $_POST['apple_news_theme'] );
		}

		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to delete the theme because no name was provided', 'apple-news' )
			);
			return;
		}

		// Get the key
		$key = $this->theme_key_from_name( $name );

		// Unindex the theme
		$this->unindex_theme( $name );

		// Delete the theme
		delete_option( $key );

		// Indicate success
		\Admin_Apple_Notice::success( sprintf(
			__( 'Successfully deleted theme %s', 'apple-news' ),
			$name
		) );
	}

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
	 * @param string $name
	 * @access private
	 */
	private function save_edit_theme() {
		// Get the theme name
		if ( ! isset( $_POST['apple_news_theme_name'] ) ) {
			\Admin_Apple_Notice::error(
				__( 'No theme name was set', 'apple-news' )
			);
		}

		$name = sanitize_text_field( $_POST['apple_news_theme_name'] );
		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'The theme name was empty', 'apple-news' )
			);
		}

		// Create a formatting object from the name.
		// It will automatically save settings.
		$formatting = $this->get_formatting_object( $name );

		// Index the theme and check if it changed names
		$this->index_theme( $name );
		$previous_name = ( isset( $_POST['apple_news_theme_name_previous'] ) ) ? sanitize_text_field( $_POST['apple_news_theme_name_previous'] ) : '';
		if ( $name !== $previous_name && ! empty( $previous_name ) ) {
			$this->unindex_theme( $previous_name );
		}

		// If this is the active theme, update global settings
		if ( $name === $this->get_active_theme()
			|| $previous_name === $this->get_active_theme() ) {
			$this->set_theme( $name, true );
		}

		// Indicate success
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
	 * Get a formatting object for the given theme.
	 * If no theme is provided, get current global formatting settings.
	 *
	 * @todo Remove this.
	 *
	 * @return array
	 * @access private
	 */
	private function get_formatting_object( $name = null ) {
		if ( empty( $name ) ) {
			return new Admin_Apple_Settings_Section_Formatting( '' );
		} else {
			return new Admin_Apple_Settings_Section_Formatting(
				$this->theme_edit_page_name,
				false,
				'apple_news_save_edit_theme',
				$this->theme_key_from_name( $name )
			);
		}
	}

	/**
	 * Validate data for an import file upload.
	 *
	 * @param array $data
	 * @return array|boolean
	 * @access private
	 */
	private function validate_data( $data ) {
		$settings = new \Apple_Exporter\Settings();
		$default_settings = $settings->all();
		$clean_settings = array();

		// Check for the theme name
		if ( ! isset( $data['theme_name'] ) ) {
			return __( 'The theme file did not include a name', 'apple-news' );
		}
		$clean_settings['theme_name'] = $data['theme_name'];
		unset( $data['theme_name'] );

		// Get the formatting settings that are allowed to be included in a theme
		$formatting = $this->get_formatting_object();
		$formatting_settings = $formatting->get_settings();
		if ( empty( $formatting_settings ) || ! is_array( $formatting_settings ) ) {
			return __( 'There was an error retrieving formatting settings', 'apple-news' );
		}
		$valid_settings = array_keys( $formatting_settings );

		// Get all available fonts in the system
		$fonts = $formatting->list_fonts();

		// Iterate through the valid settings and handle
		// the appropriate validation and sanitization for each
		foreach ( $valid_settings as $setting ) {
			if ( ! isset( $data[ $setting ] ) ) {
				// Get the default value instead.
				// This ensures backwards compatiblity with theme files
				// when new settings are added in future plugin versions.
				if ( isset( $default_settings[ $setting ] ) ) {
					$data[ $setting ] = $default_settings[ $setting ];
				} else {
					return sprintf(
						__( 'The theme was missing the required setting %s and no default was found', 'apple-news' ),
						$setting
					);
				}
			}

			// Find the appropriate sanitization method for each setting
			if ( ! empty( $formatting_settings[ $setting ]['type'] ) ) {
				// Figure out the proper sanitization function
				if ( 'integer' === $formatting_settings[ $setting ]['type'] ) {
					// Simply sanitize
					$clean_settings[ $setting ] = absint( $data[ $setting ] );
				} else if ( 'float' === $formatting_settings[ $setting ]['type'] ) {
					// Simply sanitize
					$clean_settings[ $setting ] = floatval( $data[ $setting ] );
				} else if ( 'color' === $formatting_settings[ $setting ]['type'] ) {
					// Sanitize
					$color = sanitize_text_field( $data[ $setting ] );

					// Validate
					if ( false === preg_match( '/#([a-f0-9]{3}){1,2}\b/i', $color ) ) {
						return sprintf(
							__( 'Invalid color value %s specified for setting %s', 'apple-news' ),
							$color,
							$setting
						);
					}

					$clean_settings[ $setting ] = $color;
				} else if ( 'font' === $formatting_settings[ $setting ]['type'] ) {
					// Sanitize
					$color = sanitize_text_field( $data[ $setting ] );

					// Validate
					if ( ! in_array( $data[ $setting ], $fonts, true ) ) {
						return sprintf(
							__( 'Invalid font value %s specified for setting %s', 'apple-news' ),
							$data[ $setting ],
							$setting
						);
					}

					$clean_settings[ $setting ] = $data[ $setting ];
				} else if ( 'text' === $formatting_settings[ $setting ]['type'] ) {
					// Simply sanitize
					$clean_settings[ $setting ] = sanitize_text_field( $data[ $setting ] );
				} else if ( is_array( $formatting_settings[ $setting ]['type'] ) ) {
					// Sanitize
					$color = sanitize_text_field( $data[ $setting ] );

					// Validate
					if ( ! in_array( $data[ $setting ], $formatting_settings[ $setting ]['type'], true ) ) {
						return sprintf(
							__( 'Invalid value %s specified for setting %s', 'apple-news' ),
							$data[ $setting ],
							$setting
						);
					}

					$clean_settings[ $setting ] = $data[ $setting ];
				}
			} else if ( 'meta_component_order' === $setting ) {
				// This needs to be handled specially
				if ( ! is_array( $data[ $setting ] ) ) {
					return __( 'Invalid value for meta component order', 'apple-news' );
				}

				// This has to be done separately for PHP 5.3 compatibility
				$array_diff = array_diff( $data[ $setting ], array( 'cover', 'title', 'byline' ) );
				if ( ! empty( $array_diff ) ) {
					return __( 'Invalid value for meta component order', 'apple-news' );
				}

				// Sanitize
				$clean_settings[ $setting ] = array_map( 'sanitize_text_field', $data[ $setting ] );
			} else {
				return sprintf(
					__( 'An invalid setting was encountered: %s', 'apple-news' ),
					$setting
				);
			}

			// Remove this from the settings being processed so we know later
			// if extra, invalid data was included.
			unset( $data[ $setting ] );
		}

		// Handle JSON templates.
		$this->validate_json_templates( $data, $clean_settings );

		// Check if invalid data was present
		if ( ! empty( $data ) ) {
			return __( 'The theme file contained unsupported settings', 'apple-news' );
		}

		return $clean_settings;
	}

	/**
	 * Ensures that JSON templates defined in a theme spec are valid.
	 *
	 * @param array &$data The data array containing import data for the theme.
	 * @param array &$clean_settings The cleaned array containing the final settings.
	 *
	 * @access private
	 */
	private function validate_json_templates( &$data, &$clean_settings ) {

		// If no JSON templates are defined in the theme, bail.
		if ( empty( $data['json_templates'] )
			|| ! is_array( $data['json_templates'] )
		) {
			return;
		}

		// Get a list of components that may have customized JSON.
		$component_factory = new \Apple_Exporter\Component_Factory();
		$component_factory->initialize();
		$components = $component_factory::get_components();

		// Iterate over components and look for customized JSON for each.
		foreach ( $components as $component_class ) {

			// Negotiate the component key.
			$component = new $component_class;
			$component_key = $component->get_component_name();

			// Determine if this component key is defined in the import data.
			if ( empty( $data['json_templates'][ $component_key ] )
				|| ! is_array( $data['json_templates'][ $component_key ] )
			) {
				continue;
			}

			// Loop through component key and validate.
			$current_component = &$data['json_templates'][ $component_key ];
			$specs = $component->get_specs();
			foreach ( $specs as $spec_key => $spec ) {

				// Determine if the spec is defined as a JSON template in the theme.
				if ( empty( $current_component[ $spec_key ] )
					|| ! is_array( $current_component[ $spec_key ] )
				) {
					continue;
				}

				// Validate this spec.
				if ( ! $spec->validate( $current_component[ $spec_key ] ) ) {
					\Admin_Apple_Notice::error( sprintf(
						__(
							'The spec for %s had invalid tokens and cannot be saved',
							'apple-news'
						),
						$component_key . '/' . $spec_key
					) );

					return;
				}

				// Clone this spec over to the clean settings array.
				$clean_settings['json_templates'][ $component_key ][ $spec_key ] = $current_component[ $spec_key ];
				unset( $data['json_templates'][ $component_key ][ $spec_key ] );
			}

			// Clean up.
			if ( empty( $data['json_templates'][ $component_key] ) ) {
				unset( $data['json_templates'][ $component_key ] );
			}
		}

		// Clean up.
		if ( empty( $data['json_templates'] ) ) {
			unset( $data['json_templates'] );
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
