<?php
/**
 * Publish to Apple News Admin Screens: Admin_Apple_Sections class
 *
 * Contains a class which is used to manage the Sections admin settings page.
 *
 * @package Apple_News
 * @since 1.2.2
 */

/**
 * This class is in charge of handling the management of Apple News sections.
 *
 * @since 1.2.2
 */
class Admin_Apple_Sections extends Apple_News {

	/**
	 * Section management page name.
	 *
	 * @var string
	 * @access private
	 */
	private $page_name;

	/**
	 * Constructor.
	 */
	function __construct() {
		$this->page_name = $this->plugin_domain . '-sections';
		add_action( 'admin_menu', array( $this, 'setup_section_page' ), 99 );
	}

	/**
	 * Options page setup.
	 *
	 * @access public
	 */
	public function setup_section_page() {
		add_submenu_page(
			'apple_news_index',
			__( 'Apple News Sections', 'apple-news' ),
			__( 'Sections', 'apple-news' ),
			apply_filters( 'apple_news_settings_capability', 'manage_options' ),
			$this->page_name,
			array( $this, 'page_sections_render' )
		);
	}

	/**
	 * Options page render.
	 *
	 * @access public
	 */
	public function page_sections_render() {
		if ( ! current_user_can( apply_filters( 'apple_news_settings_capability', 'manage_options' ) ) ) {
			wp_die( __( 'You do not have permissions to access this page.', 'apple-news' ) );
		}

		include plugin_dir_path( __FILE__ ) . 'partials/page_sections.php';
	}

	/**
	 * Register assets for the options page.
	 *
	 * @param string $hook
	 * @access public
	 */
	public function register_assets( $hook ) {
		if ( 'apple-news_page_apple-news-sections' != $hook ) {
			return;
		}

		wp_enqueue_style( 'apple-news-sections-css', plugin_dir_url( __FILE__ ) .
			'../assets/css/sections.css', array() );

		wp_enqueue_script( 'apple-news-sections-js', plugin_dir_url( __FILE__ ) .
			'../assets/js/sections.js', array( 'jquery' )
		);

		wp_localize_script( 'apple-news-sections-js', 'appleNewsSections', array(
			'key1' => __( 'Message 1', 'apple-news' ),
		) );
	}
}
