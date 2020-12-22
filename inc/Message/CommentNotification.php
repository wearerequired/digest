<?php
/**
 * This file holds the Comment_Notification_Message class.
 */

namespace Required\Digest\Message;

/**
 * Comment_Notification_Message class.
 *
 * Responsible for creating the comment notification section
 */
class CommentNotification extends CommentModeration {
	/**
	 * Get comment moderation section message.
	 *
	 * @return string The section message.
	 */
	public function get_message() {
		$processed_count = \count( $this->entries ) - \count( array_filter( $this->entries ) );

		$message  = '<p><b>' . __( 'New Comments', 'digest' ) . '</b></p>';
		$message .= '<p>';
		$message .= sprintf(
			// translators: %s: Number of comments.
			_n(
				'There was %s new comment.',
				'There were %s new comments.',
				\count( $this->entries ),
				'digest'
			),
			number_format_i18n( \count( $this->entries ) )
		);
		if ( 0 < $processed_count ) {
			$message .= ' ';
			$message .= sprintf(
				// translators: %s: Number of comments.
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
			// translators: %s: URL for moderation page.
			'<p>' . __( 'Please visit the <a href="%s">moderation panel</a>.', 'digest' ) . '</p>',
			admin_url( 'edit-comments.php?comment_status=moderated' )
		);

		return $message;
	}

	/**
	 * Get the comment moderation message.
	 *
	 * @param int $comment The comment ID.
	 * @param int $time    The timestamp when the comment was written.
	 * @return string The comment moderation message.
	 */
	protected function get_single_message( $comment, $time ) {
		/* @var WP_Comment $comment */
		$comment = get_comment( $comment );

		if ( null === $comment ) {
			return '';
		}

		$message = $this->get_single_comment_content( $comment, $time );

		$actions = [
			'view' => __( 'Permalink', 'digest' ),
		];

		if ( $this->user_can_edit_comment( $comment->comment_ID ) ) {
			if ( \defined( 'EMPTY_TRASH_DAYS' ) && EMPTY_TRASH_DAYS ) {
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
