<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;
use Required\Digest\Plugin as Digest_Message;

class Digest extends WP_UnitTestCase {
	/**
	 * @var int
	 */
	protected static $user_id;

	public static function wpSetUpBeforeClass() {
		self::$user_id = self::factory()->user->create( [
			'display_name' => 'John Doe',
			'user_email'   => 'foo@example.com',
		] );
	}

	public function test_empty_digest() {
		$digest = new Digest_Message( 'bar@example.com', [] );

		$this->assertEmpty( $digest->get_message() );
	}

	public function test_digest_with_unregistered_events() {
		$digest = new Digest_Message( 'foo@example.com', [
			[ current_time( 'timestamp' ), 'foo', 'bar' ],
			[ current_time( 'timestamp' ), 'bar', 'baz' ],
		] );

		$this->assertEmpty( $digest->get_message() );
	}

	public function test_digest_with_registered_events() {
		$comment_id = self::factory()->comment->create();

		$digest = new Digest_Message( 'foo@example.com', [
			[ current_time( 'timestamp' ), 'new_user_notification', self::$user_id ],
			[ current_time( 'timestamp' ), 'comment_notification', $comment_id ],
		] );

		$this->assertContains( 'Hi John Doe', $digest->get_message() );
		$this->assertContains( 'That\'s it, have a nice day!', $digest->get_message() );
	}

	public function test_digest_with_registered_events_no_user() {
		$comment_id = self::factory()->comment->create();

		$digest = new Digest_Message( 'bar@example.com', [
			[ current_time( 'timestamp' ), 'new_user_notification', self::$user_id ],
			[ current_time( 'timestamp' ), 'comment_notification', $comment_id ],
		] );

		$this->assertContains( 'Hi there', $digest->get_message() );
		$this->assertContains( 'That\'s it, have a nice day!', $digest->get_message() );
	}
}
