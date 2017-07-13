<?php
/**
 * This file holds the WP_Digest_Message class.
 *
 * @package Digest
 */

namespace Required\Digest;

use WP_User;

/**
 * Digest class.
 *
 * Responsible for creating a new digest message to be sent per email.
 */
class Digest {
	/**
	 * The queue items.
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The current user object.
	 *
	 * @var WP_User|null User object if user exists, null otherwise.
	 */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param string $recipient The recipient's email address.
	 * @param array  $items     The queue items for this recipient.
	 */
	public function __construct( $recipient, array $items ) {
		// Load the user with this email address if it exists.
		$user = get_user_by( 'email', $recipient );

		if ( $user ) {
			$this->user = $user;
		}

		$this->events = $this->process_event_items( $items );
	}

	/**
	 * Process all queue items and generate the according messages.
	 *
	 * @param array $items The queue items.
	 *
	 * @return array The processed event messages.
	 */
	protected function process_event_items( $items ) {
		$events = [];

		foreach ( $items as $item ) {
			$events[ $item[1] ][ $item[2] ] = $item[0];
		}

		return $events;
	}

	/**
	 * Process the queue for a single recipient.
	 *
	 * @return string The generated message.
	 */
	public function get_message() {
		$message = '';

		// Loop through the processed events.
		foreach ( digest_get_registered_events() as $event ) {
			if ( ! isset( $this->events[ $event ] ) ) {
				continue;
			}

			/**
			 * Filter the message section
			 *
			 * @param string  $message The message.
			 * @param array   $entries The event items.
			 * @param WP_User $user    The current user.
			 * @param string  $event   The current event.
			 */
			$message .= apply_filters( 'digest_message_section_' . $event, '', $this->events[ $event ], $this->user, $event );
		}

		if ( '' === $message ) {
			return '';
		}

		$salutation  = $this->user ? sprintf( __( 'Hi %s', 'digest' ), $this->user->display_name ) : __( 'Hi there', 'digest' );
		$valediction = '<p>' . __( "That's it, have a nice day!", 'digest' ) . '</p>';
		$salutation  = '<p>' . $salutation . '</p><p>' . __( "See what's happening on your site:", 'digest' ) . '</p>';

		return $salutation . $message . $valediction;
	}
}
