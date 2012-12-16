<?php
/*
Plugin Name: Weekly Class Schedule
Plugin URI: http://pulsarwebdesign.com/weekly-class-schedule
Description: Weekly Class Schedule generates a weekly schedule of classes. It provides you with an easy way to manage and update the schedule as well as the classes and instructors database.
Version: 2.0.5
Author: Pulsar Web Design
Author URI: http://pulsarwebdesign.com
License: GPL2

Copyright 2011  Pulsar Web Design  (email : ty@pulsarwebdesign.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* This version does not use late static binding and can work on PHP 5.2 and higher */

/*
 * Define constants
 */
define('WCS_VERSION', '2.0.5');

if ( ! defined( 'WCS_PLUGIN_BASENAME' ) )
	define( 'WCS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'WCS_PLUGIN_NAME' ) )
	define( 'WCS_PLUGIN_NAME', trim( dirname( WCS_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'WCS_PLUGIN_DIR' ) )
	define( 'WCS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WCS_PLUGIN_NAME );

if ( ! defined( 'WCS_PLUGIN_URL' ) )
	define( 'WCS_PLUGIN_URL', WP_PLUGIN_URL . '/' . WCS_PLUGIN_NAME );

require_once WCS_PLUGIN_DIR . '/includes/WcsApp.php';

WcsApp::init();