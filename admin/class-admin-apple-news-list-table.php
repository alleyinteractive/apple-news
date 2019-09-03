<?php
/**
 * Publish to Apple News: Admin_Apple_News_List_Table class
 *
 * @package Apple_News
 */

// Include WP_List_Table, if it hasn't been already.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Use WordPress List_Table class to create a custom table displaying posts
 * information and actions.
 *
 * @since 0.4.0
 */
class Admin_Apple_News_List_Table extends WP_List_Table {

	/**
	 * How many entries per page will be displayed.
	 *
	 * @var int
	 * @since 0.4.0
	 */
	public $per_page = 20;

	/**
	 * Current settings.
	 *
	 * @var \Apple_Exporter\Settings
	 * @since 0.9.0
	 */
	public $settings;

	/**
	 * Constructor.
	 *
	 * @param \Apple_Exporter\Settings $settings Settings in use during this run.
	 * @access public
	 */
	public function __construct( $settings ) {
		// Load current settings.
		$this->settings = $settings;

		// Initialize the table.
		parent::__construct(
			array(
				'singular' => __( 'article', 'apple-news' ),
				'plural'   => __( 'articles', 'apple-news' ),
				'ajax'     => false,
			)
		);
	}

	/**
	 * Set column defaults.
	 *
	 * @param mixed  $item        Default value for the column.
	 * @param string $column_name The name of the column.
	 * @access public
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
				$default = $item[ $column_name ];
				break;
			case 'updated_at':
				$default = $this->get_updated_at( $item );
				break;
			case 'status':
				$default = $this->get_status_for( $item );
				break;
			case 'sync':
				$default = $this->get_synced_status_for( $item );
				break;
		}

		return apply_filters( 'apple_news_column_default', $default, $column_name, $item );
	}

	/**
	 * Get the updated at time.
	 *
	 * @param \WP_Post $post The post to analyze.
	 * @access private
	 * @return string
	 */
	private function get_updated_at( $post ) {
		$updated_at = get_post_meta( $post->ID, 'apple_news_api_modified_at', true );

		if ( $updated_at ) {
			return get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $updated_at ) ), 'F j, h:i a' );
		}

		return __( 'Never', 'apple-news' );
	}

	/**
	 * Get the Apple News status.
	 *
	 * @param \WP_Post $post The post to analyze.
	 * @access private
	 * @return string
	 */
	private function get_status_for( $post ) {
		return \Admin_Apple_News::get_post_status( $post->ID );
	}

	/**
	 * Get the synced status.
	 *
	 * @param \WP_Post $post The post to analyze.
	 * @access private
	 * @return string
	 */
	private function get_synced_status_for( $post ) {
		$remote_id = get_post_meta( $post->ID, 'apple_news_api_id', true );

		if ( ! $remote_id ) {
			// There is no remote id, check for a delete mark.
			$deleted = get_post_meta( $post->ID, 'apple_news_api_deleted', true );
			if ( $deleted ) {
				return __( 'Deleted', 'apple-news' );
			}

			$pending = get_post_meta( $post->ID, 'apple_news_api_pending', true );
			if ( $pending ) {
				return __( 'Pending', 'apple-news' );
			}

			// No delete mark, this has not been published yet.
			return __( 'Not published', 'apple-news' );
		}

		$updated = get_post_meta( $post->ID, 'apple_news_api_modified_at', true );
		$updated = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $updated ) ) ) );
		$local   = strtotime( $post->post_modified );

		if ( $local > $updated ) {
			return __( 'Needs to be updated', 'apple-news' );
		}

		return __( 'Published', 'apple-news' );
	}

	/**
	 * This method is responsible for what is rendered in any column with a
	 * name/slug of 'title'.
	 *
	 * Every time the class needs to render a column, it first looks for a method
	 * named column_{$column_title}, if it exists, that method is run, otherwise,
	 * column_default() is called.
	 *
	 * Actions can be generated here.
	 *
	 * @param \WP_Post $item The post to analyze.
	 * @access public
	 * @return string
	 */
	public function column_title( $item ) {
		$current_screen = get_current_screen();
		if ( empty( $current_screen->parent_base ) ) {
			return;
		}

		// Build the base URL.
		$base_url = add_query_arg(
			array(
				'page'    => $current_screen->parent_base,
				'post_id' => $item->ID,
			),
			get_admin_url( null, 'admin.php' )
		);

		// Add common actions.
		$actions = array(
			'export' => sprintf(
				"<a href='%s'>%s</a>",
				esc_url( Admin_Apple_Index_Page::action_query_params( 'export', $base_url ) ),
				esc_html__( 'Download', 'apple-news' )
			),
		);

		// Only add push if the article is not pending publish.
		$pending = get_post_meta( $item->ID, 'apple_news_api_pending', true );
		if ( empty( $pending ) ) {
			$actions['push'] = sprintf(
				"<a href='%s'>%s</a>",
				esc_url( Admin_Apple_Index_Page::action_query_params( 'push', $base_url ) ),
				esc_html__( 'Publish', 'apple-news' )
			);
		}

		// If the article is pending, add a reset action in case it's stuck.
		if ( ! empty( $pending ) ) {
			$actions['reset'] = sprintf(
				"<a href='%s' class='reset-button'>%s</a>",
				esc_url( Admin_Apple_Index_Page::action_query_params( 'reset', $base_url ) ),
				esc_html__( 'Reset', 'apple-news' )
			);
		}

		// Add the delete action, if required.
		if ( get_post_meta( $item->ID, 'apple_news_api_id', true ) ) {
			$actions['delete'] = sprintf(
				"<a title='%s' href='%s' class='delete-button'>%s</a>",
				esc_html__( 'Delete from Apple News', 'apple-news' ),
				esc_url( Admin_Apple_Index_Page::action_query_params( 'delete', $base_url ) ),
				esc_html__( 'Delete', 'apple-news' )
			);
		}

		// Create the share URL.
		$share_url = get_post_meta( $item->ID, 'apple_news_api_share_url', true );
		if ( $share_url ) {
			$actions['share'] = sprintf(
				"<a class='share-url-button' title='%s' href='#'>%s</a><br/><input type='text' name='share-url-%s' class='apple-share-url' value='%s' />",
				esc_html__( 'Preview in News app', 'apple-news' ),
				esc_html__( 'Copy News URL', 'apple-news' ),
				absint( $item->ID ),
				esc_url( $share_url )
			);
		}

		// Return the row action HTML.
		return apply_filters(
			'apple_news_column_title',
			sprintf(
				'%1$s <span>(id:%2$s)</span> %3$s',
				esc_html( $item->post_title ),
				absint( $item->ID ),
				$this->row_actions( $actions ) // Can't be escaped but all elements are fully escaped above.
			),
			$item,
			$actions
		);
	}

	/**
	 * Dictates the table columns and titles. The 'cb' column is special and, if
	 * existent, there needs to be a `column_cb` method defined.
	 *
	 * @access public
	 * @return array An array where the key is the column slug and the value is the title text.
	 */
	public function get_columns() {
		return apply_filters(
			'apple_news_export_list_columns',
			array(
				'cb'         => '<input type="checkbox">',
				'title'      => __( 'Title', 'apple-news' ),
				'updated_at' => __( 'Last updated at', 'apple-news' ),
				'status'     => __( 'Apple News Status', 'apple-news' ),
				'sync'       => __( 'Sync Status', 'apple-news' ),
			)
		);
	}

	/**
	 * Required IF using checkboxes or bulk actions. The 'cb' column gets special
	 * treatment when columns are processed. It ALWAYS needs to have it's own
	 * method.
	 *
	 * @param \WP_Post $item The post to analyze.
	 * @access public
	 * @return string
	 */
	public function column_cb( $item ) {
		// Omit if the article is pending publish.
		$pending = get_post_meta( $item->ID, 'apple_news_api_pending', true );
		if ( ! empty( $pending ) ) {
			return '';
		}

		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s">',
			esc_attr( $this->_args['singular'] ),
			absint( $item->ID )
		);
	}

	/**
	 * Get bulk actions.
	 *
	 * @access public
	 * @return array
	 */
	public function get_bulk_actions() {
		return apply_filters(
			'apple_news_bulk_actions',
			array(
				Admin_Apple_Index_Page::namespace_action( 'push' ) => __( 'Publish', 'apple-news' ),
			)
		);
	}

	/**
	 * Prepare items for the table.
	 *
	 * @access public
	 */
	public function prepare_items() {
		/**
		 * Set column headers. It expects an array of columns, and as second
		 * argument an array of hidden columns, which in this case is empty.
		 */
		$columns               = $this->get_columns();
		$this->_column_headers = array( $columns, array(), array() );

		// Build the default args for the query.
		$current_page = $this->get_pagenum();
		$args         = array(
			'post_type'      => $this->settings->get( 'post_types' ),
			'post_status'    => 'publish',
			'posts_per_page' => $this->per_page,
			'offset'         => ( $current_page - 1 ) * $this->per_page,
			'orderby'        => 'ID',
			'order'          => 'DESC',
		);

		// Add the publish status filter if set.
		$publish_status = $this->get_publish_status_filter();
		if ( ! empty( $publish_status ) ) {
			switch ( $publish_status ) {
				case 'published':
					$args['meta_query'] = array(
						array(
							'key'     => 'apple_news_api_id',
							'compare' => '!=',
							'value'   => '',
						),
					);
					break;
				case 'not_published':
					$args['meta_query'] = array(
						'relation' => 'AND',
						array(
							'relation' => 'OR',
							array(
								'key'     => 'apple_news_api_id',
								'compare' => 'NOT EXISTS',
							),
							array(
								'key'     => 'apple_news_api_id',
								'compare' => '=',
								'value'   => '',
							),
						),
						array(
							'key'     => 'apple_news_api_deleted',
							'compare' => 'NOT EXISTS',
						),
					);
					break;
				case 'deleted':
					$args['meta_query'] = array(
						array(
							'key'     => 'apple_news_api_deleted',
							'compare' => 'EXISTS',
						),
					);
					break;
				case 'pending':
					$args['meta_query'] = array(
						array(
							'key'     => 'apple_news_api_pending',
							'compare' => 'EXISTS',
						),
					);
					break;
			}
		}

		// Add the date filters if set.
		$date_from = $this->get_date_from_filter();
		$date_to   = $this->get_date_to_filter();
		if ( ! empty( $date_from ) || ! empty( $date_to ) ) {
			$args['date_query'] = array(
				array(
					'inclusive' => true,
				),
			);

			if ( ! empty( $date_from ) ) {
				$args['date_query'][0]['after'] = $date_from;
			}

			if ( ! empty( $date_to ) ) {
				$args['date_query'][0]['before'] = $date_to;
			}
		}

		// Add the search filter if set.
		$search = $this->get_search_filter();
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		// Data fetch.
		$query = new WP_Query( apply_filters( 'apple_news_export_table_get_posts_args', $args ) );

		// Set data.
		$this->items = $query->posts;
		$total_items = $query->found_posts;
		$this->set_pagination_args(
			apply_filters(
				'apple_news_export_table_pagination_args',
				array(
					'total_items' => $total_items,
					'per_page'    => $this->per_page,
					'total_pages' => ceil( $total_items / $this->per_page ),
				)
			)
		);
	}

	/**
	 * Display extra filtering options.
	 *
	 * @param string $which Which section of the table we are on.
	 * @access protected
	 */
	protected function extra_tablenav( $which ) {
		// Only display on the top of the table.
		if ( 'top' !== $which ) {
			return;
		}
		?>
		<div class="alignleft actions">
		<?php
		// Add a publish state filter.
		$this->publish_status_filter_field();

		// Add a dange range filter.
		$this->date_range_filter_field();

		// Allow for further options to be added within themes and plugins.
		do_action( 'apple_news_extra_tablenav' );

		submit_button( __( 'Filter', 'apple-news' ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
		?>
		</div>
		<?php
	}

	/**
	 * Get the current publish status filter value.
	 *
	 * @return string
	 * @access protected
	 */
	protected function get_publish_status_filter() {
		return ( ! empty( $_GET['apple_news_publish_status'] ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
			? sanitize_text_field( wp_unslash( $_GET['apple_news_publish_status'] ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
			: '';
	}

	/**
	 * Get the current date from filter value.
	 *
	 * @return string
	 * @access protected
	 */
	protected function get_date_from_filter() {
		return ( ! empty( $_GET['apple_news_date_from'] ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
			? sanitize_text_field( wp_unslash( $_GET['apple_news_date_from'] ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
			: '';
	}

	/**
	 * Get the current date to filter value.
	 *
	 * @return string
	 * @access protected
	 */
	protected function get_date_to_filter() {
		return ( ! empty( $_GET['apple_news_date_to'] ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
			? sanitize_text_field( wp_unslash( $_GET['apple_news_date_to'] ) ) // phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.Security.NonceVerification.Recommended
			: '';
	}

	/**
	 * Get the current search filter value.
	 *
	 * @return string
	 * @access protected
	 */
	protected function get_search_filter() {
		return ( ! empty( $_GET['s'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			? sanitize_text_field( wp_unslash( $_GET['s'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			: '';
	}

	/**
	 * Display a dropdown to filter by publish state.
	 *
	 * @access protected
	 */
	protected function publish_status_filter_field() {
		// Add available statuses.
		$publish_statuses = apply_filters(
			'apple_news_publish_statuses',
			array(
				''              => __( 'Show All Statuses', 'apple-news' ),
				'published'     => __( 'Published', 'apple-news' ),
				'not_published' => __( 'Not Published', 'apple-news' ),
				'pending'       => __( 'Pending', 'apple-news' ),
				'deleted'       => __( 'Deleted', 'apple-news' ),
			)
		);

		// Build the dropdown.
		?>
		<select name="apple_news_publish_status" id="apple_news_publish_status">
		<?php
		foreach ( $publish_statuses as $value => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $value ),
				selected( $value, $this->get_publish_status_filter(), false ),
				esc_html( $label )
			);
		}
		?>
		</select>
		<?php
	}

	/**
	 * Display datepickers to filter by date range
	 *
	 * @access protected
	 */
	protected function date_range_filter_field() {
		?>
		<input type="text" placeholder="<?php esc_attr_e( 'Show Posts From', 'apple-news' ); ?>" name="apple_news_date_from" id="apple_news_date_from" value="<?php echo esc_attr( $this->get_date_from_filter() ); ?>" />
		<input type="text" placeholder="<?php esc_attr_e( 'Show Posts To', 'apple-news' ); ?>" name="apple_news_date_to" id="apple_news_date_to" value="<?php echo esc_attr( $this->get_date_to_filter() ); ?>" />
		<?php
	}
}
