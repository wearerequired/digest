<?php
/**
 * Implement pluggable functions so we can add those emails to the queue.
 *
 * If it works and the functions haven't been declared yet,
 * we store that information in an option.
 * This way the plugin knows which hooks work and which don't.
 *
 * @package Digest
 */

use Required\Digest\Queue;

global $wp_version;

$enabled = array();

if ( ! function_exists( 'wp_new_user_notification' ) ) {
	$enabled[] = 'new_user_notification';

	/**
	 * Email login credentials to a newly-registered user.
	 *
	 * A new user registration notification is also sent to admin email.
	 *
	 * @since 2.0.0
	 * @since 4.3.0 The `$plaintext_pass` parameter was changed to `$notify`.
	 * @since 4.3.1 The `$plaintext_pass` parameter was deprecated. `$notify` added as a third parameter.
	 *
	 * @global wpdb         $wpdb       WordPress database object for queries.
	 * @global PasswordHash $wp_hasher  Portable PHP password hashing framework instance.
	 *
	 * @param int    $user_id    User ID.
	 * @param null   $deprecated Not used (argument deprecated).
	 * @param string $notify     Optional. Type of notification that should happen. Accepts 'admin' or an empty
	 *                           string (admin only), or 'both' (admin and user). The empty string value was kept
	 *                           for backward-compatibility purposes with the renamed parameter. Default empty.
	 */
	function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
		if ( null !== $deprecated ) {
			_deprecated_argument( __FUNCTION__, '4.3.1' );
		}

		global $wpdb, $wp_hasher;
		$user = get_userdata( $user_id );

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		Queue::add( get_option( 'admin_email' ), 'new_user_notification', $user_id );

		if ( 'admin' === $notify || empty( $notify ) ) {
			return;
		}

		// Generate something random for a password reset key.
		$key = wp_generate_password( 20, false );

		/** This action is documented in wp-login.php */
		do_action( 'retrieve_password_key', $user->user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

		$message = sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
		$message .= __( 'To set your password, visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . ">\r\n\r\n";

		$message .= wp_login_url() . "\r\n";

		wp_mail( $user->user_email, sprintf( __( '[%s] Your username and password info' ), $blogname ), $message );
	}
}

if ( ! function_exists( 'wp_password_change_notification' ) ) {
	$enabled[] = 'password_change_notification';

	/**
	 * Notify the blog admin of a user changing password, normally via email.
	 *
	 * @param WP_User $user User object.
	 */
	function wp_password_change_notification( &$user ) {
		Queue::add( get_option( 'admin_email' ), 'password_change_notification', $user->ID );
	}
}

update_option( 'digest_hooks', $enabled, false );
