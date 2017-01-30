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
	 * Theme preview page name.
	 *
	 * @var string
	 * @access public
	 */
	public $theme_preview_page_name;

	/**
	 * Key for the theme index.
	 *
	 * @var string
	 * @const
	 */
	const theme_index_key = 'apple_news_installed_themes';

	/**
	 * Key for the active theme.
	 *
	 * @var string
	 * @const
	 */
	const theme_active_key = 'apple_news_active_theme';

	/**
	 * Prefix for individual theme keys.
	 *
	 * @var string
	 * @const
	 */
	const theme_key_prefix = 'apple_news_theme_';

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
		$this->theme_page_name = $this->plugin_domain . '-themes';
		$this->theme_preview_page_name = $this->plugin_domain . '-theme-preview';

		$this->valid_actions = array(
			'apple_news_create_theme' => array( $this, 'create_theme' ),
			'apple_news_upload_theme' => array( $this, 'upload_theme' ),
			'apple_news_export_theme' => array( $this, 'export_theme' ),
			'apple_news_delete_theme' => array( $this, 'delete_theme' ),
			'apple_news_set_theme' => array( $this, 'set_theme' ),
		);

		add_action( 'admin_menu', array( $this, 'setup_theme_pages' ), 99 );
		add_action( 'admin_init', array( $this, 'action_router' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Check for a valid theme setup on the site.
	 *
	 * @access private
	 */
	private function validate_themes() {
		$themes = $this->list_themes();
		if ( empty( $themes ) ) {
			$this->create_theme( __( 'Default', 'apple-news' ) );
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
			__( 'Apple News Theme Preview', 'apple-news' ),
			__( 'Theme Preview', 'apple-news' ),
			apply_filters( 'apple_news_settings_capability', 'manage_options' ),
			$this->theme_preview_page_name,
			array( $this, 'page_theme_preview_render' )
		);
	}

	/**
	 * Themes page render.
	 *
	 * @access public
	 */
	public function page_themes_render() {
		if ( ! current_user_can( apply_filters( 'apple_news_settings_capability', 'manage_options' ) ) ) {
			wp_die( __( 'You do not have permissions to access this page.', 'apple-news' ) );
		}

		include plugin_dir_path( __FILE__ ) . 'partials/page_themes.php';
	}

	/**
	 * Theme preview page render.
	 *
	 * @access public
	 */
	public function page_theme_preview_render() {
		if ( ! current_user_can( apply_filters( 'apple_news_settings_capability', 'manage_options' ) ) ) {
			wp_die( __( 'You do not have permissions to access this page.', 'apple-news' ) );
		}

		$error = '';
		// Check for a valid theme
		if ( ! isset( $_GET['theme'] ) ) {
			$error = __( 'No theme was specified to preview', 'apple-news' );
		} else {
			$theme_name = sanitize_text_field( $_GET['theme'] );

			// Load the theme
			$theme = get_option( $this->theme_key_from_name( $theme_name ) );
			if ( empty( $theme ) || ! is_array( $theme ) ) {
				$error = sprintf(
					__( 'The theme %s does not exist', 'apple-news' ),
					$theme_name
				);
			}
		}

		// Set the URL for the back button
		$theme_admin_url = $this->theme_admin_url();

		// Load the preview page
		include plugin_dir_path( __FILE__ ) . 'partials/page_theme_preview.php';
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
			'apple-news_page_apple-news-theme-preview',
		), true ) ) {
			return;
		}

		wp_enqueue_style( 'apple-news-themes-css', plugin_dir_url( __FILE__ ) .
		'../assets/css/themes.css', array() );

		wp_enqueue_script( 'apple-news-themes-js', plugin_dir_url( __FILE__ ) .
			'../assets/js/themes.js', array( 'jquery' )
		);

		wp_localize_script( 'apple-news-themes-js', 'appleNewsThemes', array(
			'deleteWarning' => __( 'Are you sure you want to delete the theme', 'apple-news' ),
			'noNameError' => __( 'Please enter a name for the new theme.', 'apple-news' ),
			'tooLongError' => __( 'Theme names must be 45 characters or less.', 'apple-news' ),
		) );
	}

	/**
	 * List all available themes
	 *
	 * @access public
	 * @return array
	 */
	public function list_themes() {
		return get_option( self::theme_index_key, array() );
	}

	/**
	 * Get the active theme
	 *
	 * @access public
	 * @return string
	 */
	public function get_active_theme() {
		return get_option( self::theme_active_key );
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
	 * Saves the theme JSON for the key provided.
	 *
	 * @param string $name
	 * @param array $settings
	 * @access private
	 */
	private function save_theme( $name, $settings ) {
		// Get the index
		$index = self::list_themes();
		if ( ! is_array( $index ) ) {
			$index = array();
		}

		$key = $this->theme_key_from_name( $name );

		// Save the theme settings
		update_option( $key, $settings, false );

		// Add the key to the index
		$index[] = $name;

		// If a duplicate was added, it's just going to overwrite.
		// The user has been warned by this point.
		$index = array_unique( $index );

		// Save the theme index
		update_option( self::theme_index_key, $index, false );

		// Indicate success
		\Admin_Apple_Notice::success( sprintf(
			__( 'The theme %s was saved successfully', 'apple-news' ),
			$name
		) );
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
		check_admin_referer( 'apple_news_themes' );

		// Call the callback for the action for further processing
		call_user_func( $this->valid_actions[ $action ] );
	}

	/**
	 * Handles creating a new theme from current formatting settings.
	 *
	 * @param string $name
	 * @access private
	 */
	private function create_theme( $name = null ) {
		// If no name was provided, attempt to get it from POST data
		if ( empty( $name ) && ! empty( $_POST['apple_news_theme_name'] ) ) {
			$name = sanitize_text_field( $_POST['apple_news_theme_name'] );
		}

		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to create the theme because no name was provided', 'apple-news' )
			);
			return;
		}

		// Get all the current formatting settings for the site and save them as a new theme
		$this->save_theme( $name, $this->get_formatting_settings() );

		// If you're creating a theme from the current settings, it's technically already active.
		$this->set_theme( $name );
	}

	/**
	 * Handles setting the active theme.
	 *
	 * @param string $name
	 * @access private
	 */
	private function set_theme( $name = null ) {
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

		// Attempt to load the theme settings
		$key = $this->theme_key_from_name( $name );
		$new_settings = get_option( $key );
		if ( empty( $new_settings ) ) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'There was an error loading settings for the theme %s', 'apple-news' ),
				$key
			) );
			return;
		}

		// Preserve API settings since these are not part of the theme
		$settings = new \Admin_Apple_Settings();
		$current_settings = $settings->fetch_settings()->all();
		$new_settings = wp_parse_args( $new_settings, $current_settings );

		// Load the settings from the theme
		$settings->save_settings( $new_settings );

		// Set the theme active
		update_option( self::theme_active_key, $name, false );

		// Indicate success
		\Admin_Apple_Notice::success( sprintf(
			__( 'Successfully switched to theme %s', 'apple-news' ),
			$name
		) );
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

		// Make sure it exists
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
		update_option( self::theme_index_key, $themes, false );
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

		$result = $this->validate_data( $import_data );
		if ( ! is_array( $result ) ) {
			\Admin_Apple_Notice::error( sprintf(
				__( 'The theme file was invalid and cannot be imported: %s', 'apple-news' ),
				$result
			 ) );
			return;
		} else {
			// Get the name from the data and unset it since it doesn't need to be stored
			$name = $result['theme_name'];
			unset( $result['theme_name'] );
			$this->save_theme( $name, $result );
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
		echo json_encode( $theme, $JSON_PRETTY_PRINT );

		exit;
	}

	/**
	 * Filter the current settings down to only formatting settings.
	 *
	 * @return array
	 * @access private
	 */
	private function get_formatting_settings() {
		$theme_settings = array();

		// Get the keys of all formatting settings
		$formatting = new Admin_Apple_Settings_Section_Formatting( '' );
		$formatting_settings = $formatting->get_settings();
		if ( empty( $formatting_settings ) ) {
			return $theme_settings;
		}

		$formatting_settings_keys = array_keys( $formatting_settings );

		// Get all current settings
		$settings = new Admin_Apple_Settings();
		$all_settings = $settings->fetch_settings()->all();

		// Retrieve values only for formatting settings
		foreach ( $formatting_settings_keys as $key ) {
			if ( isset( $all_settings[ $key ] ) ) {
				$theme_settings[ $key ] = $all_settings[ $key ];
			}
		}

		return $theme_settings;
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
		$valid_settings = array_keys( $settings->all() );
		$clean_settings = array();

		// Check for the theme name
		if ( ! isset( $data['theme_name'] ) ) {
			return __( 'The theme file did not include a name', 'apple-news' );
		}
		$clean_settings['theme_name'] = $data['theme_name'];
		unset( $data['theme_name'] );

		// Get the formatting settings that are allowed to be included in a theme
		$formatting = new Admin_Apple_Settings_Section_Formatting( '' );
		$formatting_settings = $formatting->get_settings();
		if ( empty( $formatting_settings ) || ! is_array( $formatting_settings ) ) {
			return __( 'There was an error retrieving formatting settings', 'apple-news' );
		}
		$valid_settings = array_keys( $formatting_settings );

		// Get all available fonts in the system
		$section = new Admin_Apple_Settings_Section( '' );
		$fonts = $section->list_fonts();

		// Iterate through the valid settings and handle
		// the appropriate validation and sanitization for each
		foreach ( $valid_settings as $setting ) {
			if ( ! isset( $data[ $setting ] ) ) {
				return sprintf(
					__( 'The theme was missing the required setting %s', 'apple-news' ),
					$setting
				);
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
					if ( ! in_array( $data[ $setting ], $fonts ) ) {
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
					if ( ! in_array( $data[ $setting ], $formatting_settings[ $setting ]['type'] ) ) {
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
				if ( ! is_array( $data[ $setting ] )
					|| 3 !== count( $data[ $setting ] ) ) {
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

		// Check if invalid data was present
		if ( ! empty( $data ) ) {
			return __( 'The theme file contained unsupported settings', 'apple-news' );
		}

		return $clean_settings;
	}

	/**
	 * Generates a key for the theme from the provided name
	 *
	 * @param string $name
	 * @return string
	 * @access public
	 */
	public function theme_key_from_name( $name ) {
		return self::theme_key_prefix . sanitize_key( $name );
	}

	/**
	 * Generates the preview URL for a theme
	 *
	 * @param string $name
	 * @return string
	 * @access public
	 */
	public function theme_preview_url( $name ) {
		return add_query_arg(
			array(
				'page' => $this->theme_preview_page_name,
				'theme' => $name,
			),
			admin_url( 'admin.php' )
		);
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
