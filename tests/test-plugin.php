<?php
/**
 * Test our plugin.
 *
 * @package WP_Digest
 */

/**
 * Class WP_Digest_Test_Plugin.
 */
class WP_Digest_Test_Plugin extends WP_Digest_TestCase {
	/**
	 * The plugin should be installed and activated.
	 */
	function test_plugin_activated() {
		$this->assertTrue( class_exists( 'WP_Digest_Plugin' ) );
	}
}
