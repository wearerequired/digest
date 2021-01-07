<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;
use \Required\Digest\Event\Registry as EventRegistry;

use function Required\Digest\register_default_events;

class Registry extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		EventRegistry::clear();
	}

	public function tearDown() {
		EventRegistry::clear();

		parent::tearDown();
	}

	public function test_register_event() {
		EventRegistry::register_event( 'foo' );

		$this->assertSame( [ 'foo' ], EventRegistry::get_registered_events() );
	}

	public function test_register_event_twice() {
		EventRegistry::register_event( 'foo' );
		EventRegistry::register_event( 'foo' );

		$this->assertSame( [ 'foo' ], EventRegistry::get_registered_events() );
	}

	public function test_register_event_adds_filter_with_callback() {
		EventRegistry::register_event( 'foo', 'bar' );

		$this->assertNotFalse( has_filter( 'digest_message_section_foo', 'bar' ) );
	}

	public function test_is_registered_event() {
		$before = EventRegistry::is_registered_event( 'foo' );
		EventRegistry::register_event( 'foo' );
		$after = EventRegistry::is_registered_event( 'foo' );

		$this->assertFalse( $before );
		$this->assertTrue( $after );
	}

	public function get_registered_events_empty() {
		$this->assertEmpty( EventRegistry::get_registered_events() );
	}

	public function test_register_default_events(  ) {
		register_default_events();

		$this->assertEqualSets( [
			'comment_moderation',
			'comment_notification',
			'core_update_failure',
			'core_update_success',
			'new_user_notification',
			'password_change_notification'
		], EventRegistry::get_registered_events() );
	}

	public function test_register_default_events_without_pluggable(  ) {
		update_option( 'digest_hooks', [], false );

		register_default_events();

		$this->assertEqualSets( [
			'comment_moderation',
			'comment_notification',
			'core_update_failure',
			'core_update_success',
		], EventRegistry::get_registered_events() );
	}
}
