<?php
defined( 'WPINC' ) or die;

class WP_Digest_Queue {
	protected static $option = 'digest_queue';

	/**
	 * Retrieve the digest queue option.
	 *
	 * It can be modified using the `digest_queue` filter.
	 *
	 * @return array The digest queue.
	 */
	public static function get() {
		return apply_filters( 'digest_queue', get_site_option( self::$option, array() ) );
	}

	/**
	 * Clear the digest queue.
	 */
	public static function clear() {
		delete_site_option( self::$option );
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

		update_site_option( self::$option, $queue );
	}
}
