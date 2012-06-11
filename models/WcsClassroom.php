<?php

/**
 * @file
 * Defines the WcsClassroom class.
 */
class WcsClassroom extends WcsActiveRecord
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
    return new WcsClassroom();
  }
  
  private function tableName()
	{
		global $wpdb;
		return $wpdb->prefix . 'wcs2_classroom';
	}
}