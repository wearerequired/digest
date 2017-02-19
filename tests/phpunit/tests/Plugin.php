<?php

namespace Required\Digest\Tests;

use \WP_UnitTestCase;

class Plugin extends WP_UnitTestCase {
	function test_plugin_is_activated() {
		$this->assertTrue( class_exists( 'Required\\Digest\\Controller' ) );
	}
}
