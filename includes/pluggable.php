<?php
/**
 * Implement pluggable functions so we can add those emails to the queue.
 *
 * If it works and the functions haven't been declared yet,
 * we store that information in an option.
 * This way the plugin knows which hooks work and which don't.
 *
 * @package WP_Digest
 */

$enabled = array();

if ( ! function_exists( 'wp_new_user_notification' ) ) {
	$enabled[] = 'new_user_notification';

	/**
	 * Email login credentials to a newly-registered user.
	 *
	 * A new user registration notification is also sent to admin email.
	 *
	 * @param int    $user_id        User ID.
	 * @param string $plaintext_pass Optional. The user's plaintext password. Default empty.
	 */
	function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
		WP_Digest_Queue::add( get_option( 'admin_email' ), 'new_user_notification', $user_id );

		$user = get_userdata( $user_id );

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		if ( empty( $plaintext_pass ) ) {
			return;
		}

		$message = sprintf( __( 'Username: %s', 'default' ), $user->user_login ) . "\r\n";
		$message .= sprintf( __( 'Password: %s', 'default' ), $plaintext_pass ) . "\r\n";
		$message .= wp_login_url() . "\r\n";

		wp_mail( $user->user_email, sprintf( __( '[%s] Your username and password', 'default' ), $blogname ), $message );
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
		WP_Digest_Queue::add( get_option( 'admin_email' ), 'password_change_notification', $user->ID );
	}
}

// Todo: Use this somewhere.
update_option( 'digest_hooks', $enabled, false );
