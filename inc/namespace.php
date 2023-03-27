<?php
/**
 * Common Digest Functions.
 */

namespace Required\Digest;

use Required\Digest\Event\Registry;
use Required\Digest\Message\CommentModeration;
use Required\Digest\Message\CommentNotification;
use Required\Digest\Message\CoreUpdate;
use Required\Digest\Message\PasswordChangeNotification;
use Required\Digest\Message\UserNotification;
use Required\Digest\Setting\FrequencySetting;

define( __NAMESPACE__ . '\PLUGIN_FILE', dirname( __DIR__ ) . '/digest.php' );
define( __NAMESPACE__ . '\PLUGIN_DIR', dirname( __DIR__ ) );
define( __NAMESPACE__ . '\VERSION', '3.0.0' );

/**
 * Initializes the plugin.
 */
function bootstrap() {
	$frequency_setting = new FrequencySetting();
	add_action( 'init', [ $frequency_setting, 'register' ] );

	// Hook into WordPress functions for the notifications.
	add_action( 'comment_notification_recipients', __NAMESPACE__ . '\\comment_notification_recipients', 10, 2 );
	add_action( 'comment_moderation_recipients', __NAMESPACE__ . '\\comment_moderation_recipients', 10, 2 );
	add_action( 'auto_core_update_email', __NAMESPACE__ . '\\auto_core_update_email', 10, 3 );

	add_action( 'init', __NAMESPACE__ . '\\register_default_events' );

	add_action( 'digest_event', [ Cron::class, 'init' ] );

	register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate_plugin' );
	register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate_plugin' );
}

/**
 * Schedule our cronjob on plugin activation.
 */
function activate_plugin() {
	// Get timestamp of the next full hour.
	$current_time = time();
	$timestamp    = $current_time + ( 3600 - ( ( gmdate( 'i', $current_time ) * 60 ) + gmdate( 's', $current_time ) ) );

	wp_clear_scheduled_hook( 'digest_event' );
	wp_schedule_event( $timestamp, 'hourly', 'digest_event' );
}

/**
 * Unschedule our cronjob on plugin deactivation.
 */
function deactivate_plugin() {
	wp_unschedule_event( wp_next_scheduled( 'digest_event' ), 'digest_event' );
}

/**
 * Hook into the new comment notification to add the comment to the queue.
 *
 * @param string[] $emails     An array of email addresses to receive a comment notification.
 * @param int      $comment_id The comment ID.
 * @return array An empty array to prevent sending an email directly.
 */
