<?php
/**
 * Delete all traces on uninstall.
 *
 * @package WP_Digest
 */

defined( 'WPINC' ) or die;

delete_option( 'digest_frequency' );
delete_option( 'digest_hooks' );
delete_option( 'digest_queue' );
