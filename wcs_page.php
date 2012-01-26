<?php
function print_page_output( $atts ) {
	global $wpdb;
	global $classes_obj;
	global $instructors_obj;
	global $schedule_obj;
	$table_name = $schedule_obj->table_name;
	$week_days_array = array ( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'	);
	$enable_24h = get_option( 'enable_24h' );
	$enable_timezones = get_option( 'enable_timezones' );
	$enable_classrooms = get_option( 'enable_classrooms' );
	$enable_unesc_notes = get_option( 'enable_unescaped_notes' );
	$schedule_tables_array = array( '' );
	$verify = true;

	$page_output = '<div id="wcs-container">';
	if ( $enable_classrooms == "on" ) {
		if ( empty( $atts ) ) {
			$classroom_message = "<h2>Classroom attribute is not defined</h2>";
			$classroom_message .= "<p>Check documentation for more information.</p>";
			$page_output .= $classroom_message;
			$verify = false;
		} else {
			foreach ( $atts as $classroom ) {
				$schedule_tables_array[] = $classroom;
			}
			array_shift( $schedule_tables_array );
			$verify = true;
		}
	}

	// Print Schedule in table format
	if ( $verify ) :
		$sql = "SELECT * FROM " . $table_name . " WHERE visible = '1'";
		$results = $wpdb->get_results( $sql );

		foreach ( $schedule_tables_array as $key => $schedule ) :
			$page_output .= "<br/><h2>" . ucwords( $schedule ) . "</h2>";
			if ( $enable_classrooms == "on" ) {
				$sql = "SELECT start_hour FROM " . $table_name . " WHERE classroom = '" . $schedule . "' ORDER BY start_hour ASC";
				$start_hours_array = array_unique( $wpdb->get_col( $sql ) );
			} else {
				$sql = "SELECT start_hour FROM " . $table_name . " ORDER BY start_hour ASC";
				$start_hours_array = array_unique( $wpdb->get_col( $sql ) );
			}
			$page_output .= '<table class="wcs-schedule-table"><tr><th>&nbsp;</th>';
				foreach ( $week_days_array as $value ) {
					$page_output .= "<th class='weekday-label weekday-column'>" . substr( $value, 0, 3 ) . "</th>";
				}
			$page_output .=	'</tr>';
			foreach ( $start_hours_array as $start_hour ) {
				if ( $enable_24h == "on" ) {
					$page_output .= "<tr><td class='hour-label'>" . clean_time_format( $start_hour ) . "</td>";
				} else {
					$page_output .= "<tr><td class='hour-label'>" . convert_to_am_pm( $start_hour ) . "</td>";
				}

				foreach ( $week_days_array as $weekday ) {
					$page_output .= "<td class='" . strtolower( $weekday ) . "-column weekday-column'>";
					foreach ( $results as $entry) {
						if ( $enable_classrooms == "on" ) {
							$verify = ( $entry->classroom == $schedule ? true : false );
						} else {
							$verify = true;
						}
						if ( $entry->start_hour == $start_hour && $entry->week_day == $weekday && ( $verify ) ) {
							$sql = "SELECT item_description FROM " . $classes_obj->table_name . " WHERE id = '" . $entry->class_id . "'";
							$class_desc = esc_html( stripslashes( $wpdb->get_var( $sql ) ) );
							$sql = "SELECT item_description FROM " . $instructors_obj->table_name . " WHERE id = '" . $entry->instructor_id . "'";
							$inst_desc = esc_html( stripslashes( $wpdb->get_var( $sql ) ) );

							$class = esc_html( stripslashes( $entry->class ) );
							$inst = esc_html( stripslashes ( $entry->instructor ) );

							if ( $enable_24h == "on" ) {
								$class_start = clean_time_format( $entry->start_hour );
								$class_end = clean_time_format( $entry->end_hour );
							} else {
								$class_start = convert_to_am_pm( $entry->start_hour );
								$class_end = convert_to_am_pm( $entry->end_hour );
							}
							if ( $enable_timezones == "on" ) {
								$timezone = $entry->timezone;
							}
							$unescaped_notes = stripslashes($entry->notes);

							$output = "<!--[if IE 7]><div class='ie-container'><![endif]-->";
							$output .= "<div class='active-box-container'><div class='class-box'>" . $class . "</a></div>";
							$output .= "<div class='class-info'><a class='qtip-target' title='" . $class_desc . "'>" . $class . "</a>";
							$output .= " with ";
							$output .= "<a class='qtip-target' title='" . $inst_desc . "'>" . $inst . "</a><br/>";
							$output .= $class_start . " to " . $class_end;
							if ( $enable_timezones == "on" ) {
								$output .= "<div class'timezone-container'>" . $timezone . "</div>";
							}
							if ($enable_unesc_notes == 'on') {
							  $output .= "<div class='notes-container'>" . $unescaped_notes . "</div>";
							} else {
							  $output .= "<div class='notes-container'>" . esc_html($entry->notes) . "</div>";
							}
							$output .= "</div></div>";
							$output .= "<!--[if IE 7]></div><![endif]-->";
							$page_output .= $output;
						}
					}
					$page_output .= "</td>";
				}
				$page_output .= "</tr>";
			}
	$page_output .= '</table>';

	endforeach;
	endif;

	$page_output .= '</div>';
	return $page_output;
}
?>