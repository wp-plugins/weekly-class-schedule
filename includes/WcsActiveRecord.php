<?php

class WcsActiveRecord
{
  public static function model()
  {
    $instance = new WcsActiveRecord();
    return $instance;
  }
  
	/**
	 * Return a DB row based on an id.
	 *
	 * @param int $id
	 * @return WcsActiveRecord $active_record
	 *	A WcsActiveRecord object
	 *	NULL if no row found
	 */
	public function getById( $id )
	{
		global $wpdb;
		$table_name = $this->_tableName;
		$sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id );
		$row = $wpdb->get_row( $sql, OBJECT );

		if ( $row ) {
		  $active_record = self::mapRowToAR( $row, get_class( $this ) );
			return $active_record;
		}
	}

	/**
	 * Return a result object with the specified columns as properties.
	 *
	 * @param array $attributes
	 * 	Column names
	 * @return stdClass $results
	 */
	public function getCols( array $attributes )
	{
	  global $wpdb;
	  
	  $cols = implode(", ", $attributes);
	  $table_name = $this->_tableName;

	  $sql = $wpdb->prepare( "SELECT $cols FROM $table_name" );
	  $results = $wpdb->get_results( $sql );

	  if ( ! empty( $results ) )
	    return $results;
	}

	/**
	 * Returns a single column
	 *
	 * @param string $col
	 * 	Column name
	 * @return array
	 */
	public function getCol( $col )
	{
	  global $wpdb;
	  
	  $table_name = $this->_tableName;

	  $sql = $wpdb->prepare( "SELECT $col FROM $table_name" );
	  $results = $wpdb->get_col( $sql );

	  if ( ! empty( $results ) )
	    return $results;
	}

	/**
	 * Returns a row based on an attribute.
	 *
	 * @param string $column
	 * 	The column name
	 * @param string $value
	 * 	The value to search agains ("WHERE $col = $value")
	 * 
	 * @return WcsActiveRecord
	 * 	A single WcsActiveRecord object
	 */
	public function getByAttribute( $column, $value )
	{
	  global $wpdb;
	  $table_name = $this->_tableName;

	  $sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE $column = '$value' LIMIT 1" );
	  $results = $wpdb->get_results( $sql );

	  if ( isset( $results[0] ) && ! empty( $results[0] ) )
	    return self::mapRowToAR( $results[0], get_class( $this ) );

	}

	/**
	 * Returns all rows based on an attribute.
	 *
	 * @param string $column
	 * 	The column name
	 * @param string $value
	 * 	The value to search agains ("WHERE $col = $value")
	 *
	 * @return array of AR (late binding) objects
	 */
	public function getRowsByAttribute( $column, $value )
	{
	  global $wpdb;
	  $table_name = $this->_tableName;

	  $sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE $column = '$value'" );
	  $results = $wpdb->get_results( $sql );

	  if ( ! empty( $results ) ) {
	    $active_records = array();
	    foreach ( $results as $record ) {
  	    $active_records[] = self::mapRowToAR( $record, get_class( $this ) );
	    }
	    return $active_records;
	  }
	}

	/**
	 * Returns records based on multiple attributes
	 *
	 * @param array $attributes
	 * 	An array structured with 'column' => 'value'.
	 * 
	 * @return array $active_records
	 * 	Array of AR objects
	 */
	public function getByAttributes( array $attributes )
	{
	  global $wpdb;
	  $table_name = $this->_tableName;

	  $where_statement = '';
	  if ( ! empty( $attributes ) ) {
	    $i = 1;
	    $length = count( $attributes );

	    foreach ( $attributes as $col => $value ) {
	      if ( $i < $length )
	        $where_statement .= "$col = '$value' AND ";
	      else
	        $where_statement .= "$col = '$value'";

	      $i++;
	    }
	  }

	  $sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE $where_statement" );
	  $results = $wpdb->get_results( $sql );

	  if ( ! empty( $results ) ) {
	    $active_records = array();
	    foreach ( $results as $record ) {
  	    $active_records[] = self::mapRowToAR( $record, get_class( $this ) );
	    }
	    return $active_records;
	  }
	}

  /**
   * Returns all the records of the model
   *
   * @param array $order_by
   * 	An array with 2 parameters:
   *  - (string) name of column to sort by
   *  - (string) order: ASC or DESC
   *  
   *  @return array $results
   *  	Array of AR objects
   */
  public function getAllRecords( $order_by = array() )
  {
    global $wpdb;
    $table_name = $this->_tableName;

    if ( ! empty( $order_by ) )
      $sql = "SELECT * FROM $table_name ORDER BY $order_by[0] $order_by[1]";
    else
      $sql = "SELECT * FROM $table_name";

    $sql = $wpdb->prepare( $sql );
    $results = $wpdb->get_results( $sql );

    if ( $results ) {
      foreach ( $results as $key => $value ) {
        $results[$key] = self::mapRowToAR( $value, get_class( $this ) );
      }
    }

    if ( ! empty( $results ) )
      return $results;
  }

  /**
   * Saves an active record to the database.
   *
	 * @return boolean
	 * 	TRUE on success
   */
	public function save( $trusted = FALSE, $suppres_messages = FALSE )
	{
	  global $wpdb;
	  $messages = array();
	  $errors = array();
	  
	  // Prepare insert/update array
	  $cols = (array) $this;
	  unset( $cols['_tableName']);

	  $this->setTimeUserValues();

	  if ( $this->isNewRecord() ) {
  	  $num_insert = $wpdb->insert( $this->_tableName, $cols );
  	  if ( $num_insert !== FALSE && $num_insert != 0 ) {
  	    $messages[] = sprintf( __( '%d item was added to the database', '%d items were added to the database', $num_insert ), $num_insert );
  	  } else {
  	    $errors[] = __( 'Failed to add item to the database' );
  	  }
	  }
	  else {
  	  $num_updated = $wpdb->update( $this->_tableName, $cols, array( 'id' => $this->id ) );
      if ( $num_updated !== FALSE && $num_updated != 0 ) {
  	    $messages[] = sprintf( __( '%d item was updated', '%d items were updated', $num_updated ), $num_updated );
      } else {
        $errors[] = __( 'Failed to update item');
     }
	  }

    if ( ! empty( $errors ) ) {
      if ( $suppres_messages != TRUE ) WcsHtml::show_wp_message( $errors );
      return FALSE;
    } elseif ( ! empty( $messages ) ) {
      if ( $suppres_messages != TRUE ) WcsHtml::show_wp_message( $messages, 'updated' );
    }

    return TRUE;
	}

	/**
	 * Delete an active record from the database.
	 */
	public function delete()
	{
	  global $wpdb;
	  $table_name = $this->_tableName;

	  $sql = $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $this->id );
	  $num_deleted = $wpdb->query( $sql );

	  return $num_deleted;
	}

	/**
	 * Maps a database row to an Active Record object.
	 *
	 * @param stdClass $row
	 */
	protected static function mapRowToAR( $row, $class_name )
	{
	  $active_record = new $class_name();
	  
	  foreach ( $row as $key => $value ) {
	    $active_record->$key = $value;
	  }
    if ( !empty($active_record) )
	    return $active_record;
	}

	/**
	 * Return TRUE if the record is a new one.
	 */
	protected function isNewRecord()
	{
	  global $wpdb;
	  $table = $this->_tableName;

	  $sql = $wpdb->prepare( "SELECT id FROM $table WHERE id = %d", $this->id );
	  $record = $wpdb->get_var( $sql );

	  if ( isset( $record ) && ! empty( $record))
	    return FALSE;
	  else
	    return TRUE;
	}

	/**
	 * Create default values for time and user -> created and modified.
	 */
	public function setTimeUserValues()
	{
	  $now = time();
	  $uid = get_current_user_id();

	  $this->time_modified = $now;
	  $this->user_modified = $uid;

	  if ( $this->isNewRecord() ) {
	    $this->time_created = $now;
	    $this->user_created = $uid;
	  }
	}
}
