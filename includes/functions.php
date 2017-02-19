<?php
/**
 * Template Tags
 *
 * @package WP_Digest
 */

use Required\Digest\Queue;

/**
 * Retrieve the digest queue option.
 *
 * It can be modified using the `digest_queue` filter.
 *
 * @return array The digest queue.
 */
function digest_queue_get() {
	return Queue::get();
}

/**
 * Add an event to the queue for a specific recipient.
 *
 * @param string $recipient The recipient's email address.
 * @param string $event     The type of the event.
 * @param string $data      Data to store for this event, for example a comment ID.
 */
function digest_queue_add( $recipient, $event, $data ) {
	Queue::add( $recipient, $event, $data );
}

/**
 * Clear the digest queue.
 */
function digest_queue_clear() {
	Queue::clear();
}
