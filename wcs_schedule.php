<?php

class WcsSchedule {
		
	public $name;
	public $table_name;
	private $assoc_tables_array;
	private $week_days_array = array ( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'	);
	
	function __construct( $instant_name, $assoc_tables = array() ) {
		global $wpdb;
		$this->name = $instant_name;
		$this->table_name = $wpdb->prefix . "wcs_" . $this->name . "_schedule";
		$this->assoc_tables_array = $assoc_tables;
	}
		
	public function create_wcs_schedule_table() {
		$output;	
		foreach( $this->assoc_tables_array as $key => $value ) {
			$output .= $value . "_id int(11) NOT NULL,";
			$output .= $value . " VARCHAR(55) NOT NULL,";
		}
		$sql = "CREATE TABLE " . $this->table_name . " (
      	  id int(11) NOT NULL AUTO_INCREMENT,
      	  " . $output . "
      	  week_day VARCHAR(25) NOT NULL,
      	  start_hour TIME NOT NULL default '00:00:00',
      	  end_hour TIME NOT NULL default '00:00:00',
      	  notes TEXT NOT NULL,
      	  UNIQUE KEY id (id)
      	);";
	dbDelta( $sql );
	}
	
	private function validate_time_logic( $start_hour, $end_hour, $week_day ) {
		global $wpdb;
		$collision = array();
		$sql = $wpdb->prepare( "SELECT start_hour, end_hour FROM " . $this->table_name . " WHERE week_day = '%s'", $week_day );
		$results = $wpdb->get_results( $sql, ARRAY_A );
		foreach ( $results as $value ) {
			if ( $start_hour <= $value['start_hour'] ) {
				$collision[] = ( $end_hour > $value['start_hour'] ? true : false );
			}
			if ( $end_hour >= $value['end_hour'] ) {
				$collision[] = ( $start_hour < $value['end_hour'] ? true : false );
			}
			if ( $start_hour >= $value['start_hour'] ) {
				$collision[] = ( $start_hour < $value['end_hour'] ? true : false );
			}
			if ( $end_hour <= $value['end_hour'] ) {
				$collision[] = ( $end_hour > $value['start_hour'] ? true : false );
			}
		}
		$collision[] = ( $end_hour <= $start_hour ? true : false );
		if ( in_array( true, $collision ) ) {
			return false;
		} else {
			return true;
		}
	}
	
