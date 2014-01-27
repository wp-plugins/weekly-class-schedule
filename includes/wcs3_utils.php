<?php
/**
 * Utility functions for WCS3.
 */

/**
 * Returns all post of the specified type.
 *
 * @param string $type: e.g. class, instructor, etc.
 */
function wcs3_get_posts_of_type( $type ) {
	$args = array(
    	'orderby' => 'post_title',
    	'post_type' => $type,
    	'post_status' => 'publish',
	    'posts_per_page' => 99999,
	);

	$posts = get_posts( $args );
	return $posts;
}

/**
 * Returns and HTTP JSON response.
 * 
 * @param mixed $data: JSON data to be encoded and sent.
 */
function wcs3_json_response( $data ) {
    header('Content-Type: application/json');
    echo json_encode( $data );
}

/**
 * Generates weekday array
 * 
 * @param bool $abbr: if TRUE returns abbreviated weekday names.
 */
function wcs3_get_weekdays( $abbr = FALSE ) {
    global $wp_locale;
    
    $days = array();
    
    if ($abbr) {
        $abbr_array = $wp_locale->weekday_abbrev;
        foreach ( $abbr_array as $value ) {
            $days[] = $value;
        }
    }
    else {
        $days = $wp_locale->weekday;
    }
            
    return $days;
}

/**
 * Returns an indexed array of weekday rotated according to $first_day_of_week.
 * 
 * @param bool $abbr: if TRUE returns abbreviated weekday names.
 * @param int $first_day_of_week: index.
 */
function wcs3_get_indexed_weekdays( $abbr = FALSE, $first_day_of_week = 0 ) {
    $weekdays = wcs3_get_weekdays( $abbr );
    $weekdays = array_flip( $weekdays );
    
    if ( $first_day_of_week > 0 ) {
    	// Rotate array based on first day of week setting.
    	$slice1 = array_slice( $weekdays, $first_day_of_week );
    	$slice2 = array_slice( $weekdays, 0, $first_day_of_week );
    	$weekdays = array_merge( $slice1, $slice2 );
    }
    
    return $weekdays;
}

/**
 * Generages a simple HTML checkbox input field.
 * 
 * @param string $name: will be used both for name and id
 * @param bool $checked.
 */
function wcs3_bool_checkbox( $name, $checked = 'yes', $text = '' ) {
    $check = '';
    if ( $checked == 'yes' ) {
        $check = 'checked';
    }

    echo '<input type="hidden" name="' . $name . '" id="' . $name . '" value="no">';
    echo '<input type="checkbox" name="' . $name . '" id="' . $name . '" value="yes" ' . $check . '>' . $text;
}

/**
 * Generates an HTML select list.
 * 
 * @param array $values: id => value.
 */
function wcs3_select_list( $values, $name = '', $default = NULL, $id = '' ) {
	$output = ( $name == '' ) ? '<select>' : "<select id='$name' name='$name'>";

	if ( !empty( $values ) ) {
	    foreach ( $values as $key => $value ) {
	        if ( $key == $default ) {
	            $output .= "<option value='$key' selected='selected'>$value</option>";
	        }
	        else {
	            $output .= "<option value='$key'>$value</option>";
	        }
	    }
	}
	else {
	    $output .= '<option value="_none"> --- </option>';
	}

	$output .= '</select>';
	return $output;;
}

function wcs3_colorpicker( $name, $default = 'DDFFDD', $size = 8 ) {
    echo '<input type="text" class="wcs_colorpicker" id="' . $name . '" name="' . $name . '" value="' . $default . '" size="' . $size . '">';
    echo '<span style="background: #' . $default . ';" class="colorpicker-preview ' . $name . '">&nbsp;</span>';
}


/**
 * Returns the installation default timezone. The method first checks for a WP
 * setting and if it can't find it, it uses the server setting. If the server setting
 * is also missing, the string UTC will be used.
 */
function wcs3_get_system_timezone()
{
    
	$php_timezone = ( ini_get('date.timezone') ) ? ini_get('date.timezone') : 'UTC';
	$wp_timezone = get_option( 'timezone_string' );

	return ( $wp_timezone == '' ) ? $php_timezone : $wp_timezone;
}

/**
 * Sets PHP's global timezone var.
 */
function wcs3_set_global_timezone() {
    $timezone = wcs3_get_system_timezone();
    date_default_timezone_set( $timezone );
}

/**
 * Deletes all the data after wcs3
 */
function wcs3_delete_everything() {
	global $wpdb;

	delete_option( 'wcs3_db_version' );
	delete_option( 'wcs3_settings' );
	delete_option( 'wcs3_advanced_settings' );
	delete_option( 'wcs3_version' );

	$post_types = array(
	'wcs3_class',
	'wcs3_instructor',
	'wcs3_location',
	);

	foreach ( $post_types as $type ) {
		$posts = get_posts( array(
		'numberposts' => -1,
		'post_type' => $type,
		'post_status' => 'any' ) );

		foreach ( $posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	$table_name = $wpdb->prefix . "wcs3_schedule";

	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}