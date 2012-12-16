<?php

/**
 * @file
 * Defines the WcsClass class.
 */
class WcsSchedule extends WcsActiveRecord
{
  public $_tableName;

  public function __construct()
  {
    $this->_tableName = $this->tableName();
  }

  /**
   * Returns an instance of the class.
   */
  public static function model()
  {
    return new WcsSchedule();
  }

  private function tableName()
	{
		global $wpdb;
		return $wpdb->prefix . 'wcs2_schedule';
	}

	/**
	 * Returns the week days array arranged as per the options 'wcs_first_day_of_week'
	 * and 'wcs_number_of_days.
	 *
	 * @param int $number_of_days
	 * 	Number of days to create
	 */
	public static function getWeekDaysArray( $ignore_number_of_days = FALSE, $short_names = FALSE )
	{
	  if ( $short_names == TRUE ) {
	    $weekday_names = self::generateShortWeekdays();
	  }
	  else {
	    $weekday_names = self::generateWeekdays();
	  }

	  $start_day = get_option( 'wcs_first_day_of_week', 1 );

	  if ( $ignore_number_of_days == TRUE)
	    $number_of_days = 7;
	  else
	    $number_of_days = get_option( 'wcs_number_of_days', 7 );

	  $weekday_values = array();

	  $i = 0;
	  foreach ( $weekday_names as $name ) {
	    $weekday_values[$name] = $i;
	    $i++;
	  }

	  $array_one = array_slice( $weekday_names, $start_day );
	  $array_two = array_slice( $weekday_names, 0, $start_day );
	  $weekday_names = array_merge( $array_one, $array_two );
	  $weekday_names = array_slice( $weekday_names, 0, $number_of_days );

	  foreach ( $weekday_names as $weekday ) {
	    $weekdays[$weekday_values[$weekday]] = $weekday;
	  }

	  return $weekdays;
	}

	/**
	 * Returns a sorted array only of weekdays which are used in the database.
	 */
	public static function getDbSortedWeekdays()
	{
	  if ( is_array( WcsSchedule::model()->getCol( 'weekday' ) ) )
	   $db_weekdays = array_unique( WcsSchedule::model()->getCol( 'weekday' ) );
	  else
	    return;

	  $weekdays = self::generateWeekdays();

	  foreach ( $weekdays as $key => $weekday ) {
	    if ( ! in_array( $key, $db_weekdays ) )
	      unset( $weekdays[$key] );
	  }

	  return $weekdays;
	}

	/**
	 * Returns the start hours array sorted and formated according to user preference
	 * and only of active weekdays.
	 *
	 * @parram string $classroom
	 * 	If set, only the entries for the specified classroom will be retrieved.
	 */
	public static function getStartHours( $classroom = NULL )
	{
	  $is_24_mode = ( get_option( 'wcs_24_hour_mode' ) == 'yes' ) ? TRUE : FALSE;
	  date_default_timezone_set( WcsTime::getDefaultTimezone() );

	  $hours = array();
	  $timezones = array();

	  if ( $classroom == NULL ) {
  	  $classes = self::model()->getByAttributes( array( 'visibility' => 1 ) );
	  }
	  else {
	    $classroom_id = WcsClassroom::model()->getByAttribute( 'classroom_name', $classroom )->id;

	    if ( $classroom_id )
	      $classes = self::model()->getByAttributes( array( 'classroom_id' => $classroom_id, 'visibility' => 1 ) );
	  }

	  if ( ! $classes )
	    return;

	  $weekdays = array_flip( WcsSchedule::model()->getWeekDaysArray() );
	  foreach ( $classes as $class ) {
	    /* Filter non active weekdays */
	    if ( in_array( $class->weekday, $weekdays ) ) {
  	    $hours[] = $class->start_hour;
  	    $timezones[] = $class->timezone;
	    }
	  }

	  $start_hours = array();

	  /* Create start hours array and sort it */
	  foreach ( $hours as $key => $value ) {
	    /* Combine hours and timezones arrays if timezones enabled */
	    if ( get_option( 'wcs_use_timezones' ) == 'yes' ) {
	      $start_hours[] = trim( $value . ' ' . $timezones[$key] );
	    }
	    else {
	      $start_hours[] = trim( $value );
	    }
	  }

	  $start_hours = array_unique( $start_hours );

	  foreach ( $start_hours as $key => $value ) {
  	  $start_hours[$key] = date( 'G:i', strtotime( $value, 0) );
	  }

	  $start_hours = array_unique( $start_hours );

	  natsort( $start_hours );

	  if ( ! $is_24_mode ) {
	    foreach ( $start_hours as $key => $value ) {
	      $start_hours[$key] = date( 'g:i a', strtotime( $value, 0) );
	    }
	  }

	  return $start_hours;
	}

