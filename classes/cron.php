<?php
/**
 * WP Digest Cron implementation.
 *
 * @package WP_Digest
 */

defined( 'WPINC' ) or die;

if ( ! defined( 'EMPTY_TRASH_DAYS' ) ) {
	define( 'EMPTY_TRASH_DAYS', 30 );
}

/**
 * WP_Digest_Cron class.
 *
 * It's run every hour.
 */
class WP_Digest_Cron {
	/**
	 * The plugin options.
	 *
	 * @var array The plugin options.
	 */
	protected static $options;

	/**
	 * This method hooks to the cron action to process the queue.
	 */
	public static function init() {
		self::$options = get_option( 'digest_frequency', array(
			'period' => 'weekly',
			'hour'   => 18,
			'day'    => absint( get_option( 'start_of_week' ) ),
		) );

		if ( self::ready() ) {
			self::load_globals();
			self::run();
		}
	}

	/**
	 * Checks if it's already time to send the emails.
	 *
	 * @return bool True if the queue can be processed, false otherwise.
	 */
	protected static function ready() {
		// Return early if the hour is wrong.
		if ( absint( self::$options['hour'] ) !== absint( date_i18n( 'G' ) ) ) {
			return false;
		}

		// Return early if the day is wrong.
		if (
			'weekly' === self::$options['period'] &&
			absint( self::$options['day'] ) !== absint( date_i18n( 'w' ) )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Load required files and set up needed globals.
	 */
	protected static function load_globals() {
		// Load WP_Locale and other needed functions.
		require_once( ABSPATH . WPINC . '/pluggable.php' );
		require_once( ABSPATH . WPINC . '/locale.php' );
		require_once( ABSPATH . WPINC . '/rewrite.php' );
		$GLOBALS['wp_locale']  = new WP_Locale();
		$GLOBALS['wp_rewrite'] = new WP_Rewrite();
	}

	/**
	 * Run Boy Run
	 */
	protected static function run() {
		$queue = WP_Digest_Queue::get();

		if ( empty( $queue ) ) {
			return;
		}

		require_once( dirname( __FILE__ ) . '/message.php' );

		// Set up the correct subject.
		$subject = ( 'daily' === self::$options['period'] ) ? __( 'Today on %s', 'digest' ) : __( 'Past Week on %s', 'digest' );

		/**
		 * Filter the digest subject.
		 *
		 * @param string $subject The digest's subject line.
		 *
		 * @return string The filtered subject.
		 */
		$subject = apply_filters( 'digest_cron_email_subject', sprintf( $subject, get_bloginfo( 'name' ) ) );

		// Loop through the queue.
		foreach ( $queue as $recipient => $items ) {
			$message = new WP_Digest_Message( $recipient, $items );

			/**
			 * Filter the digest message.
			 *
			 * @param string $message   The message to be sent.
			 * @param string $recipient The recipient's email address.
			 *
			 * @return string The filtered message.
			 */
			$message = apply_filters( 'digest_cron_email_message', $message->get_message(), $recipient );

			// Send digest.
			wp_mail( $recipient, $subject, $message, array( 'Content-Type: text/html; charset=UTF-8' ) );
		}

		// Clear queue.
		WP_Digest_Queue::clear();
	}
}

add_action( 'digest_event', array( 'WP_Digest_Cron', 'init' ) );
