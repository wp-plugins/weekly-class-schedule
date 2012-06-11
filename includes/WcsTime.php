<?php
/**
 * @file
 * Defines the WcsTime class. 
 * 
 * This class is responsible for all time-related tasks such as
 * time collistion calculations, 24 mode vs AM/PM, etc..
 */

abstract class WcsTime
{
  /**
   * Renders an hour time field
   * 
   * @param string $name
   * 	The name of the form element to be used in form processing.
   * @param string $default
   * 	Format: hh:mm:ss
   */
  public static function renderHourSelectList( $name, $default = NULL )
  {
    $is_24 = ( get_option( 'wcs_24_hour_mode' ) == 'yes' ) ? TRUE : FALSE;
    $increment = get_option( 'wcs_time_increments', 15 );
    
    if ( $default != NULL ) {
      $time = explode( ':', $default );
    }
    
    if ( ( $time != FALSE ) && isset( $time[0] ) && isset( $time[1] ) ) {
      $default_hour = $time[0];
      $default_min = substr( $time[1], 0, 2 );
      
      if ( $is_24 == FALSE ) {
        $default_ampm = ( substr( $time[1], 3, 2) == 'pm' ) ? 'PM' : 'AM';
      }
      
    }
    else {
      $default_hour = 9;
      $default_min = NULL;
      $default_ampm = 'AM';
    }
    
    $minutes_array = range( 0, 59, $increment );
    
    foreach ( $minutes_array as $key => $value ) {
      $minutes_array[$key] = str_pad( $value, 2, '0', STR_PAD_LEFT );
    }
    
    $minutes = WcsHtml::generateSelectList( $minutes_array, array( 'name' => $name . '_minutes' ), FALSE, $default_min );
    
    if ( $is_24 ) {
      /* 24 hour mode */
      $hours = WcsHtml::generateSelectList( range( 0, 23 ), array( 'name' => $name . '_hours' ), FALSE, $default_hour );
      $form = "<td>$hours</td><td>$minutes</td>";
    }
    else {
      /* AM/PM mode */
      $hours = WcsHtml::generateSelectList( range( 1, 12 ), array( 'name' => $name . '_hours' ), FALSE, $default_hour );
      $am_pm = WcsHtml::generateSelectList( array( 'AM', 'PM' ), array( 'name' => $name . '_ampm' ), FALSE, $default_ampm );
      $form = "<td>$hours</td><td>$minutes</td><td>$am_pm</td>";
    }
    
    $output = '<table class="hour-form-table">';
    $output .= '<tr>';
    
    $output .= $form;
    
    $output .= '</tr>';
    $output .= '</table>';
    
    return $output;
  }
  
  /**
   * Renders a list of timezone and defaults to the server timezone.
   * 
   * @param string $name
   * 	The name of the form element to be used in form processing.
   * @param string $default
   */
  public static function renderTimezonesSelectList( $name, $default = NULL )
  {
    $server_timezone = self::getDefaultTimezone();
    $timezones_list = DateTimeZone::listIdentifiers();
    
    if ( $default == NULL )
      $default = $server_timezone;
    
    $option_groups = array(
    	"Africa",
      "America",
      "Antarctica",
      "Arctic",
      "Asia",
      "Atlantic",
      "Australia",
      "Europe",
      "Indian",
      "Pacific",
    );
    
    $output = "<select id='timezone_string' name='timezone'>";
    
    foreach ( $option_groups as $group ) {
      $group_timezones = array();
      
      $output .= "<optgroup label='$group'>";
      
      foreach ( $timezones_list as $timezone ) {
        if ( preg_match( "/^$group/", $timezone ) > 0 ) {
          $short_timezone = str_replace( $group . '/', '', $timezone );
          $group_timezones[$timezone] = $short_timezone;
        }
      }
      
      foreach ( $group_timezones as $timezone => $short_timezone ) {
        if ( $timezone == $default ) {
          $output .= "<option value='$timezone' selected='selected'>$short_timezone</option>";
        }
        else
          $output .= "<option value='$timezone'>$short_timezone</option>";
      }
      
      $output .= '</optgroup>';
    }
    
    $output .= '</select>';
    return $output;
  }
  
  public static function filterTimezones( $timezone, $group )
  {
    return;
  }
  
  /**
   * Returns the installation default timezone. The method first checks for a WP
   * setting and if it can't find it, it uses the server setting. If the server setting
   * is also missing, the string UTC will be used.
   */
  public static function getDefaultTimezone()
  {
    $php_timezone = ( ini_get('date.timezone') ) ? ini_get('date.timezone') : 'UTC';
    $wp_timezone = get_option( 'timezone_string' );
    
    return ( $wp_timezone == '' ) ? $php_timezone : $wp_timezone;
  }
  
  public static function prepareHour( $hour, $minute, $ampm = NULL )
  {
    if ( $ampm == NULL ) {
      /* 24 Hour mode */
      $output = $hour . ':' . $minute . ':00';
    }
    else {
      /* AM/PM mode */
      if ( $ampm != 'AM' && $ampm != 'PM' )
        return FALSE;
      
      $output = date( 'H:i:s', strtotime( $hour . ':' . $minute . ' ' . $ampm, 0 ) );
    }
    
    return $output;
  }
  
  /**
   * Converts a time string into WCS database time format.
   * 
   * @param string $time
   */
  public static function convertTimeToDbFormat( $time )
  {
    $time = date( 'H:i', strtotime( $time, 0 ) ) . ':00';
    return $time;
  }
}