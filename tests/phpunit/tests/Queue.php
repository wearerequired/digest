<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;
use \Required\Digest\Queue as Digest_Queue;

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
		sort( $expected );
		sort( $actual );
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
		$expected = array(
			'foo@example.com' => array(
				array(
					current_time( 'timestamp' ),
					'foo',
					'bar',
				),
			),
		);

		Digest_Queue::add( 'foo@example.com', 'foo', 'bar' );

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}

	public function test_queue_add_duplicate() {
		$expected = array(
			'foo@example.com' => array(
				array(
					current_time( 'timestamp'),
					'foo',
					'bar',
				),
				array(
					current_time( 'timestamp'),
					'hello',
					'world',
				),
			),
		);

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
		$expected = array( array( current_time( 'timestamp' ), $event, $data ) );

		Digest_Queue::add( $recipient, $event, $data );

		$queue = Digest_Queue::get();

		$this->assertArrayHasKey( $recipient, $queue );
		$this->assertEquals( 1, count( $queue[ $recipient ] ) );
		$this->assertEqualSetsWithDelta( $expected, $queue[ $recipient ] );
	}

	public function data_queue_entries() {
		return array(
			array( 'johndoe@example.org', 'foo', 'bar' ),
			array( 'janedoe@example.org', '', '' ),
			array( 'foo@example.org', 0, 0 ),
			array( 'bar@example.org', 'foo', 'bar' ),
		);
	}

	public function test_comment_moderation_recipients() {
		digest()->comment_moderation_recipients( array(
			'foo@example.com',
			'bar@example.com',
		), 123 );

		$expected = array(
			'foo@example.com' => array(
				array(
					current_time( 'timestamp'),
					'comment_moderation',
					123,
				),
			),
			'bar@example.com' => array(
				array(
					current_time( 'timestamp'),
					'comment_moderation',
					123,
				),
			),
		);

		$this->assertEqualSetsWithDelta( $expected, Digest_Queue::get() );
	}
}
