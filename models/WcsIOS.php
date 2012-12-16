<?php
/**
 * @file
 * Defines the WcsIOS model class. 
 */

class WcsIOS
{
  /**
   * Generate schedule.json
   */
  public static function updateScheduleJson()
  {
    global $wpdb, $wp_locale;
    
    $multiDimArray = WcsSchedule::getClassesMultiDimArray( NULL, $ios = TRUE );

    if ( ! empty( $multiDimArray ) ) {
      foreach ( $multiDimArray as $key => $value ) {
        foreach ($value as $id => $class ) {
          $multiDimArray[$key][$id]->class_name = $class->getClassName();
          $multiDimArray[$key][$id]->instructor_name = $class->getInstructorName();
          $multiDimArray[$key][$id]->classroom_name = $class->getClassroomName();
          
          unset( $multiDimArray[$key][$id]->_tableName );
          unset( $multiDimArray[$key][$id]->visibility );
          unset( $multiDimArray[$key][$id]->time_created );
          unset( $multiDimArray[$key][$id]->user_created );
          unset( $multiDimArray[$key][$id]->time_modified );
          unset( $multiDimArray[$key][$id]->user_modified );
        }
      }
    }
    
    $output = json_encode( $multiDimArra );
    $fp = file_put_contents(WCS_PLUGIN_DIR . '/ios/schedule.json', $output);
    
    if ( $fp > 0 ) {
      self::updateItemJson( 'class' );
      self::updateItemJson( 'instructor' );
      self::updateItemJson( 'classroom' );
      self::updateWcsJson();
    }
  }
  
  /**
   * Generate class.json and instructor.json (based on $item)
   * 
   * @param string $item
   *  $item can be either class, instructor, or classroom.
   */
  public static function updateItemJson( $item )
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcs2_' . $item;
    $schedule_table = $wpdb->prefix . 'wcs2_schedule';
    $item_id = $item . '_id';
    $item_name = $item . '_name';
    $item_description = $item . '_description';
    
    $sql = "SELECT DISTINCT a.id, a.$item_name, a.$item_description FROM $table_name a ";
    $sql .= "INNER JOIN $schedule_table b ON a.id = b.$item_id WHERE b.visibility = 1";
    $sql = $wpdb->prepare( $sql, '' );
    $results = $wpdb->get_results( $sql );
    
    $items = array();
    foreach ( $results as $value ) {
      $items[$value->id] = $value;
      unset( $items[$value->id]->id );
    }
    
    $output = json_encode( $items );
    $fp = file_put_contents( WCS_PLUGIN_DIR . "/ios/$item.json", $output );
    
    if ( $fp > 0 )
      self::updateWcsJson( $item );
  }
  
  /**
   * Generate wcs.json which contains the site's name and a last_updated timestamp.
   */
  public static function updateWcsJson()
  {
    $settings = array(
      'site_name' => get_bloginfo( 'name' ),
      'last_updated' => time(),
    );
    
    $output = json_encode( $settings );
    $fp = file_put_contents( WCS_PLUGIN_DIR . "/ios/wcs.json", $output );
  }
  
  /**
   * Create the initial 5 base json files.
   */
  public static function generateInitialJsonFiles()
  {
    self::updateScheduleJson();
    self::updateItemJson( 'class' );
    self::updateItemJson( 'instructor' );
    self::updateItemJson( 'classroom' );
    self::updateWcsJson();
  }
}