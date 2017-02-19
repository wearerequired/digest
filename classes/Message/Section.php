<?php
/**
 * Section class.
 *
 * @package Digest
 */

namespace Required\Digest\Message;

use WP_User;

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
	 * @access protected
	 *
	 * @var array
	 */
	protected $entries = array();

	/**
	 * The current user object.
	 *
	 * @since  2.0.0
	 * @access protected
	 *
	 * @var WP_User|false User object if user exists, false otherwise.
	 */
	protected $user = false;

	/**
	 * Returns the section message.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @return string The section message.
	 */
	abstract public function get_message();

	/**
	 * Constructor.
	 *
	 * Sets the current user.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @param WP_User $user The current user.
	 */
	public function __construct( WP_User $user ) {
		$this->user = $user;
	}
}
