<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;
use \Required\Digest\Message\PasswordChangeNotification as PasswordMessage;

class PasswordChangeNotification extends WP_UnitTestCase {
	public function test_no_entries() {
		$message = new PasswordMessage( [] );

		$this->assertSame( '', $message->get_message() );
	}

	public function test_invalid_entry() {
		$message = new PasswordMessage( [
			'123' => time(),
		] );

		$this->assertSame( '', $message->get_message() );
	}

	public function test_single_entry() {
		$user_1 = self::factory()->user->create( [
			'display_name' => 'John Doe',
		] );

		$message = new PasswordMessage( [
			$user_1 => time(),
		] );

		$this->assertStringContainsString( 'The following user lost and changed his password', $message->get_message() );
		$this->assertStringContainsString( 'John Doe', $message->get_message() );
		$this->assertStringContainsString( "ID: $user_1", $message->get_message() );
	}

	public function test_entries() {
		$user_1 = self::factory()->user->create( [
			'display_name' => 'John Doe',
		] );

		$user_2 = self::factory()->user->create( [
			'display_name' => 'Jane Doe',
		] );

		$message = new PasswordMessage( [
			$user_1 => time(),
			$user_2 => time(),
		] );

		$this->assertStringContainsString( 'The following users lost and changed their passwords', $message->get_message() );
		$this->assertStringContainsString( 'John Doe', $message->get_message() );
		$this->assertStringContainsString( 'Jane Doe', $message->get_message() );
		$this->assertStringContainsString( "ID: $user_1", $message->get_message() );
		$this->assertStringContainsString( "ID: $user_2", $message->get_message() );
	}
}
