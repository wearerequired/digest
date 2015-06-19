<?php
defined( 'WPINC' ) or die;

class WP_Digest_Plugin extends WP_Stack_Plugin2 {

	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * Plugin version.
	 */
	const VERSION = '0.1.0';

	/**
	 * Constructs the object, hooks in to `plugins_loaded`.
	 */
	protected function __construct() {
		$this->hook( 'plugins_loaded', 'add_hooks' );
	}

	/**
	 * Adds hooks.
	 */
	public function add_hooks() {
		$this->hook( 'init' );

		$this->hook( 'admin_enqueue_scripts' );
		$this->hook( 'admin_init', 'add_settings' );
	}

	/**
	 * Initializes the plugin, registers textdomain, etc.
	 */
	public function init() {
		$this->load_textdomain( 'digest', '/languages' );
	}

	/**
	 * Schedule our cronjob on plugin activation.
	 */
	public function activate_plugin() {
		// Get timestamp of the next full hour
		$current_time = current_time( 'timestamp' );
		$timestamp    = $current_time + ( 3600 - ( ( date( 'i', $current_time ) * 60 ) + date( 's', $current_time ) ) );

		wp_clear_scheduled_hook( 'digest_event' );
		wp_schedule_event( $timestamp, 'hourly', 'digest_event' );
	}

	/**
	 * Unschedule our cronjob on plugin deactivation.
	 */
	public function deactivate_plugin() {
		wp_unschedule_event( wp_next_scheduled( 'digest_event' ), 'digest_event' );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( 'options-general.php' === $hook_suffix ) {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_enqueue_script( 'digest', $this->get_url() . 'js/digest' . $suffix . '.js', array(), self::VERSION, true );
			wp_enqueue_style( 'digest', $this->get_url() . 'css/digest' . $suffix . '.css', array(), self::VERSION );
		}
	}

	/**
	 * Create settings sections and fields.
	 */
	public function add_settings() {
		add_settings_section(
			'digest_notifications',
			__( 'Email Notifcations', 'digest' ),
			function () {
				_e( "Get a daily or weekly digest of what's happening on your site instead of receiving a single email each time.", 'digest' );
			},
			'general'
		);

		add_settings_field(
			'digest_frequency',
			sprintf( '<label for="digest_frequency_period">%s</label>', __( 'Frequency', 'digest' ) ),
			array( $this, 'settings_field_frequency' ),
			'general',
			'digest_notifications'
		);

		register_setting( 'general', 'digest_frequency', array( $this, 'sanitize_frequency_option' ) );
	}

	/**
	 * Settings field callback that prints the actual input fields.
	 */
	public function settings_field_frequency() {
		$options = get_option( 'digest_frequency', array(
			'period' => 'weekly',
			'hour'   => 18,
			'day'    => absint( get_option( 'start_of_week' ) ),
		) );
		?>
		<p>
			<?php _e( 'Send me a digest of new site activity', 'digest' ); ?>
			<select name="digest_frequency[period]" id="digest_frequency_period">
				<option value="daily" <?php selected( 'daily', $options['period'] ); ?>><?php _ex( 'every day', 'frequency', 'digest' ); ?></option>
				<option value="weekly" <?php selected( 'weekly', $options['period'] ); ?>><?php _ex( 'every week', 'frequency', 'digest' ); ?></option>
			</select>
			<span id="digest_frequency_hour_wrapper">
				<?php _e( 'at', 'digest' ); ?>
				<select name="digest_frequency[hour]" id="digest_frequency_hour">
					<?php for ( $hour = 0; $hour <= 23; $hour ++ ) : ?>
						<option value="<?php echo esc_attr( $hour ); ?>" <?php selected( $hour, $options['hour'] ); ?>><?php printf( __( '%02d:00', 'digest' ), $hour ); ?></option>
					<?php endfor; ?>
				</select>
				<?php _e( "o'clock", 'digest' ); ?>
			</span>
			<span id="digest_frequency_day_wrapper" <?php echo 'weekly' !== $options['period'] ? 'class="digest-hidden"' : ''; ?>>
				<?php
				_e( 'on', 'digest' );

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
	 * @param array $value The POST da.
	 *
	 * @return array The sanitized frequency option.
	 */
	public function sanitize_frequency_option( $value ) {
		if ( 'daily' !== $value['period'] ) {
			$value['period'] = 'weekly';
		}

		$value['hour'] = filter_var(
			absint( $value['hour'] ),
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'default'   => 18,
					'min_range' => 0,
					'max_range' => 23,
				)
			)
		);

		$value['day'] = filter_var(
			absint( $value['day'] ),
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'default'   => get_option( 'start_of_week' ),
					'min_range' => 0,
					'max_range' => 6,
				)
			)
		);

		return $value;
	}
}
