<?php
/**
 * @file
 * Defines the WcsScheduleForm class.
 */

class WcsScheduleForm extends WcsForm implements IWcsForm
{
  /**
  * @see IWcsForm::begin()
  */
  public function begin( $op, $id )
  {
    $this->validate( $op, $id );

    // TODO: Remove this
    WcsIOS::updateScheduleJson();
  }

  /**
  * @see IWcsForm::validate()
  */
  public function validate( $op, $id )
  {
    global $wpdb;
    $form_errors = array();

    switch ( $op ) {
      case 'save_item':
      case 'add_item':
        /* Define all fields */
        $fields = array(
          'class_select',
          'instructor_select',
          'classroom_select',
          'weekday_select',
          'start_hour_hours',
          'start_hour_minutes',
          ( isset( $_POST['start_hour_ampm'] ) ? 'start_hour_ampm' : NULL ),
          'end_hour_hours',
          'end_hour_minutes',
          ( isset( $_POST['end_hour_ampm'] ) ? 'end_hour_ampm' : NULL ),
          ( isset( $_POST['timezone'] ) ? 'timezone' : NULL ),
          'visibility_select',
          'notes',
        );

        /* Remove NULL values */
        foreach ( $fields as $key => $value ) {
          if ( $value == NULL )
            unset( $fields[$key] );
        }

        /* Filter non-required fields */
        $notes_index = array_search( 'notes', $fields );
        if ( $notes_index ) unset( $fields[$notes_index] );

        /* Validate no empty fields */
        foreach ( $fields as $key => $value ) {
          $field_name = str_replace('select', '', str_replace('_', ' ', $value) );
          if ( ( empty( $_POST[$value] ) || ! $_POST[$value] ) && $_POST[$value] !== '0' ) {
            $form_errors[] = __( 'Illegal selection', 'weekly-class-schedule' );
          }

          if ( $_POST[$value] == '_none' )
            $form_errors[] = sprintf( __( 'Please select a %s', 'weekly-class-schedule' ), $field_name );
        }

        /* Filter hour fields */
        foreach ( $fields as $key => $value ) {
          if ( preg_match( "/^(start_hour|end_hour)/", $value ) > 0 )
            unset( $fields[$key] );
        }

        /* Validate legal selections */
        $options['class_select'] = WcsClass::model()->getCol( 'id' );
        $options['instructor_select'] = WcsInstructor::model()->getCol( 'id' );
        $options['classroom_select'] = WcsClassroom::model()->getCol( 'id' );
        $options['weekday_select'] = array_flip( WcsSchedule::model()->generateWeekdays() );
        $options['visibility_select'] = array( 0 => '0', 1 => '1' );

        if ( isset( $_POST['timezone'] ) && ! empty( $_POST['timezone'] ) )
          $options['timezone'] = DateTimeZone::listIdentifiers();

        foreach ( $options as $key => $value ) {
          if (is_array( $value ) ) {
            $index = array_search( $_POST[$key], $value );
            if ( $index === FALSE )
              $form_errors[] = __( 'Illegal selection', 'weekly-class-schedule' );
          }
        }

        /* Create hour strings */
        $start_hour_array = array(
        	'h' => $_POST['start_hour_hours'],
          'm' => $_POST['start_hour_minutes'],
          'e' => ( isset( $_POST['start_hour_ampm'] ) ? ' ' . $_POST['start_hour_ampm'] : ':00' ),
        );

        $end_hour_array = array(
          'h' => $_POST['end_hour_hours'],
          'm' => $_POST['end_hour_minutes'],
          'e' => ( isset( $_POST['end_hour_ampm'] ) ? ' ' . $_POST['end_hour_ampm'] : ':00' ),
        );

        $start_hour = $start_hour_array['h'] . ':' . $start_hour_array['m'] . $start_hour_array['e'];
        $end_hour = $end_hour_array['h'] . ':' . $end_hour_array['m'] . $end_hour_array['e'];

        /* Assign additional variables */
        $instructor = $_POST['instructor_select'];
        $classroom = $_POST['classroom_select'];
        $weekday = $_POST['weekday_select'];
        $timezone = ( isset( $_POST['timezone'] ) ? $_POST['timezone'] : WcsTime::getDefaultTimezone() );

        $time_logic_validation = WcsSchedule::model()->validateTimeLogic( $start_hour, $end_hour, $instructor, $classroom, $weekday, $timezone );
        if ( is_array( $time_logic_validation ) ) {
          $form_errors = array_merge( $form_errors, $time_logic_validation );
        }

        break;
      case 'delete_items':
        /* Validate at least one checkbox has been selected */
        $ids = array();
        foreach ( $_POST as $key => $value ) {
          if ( preg_match( "/^delete_[0-9]+$/", $key ) == 1 ) {
            $ids[] = $value;
          }
        }

        if ( empty( $ids )) {
          $form_errors[] = ( __( 'Please select an item to delete', 'weekly-class-schedule' ) );
        }
        break;
    }

    if ( ! empty( $form_errors ) ) {
      WcsForm::setFormError( $form_errors );
      return;
    }

    /* Continue the processing chain */
    $this->process( $op, $id );
  }

