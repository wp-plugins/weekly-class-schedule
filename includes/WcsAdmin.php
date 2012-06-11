<?php
/**
 * @file
 * Defines the WCS Admin class
 */

abstract class WcsAdmin
{
  public static function init()
  {
    /* Register admin menu action */
    add_action( 'admin_menu', array( 'WcsAdmin', 'wcs_admin_callback' ) );
    add_action( 'admin_menu', array( 'WcsOptions', 'wcs_options' ) );
    
    /* Load colorpicker */
    wp_register_style( 'wcs_colorpicker_stylesheet', WCS_PLUGIN_URL . '/plugins/colorpicker/css/colorpicker.css' );
    wp_enqueue_style( 'wcs_colorpicker_stylesheet' );
    wp_register_script( 'wcs_colorpicker', WCS_PLUGIN_URL . '/plugins/colorpicker/js/colorpicker.js' );
    wp_enqueue_script( 'wcs_colorpicker' );
    
    /* Load admin CSS and scripts */
    wp_register_style( 'wcs_admin_stylesheet', WCS_PLUGIN_URL . '/css/wcs_admin.css' );
    wp_enqueue_style( 'wcs_admin_stylesheet' );
    wp_register_script( 'wcs_admin_script', WCS_PLUGIN_URL . '/js/wcs_admin.js' );
    wp_enqueue_script( 'wcs_admin_script' );
  }
  
  /* Create menu items */
  public static function wcs_admin_callback() {
    $wc_schedule = __( 'WC Schedule' );
    $classes = __( 'Classes' );
    $instructors = __( 'Instructors' );
    $classrooms = __( 'Classrooms' );
    $options = __( 'Options' );
    
    add_menu_page( $wc_schedule, $wc_schedule, 'manage_options', 'wcs-schedule', array( 'WcsAdmin', 'wcs_schedule_admin_page' ), WCS_PLUGIN_URL . '/images/favicon.ico' );
    add_submenu_page( 'wcs-schedule', $classes, $classes, 'manage_options', 'wcs-classes', array( 'WcsAdmin', 'wcs_classes_admin_page' ) );
    add_submenu_page( 'wcs-schedule', $instructors, $instructors, 'manage_options', 'wcs-instructors', array( 'WcsAdmin', 'wcs_instructors_admin_page' ) );
    add_submenu_page( 'wcs-schedule', $classrooms, $classrooms, 'manage_options', 'wcs-classrooms', array( 'WcsAdmin', 'wcs_classrooms_admin_page' ) );
    add_submenu_page( 'wcs-schedule', $options, $options, 'manage_options', 'wcs-options', array( 'WcsAdmin', 'wcs_admin_options_page' ) );
  }
  
  /* Schedule admin page */
  public static function wcs_schedule_admin_page() {
    $class_controller = new WcsController( 'schedule' );
    $data['items'] = WcsSchedule::model()->getCols( array( 'id' ) );
    $class_controller->render( 'ScheduleAdmin', $data, 'schedule' );
  }
  
  /* Class admin page */
  public static function wcs_classes_admin_page() {
    $class_controller = new WcsController( 'class' );
    $data['items'] = WcsClass::model()->getCols( array( 'id', 'class_name', 'class_description' ) );
    $class_controller->render( 'ClassAdmin', $data );
  }
  
  /* Instructor admin page */
  public static function wcs_instructors_admin_page() {
    $instructor_controller = new WcsController( 'instructor' );
    $data['items'] = WcsInstructor::model()->getCols( array( 'id', 'instructor_name', 'instructor_description' ) );
    $instructor_controller->render( 'InstructorAdmin', $data );
  }
  
  /* Classroom admin page */
  public static function wcs_classrooms_admin_page() {
    $classroom_controller = new WcsController( 'classroom' );
    $data['items'] = WcsClassroom::model()->getCols( array( 'id', 'classroom_name', 'classroom_description' ) );
    $classroom_controller->render( 'ClassroomAdmin', $data );
  }
  
  /* Options page */
  public static function wcs_admin_options_page() {
    WcsOptions::renderOptionsPage();
  }
}