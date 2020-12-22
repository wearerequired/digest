<?php
/**
 * Digest message interface.
 */

namespace Required\Digest\Message;

/**
 * Digest message interface.
 *
 * @since 2.0.0
 */
interface MessageInterface {
	/**
	 * Returns the message.
	 *
	 * @since  2.0.0
	 *
	 * @return string The section message.
	 */
	public function get_message();
}
