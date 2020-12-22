<?php
/**
 * Queue class.
 */

namespace Required\Digest;

/**
 * Queue class.
 *
 * Responsible for adding message to the queue and
 * clearing it after it has been processed.
 *
 * Example usage:
 * Queue::add( 'test@example.com', 'comment_notification', $comment_id );
 * Queue::add( 'test@example.com', 'comment_moderation', $comment_id );
 * Queue::add( 'test@example.com', 'new_user_notification', $user_id );
 * Queue::add( 'test@example.com', 'password_change_notification', $user_id );
 * Queue::add( 'test@example.com', 'core_update_success', $version );
 *
 * @since 1.0.0
 */
class Queue {
	/**
	 * Digest queue option name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected static $option = 'digest_queue';

	/**
	 * Retrieves the digest queue option.
	 *
	 * It can be modified using the `digest_queue` filter.
	 *
	 * @since  1.0.0
	 *
	 * @return array The digest queue.
	 */
	public static function get() {
		return apply_filters( 'digest_queue', get_option( self::$option, [] ) );
	}

	/**
	 * Clears the digest queue.
	 *
	 * @since 1.0.0
	 */
	public static function clear() {
		delete_option( self::$option );
	}

	/**
	 * Adds an event to the queue for a specific recipient.
	 *
	 * @since 1.0.0
	 *
	 * @param string $recipient The recipient's email address.
	 * @param string $event     The type of the event.
	 * @param string $data      Data to store for this event, for example a comment ID.
	 */
	public static function add( $recipient, $event, $data ) {
		$queue = self::get();

		$queue[ $recipient ] = $queue[ $recipient ] ?? [];

		$queue[ $recipient ][] = [ current_time( 'timestamp' ), $event, $data ];

		update_option( self::$option, $queue );
	}
}
