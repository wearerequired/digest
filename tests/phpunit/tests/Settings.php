<?php

namespace Required\Digest\Tests;

use Required\Digest\Queue;
use Required\Digest\Setting\FrequencySetting;
use \WP_UnitTestCase;
use \MockAction;

class Settings extends WP_UnitTestCase {
	/**
	 * @var FrequencySetting
	 */
	protected static $frequency_setting;

	public static function setUpBeforeClass(  ) {
		parent::setUpBeforeClass();

		self::$frequency_setting = new FrequencySetting();
	}

	public function test_plugin_action_links() {
		$this->assertEqualSets(
			array(
				'settings' => sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'options-general.php#digest' ) ),
					__( 'Settings', 'digest' )
				),
			),
			self::$frequency_setting->plugin_action_links( array() )
		);
	}

	public function data_sanitize_frequency_option() {
		return array(
			// Period.
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => 1, 'day' => 1 ),
			),
			array(
				array( 'period' => 'daily', 'hour' => 1, 'day' => 1 ),
				array( 'period' => 'daily', 'hour' => 1, 'day' => 1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => 1 ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => 1 ),
			),
			// Hour.
			array(
				array( 'period' => 'weekly', 'hour' => 18, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => -1, 'day' => 1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 0, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => 0, 'day' => 1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 18, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => 24, 'day' => 1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 23, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => 23, 'day' => 1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 18, 'day' => 1 ),
				array( 'period' => 'weekly', 'hour' => 'foo', 'day' => 1 ),
			),
			// Day.
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => get_option( 'start_of_week' ) ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => -1 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => 0 ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => 0 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => get_option( 'start_of_week' ) ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => 7 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => 6 ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => 6 ),
			),
			array(
				array( 'period' => 'weekly', 'hour' => 1, 'day' => get_option( 'start_of_week' ) ),
				array( 'period' => 'foo', 'hour' => 1, 'day' => 'foo' ),
			),
		);
	}

	/**
	 * @dataProvider data_sanitize_frequency_option
	 *
	 * @param array $expected
	 * @param array $actual
	 */
	public function test_sanitize_frequency_option( $expected, $actual ) {
		$this->assertEqualSets( $expected, self::$frequency_setting->sanitize_frequency_option( $actual ) );
	}
}
