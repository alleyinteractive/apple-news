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
	private $valid_actions;

	/**
	 * Renders a theme option field for use in a form.
	 *
	 * @param \Apple_Exporter\Theme $theme The Theme object to use.
	 * @param string                $option_name The option name to process.
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
		$options = \Apple_Exporter\Theme::get_options();
		if ( ! isset( $options[ $option_name ] ) ) {
			return '';
		}

		// Ensure the option is not hidden. Hidden options should not be used.
		$option = $options[ $option_name ];
		if ( ! empty( $option['hidden'] ) ) {
			return '';
		}

		// If the field has its own render callback, use that instead.
		if ( ! empty( $option['callback'] ) && is_callable( $option['callback'] ) ) {
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
				foreach ( $option['options'] as $option_value ) {
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
		$this->theme_page_name      = $this->plugin_domain . '-themes';
		$this->theme_edit_page_name = $this->plugin_domain . '-theme-edit';

		$this->valid_actions = array(
			'apple_news_upload_theme'        => array(
				'callback' => array( $this, 'upload_theme' ),
				'nonce'    => 'apple_news_themes',
			),
			'apple_news_export_theme'        => array(
				'callback' => array( $this, 'export_theme' ),
				'nonce'    => 'apple_news_themes',
			),
			'apple_news_delete_theme'        => array(
				'callback' => array( $this, 'delete_theme' ),
				'nonce'    => 'apple_news_themes',
			),
			'apple_news_save_edit_theme'     => array(
				'callback' => array( $this, 'save_edit_theme' ),
				'nonce'    => 'apple_news_save_edit_theme',
			),
			'apple_news_set_theme'           => array(
				'callback' => array( $this, 'set_theme' ),
				'nonce'    => 'apple_news_themes',
			),
			'apple_news_load_example_themes' => array(
				'callback' => array( $this, 'load_example_themes' ),
				'nonce'    => 'apple_news_themes',
			),
		);

		add_action( 'admin_menu', array( $this, 'setup_theme_pages' ), 99 );
		add_action( 'admin_init', array( $this, 'action_router' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'admin_notices', array( $this, 'theme_nag' ), 1 );
		add_filter( 'admin_title', array( $this, 'set_title' ), 10, 1 );
	}

	/**
	 * Route all possible theme actions to the right place.
	 *
	 * @access public
	 */
	public function action_router() {

		// Determine if a valid action was specified.
		$action = isset( $_REQUEST['action'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) )
			: null;
		if ( ( empty( $action ) || ! array_key_exists( $action, $this->valid_actions ) ) ) {
			return;
		}

		// Check the nonce.
		check_admin_referer( $this->valid_actions[ $action ]['nonce'] );

		// Call the callback for the action for further processing.
		if ( isset( $this->valid_actions[ $action ]['callback'] )
			&& is_callable( $this->valid_actions[ $action ]['callback'] )
		) {
			call_user_func( $this->valid_actions[ $action ]['callback'] );
		}
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
		$theme = new \Apple_Exporter\Theme();
		$theme->set_name( $name );
		if ( ! $theme->load( $settings ) || ! $theme->save() ) {
			return sprintf(
				// translators: token is an error message.
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
		$theme = new \Apple_Exporter\Theme();
		if ( isset( $_GET['theme'] ) ) { // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
			$theme_name = sanitize_text_field( wp_unslash( $_GET['theme'] ) ); // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
			$theme->set_name( $theme_name );
			if ( false === $theme->load() ) {
				$error = sprintf(
					// translators: token is a theme name.
					__( 'The theme %s does not exist', 'apple-news' ),
					$theme_name
				);
			}
		}

		// Set the URL for the back button and form action.
		$theme_admin_url = $this->theme_admin_url();

		// Get information about theme options.
		$theme_options = \Apple_Exporter\Theme::get_options();

		// Load the edit page.
		include plugin_dir_path( __FILE__ ) . 'partials/page-theme-edit.php';
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

		include plugin_dir_path( __FILE__ ) . 'partials/page-themes.php';
	}

	/**
	 * Register assets for the options page.
	 *
	 * @param string $hook The context under which this function is called.
	 * @access public
	 */
	public function register_assets( $hook ) {
		if ( ! in_array(
			$hook,
			array(
				'apple-news_page_apple-news-themes',
				'admin_page_apple-news-theme-edit',
			),
			true
		) ) {
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
			self::$version,
			false
		);

		wp_localize_script(
			'apple-news-themes-js',
			'appleNewsThemes',
			array(
				'deleteWarning' => __( 'Are you sure you want to delete the theme', 'apple-news' ),
				'noNameError'   => __( 'Please enter a name for the new theme.', 'apple-news' ),
				'tooLongError'  => __( 'Theme names must be 45 characters or less.', 'apple-news' ),
			)
		);

		if ( 'admin_page_apple-news-theme-edit' === $hook ) {
			wp_enqueue_style(
				'apple-news-select2-css',
				plugin_dir_url( __FILE__ ) . '../assets/css/select2.min.css',
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
				plugin_dir_url( __FILE__ ) . '../assets/js/select2.full.min.js',
				array( 'jquery' ),
				self::$version,
				false
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
					'apple-news-preview-js',
				),
				self::$version,
				false
			);

			wp_localize_script(
				'apple-news-theme-edit-js',
				'appleNewsThemeEdit',
				array(
					'fontNotice' => __( 'Font preview is only available on macOS', 'apple-news' ),
				)
			);
		}
	}

	/**
	 * Fix the title since WordPress doesn't set one.
	 *
	 * @param string $admin_title The title to be filtered.
	 * @access public
	 * @return string
	 */
	public function set_title( $admin_title ) {
		$screen = get_current_screen();
		if ( 'admin_page_' . $this->theme_edit_page_name === $screen->base ) {
			$admin_title = sprintf(
				// translators: token is a theme name.
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

		// Don't add the submenu pages if the settings aren't initialized.
		if ( ! self::is_initialized() ) {
			return;
		}

		// Ensure there is at least one theme created.
		$registry = \Apple_Exporter\Theme::get_registry();
		if ( empty( $registry ) ) {
			$theme = new \Apple_Exporter\Theme();
			$theme->save();
			$theme->set_active();
		}

		// Add the primary themes page.
		add_submenu_page(
			'apple_news_index',
			__( 'Apple News Themes', 'apple-news' ),
			__( 'Themes', 'apple-news' ),
			apply_filters( 'apple_news_settings_capability', 'manage_options' ),
			$this->theme_page_name,
			array( $this, 'page_themes_render' )
		);

		// Add the edit theme page.
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
	 * Returns the URL of the themes admin page.
	 *
	 * @access public
	 * @return string The URL of the themes admin page.
	 */
	public function theme_admin_url() {
		return add_query_arg(
			'page',
			$this->theme_page_name,
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Generates the edit URL for a theme.
	 *
	 * @param string $name The name of the theme for which to generate an edit URL.
	 *
	 * @access public
	 * @return string The URL to edit a specific theme.
	 */
	public function theme_edit_url( $name = null ) {

		// Build the base edit URL.
		$url = add_query_arg(
			'page',
			$this->theme_edit_page_name,
			admin_url( 'admin.php' )
		);

		// Add the theme name to edit, if set.
		if ( ! empty( $name ) ) {
			$url = add_query_arg( 'theme', $name, $url );
		}

		return $url;
	}

	/**
	 * Nags the user about using Markdown.
	 *
	 * @access public
	 */
	public function theme_nag() {

		// If the plugin isn't initialized yet, don't nag the user.
		if ( true !== \Apple_News::is_initialized() ) {
			return;
		}

		// If we aren't on one of the Apple News admin pages, don't nag the user.
		$screen = get_current_screen();
		if ( false === strpos( $screen->base, 'apple_news' )
			&& false === strpos( $screen->base, 'apple-news' )
		) {
			return;
		}

		// If HTML support is not enabled, nag the user.
		$options = get_option( self::$option_name );
		if ( ! isset( $options['html_support'] ) || 'yes' !== $options['html_support'] ) {
			\Admin_Apple_Notice::info(
				sprintf(
					/* translators: First parameter is opening a tag, second is closing a tag */
					__( 'As of Publish to Apple News version 1.4.0, HTML format is the preferred output format. We noticed that you are still using Markdown. You can update this setting on the %1$ssettings page%2$s.', 'apple-news' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=apple-news-options' ) ) . '">',
					'</a>'
				),
				get_current_user_id(),
				true
			);
		}

		// If the active theme isn't named "Default", don't nag the user.
		if ( __( 'Default', 'apple-news' ) !== \Apple_Exporter\Theme::get_active_theme_name() ) {
			return;
		}

		// Determine if the theme is using the default settings.
		$theme = new \Apple_Exporter\Theme();
		$theme->set_name( \Apple_Exporter\Theme::get_active_theme_name() );
		$theme->load();

		// If the theme has been customized, don't nag the user.
		if ( ! $theme->is_default() ) {
			return;
		}

		// Nag the user.
		\Admin_Apple_Notice::info(
			sprintf(
				/* translators: First parameter is opening a tag, second is closing a tag */
				__( 'It looks like you are using the default theme. You can choose a new theme or customize your theme on the %1$sthemes page%2$s.', 'apple-news' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=apple-news-themes' ) ) . '">',
				'</a>'
			)
		);
	}

	/**
	 * Handles deleting a theme.
	 *
	 * @access private
	 */
	private function delete_theme() {

		// Check the nonce.
		$action = isset( $_REQUEST['action'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) )
			: null;
		check_admin_referer( $this->valid_actions[ $action ]['nonce'] );

		// Attempt to get the name of the theme from postdata.
		if ( empty( $name ) && ! empty( $_POST['apple_news_theme'] ) ) {
			$name = sanitize_text_field( wp_unslash( $_POST['apple_news_theme'] ) );
		}

		// Ensure a name was provided.
		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to delete the theme because no name was provided', 'apple-news' )
			);
			return;
		}

		// Remove the theme.
		$theme = new \Apple_Exporter\Theme();
		$theme->set_name( $name );
		$theme->delete();

		// Indicate success.
		\Admin_Apple_Notice::success(
			sprintf(
				// translators: token is a theme name.
				__( 'Successfully deleted theme %s', 'apple-news' ),
				$name
			)
		);
	}

	/**
	 * Handles exporting a new theme to a JSON file.
	 *
	 * @access private
	 */
	private function export_theme() {

		// Check the nonce.
		$action = isset( $_REQUEST['action'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) )
			: null;
		check_admin_referer( $this->valid_actions[ $action ]['nonce'] );

		// Get the theme name from POST data.
		if ( ! empty( $_POST['apple_news_theme'] ) ) {
			$name = sanitize_text_field( wp_unslash( $_POST['apple_news_theme'] ) );
		}

		// Ensure we got a theme name.
		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__(
					'Unable to export the theme because no name was provided',
					'apple-news'
				)
			);
			return;
		}

		// Try to load the theme.
		$theme = new \Apple_Exporter\Theme();
		$theme->set_name( $name );
		if ( ! $theme->load() ) {
			\Admin_Apple_Notice::error(
				sprintf(
					// translators: token is a theme name.
					__( 'The theme %s could not be found', 'apple-news' ),
					$name
				)
			);
			return;
		}

		// Get the settings from the theme.
		$settings = $theme->all_settings();

		// Add the theme name.
		$settings['theme_name'] = $name;

		// Generate the filename.
		$filename = \Apple_Exporter\Theme::theme_key( $name ) . '.json';

		// Negotiate whether to pretty print the JSON.
		$pretty_print = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;

		// Stream the download to the user.
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
		echo wp_json_encode( $settings, $pretty_print );

		exit;
	}

	/**
	 * Handle saving theme settings from the edit form.
	 *
	 * @access private
	 */
	private function save_edit_theme() {

		// Check the nonce.
		$action = isset( $_REQUEST['action'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) )
			: null;
		check_admin_referer( $this->valid_actions[ $action ]['nonce'] );

		// Create a theme object.
		$theme = new \Apple_Exporter\Theme();

		// Get the theme name.
		if ( ! isset( $_POST['apple_news_theme_name'] ) ) {
			\Admin_Apple_Notice::error(
				__( 'No theme name was set', 'apple-news' )
			);

			return;
		}

		// Ensure the theme name is valid.
		$name = sanitize_text_field( wp_unslash( $_POST['apple_news_theme_name'] ) );
		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'The theme name was empty', 'apple-news' )
			);

			return;
		}

		// Negotiate previous theme name.
		$previous_name = ( ! empty( $_POST['apple_news_theme_name_previous'] ) )
			? sanitize_text_field( wp_unslash( $_POST['apple_news_theme_name_previous'] ) )
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
			\Admin_Apple_Notice::error(
				sprintf(
					// translators: token is a theme name.
					__( 'Theme name %s is already in use.', 'apple-news' ),
					$name
				)
			);

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
			\Admin_Apple_Notice::error(
				sprintf(
					// translators: first token is a theme name, second is an error message.
					__( 'Could not save theme %1$s: %2$s', 'apple-news' ),
					$name,
					$theme->get_last_error()
				)
			);

			return;
		}

		// Process rename, if requested.
		if ( 'rename' === $action ) {
			$theme->rename( $name );
		}

		// Indicate success.
		\Admin_Apple_Notice::success(
			sprintf(
				// translators: token is a theme name.
				__( 'The theme %s was saved successfully', 'apple-news' ),
				$name
			)
		);
	}

	/**
	 * Handles setting the active theme.
	 *
	 * @access private
	 */
	private function set_theme() {

		// Check the nonce.
		$action = isset( $_REQUEST['action'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) )
			: null;
		check_admin_referer( $this->valid_actions[ $action ]['nonce'] );

		// Get the theme name from postdata.
		if ( ! empty( $_POST['apple_news_active_theme'] ) ) {
			$name = sanitize_text_field( wp_unslash( $_POST['apple_news_active_theme'] ) );
		}

		// Ensure we have a theme name.
		if ( empty( $name ) ) {
			\Admin_Apple_Notice::error(
				__( 'Unable to set the theme because no name was provided', 'apple-news' )
			);

			return;
		}

		// Set the theme as active.
		$theme = new \Apple_Exporter\Theme();
		$theme->set_name( $name );
		$theme->set_active();

		// Indicate success.
		\Admin_Apple_Notice::success(
			sprintf(
				// translators: token is a theme name.
				__( 'Successfully switched to theme %s', 'apple-news' ),
				$name
			)
		);
	}

	/**
	 * Handles uploading a new theme from a JSON file.
	 *
	 * @access private
	 */
	private function upload_theme() {

		// Try to handle the file upload.
		$file = wp_import_handle_upload();
		if ( isset( $file['error'] ) ) {
			\Admin_Apple_Notice::error(
				__( 'There was an error uploading the theme file', 'apple-news' )
			);
			return;
		}

		// Ensure the filepath and ID are set.
		if ( ! isset( $file['file'], $file['id'] ) ) {
			\Admin_Apple_Notice::error(
				__( 'The file did not upload properly. Please try again.', 'apple-news' )
			);
			return;
		}

		// Ensure the file exists at the given path.
		$this->file_id = absint( $file['id'] );
		if ( ! file_exists( $file['file'] ) ) {
			wp_import_cleanup( $this->file_id );
			\Admin_Apple_Notice::error(
				sprintf(
					// translators: token is a filepath wrapped in <code>.
					__( 'The export file could not be found at %s. It is likely that this was caused by a permissions problem.', 'apple-news' ),
					'<code>' . esc_html( $file['file'] ) . '</code>'
				)
			);
			return;
		}

		// Ensure the given path is a filepath.
		if ( ! is_file( $file['file'] ) ) {
			wp_import_cleanup( $this->file_id );
			\Admin_Apple_Notice::error(
				__( 'The path is not a file, please try again.', 'apple-news' )
			);
			return;
		}

		// Get the contents of the file and clean up.
		$file_contents = file_get_contents( $file['file'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$import_data   = json_decode( $file_contents, true );
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

		// Indicate success.
		\Admin_Apple_Notice::success(
			sprintf(
				// translators: token is the theme name.
				__( 'Successfully uploaded theme %s', 'apple-news' ),
				$name
			)
		);
	}
}
