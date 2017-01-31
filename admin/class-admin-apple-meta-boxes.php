<?php

/**
 * This class provides a meta box to publish posts to Apple News from the edit screen.
 *
 * @since 0.9.0
 */
class Admin_Apple_Meta_Boxes extends Apple_News {

	/**
	 * Current settings.
	 *
	 * @since 0.9.0
	 * @var array
	 * @access private
	 */
	private $settings;

	/**
	 * Publish action.
	 *
	 * @since 0.9.0
	 * @var array
	 * @access private
	 */
	private $publish_action = 'apple_news_publish';

	/**
	 * Constructor.
	 */
	function __construct( $settings = null ) {
		$this->settings = $settings;

		// Register hooks if enabled
		if ( 'yes' == $settings->get( 'show_metabox' ) ) {
			// Handle a publish action and saving fields
			add_action( 'save_post', array( $this, 'do_publish' ), 10, 2 );

			// Add the custom meta boxes to each post type
			$post_types = $settings->get( 'post_types' );
			if ( ! is_array( $post_types ) ) {
				$post_types = array( $post_types );
			}

			foreach ( $post_types as $post_type ) {
				add_action( 'add_meta_boxes_' . $post_type, array( $this, 'add_meta_boxes' ) );
			}

			// Register assets used by the meta box
			add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		}
	}

	/**
	 * Check for a publish action from the meta box.
	 *
	 * @since 0.9.0
	 * @param int $post_id
	 * @param WP_Post $post
	 * @access public
	 */
	public function do_publish( $post_id, $post ) {
		// Check if the values we want are present in $_REQUEST params.
		if ( empty( $_POST['apple_news_nonce'] )
			|| empty( $_POST['post_ID'] ) ) {
			return;
		}

		// Check the nonce
		if ( ! wp_verify_nonce( $_POST['apple_news_nonce'], $this->publish_action ) ) {
			return;
		}

		// Save meta box fields
		$post_id = absint( $_POST['post_ID'] );
		self::save_post_meta( $post_id );

		// If this is set to autosync or no action is set, we're done here
		if ( 'yes' == $this->settings->get( 'api_autosync' )
			|| 'publish' != $post->post_status
			|| empty( $_POST['apple_news_publish_action'] )
			|| $this->publish_action != $_POST['apple_news_publish_action'] ) {
			return;
		}

		// Proceed with the push
		$action = new Apple_Actions\Index\Push( $this->settings, $post_id );
		try {
			$action->perform();

			// In async mode, success or failure will be displayed later
			if ( 'yes' !== $this->settings->get( 'api_async' ) ) {
				Admin_Apple_Notice::success( __( 'Your article has been pushed successfully to Apple News!', 'apple-news' ) );
			} else {
				Admin_Apple_Notice::success( __( 'Your article will be pushed shortly to Apple News.', 'apple-news' ) );
			}
		} catch ( Apple_Actions\Action_Exception $e ) {
			Admin_Apple_Notice::error( $e->getMessage() );
		}
	}

	/**
	 * Saves the Apple News meta fields associated with a post
	 *
	 * @param int $post_id
	 * @access public
	 * @static
	 */
	public static function save_post_meta( $post_id ) {

		// Determine whether to save sections.
		if ( empty( $_POST['apple_news_sections_by_taxonomy'] ) ) {
			$sections = array();
			if ( ! empty( $_POST['apple_news_sections'] )
				&& is_array( $_POST['apple_news_sections'] )
			) {
				$sections = array_map(
					'sanitize_text_field',
					$_POST['apple_news_sections']
				);
			}
			update_post_meta( $post_id, 'apple_news_sections', $sections );
		} else {
			delete_post_meta( $post_id, 'apple_news_sections' );
		}

		if ( ! empty( $_POST['apple_news_is_preview'] ) && 1 === intval( $_POST['apple_news_is_preview'] ) ) {
			$is_preview = true;
		} else {
			$is_preview = false;
		}
		update_post_meta( $post_id, 'apple_news_is_preview', $is_preview );

		if ( ! empty( $_POST['apple_news_is_sponsored'] ) && 1 === intval( $_POST['apple_news_is_sponsored'] ) ) {
			$is_sponsored = true;
		} else {
			$is_sponsored = false;
		}
		update_post_meta( $post_id, 'apple_news_is_sponsored', $is_sponsored );

		if ( ! empty( $_POST['apple_news_pullquote'] ) ) {
			$pullquote = sanitize_text_field( $_POST['apple_news_pullquote'] );
		} else {
			$pullquote = '';
		}
		update_post_meta( $post_id, 'apple_news_pullquote', $pullquote );

		if ( ! empty( $_POST['apple_news_pullquote_position'] ) ) {
			$pullquote_position = sanitize_text_field( $_POST['apple_news_pullquote_position'] );
		} else {
			$pullquote_position = 'middle';
		}
		update_post_meta( $post_id, 'apple_news_pullquote_position', $pullquote_position );
	}

