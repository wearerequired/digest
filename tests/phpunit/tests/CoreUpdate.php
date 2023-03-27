<?php

namespace Required\Digest\Tests;

use Required\Digest\Message\CoreUpdate as CoreUpdateMessage;
use WP_UnitTestCase;

class CoreUpdate extends WP_UnitTestCase {
	public function test_no_entries() {
		$message = new CoreUpdateMessage( [], null, 'core_update_success' );

		$this->assertSame( '', $message->get_message() );
	}

	public function test_no_entries_failure() {
		$message = new CoreUpdateMessage( [], null, 'core_update_failure' );

		$this->assertSame( '', $message->get_message() );
	}

	public function test_single_entry_success() {
		$message = new CoreUpdateMessage(
			[
				'100.1.0' => time(),
			],
			null,
			'core_update_success'
		);

		$this->assertStringContainsString( 'has been updated automatically to WordPress 100.1.0', $message->get_message() );
	}

	public function test_single_entry_failure() {
		$message = new CoreUpdateMessage(
			[
				'100.1.0' => time(),
			],
			null,
			'core_update_failure'
		);

		$this->assertStringContainsString( 'Please update your site', $message->get_message() );
		$this->assertStringContainsString( 'WordPress 100.1.0', $message->get_message() );
	}
}
