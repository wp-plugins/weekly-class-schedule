<?php
/**
 * @file
 * WcsDb class definition
 */

abstract class WcsDb
{
  public static function createWcs2Tables() {
    global $wpdb;
    $wpdb->show_errors();

    // Create tables SQL
    $sql = "CREATE TABLE " . $wpdb->prefix . "wcs2_class (
  			id int(11) NOT NULL AUTO_INCREMENT,
  			class_name varchar(256) DEFAULT '' NOT NULL,
  			class_description text,
  			time_created varchar(50) NOT NULL DEFAULT '',
  			user_created bigint(20) NOT NULL,
  			time_modified varchar(50) NOT NULL DEFAULT '',
  			user_modified bigint(20) NOT NULL,
  			PRIMARY KEY  (id),
  			UNIQUE KEY class_name (class_name)
  			);
  			CREATE TABLE " . $wpdb->prefix . "wcs2_classroom (
  			id int(11) NOT NULL AUTO_INCREMENT,
  			classroom_name varchar(256) NOT NULL DEFAULT '',
  			classroom_description text,
  			time_created varchar(50) NOT NULL DEFAULT '',
  			user_created bigint(20) NOT NULL,
  			time_modified varchar(50) NOT NULL DEFAULT '',
  			user_modified bigint(20) NOT NULL,
  			PRIMARY KEY  (id),
  			UNIQUE KEY classroom_name (classroom_name)
  			);
  			CREATE TABLE " . $wpdb->prefix . "wcs2_instructor (
  			id int(11) NOT NULL AUTO_INCREMENT,
  			instructor_name varchar(256) NOT NULL DEFAULT '',
  			instructor_description text,
  			time_created varchar(50) NOT NULL DEFAULT '',
  			user_created bigint(20) NOT NULL,
  			time_modified varchar(50) NOT NULL DEFAULT '',
  			user_modified bigint(20) NOT NULL,
  			PRIMARY KEY  (id),
  			UNIQUE KEY instructor_name (instructor_name)
  			);
  			CREATE TABLE " . $wpdb->prefix . "wcs2_schedule (
  			id int(11) NOT NULL AUTO_INCREMENT,
  			class_id int(11) NOT NULL,
  			instructor_id int(11) NOT NULL,
  			classroom_id int(11) NOT NULL,
  			weekday varchar(32) NOT NULL,
  			start_hour time NOT NULL,
  			end_hour time NOT NULL,
  			timezone varchar(32) DEFAULT NULL,
  			visibility int(11) NOT NULL DEFAULT '1',
  			time_created varchar(50) NOT NULL DEFAULT '',
  			user_created bigint(20) NOT NULL,
  			time_modified varchar(50) NOT NULL DEFAULT '',
  			user_modified bigint(20) NOT NULL,
  			notes text,
  			PRIMARY KEY  (id)
  			);";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  }

  public static function dropOldWcsTables() {
    global $wpdb;
    $sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . "wcs_class, ";
    $sql .= $wpdb->prefix . "wcs_instructor, ";
    $sql .= $wpdb->prefix . "wcs_classroom, ";
    $sql .= $wpdb->prefix . "wcs_options, ";
    $sql .= $wpdb->prefix . "wcs_timezones, ";
    $sql .= $wpdb->prefix . "wcs_studio_schedule ";
    $sql = $wpdb->prepare( $sql );
    $wpdb->query( $sql );
  }

  /**
   * Migrate data from original WCS tables to new WCS 2.0 tables.
   */
  public static function migrateOldData()
  {
    global $wpdb;

    /* Import Classes */
    $class_table = $wpdb->prefix . 'wcs_class';
    $class_sql = $wpdb->prepare( "SELECT item_name, item_description FROM $class_table" );
    $class_results = $wpdb->get_results( $class_sql );

    if ( ! empty( $class_results ) ) {
      foreach ( $class_results as $class ) {
        $record = new WcsClass();
        $record->class_name = $class->item_name;
        $record->class_description = $class->item_description;

        $record->setTimeUserValues();
        $record->save( FALSE, TRUE );
      }
    }

    /* Import instructors */
    $instructor_table = $wpdb->prefix . 'wcs_instructor';
    $instructor_sql = $wpdb->prepare( "SELECT item_name, item_description FROM $instructor_table" );
    $instructor_results = $wpdb->get_results( $instructor_sql );

    if ( ! empty( $instructor_results ) ) {
      foreach ( $instructor_results as $instructor ) {
        $record = new WcsInstructor();
        $record->instructor_name = $instructor->item_name;
        $record->instructor_description = $instructor->item_description;

        $record->setTimeUserValues();
        $record->save( FALSE, TRUE );
      }
    }

    /* Import classrooms */
    $classroom_table = $wpdb->prefix . 'wcs_classroom';
    $classroom_sql = $wpdb->prepare( "SELECT item_name, item_description FROM $classroom_table" );
    $classroom_results = $wpdb->get_results( $classroom_sql );

    if ( ! empty( $classroom_results ) ) {
      foreach ( $classroom_results as $classroom ) {
        $record = new WcsClassroom();
        $record->classroom_name = $classroom->item_name;
        $record->classroom_description = $classroom->item_description;

        $record->setTimeUserValues();
        $record->save( FALSE, TRUE );
      }
    }

    /* Import schedule */
    $entries_table = $wpdb->prefix . 'wcs_studio_schedule';
    $entries_sql = $wpdb->prepare( "SELECT * FROM $entries_table" );
    $entries_results = $wpdb->get_results( $entries_sql );

    if ( ! empty( $entries_results ) ) {
      global $wp_locale;
      $weekdays = array_flip( $wp_locale->weekday );
      
      foreach ( $entries_results as $entries ) {
        $record = new WcsSchedule();
        $record->class_id = $entries->class_id;
        $record->instructor_id = $entries->instructor_id;
        $record->classroom_id = $entries->classroom_id;
        $record->weekday = $weekdays[$entries->week_day];
        $record->start_hour = $entries->start_hour;
        $record->end_hour = $entries->end_hour;
        $record->timezone = WcsTime::getDefaultTimezone();
        $record->notes = $entries->notes;

        $record->setTimeUserValues();
        $record->save( FALSE, TRUE );
      }
    }
  }
}