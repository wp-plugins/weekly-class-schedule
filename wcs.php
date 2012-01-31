<?php
/*
Plugin Name: Weekly Class Schedule
Plugin URI: http://pulsarwebdesign.com/weekly-class-schedule
Description: Weekly Class Schedule generates a weekly schedule of classes. It provides you with an easy way to manage and update the schedule as well as the classes and instructors database.
Version: 1.2.5.1
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

define('WCS_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('WCS_LATEST_VERSION', '1.2.5.1');

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
require_once( 'wcs_functions.php' );
require_once( 'wcs_table.php' );
require_once( 'wcs_schedule.php' );
require_once( 'wcs_update.php' );
require_once( 'wcs_widget.php' );

// Load jQuery
function load_cdn_jquery() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js');
    wp_enqueue_script( 'jquery' );
}

add_action('wp_enqueue_scripts', 'load_cdn_jquery');

// Load scripts and styles for the entire website
function load_wcs_scripts_and_style() {
	wp_register_style( 'wcs_admin', WCS_PLUGIN_URL . 'css/wcs_admin.css' );
	wp_enqueue_style( 'wcs_admin' );
	wp_enqueue_script( 'jquery' );
}

add_action('init', 'load_wcs_scripts_and_style');

// Load scripts and styles for admin area
function load_wcs_admin_scripts_and_styles() {
	if ( is_admin() ) {
		wp_register_script( 'options_page_script', WCS_PLUGIN_URL . 'js/options_page.min.js');
		wp_enqueue_script( 'options_page_script' );
	}
}

add_action('admin_init', 'load_wcs_admin_scripts_and_styles');

// Load scripts and styles for website (no admin area support)
function load_wcs_website_scripts_and_styles() {
	if ( ! is_admin() ) {
		wp_register_style( 'wcs_style', WCS_PLUGIN_URL . 'css/wcs_style.css' );
		wp_enqueue_style( 'wcs_style' );
		wp_register_script( 'qtip_script', WCS_PLUGIN_URL . 'qtip/jquery.qtip.min.js');
		wp_enqueue_script( 'qtip_script' );
		wp_register_style( 'qtip_style', WCS_PLUGIN_URL . 'qtip/jquery.qtip.min.css' );
		wp_enqueue_style( 'qtip_style' );
		wp_register_script( 'hoverIntent_script', WCS_PLUGIN_URL . 'hoverIntent/jquery.hoverIntent.minified.js');
		wp_enqueue_script( 'hoverIntent_script' );
		wp_register_script( 'wcs_script', WCS_PLUGIN_URL . 'js/wcs.min.js');
		wp_enqueue_script( 'wcs_script' );
		wp_register_script( 'apply_qtip', WCS_PLUGIN_URL . 'qtip/apply.qtip.min.js');
		wp_enqueue_script( 'apply_qtip' );
	}
}
add_action('init', 'load_wcs_website_scripts_and_styles');

// ------- Plugin activation --------

// Create WcsTable objects
global $wpdb;
global $classes_obj;
global $instructors_obj;
global $classroom_obj;
$classes_obj = new WcsTable( 'class', 'studio' );
$instructors_obj = new WcsTable( 'instructor', 'studio' );
$classroom_obj = new WcsTable( 'classroom', 'studio' );

global $schedule_obj;
$assoc_tables_array = array( 'class', 'instructor', 'classroom' );
$schedule_obj = new WcsSchedule( 'studio', $assoc_tables_array );

global $update_obj;
$update_obj = new WcsUpdate();
define( 'WCS_VERSION', $wpdb->get_var( "SELECT option_value FROM " . $update_obj->table_name . " WHERE option_name = 'version'" ) );

// Create plugin tables on plugin activation
function create_wcs_table_objects() {
	global $classes_obj;
	global $instructors_obj;
	global $classroom_obj;
	global $schedule_obj;
	global $update_obj;
	$classes_obj->create_wcs_table();
	$instructors_obj->create_wcs_table();
	$classroom_obj->create_wcs_table();
	$classroom_obj->add_default_value( 'Classroom A', 'This is the default value' );
	$schedule_obj->create_wcs_schedule_table();
	$schedule_obj->add_timezone_column();
	$schedule_obj->add_visibility_column();
	$schedule_obj->add_classrooms_columns();

	$update_obj->update_version_number_in_database( WCS_LATEST_VERSION );
}
register_activation_hook( __FILE__, 'create_wcs_table_objects' );

function run_update_procedures() {
	if ( WCS_VERSION != WCS_LATEST_VERSION || WCS_VERSION == NULL ) {
		global $schedule_obj;
		global $classroom_obj;
		global $update_obj;
		$schedule_obj->add_timezone_column();
		$schedule_obj->add_visibility_column();
		$schedule_obj->add_classrooms_columns();
		$classroom_obj->create_wcs_table();
		$classroom_obj->add_default_value( 'Classroom A', 'This is the default value' );

		$update_obj->update_version_number_in_database( WCS_LATEST_VERSION );
	}
}

add_action( 'admin_init', 'run_update_procedures' );

// Multi-site installtion
function create_tables_for_slave_sites() {
	global $update_obj;
	$table_exists = $update_obj->check_wcs_tables( 'studio_schedule' );

	if ( !$table_exists ) {
		global $classes_obj;
		global $instructors_obj;
		global $classroom_obj;
		global $schedule_obj;

		$classes_obj->create_wcs_table();
		$instructors_obj->create_wcs_table();
		$classroom_obj->create_wcs_table();
		$classroom_obj->add_default_value( 'Classroom A', 'This is the default value' );
		$schedule_obj->create_wcs_schedule_table();
		$schedule_obj->add_timezone_column();
		$schedule_obj->add_visibility_column();
		$schedule_obj->add_classrooms_columns();

		$update_obj->update_version_number_in_database( WCS_LATEST_VERSION );
	}
}

add_action( 'admin_init', 'create_tables_for_slave_sites' );

// Un-install (remove) WCS tables
function wcs_uninstall() {
	global $wpdb;
	global $classes_obj;
	global $instructors_obj;
	global $schedule_obj;
	global $classroom_obj;

	remove_wcs_object_table( $classes_obj );
	remove_wcs_object_table( $instructors_obj );
	remove_wcs_object_table( $classroom_obj );
	remove_wcs_object_table( $schedule_obj );

	// Uninstall timezones table (if exists)
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "wcs_timezones" );
}

function clean_up_options_table() {
	global $update_obj;
	remove_wcs_object_table( $update_obj );
}
register_deactivation_hook( __FILE__, 'clean_up_options_table' );

// Generate WCS admin area menus
function wcs_admin_page() {
	global $schedule_obj;
	$enable_classrooms = get_option( 'enable_classrooms' );
	if ( $enable_classrooms == "on") {
		$schedule_obj->assoc_tables_array = array( 'class', 'instructor', 'classroom' );
		$schedule_obj->add_default_classrooms_to_entries( 'classroom' );
	} else {
		$schedule_obj->assoc_tables_array = array( 'class', 'instructor' );
	}
	$schedule_obj->manage_db_actions();
	if ( isset( $_GET['edit'] ) ) {
		$schedule_obj->print_wcs_admin_edit();
	} else {
		$schedule_obj->print_wcs_admin();
	}
}

function wcs_classes_admin_page() {
	global $classes_obj;
	$classes_obj->manage_db_actions();
	if ( isset( $_GET['edit'] ) ) {
		$classes_obj->print_wcs_admin_edit();
	} else {
		$classes_obj->print_wcs_admin();
	}
}
function wcs_instructors_admin_page() {
	global $instructors_obj;
	$instructors_obj->manage_db_actions();
	if ( isset( $_GET['edit'] ) ) {
		$instructors_obj->print_wcs_admin_edit();
	} else {
		$instructors_obj->print_wcs_admin();
	}
}
function wcs_classrooms_admin_page() {
	global $classroom_obj;
	$classroom_obj->manage_db_actions();
	if ( isset( $_GET['edit'] ) ) {
		$classroom_obj->print_wcs_admin_edit();
	} else {
		$classroom_obj->print_wcs_admin();
	}
}
function wcs_admin_options_page() {
	include_once( 'wcs_admin_options.php');
}
function wcs_admin_page_callback() {
	$enable_classrooms = get_option( 'enable_classrooms' );

	add_menu_page( 'WC Schedule', 'WC Schedule', 'manage_categories', 'wcs_admin_page', 'wcs_admin_page', 'http://pulsarwebdesign.com/sites/default/files/favicon.ico' );
	add_submenu_page( 'wcs_admin_page', 'Classes', 'Classes', 'manage_categories', 'wcs_classes_admin_menu', 'wcs_classes_admin_page' );
	add_submenu_page( 'wcs_admin_page', 'Instructors', 'Instructors', 'manage_categories', 'wcs_instructors_admin_menu', 'wcs_instructors_admin_page' );
	if ( $enable_classrooms == "on" ) {
	add_submenu_page( 'wcs_admin_page', 'Classrooms', 'Classrooms', 'manage_categories', 'wcs_classrooms_admin_menu', 'wcs_classrooms_admin_page' );
	}
	add_submenu_page( 'wcs_admin_page', 'Options', 'Options', 'manage_categories', 'wcs_options_admin_menu', 'wcs_admin_options_page' );
}
add_action( 'admin_menu', 'wcs_admin_page_callback' );

// Register options page
function register_wcs_settings() {
 	add_settings_section('wcs_main', 'WCS Main Settings', 'wcs_main_section_text', 'wcs_options_page');

 	// 24h support
 	register_setting( 'wcs_options', 'enable_24h' );
	add_settings_field( 'wcs_enable_24h', 'Enable 24h Mode', 'wcs_enable_24h_setting_fields', 'wcs_options_page', 'wcs_main' );

	// Timezones support
	register_setting( 'wcs_options', 'enable_timezones' );
	add_settings_field( 'wcs_enable_timezones', 'Enable Timezones', 'wcs_enable_timezones_setting_fields', 'wcs_options_page', 'wcs_main' );

	// Classroom support
	register_setting( 'wcs_options', 'enable_classrooms' );
	add_settings_field( 'wcs_enable_classrooms', 'Enable Classrooms', 'wcs_enable_classrooms_setting_fields', 'wcs_options_page', 'wcs_main' );
	
	// Unescaped Notes support
	register_setting( 'wcs_options', 'enable_unescaped_notes' );
	add_settings_field( 'wcs_enable_unescaped_notes', 'Enable Unescaped Notes', 'wcs_enable_unescaped_notes_setting_fields', 'wcs_options_page', 'wcs_main' );
}

add_action( 'admin_init', 'register_wcs_settings' );

function wcs_main_section_text() {
	echo '';
}

// 24h field output
function wcs_enable_24h_setting_fields() {
	$options = get_option( 'enable_24h' );
	$output = "<input id='enable_24h' name='enable_24h' type='checkbox' value='on'";
	if ( $options == "on" ) {
		$output .= " checked='yes' />";
	} else {
		$output .= " />";
	}
	echo $output;
}

// Timezones field output
function wcs_enable_timezones_setting_fields() {
	$options = get_option( 'enable_timezones' );
	$output = "<input id='enable_timezones' name='enable_timezones' type='checkbox' value='on'";
	if ( $options == "on" ) {
		$output .= " checked='yes' />";
	} else {
		$output .= " />";
	}
	echo $output;
}

// Classroom field output
function wcs_enable_classrooms_setting_fields() {
	$options = get_option( 'enable_classrooms' );
	$output = "<input id='enable_classrooms' name='enable_classrooms' type='checkbox' value='on'";
	if ( $options == "on" ) {
		$output .= " checked='yes' />";
	} else {
		$output .= " />";
	}
	echo $output;
}

// Unescaped notes field output
function wcs_enable_unescaped_notes_setting_fields() {
  $options = get_option( 'enable_unescaped_notes' );
  $output = "<input id='enable_unescaped_notes' name='enable_unescaped_notes' type='checkbox' value='on'";
  if ( $options == "on" ) {
    $output .= " checked='yes' />";
  } else {
    $output .= " />";
  }
  echo $output;
}

function add_timezones_table() {
	global $wpdb;
	$enable_timezones = get_option( 'enable_timezones' );
	if ( $enable_timezones == "on" ) {
		require_once( 'timezones.php' );
		dbDelta( $create_timezones_table );
		$wpdb->query( $insert_timezones_values );
	}
}

add_action( 'admin_init', 'add_timezones_table' );

// Add shortcode
function wcs_shortcode_callback( $atts ) {
	include_once( 'wcs_page.php' );
	return print_page_output( $atts );
}
add_shortcode( 'wcs', 'wcs_shortcode_callback' );