	/**
	 * Returns an array of dates beginning in Sunday, Jan 4, 1970.
	 *
	 * Used mostly for setting a reference with strtotime().
	 */
	private static function generateWeekdaysDates()
	{
	  return array(
	    'Jan 4, 1970', // Sunday
	    'Jan 5, 1970', // Monday
	    'Jan 6, 1970', // Tuesday
	    'Jan 7, 1970', // Wednesday
	    'Jan 8, 1970', // Thursday
	    'Jan 9, 1970', // Friday
	    'Jan 10, 1970', // Saturday
	  );
	}

	/**
	 * Generates a sort array of weekdays.
	 */
	public static function generateWeekdays()
	{
	  global $wp_locale;
    return $wp_locale->weekday;
	}

	public static function generateShortWeekdays()
	{
	  global $wp_locale;
	  return $wp_locale->weekday_abbrev;
	}

	/**
	 * Return the visibility options array
	 */
	public static function getVisibilityOptions()
	{
	  return array( 0 => 'Hidden', 1 => 'Visible');
	}

	/**
	 * Return a multidimensional array of all schedule entries in the following structure:
	 * $array[weekday][start_hour][class_id][ (WcsSchedule) class].
	 *
	 * @parram string $classroom
	 * 	If set, only the entries for the specified classroom will be retrieved.
	 */
	public static function getClassesMultiDimArray( $classroom = NULL, $ios = FALSE )
	{
	  $default_timezone = WcsTime::getDefaultTimezone();
	  date_default_timezone_set( $default_timezone );

	  $multi_array = array();

	  if ( $classroom == NULL )
	    $classes = self::model()->getByAttributes( array( 'visibility' => 1 ), array( 'col' => 'start_hour', 'order' => 'ASC' ) );
	  else {
	    $classroom_obj = WcsClassroom::model()->getByAttribute( 'classroom_name', $classroom );
	    if ( isset( $classroom_obj->id ) )
	      $classroom_id = WcsClassroom::model()->getByAttribute( 'classroom_name', $classroom )->id;
	    else
	      return;

	    if ( $classroom_id ) {
	      $classes = self::model()->getByAttributes( array( 'classroom_id' => $classroom_id, 'visibility' => 1 ), array( 'col' => 'start_hour', 'order' => 'ASC') );
	    }
	  }

	  if ( ! $classes )
	    return;

	  $weekdays = self::generateWeekdays();
	  $weekday_dates = self::generateWeekdaysDates();

	  foreach ( $classes as $class ) {
	    if ( get_option( 'wcs_use_timezones' ) == 'yes' ) {
  	    /* Recalculate results with timezone considerations */
  	    $time = $weekday_dates[$class->weekday] . ', ' . $class->start_hour . ' ' . $class->timezone;
  	    $end_time = $weekday_dates[$class->weekday] . ', ' . $class->end_hour . ' ' . $class->timezone;
	    }
	    else {
	      $time = $weekday_dates[$class->weekday] . ', ' . $class->start_hour . ' ' . $default_timezone;
	      $end_time = $weekday_dates[$class->weekday] . ', ' . $class->end_hour . ' ' . $default_timezone;
	    }
	    $timestamp = strtotime( $time, 0 );
	    $end_timestamp = strtotime( $end_time, 0 );

	    if ( $ios == TRUE ) {
	      $weekday = date_i18n( 'w', $timestamp );
	      $class->start_hour = date( 'H:i', $timestamp ) . ':00';
	      $class->end_hour = date( 'H:i', $end_timestamp ) . ':00';
	      $multi_array[$weekday][] = $class;
	    }
	    else {
	      $weekday = date_i18n( 'l', $timestamp );
	      $start_hour = date( 'H:i', $timestamp ) . ':00';
	      $multi_array[$weekday][$start_hour][$class->id] = $class;
	    }
	  }

    if ( ! empty( $multi_array ) )
      return $multi_array;
	}


	/**
	 * Returns a class name based on the instance class_id property.
	 */
	public function getClassName( $strip_slashes = FALSE )
	{
	  $class = WcsClass::model()->getById( $this->class_id );
	  $class_name = ( isset( $class->class_name ) ) ? $class->class_name : NULL;

	  if ( $strip_slashes )
	    return stripslashes( $class_name );

	  return $class_name;
	}

