<?php
/**
 * Publish to Apple News CLI: Apple_News_CLI_Command class
 *
 * Contains a class which is used to register and execute WP-CLI commands.
 *
 * @package    Apple_News
 * @subpackage CLI
 * @since      1.3.1
 */

/**
 * A class which is used to register and execute WP-CLI commands.
 *
 * @since 1.3.1
 */
class Apple_News_CLI_Command extends WP_CLI_Command {

	/**
	 * The batch size for batch post processing.
	 *
	 * @var integer
	 */
	const BATCH_SIZE = 100;

	/**
	 * An array containing a cache of max IDs per table.
	 *
	 * @access private
	 * @var array
	 */
	private $_max_ids = array();

	/**
	 * A map between postmeta keys used locally and the remote API data keys.
	 *
	 * @access private
	 * @var array
	 */
	private $_postmeta_map = array(
		'apple_news_api_created_at'  => 'createdAt',
		'apple_news_api_id'          => 'id',
		'apple_news_is_preview'      => 'isPreview',
		'apple_news_is_sponsored'    => 'isSponsored',
		'apple_news_api_modified_at' => 'modifiedAt',
		'apple_news_api_revision'    => 'revision',
		'apple_news_api_share_url'   => 'shareUrl',
	);

	/**
	 * A local copy of the Publish to Apple News settings.
	 *
	 * @access private
	 * @var array
	 */
	private $_settings;

	/**
	 * Updates local postmeta for each post with Apple News API values.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : If present, does not modify anything, but prints what would be done.
	 *
	 * ## EXAMPLES
	 *
	 * wp apple-news sync-api-data
	 * wp apple-news sync-api-data --dry-run
	 *
	 * @subcommand sync-api-data
	 *
	 * @param array $args       Arguments passed to the command.
	 * @param array $assoc_args Associative arguments passed to the command.
	 *
	 * @access     public
	 */
	public function command_sync_api_data( $args, $assoc_args ) {

		// Determine whether we are doing a dry run or not.
		$dry_run = ( ! empty( $assoc_args['dry-run'] ) );

		// Process each post linked to Apple News and update local postmeta.
		\WP_CLI::log( 'Updating local postmeta with values from Apple News.' );
		$this->_bulk_task(
			array(
				'table'  => 'postmeta',
				'search' => array(
					array(
						'key'     => 'meta_key',
						'value'   => 'apple_news_api_id',
						'compare' => '=',
					),
				),
			),
			array( $this, '_sync_api_data' ),
			1,
			array( $dry_run )
		);
	}

