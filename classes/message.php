<?php
/**
 * This file holds the WP_Digest_Message class.
 *
 * @package WP_Digest
 */

defined( 'WPINC' ) or die;

/**
 * WP_Digest_Message class.
 *
 * Responsible for creating a new digest message
 * to be sent per email.
 */
class WP_Digest_Message {
	/**
	 * The queue items.
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The current user object.
	 *
	 * @var WP_User|false User object if user exists, false otherwise.
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
		$this->user   = get_user_by( 'email', $recipient );
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
			$method = 'get_' . $item[1] . '_message';

			if ( in_array( $item[1], array( 'core_update_success', 'core_update_fail', 'core_update_manual' ) ) ) {
				$item[1] = 'core_update';
			}

			$message = '';

			if ( method_exists( $this, $method ) ) {
				$message = $this->$method( $item[2], $item[0] );
			}

			/**
			 * Filter the single event message.
			 *
			 * @param string $message The message.
			 * @param array  $item    The event item.
			 *
			 * @return string The filtered message.
			 */
			$message = apply_filters( 'digest_event_message', $message, $item );

			$events[ $item[1] ][] = $message;
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
				'core_update',
				'comment_notification',
				'comment_moderation',
				'new_user_notification',
				'password_change_notification',
			) as $event
		) {
			if ( isset( $this->events[ $event ] ) && 0 < count( array_filter( $this->events[ $event ] ) ) ) {
				// Add some text before and after the entries.
				$message .= $this->get_event_section( $event, $this->events[ $event ] );
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

	/**
	 * Get the content for a specific event by adding some text before and after the entries.
	 *
	 * @param string $section The type of event, e.g. comment notification or core update.
	 * @param array  $entries The entries for this event.
	 *
	 * @return string The section's content.
	 */
	protected function get_event_section( $section, array $entries ) {
		$method = 'get_' . $section . '_section_message';

		$message = '<p><b>' . __( 'Others', 'digest' ) . '</b></p>';
		$message .= implode( '', $entries );

		if ( method_exists( $this, $method ) ) {
			$message = $this->$method( $entries );
		}

		/**
		 * Filter the event section message.
		 *
		 * @param string $message The message.
		 * @param string $section The event section name.
		 * @param array  $entries The event entries.
		 *
		 * @return string The filtered message.
		 */
		$message = apply_filters( 'digest_event_section_message', $message, $section, $entries );

		return $message;
	}

	/**
	 * Get comment notification section message.
	 *
	 * @param array $entries The comment notification entries.
	 *
	 * @return string The section message.
	 */
	protected function get_comment_notification_section_message( array $entries ) {
		$processed_count = count( $entries ) - count( array_filter( $entries ) );

		$message = '<p><b>' . __( 'New Comments', 'digest' ) . '</b></p>';
		$message .= '<p>';
		$message .= sprintf(
			_n(
				'There was %s new comment.',
				'There were %s new comments.',
				count( $entries ),
				'digest'
			),
			number_format_i18n( count( $entries ) )
		);
		if ( 0 < $processed_count ) {
			$message .= ' ';
			$message .= sprintf(
				_n(
					'%s comment was already moderated.',
					'%s comments were already moderated.',
					$processed_count,
					'digest'
				),
				number_format_i18n( $processed_count )
			);
		}
		$message .= '</p>';
		$message .= implode( '', $entries );

		return $message;
	}

	/**
	 * Get comment moderation section message.
	 *
	 * @param array $entries The comment moderation entries.
	 *
	 * @return string The section message.
	 */
	protected function get_comment_moderation_section_message( array $entries ) {
		$processed_count = count( $entries ) - count( array_filter( $entries ) );

		$message = '<p><b>' . __( 'Pending Comments', 'digest' ) . '</b></p>';
		$message .= '<p>';
		$message .= sprintf(
			_n(
				'There is %s new comment waiting for approval.',
				'There are %s new comments waiting for approval.',
				count( $entries ),
				'digest'
			),
			number_format_i18n( count( $entries ) )
		);
		if ( 0 < $processed_count ) {
			$message .= ' ';
			$message .= sprintf(
				_n(
					'%s comment was already moderated.',
					'%s comments were already moderated.',
					$processed_count,
					'digest'
				),
				number_format_i18n( $processed_count )
			);
		}
		$message .= '</p>';
		$message .= implode( '', $entries );
		$message .= sprintf(
			'<p>' . __( 'Please visit the <a href="%s">moderation panel</a>.', 'digest' ) . '</p>',
			admin_url( 'edit-comments.php?comment_status=moderated' )
		);

		return $message;
	}

	/**
	 * Get new user notification section message.
	 *
	 * @param array $entries The new user notification entries.
	 *
	 * @return string The section message.
	 */
	protected function get_new_user_notification_section_message( array $entries ) {
		$message = '<p><b>' . __( 'New User Sign-ups', 'digest' ) . '</b></p>';
		$message .= '<p>' . _n( 'The following user signed up on your site:', 'The following users signed up on your site:', count( $entries ), 'digest' ) . '</p>';
		$message .= '<ul>' . implode( '', $entries ) . '</ul>';

		return $message;
	}

	/**
	 * Get password change notification section message.
	 *
	 * @param array $entries The password change notification entries.
	 *
	 * @return string The section message.
	 */
	protected function get_password_change_notification_section_message( array $entries ) {
		$message = '<p><b>' . __( 'Password Changes', 'digest' ) . '</b></p>';
		$message .= '<p>' . _n( 'The following user lost and changed his password:', 'The following users lost and changed their passwords:', count( $entries ), 'digest' ) . '</p>';
		$message .= '<ul>' . implode( '', $entries ) . '</ul>';

		return $message;
	}

	/**
	 * Get core update section message.
	 *
	 * @param array $entries The core update notification entries.
	 *
	 * @return string The section message.
	 */
	protected function get_core_update_section_message( array $entries ) {
		$message = '<p><b>' . __( 'Core Updates', 'digest' ) . '</b></p>';
		$message .= implode( '', $entries );

		return $message;
	}

	/**
	 * Get the comment notification message.
	 *
	 * @param int $comment_id The comment ID.
	 * @param int $time       The timestamp when the comment was written.
	 *
	 * @return string The comment moderation message.
	 */
	protected function get_comment_notification_message( $comment_id, $time ) {
		/**
		 * Current comment object.
		 *
		 * @var object $comment
		 */
		$comment = get_comment( $comment_id );

		if ( null === $comment || '1' !== $comment->comment_approved ) {
			return '';
		}

		$message = $this->comment_message( $comment, $time );

		$actions = array(
			'view' => __( 'Permalink', 'digest' ),
		);

		if ( $this->user && user_can( $this->user, 'edit_comment' ) ) {
			if ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = _x( 'Trash', 'verb', 'digest' );
			} else {
				$actions['delete'] = __( 'Delete', 'digest' );
			}
			$actions['spam'] = _x( 'Spam', 'verb', 'digest' );
		}

		if ( ! empty( $actions ) ) {
			$message .= '<p>' . $this->comment_action_links( $actions, $comment_id ) . '</p>';
		}

		return $message;
	}

	/**
	 * Get the comment moderation message.
	 *
	 * @param int $comment_id The comment ID.
	 * @param int $time       The timestamp when the comment was written.
	 *
	 * @return string The comment moderation message.
	 */
	protected function get_comment_moderation_message( $comment_id, $time ) {
		/**
		 * Current comment object.
		 *
		 * @var object $comment
		 */
		$comment = get_comment( $comment_id );

		if ( null === $comment || '0' !== $comment->comment_approved ) {
			return '';
		}

		$message = $this->comment_message( $comment, $time );

		$actions = array(
			'view' => __( 'Permalink', 'digest' ),
		);

		if ( $this->user && user_can( $this->user, 'edit_comment' ) ) {
			$actions['approve'] = __( 'Approve', 'digest' );

			if ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = _x( 'Trash', 'verb', 'digest' );
			} else {
				$actions['delete'] = __( 'Delete', 'digest' );
			}
			$actions['spam'] = _x( 'Spam', 'verb', 'digest' );
		}

		if ( ! empty( $actions ) ) {
			$message .= '<p>' . $this->comment_action_links( $actions, $comment_id ) . '</p>';
		}

		return $message;
	}

	/**
	 * Get the new user notification message.
	 *
	 * @param int $user_id The user ID.
	 * @param int $time    The timestamp when the user signed up.
	 *
	 * @return string The new user notification message.
	 */
	protected function get_new_user_notification_message( $user_id, $time ) {
		$user = new WP_User( $user_id );

		if ( 0 === $user->ID ) {
			return '';
		}

		return sprintf(
			'<li>' . __( '%s (ID: %d) %s ago', 'digest' ) . '</li>',
			$user->display_name, $user->ID,
			human_time_diff( $time, current_time( 'timestamp' ) )
		);
	}

	/**
	 * Get the password change notification message.
	 *
	 * @param int $user_id The user ID.
	 * @param int $time    The timestamp when the user changed his password.
	 *
	 * @return string The password change notification message.
	 */
	protected function get_password_change_notification_message( $user_id, $time ) {
		$user = new WP_User( $user_id );

		if ( 0 === $user->ID ) {
			return '';
		}

		return sprintf(
			'<li>' . __( '%s (ID: %d) %s ago', 'digest' ) . '</li>',
			esc_html( $user->display_name ),
			absint( $user->ID ),
			human_time_diff( $time, current_time( 'timestamp' ) )
		);
	}

	/**
	 * Get the message for a successful core update.
	 *
	 * @param string $version The version WordPress was updated to.
	 * @param int    $time    The timestamp when the update happened.
	 *
	 * @return string The core update message.
	 */
	protected function get_core_update_success_message( $version, $time ) {
		$message = sprintf(
			'<p>' . __( 'Your site at <a href="%1$s">%2$s</a> has been updated automatically to WordPress %3$s %4$s ago.', 'digest' ) . '</p>',
			esc_url( home_url() ),
			esc_html( str_replace( array( 'http://', 'https://' ), '', home_url() ) ),
			esc_html( $version ),
			human_time_diff( $time, current_time( 'timestamp' ) )
		);

		// Can only reference the About screen if their update was successful.
		list( $about_version ) = explode( '-', $version, 2 );

		$message .= sprintf(
			'<p>' . __( 'For more on version %1$s, see the <a href="%2$s">About WordPress</a> screen.', 'digest' ) . '</p>',
			esc_html( $about_version ),
			esc_url( admin_url( 'about.php' ) )
		);

		return $message;
	}

	/**
	 * Get the message for a failed core update.
	 *
	 * @param string $version The version WordPress was updated to.
	 * @param int    $time    The timestamp when the update attempt happened.
	 *
	 * @return string The core update message.
	 */
	protected function get_core_update_fail_message( $version, $time ) {
		global $wp_version;

		// Check if WordPress hasn't already been updated.
		if ( version_compare( $wp_version, $version, '>=' ) ) {
			return '';
		}

		$message = sprintf(
			'<p>' . __( 'Please update your site at <a href="%1$s">%2$s</a> to WordPress %3$s. Updating is easy and only takes a few moments.', 'digest' ) . '</p>',
			esc_url( home_url() ),
			esc_html( str_replace( array( 'http://', 'https://' ), '', home_url() ) ),
			esc_html( $version ),
			human_time_diff( $time, current_time( 'timestamp' ) )
		);

		$message .= '<p>' . sprintf( '<a href="%s">%s</a>', network_admin_url( 'update-core.php' ), __( 'Update now', 'digest' ) ) . '</p>';

		return $message;
	}

	/**
	 * Get the message for an available core update that can't be installed automatically.
	 *
	 * @param string $version The version WordPress was updated to.
	 * @param int    $time    The timestamp when the update notification got in.
	 *
	 * @return string The core update message.
	 */
	protected function get_core_update_manual_message( $version, $time ) {
		return $this->get_core_update_fail_message( $version, $time );
	}

	/**
	 * Get the comment message.
	 *
	 * @param object $comment The comment object.
	 * @param int    $time    The timestamp when the comment was written.
	 *
	 * @return string The comment message.
	 */
	protected function comment_message( $comment, $time ) {
		$post_link = '<a href="' . esc_url( get_permalink( $comment->comment_post_ID ) ) . '">' . get_the_title( $comment->comment_post_ID ) . '</a>';

		$message = '';

		switch ( $comment->comment_type ) {
			case 'trackback':
				$message .= sprintf( __( 'Trackback on %1$s %2$s ago:', 'digest' ), $post_link, human_time_diff( $time, current_time( 'timestamp' ) ) ) . '<br />';
			case 'pingback':
				$message .= sprintf( __( 'Pingback on %1$s %2$s ago:', 'digest' ), $post_link, human_time_diff( $time, current_time( 'timestamp' ) ) ) . '<br />';
			case 'trackback':
			case 'pingback':
				$message .= sprintf( __( 'Website: %s', 'digest' ), '<a href="' . esc_url( $comment->comment_author_url ) . '">' . esc_html( $comment->comment_author ) . '</a>' ) . '<br />';
				$message .= sprintf( __( 'Excerpt: %s', 'digest' ), '<br />' . $this->comment_text( $comment->comment_ID ) );
				break;
			default: // Comments.
				$author = sprintf( __( 'Author: %s', 'digest' ), esc_html( $comment->comment_author ) );
				if ( ! empty( $comment->comment_author_url ) ) {
					$author = sprintf( __( 'Author: %s', 'digest' ), '<a href="' . esc_url( $comment->comment_author_url ) . '">' . esc_html( $comment->comment_author ) . '</a>' );
				}
				$message = sprintf( __( 'Comment on %1$s %2$s ago:', 'digest' ), $post_link, human_time_diff( $time, current_time( 'timestamp' ) ) ) . '<br />';
				$message .= $author . '<br />';
				$message .= sprintf( __( 'Email: %s', 'digest' ), '<a href="mailto:' . esc_attr( $comment->comment_author_email ) . '">' . esc_html( $comment->comment_author_email ) . '</a>' ) . '<br />';
				$message .= sprintf( __( 'Comment: %s', 'digest' ), '<br />' . $this->comment_text( $comment->comment_ID ) );
				break;
		}

		return $message;
	}

	/**
	 * Get the comment text, which is already filtered by WordPress.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return string The filtered comment text
	 */
	protected function comment_text( $comment_id ) {
		ob_start();

		comment_text( $comment_id );

		return ob_get_clean();
	}

	/**
	 * Add action links to the message
	 *
	 * @param array $actions    Actions for that comment.
	 * @param int   $comment_id The comment ID.
	 *
	 * @return string The comment action links.
	 */
	protected function comment_action_links( array $actions, $comment_id ) {
		$links = array();

		foreach ( $actions as $action => $label ) {
			$url = admin_url( sprintf( 'comment.php?action=%s&c=%d', $action, $comment_id ) );

			if ( 'view' === $action ) {
				$url = get_comment_link( $comment_id );
			}

			$links[] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $url ),
				esc_html( $label )
			);
		}

		return implode( ' | ', $links );
	}
}

add_action( 'digest_event', array( 'WP_Digest_Cron', 'init' ) );
