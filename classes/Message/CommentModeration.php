<?php
/**
 * Comment moderation class.
 *
 * @package Digest
 */

namespace Required\Digest\Message;

use WP_Comment;
use WP_User;

/**
 * Comment moderation message class.
 *
 * Responsible for creating the comment moderation section.
 *
 * @since 2.0.0
 */
class CommentModeration extends Section {
	/**
	 * Returns the comment moderation section message.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @return string The section message.
	 */
	public function get_message() {
		$processed_count = count( $this->entries ) - count( array_filter( $this->entries ) );

		$message = '<p><b>' . __( 'Pending Comments', 'digest' ) . '</b></p>';
		$message .= '<p>';
		$message .= sprintf(
			_n(
				'There is %s new comment waiting for approval.',
				'There are %s new comments waiting for approval.',
				count( $this->entries ),
				'digest'
			),
			number_format_i18n( count( $this->entries ) )
		);
		if ( $processed_count > 0 ) {
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
	 * Returns the comment moderation message.
	 *
	 * @since  2.0.0
	 * @access protected
	 *
	 * @param int $comment The comment ID.
	 * @param int $time    The timestamp when the comment was written.
	 *
	 * @return string The comment moderation message.
	 */
	protected function get_single_message( $comment, $time ) {
		$comment = get_comment( $comment );

		if ( null === $comment || '0' !== $comment->comment_approved ) {
			return '';
		}

		$message = $this->get_single_comment_content( $comment, $time );

		$actions = array(
			'view' => __( 'Permalink', 'digest' ),
		);

		if ( $this->user_can_edit_comment( $comment->comment_ID ) ) {
			$actions['approve'] = __( 'Approve', 'digest' );

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

	/**
	 * Returns the message for a single comment.
	 *
	 * @since  2.0.0
	 * @access protected
	 *
	 * @param WP_Comment $comment The comment object.
	 * @param int        $time    The timestamp when the comment was written.
	 *
	 * @return string The comment message.
	 */
	protected function get_single_comment_content( WP_Comment $comment, $time ) {
		$post_link = '<a href="' . esc_url( get_permalink( $comment->comment_post_ID ) ) . '">' . get_the_title( $comment->comment_post_ID ) . '</a>';

		$message = '';

		switch ( $comment->comment_type ) {
			case 'trackback':
			case 'pingback':
				if ( 'pingback' === $comment->comment_type ) {
					$message .= sprintf( __( 'Pingback on %1$s %2$s ago:', 'digest' ), $post_link, human_time_diff( $time, current_time( 'timestamp' ) ) ) . '<br />';
				} else {
					$message .= sprintf( __( 'Trackback on %1$s %2$s ago:', 'digest' ), $post_link, human_time_diff( $time, current_time( 'timestamp' ) ) ) . '<br />';
				}
				$message .= sprintf( __( 'Website: %s', 'digest' ), '<a href="' . esc_url( $comment->comment_author_url ) . '">' . esc_html( $comment->comment_author ) . '</a>' ) . '<br />';
				$message .= sprintf( __( 'Excerpt: %s', 'digest' ), '<br />' . $this->get_comment_text( $comment->comment_ID ) );
				break;
			default: // Comments.
				$author = sprintf( __( 'Author: %s', 'digest' ), esc_html( $comment->comment_author ) );
				if ( $comment->comment_author_url ) {
					$author = sprintf( __( 'Author: %s', 'digest' ), '<a href="' . esc_url( $comment->comment_author_url ) . '">' . esc_html( $comment->comment_author ) . '</a>' );
				}
				$message = sprintf( __( 'Comment on %1$s %2$s ago:', 'digest' ), $post_link, human_time_diff( $time, current_time( 'timestamp' ) ) ) . '<br />';
				$message .= $author . '<br />';
				if ( $comment->comment_author_email ) {
					$message .= sprintf( __( 'Email: %s', 'digest' ), '<a href="mailto:' . esc_attr( $comment->comment_author_email ) . '">' . esc_html( $comment->comment_author_email ) . '</a>' ) . '<br />';
				}
				$message .= sprintf( __( 'Comment: %s', 'digest' ), '<br />' . $this->get_comment_text( $comment->comment_ID ) );
				break;
		}

		return $message;
	}

	/**
	 * Returns the comment text.
	 *
	 * It is already filtered by WordPress.
	 *
	 * @since  2.0.0
	 * @access protected
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @return string The filtered comment text
	 */
	protected function get_comment_text( $comment_id ) {
		$comment = get_comment( $comment_id );

		$comment_text = get_comment_text( $comment );

		/** This filter is documented in wp-includes/comment-template.php */
		return apply_filters( 'comment_text', $comment_text, $comment, array() );
	}

	/**
	 * Adds action links to the message.
	 *
	 * @since  2.0.0
	 * @access protected
	 *
	 * @param array      $actions Actions for that comment.
	 * @param WP_Comment $comment The comment object.
	 *
	 * @return string The comment action links.
	 */
	protected function get_comment_action_links( array $actions, WP_Comment $comment ) {
		$links = array();

		foreach ( $actions as $action => $label ) {
			$url = admin_url( sprintf( 'comment.php?action=%s&c=%d', $action, $comment->comment_ID ) );

			if ( 'view' === $action ) {
				$url = get_comment_link( $comment );
			}

			$links[] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $url ),
				esc_html( $label )
			);
		}

		return implode( ' | ', $links );
	}

	/**
	 * Whether the current user can edit a given comment or not.
	 *
	 * @since  2.0.0
	 * @access protected
	 *
	 * @param int $comment_id Comment ID.
	 *
	 * @return bool True if the user can edit the comment, false otherwise.
	 */
	protected function user_can_edit_comment( $comment_id ) {
		return $this->user && ( user_can( $this->user, 'edit_comment', $comment_id ) || get_option( 'admin_email' ) === $this->user->user_email );
	}
}
