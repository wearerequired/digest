<?php
/**
 * This file holds the Core_Update_Message class.
 */

namespace Required\Digest\Message;

use WP_User;

/**
 * Core_Update_Message class.
 *
 * Responsible for creating the core update section
 */
class CoreUpdate extends Section {
	/**
	 * The event type.
	 *
	 * @since  2.0.0
	 *
	 * @var string
	 */
	protected $event;

	/**
	 * Constructor.
	 *
	 * @since  2.0.0
	 *
	 * @param array    $entries The core update entries.
	 * @param \WP_User|null $user The current user.
	 * @param string   $event   The current event.
	 */
	public function __construct( $entries, ?WP_User $user = null, $event ) {
		$this->event = $event;

		parent::__construct( $entries, $user );
	}

	/**
	 * Get core update section message.
	 *
	 * @return string The section message.
	 */
	public function get_message() {
		return implode( '', $this->entries );
	}

	/**
	 * Get the single core update/failure message.
	 *
	 * @param string $version The version WordPress was updated to.
	 * @param int    $time    The timestamp when the update happened.
	 * @return string The core update message.
	 */
	protected function get_single_message( $version, $time ) {
		if ( 'core_update_success' === $this->event ) {
			return $this->get_core_update_success_message( $version, $time );
		}

		return $this->get_core_update_fail_message( $version );
	}

	/**
	 * Get the message for a successful core update.
	 *
	 * @param string $version The version WordPress was updated to.
	 * @param int    $time    The timestamp when the update happened.
	 * @return string The core update message.
	 */
	protected function get_core_update_success_message( $version, $time ) {
		$message = sprintf(
			// translators: 1: Site name, 2: WP Version, 3: Humman time diff.
			'<p>' . __( 'Your site at %1$s has been updated automatically to WordPress %2$s %3$s ago.', 'digest' ) . '</p>',
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( home_url() ),
				esc_html( str_replace( [ 'http://', 'https://' ], '', home_url() ) )
			),
			esc_html( $version ),
			human_time_diff( $time, current_time( 'timestamp' ) )
		);

		// Can only reference the About screen if their update was successful.
		list( $about_version ) = explode( '-', $version, 2 );

		$message .= sprintf(
			// translators: 1: WP Version, 2: Link to about page.
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
	 * @return string The core update message.
	 */
	protected function get_core_update_fail_message( $version ) {
		global $wp_version;

		// Check if WordPress hasn't already been updated.
		if ( version_compare( $wp_version, $version, '>=' ) ) {
			return '';
		}

		$message = sprintf(
			// translators: 1: Site name, 2: WP Version.
			'<p>' . __( 'Please update your site at %1$s to WordPress %2$s. Updating is easy and only takes a few moments.', 'digest' ) . '</p>',
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( home_url() ),
				esc_html( str_replace( [ 'http://', 'https://' ], '', home_url() ) )
			),
			esc_html( $version )
		);

		$message .= '<p>' . sprintf( '<a href="%s">%s</a>', network_admin_url( 'update-core.php' ), __( 'Update now', 'digest' ) ) . '</p>';

		return $message;
	}
}
