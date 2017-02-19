<?php
/**
 * This file holds the WP_Digest_Comment_Notification_Message class.
 *
 * @package Digest
 */

namespace Required\Digest\Message;

/**
 * WP_Digest_Password_Change_Notification_Message class.
 *
 * Responsible for creating the password change notification messag.e
 */
class Password_Change_Notification extends Section {
	/**
	 * Constructor.
	 *
	 * @param array    $entries The user notification entries.
	 * @param \WP_User $user    The current user.
	 */
	public function __construct( $entries, \WP_User $user ) {
		parent::__construct( $user );

		foreach ( $entries as $user_id => $time ) {
			$this->entries[] = $this->get_single_message( $user_id, $time );
		}
	}

	/**
	 * Get password change notification section message.
	 *
	 * @return string The section message.
	 */
	public function get_message() {
		$message = '<p><b>' . __( 'Password Changes', 'digest' ) . '</b></p>';
		if ( 1 === count( $this->entries ) ) {
			$message .= '<p>' . __( 'The following user lost and changed his password:', 'digest' ) . '</p>';
		} else {
			$message .= '<p>' . __( 'The following users lost and changed their passwords:', 'digest' ) . '</p>';
		}
		$message .= '<ul>' . implode( '', $this->entries ) . '</ul>';

		return $message;
	}

	/**
	 * Get the password change notification message.
	 *
	 * @param int $user_id The user ID.
	 * @param int $time    The timestamp when the user changed his password.
	 *
	 * @return string The password change notification message.
	 */
	protected function get_single_message( $user_id, $time ) {
		$user = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			return '';
		}

		return sprintf(
			/* translators: 1: user display name, 2: user ID, 3: human time dif */
			'<li>' . __( '%1$s (ID: %2$d) %3$s ago', 'digest' ) . '</li>',
			esc_html( $user->display_name ),
			absint( $user->ID ),
			human_time_diff( $time, current_time( 'timestamp' ) )
		);
	}
}
