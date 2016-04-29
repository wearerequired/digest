<?php
/**
 * This file holds the WP_Digest_Message class.
 *
 * @package WP_Digest
 */

namespace Required\Digest;

/**
 * Message class.
 *
 * Responsible for creating a new digest message to be sent per email.
 */
class Message {
	/**
	 * The queue items.
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The current user object.
	 *
	 * @var \WP_User|false User object if user exists, false otherwise.
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
		$this->user = get_user_by( 'email', $recipient );

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
		$events = array();

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

		// Loop through the processed events in manual order.
		foreach (
			array(
				'core_update_success',
				'core_update_failure',
				'comment_notification',
				'comment_moderation',
				'new_user_notification',
				'password_change_notification',
			) as $event
		) {
			if ( isset( $this->events[ $event ] ) && 0 < count( array_filter( $this->events[ $event ] ) ) ) {
				/**
				 * Filter the message section
				 *
				 * @param string $message The message.
				 * @param array  $entries The event items.
				 * @param object $user    The current user.
				 * @param string $event   The current event.
				 */
				$message .= apply_filters( 'digest_message_section_' . $event, '', $this->events[ $event ], $this->user, $event );
			}
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

add_action( 'digest_event', array( 'Required\\Digest\\Cron', 'init' ) );
