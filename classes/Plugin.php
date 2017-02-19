<?php
/**
 * Main plugin file.
 *
 * @package Digest
 */

namespace Required\Digest;

use Required\Digest\Event\RegistryInterface;
use Required\Digest\Setting\FrequencySetting;

/**
 * WP_Digest_Plugin class.
 *
 * Responsible for adding the settings screen and
 * hooking into some WordPress functions for the notifications.
 */
class Plugin {
	/**
	 * Plugin version.
	 *
	 * @since 2.0.0
	 */
	const VERSION = '2.0.0-alpha';

	/**
	 * Event registry.
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @var RegistryInterface
	 */
	protected $event_registry;

	/**
	 * Plugin constructor.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @param RegistryInterface $event_registry The event registry to use.
	 */
	public function __construct( RegistryInterface $event_registry ) {
		$this->event_registry = $event_registry;
	}

	/**
	 * Returns the digest event registry.
	 *
	 * @return RegistryInterface
	 */
	public function event_registry() {
		return $this->event_registry;
	}

	/**
	 * Adds hooks.
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		$frequency_setting = new FrequencySetting();
		add_action( 'init', array( $frequency_setting, 'register' ) );

		// Hook into WordPress functions for the notifications.
		add_action( 'comment_notification_recipients', array( $this, 'comment_notification_recipients' ), 10, 2 );
		add_action( 'comment_notification_recipients', array( $this, 'comment_notification_recipients' ), 10, 2 );
		add_action( 'auto_core_update_email', array( $this, 'auto_core_update_email' ), 10, 3 );

		add_action( 'init', array( $this->event_registry(), 'register_default_events' ) );
	}

	/**
	 * Initializes the plugin, registers textdomain, etc.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'user-feedback' );
	}

	/**
	 * Schedule our cronjob on plugin activation.
	 */
	public function activate_plugin() {
		// Get timestamp of the next full hour.
		$current_time = current_time( 'timestamp' );
		$timestamp    = $current_time + ( 3600 - ( ( date( 'i', $current_time ) * 60 ) + date( 's', $current_time ) ) );

		wp_clear_scheduled_hook( 'digest_event' );
		wp_schedule_event( $timestamp, 'hourly', 'digest_event' );
	}

	/**
	 * Unschedule our cronjob on plugin deactivation.
	 */
	public function deactivate_plugin() {
		wp_unschedule_event( wp_next_scheduled( 'digest_event' ), 'digest_event' );
	}

	/**
	 * Hook into the new comment notification to add the comment to the queue.
	 *
	 * @SuppressWarnings(PHPMD)
	 *
	 * @param string[] $emails     An array of email addresses to receive a comment notification.
	 * @param int      $comment_id The comment ID.
	 *
	 * @return array An empty array to prevent sending an email directly.
	 */
	public function comment_notification_recipients( $emails, $comment_id ) {
		$comment = get_comment( $comment_id );
		$post    = get_post( $comment->comment_post_ID );
		$author  = get_userdata( $post->post_author );

		/**
		 * Filter whether to notify comment authors of their comments on their own posts.
		 *
		 * By default, comment authors aren't notified of their comments on their own
		 * posts. This filter allows you to override that.
		 *
		 * @param bool $notify     Whether to notify the post author of their own comment.
		 *                         Default false.
		 * @param int  $comment_id The comment ID.
		 */
		$notify_author = apply_filters( 'comment_notification_notify_author', false, $comment_id );

		// The comment was left by the author.
		if ( $author && ! $notify_author && $comment->user_id === $post->post_author ) {
			unset( $emails[ $author->user_email ] );
		}

		// The author moderated a comment on their own post.
		if ( $author && ! $notify_author && get_current_user_id() === $post->post_author ) {
			unset( $emails[ $author->user_email ] );
		}

		// The post author is no longer a member of the blog.
		if ( $author && ! $notify_author && ! user_can( $post->post_author, 'read_post', $post->ID ) ) {
			unset( $emails[ $author->user_email ] );
		}

		foreach ( $emails as $recipient ) {
			Queue::add( $recipient, 'comment_notification', $comment_id );
		}

		return array();
	}

	/**
	 * Hook into the comment moderation notification to add the comment to the queue.
	 *
	 * @param string[] $emails     An array of email addresses to receive a comment notification.
	 * @param int      $comment_id The comment ID.
	 *
	 * @return array An empty array to prevent sending an email directly.
	 */
	public function comment_moderation_recipients( $emails, $comment_id ) {
		foreach ( $emails as $recipient ) {
			Queue::add( $recipient, 'comment_moderation', $comment_id );
		}

		return array();
	}

	/**
	 * Add core update notifications to our queue.
	 *
	 * This is only done when the update failed or was successful.
	 * If there was a critical error, WordPress should still send the email immediately.
	 *
	 * @see WP_Automatic_Updater::send_email()
	 *
	 * @param array  $email       {
	 *                            Array of email arguments that will be passed to wp_mail().
	 *
	 * @type string  $to          The email recipient. An array of emails
	 *                            can be returned, as handled by wp_mail().
	 * @type string  $subject     The email's subject.
	 * @type string  $body        The email message body.
	 * @type string  $headers     Any email headers, defaults to no headers.
	 * }
	 *
	 * @param string $type        The type of email being sent. Can be one of
	 *                            'success', 'fail', 'manual', 'critical'.
	 * @param object $core_update The update offer that was attempted.
	 *
	 * @return array The modified $email array without a recipient.
	 */
	public function auto_core_update_email( array $email, $type, $core_update ) {
		$next_core_update = get_preferred_from_update_core();

		// If the update transient is empty, use the update we just performed.
		if ( ! $next_core_update ) {
			$next_core_update = $core_update;
		}

		// If the auto update is not to the latest version, say that the current version of WP is available instead.
		$version = 'success' === $type ? $core_update->current : $next_core_update->current;

		if ( in_array( $type, array( 'success', 'fail', 'manual' ) ) ) {
			Queue::add( get_site_option( 'admin_email' ), 'core_update_' . $type, $version );
			$email['to'] = array();
		}

		return $email;
	}

	/**
	 * Sends the scheduled email to all the recipients in the digest queue.
	 *
	 * @param string $subject Email subject.
	 */
	public function send_email( $subject ) {
		$queue = Queue::get();

		if ( empty( $queue ) ) {
			return;
		}

		// Loop through the queue.
		foreach ( $queue as $recipient => $items ) {
			$digest = new Digest( $recipient, $items );

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
			wp_mail( $recipient, $subject, $digest, array( 'Content-Type: text/html; charset=UTF-8' ) );
		}
	}
}
