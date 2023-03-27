<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;
use \Required\Digest\Message\CommentNotification as CommentNotificationMessage;

class CommentNotification extends WP_UnitTestCase {
	public function test_no_entries() {
		$message = new CommentNotificationMessage( [] );

		$this->assertStringContainsString( 'There were 0 new comments', $message->get_message() );
		$this->assertStringNotContainsString( 'already moderated.', $message->get_message() );
	}

	public function test_invalid_entry() {
		$message = new CommentNotificationMessage( [
			'123' => time(),
		] );

		$this->assertStringContainsString( 'There was 1 new comment', $message->get_message() );
		$this->assertStringContainsString( '1 comment was already moderated.', $message->get_message() );
	}

	public function test_already_processed_entry() {
		$comment_id = self::factory()->comment->create();

		$message = new CommentNotificationMessage( [
			$comment_id => time(),
		] );

		$this->assertStringContainsString( 'There was 1 new comment', $message->get_message() );
	}

	public function test_comment_action_links() {
		$comment_id = self::factory()->comment->create( [
			'comment_approved' => 0,
		] );

		$user = self::factory()->user->create_and_get( [
			'role'  => 'administrator',
			'user_email' => 'foo@example.com',
		] );

		$message = new CommentNotificationMessage( [
			$comment_id => time(),
		], $user );

		$this->assertStringNotContainsString( 'Approve', $message->get_message() );
		$this->assertStringContainsString( 'Trash', $message->get_message() );
		$this->assertStringContainsString( 'Spam', $message->get_message() );
	}

	public function test_comment_action_links_no_capabilities() {
		$comment_id = self::factory()->comment->create( [
			'comment_approved' => 0,
		] );

		$user = self::factory()->user->create_and_get( [
			'role'  => 'subscriber',
			'user_email' => 'foo@example.com',
		] );

		$message = new CommentNotificationMessage( [
			$comment_id => time(),
		], $user );

		$this->assertStringNotContainsString( 'Trash', $message->get_message() );
		$this->assertStringNotContainsString( 'Spam', $message->get_message() );
	}

	public function test_pingback() {
		$comment_id = self::factory()->comment->create( [
			'comment_approved' => 0,
			'comment_type'     => 'pingback',
		] );

		$message = new CommentNotificationMessage( [
			$comment_id => time(),
		] );

		$this->assertStringContainsString( 'Pingback on ', $message->get_message() );
	}

	public function test_trackback() {
		$comment_id = self::factory()->comment->create( [
			'comment_approved' => 0,
			'comment_type'     => 'trackback',
		] );

		$message = new CommentNotificationMessage( [
			$comment_id => time(),
		] );

		$this->assertStringContainsString( 'Trackback on ', $message->get_message() );
	}

	public function test_comment_author_url() {
		$comment_id = self::factory()->comment->create( [
			'comment_approved' => 0,
			'comment_author_url' => 'http://example.com'
		] );

		$message = new CommentNotificationMessage( [
			$comment_id => time(),
		] );

		$this->assertStringContainsString( 'http://example.com', $message->get_message() );
	}

	public function test_comment_author_email() {
		$comment_id = self::factory()->comment->create( [
			'comment_approved' => 0,
			'comment_author_email' => 'foo@example.com'
		] );

		$message = new CommentNotificationMessage( [
			$comment_id => time(),
		] );

		$this->assertStringContainsString( 'foo@example.com', $message->get_message() );
	}
}
