<?php
/**
 * Handles asynchronous actions for the Apple News plugin.
 *
 * @author  Bradford Campeau-Laurion
 * @since   1.0.0
 * @package Apple_News
 */

/**
 * Entry-point class for the plugin.
 */
class Admin_Apple_Async extends Apple_News {

	/**
	 * Current plugin settings.
	 *
	 * @var array
	 * @access private
	 */
	private $settings;

	/**
	 * Hook name for publishing in async mode
	 *
	 * @access public
	 */
	const ASYNC_PUSH_HOOK = 'apple_news_async_push';

	/**
	 * Constructor.
	 *
	 * @param \Apple_Exporter\Settings $settings Settings to use during this run.
	 * @access public
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;

		// If async mode is enabled create the action hook.
		if ( 'yes' === $settings->get( 'api_async' ) ) {
			add_action( self::ASYNC_PUSH_HOOK, array( $this, 'async_push' ), 10, 2 );

			// If we're on VIP, set async mode to use the jobs system.
			if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
				add_filter( 'wpcom_vip_passthrough_cron_to_jobs', array( $this, 'passthrough_cron_to_jobs' ) );
			}
		}
	}

	/**
	 * Handle performing an asynchronous push request.
	 *
	 * @since 1.0.0
	 * @param int $post_id The post ID to be pushed.
	 * @param int $user_id The user ID performing the push.
	 * @access public
	 */
	public function async_push( $post_id, $user_id ) {
		/**
		 * This hook could be used for an ini_set to increase max execution time
		 * for asynchronous publishing to handle large push requests.
		 * Since some hosts wouldn't support it, the code isn't added directly here.
		 *
		 * On WordPress VIP this isn't necessary since the plugin
		 * will automatically use the jobs system which can handle requests up to 12 hours.
		 */
		do_action( 'apple_news_before_async_push' );

		// Ensure that the job can't be picked up twice.
		$in_progress = get_post_meta( $post_id, 'apple_news_api_async_in_progress', true );
		if ( ! empty( $in_progress ) ) {
			return;
		}

		update_post_meta( $post_id, 'apple_news_api_async_in_progress', time() );

		// Ensure that the post is still published.
		$post = get_post( $post_id );
		if ( 'publish' !== $post->post_status ) {
			Admin_Apple_Notice::error(
				sprintf(
					// translators: token is the post title.
					__( 'Article %s is no longer published and cannot be pushed to Apple News.', 'apple-news' ),
					$post->post_title
				),
				$user_id
			);
			return;
		}

		$action = new Apple_Actions\Index\Push( $this->settings, $post_id );
		try {
			$action->perform( true, $user_id );
		} catch ( Apple_Actions\Action_Exception $e ) {
			Admin_Apple_Notice::error( $e->getMessage(), $user_id );
		}

		do_action( 'apple_news_after_async_push' );
	}

	/**
	 * On WordPress VIP only, run async publishing requests through the jobs system.
	 * This will allow for a maximum publishing time up to 12 hours, which is
	 * well in excess of even the most lengthy API request.
	 *
	 * @since 1.0.0
	 * @param array $hooks The list of hooks to be modified.
	 * @access public
	 * @return array The modified list of hooks.
	 */
	public function passthrough_cron_to_jobs( $hooks ) {
		$hooks[] = self::ASYNC_PUSH_HOOK;
		return $hooks;
	}
}
