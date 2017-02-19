<?php
/**
 * Delete all traces on uninstall.
 *
 * @package Digest
 */

defined( 'WPINC' ) or die;

defined( 'WP_UNINSTALL_PLUGIN' ) or die;

delete_option( 'digest_frequency' );
delete_option( 'digest_hooks' );
delete_option( 'digest_queue' );
