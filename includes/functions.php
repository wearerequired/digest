<?php
/**
 * Template Tags
 *
 * @package WP_Digest
 */

use Required\Digest\Queue;

function digest_register_event( $event, $callback ) {
	digest()->register_event( $event, $callback );
}

function digest_is_registered_event( $event ) {
	return digest()->is_registered_event( $event );
}

function digest_get_registered_events() {
	return digest()->get_registered_events();
}

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