	public function manage_db_actions() {
		global $wpdb;
		global $db_updated;
		$enable_24h = get_option( 'enable_24h' );
		// Add item to the database
		if ( isset( $_POST['submit'] ) ) {
			verify_wcs_nonces( $this->name );
			if ( $enable_24h == "on" ) {
				$fields = array(
						$_POST['weekday_select'],
						$_POST['start_hour_hours'],
						$_POST['start_hour_minutes'],
						$_POST['end_hour_hours'],
						$_POST['end_hour_minutes'],
						);
			} else {
				$fields = array(
						$_POST['weekday_select'],
						$_POST['start_hour_hours'],
						$_POST['start_hour_minutes'],
						$_POST['start_hour_am_pm'],
						$_POST['end_hour_hours'],
						$_POST['end_hour_minutes'],
						$_POST['end_hour_am_pm'],
						);
			}
			foreach ( $this->assoc_tables_array as $value ) {
				$fields[] = $_POST["{$value}_select"];
			}
			if ( verify_no_empty_fields( $fields ) ) {
				
				if ( verify_selection_is_in_db( $this->assoc_tables_array ) ) {
						
					if ( $enable_24h == "on" ) {
						$start_hour = esc_js( convert_to_24h( $_POST['start_hour_hours'], $_POST['start_hour_minutes'] ) );
						$end_hour = esc_js( convert_to_24h( $_POST['end_hour_hours'], $_POST['end_hour_minutes'] ) );
					} else {
						$start_hour = esc_js( convert_from_am_pm( $_POST['start_hour_hours'], $_POST['start_hour_minutes'], $_POST['start_hour_am_pm'] ) );
						$end_hour = esc_js( convert_from_am_pm( $_POST['end_hour_hours'], $_POST['end_hour_minutes'], $_POST['end_hour_am_pm'] ) );
					}
					
					if ( $this->validate_time_logic( $start_hour, $end_hour, $_POST['weekday_select'] ) ) {
						$insert_array = array(
								'week_day' => $_POST['weekday_select'],
								'start_hour' => $start_hour,
								'end_hour' => $end_hour,
								'notes' => $_POST['new_entry_notes'],
								);
					
						foreach ( $this->assoc_tables_array as $value ) {
							$sql = "SELECT id FROM " . $wpdb->prefix . "wcs_" . $value;
							$sql .= " WHERE item_name = '" . esc_js( $_POST["{$value}_select"] ) . "'";
							$item_id = $wpdb->get_var( $sql );
							$new_array = array( $value . '_id' => $item_id, $value => esc_js( $_POST["{$value}_select"] ) );
							$insert_array = array_merge( $insert_array, $new_array );
						}
					
						$wpdb->insert( $this->table_name, $insert_array );
		
						$message = "The entry has been added to the database.";
						show_wp_message( $message, 'updated' );
						$db_updated = true;
					} else {
						$message = "Something doesn't make sense. Please check your entries and try again.";
						show_wp_message( $message, 'error' );
					}
				}	
			}
		}

		// Delete item from the database
		if ( isset( $_POST['delete'] ) ) {
			verify_wcs_nonces( $this->name );
			$affected_rows = 0;
			foreach ( $_POST as $key => $value ) {
				if( $value == 'on' ) {
					$sql = $wpdb->prepare( "DELETE FROM " . $this->table_name . " WHERE id = %d", array( $key ));
					$affected_rows += $wpdb->query( $sql );
				}
			}
	
			$message = $affected_rows . " ";
			$message .= ( $affected_rows == 1 ) ? 'item has ' : 'items have ';
			$message .= "been deleted from the database.";
			show_wp_message( $message, 'updated' );
		}
		
		// Update items
		if ( isset( $_POST['update'] ) ) {
			verify_wcs_nonces( $this->name );
			$fields = array(
						$_POST['weekday_select'],
						$_POST['start_hour_hours'],
						$_POST['start_hour_minutes'],
						$_POST['start_hour_am_pm'],
						$_POST['end_hour_hours'],
						$_POST['end_hour_minutes'],
						$_POST['end_hour_am_pm'],
						);
			foreach ( $this->assoc_tables_array as $value ) {
				$fields[] = $_POST["{$value}_select"];
			}
			if ( verify_no_empty_fields( $fields ) ) {
				
				// Check for collisions
					
				$start_hour = esc_js( convert_from_am_pm( $_POST['start_hour_hours'], $_POST['start_hour_minutes'], $_POST['start_hour_am_pm'] ) );
				$end_hour = esc_js( convert_from_am_pm( $_POST['end_hour_hours'], $_POST['end_hour_minutes'], $_POST['end_hour_am_pm'] ) );
				$updated_entry_id = esc_js( $_POST['updated_item_id'] );
				$update_id = array( "id" => $updated_entry_id );
				
				$update_array = array(
							'week_day' => esc_js($_POST['weekday_select']),
							'start_hour' => $start_hour,
							'end_hour' => $end_hour,
							'notes' => $_POST['new_entry_notes'],
							);
					
				foreach ( $this->assoc_tables_array as $value ) {
					$sql = "SELECT id FROM " . $wpdb->prefix . "wcs_" . $value;
					$sql .= " WHERE item_name = '" . esc_js( $_POST["{$value}_select"] ) . "'";
					$item_id = $wpdb->get_var( $sql );
					$new_array = array( $value . '_id' => $item_id, $value => esc_js( $_POST["{$value}_select"] ) );
					$update_array = array_merge( $update_array, $new_array );
				}
				
				$db_update = $wpdb->update( $this->table_name, $update_array, $update_id );
				
				if ( $db_update ) {
					$message = $updated_item_name . "Entry has been updated succesfully.";
					show_wp_message( $message, 'updated' );
					$db_updated = true;
				}
			}
		}
	}
	
