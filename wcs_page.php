<div id="wcs-container">
<?php
global $wpdb;
global $classes_obj;
global $instructors_obj;
global $schedule_obj;
$table_name = $schedule_obj->table_name;
$week_days_array = array ( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'	);
$sql = "SELECT start_hour FROM " . $table_name . " WHERE visible = '1' ORDER BY start_hour ASC ";
$start_hours_array = array_unique( $wpdb->get_col( $sql ) );

// Print Schedule in table format ?>
<?php
	$sql = "SELECT * FROM " . $table_name;
	$results = $wpdb->get_results( $sql );
	$enable_24h = get_option( 'enable_24h' );
	$enable_timezones = get_option( 'enable_timezones' );
?>
<table id="wcs-schedule-table">
	<tr>
		<th>&nbsp;</th>
		<?php
			foreach ( $week_days_array as $value ) {
				echo "<th class='weekday-label weekday-column'>" . substr( $value, 0, 3 ) . "</th>";
			}
		?>
	</tr>
	<?php
		foreach ( $start_hours_array as $start_hour ) {
			if ( $enable_24h == "on" ) {
				echo "<tr><td class='hour-label'>" . clean_time_format( $start_hour ) . "</td>";
			} else {
				echo "<tr><td class='hour-label'>" . convert_to_am_pm( $start_hour ) . "</td>";
			}
			
			foreach ( $week_days_array as $weekday ) {
				echo "<td class='" . strtolower( $weekday ) . "-column weekday-column'>";
				foreach ( $results as $entry) {
					if ( $entry->start_hour == $start_hour && $entry->week_day == $weekday && $entry->visible == "1" ) {
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
						$notes = ( strlen( $entry->notes ) > 14 ) ? substr( $entry->notes, 0 , 12 ) . "..." : $entry->notes;
						
						$output = "<!--[if IE 7]><div class='ie-container'><![endif]-->";
						$output .= "<div class='active-box-container'><div class='class-box'>" . $class . "</a></div>";
						$output .= "<div class='class-info'><a class='qtip-target' title='" . $class_desc . "'>" . $class . "</a>";
						$output .= " with ";
						$output .= "<a class='qtip-target' title='" . $inst_desc . "'>" . $inst . "</a><br/>";
						$output .= $class_start . " to " . $class_end;
						if ( $enable_timezones == "on" ) {
							$output .= "<div class'timezone-container'>" . $timezone . "</div>";
						}
						$output .= "<div class='notes-container'>" . $entry->notes . "</div>"; 
						$output .= "</div></div>";
						$output .= "<!--[if IE 7]></div><![endif]-->";
						echo $output;
					} 
				}
				echo "</td>";
			}
			
			echo "</tr>";
		}
	?>
</table>
</div>




