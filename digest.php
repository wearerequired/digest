<?php
/**
 * Plugin Name: Digest Notifications
 * Plugin URI:  https://required.com/services/wordpress-plugins/digest-notifications/
 * Description: Get a daily or weekly digest of what's happening on your site
 *              instead of receiving a single email each time.
 * Version:     2.0.0-alpha
 * Author:      required
 * Author URI:  https://required.com
 * License:     GPLv2+
 * Text Domain: digest
 * Domain Path: /languages
 *
 * @package Digest
 */

/**
 * Copyright (c) 2015-2107 required (email : support@required.ch)
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

defined( 'ABSPATH' ) or die;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	include __DIR__ . '/vendor/autoload.php';
}

if ( ! class_exists( 'Required\\Digest\\Plugin' ) ) {
	trigger_error( sprintf( '%s does not exist. Check Composer\'s autoloader.',  'Required\\Digest\\Plugin' ) );
	return;
}

$requirements_check = new WP_Requirements_Check( [
	'title' => __( 'Digest Notifications', 'digest' ),
	'php'   => '5.3',
	'wp'    => '4.4',
	'file'  => __FILE__,
] );

if ( $requirements_check->passes() ) {
	require_once __DIR__ . '/includes/pluggable.php';
	require_once __DIR__ . '/includes/functions.php';
	require_once __DIR__ . '/inc/namespace.php';

	add_action( 'plugins_loaded', 'Required\\Digest\\bootstrap' );
}

unset( $requirements_check );