	public function print_wcs_admin() {
		global $wpdb;
		$enable_24h = get_option( 'enable_24h' );
		$result_set = $wpdb->get_results( "SELECT * FROM " . $this->table_name ); ?>
		<div class='wrap'>
			<h1><?php echo ucwords($this->name); ?> Schedule Setup</h1>
			<p>
				Use this shortcode <code>[wcs]</code>	to display the schedule.<br/>
			</p>

			<div id="<?php echo $this->name; ?>-schedule-admin-container">
				<h2>Schedule</h2>
		
				<?php if ( ! $result_set ) show_wp_message( "There are no classes in the database.", "updated"); ?>
	
				<form action="" method="post" id="wcs-add-schedule-entry-form">
				<?php
					foreach ( $this->week_days_array as $value ) {
						$sql = "SELECT * FROM " . $this->table_name . " WHERE week_day='";
						$sql .= $value . "' ORDER BY start_hour ASC";
						$entries = $wpdb->get_results( $sql ); 
			
						// Only printing the days which have classes.
						if( ! empty ( $entries ) ) {
							echo "<h3>" . $value . "</h3>"; 
							?>
							<table class="wp-list-table widefat narrowfat fixed">
								<tr>
									<th class="check-column"></th>
									<?php 
										foreach ( $this->assoc_tables_array as $value ) {
											echo "<th>" . ucwords( $value ) . "</th>";
										}
									?>
									<th>From</th>
									<th>To</th>
									<th>Notes</th>
									<th class='edit-button-column'></th>
								</tr>
					
							<?php
							foreach ( $entries as $value ) {
									
								$edit_url = "?" . key($_GET) . "=" . $_GET['page'] . "&edit=" . $value->id;
								$notes = ( strlen( $value->notes ) > 14 ) ? substr( $value->notes, 0 , 12 ) . "..." : $value->notes;
								
								$output = "<tr>";
								$output .= "<th class='check-column'><input type='checkbox' name='" . $value->id . "' /></th>";
								foreach ( $this->assoc_tables_array as $second_value ) {
									$output .= "<td>" . $value->$second_value . "</td>";
								}
								
								if ( $enable_24h == "on" ) {
									$output .= "<td>" . clean_time_format( $value->start_hour ) . "</td>";
									$output .= "<td>" . clean_time_format( $value->end_hour ) . "</td>";
								} else {
									$output .= "<td>" . convert_to_am_pm( $value->start_hour ) . "</td>";
									$output .= "<td>" . convert_to_am_pm( $value->end_hour ) . "</td>";
								}
								$output .= "<td>" . $notes . "</td>";
								$output .= "<td class='edit-button-column'><a href='" . $edit_url . "'>Edit</a></td>";
								
								echo $output;
							}
							?>
							</table>
						<?php 
						} 
					}
				?>
	
				<br />
				<h2>Add Schedule Entry</h2>
				<table class="wp-list-table widefat entry-table fixed">
					<?php 
						foreach ( $this->assoc_tables_array as $value ) {
							$output = "<tr><td class='wcs-label-column'>" . ucwords( $value ) . "</td>";
							$output .= "<td class='wcs-entry-column'>";
							$sql = "SELECT item_name FROM " . $wpdb->prefix . "wcs_" . $value;
							$output .= "<select name='" . $value . "_select'>";
							$results = $wpdb->get_col( $sql );
							if ( ! $results ) {
								$output .= "<option value =''>Please add to $value database</option>"; 
							} else {
								foreach ( $results as $key => $second_value ) {
									$output .= "<option value ='" . $second_value . "'>" . $second_value . "</option>"; 
								}
							}
							$output .= "</select></td></tr>";
							echo $output;
						}
					?>
					<tr>	
						<td>Day:</td>
						<td> 
							<select name="weekday_select">
							<?php
								foreach( $this->week_days_array as $value ) {
									echo "<option value ='" . $value . "'>" . $value . "</option>";
								}
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Start Hour:</td>
						<td>
							<select name="start_hour_hours">
							<?php 
								if ( $enable_24h == "on" ) {
									$hours = range(0, 23);
									foreach( $hours as $value ) {
										echo "<option value ='" . $value . "'>" . $value . "</option>";
									}
								} else {
									$hours = range(1, 12);
									foreach( $hours as $value ) {
										echo "<option value ='" . $value . "'>" . $value . "</option>";
									}
								}
							?>
							</select>
							<select name="start_hour_minutes">
							<?php 
								$minutes = array(0, 15, 30, 45);
								foreach( $minutes as $value ) {
									echo "<option value ='" . $value . "'>" . $value . "</option>";
								}
							?>
							</select>
							<?php if ( $enable_24h != "on" ) : ?>
							<select name="start_hour_am_pm">
								<option value="AM">AM</option>
								<option value="PM">PM</option>
							</select>
							<?php endif; ?>
						</td>
					</tr>	
					<tr>
						<td>End Hour:</td>
						<td> 
							<select name="end_hour_hours">
							<?php 
								if ( $enable_24h == "on" ) {
									$hours = range(0, 23);
									foreach( $hours as $value ) {
										echo "<option value ='" . $value . "'>" . $value . "</option>";
									}
								} else {
									$hours = range(1, 12);
									foreach( $hours as $value ) {
										echo "<option value ='" . $value . "'>" . $value . "</option>";
									}
								}
							?>
							</select>
							<select name="end_hour_minutes">
							<?php 
								$minutes = array(0, 15, 30, 45);
								foreach( $minutes as $value ) {
									echo "<option value ='" . $value . "'>" . $value . "</option>";
								}
							?>
							</select>
							<?php if ( $enable_24h != "on" ) : ?>
							<select name="end_hour_am_pm">
								<option value="AM">AM</option>
								<option value="PM">PM</option>
							</select>
							<?php endif; ?>
						</td>
						<tr>
							<td>Notes:</td>
							<td>
							<textarea name="new_entry_notes" class="large-text" placeholder="Add notes" /><?php if ( $db_updated ) { echo ""; } else { stripslashes( $_POST['new_entry_notes'] ); } ?></textarea>
						</td>
						</tr>
					</tr>
				</table>
				<p>
					<input id='submit' type='submit' class='button-primary' value='Add Schedule Entry' name='submit' />
					<input id='delete' type='submit' class='button-primary' value='Delete Entry' name='delete' />
				</p>
				
				<?php wp_nonce_field( $this->name ); ?>
	
				</form>
			</div> <!-- end of schedule-container -->
		</div> <!-- end of wrap -->
		<?php	
	}

