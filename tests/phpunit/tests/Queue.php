<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;
use \Required\Digest\Queue as Digest_Queue;

class Queue extends WP_UnitTestCase {
	public function test_empty_queue() {
		$this->assertEmpty( Digest_Queue::get() );
	}

	public function test_queue_clearing() {
		Digest_Queue::add( 'johndoe@example.org', 'foo', 'bar' );
		$this->assertEquals( 1, count( Digest_Queue::get() ) );
		Digest_Queue::clear();
		$this->assertEquals( 0, count( Digest_Queue::get() ) );
	}

	public function test_queue_add() {
		$current_time = current_time( 'timestamp' );

		$expected = array(
			'johndoe@example.org' => array(
				array(
					$current_time,
					'foo',
					'bar',
				),
			),
		);

		Digest_Queue::add( 'johndoe@example.org', 'foo', 'bar' );

		$this->assertEquals( $expected, Digest_Queue::get() );
	}

	public function test_queue_add_duplicate() {
		$current_time = current_time( 'timestamp' );

		$expected = array(
			'johndoe@example.org' => array(
				array(
					$current_time,
					'foo',
					'bar',
				),
				array(
					$current_time,
					'hello',
					'world',
				),
			),
		);

		Digest_Queue::add( 'johndoe@example.org', 'foo', 'bar' );
		Digest_Queue::add( 'johndoe@example.org', 'hello', 'world' );

		$this->assertEquals( $expected, Digest_Queue::get() );
	}

	/**
	 * Test adding many entries to the queue.
	 *
	 * @dataProvider get_queue_entries
	 *
	 * @param string $recipient The recipient.
	 * @param string $event     The type of the event.
	 * @param string $data      Data to store for this event, for example a comment ID.
	 */
	public function test_queue_add_many( $recipient, $event, $data ) {
		$current_time = current_time( 'timestamp' );

		$expected = array( array( $current_time, $event, $data ) );

		Digest_Queue::add( $recipient, $event, $data );

		$queue = Digest_Queue::get();

		$this->assertArrayHasKey( $recipient, $queue );
		$this->assertEquals( 1, count( $queue[ $recipient ] ) );
		$this->assertEquals( $expected, $queue[ $recipient ] );
	}

	public function get_queue_entries() {
		return array(
			array( 'johndoe@example.org', 'foo', 'bar' ),
			array( 'janedoe@example.org', '', '' ),
			array( 'foo@example.org', 0, 0 ),
			array( 'bar@example.org', 'foo', 'bar' ),
		);
	}
}
