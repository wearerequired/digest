<?php
/**
 * Registry class.
 *
 * @package Digest
 */

namespace Required\Digest\Event;

use Required\Digest\Message\Comment_Moderation;
use Required\Digest\Message\Comment_Notification;
use Required\Digest\Message\Core_Update;
use Required\Digest\Message\Password_Change_Notification;
use Required\Digest\Message\User_Notification;

/**
 * Event registry.
 *
 * @since 2.0.0
 */
class Registry implements RegistryInterface {
	/**
	 * The registered events.
	 *
	 * @since  2.0.0
	 * @access protected
	 *
	 * @var array
	 */
	protected $registered_events = array();

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
	public function register_event( $event, $callback = null ) {
		if ( $this->is_registered_event( $event ) ) {
			return;
		}

		$this->registered_events[] = $event;

		if ( null !== $callback ) {
			add_filter( 'digest_message_section_' . $event, $callback, 10, 9999 );
		}
	}

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
	public function is_registered_event( $event ) {
		return in_array( $event, $this->registered_events, true );
	}

	/**
	 * Returns all registered events.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @return array The registered events.
	 */
	public function get_registered_events() {
		return $this->registered_events;
	}

	/**
	 * Registers all the default events.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function register_default_events() {
		// Register default events.
		$this->register_event( 'core_update_success', function ( $content, $entries, $user, $event ) {
			$message = new Core_Update( $entries, $user, $event );

			if ( '' === $content ) {
				$content = '<p><b>' . __( 'Core Updates', 'digest' ) . '</b></p>';
			}

			return $content . $message->get_message();
		} );

		$this->register_event( 'core_update_failure', function ( $content, $entries, $user, $event ) {
			$message = new Core_Update( $entries, $user, $event );

			if ( '' === $content ) {
				$content = '<p><b>' . __( 'Core Updates', 'digest' ) . '</b></p>';
			}

			return $content . $message->get_message();
		} );

		$this->register_event( 'comment_moderation', function ( $content, $entries, $user ) {
			$message = new Comment_Moderation( $entries, $user );

			return $content . $message->get_message();
		} );

		$this->register_event( 'comment_notification', function ( $content, $entries, $user ) {
			$message = new Comment_Notification( $entries, $user );

			return $content . $message->get_message();
		} );

		if ( in_array( 'new_user_notification', get_option( 'digest_hooks' ), true ) ) {
			$this->register_event( 'new_user_notification', function ( $content, $entries, $user ) {
				$message = new User_Notification( $entries, $user );

				return $content . $message->get_message();
			} );
		}

		if ( in_array( 'password_change_notification', get_option( 'digest_hooks' ), true ) ) {
			$this->register_event( 'password_change_notification', function ( $content, $entries, $user ) {
				$message = new Password_Change_Notification( $entries, $user );

				return $content . $message->get_message();
			} );
		}

		/**
		 * Fires after registering the default events.
		 *
		 * @since 2.0.0
		 */
		do_action( 'digest_register_events' );
	}
}
