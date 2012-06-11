<?php
/**
 * Defines the WcsOutputController class.
 */

class WcsOutputController
{
  public static function wcsShortcodeCallback( $attr )
  {
    $output = '';
    $classroom = $attr['classroom'];
    $weekday_names = array();
    
    $use_short_day_names = ( get_option( 'wcs_short_day_names', 'yes' ) == 'yes' ) ? TRUE : FALSE;
    $weekdays = WcsSchedule::model()->getWeekDaysArray();
    
    if ( ! $use_short_day_names )
      $weekday_names = $weekdays;
    else {
      $weekday_names = WcsSchedule::model()->getWeekDaysArray( FALSE, TRUE );
    }
    
    /* Load multiple schedules each for a single classrooms */
    if ( isset( $classroom ) && ! empty( $classroom ) ) {
      $classes = WcsSchedule::model()->getClassesMultiDimArray( $classroom );
      $start_hours = WcsSchedule::model()->getStartHours( $classroom );
    }
    else {
      /* Load all classrooms in one schedule */
      $classes = WcsSchedule::model()->getClassesMultiDimArray();
      $start_hours = WcsSchedule::model()->getStartHours();
    }

    if ( ! $classes || ! $start_hours ) {
      $output = 'No Classes';
      return $output;
    }
    
    
    if ( isset( $attr['layout'] ) ) {
      if ( $attr['layout'] == 'vertical' ) {
        $view = WCS_PLUGIN_DIR . '/views/WcsOutputView.php';
      }
      elseif ( $attr['layout'] == 'horizontal' ) {
        $view = WCS_PLUGIN_DIR . '/views/WcsOutputHorizontalView.php';
      }
      elseif ( $attr['layout'] == 'list' ) {
        $view = WCS_PLUGIN_DIR . '/views/WcsOutputListView.php';
      }
    }
    else {
      $view = WCS_PLUGIN_DIR . '/views/WcsOutputView.php';
    }

    
    ob_start();
    include $view;
    
    $output .= ob_get_contents();
    ob_end_clean();
    
    return $output;
  }
}