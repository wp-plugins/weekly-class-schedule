<?php
/*
Plugin Name: Weekly Class Schedule
Plugin URI: http://pulsarwebdesign.com/weekly-class-schedule
Description: Weekly Class Schedule generates a weekly schedule of classes. It provides you with an easy way to manage and update the schedule as well as the classes and instructors database.
Version: 1.1
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

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
require_once( 'wcs_functions.php' );
require_once( 'wcs_table.php' );
require_once( 'wcs_schedule.php' );

// Load jQuery
function load_cdn_jquery() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js');
    wp_enqueue_script( 'jquery' );
}

add_action('wp_enqueue_scripts', 'load_cdn_jquery');

// Load scripts and styles for the entire website
function load_wcs_scripts_and_style() {
	wp_register_style( 'wcs_admin', WCS_PLUGIN_URL . '/css/wcs_admin.css' );
	wp_enqueue_style( 'wcs_admin' );
	wp_enqueue_script( 'jquery' );
}

add_action('init', 'load_wcs_scripts_and_style'); 

// Load scripts and styles for admin area
function load_wcs_admin_scripts_and_styles() {
	if ( is_admin() ) {
		wp_register_script( 'options_page_script', WCS_PLUGIN_URL . '/js/options_page.min.js');
		wp_enqueue_script( 'options_page_script' );
	}
}

add_action('admin_init', 'load_wcs_admin_scripts_and_styles');

// Load scripts and styles for website (no admin area support)
function load_wcs_website_scripts_and_styles() {
	if ( ! is_admin() ) {
		wp_register_style( 'wcs_style', WCS_PLUGIN_URL . '/css/wcs_style.css' );
		wp_enqueue_style( 'wcs_style' );
		wp_register_script( 'qtip_script', WCS_PLUGIN_URL . '/qtip/jquery.qtip.min.js');
		wp_enqueue_script( 'qtip_script' );
		wp_register_style( 'qtip_style', WCS_PLUGIN_URL . '/qtip/jquery.qtip.min.css' );
		wp_enqueue_style( 'qtip_style' );
		wp_register_script( 'hoverIntent_script', WCS_PLUGIN_URL . '/hoverIntent/jquery.hoverIntent.minified.js');
		wp_enqueue_script( 'hoverIntent_script' );
		wp_register_script( 'wcs_script', WCS_PLUGIN_URL . '/js/wcs.min.js');
		wp_enqueue_script( 'wcs_script' );
		wp_register_script( 'apply_qtip', WCS_PLUGIN_URL . '/qtip/apply.qtip.min.js');
		wp_enqueue_script( 'apply_qtip' );
	}
}
add_action('init', 'load_wcs_website_scripts_and_styles');

// ------- Plugin activation --------

// Create WcsTable objects
global $classes_obj;
global $instructors_obj;
$classes_obj = new WcsTable( 'class', 'studio' );
$instructors_obj = new WcsTable( 'instructor', 'studio' );

global $schedule_obj;
$assoc_tables_array = array( 'class', 'instructor' );
$schedule_obj = new WcsSchedule( 'studio', $assoc_tables_array );

// Create plugin tables on plugin activation
function create_wcs_table_objects() {
	global $classes_obj;
	global $instructors_obj;
	global $schedule_obj;
	$classes_obj->create_wcs_table();
	$instructors_obj->create_wcs_table();
	$schedule_obj->create_wcs_schedule_table();
}
register_activation_hook( __FILE__, 'create_wcs_table_objects' );

// Un-install (remove) WCS tables
function wcs_uninstall() {
	global $classes_obj;
	global $instructors_obj;
	global $schedule_obj;
	
	remove_wcs_object_table( $classes_obj );
	remove_wcs_object_table( $instructors_obj );
	remove_wcs_object_table( $schedule_obj );
}

// Generate WCS admin area menus
function wcs_admin_page() {
	global $schedule_obj;
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
function wcs_admin_options_page() {
	include_once( 'wcs_admin_options.php');
}
function wcs_admin_page_callback() {
	add_menu_page( 'WC Schedule', 'WC Schedule', 'manage_categories', 'wcs_admin_page', 'wcs_admin_page', 'http://pulsarwebdesign.com/sites/default/files/favicon.ico' );
	add_submenu_page( 'wcs_admin_page', 'Classes', 'Classes', 'manage_categories', 'wcs_classes_admin_menu', 'wcs_classes_admin_page' );
	add_submenu_page( 'wcs_admin_page', 'Instructors', 'Instructors', 'manage_categories', 'wcs_instructors_admin_menu', 'wcs_instructors_admin_page' );
	add_submenu_page( 'wcs_admin_page', 'Options', 'Options', 'manage_categories', 'wcs_options_admin_menu', 'wcs_admin_options_page' );
}
add_action( 'admin_menu', 'wcs_admin_page_callback' );

// Register options page
function register_wcs_settings() { // whitelist options
 	register_setting( 'wcs_options', 'enable_24h' );
	add_settings_section('wcs_main', 'WCS Main Settings', 'wcs_main_section_text', 'wcs_options_page');
	add_settings_field('wcs_enable_24h', 'Enable 24h Mode', 'wcs_main_setting_fields', 'wcs_options_page', 'wcs_main');
}

add_action( 'admin_init', 'register_wcs_settings' );

function wcs_main_section_text() {
	echo '';
}
function wcs_main_setting_fields() {
	$options = get_option( 'enable_24h' );
	$output = "<input id='enable_24h' name='enable_24h' type='checkbox'";
	if ( $options == "on" ) {
		$output .= " checked='yes' value='off' />";
	} else {
		$output .= " value='on' />";
	}
	echo $output;
}

// Add shortcode
function wcs_shortcode_callback( $atts ) {
	include_once( 'wcs_page.php' );
}
add_shortcode( 'wcs', 'wcs_shortcode_callback' );