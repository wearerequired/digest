<?php
/**
 * Section class.
 */

namespace Required\Digest\Message;

/**
 * Class for a single section of the digest email.
 *
 * Can be extended by other classes to modify the section message.
 *
 * @since 2.0.0
 */
abstract class Section implements MessageInterface {
	/**
	 * The section entries.
	 *
	 * @since  2.0.0
	 *
	 * @var array
	 */
	protected $entries = [];

	/**
	 * The current user object.
	 *
	 * @since  2.0.0
	 *
	 * @var \WP_User|null User object if user exists, false otherwise.
	 */
	protected $user;

	/**
	 * Returns the section message.
	 *
	 * @since  2.0.0
	 *
	 * @return string The section message.
	 */
	abstract public function get_message();

	/**
	 * Returns a single message in the section.
	 *
	 * @since  2.0.0
	 *
	 * @param string|int $entry The single entry item. For example a user ID or comment ID.
	 * @param int        $time  The timestamp when the update happened.
	 * @return string The single message.
	 */
	abstract protected function get_single_message( $entry, $time );

	/**
	 * Constructor.
	 *
	 * Sets the current user.
	 *
	 * @since  2.0.0
	 *
	 * @param array         $entries The message entries.
	 * @param \WP_User|null $user The current user.
	 */
	public function __construct( $entries, $user = null ) {
		$this->user = $user;

		foreach ( $entries as $entry => $time ) {
			$this->entries[] = $this->get_single_message( $entry, $time );
		}
	}
}
