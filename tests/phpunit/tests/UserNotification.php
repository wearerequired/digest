<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;
use \Required\Digest\Message\UserNotification as UserNotificationMessage;

class UserNotification extends WP_UnitTestCase {
	public function test_no_entries() {
		$message = new UserNotificationMessage( array() );

		$this->assertSame( '', $message->get_message() );
	}

	public function test_invalid_entry() {
		$message = new UserNotificationMessage( array(
			'123' => time(),
		) );

		$this->assertSame( '', $message->get_message() );
	}

	public function test_entries() {
		$user_1 = self::factory()->user->create( array(
			'display_name' => 'John Doe',
		) );

		$user_2 = self::factory()->user->create( array(
			'display_name' => 'Jane Doe',
		) );

		$message = new UserNotificationMessage( array(
			$user_1 => time(),
			$user_2 => time(),
		) );

		$this->assertContains( 'John Doe', $message->get_message() );
		$this->assertContains( 'Jane Doe', $message->get_message() );
		$this->assertContains( "ID: $user_1", $message->get_message() );
		$this->assertContains( "ID: $user_2", $message->get_message() );
	}
}
