<?php

class WcsTable {
		
	public $name;
	public $table_name;
	private $assoc_schedule_name;
	private $db_updated = false;
	
	function __construct( $instant_name, $assoc_schedule ) {
		global $wpdb;
		$this->name = $instant_name;
		$this->table_name = $wpdb->prefix . "wcs_" . $this->name;
		$this->assoc_schedule_name = $wpdb->prefix . "wcs_" . $assoc_schedule . "_schedule";
	}
		
	public function create_wcs_table() {
		$sql = "CREATE TABLE " . $this->table_name . " (
  	 			id int(11) NOT NULL AUTO_INCREMENT,
  	 			item_name VARCHAR(55) NOT NULL,
  				item_description text NOT NULL,
  	 			UNIQUE KEY id (id)
  				);";
		
		dbDelta( $sql );
	}
	
	public function manage_db_actions() {
		global $wpdb;
		global $db_updated;
		// Add item to the database
		if ( isset( $_POST['submit'] ) ) {
			verify_wcs_nonces( $this->name );
			$fields = array( 
						$_POST['new_item_name'], 
						$_POST['new_item_decription'] 
						);
			if ( verify_no_empty_fields( $fields ) ) {
				$new_item_name = $_POST['new_item_name'];
				$new_item_description = $_POST['new_item_decription'];
				
				$insert_array = array(
								'item_name' => $new_item_name,
								'item_description' => $new_item_description,
								);
								
				$db_insert = $wpdb->insert( $this->table_name, $insert_array );
				if ( $db_insert ) {
					$message = $new_item_name . " has been added to the database.";
					show_wp_message( $message, 'updated' );
					$db_updated = true;
				} else {
					show_wp_message( "Operation failed", 'error' );
				}
			}
		}
		
		// Delete item from the database
		if ( isset( $_POST['delete'] ) ) {
			verify_wcs_nonces( $this->name );
			$affected_rows = 0;
			foreach ( $_POST as $key => $value ) {
				if( $value == 'on' ) {
					$sql = $wpdb->prepare( "DELETE FROM " . $this->table_name . " WHERE id = %d", array( $key ) );
					$affected_rows += $wpdb->query( $sql );
				
					// Remove deleted items from associated schedule
					$sql = $wpdb->prepare( "DELETE FROM " . $this->assoc_schedule_name . " WHERE " . $this->name . "_id = %d", array ( $key ) );
					$wpdb->query( $sql );
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
						$_POST['updated_item_id'], 
						$_POST['update_item_name'],
						$_POST['updated_item_decription'] 
						);
			if ( verify_no_empty_fields( $fields ) ) {
				$updated_item_id = $_POST['updated_item_id'];
				$updated_item_name = $_POST['update_item_name'];
				$updated_item_description = $_POST['updated_item_decription']; 
				
				$update_array = array(
								'item_name' => $updated_item_name,
								'item_description' => $updated_item_description,
								);
				
				$update_id = array( "id" => $updated_item_id );
						
				$db_update = $wpdb->update( $this->table_name, $update_array, $update_id );
				
				if ( $db_update ) {
					$message = $updated_item_name . " has been updated succesfully.";
					show_wp_message( $message, 'updated' );
					$db_updated = true;
					
					$wpdb->update( $this->assoc_schedule_name, array( $this->name => $updated_item_name ), array( $this->name . '_id' => $updated_item_id ) ); 
				} else {
					show_wp_message( "Operation failed", 'error' );
				}
			}
		}
	}
	
	public function print_wcs_admin() {
		global $wpdb;
		global $db_updated;
		$result_set = $wpdb->get_results( "SELECT * FROM " . $this->table_name ); 
		?>
		<div class="wrap">
			<div id="wcs-<?php echo $this->name; ?>">
				<h2><?php echo ucwords( $this->name ); ?> Setup</h2>
				
				<?php if ( ! $result_set ) show_wp_message( "There are no classes in the database.", "updated"); ?>
				
				<form action="" method="post" id="wcs-add-item-form">
				
				<table class="wp-list-table widefat fixed">
					<tr>
						<th class="check-column"></th>
						<th class="wcs-name-column">Name</th>
						<th class="wcs-description-column">Description</th>
						<th class='edit-button-column'></th>
					</tr>
					<tr>
						<?php
						foreach ( $result_set as $value ) {
							$edit_url = "?" . key($_GET) . "=" . $_GET['page'] . "&edit=" . $value->id;
						
							$output = "<tr>";
							$output .= "<th class='check-column'><input type='checkbox' name='" . $value->id . "' /></th>";
							$output .= "<td class='wcs-name-column'>" . stripslashes( $value->item_name ) . "</td>";
							$output .= "<td>" . stripslashes( $value->item_description ) . "</td>";
							$output .= "<td class='edit-button-column'><a href='" . $edit_url . "'>Edit</a></td>";
							$output .= "</tr>";
							echo $output;
						}
						?>
					</tr>
					<tr>
						<td></td>
						<td><input type="text" name="new_item_name" maxlength="50" placeholder="Add <?php echo $this->name; ?> name" value="<?php echo ( $db_updated ) ? "" : stripslashes( $_POST['new_item_name'] ); ?>"/></td>
						<td><textarea name="new_item_decription" class="large-text" placeholder="Add <?php echo $this->name; ?> description" /><?php echo ( $db_updated ) ? "" : stripslashes( $_POST['new_item_decription'] ); ?></textarea></td>
						<td class='edit-button-column'></td>
					</tr>
				</table>
	
				<p>
					<input id='submit' type='submit' class='button-primary' value='Add Item' name='submit' />
					<input id='delete' type='submit' class='button-primary' value='Delete Selected' name='delete' />
				</p>
		
				<?php wp_nonce_field( $this->name ); ?>
				
				</form>
	
			</div> 
		</div> <!-- end of wrap -->
		<?php
	}

	public function print_wcs_admin_edit() {
		global $wpdb;
		global $db_updated;
		$item_id = $wpdb->escape( $_GET['edit'] );
		$result_set = $wpdb->get_row( "SELECT * FROM " . $this->table_name . " WHERE id = '" . $item_id . "'", ARRAY_A );
		?>
		<div class="wrap">
			<div id="wcs-<?php echo $this->name; ?>">
				<h2>Edit <?php echo ucwords( $this->name ); ?></h2>
				
				<?php if ( ! $result_set ) show_wp_message( "There are no items in the database.", "updated"); ?>
				
				<form action="" method="post" id="wcs-edit-item-form">
				
				<table class="wp-list-table widefat fixed">
					<tr>
						<th class="check-column"></th>
						<th class="wcs-name-column">Name</th>
						<th class="wcs-description-column">Description</th>
						<th class='edit-button-column'></th>
					</tr>
					<tr>
						<td><input type='hidden' name='updated_item_id' value='<?php echo stripslashes( $result_set['id'] ); ?>' /></td>
						<td><input type="text" name="update_item_name" maxlength="40" value="<?php echo stripslashes( $result_set['item_name'] ); ?>"/></td>
						<td><textarea name="updated_item_decription" class="large-text" /><?php echo stripslashes( $result_set['item_description'] ); ?></textarea></td>
						<td class='edit-button-column'></td>
					</tr>
				</table>
	
				<p>
					<input id='submit' type='submit' class='button-primary' value='Update Item' name='update' />
				</p>
		
				<?php wp_nonce_field( $this->name ); ?>
				
				</form>
	
			</div> 
		</div> <!-- end of wrap -->
		<?php
	}
}