	/**
	 * Add the Apple News meta boxes
	 *
	 * @since 0.9.0
	 * @param WP_Post $post
	 * @access public
	 */
	public function add_meta_boxes( $post ) {
		// Add the publish meta box
		add_meta_box(
			'apple_news_publish',
			__( 'Apple News', 'apple-news' ),
			array( $this, 'publish_meta_box' ),
			$post->post_type,
			apply_filters( 'apple_news_publish_meta_box_context', 'side' ),
			apply_filters( 'apple_news_publish_meta_box_priority', 'high' )
		);
	}

	/**
	 * Add the Apple News publish meta box
	 *
	 * @since 0.9.0
	 * @param WP_Post $post
	 * @access public
	 */
	public function publish_meta_box( $post ) {
		// Only show the publish feature if the user is authorized and auto sync is not enabled.
		// Also check if the post has been previously published and/or deleted.
		$api_id = get_post_meta( $post->ID, 'apple_news_api_id', true );
		$deleted = get_post_meta( $post->ID, 'apple_news_api_deleted', true );
		$pending = get_post_meta( $post->ID, 'apple_news_api_pending', true );
		$is_preview = get_post_meta( $post->ID, 'apple_news_is_preview', true );
		$is_sponsored = get_post_meta( $post->ID, 'apple_news_is_sponsored', true );
		$pullquote = get_post_meta( $post->ID, 'apple_news_pullquote', true );
		$pullquote_position = get_post_meta( $post->ID, 'apple_news_pullquote_position', true );

		// Set the default value
		if ( empty( $pullquote_position ) ) {
			$pullquote_position = 'middle';
		}
		?>
		<div id="apple-news-publish">
		<?php wp_nonce_field( $this->publish_action, 'apple_news_nonce' ); ?>
		<?php self::build_sections_override( $post->ID ); ?>
		<div class="apple-news-sections">
			<?php
				$section = new Apple_Actions\Index\Section( $this->settings );
				try {
					$sections = $section->get_sections();
				} catch ( Apple_Actions\Action_Exception $e ) {
					Admin_Apple_Notice::error( $e->getMessage() );
				}

				if ( ! empty( $sections ) ) :
					?>
					<h3><?php esc_html_e( 'Sections', 'apple-news' ) ?></h3>
					<?php
					self::build_sections_field( $sections, $post->ID );
				endif;
			?>
			<p class="description"><?php esc_html_e( 'Select the sections in which to publish this article. Uncheck them all for a standalone article.' , 'apple-news' ) ?></p>
		</div>
		<h3><?php esc_html_e( 'Preview?', 'apple-news' ) ?></h3>
		<input id="apple-news-is-preview" name="apple_news_is_preview" type="checkbox" value="1" <?php checked( $is_preview ) ?>>
		<p class="description"><?php esc_html_e( 'Check this to publish the article as a draft.' , 'apple-news' ) ?></p>
		<h3><?php esc_html_e( 'Sponsored?', 'apple-news' ) ?></h3>
		<input id="apple-news-is-sponsored" name="apple_news_is_sponsored" type="checkbox" value="1" <?php checked( $is_sponsored ) ?>>
		<p class="description"><?php esc_html_e( 'Check this to indicate this article is sponsored content.' , 'apple-news' ) ?></p>
		<h3><?php esc_html_e( 'Pull quote', 'apple-news' ) ?></h3>
		<textarea name="apple_news_pullquote" placeholder="<?php esc_attr_e( 'A pull quote is a key phrase, quotation, or excerpt that has been pulled from an article and used as a graphic element, serving to entice readers into the article or to highlight a key topic.', 'apple-news' ) ?>" rows="6" class="large-text"><?php echo esc_textarea( $pullquote ) ?></textarea>
		<p class="description"><?php esc_html_e( 'This is optional and can be left blank.', 'apple-news' ) ?></p>
		<h3><?php esc_html_e( 'Pull quote position', 'apple-news' ) ?></h3>
		<select name="apple_news_pullquote_position">
			<option <?php selected( $pullquote_position, 'top' ) ?> value="top"><?php esc_html_e( 'top', 'apple-news' ) ?></option>
			<option <?php selected( $pullquote_position, 'middle' ) ?> value="middle"><?php esc_html_e( 'middle', 'apple-news' ) ?></option>
			<option <?php selected( $pullquote_position, 'bottom' ) ?> value="bottom"><?php esc_html_e( 'bottom', 'apple-news' ) ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'The position in the article where the pull quote will appear.', 'apple-news' ) ?></p>
		<?php
		if ( 'yes' != $this->settings->get( 'api_autosync' )
			&& current_user_can( apply_filters( 'apple_news_publish_capability', 'manage_options' ) )
			&& 'publish' === $post->post_status
			&& empty( $api_id )
			&& empty( $deleted )
			&& empty( $pending ) ):
		?>
		<input type="hidden" id="apple-news-publish-action" name="apple_news_publish_action" value="">
		<input type="button" id="apple-news-publish-submit" name="apple_news_publish_submit" value="<?php esc_attr_e( 'Publish to Apple News', 'apple-news' ) ?>" class="button-primary" />
		<?php
		elseif ( 'yes' == $this->settings->get( 'api_autosync' )
			&& empty( $api_id )
			&& empty( $deleted )
			&& empty( $pending ) ):
		?>
		<p><?php esc_html_e( 'This post will be automatically sent to Apple News on publish.', 'apple-news' ); ?></p>
		<?php
		elseif ( 'yes' == $this->settings->get( 'api_async' )
			&& ! empty( $pending ) ):
		?>
		<p><?php esc_html_e( 'This post is currently pending publishing to Apple News.', 'apple-news' ); ?></p>
		<?php
		endif;

