<?php

namespace Required\Digest\Tests;

use Required\Digest\Setting\FrequencySetting;
use WP_UnitTestCase;

class Settings extends WP_UnitTestCase {
	/**
	 * @var FrequencySetting
	 */
	protected static $frequency_setting;

	public static function setUpBeforeClass(  ) {
		parent::setUpBeforeClass();

		self::$frequency_setting = new FrequencySetting();
	}

	public function test_register() {
		self::$frequency_setting->register();

		$this->assertNotFalse(
			has_action( 'admin_init', [
				self::$frequency_setting,
				'add_settings_fields',
			] )
		);

		$this->assertNotFalse(
			has_action( 'admin_enqueue_scripts', [
				self::$frequency_setting,
				'admin_enqueue_scripts',
			] )
		);
	}

	public function test_add_settings_fields() {
		global $wp_settings_sections, $wp_settings_fields;

		self::$frequency_setting->add_settings_fields();

		$this->assertArrayHasKey( 'digest_notifications', $wp_settings_sections['general'] );
		$this->assertArrayHasKey( 'digest_frequency', $wp_settings_fields['general']['digest_notifications'] );
	}

	public function test_admin_enqueue_scripts_invalid_hook_suffix() {
		self::$frequency_setting->admin_enqueue_scripts( 'foo' );

		$this->assertFalse( wp_script_is( 'digest' ) );
		$this->assertFalse( wp_style_is( 'digest' ) );
	}

	public function test_admin_enqueue_scripts() {
		self::$frequency_setting->admin_enqueue_scripts( 'options-general.php' );

		$this->assertTrue( wp_script_is( 'digest' ) );
		$this->assertTrue( wp_style_is( 'digest' ) );
	}

	public function test_plugin_action_links() {
		$this->assertEqualSets(
			[
				'settings' => sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'options-general.php#digest' ) ),
					__( 'Settings', 'digest' )
				),
			],
			self::$frequency_setting->plugin_action_links( [] )
		);
	}

	public function data_sanitize_frequency_option() {
		return [
			// Period.
			[
				[ 'period' => 'weekly', 'hour' => 1, 'day' => 1 ],
				[ 'period' => 'weekly', 'hour' => 1, 'day' => 1 ],
			],
			[
				[ 'period' => 'daily', 'hour' => 1, 'day' => 1 ],
				[ 'period' => 'daily', 'hour' => 1, 'day' => 1 ],
			],
			[
				[ 'period' => 'weekly', 'hour' => 1, 'day' => 1 ],
				[ 'period' => 'foo', 'hour' => 1, 'day' => 1 ],
			],
			// Hour.
			[
				[ 'period' => 'weekly', 'hour' => 18, 'day' => 1 ],
				[ 'period' => 'weekly', 'hour' => -1, 'day' => 1 ],
			],
			[
				[ 'period' => 'weekly', 'hour' => 0, 'day' => 1 ],
				[ 'period' => 'weekly', 'hour' => 0, 'day' => 1 ],
			],
			[
				[ 'period' => 'weekly', 'hour' => 18, 'day' => 1 ],
				[ 'period' => 'weekly', 'hour' => 24, 'day' => 1 ],
			],
			[
				[ 'period' => 'weekly', 'hour' => 23, 'day' => 1 ],
				[ 'period' => 'weekly', 'hour' => 23, 'day' => 1 ],
			],
			[
				[ 'period' => 'weekly', 'hour' => 18, 'day' => 1 ],
				[ 'period' => 'weekly', 'hour' => 'foo', 'day' => 1 ],
			],
			// Day.
			[
				[ 'period' => 'weekly', 'hour' => 1, 'day' => get_option( 'start_of_week' ) ],
				[ 'period' => 'foo', 'hour' => 1, 'day' => -1 ],
			],
			[
				[ 'period' => 'weekly', 'hour' => 1, 'day' => 0 ],
				[ 'period' => 'foo', 'hour' => 1, 'day' => 0 ],
			],
			[
				[ 'period' => 'weekly', 'hour' => 1, 'day' => get_option( 'start_of_week' ) ],
				[ 'period' => 'foo', 'hour' => 1, 'day' => 7 ],
			],
			[
				[ 'period' => 'weekly', 'hour' => 1, 'day' => 6 ],
				[ 'period' => 'foo', 'hour' => 1, 'day' => 6 ],
			],
			[
				[ 'period' => 'weekly', 'hour' => 1, 'day' => get_option( 'start_of_week' ) ],
				[ 'period' => 'foo', 'hour' => 1, 'day' => 'foo' ],
			],
		];
	}

	/**
	 * @dataProvider data_sanitize_frequency_option
	 *
	 * @param array $expected
	 * @param array $actual
	 */
	public function test_sanitize_frequency_option( $expected, $actual ) {
		$this->assertEqualSetsWithIndex( $expected, self::$frequency_setting->sanitize_frequency_option( $actual ) );
	}
}
