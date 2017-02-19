<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;
use Required\Digest\Digest as Digest_Message;

class Digest extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		wp_set_current_user( self::factory()->user->create( array(
			'role'  => 'editor',
			'email' => 'foo@example.com',
		) ) );
	}

	public function test_empty_digest() {
		$digest = new Digest_Message( 'foo@example.com', array() );

		$this->assertEmpty( $digest->get_message() );
	}
}
