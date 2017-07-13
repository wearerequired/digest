<?php
/**
 * Registry interface.
 *
 * @package Digest
 */

namespace Required\Digest\Event;

/**
 * Event registry interface.
 *
 * @since 2.0.0
 */
interface RegistryInterface {
	/**
	 * Registers an event for the digest.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @param string   $event    Event name.
	 * @param callable $callback Optional. Callback to be used when sending the digest.
	 *
	 * @return void
	 */
	public function register_event( $event, $callback = null );

	/**
	 * Determines if an event has been registered.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @param string $event Event name.
	 *
	 * @return bool True if the event has been registered, false otherwise.
	 */
	public function is_registered_event( $event );

	/**
	 * Returns all registered events.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @return array The registered events.
	 */
	public function get_registered_events();

	/**
	 * Registers all the default events.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function register_default_events();
}