		// Add data about the article if it exists
		if ( ! empty( $deleted ) ) :
			?>
			<p><b><?php esc_html_e( 'This post has been deleted from Apple News', 'apple-news' ) ?></b></p>
			<?php
		endif;

		if ( ! empty( $api_id ) ) :
			$state = \Admin_Apple_News::get_post_status( $post->ID );

			$share_url = get_post_meta( $post->ID, 'apple_news_api_share_url', true );
			$created_at = get_post_meta( $post->ID, 'apple_news_api_created_at', true );
			$created_at = empty( $created_at ) ? __( 'None', 'apple-news' ) : get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $created_at ) ), 'F j, h:i a' );
			$modified_at = get_post_meta( $post->ID, 'apple_news_api_modified_at', true );
			$modified_at = empty( $modified_at ) ? __( 'None', 'apple-news' ) : get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $modified_at ) ), 'F j, h:i a' );
			?>
			<p><b><?php esc_html_e( 'Apple News Publish Information', 'apple-news' ) ?></b>
			<br/><?php esc_html_e( 'ID', 'apple-news' ) ?>: <?php echo esc_html( $api_id ) ?>
			<br/><?php esc_html_e( 'Created at', 'apple-news' ) ?>: <?php echo esc_html( $created_at ) ?>
			<br/><?php esc_html_e( 'Modified at', 'apple-news' ) ?>: <?php echo esc_html( $modified_at ) ?>
			<br/><?php esc_html_e( 'Share URL', 'apple-news' ) ?>: <a href="<?php echo esc_url( $share_url ) ?>" target="_blank"><?php echo esc_html( $share_url ) ?></a>
			<br/><?php esc_html_e( 'Revision', 'apple-news' ) ?>: <?php echo esc_html( get_post_meta( $post->ID, 'apple_news_api_revision', true ) ) ?>
			<br/><?php esc_html_e( 'State', 'apple-news' ) ?>: <?php echo esc_html( $state ) ?>
			<?php
		endif;
		?>
		</div>
		<?php
	}

	/**
	 * Builds the sections dropdown
	 *
	 * @param array $sections
	 * @param int $post_id
	 * @access public
	 * @static
	 */
	public static function build_sections_field( $sections, $post_id ) {

		// Make sure we have sections.
		if ( empty( $sections ) ) {
			return '';
		}

		// Iterate over the list of sections and print each.
		$apple_news_sections = get_post_meta( $post_id, 'apple_news_sections', true );
		foreach ( $sections as $section ) {
			?>
			<div class="section">
				<input id="apple-news-section-<?php echo esc_attr( $section->id ) ?>" name="apple_news_sections[]" type="checkbox" value="<?php echo esc_attr( $section->links->self ) ?>" <?php checked( self::section_is_checked( $apple_news_sections, $section->links->self, $section->isDefault ) ) ?>>
				<label for="apple-news-section-<?php echo esc_attr( $section->id ) ?>"><?php echo esc_html( $section->name ) ?></label>
			</div>
			<?php
		}
	}

	/**
	 * Builds the sections override checkbox, if necessary.
	 *
	 * @param int $post_id The ID of the post to build the checkbox field for.
	 *
	 * @access public
	 */
	public static function build_sections_override( $post_id ) {

		// Determine if there are section/taxonomy mappings set.
		$mappings = get_option( Admin_Apple_Sections::TAXONOMY_MAPPING_KEY );
		if ( empty( $mappings ) ) {
			return;
		}

		// Add checkbox to allow override of automatic section assignment.
		$mapping_taxonomy = Admin_Apple_Sections::get_mapping_taxonomy();
		$sections = get_post_meta( $post_id, 'apple_news_sections', true );
		?>
		<div class="section-override">
			<label for="apple-news-sections-by-taxonomy">
			<input id="apple-news-sections-by-taxonomy" name="apple_news_sections_by_taxonomy" type="checkbox" <?php checked( ! is_array( $sections ) ); ?> />
				<?php esc_html_e( 'Assign sections by', 'apple-news' ); ?>
				<?php echo esc_html( strtolower( $mapping_taxonomy->labels->singular_name ) ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Determine if a section is checked
	 *
	 * @param array $sections
	 * @param int $section_id
	 * @param int $is_default
	 * @access public
	 * @static
	 */
	public static function section_is_checked( $sections, $section_id, $is_default ) {
		// If no sections exist, return true if this is the default.
		// If sections is an empty array, this is intentional though and nothing should be checked.
		// If sections are provided, then only use those for matching.
		if ( ( empty( $sections ) && ! is_array( $sections ) && 1 == $is_default )
			|| ( ! empty( $sections ) && is_array( $sections ) && in_array( $section_id, $sections ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Registers assets used by meta boxes.
	 *
	 * @param string $hook
	 * @access public
	 */
	public function register_assets( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		wp_enqueue_style( $this->plugin_slug . '_meta_boxes_css', plugin_dir_url(
			__FILE__ ) .  '../assets/css/meta-boxes.css' );

		wp_enqueue_script( $this->plugin_slug . '_meta_boxes_js', plugin_dir_url(
			__FILE__ ) .  '../assets/js/meta-boxes.js', array( 'jquery' ),
			self::$version, true );

		// Localize the JS file for handling frontend actions.
		wp_localize_script( $this->plugin_slug . '_meta_boxes_js', 'apple_news_meta_boxes', array(
			'publish_action' => $this->publish_action,
		) );

	}

}