  /**
  * @see IWcsForm::process()
  */
  public function process( $op, $id )
  {
    if ( $op != 'delete_items') {
      $class = WcsClass::model()->getByAttribute( 'id', $_POST['class_select'] );
      $instructor = WcsInstructor::model()->getByAttribute( 'id', $_POST['instructor_select'] );
      $classroom = WcsClassroom::model()->getByAttribute( 'id', $_POST['classroom_select'] );
      $weekday = $_POST['weekday_select'];

      if ( isset( $_POST['start_hour_ampm'] ) ) {
        $start_hour = WcsTime::prepareHour( $_POST['start_hour_hours'], $_POST['start_hour_minutes'], $_POST['start_hour_ampm'] );
      }
      else {
        $start_hour = WcsTime::prepareHour( $_POST['start_hour_hours'], $_POST['start_hour_minutes'] );
      }

      if ( isset( $_POST['end_hour_ampm'] ) ) {
        $end_hour = WcsTime::prepareHour( $_POST['end_hour_hours'], $_POST['end_hour_minutes'], $_POST['end_hour_ampm'] );
      }
      else {
        $end_hour = WcsTime::prepareHour( $_POST['end_hour_hours'], $_POST['end_hour_minutes'] );
      }

      $timezone = ( isset( $_POST['timezone'] ) ) ? $_POST['timezone'] : WcsTime::getDefaultTimezone();
      $visibility = $_POST['visibility_select'];
      $notes = $_POST['notes'];

      if ( $op == 'add_item' ) {
        $record = new WcsSchedule();
      }
      elseif ( $op == 'save_item' ) {
        $record = WcsSchedule::model()->getById( $id );
      }

      $record->class_id = ( isset( $class->id ) ) ? $class->id : NULL;
      $record->instructor_id = ( isset( $instructor->id ) ) ? $instructor->id : NULL;
      $record->classroom_id = ( isset( $classroom->id ) ) ? $classroom->id : NULL;
      $record->weekday = ( isset( $weekday ) ) ? $weekday : NULL;
      $record->start_hour = ( $start_hour ) ? $start_hour : NULL;
      $record->end_hour = ( $end_hour ) ? $end_hour : NULL;
      $record->timezone = ( isset( $timezone ) ) ? $timezone : NULL;
      $record->visibility = ( isset( $visibility ) ) ? $visibility : NULL;
      $record->notes = ( isset( $notes ) ) ? $notes : NULL;

      $record->setTimeUserValues();
      $record->save();
    }
    elseif ( $op == 'delete_items') {
      $ids = array();
      $count = 0;

      foreach ( $_POST as $key => $value ) {
        if ( preg_match( "/^delete_[0-9]+$/", $key ) == 1 ) {
          $class = $this->_class_name;
          $instance = new $class();

          $record = $instance->getById( $value );
          if ( $record->delete() > 0 )
            $count++;
        }
      }

      if ( $count > 0 ) {
        $message = sprintf( _n( "%d item deleted from database", "%d items deleted from database", $count, 'weekly-class-schedule' ), $count );
        WcsHtml::show_wp_message( $message, 'updated' );
      }
    }
    WcsIOS::updateScheduleJson();
  }
}