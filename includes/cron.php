<?php
/**
 * Cron helper functions.
 *
 * @package WP_Digest
 */

require_once( dirname( __FILE__ ) . '/../classes/cron.php' );
require_once( dirname( __FILE__ ) . '/../classes/section-message.php' );
require_once( dirname( __FILE__ ) . '/../classes/comment-moderation-message.php' );
require_once( dirname( __FILE__ ) . '/../classes/comment-notification-message.php' );
require_once( dirname( __FILE__ ) . '/../classes/core-update-message.php' );
require_once( dirname( __FILE__ ) . '/../classes/user-notification-message.php' );

add_action( 'digest_event', array( 'WP_Digest_Cron', 'init' ) );

// Hook into the digest notification messages.
add_filter( 'digest_message_section_core_update_success', function ( $content, $entries, $user, $event ) {
	$message = new WP_Digest_Core_Update_Message( $entries, $user, $event );

	if ( '' === $content ) {
		$content = '<p><b>' . __( 'Core Updates', 'digest' ) . '</b></p>';
	}

	return $content . $message->get_message();
}, 10, 4 );

add_filter( 'digest_message_section_core_update_failure', function ( $content, $entries, $user, $event ) {
	$message = new WP_Digest_Core_Update_Message( $entries, $user, $event );

	if ( '' === $content ) {
		$content = '<p><b>' . __( 'Core Updates', 'digest' ) . '</b></p>';
	}

	return $content . $message->get_message();
}, 10, 4 );

add_filter( 'digest_message_section_comment_moderation', function ( $content, $entries, $user ) {
	$message = new WP_Digest_Comment_Moderation_Message( $entries, $user );

	return $content . $message->get_message();
}, 10, 3 );

add_filter( 'digest_message_section_comment_notification', function ( $content, $entries, $user ) {
	$message = new WP_Digest_Comment_Notification_Message( $entries, $user );

	return $content . $message->get_message();
}, 10, 3 );

add_filter( 'digest_message_section_new_user_notification', function ( $content, $entries, $user ) {
	$message = new WP_Digest_User_Notification_Message( $entries, $user );

	return $content . $message->get_message();
}, 10, 3 );

add_filter( 'digest_message_section_password_change_notification', function ( $content, $entries, $user ) {
	$message = new WP_Digest_Password_Change_Notification_Message( $entries, $user );

	return $content . $message->get_message();
}, 10, 3 );
