<?php
/**
 * Publish to Apple News Admin: Admin_Apple_Notice class
 *
 * @package Apple_News
 */

/**
 * A class to manage Publish to Apple News notices in the WP admin.
 *
 * @since 0.6.0
 */
class Admin_Apple_Notice {

	/**
	 * Key for admin notices.
	 *
	 * @access public
	 */
	const KEY = 'apple_news_notice';

	/**
	 * Add an error message.
	 *
	 * @param string $message     The message to be displayed.
	 * @param int    $user_id     The user ID for which to display the message.
	 * @param bool   $dismissable Whether the message is dismissable (dismissed state stored in DB).
	 * @access public
	 */
	public static function error( $message, $user_id = null, $dismissable = false ) {
		self::message( $message, 'error', $user_id, $dismissable );
	}

	/**
	 * Check if any notices exist for the current user.
	 *
	 * @access public
	 * @return bool True if the user has notices, false otherwise.
	 */
	public static function has_notice() {
		$messages = self::get_user_meta( get_current_user_id() );
		return ! empty( $messages );
	}

	/**
	 * Add an info message.
	 *
	 * @param string $message     The message to be displayed.
	 * @param int    $user_id     The user ID for which to display the message.
	 * @param bool   $dismissable Whether the message is dismissable (dismissed state stored in DB).
	 * @access public
	 */
	public static function info( $message, $user_id = null, $dismissable = false ) {
		self::message( $message, 'warning', $user_id, $dismissable );
	}

	/**
	 * Add a notice message to be displayed.
	 *
	 * @param string $message     The message to be displayed.
	 * @param string $type        The type of message to display.
	 * @param int    $user_id     The user ID for which to display the message.
	 * @param bool   $dismissable Whether the message is dismissable (dismissed state stored in DB).
	 * @access public
	 */
	public static function message( $message, $type, $user_id = null, $dismissable = false ) {

		// Default to the current user, if no ID was specified.
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// Sanitize values.
		$message = wp_kses( $message, array( 'a' => array( 'href' => array() ) ) );
		$type    = sanitize_text_field( $type );

		// Pull usermeta and see if the message already exists.
		$messages = self::get_user_meta( $user_id );
		if ( ! empty( $messages ) && is_array( $messages ) ) {
			foreach ( $messages as $message_check ) {
				if ( ! empty( $message_check['message'] )
					&& $message === $message_check['message']
					&& ! empty( $message_check['type'] )
					&& $type === $message_check['type']
				) {
					return;
				}
			}
		}

		// Add the message to usermeta for later display.
		self::add_user_meta(
			$user_id,
			array(
				'dismissable' => $dismissable,
				'dismissed'   => false,
				'message'     => $message,
				'type'        => $type,
			)
		);
	}

	/**
	 * Registers assets used by meta boxes.
	 *
	 * @access public
	 */
	public static function register_assets() {

		// Enqueue notice script.
		wp_enqueue_script(
			'apple_news_notices_js',
			plugin_dir_url( __FILE__ ) . '../assets/js/notices.js',
			array(),
			Apple_News::$version,
			true
		);
	}

	/**
	 * Show the admin notice(s).
	 *
	 * @access public
	 */
	public static function show() {

		// Check for notices.
		$notices = self::get_user_meta( get_current_user_id() );
		if ( empty( $notices ) || ! is_array( $notices ) ) {
			return;
		}

		// Keep track of an updated list of notices to save to the DB, if necessary.
		$updated_notices = array();

		// Show the notices.
		foreach ( $notices as $notice ) {

			// If the notice doesn't have a message (for some reason), skip it.
			if ( empty( $notice['message'] ) ) {
				continue;
			}

			// If a type isn't specified, default to 'updated'.
			$type = isset( $notice['type'] ) ? $notice['type'] : 'updated';

			// Only display notices that aren't dismissed.
			if ( empty( $notice['dismissed'] ) ) {
				self::show_notice( $notice['message'], $type );
			}

			// If the notice is dismissable, ensure it persists in the DB.
			if ( ! empty( $notice['dismissable'] ) ) {
				$updated_notices[] = $notice;
			}
		}

		// Delete the notices in the DB if they have changed.
		if ( $notices !== $updated_notices ) {
			self::update_user_meta( get_current_user_id(), $updated_notices );
		}
	}

	/**
	 * Add a success message.
	 *
	 * @param string $message     The message to be displayed.
	 * @param int    $user_id     The user ID for which to display the message.
	 * @param bool   $dismissable Whether the message is dismissable (dismissed state stored in DB).
	 * @access public
	 */
	public static function success( $message, $user_id = null, $dismissable = false ) {
		self::message( $message, 'success', $user_id, $dismissable );
	}

