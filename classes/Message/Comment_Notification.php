<?php
/**
 * This file holds the Comment_Notification_Message class.
 *
 * @package WP_Digest
 */

namespace Required\Digest\Message;

/**
 * Comment_Notification_Message class.
 *
 * Responsible for creating the comment notification section
 */
class Comment_Notification extends Comment_Moderation {
	/**
	 * Constructor.
	 *
	 * @param array    $entries The comment moderation entries.
	 * @param \WP_User $user    The current user.
	 */
	public function __construct( $entries, \WP_User $user ) {
		parent::__construct( $entries, $user );

		$this->entries = array();
		foreach ( $entries as $comment => $time ) {
			$this->entries[] = $this->get_single_message( get_comment( $comment ), $time );
		}
	}

	/**
	 * Get comment moderation section message.
	 *
	 * @return string The section message.
	 */
	public function get_message() {
		$processed_count = count( $this->entries ) - count( array_filter( $this->entries ) );

		$message = '<p><b>' . __( 'New Comments', 'digest' ) . '</b></p>';
		$message .= '<p>';
		$message .= sprintf(
			_n(
				'There was %s new comment.',
				'There were %s new comments.',
				count( $this->entries ),
				'digest'
			),
			number_format_i18n( count( $this->entries ) )
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
		$message .= implode( '', $this->entries );
		$message .= sprintf(
			'<p>' . __( 'Please visit the <a href="%s">moderation panel</a>.', 'digest' ) . '</p>',
			admin_url( 'edit-comments.php?comment_status=moderated' )
		);

		return $message;
	}

	/**
	 * Get the comment moderation message.
	 *
	 * @param \WP_Comment $comment The comment object.
	 * @param int         $time    The timestamp when the comment was written.
	 *
	 * @return string The comment moderation message.
	 */
	protected function get_single_message( \WP_Comment $comment, $time ) {
		if ( null === $comment ) {
			return '';
		}

		$message = $this->get_single_comment_content( $comment, $time );

		$actions = array(
			'view' => __( 'Permalink', 'digest' ),
		);

		if ( $this->user && user_can( $this->user, 'edit_comment' ) || $this->user && get_option( 'admin_email' ) === $this->user->user_email ) {
			if ( defined( 'EMPTY_TRASH_DAYS' ) && EMPTY_TRASH_DAYS ) {
				$actions['trash'] = _x( 'Trash', 'verb', 'digest' );
			} else {
				$actions['delete'] = __( 'Delete', 'digest' );
			}
			$actions['spam'] = _x( 'Spam', 'verb', 'digest' );
		}

		if ( ! empty( $actions ) ) {
			$message .= '<p>' . $this->get_comment_action_links( $actions, $comment ) . '</p>';
		}

		return $message;
	}
}
