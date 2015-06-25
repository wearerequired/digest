<?php
/**
 * WP Digest Queue implementation.
 *
 * @package WP_Digest
 */

defined( 'WPINC' ) or die;

/**
 * WP_Digest_Queue class.
 *
 * Responsible for adding message to the queue and
 * clearing it after it has been processed.
 *
 * Example usage:
 * WP_Digest_Queue::add( 'test@example.com', 'comment_notification', $comment_id );
 * WP_Digest_Queue::add( 'test@example.com', 'comment_moderation', $comment_id );
 * WP_Digest_Queue::add( 'test@example.com', 'new_user_notification', $user_id );
 * WP_Digest_Queue::add( 'test@example.com', 'password_change_notification', $user_id );
 * WP_Digest_Queue::add( 'test@example.com', 'core_update_success', $version );
 */
class WP_Digest_Queue {
	/**
	 * Digest queue option name.
	 *
	 * @var string
	 */
	protected static $option = 'digest_queue';

	/**
	 * Retrieve the digest queue option.
	 *
	 * It can be modified using the `digest_queue` filter.
	 *
	 * @return array The digest queue.
	 */
	public static function get() {
		return apply_filters( 'digest_queue', get_option( self::$option, array() ) );
	}

	/**
	 * Clear the digest queue.
	 */
	public static function clear() {
		delete_option( self::$option );
	}

	/**
	 * Add an event to the queue for a specific recipient.
	 *
	 * @param string $recipient The recipient's email address.
	 * @param string $event     The type of the event.
	 * @param string $data      Data to store for this event, for example a comment ID.
	 */
	public static function add( $recipient, $event, $data ) {
		$queue = self::get();

		$queue[ $recipient ] = isset( $queue[ $recipient ] ) ? $queue[ $recipient ] : array();

		$queue[ $recipient ][] = array( current_time( 'timestamp' ), $event, $data );

		update_option( self::$option, $queue );
	}
}
