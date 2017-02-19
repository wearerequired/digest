<?php
/**
 * WP Digest Cron implementation.
 *
 * @package WP_Digest
 */

namespace Required\Digest;

/**
 * Cron class.
 *
 * It's run every hour.
 */
class Cron {
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

		self::ready() && self::run();
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
	 * Run Boy Run
	 */
	protected static function run() {
		if ( empty( Queue::get() ) ) {
			return;
		}

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

		digest()->send_email( $subject );

		// Clear queue.
		Queue::clear();
	}
}
