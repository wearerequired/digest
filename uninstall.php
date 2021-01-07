<?php
/**
 * Delete all traces on uninstall.
 */

defined( 'WPINC' ) || die;

defined( 'WP_UNINSTALL_PLUGIN' ) || die;

delete_option( 'digest_frequency' );
delete_option( 'digest_hooks' );
delete_option( 'digest_queue' );
