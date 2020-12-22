<?php
/**
 * UserNotification class.
 */

namespace Required\Digest\Message;

/**
 * User notification message class.
 *
 * Responsible for creating the comment notification section.
 *
 * @since 2.0.0
 */
class UserNotification extends Section {
	/**
	 * Returns the core update section message.
	 *
	 * @since  2.0.0
	 *
	 * @return string The section message.
	 */
	public function get_message() {
		return implode( '', $this->entries );
	}

	/**
	 * Returns the new user notification message.
	 *
	 * @since  2.0.0
	 *
	 * @param int $user_id The user ID.
	 * @param int $time    The timestamp when the user signed up.
	 * @return string The new user notification message.
	 */
	protected function get_single_message( $user_id, $time ) {
		$user = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			return '';
		}

		return sprintf(
			/* translators: 1: user display name, 2: user ID, 3: human time dif */
			'<li>' . __( '%1$s (ID: %2$d) %3$s ago', 'digest' ) . '</li>',
			$user->display_name,
			$user->ID,
			human_time_diff( $time, current_time( 'timestamp' ) )
		);
	}
}
