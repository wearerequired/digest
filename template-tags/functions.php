<?php
/**
 * Template Tags
 *
 * @package Digest
 */

use Required\Digest\Queue;
use Required\Digest\Event\Registry;

/**
 * Registers an event for the digest.
 *
 * @since 2.0.0
 *
 * @param string   $event    Event name.
 * @param callable $callback Optional. Callback to be used when sending the digest.
 *
 * @return void
 */
function digest_register_event( $event, $callback ) {
	Registry::register_event( $event, $callback );
}

/**
 * Determines if an event has been registered.
 *
 * @since 2.0.0
 *
 * @param string $event Event name.
 *
 * @return bool True if the event has been registered, false otherwise.
 */
function digest_is_registered_event( $event ) {
	return Registry::is_registered_event( $event );
}

/**
 * Returns all registered events.
 *
 * @since 2.0.0
 *
 * @return array The registered events.
 */
function digest_get_registered_events() {
	return Registry::get_registered_events();
}

/**
 * Retrieves the digest queue.
 *
 * It can be modified using the `digest_queue` filter.
 *
 * @since 1.0.0
 *
 * @return array The digest queue.
 */
function digest_queue_get() {
	return Queue::get();
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
function digest_queue_add( $recipient, $event, $data ) {
	Queue::add( $recipient, $event, $data );
}

/**
 * Clears the digest queue.
 *
 * @since 1.0.0
 */
function digest_queue_clear() {
	Queue::clear();
}
