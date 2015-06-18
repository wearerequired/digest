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
		// Add your hooks here
	}

	/**
	 * Initializes the plugin, registers textdomain, etc.
	 */
	public function init() {
		$this->load_textdomain( 'wp-digest', '/languages' );
	}
}