	/**
	 * Clear all of the caches for memory management.
	 *
	 * @see    WPCOM_VIP_CLI_Command
	 *
	 * @access protected
	 */
	protected function stop_the_insanity() {
		/**
		 * Import wpdb and wp_object_cache objects to reset values.
		 *
		 * @var \WP_Object_Cache $wp_object_cache
		 * @var \wpdb            $wpdb
		 */
		global $wpdb, $wp_object_cache;

		$wpdb->queries = array();

		if ( is_object( $wp_object_cache ) ) {
			$wp_object_cache->group_ops      = array();
			$wp_object_cache->stats          = array();
			$wp_object_cache->memcache_debug = array();
			$wp_object_cache->cache          = array();

			if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
				$wp_object_cache->__remoteset();
			}
		}
	}

	/**
	 * A generic function to run a bulk task given an arguments array.
	 *
	 * @param array    $args     Arguments to use when running the bulk task.
	 * @param callback $callback A callback function to run on each post found.
	 * @param int      $min_id   The minimum ID for lookup. Defaults to 1.
	 * @param array    $params   Additional parameters to pass to the callback.
	 *
	 * @access private
	 */
	private function _bulk_task( $args, $callback, $min_id = 1, $params = array() ) {

		// Ensure callback is valid.
		if ( ! is_callable( $callback ) ) {
			\WP_CLI::error( 'You specified an invalid callback.' );
		}

		// Process this search term until we run out of hits.
		$processed = 0;
		while ( $ids = $this->_get_batch_results( $args, $min_id ) ) {
			foreach ( $ids as $id ) {

				// Call the callback.
				$callback_params = $params;
				array_unshift( $callback_params, get_post( $id ) );
				call_user_func_array( $callback, $callback_params );

				// Update position.
				$min_id = $id + 1;
				$processed ++;
			}
			\WP_CLI::log( sprintf( 'Processed %d posts.', $processed ) );
		}
	}

	/**
	 * Gets a batch of post IDs matching specified criteria.
	 *
	 * @param array $args   A limited subset of WP_Query args.
	 * @param int   $min_id The minimum ID to use when searching for results.
	 *
	 * @access private
	 * @return array|bool An array of posts on success, false if no hits.
	 */
	private function _get_batch_results( $args, $min_id = 1 ) {

		global $wpdb;

		// Contain memory leaks at the beginning of each run.
		if ( function_exists( 'stop_the_insanity' ) ) {
			stop_the_insanity();
		} else {
			$this->stop_the_insanity();
		}

		// Ensure a table was specified.
		if ( empty( $args['table'] ) ) {
			\WP_CLI::error( 'You must specify a table to query.' );
		}
		$table = $args['table'];

		// Ensure search parameters were specified.
		if ( empty( $args['search'] ) || ! is_array( $args['search'] ) ) {
			\WP_CLI::error( 'You must specify search terms.' );
		}

		// Validate table selection by looking up ID.
		$id_column = $this->_get_id_column_for_table( $table );
		if ( empty( $id_column ) ) {
			\WP_CLI::error( 'You requested an unsupported table.' );
		}

		// Start building query args by adding the dynamic search range.
		$query_args = array(
			$min_id,
			$min_id + self::BATCH_SIZE * 10,
		);

		// Compile search directives.
		$search_directives = array();
		$search_relation   = 'AND';
		foreach ( $args['search'] as $key => $search_config ) {

			// Check for relation condition.
			if ( 'relation' === $key ) {
				if ( 'AND' !== $search_config && 'OR' !== $search_config ) {
					\WP_CLI::error( 'You specified an invalid relation.' );
				}
				$search_relation = $search_config;
				continue;
			}

			// Validate column name.
			if ( empty( $search_config['key'] )
			     || ! $this->_validate_column_for_table(
					$table,
					$search_config['key']
				)
			) {
				\WP_CLI::error( 'You specified an invalid search column.' );
			}

			// Ensure there is a value to compare against.
			if ( ! isset( $search_config['value'] ) ) {
				\WP_CLI::error( 'You did not specify a value to compare against.' );
			}

			// Determine comparison operator.
			$compare = '=';
			if ( ! empty( $search_config['compare'] ) ) {
				$valid_compares = array( '=', '!=', 'LIKE' );
				if ( ! in_array(
					$search_config['compare'],
					$valid_compares,
					true
				) ) {
					\WP_CLI::error( 'You specified an invalid comparator.' );
				}
				$compare = $search_config['compare'];
			}

			// If comparison operator is LIKE, properly escape the value.
			if ( 'LIKE' === $compare ) {
				$search_config['value']
					= '%' . $wpdb->esc_like( $search_config['value'] ) . '%';
			}

			// Add the search directive.
			$search_directives[] = $search_config['key'] . ' ' . $compare . ' %s';
			$query_args[]        = $search_config['value'];
		}

		// Collapse search directives into a search string.
		$search_directives = implode(
			' ' . $search_relation . ' ',
			$search_directives
		);

		// Add maximum.
		$query_args[] = self::BATCH_SIZE;

		// Compile query.
		$query = <<<SQL
SELECT DISTINCT {$id_column}
FROM {$wpdb->$table}
WHERE {$id_column} >= %d
AND {$id_column} < %d
AND ( {$search_directives} )
ORDER BY {$id_column} ASC
LIMIT %d
SQL;

		// Run query on loop until we get some hits, increasing stepping.
		$max_id = $this->_get_max_id_for_table( $table );
		for ( $min_id; $min_id < $max_id; $min_id += self::BATCH_SIZE * 10 ) {
			$query_args[0] = $min_id;
			$query_args[1] = $min_id + self::BATCH_SIZE * 10;
			$results       = $wpdb->get_col( $wpdb->prepare( $query, $query_args ) );
			if ( ! empty( $results ) ) {
				return $results;
			}
		}

		return false;
	}

	/**
	 * Returns the name of the ID column for a given WPDB table partial.
	 *
	 * @param string $table The WPDB table partial (e.g., posts or postmeta).
	 *
	 * @access private
	 * @return bool|string The name of the ID column on success, false on failure.
	 */
	private function _get_id_column_for_table( $table ) {
		switch ( $table ) {
			case 'posts':
				return 'ID';
			case 'postmeta':
				return 'post_id';
			default:
				return false;
		}
	}

	/**
	 * Returns the maximum ID for a given table, cached at a static value per run.
	 *
	 * @param string $table The WPDB table partial (e.g., posts or postmeta).
	 *
	 * @access private
	 * @return int The maximum ID for the table.
	 */
	private function _get_max_id_for_table( $table ) {

		// Check for cache hit.
		if ( empty( $this->_max_ids[ $table ] ) ) {

			global $wpdb;

			// Negotiate ID column.
			$id_column = $this->_get_id_column_for_table( $table );
			if ( empty( $id_column ) ) {
				\WP_CLI::error( 'You specified an invalid table.' );
			}

			// Set up query for max ID.
			$max_query = <<<SQL
SELECT MAX( {$id_column} )
FROM {$wpdb->$table}
SQL;

			// Save to local cache.
			$this->_max_ids[ $table ] = $wpdb->get_var( $max_query );
		}

		return $this->_max_ids[ $table ];
	}

	/**
	 * Gets Publish to Apple News settings.
	 *
	 * @access private
	 * @return array An array of settings.
	 */
	private function _get_settings() {

		// Determine if we already have a local copy of the settings.
		if ( empty( $this->_settings ) ) {
			$admin_apple_settings = new Admin_Apple_Settings;
			$this->_settings      = $admin_apple_settings->fetch_settings();
		}

		return $this->_settings;
	}

	/**
	 * A callback function for a batch task to sync API data for a particular post.
	 *
	 * @param WP_Post $post    The post to process.
	 * @param bool    $dry_run Whether the command was executed as a dry run or not.
	 *
	 * @access private
	 */
	private function _sync_api_data( $post, $dry_run ) {
		WP_CLI::log( sprintf( 'Processing post %d...', $post->ID ) );

		// If the dry-run flag was specified, bail out.
		if ( $dry_run ) {
			WP_CLI::log( 'Skipping post due to dry run flag.' );

			return;
		}

		// Try to get the updated data from the API.
		$get          = new \Apple_Actions\Index\Get( $this->_get_settings(), $post->ID );
		$api_response = $get->perform();
		if ( empty( $api_response->data->id ) ) {
			WP_CLI::warning( sprintf( 'Could not get API data for post %d.', $post->ID ) );

			return;
		}

		// Loop through postmeta and update each as necessary.
		foreach ( $this->_postmeta_map as $meta_key => $data_key ) {

			// If the data key isn't set, skip.
			if ( ! isset( $api_response->data->$data_key ) ) {
				WP_CLI::warning( sprintf( 'Key %s not set for post %d. Skipping.', $data_key, $post->ID ) );
				continue;
			}

			// Get the current value from postmeta.
			$meta_value = get_post_meta( $post->ID, $meta_key, true );

			// If the current meta value and API data value are empty, skip.
			if ( empty( $meta_value ) && empty( $api_response->data->$data_key ) ) {
				continue;
			}

			// If the current meta value is the same as the API data value, skip.
			if ( $meta_value === $api_response->data->$data_key ) {
				continue;
			}

			// Update the postmeta value to match the API value.
			WP_CLI::log( sprintf(
				'Updating %s for %d from %s to %s.',
				$meta_key,
				$post->ID,
				$meta_value,
				$api_response->data->$data_key
			) );
			update_post_meta( $post->ID, $meta_key, $api_response->data->$data_key );
		}
	}

	/**
	 * Ensures a given column name exists within the specified table.
	 *
	 * @param string $table  The WPDB table partial (e.g., posts or postmeta).
	 * @param string $column The column to validate.
	 *
	 * @access private
	 * @return bool True if the column is valid, false if not.
	 */
	private function _validate_column_for_table( $table, $column ) {

		// Build list of valid columns, and fail on unsupported tables.
		switch ( $table ) {
			case 'posts':
				$valid_columns = array(
					'ID',
					'post_author',
					'post_date',
					'post_date_gmt',
					'post_content',
					'post_title',
					'post_excerpt',
					'post_status',
					'comment_status',
					'ping_status',
					'post_password',
					'post_name',
					'to_ping',
					'pinged',
					'post_modified',
					'post_modified_gmt',
					'post_content_filtered',
					'post_parent',
					'guid',
					'menu_order',
					'post_type',
					'post_mime_type',
					'comment_count',
				);
				break;
			case 'postmeta':
				$valid_columns = array(
					'meta_id',
					'post_id',
					'meta_key',
					'meta_value',
				);
				break;
			default:
				return false;
		}

		return in_array( $column, $valid_columns, true );
	}
}