	public function print_wcs_admin_edit() {
		global $wpdb;
		global $db_updated;
		$item_id = $wpdb->escape( $_GET['edit'] );
		$result_set = $wpdb->get_row( "SELECT * FROM " . $this->table_name . " WHERE id = '" . $item_id . "'", ARRAY_A );
		$start_time_array = convert_to_am_pm( $result_set['start_hour'], 'array' );
		$end_time_array = convert_to_am_pm( $result_set['end_hour'], 'array' ); 
		?>
		<div class="wrap">
			<div id="<?php echo $this->name; ?>-schedule-admin-container">
				<h2>Edit Schedule Entry</h2>
					<form action="" method="post" id="wcs-edit-schedule-entry-form">
					<table class="wp-list-table widefat entry-table fixed">
						<?php 
							foreach ( $this->assoc_tables_array as $value ) {
								$output = "<tr><td class='wcs-label-column'>" . ucwords( $value ) . "</td>";
								$output .= "<td class='wcs-entry-column'>";
								$sql = "SELECT item_name FROM " . $wpdb->prefix . "wcs_" . $value;
								$output .= "<select name='" . $value . "_select'>";
								$results = $wpdb->get_col( $sql );
								foreach ( $results as $key => $second_value ) {
									if ( $result_set[$value] == $second_value ) {
										$output .= "<option selected='selected' value ='" . $second_value . "'>" . $second_value . "</option>";
									} else {
										$output .= "<option value ='" . $second_value . "'>" . $second_value . "</option>"; 
									}
								}
								$output .= "</select></td></tr>";
								echo $output;
							}
						?>
						<tr>	
							<td>Day:</td>
							<td> 
								<select name="weekday_select">
								<?php
									foreach( $this->week_days_array as $value ) {
										if ( $result_set['week_day'] == $value) {
											echo "<option selected='selected' value ='" . $value . "'>" . $value . "</option>";
										} else {
											echo "<option value ='" . $value . "'>" . $value . "</option>";
										}
									}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Start Hour:</td>
							<td>
								<select name="start_hour_hours">
								<?php 
									$hours = range(1, 12);
									foreach( $hours as $value ) {
										if ( $start_time_array['hours'] == $value ) {
											echo "<option selected='selected' value ='" . $value . "'>" . $value . "</option>";
										} else {
											echo "<option value ='" . $value . "'>" . $value . "</option>";
										}
									}
								?>
								</select>
								<select name="start_hour_minutes">
								<?php 
									$minutes = array(0, 15, 30, 45);
									foreach( $minutes as $value ) {
										if ( $start_time_array['minutes'] == $value ) {
											echo "<option selected='selected' value ='" . $value . "'>" . $value . "</option>";
										} else {
											echo "<option value ='" . $value . "'>" . $value . "</option>";
										}
									}
								?>
								</select>
								<select name="start_hour_am_pm">
									<option <?php if ( $start_time_array['am_pm'] == 'AM' ) echo "selected='selected'"; ?> value="AM">AM</option>
									<option <?php if ( $start_time_array['am_pm'] == 'PM' ) echo "selected='selected'"; ?> value="PM">PM</option>
								</select>
							</td>
						</tr>	
						<tr>
							<td>End Hour:</td>
							<td> 
								<select name="end_hour_hours">
								<?php 
									$hours = range(1, 12);
									foreach( $hours as $value ) {
										if ( $end_time_array['hours'] == $value ) {
											echo "<option selected='selected' value ='" . $value . "'>" . $value . "</option>";
										} else {
											echo "<option value ='" . $value . "'>" . $value . "</option>";
										}
									}
								?>
								</select>
								<select name="end_hour_minutes">
								<?php 
									$minutes = array(0, 15, 30, 45);
									foreach( $minutes as $value ) {
										if ( $end_time_array['minutes'] == $value ) {
											echo "<option selected='selected' value ='" . $value . "'>" . $value . "</option>";
										} else {
											echo "<option value ='" . $value . "'>" . $value . "</option>";
										}
									}
								?>
								</select>
								<select name="end_hour_am_pm">
									<option <?php if ( $end_time_array['am_pm'] == 'AM' ) echo "selected='selected'"; ?> value="AM">AM</option>
									<option <?php if ( $end_time_array['am_pm'] == 'PM' ) echo "selected='selected'"; ?> value="PM">PM</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>Notes:</td>
							<td>
							<textarea name="new_entry_notes" class="large-text" placeholder="Add notes" /><?php echo $result_set['notes']; ?></textarea>
						</td>
						</tr>
					</table>
					<input type='hidden' name='updated_item_id' value='<?php echo stripslashes( $result_set['id'] ); ?>' />
					<p>
						<input id='submit' type='submit' class='button-primary' value='Update Entry' name='update' />
					</p>
				
					<?php wp_nonce_field( $this->name ); ?>
	
					</form>
				</div>
			</div> <!-- end of wrap -->
		<?php
	}
}
