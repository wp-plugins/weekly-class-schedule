<?php
/**
 * Defines the WcsItemForm class.
 */

class WcsItemForm extends WcsForm implements IWcsForm
{
  /**
   * @see IWcsForm::begin()
   */
  public function begin( $op, $id )
  {
    $this->validate( $op, $id );
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
        // Validate name field is not empty
        if ( ! isset( $_POST['new_item_name'] ) || empty( $_POST['new_item_name'] ) )
          $form_errors[] = __( 'Name field cannot be empty' );

        // Validate name is not already in the database
        $table = $this->_table_name;
        $name = trim( preg_replace("/ [ ]+/", ' ', $_POST['new_item_name']));
        $sql = $wpdb->prepare( "SELECT id FROM $table WHERE {$this->_base_name}_name = %s LIMIT 1", $name );
        $record = $wpdb->get_var( $sql );

        if ( $op == 'add_item' ) {
          if ( isset( $record ) && ! empty( $record ))
            $form_errors[] = __( 'Item already exists in the database' );
        }
        elseif ( $op == 'save_item' ) {
          if ( isset( $record ) && ! empty( $record ) && $record != $id )
            $form_errors[] = __( 'Item already exists in the database' );
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
          $form_errors[] = ( __( 'Please select an item to delete' ) );
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
      $item = $_POST['new_item_name'];
      $description = $_POST['new_item_description'];

      if ( $op == 'add_item' ) {
        $class = $this->_form_id;
        $record = new $class();
      }
      elseif ( $op == 'save_item' ) {
        $instance = new $class();
        $class = $this->_class_name;
        $record = $instance->getById( $id );
      }

      $cols = array (
      	'name_col' => $this->_base_name . '_name',
      	'desc_col' => $this->_base_name . '_description',
      );

      $record->$cols['name_col'] = $item;
      $record->$cols['desc_col'] = $description;

      $record->setTimeUserValues();
      $record->save();
    }
    elseif ( $op == 'delete_items') {
      $ids = array();
      $count = 0;

      foreach ( $_POST as $key => $value ) {
        if ( preg_match( "/^delete_[0-9]+$/", $key ) == 1 ) {
          $instance = new $class();
          $class = $this->_class_name;
          $record = $instance->getById( $value );
          if ( $record->delete() > 0 )
            $count++;

          /* Remove all dependant entries from schedule entry */
          $col = $this->_base_name . '_id';
          $schedule_entries = WcsSchedule::model()->getByAttributes( array( $col => $value ) );
          if ( $schedule_entries ) {
            foreach ( $schedule_entries as $entry ) {
              $entry->delete();
            }
          }
        }
      }

      if ( $count > 0 ) {
        $message = sprintf( _n( "%d item deleted from database", "%d items deleted from database", $count), $count );
        WcsHtml::show_wp_message( $message, 'updated' );
      }
    }
  }
}