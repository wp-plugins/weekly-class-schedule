<?php

class WcsUpdate {
		
	public $table_name;
	
	public function __construct() {
		global $wpdb;
		
		$this->table_name = $wpdb->prefix . "wcs_options";
		$sql = "CREATE TABLE " . $this->table_name . " (
  	 			id int(11) NOT NULL AUTO_INCREMENT,
  	 			option_name VARCHAR(155) NOT NULL,
  	 			option_value VARCHAR(155) NOT NULL,
  	 			UNIQUE KEY id (id)
  				);";
		
		dbDelta( $sql );
	}
	
	public function update_version_number_in_database( $version ) {
		global $wpdb;
		$sql = "SELECT option_name FROM " . $this->table_name . " WHERE option_name = 'version'";
		$options = $wpdb->get_results( $sql );
		if ( $options ) {
			$wpdb->update( 
				$this->table_name, 
				array( 'option_name' => 'version', 'option_value' => $version ), 
				array( 'option_name' => 'version' ),
				array( '%s', '%s' )
			);
		} else {
			$wpdb->insert( 
				$this->table_name, 
				array( 'option_name' => 'version', 'option_value' => $version ), 
				array( '%s', '%s' )
			);
		}
	}
	
	public function check_wcs_tables( $table_name ) {
		global $wpdb;
		$sql = "SHOW TABLES LIKE '" . $wpdb->prefix . "wcs_" . $table_name . "'";
		$results = $wpdb->get_var( $sql );
		return $results;
	}
	
}
