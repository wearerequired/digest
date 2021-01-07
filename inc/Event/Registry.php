<?php
/**
 * Registry class.
 */

namespace Required\Digest\Event;

/**
 * Event registry.
 *
 * @since 2.0.0
 */
class Registry {
	/**
	 * The registered events.
	 *
	 * @since  2.0.0
	 *
	 * @var array
	 */
	protected static $registered_events = [];

	/**
	 * Registers an event for the digest.
	 *
	 * @since  2.0.0
	 *
	 * @param string   $event    Event name.
	 * @param callable $callback Optional. Callback to be used when sending the digest.
	 * @return void
	 */
	public static function register_event( $event, $callback = null ) {
		if ( self::is_registered_event( $event ) ) {
			return;
		}

		self::$registered_events[] = $event;

		if ( null !== $callback ) {
			add_filter( 'digest_message_section_' . $event, $callback, 10, 9999 );
		}
	}

	/**
	 * Determines if an event has been registered.
	 *
	 * @since  2.0.0
	 *
	 * @param string $event Event name.
	 * @return bool True if the event has been registered, false otherwise.
	 */
	public static function is_registered_event( $event ) {
		return \in_array( $event, self::$registered_events, true );
	}

	/**
	 * Returns all registered events.
	 *
	 * @since  2.0.0
	 *
	 * @return array The registered events.
	 */
	public static function get_registered_events() {
		return self::$registered_events;
	}

	/**
	 * Clears all registered events.
	 *
	 * @since 2.0.0
	 */
	public static function clear() {
		self::$registered_events = [];
	}
}
