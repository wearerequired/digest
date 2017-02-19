<?php
/**
 * Main plugin file.
 *
 * @package WP_Digest
 */

namespace Required\Digest;
use Required\Digest\Message\Comment_Moderation;
use Required\Digest\Message\Comment_Notification;
use Required\Digest\Message\Core_Update;
use Required\Digest\Message\Password_Change_Notification;
use Required\Digest\Message\User_Notification;

/**
 * WP_Digest_Plugin class.
 *
 * Responsible for adding the settings screen and
 * hooking into some WordPress functions for the notifications.
 */
class Plugin {
	/**
	 * Plugin version.
	 */
	const VERSION = '2.0.0-alpha';

	/**
	 * Registered events.
	 *
	 * @var array
	 */
	protected $registered_events = array();

	/**
	 * Returns the URL to the plugin directory
	 *
	 * @return string The URL to the plugin directory.
	 */
	protected function get_url() {
		return plugin_dir_url( __DIR__ );
	}

	/**
	 * Returns the path to the plugin directory.
	 *
	 * @return string The absolute path to the plugin directory.
	 */
	protected function get_path() {
		return plugin_dir_path( __DIR__ );
	}

	/**
	 * Adds hooks.
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Settings screen.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );

		// Add an action link pointing to the options page.
		add_action( 'plugin_action_links_' . plugin_basename( $this->get_path() ) . '/digest.php', array(
			$this,
			'plugin_action_links',
		) );

		// Hook into WordPress functions for the notifications.
		add_action( 'comment_notification_recipients', array( $this, 'comment_notification_recipients' ), 10, 2 );
		add_action( 'comment_notification_recipients', array( $this, 'comment_notification_recipients' ), 10, 2 );
		add_action( 'auto_core_update_email', array( $this, 'auto_core_update_email' ), 10, 3 );

		add_action( 'init', array( $this, 'register_default_events' ) );
	}

	public function register_event( $event, $callback = null ) {
		if ( $this->is_registered_event( $event ) ) {
			return;
		}

		$this->registered_events[] = $event;

		if ( null !== $callback ) {
			add_filter( 'digest_message_section_' . $event, $callback, 10, 9999 );
		}
	}

	public function is_registered_event( $event ) {
		return in_array( $event, $this->registered_events, true);
	}

	public function get_registered_events() {
		return $this->registered_events;
	}

	public function register_default_events() {
		// Register default events.
		$this->register_event( 'core_update_success', function ( $content, $entries, $user, $event ) {
			$message = new Core_Update( $entries, $user, $event );

			if ( '' === $content ) {
				$content = '<p><b>' . __( 'Core Updates', 'digest' ) . '</b></p>';
			}

			return $content . $message->get_message();
		} );

		$this->register_event( 'core_update_failure', function ( $content, $entries, $user, $event ) {
			$message = new Core_Update( $entries, $user, $event );

			if ( '' === $content ) {
				$content = '<p><b>' . __( 'Core Updates', 'digest' ) . '</b></p>';
			}

			return $content . $message->get_message();
		} );

		$this->register_event( 'comment_moderation', function ( $content, $entries, $user ) {
			$message = new Comment_Moderation( $entries, $user );

			return $content . $message->get_message();
		} );

		$this->register_event( 'comment_notification', function ( $content, $entries, $user ) {
			$message = new Comment_Notification( $entries, $user );

			return $content . $message->get_message();
		} );

		if ( in_array( 'new_user_notification', get_option( 'digest_hooks' ), true ) ) {
			$this->register_event( 'new_user_notification', function ( $content, $entries, $user ) {
				$message = new User_Notification( $entries, $user );

				return $content . $message->get_message();
			} );
		}

		if ( in_array( 'password_change_notification', get_option( 'digest_hooks' ), true ) ) {
			$this->register_event( 'password_change_notification', function ( $content, $entries, $user ) {
				$message = new Password_Change_Notification( $entries, $user );

				return $content . $message->get_message();
			} );
		}

		/**
		 * Fired after registering the default events.
		 */
		do_action( 'digest_register_events' );
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
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( 'options-general.php' === $hook_suffix ) {
			$suffix = SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'digest', $this->get_url() . 'js/digest' . $suffix . '.js', array(), self::VERSION, true );
			wp_enqueue_style( 'digest', $this->get_url() . 'css/digest' . $suffix . '.css', array(), self::VERSION );
		}
	}

	/**
	 * Create settings sections and fields.
	 */
	public function add_settings() {
		add_settings_section(
			'digest_notifications',
			__( 'Email Notifications', 'digest' ),
			function () {
				esc_html_e( "You get a daily or weekly digest of what's happening on your site. Here you can configure its frequency.", 'digest' );
			},
			'general'
		);

		add_settings_field(
			'digest_frequency',
			sprintf( '<label for="digest_frequency_period" id="digest">%s</label>', __( 'Frequency', 'digest' ) ),
			array( $this, 'settings_field_frequency' ),
			'general',
			'digest_notifications'
		);

		register_setting( 'general', 'digest_frequency', array( $this, 'sanitize_frequency_option' ) );
	}

	/**
	 * Settings field callback that prints the actual input fields.
	 */
	public function settings_field_frequency() {
		$options     = get_option( 'digest_frequency', array(
			'period' => 'weekly',
			'hour'   => 18,
			'day'    => absint( get_option( 'start_of_week' ) ),
		) );
		$time_format = get_option( 'time_format' );
		?>
		<p>
			<?php esc_html_e( 'Send me a digest of new site activity', 'digest' ); ?>
			<select name="digest_frequency[period]" id="digest_frequency_period">
				<option value="daily" <?php selected( 'daily', $options['period'] ); ?>>
					<?php echo esc_attr_x( 'every day', 'frequency', 'digest' ); ?>
				</option>
				<option value="weekly" <?php selected( 'weekly', $options['period'] ); ?>>
					<?php echo esc_attr_x( 'every week', 'frequency', 'digest' ); ?>
				</option>
			</select>
			<span id="digest_frequency_hour_wrapper">
				<?php esc_html_e( 'at', 'digest' ); ?>
				<select name="digest_frequency[hour]" id="digest_frequency_hour">
					<?php for ( $hour = 0; $hour <= 23; $hour ++ ) : ?>
						<option value="<?php echo esc_attr( $hour ); ?>" <?php selected( $hour, $options['hour'] ); ?>>
							<?php echo esc_html( date( $time_format, mktime( $hour, 0, 0, 1, 1, 2011 ) ) ); ?>
						</option>
					<?php endfor; ?>
				</select>
				<?php esc_html_e( "o'clock", 'digest' ); ?>
			</span>
			<span id="digest_frequency_day_wrapper" <?php echo 'weekly' !== $options['period'] ? 'class="digest-hidden"' : ''; ?>>
				<?php
				esc_html_e( 'on', 'digest' );

				global $wp_locale;
				?>
				<select name="digest_frequency[day]" id="digest_frequency_day">
					<?php for ( $day_index = 0; $day_index <= 6; $day_index ++ ) : ?>
						<option value="<?php echo esc_attr( $day_index ); ?>" <?php selected( $day_index, $options['day'] ); ?>>
							<?php echo esc_html( $wp_locale->get_weekday( $day_index ) ); ?>
						</option>
					<?php endfor; ?>
				</select>
			</span>
		</p>
		<?php
	}

	/**
	 * Sanitize the digest frequency option.
	 *
	 * @param array $value The POST da.
	 *
	 * @return array The sanitized frequency option.
	 */
	public function sanitize_frequency_option( array $value ) {
		if ( 'daily' !== $value['period'] ) {
			$value['period'] = 'weekly';
		}

		$value['hour'] = filter_var(
			$value['hour'],
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'default'   => 18,
					'min_range' => 0,
					'max_range' => 23,
				),
			)
		);

		$value['day'] = filter_var(
			$value['day'],
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'default'   => get_option( 'start_of_week', 0 ),
					'min_range' => 0,
					'max_range' => 6,
				),
			)
		);

		return $value;
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @param array $links Plugin action links.
	 *
	 * @return array The modified plugin action links
	 */
	public function plugin_action_links( array $links ) {
		return array_merge(
			array(
				'settings' => sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'options-general.php#digest' ) ),
					__( 'Settings', 'digest' )
				),
			),
			$links
		);
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
	 * @see WP_Upgrader::send_email()
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
		$next_user_core_update = get_preferred_from_update_core();

		// If the update transient is empty, use the update we just performed.
		if ( ! $next_user_core_update ) {
			$next_user_core_update = $core_update;
		}

		// If the auto update is not to the latest version, say that the current version of WP is available instead.
		$version = 'success' === $type ? $core_update->current : $next_user_core_update->current;

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
			 * @param string $digest   The message to be sent.
			 * @param string $recipient The recipient's email address.
			 */
			$digest = apply_filters( 'digest_cron_email_message', $digest->get_message(), $recipient );

			// Send digest.
			wp_mail( $recipient, $subject, $digest, array( 'Content-Type: text/html; charset=UTF-8' ) );
		}
	}
}
