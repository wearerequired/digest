<?php
/**
 * Frequency setting class.
 */

namespace Required\Digest\Setting;

use const Required\Digest\PLUGIN_FILE;
use const Required\Digest\VERSION;

/**
 * Setting for the digest frequency.
 *
 * @since 2.0.0
 */
class FrequencySetting implements SettingInterface {

	/**
	 * Registers the setting.
	 *
	 * @since  2.0.0
	 */
	public function register() {
		register_setting(
			'general',
			'digest_frequency',
			[ $this, 'sanitize_frequency_option' ]
		);

		add_action( 'admin_init', [ $this, 'add_settings_fields' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		// Add an action link pointing to the options page.
		add_action(
			'plugin_action_links_' . plugin_basename( PLUGIN_FILE ),
			[
				$this,
				'plugin_action_links',
			]
		);
	}

	/**
	 * Adds a new settings section and settings fields to Settings -> General.
	 *
	 * @since  2.0.0
	 */
	public function add_settings_fields() {
		add_settings_section(
			'digest_notifications',
			__( 'Email Notifications', 'digest' ),
			function () {
				esc_html_e( "You get a daily or weekly digest of what's happening on your site. Here you can configure its frequency.", 'digest' );
			},
			'general'
		);

		add_settings_field(
			'digest_frequency',
			sprintf( '<label for="digest_frequency_period" id="digest">%s</label>', __( 'Frequency', 'digest' ) ),
			[ $this, 'settings_field_frequency' ],
			'general',
			'digest_notifications'
		);
	}

	/**
	 * Settings field callback that prints the actual input fields.
	 *
	 * @since  2.0.0
	 */
	public function settings_field_frequency() {
		$options     = get_option(
			'digest_frequency',
			[
				'period' => 'weekly',
				'hour'   => 18,
				'day'    => absint( get_option( 'start_of_week' ) ),
			]
		);
		$time_format = get_option( 'time_format' );
		?>
		<p>
			<?php esc_html_e( 'Send me a digest of new site activity', 'digest' ); ?>
			<select name="digest_frequency[period]" id="digest_frequency_period">
				<option value="daily" <?php selected( 'daily', $options['period'] ); ?>>
					<?php echo esc_attr_x( 'every day', 'frequency', 'digest' ); ?>
				</option>
				<option value="weekly" <?php selected( 'weekly', $options['period'] ); ?>>
					<?php echo esc_attr_x( 'every week', 'frequency', 'digest' ); ?>
				</option>
				<option value="monthly" <?php selected( 'monthly', $options['period'] ); ?>>
					<?php echo esc_attr_x( 'every month', 'frequency', 'digest' ); ?>
				</option>
			</select>
			<span id="digest_frequency_hour_wrapper">
				<?php esc_html_e( 'at', 'digest' ); ?>
				<select name="digest_frequency[hour]" id="digest_frequency_hour">
					<?php for ( $hour = 0; $hour <= 23; $hour ++ ) : ?>
						<option value="<?php echo esc_attr( $hour ); ?>" <?php selected( $hour, $options['hour'] ); ?>>
							<?php echo esc_html( gmdate( $time_format, mktime( $hour, 0, 0, 1, 1, 2011 ) ) ); ?>
						</option>
					<?php endfor; ?>
				</select>
				<?php esc_html_e( "o'clock", 'digest' ); ?>
			</span>
			<span id="digest-frequency-day-wrapper" <?php echo 'weekly' !== $options['period'] ? 'class="digest-hidden"' : ''; ?>>
				<?php
				esc_html_e( 'on', 'digest' );

				global $wp_locale;
				?>
				<select name="digest_frequency[day]" id="digest_frequency_day">
					<?php for ( $day_index = 0; $day_index <= 6; $day_index ++ ) : ?>
						<option value="<?php echo esc_attr( $day_index ); ?>" <?php selected( $day_index, $options['day'] ); ?>>
							<?php echo esc_html( $wp_locale->get_weekday( $day_index ) ); ?>
						</option>
					<?php endfor; ?>
				</select>
			</span>
		</p>
		<?php
	}

	/**
	 * Sanitize the digest frequency option.
	 *
	 * @since  2.0.0
	 *
	 * @param mixed $value The unsanitized value.
	 * @return array The sanitized frequency option.
	 */
	public function sanitize_frequency_option( $value ) {
		$value     = (array) $value;
		$new_value = [];

		$new_value['period'] = isset( $value['period'] ) ? $value['period'] : 'weekly';
		$new_value['hour']   = isset( $value['hour'] ) ? $value['hour'] : 18;
		$new_value['day']    = isset( $value['day'] ) ? $value['day'] : get_option( 'start_of_week', 0 );

		if ( ! \in_array( $new_value['period'], [ 'daily', 'weekly', 'monthly' ], true ) ) {
			$new_value['period'] = 'weekly';
		}

		$new_value['hour'] = filter_var(
			$new_value['hour'],
			FILTER_VALIDATE_INT,
			[
				'options' => [
					'default'   => 18,
					'min_range' => 0,
					'max_range' => 23,
				],
			]
		);

		$new_value['day'] = filter_var(
			$new_value['day'],
			FILTER_VALIDATE_INT,
			[
				'options' => [
					'default'   => get_option( 'start_of_week', 0 ),
					'min_range' => 0,
					'max_range' => 6,
				],
			]
		);

		return $new_value;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since  2.0.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( 'options-general.php' === $hook_suffix ) {
			wp_enqueue_script( 'digest', plugin_dir_url( PLUGIN_FILE ) . '/js/digest.js', [], VERSION, true );
			wp_enqueue_style( 'digest', plugin_dir_url( PLUGIN_FILE ) . '/css/digest.css', [], VERSION );
		}
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since  2.0.0
	 *
	 * @param array $links Plugin action links.
	 * @return array The modified plugin action links
	 */
	public function plugin_action_links( array $links ) {
		return array_merge(
			[
				'settings' => sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'options-general.php#digest' ) ),
					__( 'Settings', 'digest' )
				),
			],
			$links
		);
	}
}
