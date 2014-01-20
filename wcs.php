<?php
/*
Plugin Name: Weekly Class Schedule
Plugin URI: http://pulsarwebdesign.com/weekly-class-schedule
Description: Weekly Class Schedule generates a weekly schedule of classes. It provides you with an easy way to manage and update the schedule as well as the classes and instructors database.
Version: 3.02
Text Domain: wcs3
Author: Pulsar Web Design
Author URI: http://pulsarwebdesign.com
License: GPL2

Copyright 2011  Pulsar Web Design  (email : info@pulsarwebdesign.com)

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

define( 'WCS3_VERSION', '3.02' );

define( 'WCS3_REQUIRED_WP_VERSION', '3.0' );

if ( ! defined( 'WCS3_PLUGIN_BASENAME' ) )
	define( 'WCS3_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'WCS3_PLUGIN_NAME' ) )
	define( 'WCS3_PLUGIN_NAME', trim( dirname( WCS3_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'WCS3_PLUGIN_DIR' ) )
	define( 'WCS3_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

if ( ! defined( 'WCS3_PLUGIN_URL' ) )
	define( 'WCS3_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

if ( ! defined( 'WCS3_ADMIN_READ_CAPABILITY' ) )
	define( 'WCS3_ADMIN_READ_CAPABILITY', 'edit_posts' );

if ( ! defined( 'WCS3_ADMIN_READ_WRITE_CAPABILITY' ) )
	define( 'WCS3_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );

if ( ! defined( 'WCS3_DB_VERSION' ) )
	define( 'WCS3_DB_VERSION', '1.0' );

if ( ! defined( 'WCS3_BASE_DATE' ) )
	define( 'WCS3_BASE_DATE', '2001-01-01' );

/**
 * List of allowed HTML tags for the notes field (if enabled).
 * 
 * @see http://codex.wordpress.org/Function_Reference/wp_kses
 */
$wcs3_allowed_html = array(
            'a' => array(
                'href' => true,
                'title' => true,
            ),
            'abbr' => array(
                'title' => true,
            ),
            'acronym' => array(
                'title' => true,
            ),
            'b' => array(),
            'blockquote' => array(
                'cite' => true,
            ),
            'cite' => array(),
            'code' => array(),
            'del' => array(
                'datetime' => true,
            ),
            'em' => array(),
            'i' => array(),
            'q' => array(
                'cite' => true,
            ),
            'strike' => array(),
            'strong' => array(),
	    );

/**
 * A global data structure to allow for passing of Javascript data to the
 * front end.
 */
$wcs3_js_data = array();

/**
 * Load modules.
 */
require_once WCS3_PLUGIN_DIR . '/wcs3_modules.php';

/**
 * Returns the schedule table name including prefix.
 */
function wcs3_get_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'wcs3_schedule';
}

/**
 * Create the class, instructor, and classroom post types.
 */
add_action( 'init', 'wcs3_create_post_types' );

function wcs3_create_post_types() {
    // Register class
	register_post_type( 'wcs3_class',
		array(
    		'labels' => array(
        		'name' => __( 'Classes', 'wcs3' ),
        		'singular_name' => __( 'Class', 'wcs3' )
    		),
    		'public' => true,
    		'has_archive' => true,
		)
	);
	
	// Register instructor
	register_post_type( 'wcs3_instructor',
		array(
			'labels' => array(
			'name' => __( 'Instructors', 'wcs3' ),
			'singular_name' => __( 'Instructor', 'wcs3' )
			),
			'public' => true,
			'has_archive' => true,
		)
	);
	
	// Register location
	register_post_type( 'wcs3_location',
		array(
			'labels' => array(
			'name' => __( 'Locations', 'wcs3' ),
			'singular_name' => __( 'Location', 'wcs3' )
			),
			'public' => true,
			'has_archive' => true,
		)
	);
}

/**
 * Register admin pages (schedule management, settings, etc...).
 */
function wcs3_register_schedule_management_page() {
    // Schedule page
    add_menu_page( __( 'Schedule Management', 'wcs3' ), 
            __( 'Schedule', 'wcs3' ), 
            WCS3_ADMIN_READ_WRITE_CAPABILITY, 
            'wcs3-schedule',
            'wcs3_schedule_management_page_callback' );
    
    // Standard settings page
    add_submenu_page( 'wcs3-schedule', 
            __( 'Options', 'wcs3' ), 
            __( 'Options', 'wcs3' ), 
            'manage_options', 
            'wcs3-standard-options', 
            'wcs3_standard_settings_page_callback' );
}

add_action( 'admin_menu', 'wcs3_register_schedule_management_page' );

/* Activation procedure */
function wcs3_load_plugin() {

	if ( is_admin() && get_option( 'wcs3_activated' ) == 'weekly-class-schedule' ) {
		delete_option( 'wcs3_activated' );
		
		/* do stuff once right after activation */
    	// Create db tables
    	wcs3_create_db_tables();
    
    	load_plugin_textdomain( 'wcs3' );
    
    	// Run default settings hook.
    	do_action( 'wcs3_default_settings' );
    
    	// Update old versions
    	$wcs3_version = get_option( 'wcs3_version' );
    	if ( $wcs3_version === FALSE ) {
    		// New installation, let's try and get data from wcs2
    		$wcs2_static_data = wcs3_get_static_wcs2_data();
    		$new_ids = wcs3_create_new_wcs3_static_data( $wcs2_static_data );
    		$wcs2_schedule = wcs3_get_wcs2_schedule_data( $new_ids );
    		add_option( 'wcs3_version', WCS3_VERSION);
    	}
    	else if ( $wcs3_version < WCS3_VERSION ) {
    		// We've got an update, let's do this thing.
    		// pass
    	}
	}
}
add_action( 'admin_init', 'wcs3_load_plugin' );

/**
 * Installation of schedule db table
 */
function wcs3_install() {
    add_option( 'wcs3_activated', 'weekly-class-schedule' );
}
register_activation_hook( __FILE__, 'wcs3_install' );
