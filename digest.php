<?php
/**
 * Plugin Name: Digest Notifications
 * Plugin URI:  https://github.com/wearerequired/digest/
 * Description: Get a daily/weekly digest of what's happening on your site instead of receiving a single email each time.
 * Version:     1.2.1
 * Author:      required+
 * Author URI:  http://required.ch
 * License:     GPLv2+
 * Text Domain: digest
 * Domain Path: /languages
 *
 * @package WP_Digest
 */

/**
 * Copyright (c) 2015 required+ (email : support@required.ch)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'WPINC' ) or die;

include( dirname( __FILE__ ) . '/lib/requirements-check.php' );

$wp_digest_requirements_check = new WP_Digest_Requirements_Check( array(
	'title' => 'Digest Notifications',
	'php'   => '5.3',
	'wp'    => '4.0',
	'file'  => __FILE__,
) );

if ( $wp_digest_requirements_check->passes() ) {
	// Pull in the plugin classes and initialize.
	include( dirname( __FILE__ ) . '/lib/wp-stack-plugin.php' );
	include( dirname( __FILE__ ) . '/includes/pluggable.php' );
	include( dirname( __FILE__ ) . '/classes/queue.php' );
	include( dirname( __FILE__ ) . '/classes/cron.php' );
	include( dirname( __FILE__ ) . '/classes/plugin.php' );
	WP_Digest_Plugin::start( __FILE__ );

	register_activation_hook( __FILE__, array( WP_Digest_Plugin::get_instance(), 'activate_plugin' ) );
	register_deactivation_hook( __FILE__, array( WP_Digest_Plugin::get_instance(), 'deactivate_plugin' ) );
}

unset( $wp_digest_requirements_check );
