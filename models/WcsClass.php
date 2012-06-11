<?php

/**
 * @file
 * Defines the WcsClass class.
 */
class WcsClass extends WcsActiveRecord
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
    return new WcsClass();
  }
  
  private function tableName()
	{
		global $wpdb;
		return $wpdb->prefix . 'wcs2_class';
	}
}