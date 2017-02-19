<?php
/**
 * This file holds the abstract digest section message class.
 *
 * @package WP_Digest
 */

namespace Required\Digest\Message;

/**
 * Section_Message class.
 *
 * Can be extended by other classes to modify the section message.
 */
abstract class Section {
	/**
	 * The section entries.
	 *
	 * @var array
	 */
	protected $entries = array();

	/**
	 * The current user object.
	 *
	 * @var \WP_User|false User object if user exists, false otherwise.
	 */
	protected $user = false;

	/**
	 * Get the section message.
	 *
	 * @return string The section message.
	 */
	abstract public function get_message();

	/**
	 * Constructor. Sets the current user.
	 *
	 * @param \WP_User $user The current user.
	 */
	public function __construct( \WP_User $user ) {
		$this->user = $user;
	}
}