	/**
   * Returns an instructor name based on the instance instructor_id property.
	 */
	public function getInstructorName( $strip_slashes = FALSE ) {
	  $instructor = WcsInstructor::model()->getById( $this->instructor_id );
	  $instructor_name = ( isset( $instructor->instructor_name ) ) ? $instructor->instructor_name : NULL;

	  if ( $strip_slashes )
	    return stripslashes( $instructor_name );

	  return $instructor_name;
	}

	/**
	 * Returns a classroom name based on the instance classroom_id property.
	 */
	public function getClassroomName( $strip_slashes = FALSE ) {
	  $classroom = WcsClassroom::model()->getById( $this->classroom_id );
	  $classroom_name = ( isset( $classroom->classroom_name ) ) ? $classroom->classroom_name : NULL;

	  if ( $strip_slashes )
	    return stripslashes( $classroom_name );

	  return $classroom_name;
	}

	/**
	 * Returns the entry weekday.
	 */
	public function getWeekday( $verbose = FALSE )
	{
	  if ( $verbose == TRUE ) {
	    $weekdays = self::generateWeekdays();
	    return $weekdays[$this->weekday];
	  }

	  return $this->weekday;
	}

	/**
	 * Returns a formated string of start_hour based on selected mode.
	 */
	public function getStartHour()
	{
	  $is_24 = ( get_option( 'wcs_24_hour_mode' ) == 'yes' ) ? TRUE : FALSE;

	  if ( $is_24 ) {
	    $hour = date( 'G:i', strtotime( $this->start_hour, 0 ) );
	  }
	  else {
	    $hour = date( 'g:i a', strtotime( $this->start_hour, 0 ) );
	  }

	  return $hour;
	}

	/**
	 * Returns a formated string of end_hour based on selected mode.
	 */
	public function getEndHour()
	{
	  $is_24 = ( get_option( 'wcs_24_hour_mode' ) == 'yes' ) ? TRUE : FALSE;

	  if ( $is_24 ) {
	    $hour = date( 'G:i', strtotime( $this->end_hour, 0 ) );
	  }
	  else {
	    $hour = date( 'g:i a', strtotime( $this->end_hour, 0 ) );
	  }

	  return $hour;
	}

	/**
	 * Returns the entry timezone.
	 */
	public function getTimezone()
	{
	  return $this->timezone;
	}

	/**
	 * Returns literal visibility status.
	 */
	public function getVisibility()
	{
	  return $this->visibility == '1'  ? 'Visible' : 'Hidden';
	}

	/**
	 * Returns entry notes.
	 *
	 * @param boolean $trimmed
	 * 	If set to FALSE will return the entire notes string.
	 */
	public function getNotes( $trimmed = TRUE, $filtered = TRUE )
	{
	  if ( isset( $this->notes ) && ! empty( $this->notes ) ) {
	    if ( $trimmed == TRUE && strlen( $this->notes ) >= 20 ) {
	      $notes = substr( $this->notes, 0, 20 );
	      $last_space_pos = strrpos( $notes, ' ' );
	      $notes = substr( $notes, 0, $last_space_pos ) . '...';
	    }
	    else {
	      $notes = stripslashes( $this->notes );
	    }
	  }
	  else {
	    $notes = NULL;
	  }

	  if ( $filtered == FALSE )
	    return $notes;

	  return esc_html( $notes );
	}

	/**
	 * Validates time logic to make sure no schedule collisions exist.
	 *
	 * @param string $start_hour
	 * @param string $end_hour
	 * @param string $instructor
	 * @param string $classroom
	 * @param string $weekday
	 *
	 * @return
	 * 	Returns TRUE if no errors are found, otherwise returns the $error array.
	 */
	public function validateTimeLogic( $start_hour, $end_hour, $instructor, $classroom, $weekday, $timezone )
	{
	  $errors = array();

	  /* Convert hour strings to timestamps */
	  $start_hour = strtotime( $start_hour . ' ' . $timezone, 0 );
	  $end_hour = strtotime( $end_hour . ' ' . $timezone, 0 );

	  /* Make sure a class doesn't end before it starts */
	  if ( $start_hour >= $end_hour ) {
	    $errors[] = __( 'A class cannot end before it starts', 'weekly-class-schedule' );
	    return $errors;
	  }

	  /* Make sure instructor is available at this time if instructor collisions detection enabled */
	  if ( get_option( 'wcs_detect_instructor_collisions', 'yes' ) == 'yes' ) {
  	  $instructor_collisions = WcsSchedule::model()->checkItemCollision( 'instructor', $instructor, $start_hour, $end_hour, $weekday );
  	  if ( $instructor_collisions != NULL && is_array( $instructor_collisions ) )
  	    $errors = array_merge( $errors, $instructor_collisions);
	  }

	  /* If we already have errors, lets return to save a few CPU cycles */
	  if ( ! empty( $errors ) )
	    return $errors;

	  /* Make sure classroom is available at this time if classroom collisions detection enabled */
	  if ( get_option( 'wcs_detect_classroom_collisions', 'yes' ) == 'yes' ) {
	    $classroom_collisions = WcsSchedule::model()->checkItemCollision( 'classroom', $classroom, $start_hour, $end_hour, $weekday );
	    if ( $classroom_collisions != NULL && is_array( $classroom_collisions ) )
	      $errors = array_merge( $errors, $classroom_collisions);
	  }

	  /* Return */
	  if ( ! empty( $errors ) )
	    return $errors;
	  else
	    return TRUE;
	}

