<?php

/**
 * @file
 * Defines the WcsIntructor class.
 */
class WcsInstructor extends WcsActiveRecord
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
    return new WcsInstructor();
  }
  
  private function tableName()
	{
		global $wpdb;
		return $wpdb->prefix . 'wcs2_instructor';
	}
}