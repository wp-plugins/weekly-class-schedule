<?php
function show_wp_message( $message, $type = 'error' ) {
	?>
	<div id="message" class="<?php echo $type;?>">
		<p>
			<?php echo $message;?>
		</p>
	</div>
<?php
}

function remove_wcs_object_table( $object ) {
	global $wpdb;
	$sql = "DROP TABLE IF EXISTS " . $object->table_name . ";";
	$wpdb->query( $sql );
}

function verify_wcs_nonces( $nonce_name ) {
	if ( count( $_POST) > 0 ) {
		$nonce = $_REQUEST['_wpnonce'];
		if ( ! wp_verify_nonce( $nonce, $nonce_name ) ) die( 'What are you doing?' );
	}
}

function verify_no_empty_fields( $fields_array = array() ) {
	$empty = array();
	foreach ( $fields_array as $key => $value ) {
		$empty[] = ( strlen( $value ) == 0 ) ? true : false;
	}
	if ( in_array( true, $empty ) ) {
		$message = "Please fill in all the fields.";
		show_wp_message( $message, 'error' );
		return false;
	} else {
		return true;
	}
}

function verify_selection_is_in_db( $options = array() ) {
	global $wpdb;
	$not_in_db = array();
	foreach ( $options as $value ) {
		$sql = "SELECT item_name FROM " . $wpdb->prefix . "wcs_" . $value;
		$results = $wpdb->get_col( $sql );
		$not_in_db[] = ( ! in_array( $_POST["{$value}_select"], $results ) ) ? true : false;
	}
	if ( in_array( true, $not_in_db ) ) {
		$message = "Please select only existing items.";
		show_wp_message( $message, 'error' );
		return false;
	} else {
		return true;
	}
}

function convert_to_am_pm( $time, $format = "string" ) {
	$time_array = explode( ":", $time );
	$hours = $time_array[0];
	$minutes = $time_array[1];
	$am_pm = ( $hours >= 12 ) ? "PM" : "AM" ;
	if ( $hours == 0 ) ( $hours = 12 );
	if ( $hours > 12 ) ( $hours -= 12 );
	$hours = ltrim( $hours, "0" );
	switch ( $format ) {
		case 'string':
			$output = $hours . ":" . $minutes . " " . $am_pm;
			break;
		case 'array':
			$output = array( 'hours' => $hours, 'minutes' => $minutes, 'am_pm' => $am_pm );
			break;
	}
	
	return $output;
}

function convert_from_am_pm( $hours, $minutes, $am_pm ) {
	if ( $am_pm == "PM" ) {
		if ( $hours < 12 ) ( $hours += 12 );
	} elseif ( $am_pm == "AM" ) {
		if ( $hours == 12 ) ( $hours = 0 );
	}
	$output = sprintf( "%02d:%02d:00", $hours, $minutes );
	return $output;
}
