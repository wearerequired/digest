<?php

namespace Required\Digest\Tests;

use Required\Digest\Queue;
use \WP_UnitTestCase;
use \MockAction;

class Plugin extends WP_UnitTestCase {
	public function test_plugin_is_activated() {
		$this->assertTrue( class_exists( 'Required\\Digest\\Plugin' ) );
	}

	public function test_cron_event_scheduled() {
		$this->assertFalse( wp_next_scheduled( 'digest_event' ) );

		digest()->activate_plugin();

		$this->assertInternalType( 'int', wp_next_scheduled( 'digest_event' ) );
	}

	public function test_cron_event_unscheduled() {
		digest()->activate_plugin();
		digest()->deactivate_plugin();

		$this->assertFalse( wp_next_scheduled( 'digest_event' ) );
	}

	public function test_plugin_action_links() {
		$this->assertEqualSets(
			array(
				'settings' => sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'options-general.php#digest' ) ),
					__( 'Settings', 'digest' )
				),
			),
			digest()->plugin_action_links( array() )
		);
	}

	public function test_send_email_empty_queue() {
		$action = new MockAction();

		add_filter( 'digest_cron_email_message', array( $action, 'filter' ) );
		digest()->send_email( 'Foo' );
		remove_filter( 'digest_cron_email_message', array( $action, 'filter' ) );

		$this->assertSame( 0, $action->get_call_count() );
	}

	public function test_comment_moderation_recipients() {
		digest()->comment_moderation_recipients( array(
			'foo@example.com',
			'bar@example.com',
		), 123 );

		$queue = Queue::get();

		$this->assertArrayHasKey( 'foo@example.com', $queue );
		$this->assertArrayHasKey( 'bar@example.com', $queue );
		$this->assertCount( 1, $queue['foo@example.com'] );
		$this->assertCount( 1, $queue['bar@example.com'] );
		$this->assertSame( 'comment_moderation', $queue['foo@example.com'][0][1] );
		$this->assertSame( 'comment_moderation', $queue['bar@example.com'][0][1] );
		$this->assertSame( 123, $queue['foo@example.com'][0][2] );
		$this->assertSame( 123, $queue['bar@example.com'][0][2] );

		Queue::clear();
	}

	public function data_sanitize_frequency_option() {
		return array(
			// Period.
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => 1, 'day' => 1 ),
			),
			array(
				array( 'period' => 'daily', 'hour' => 1, 'day' => 1 ),
				array( 'period' => 'daily', 'hour' => 1, 'day' => 1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => 1 ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => 1 ),
			),
			// Hour.
			array(
				array( 'period' => 'weekly', 'hour' => 18, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => -1, 'day' => 1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 0, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => 0, 'day' => 1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 18, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => 24, 'day' => 1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 23, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => 23, 'day' => 1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 18, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => 'foo', 'day' => 1 ),
			),
			// Day.
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => get_option( 'start_of_week' ) ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => -1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => 0 ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => 0 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => get_option( 'start_of_week' ) ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => 7 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => 6 ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => 6 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => get_option( 'start_of_week' ) ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => 'foo' ),
			),
		);
	}

	/**
	 * @dataProvider data_sanitize_frequency_option
	 *
	 * @param array $expected
	 * @param array $actual
	 */
	public function test_sanitize_frequency_option( $expected, $actual ) {
		$this->assertEqualSets( $expected, digest()->sanitize_frequency_option( $actual ) );
	}
}
