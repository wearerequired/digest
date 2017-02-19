<?php

namespace Required\Digest\Tests;

use Required\Digest\Event\RegistryInterface;
use \WP_UnitTestCase;
use \Required\Digest\Event\Registry as EventRegistry;

class Registry extends WP_UnitTestCase {
	/**
	 * @var RegistryInterface
	 */
	protected $registry;

	public function setUp() {
		$this->registry = new EventRegistry();
	}

	public function test_register_event() {
		$this->registry->register_event( 'foo' );

		$this->assertSame( array( 'foo' ), $this->registry->get_registered_events() );
	}

	public function test_register_event_twice() {
		$this->registry->register_event( 'foo' );
		$this->registry->register_event( 'foo' );

		$this->assertSame( array( 'foo' ), $this->registry->get_registered_events() );
	}

	public function test_register_event_adds_filter_with_callback() {
		$this->registry->register_event( 'foo', 'bar' );

		$this->assertSame( 10, has_filter( 'digest_message_section_foo', 'bar' ) );
	}

	public function test_is_registered_event() {
		$before = $this->registry->is_registered_event( 'foo' );
		$this->registry->register_event( 'foo' );
		$after = $this->registry->is_registered_event( 'foo' );

		$this->assertFalse( $before );
		$this->assertTrue( $after );
	}

	public function get_registered_events_empty() {
		$this->assertEmpty( $this->registry->get_registered_events() );
	}

	public function test_register_default_events(  ) {
		$this->registry->register_default_events();

		$this->assertEqualSets( array(
			'comment_moderation',
			'comment_notification',
			'core_update_failure',
			'core_update_success',
			'new_user_notification',
			'password_change_notification'
		), $this->registry->get_registered_events() );
	}

	public function test_register_default_events_without_pluggable(  ) {
		update_option( 'digest_hooks', array(), false );

		$this->registry->register_default_events();

		$this->assertEqualSets( array(
			'comment_moderation',
			'comment_notification',
			'core_update_failure',
			'core_update_success',
		), $this->registry->get_registered_events() );
	}
}