	/**
	 * A WP-AJAX handler for a click on a dismiss notice button.
	 */
	public static function wp_ajax_dismiss_notice() {

		// Check the nonce.
		$nonce = isset( $_REQUEST['nonce'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) )
			: '';
		if ( ! wp_verify_nonce( $nonce, 'apple_news_dismiss_notice' ) ) {
			return;
		}

		// Safely extract message and type for comparison.
		$message = isset( $_POST['message'] )
			? wp_kses_post( wp_unslash( $_POST['message'] ) )
			: '';
		$type    = isset( $_POST['type'] )
			? wp_kses_post( wp_unslash( $_POST['type'] ) )
			: '';

		// Pull the list of notices for this user.
		$update  = false;
		$notices = self::get_user_meta( get_current_user_id() );
		foreach ( $notices as &$notice ) {
			if ( isset( $notice['message'] )
				&& $message === $notice['message']
				&& isset( $notice['type'] )
				&& $type === $notice['type']
			) {
				$notice['dismissed'] = true;
				$update              = true;
			}
		}

		// Save the updated notice config to the database.
		if ( $update ) {
			self::update_user_meta( get_current_user_id(), $notices );
		}
	}

	/**
	 * Handle adding user meta across potential hosting platforms.
	 *
	 * @param int   $user_id The user ID to use when adding meta.
	 * @param array $value   The value to add.
	 * @access private
	 * @return int|bool Meta ID if new, true on update, false otherwise.
	 */
	private static function add_user_meta( $user_id, $value ) {

		// We can't use add_user_meta because there is no equivalent on VIP.
		// Instead manage values within the same variable for consistency.
		$values = self::get_user_meta( $user_id );
		if ( empty( $values ) ) {
			$values = array();
		}

		// Add the new value.
		$values[] = $value;

		// Save using the appropriate method.
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
			return update_user_attribute( $user_id, self::KEY, $values );
		} else {
			return update_user_meta( $user_id, self::KEY, $values ); // phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_update_user_meta
		}
	}

	/**
	 * Handle deleting user meta across potential hosting platforms.
	 *
	 * @param int $user_id The user ID for which to delete meta.
	 * @access private
	 * @return bool True on success, false on failure.
	 */
	private static function delete_user_meta( $user_id ) {
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
			return delete_user_attribute( $user_id, self::KEY );
		} else {
			return delete_user_meta( $user_id, self::KEY ); // phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_delete_user_meta
		}
	}

	/**
	 * Handle getting user meta across potential hosting platforms.
	 *
	 * @param int $user_id The user ID for which to retrieve meta.
	 * @access private
	 * @return array An array of values for the key.
	 */
	private static function get_user_meta( $user_id ) {

		// Negotiate meta value.
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
			$meta_value = get_user_attribute( $user_id, self::KEY );
		} else {
			$meta_value = get_user_meta( $user_id, self::KEY, true ); // phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_get_user_meta
		}

		return ( ! empty( $meta_value ) ) ? $meta_value : array();
	}

	/**
	 * Display the admin notice template.
	 *
	 * @param string $message The message to display.
	 * @param string $type    The message type.
	 * @access private
	 */
	private static function show_notice( $message, $type ) {

		// Format messages a little nicer.
		$message       = str_replace( '|', '<br />', $message );
		$message_array = explode( ':', $message );
		if ( 2 === count( $message_array ) ) {
			/**
			 * If it's not 2, it's too unclear how to proceed.
			 * Try to split the second param on commas.
			 */
			$errors = explode( ',', $message_array[1] );
			if ( count( $errors ) > 1 ) {
				// If there isn't more than one error, this isn't worth it.
				$errors_formatted = implode( '<br />', array_map( 'trim', $errors ) );
				$message          = sprintf(
					'%s:<br />%s',
					$message_array[0],
					$errors_formatted
				);
			}
		}

		// Add the support tagline to errors.
		if ( 'error' === $type ) {
			$message .= Apple_News::get_support_info();
		}

		/**
		 * Allows the message content to be filtered before display.
		 *
		 * @param string $message The message to be displayed.
		 * @param string $type    The type of message being displayed.
		 */
		$message = apply_filters( 'apple_news_notice_message', $message, $type );

		// Load the partial for the notice.
		include plugin_dir_path( __FILE__ ) . 'partials/notice.php';
	}

	/**
	 * Handle updating user meta across potential hosting platforms.
	 *
	 * @param int   $user_id The user ID to use when updating meta.
	 * @param array $values  The new values to use.
	 * @access private
	 * @return int|bool Meta ID if new, true on update, false otherwise.
	 */
	private static function update_user_meta( $user_id, $values ) {

		// Delete existing user meta.
		self::delete_user_meta( $user_id );

		// Only add meta back if new values were provided.
		if ( ! empty( $values ) ) {
			// Save using the appropriate method.
			if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
				return update_user_attribute( $user_id, self::KEY, $values );
			} else {
				return update_user_meta( $user_id, self::KEY, $values ); // phpcs:ignore WordPress.VIP.RestrictedFunctions.user_meta_update_user_meta
			}
		}

		return true;
	}
}
