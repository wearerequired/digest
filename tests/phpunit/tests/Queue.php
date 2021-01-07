<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;
use \Required\Digest\Queue as Digest_Queue;

use function Required\Digest\auto_core_update_email;
use function Required\Digest\comment_notification_recipients;
use function Required\Digest\comment_moderation_recipients;

class Queue extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		Digest_Queue::clear();
	}

	public function tearDown() {
		Digest_Queue::clear();

		parent::tearDown();
	}

	public function assertEqualSetsWithDelta($expected, $actual) {
		ksort( $expected );
		ksort( $actual );
		$this->assertEquals( $expected, $actual, '', 1 );
	}

	public function test_empty_queue() {
		$this->assertEmpty( Digest_Queue::get() );
	}

	public function test_queue_clearing() {
		Digest_Queue::add( 'johndoe@example.org', 'foo', 'bar' );

		$queue_1 = Digest_Queue::get();
		Digest_Queue::clear();
		$queue_2 = Digest_Queue::get();

		$this->assertEquals( 1, count( $queue_1 ) );
		$this->assertEquals( 0, count( $queue_2 ) );
	}

	public function test_queue_add() {
		$expected = [
			'foo@example.com' => [
				[
					current_time( 'timestamp' ),
					'foo',
					'bar',
				],
			],
		];

		Digest_Queue::add( 'foo@example.com', 'foo', 'bar' );

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}

	public function test_queue_add_duplicate() {
		$expected = [
			'foo@example.com' => [
				[
					current_time( 'timestamp'),
					'foo',
					'bar',
				],
				[
					current_time( 'timestamp'),
					'hello',
					'world',
				],
			],
		];

		Digest_Queue::add( 'foo@example.com', 'foo', 'bar' );
		Digest_Queue::add( 'foo@example.com', 'hello', 'world' );

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}

	/**
	 * Test adding many entries to the queue.
	 *
	 * @dataProvider data_queue_entries
	 *
	 * @param string $recipient The recipient.
	 * @param string $event     The type of the event.
	 * @param string $data      Data to store for this event, for example a comment ID.
	 */
	public function test_queue_add_many( $recipient, $event, $data ) {
		$expected = [ [ current_time( 'timestamp' ), $event, $data ] ];

		Digest_Queue::add( $recipient, $event, $data );

		$queue = Digest_Queue::get();

		$this->assertArrayHasKey( $recipient, $queue );
		$this->assertEquals( 1, count( $queue[ $recipient ] ) );
		$this->assertEqualSetsWithDelta( $expected, $queue[ $recipient ] );
	}

	public function data_queue_entries() {
		return [
			[ 'johndoe@example.org', 'foo', 'bar' ],
			[ 'janedoe@example.org', '', '' ],
			[ 'foo@example.org', 0, 0 ],
			[ 'bar@example.org', 'foo', 'bar' ],
		];
	}

	public function test_comment_notification_recipients_returns_empty_aray() {
		$post_id    = self::factory()->post->create();
		$comment_id = self::factory()->comment->create( [
			'comment_post_ID' => $post_id,
		] );

		$actual = comment_notification_recipients( [
			'foo@example.com',
			'bar@example.com',
		], $comment_id );

		$this->assertEmpty( $actual );
	}

	public function test_comment_notification_recipients() {
		$post_id    = self::factory()->post->create();
		$comment_id = self::factory()->comment->create( [
			'comment_post_ID' => $post_id,
		] );

		comment_notification_recipients( [
			'foo@example.com',
			'bar@example.com',
		], $comment_id );

		$expected = [
			'foo@example.com' => [
				[
					current_time( 'timestamp'),
					'comment_notification',
					$comment_id,
				],
			],
			'bar@example.com' => [
				[
					current_time( 'timestamp'),
					'comment_notification',
					$comment_id,
				],
			],
		];

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}

	public function test_comment_notification_recipients_comment_by_author() {
		$user_id    = self::factory()->user->create( [
			'user_email' => 'foo@example.com',
		] );
		$post_id    = self::factory()->post->create( [
			'post_author' => $user_id,
		] );
		$comment_id = self::factory()->comment->create( [
			'comment_post_ID' => $post_id,
			'user_id'         => $user_id,
		] );

		comment_notification_recipients( [
			'foo@example.com',
			'bar@example.com',
		], $comment_id );

		$expected = [
			'bar@example.com' => [
				[
					current_time( 'timestamp'),
					'comment_notification',
					$comment_id,
				],
			]
		];

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}

	public function test_comment_notification_recipients_comment_by_current_user() {
		$user_id    = self::factory()->user->create( [
			'user_email' => 'foo@example.com',
		] );
		$post_id    = self::factory()->post->create( [
			'post_author' => $user_id,
		] );
		$comment_id = self::factory()->comment->create( [
			'comment_post_ID' => $post_id,
		] );

		wp_set_current_user( $user_id );

		comment_notification_recipients( [
			'foo@example.com',
			'bar@example.com',
		], $comment_id );

		$expected = [
			'bar@example.com' => [
				[
					current_time( 'timestamp'),
					'comment_notification',
					$comment_id,
				],
			]
		];

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}

	public function test_comment_notification_recipients_author_has_no_capabilities() {
		$user_id    = self::factory()->user->create( [
			'user_email' => 'foo@example.com',
		] );
		$post_id    = self::factory()->post->create( [
			'post_author' => $user_id,
			'post_status' => 'private',
			'post_type'   => 'revision',
		] );
		$comment_id = self::factory()->comment->create( [
			'comment_post_ID' => $post_id,
		] );

		comment_notification_recipients( [
			'foo@example.com',
			'bar@example.com',
		], $comment_id );

		$expected = [
			'bar@example.com' => [
				[
					current_time( 'timestamp' ),
					'comment_notification',
					$comment_id,
				],
			],
		];

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}

	public function test_comment_moderation_recipients() {
		comment_moderation_recipients( [
			'foo@example.com',
			'bar@example.com',
		], 123 );

		$expected = [
			'foo@example.com' => [
				[
					current_time( 'timestamp'),
					'comment_moderation',
					123,
				],
			],
			'bar@example.com' => [
				[
					current_time( 'timestamp'),
					'comment_moderation',
					123,
				],
			],
		];

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}

	public function test_auto_core_update_email_returns_empty_array() {
		set_site_transient( 'update_core', new \stdClass() );

		$actual = auto_core_update_email( [], 'success', (object) [ 'current' => '100.1.0' ] );

		$this->assertSame( [ 'to' => [] ], $actual );
	}

	public function test_auto_core_update_email() {
		set_site_transient( 'update_core', new \stdClass() );

		auto_core_update_email( [], 'success', (object) [ 'current' => '100.1.0' ] );

		$expected = [
			get_bloginfo( 'admin_email' ) => [
				[
					current_time( 'timestamp' ),
					'core_update_success',
					'100.1.0',
				],
			],
		];

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}

	public function test_auto_core_update_email_invalid_type() {
		set_site_transient( 'update_core', new \stdClass() );

		auto_core_update_email( [], 'foo', (object) [ 'current' => '100.1.0' ] );

		$this->assertEmpty( Digest_Queue::get() );
	}

	public function test_auto_core_update_email_no_updates() {
		set_site_transient( 'update_core', (object) [
			'updates' => [],
		] );

		auto_core_update_email( [], 'success', (object) [ 'current' => '100.1.0' ] );

		$expected = [
			get_bloginfo( 'admin_email' ) => [
				[
					current_time( 'timestamp' ),
					'core_update_success',
					'100.1.0',
				],
			],
		];

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}
}