function comment_notification_recipients( $emails, $comment_id ) {
	$comment = get_comment( $comment_id );
	$post    = get_post( $comment->comment_post_ID );
	$author  = get_userdata( $post->post_author );

	/** This filters is documented in wp-includes/pluggable.php */
	$notify_author = apply_filters( 'comment_notification_notify_author', false, $comment_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	$skipped = [];

	if ( $author && ! $notify_author ) {
		// The comment was left by the author.
		if ( $comment->user_id === $post->post_author ) {
			$skipped[] = $author->user_email;
		}

		// The author moderated a comment on their own post.
		if ( get_current_user_id() === (int) $post->post_author ) {
			$skipped[] = $author->user_email;
		}

		// The post author is no longer a member of the blog.
		if ( ! user_can( $post->post_author, 'read_post', $post->ID ) ) {
			$skipped[] = $author->user_email;
		}
	}

	foreach ( $emails as $recipient ) {
		if ( ! \in_array( $recipient, $skipped, true ) ) {
			Queue::add( $recipient, 'comment_notification', $comment_id );
		}
	}

	return [];
}

/**
 * Hook into the comment moderation notification to add the comment to the queue.
 *
 * @param string[] $emails     An array of email addresses to receive a comment notification.
 * @param int      $comment_id The comment ID.
 * @return array An empty array to prevent sending an email directly.
 */
function comment_moderation_recipients( $emails, $comment_id ) {
	foreach ( $emails as $recipient ) {
		Queue::add( $recipient, 'comment_moderation', $comment_id );
	}

	return [];
}

/**
 * Add core update notifications to our queue.
 *
 * This is only done when the update failed or was successful.
 * If there was a critical error, WordPress should still send the email immediately.
 *
 * @see WP_Automatic_Updater::send_email()
 *
 * @param array     $email       {
 *                               Array of email arguments that will be passed to wp_mail().
 *
 * @type string    $to          The email recipient. An array of emails
 *                              can be returned, as handled by wp_mail().
 * @type string    $subject     The email's subject.
 * @type string    $body        The email message body.
 * @type string    $headers     Any email headers, defaults to no headers.
 * }
 * @param string    $type        The type of email being sent. Can be one of
 *                               'success', 'fail', 'manual', 'critical'.
 * @param \stdClass $core_update The update offer that was attempted.
 * @return array The modified $email array without a recipient.
 */
function auto_core_update_email( array $email, $type, $core_update ) {
	$next_core_update = get_preferred_from_update_core();

	// If the update transient is empty, use the update we just performed.
	if ( ! $next_core_update ) {
		$next_core_update = $core_update;
	}

	// If the auto update is not to the latest version, say that the current version of WP is available instead.
	$version = 'success' === $type ? $core_update->current : $next_core_update->current;

	if ( \in_array( $type, [ 'success', 'fail', 'manual' ], true ) ) {
		Queue::add( get_site_option( 'admin_email' ), 'core_update_' . $type, $version );
		$email['to'] = [];
	}

	return $email;
}

/**
 * Registers all the default events.
 *
 * @return void
 */
function register_default_events() {
	// Register default events.
	Registry::register_event(
		'core_update_success',
		function ( $content, $entries, $user, $event ) {
			$message = new CoreUpdate( $entries, $user, $event );

			if ( '' === $content ) {
				$content = '<p><b>' . __( 'Core Updates', 'digest' ) . '</b></p>';
			}

			return $content . $message->get_message();
		}
	);

	Registry::register_event(
		'core_update_failure',
		function ( $content, $entries, $user, $event ) {
			$message = new CoreUpdate( $entries, $user, $event );

			if ( '' === $content ) {
				$content = '<p><b>' . __( 'Core Updates', 'digest' ) . '</b></p>';
			}

			return $content . $message->get_message();
		}
	);

	Registry::register_event(
		'comment_moderation',
		function ( $content, $entries, $user ) {
			$message = new CommentModeration( $entries, $user );

			return $content . $message->get_message();
		}
	);

	Registry::register_event(
		'comment_notification',
		function ( $content, $entries, $user ) {
			$message = new CommentNotification( $entries, $user );

			return $content . $message->get_message();
		}
	);

	if ( \in_array( 'new_user_notification', get_option( 'digest_hooks' ), true ) ) {
		Registry::register_event(
			'new_user_notification',
			function ( $content, $entries, $user ) {
				$message = new UserNotification( $entries, $user );

				return $content . $message->get_message();
			}
		);
	}

	if ( \in_array( 'password_change_notification', get_option( 'digest_hooks' ), true ) ) {
		Registry::register_event(
			'password_change_notification',
			function ( $content, $entries, $user ) {
				$message = new PasswordChangeNotification( $entries, $user );

				return $content . $message->get_message();
			}
		);
	}

	/**
	 * Fires after registering the default events.
	 *
	 * @since 2.0.0
	 */
	do_action( 'digest_register_events' );
}

/**
 * Sends the scheduled email to all the recipients in the digest queue.
 *
 * @param string $subject Email subject.
 */
function send_email( $subject ) {
	$queue = Queue::get();

	if ( empty( $queue ) ) {
		return;
	}

	// Loop through the queue.
	foreach ( $queue as $recipient => $items ) {
		$digest = new Plugin( $recipient, $items );

		/**
		 * Filter the digest message.
		 *
		 * @since 1.0.0
		 *
		 * @param string $digest    The message to be sent.
		 * @param string $recipient The recipient's email address.
		 */
		$digest = apply_filters( 'digest_cron_email_message', $digest->get_message(), $recipient );

		// Send digest.
		wp_mail( $recipient, $subject, $digest, [ 'Content-Type: text/html; charset=UTF-8' ] );
	}
}
