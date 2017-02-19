<?php
/**
 * User_Notification class.
 *
 * @package Digest
 */

namespace Required\Digest\Message;

use WP_User;

/**
 * User notification message class.
 *
 * Responsible for creating the comment notification section.
 *
 * @since 2.0.0
 */
class User_Notification extends Section {
	/**
	 * Constructor.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @param array   $entries The user notification entries.
	 * @param WP_User $user    The current user.
	 */
	public function __construct( $entries, WP_User $user ) {
		parent::__construct( $user );

		foreach ( $entries as $user_id => $time ) {
			$this->entries[] = $this->get_single_message( $user_id, $time );
		}
	}

	/**
	 * Returns the core update section message.
	 *
	 * @since  2.0.0
	 * @access public
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
	 * @access protected
	 *
	 * @param int $user_id The user ID.
	 * @param int $time    The timestamp when the user signed up.
	 *
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
			$user->display_name, $user->ID,
			human_time_diff( $time, current_time( 'timestamp' ) )
		);
	}
}
