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

	public function test_send_email_empty_queue() {
		$action = new MockAction();

		add_filter( 'digest_cron_email_message', array( $action, 'filter' ) );
		digest()->send_email( 'Foo' );
		remove_filter( 'digest_cron_email_message', array( $action, 'filter' ) );

		$this->assertSame( 0, $action->get_call_count() );
	}
}
