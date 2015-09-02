<?php
/**
 * Test the queue.
 *
 * @package WP_Digest
 */

/**
 * Class WP_Digest_Test_Queue.
 */
class WP_Digest_Test_Queue extends WP_Digest_TestCase {
	/**
	 * Test an empty queue.
	 */
	public function test_empty_queue() {
		$this->assertEmpty( WP_Digest_Queue::get() );
	}

	/**
	 * Test clearing the queue.
	 */
	public function test_queue_clearing() {
		WP_Digest_Queue::add( 'johndoe@example.org', 'foo', 'bar' );
		$this->assertEquals( 1, count( WP_Digest_Queue::get() ) );
		WP_Digest_Queue::clear();
		$this->assertEquals( 0, count( WP_Digest_Queue::get() ) );
	}

	/**
	 * Test adding entries to the queue.
	 */
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

		WP_Digest_Queue::add( 'johndoe@example.org', 'foo', 'bar' );

		$this->assertEquals( $expected, WP_Digest_Queue::get() );
	}

	/**
	 * Test adding duplicate entries to the queue.
	 */
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

		WP_Digest_Queue::add( 'johndoe@example.org', 'foo', 'bar' );
		WP_Digest_Queue::add( 'johndoe@example.org', 'hello', 'world' );

		$this->assertEquals( $expected, WP_Digest_Queue::get() );
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

		WP_Digest_Queue::add( $recipient, $event, $data );

		$queue = WP_Digest_Queue::get();

		$this->assertArrayHasKey( $recipient, $queue );
		$this->assertEquals( 1, count( $queue[ $recipient ] ) );
		$this->assertEquals( $expected, $queue[ $recipient ] );
	}

	/**
	 * Returns a dummy data set for the tests.
	 * @return array
	 */
	public function get_queue_entries() {
		return array(
			array( 'johndoe@example.org', 'foo', 'bar' ),
			array( 'janedoe@example.org', '', '' ),
			array( 'foo@example.org', 0, 0 ),
			array( 'bar@example.org', 'foo', 'bar' ),
		);
	}
}
