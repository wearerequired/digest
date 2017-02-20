<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;
use \Required\Digest\Message\CoreUpdate as CoreUpdateMessage;

class CoreUpdate extends WP_UnitTestCase {
	public function test_no_entries() {
		$message = new CoreUpdateMessage( array(), null, 'core_update_success' );

		$this->assertSame( '', $message->get_message() );
	}

	public function test_no_entries_failure() {
		$message = new CoreUpdateMessage( array(), null, 'core_update_failure' );

		$this->assertSame( '', $message->get_message() );
	}

	public function test_single_entry_success() {
		$message = new CoreUpdateMessage(
			array(
				'100.1.0' => time(),
			),
			null,
			'core_update_success'
		);

		$this->assertContains( 'has been updated automatically to WordPress 100.1.0', $message->get_message() );
	}

	public function test_single_entry_failure() {
		$message = new CoreUpdateMessage(
			array(
				'100.1.0' => time(),
			),
			null,
			'core_update_failure'
		);

		$this->assertContains( 'Please update your site', $message->get_message() );
		$this->assertContains( 'WordPress 100.1.0', $message->get_message() );
	}
}
