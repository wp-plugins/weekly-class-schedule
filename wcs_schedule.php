<?php

class WcsSchedule {
		
	public $name;
	public $table_name;
	public $assoc_tables_array;
	private $week_days_array = array ( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'	);
	private $minutes = array(0, 15, 30, 45);
	
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
	
	public function add_timezone_column() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE " . $this->table_name . " ADD timezone VARCHAR(120) NOT NULL" );
	}
	
	public function add_visibility_column() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE " . $this->table_name . " ADD visible TINYINT NOT NULL DEFAULT '1' AFTER id" );
	}
	
	public function add_classrooms_columns() {
		global $wpdb;
		$wpdb->query( "ALTER TABLE " . $this->table_name . " ADD classroom_id int(11) NOT NULL AFTER visible" );
		$wpdb->query( "ALTER TABLE " . $this->table_name . " ADD classroom VARCHAR(120) NOT NULL AFTER classroom_id" );
	}
	
	public function add_default_classrooms_to_entries( $source ) {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM " . $this->table_name . " WHERE classroom = ''" );
		$default_value = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wcs_" . $source );
		foreach( $results as $value ) {
			$db_update = $wpdb->update(
				$this->table_name,
				array(
					$source . "_id" => $default_value[0]->id,
					$source => $default_value[0]->item_name,
				),
				array(
					'id' => $value->id,
				)
			);
		}
	}
	
	private function validate_time_logic( $start_hour, $end_hour, $week_day, $classroom ) {
		global $wpdb;
		$collision = array();
		$sql = $wpdb->prepare( "SELECT start_hour, end_hour, classroom FROM " . $this->table_name . " WHERE week_day = '%s'", $week_day );
		$results = $wpdb->get_results( $sql, ARRAY_A );
		$enable_classrooms = get_option( 'enable_classrooms' );
		if ( $enable_classrooms == "on" ) {
			foreach ( $results as $value ) {
				if ( $classroom == $value['classroom'] ) {
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
					$collision[] = ( $end_hour <= $start_hour ? true : false );
				}
			}	
		} else {
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
				$collision[] = ( $end_hour <= $start_hour ? true : false );
			}
		} 
		
		
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
		$enable_timezones = get_option( 'enable_timezones' );
		$enable_classrooms = get_option( 'enable_classrooms' );
		
		// Add item to the database
		if ( isset( $_POST['submit'] ) ) {
			verify_wcs_nonces( $this->name );
			if ( isset( $fields ) ) {
				unset( $fields );
			}
			$fields = array(
						$_POST['weekday_select'],
						$_POST['start_hour_hours'],
						$_POST['start_hour_minutes'],
						$_POST['end_hour_hours'],
						$_POST['end_hour_minutes'],
						$_POST['visibility'],
						);
			if ( $enable_24h != "on" ) {
				$fields[] = $_POST['start_hour_am_pm'];
				$fields[] = $_POST['end_hour_am_pm'];
			}
			if ( $enable_timezones == "on" ) {
				$fields[] = $_POST['timezone'];
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
					if ( $enable_classrooms == "on" ) {
						$classroom = $_POST['classroom_select'];
					} else {
						$classroom = '';
					}
					if ( $this->validate_time_logic( $start_hour, $end_hour, $_POST['weekday_select'], $classroom ) ) {
						$insert_array = array(
								'week_day' => $_POST['weekday_select'],
								'start_hour' => $start_hour,
								'end_hour' => $end_hour,
								'notes' => $_POST['new_entry_notes'],
								'visible' => $_POST['visibility'],
								);
						if ( $enable_timezones == "on" ) {
							$insert_array['timezone'] = $_POST['timezone'];
						}
					
						foreach ( $this->assoc_tables_array as $value ) {
							$sql = "SELECT id FROM " . $wpdb->prefix . "wcs_" . $value;
							$sql .= " WHERE item_name = '" . esc_js( $_POST["{$value}_select"] ) . "'";
							$item_id = $wpdb->get_var( $sql );
							$new_array = array( $value . '_id' => $item_id, $value => esc_js( $_POST["{$value}_select"] ) );
							$insert_array = array_merge( $insert_array, $new_array );
						}
					
						$db_insert = $wpdb->insert( $this->table_name, $insert_array );
						if ( $db_insert ) {
							$message = "The entry has been added to the database.";
							show_wp_message( $message, 'updated' );
							$db_updated = true;
						} else {
							show_wp_message( "Operation failed", 'error' );
						}
						
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
						$_POST['end_hour_hours'],
						$_POST['end_hour_minutes'],
						$_POST['visibility'],
						);
			if ( $enable_24h != "on" ) {
				$fields[] = $_POST['start_hour_am_pm'];
				$fields[] = $_POST['end_hour_am_pm'];
			}
			if ( $enable_timezones == "on" ) {
				$fields[] = $_POST['timezone'];
			}
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
							'visible' => $_POST['visibility'],
							);
				if ( $enable_timezones == "on" ) {
					$update_array['timezone'] = $_POST['timezone'];
				}	
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
				} else {
					show_wp_message( "Operation failed", 'error' );
				}
			}
		}
	}
	
	public function print_wcs_admin() {
	  global $wpdb;
		$enable_24h = get_option( 'enable_24h' );
		$enable_timezones = get_option( 'enable_timezones' );
		$enable_classrooms = get_option( 'enable_classrooms' );
		$result_set = $wpdb->get_results( "SELECT * FROM " . $this->table_name );
		?>
		<div class='wrap'>
			<h1><?php echo ucwords($this->name); ?> Schedule Setup</h1>
			<p>
				Use this shortcode <code>[wcs]</code>	to display the schedule.<br/>
			</p>

			<div id="<?php echo $this->name; ?>-schedule-admin-container">
				<?php if ( ! $result_set ) show_wp_message( "There are no classes in the database.", "updated"); ?>
	
				<form action="" method="post" id="wcs-add-schedule-entry-form">
				<?php 
					if ( $enable_classrooms == "on" ) { 
						$classrooms = array_unique( $wpdb->get_col( "SELECT classroom FROM " . $this->table_name . " WHERE visible = '1'") );
					} else {
						$classrooms = array('');
					}
					?>
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
									foreach( $this->minutes as $value ) {
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
									foreach( $this->minutes as $value ) {
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
						</tr>
						<tr>
							<td>Visibility:</td>
							<td>
								<select name="visibility">
									<option value="1">Visible</option>
									<option value="0">Hidden</option>
								</select>
							</td>
						</tr>
						<?php if ( $enable_timezones == "on" ) : ?>
						<tr>
							<td>Timezone:</td>
							<td>
								<select name="timezone">
									<?php
										$sql = "SELECT GMT, name FROM " . $wpdb->prefix . "wcs_timezones";
										$timezones = $wpdb->get_results( $sql, ARRAY_A );
										foreach( $timezones as $value ) {
											echo "<option value='" . $value['name'] . "'>" . $value['name'] . "</option>";
										}
									?>
								</select>
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<td>Notes:</td>
							<td>
								<textarea name="new_entry_notes" class="large-text" placeholder="Add notes" /><?php if ( $db_updated ) { echo ""; } else { stripslashes( $_POST['new_entry_notes'] ); } ?></textarea>
							</td>
						</tr>
					</table>
					<p>
						<input id='submit' type='submit' class='button-primary' value='Add Schedule Entry' name='submit' />
					</p>
					
					<?php
					foreach ( $classrooms as $classroom) :
						echo "<h2>" . $classroom . " Schedule</h2>";
					foreach ( $this->week_days_array as $value ) {
						if ( $enable_classrooms == "on" ) {
							$sql = "SELECT * FROM " . $this->table_name . " WHERE week_day='";
							$sql .= $value . "' AND classroom = '" . $classroom . "' ORDER BY start_hour ASC";
						} else {
							$sql = "SELECT * FROM " . $this->table_name . " WHERE week_day='";
							$sql .= $value . "' ORDER BY start_hour ASC";
						}
						
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
									<th>Status</th>
									<?php if ( $enable_timezones == "on" ) : ?>
									<th>Timezone</th>
									<?php endif; ?>
									<th>Notes</th>
									<th class='edit-button-column'></th>
								</tr>
					
							<?php
							foreach ( $entries as $value ) {
									
								$edit_url = "?" . key($_GET) . "=" . $_GET['page'] . "&edit=" . $value->id;
								$init_notes = esc_html($value->notes);
								$notes = ( strlen( $init_notes ) > 14 ) ? substr( $init_notes, 0 , 12 ) . "..." : $init_notes;
								$status = ( $value->visible == 1 ) ? "Visible" : "Hidden";
								
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
								$output .= "<td>" . $status . "</td>";
								if ( $enable_timezones == "on" ) {
									$end_pos = strpos( $value->timezone, ")" );
									$output .= "<td>" . substr( $value->timezone, 0, $end_pos + 1 ) . "</td>";
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
				if ( count( $classrooms ) > 1 || ( $result_set ) ) : ?>
				<div class="custom-hr">&nbsp;</div>
				<?php endif;
				endforeach;
				?>
				
				<br />
				
				<p>
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
		$enable_24h = get_option( 'enable_24h' );
		$enable_timezones = get_option( 'enable_timezones' );
		$enable_unesc_notes = get_option( 'enable_unescaped_notes' );
		
		$item_id = $wpdb->escape( $_GET['edit'] );
		$result_set = $wpdb->get_row( "SELECT * FROM " . $this->table_name . " WHERE id = '" . $item_id . "'", ARRAY_A );
		if ( $enable_24h == "on" ) {
			$start_time_array = convert_24h_to_array( $result_set['start_hour'] );
			$end_time_array = convert_24h_to_array( $result_set['end_hour'] ); 
		} else {
			$start_time_array = convert_to_am_pm( $result_set['start_hour'], 'array' );
			$end_time_array = convert_to_am_pm( $result_set['end_hour'], 'array' ); 
		}
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
									if ( $enable_24h == "on" ) {
										$hours = range(0, 23);
									} else {
										$hours = range(1, 12);
									}
									
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
									foreach( $this->minutes as $value ) {
										if ( $start_time_array['minutes'] == $value ) {
											echo "<option selected='selected' value ='" . $value . "'>" . $value . "</option>";
										} else {
											echo "<option value ='" . $value . "'>" . $value . "</option>";
										}
									}
								?>
								</select>
								<?php if ( $enable_24h != "on" ) : ?>
								<select name="start_hour_am_pm">
									<option <?php if ( $start_time_array['am_pm'] == 'AM' ) echo "selected='selected'"; ?> value="AM">AM</option>
									<option <?php if ( $start_time_array['am_pm'] == 'PM' ) echo "selected='selected'"; ?> value="PM">PM</option>
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
									} else {
										$hours = range(1, 12);
									}
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
									foreach( $this->minutes as $value ) {
										if ( $end_time_array['minutes'] == $value ) {
											echo "<option selected='selected' value ='" . $value . "'>" . $value . "</option>";
										} else {
											echo "<option value ='" . $value . "'>" . $value . "</option>";
										}
									}
								?>
								</select>
								<?php if ( $enable_24h != "on" ) : ?>
								<select name="end_hour_am_pm">
									<option <?php if ( $end_time_array['am_pm'] == 'AM' ) echo "selected='selected'"; ?> value="AM">AM</option>
									<option <?php if ( $end_time_array['am_pm'] == 'PM' ) echo "selected='selected'"; ?> value="PM">PM</option>
								</select>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td>Visibility:</td>
							<td>
								<select name="visibility">
									<option <?php if ( $result_set['visible'] == "1" ) echo "selected='selected'"; ?> value="1">Visible</option>
									<option <?php if ( $result_set['visible'] == "0" ) echo "selected='selected'"; ?>value="0">Hidden</option>
								</select>
							</td>
						</tr>
						<?php if ( $enable_timezones == "on" ) : ?>
						<tr>
							<td>Timezone</td>
							<td>
								<select name="timezone">
								<?php
									$sql = "SELECT GMT, name FROM " . $wpdb->prefix . "wcs_timezones";
									$timezones = $wpdb->get_results( $sql, ARRAY_A );
									foreach( $timezones as $value ) {
										if ( $result_set['timezone'] == $value['name'] ) {
											echo "<option selected='selected' value='" . $value['name'] . "'>" . $value['name'] . "</option>";
										} else {
											echo "<option value='" . $value['name'] . "'>" . $value['name'] . "</option>";
										}
										
									}
								?>
								</select>
							</td>
						</tr>
						<?php endif; ?>
						<tr>
							<td>Notes:</td>
							<?php 
							  $notes = $result_set['notes'];
							  if ($enable_unesc_notes == 'on') {
							    $notes = stripslashes($notes);
							  }
							?>
							<td>
							<textarea name="new_entry_notes" class="large-text" placeholder="Add notes" /><?php echo $notes; ?></textarea>
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
