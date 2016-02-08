<?php
/**
 * This class manages admin notices.
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
	 * Current plugin settings.
	 *
	 * @var array
	 * @access private
	 * @static
	 */
	private static $settings;

	/**
	 * Constructor.
	 */
	function __construct( $settings ) {
		self::$settings = $settings;

		add_action( 'admin_notices', array( $this, 'show' ) );
	}

	/**
	 * Add a notice message to be displayed.
	 *
	 * @param string $message
	 * @param string $type
	 * @param int $user_id
	 * @static
	 * @access public
	 */
	public static function message( $message, $type, $user_id = null ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		self::add_user_meta( $user_id, self::KEY, array(
			'message' => sanitize_text_field( $message ),
			'type' => sanitize_text_field( $type )
		) );
	}

	/**
	 * Add an info message.
	 *
	 * @param string $message
	 * @param int $user_id
	 * @static
	 * @access public
	 */
	public static function info( $message, $user_id = null ) {
		self::message( $message, 'updated', $user_id );
	}

	/**
	 * Add a success message.
	 *
	 * @param string $message
	 * @param int $user_id
	 * @static
	 * @access public
	 */
	public static function success( $message, $user_id = null ) {
		self::message( $message, 'updated', $user_id );
	}

	/**
	 * Add an error message.
	 *
	 * @param string $message
	 * @param int $user_id
	 * @static
	 * @access public
	 */
	public static function error( $message, $user_id = null ) {
		self::message( $message, 'error', $user_id );
	}

	/**
	 * Check if a notice exists.
	 *
	 * @param string $message
	 * @param string $type
	 * @static
	 * @access public
	 */
	public static function has_notice() {
		$messages = self::get_user_meta( get_current_user_id(), self::KEY );
		return ! empty( $messages );
	}

	/**
	 * Show the admin notice.
	 *
	 * @static
	 * @access public
	 */
	public static function show() {
		// Check for notices
		$notices = self::get_user_meta( get_current_user_id(), self::KEY );
		if ( empty( $notices ) ) {
			return;
		}

		// Show the notices
		foreach ( $notices as $notice ) {
			if ( ! empty( $notice['message'] ) ) {
				$type = isset( $notice['type'] ) ? $notice['type'] : 'updated';
				self::show_notice( $notice['message'], $type );
			}
		}

		// Clear the notice
		self::delete_user_meta( get_current_user_id(), self::KEY );
	}

	/**
	 * Display the admin notice template.
	 *
	 * @param string $message
	 * @param string $type
	 * @static
	 * @access private
	 */
	private static function show_notice( $message, $type ) {
		?>
		<div class="notice <?php echo esc_attr( $type ) ?> is-dismissible">
			<p><strong><?php echo wp_kses_post( apply_filters( 'apple_news_notice_message', $message, $type ) ) ?></strong></p>
		</div>
		<?php
	}

	/**
	 * Handle adding user meta across potential hosting platforms.
	 *
	 * @param int $user_id
	 * @param string $key
	 * @param mixed $value
	 * @static
	 * @access private
	 */
	private static function add_user_meta( $user_id, $key, $value ) {
		// We can't use add_user_meta because there is no equivalent on VIP.
		// Instead manage values within the same variable for consistency.
		$values = self::get_user_meta( $user_id, $key );
		if ( empty( $values ) ) {
			$values = array();
		}

		// Add the new value
		$values[] = $value;

		// Save using the appropriate method
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
			return update_user_attribute( $user_id, $key, $values );
		} else {
			return update_user_meta( $user_id, $key, $values );
		}
	}

	/**
	 * Handle getting user meta across potential hosting platforms.
	 *
	 * @param int $user_id
	 * @param string $key
	 * @static
	 * @return mixed
	 * @access private
	 */
	private static function get_user_meta( $user_id, $key ) {
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
			return get_user_attribute( $user_id, $key );
		} else {
			return get_user_meta( $user_id, $key, true );
		}
	}

	/**
	 * Handle deleting user meta across potential hosting platforms.
	 *
	 * @param int $user_id
	 * @param string $key
	 * @static
	 * @access private
	 */
	private static function delete_user_meta( $user_id, $key ) {
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
			return delete_user_attribute( $user_id, $key );
		} else {
			return delete_user_meta( $user_id, $key );
		}
	}

}