  /**
   	 * Check for time collisions
	 *
	 * @param string $item_base_name
	 * 	Basename such as 'instructor', 'classroom', or 'class';
	 * @param string $item
	 * 	The actual item to check against
   * @param string $start_hour
   * @param string $end_hour
   * @param string $weekday
   *
   * @return
   * 	Error array if collisions detected, else NULL.
   */
	private static function checkItemCollision( $item_base_name, $item, $start_hour, $end_hour, $weekday )
	{
	  $errors = array();

	  $class_name = 'Wcs' . ucwords( $item_base_name );
	  $instance = new $class_name();

	  $item = $instance->getByAttribute( $item_base_name . '_name', $item );
	  $item_entries = WcsSchedule::model()->getRowsByAttribute( $item_base_name . '_id', $item->id );

	  if ( ! empty( $item_entries)) {
	    foreach ( $item_entries as $entry ) {
	      if ( $entry->weekday != $weekday )
	        continue;

	      $entry_start_hour = strtotime( $entry->start_hour . ' ' . $entry->timezone, 0 );
	      $entry_end_hour = strtotime( $entry->end_hour . ' ' . $entry->timezone, 0 );

	      /* Collision algorithm */
	      if ( ! isset( $_GET['wcsid'] ) || $entry->id != $_GET['wcsid'] ) {
  	      if ( $start_hour < $entry_end_hour && $end_hour > $entry_start_hour )
  	        $errors[] = sprintf( __( 'The %s is not available at this time', 'weekly-class-schedule' ), $item_base_name );
	      }
	    }
	  }

	  if ( ! empty( $errors ))
	    return $errors;
	}

	/**
	 * Generates a WCS table cell.
	 *
	 * @param array $classes
	 * 	Multi dimensional array as returned by getClassesMultiDimArray
	 * @param string $start_hour
	 * @param string $weekday
	 * @param string $layout
	 * @param array $start_hours_array
	 * 	Array of unique start hours as will be displayed on the schedule
	 *
	 * @see WcsSchedule::model()->getClassesMultiDimArray()
	 */
	public static function renderClassTd( $classes, $start_hour, $weekday, $layout = 'vertical', $start_hours_array = NULL )
	{
	  $output = '';
	  $format_hour = WcsTime::convertTimeToDbFormat( $start_hour );

	  // Load default template
	  $template = "[class] with [instructor]\n[start hour] to [end hour]\n[notes]";
	  $default = get_option( 'wcs_class_template', $template );

	  $weekday_css = str_replace( ' ', '-', strtolower( $weekday ) );
	  $weekdays_array = self::getWeekDaysArray();

    $weekdays_flipped = array_flip( $weekdays_array );
    $weekdays_offset = array();

    foreach ( $weekdays_flipped as $key => $value ) {
      $weekdays_offset[] = $key;
    }

	  /* Generate additional css properties */
	  $addon_css = '';

	  if ( $layout == 'vertical' ) {
  	  $number_of_days = count( $weekdays_array );
  	  if ( $weekdays_offset[0] == $weekday )
  	    $addon_css = 'first-day';
  	  elseif ( $weekdays_offset[$number_of_days - 1] == $weekday)
  	    $addon_css = 'last-day';
  	  elseif ($weekdays_offset[$number_of_days - 2] ==  $weekday)
  	    $addon_css = 'before-last-day';
	  }
	  elseif ( $layout == 'horizontal' && $start_hours_array != NULL ) {
	    $number_of_start_hours = count( $start_hours_array );
	    $temp = array();

	    $i = 0;
	    foreach ( $start_hours_array as $key => $value ) {
	      $temp[$value] = $i;
	      $i++;
	    }

	    $start_hours_array = $temp;

	    if ( $start_hours_array[$start_hour] == 0 )
	      $addon_css = 'first-hour';
	    elseif ( $start_hours_array[$start_hour] == ( $number_of_start_hours - 1 ) )
	      $addon_css = 'last-hour';
	    elseif ($start_hours_array[$start_hour] == ( $number_of_start_hours - 2 ) )
	      $addon_css = 'before-last-hour';
	  }

	  $col = ( $layout == 'vertical' ) ? $weekdays_flipped[$weekday] : $start_hours_array[$start_hour];

	  /* Generate actual td cell */
	  if ( isset( $classes[$weekday][$format_hour] ) ) {
	    $output .= "<td class='wcs-schedule-cell active $weekday_css col-$col $addon_css'>";
	    $output .= "<div class='wcs-active-class-container'>";

	    $i = 0;
	    foreach ( $classes[$weekday][$format_hour] as $class ) {
	      $odd_even = ( $i & 1 ) ? 'even' : 'odd';

	      $class_name = esc_html( stripslashes( $class->getClassName() ) );
	      $class_description = esc_html( stripslashes( WcsClass::model()->getById( $class->class_id )->class_description ) );

	      $instructor = esc_html( stripslashes( $class->getInstructorName() ) );
	      $instructor_description = esc_html( stripslashes( WcsInstructor::model()->getById( $class->instructor_id )->instructor_description ) );

	      $s_hour = $class->getStartHour();
	      $e_hour = $class->getEndHour();

	      $notes = stripslashes( $class->getNotes( FALSE, FALSE ) );

	      $details_class = "<a class='wcs-qtip' name='$class_description'>$class_name</a>";
	      $final_details = str_replace( '[class]', $details_class, $default );

	      $details_instructor = "<a class='wcs-qtip' name='$instructor_description'>$instructor</a>";
	      $final_details = str_replace( '[instructor]', $details_instructor, $final_details );

	      $final_details = str_replace( '[start hour]', $s_hour, $final_details );
	      $final_details = str_replace( '[end hour]', $e_hour, $final_details );
	      $final_details = str_replace( '[notes]', $notes, $final_details );

	      $final_details = str_replace( "\n", '<br/>', $final_details );

	      /* Generate actual class div */
	      $output .= "<div class='wcs-active-div-$i wcs-active-div $odd_even'>";
	      $output .= "<p>$class_name</p>";
	      $output .= "<div class='wcs-class-details'><p>$final_details</p></div>";
	      $output .= '</div>';

	      /* -------------------------- */
	      $i++;
	    }

	    $output .= "</div>";
	    $output .= '</td>';
	  }
	  else {
	    $output .= "<td class='wcs-schedule-cell $weekday_css col-$col $addon_css'></td>";
	  }

	  return $output;
	}

	public static function renderListItem( $classes, $start_hour, $weekday )
	{
	  $output = '';
	  $format_hour = WcsTime::convertTimeToDbFormat( $start_hour );

	  // Load default template
	  $template = "[class] with [instructor]\n[start hour] to [end hour]\n[notes]";
	  $default = get_option( 'wcs_class_template', $template );

	  if ( isset( $classes[$weekday][$format_hour] ) ) {
	    $output .= "<li>";

	    $i = 0;
	    foreach ( $classes[$weekday][$format_hour] as $class ) {
	      $odd_even = ( $i & 1 ) ? 'even' : 'odd';

	      $class_name = $class->getClassName();
	      $class_description = esc_html( stripslashes( WcsClass::model()->getById( $class->class_id )->class_description ) );

	      $instructor = $class->getInstructorName();
	      $instructor_description = esc_html( stripslashes( WcsInstructor::model()->getById( $class->instructor_id )->instructor_description ) );

	      $s_hour = $class->getStartHour();
	      $e_hour = $class->getEndHour();

	      $notes = stripslashes( $class->getNotes( FALSE, FALSE ) );

	      $details_class = "<a class='wcs-qtip' name='$class_description'>$class_name</a>";
	      $final_details = str_replace( '[class]', $details_class, $default );

	      $details_instructor = "<a class='wcs-qtip' name='$instructor_description'>$instructor</a>";
	      $final_details = str_replace( '[instructor]', $details_instructor, $final_details );

	      $final_details = str_replace( '[start hour]', $s_hour, $final_details );
	      $final_details = str_replace( '[end hour]', $e_hour, $final_details );
	      $final_details = str_replace( '[notes]', $notes, $final_details );

	      $final_details = str_replace( "\n", '<br/>', $final_details );

	      /* Generate actual class div */
	      $output .= $final_details;

	      /* -------------------------- */
	      $i++;
	    }

	    $output .= "</li>";
	  }

	  return $output;
	}

}