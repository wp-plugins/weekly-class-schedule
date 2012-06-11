<?php
/**
 * @file
 * Item form template.
 *
 * Available variables:
 * - $edit_url
 */
?>
<table class="wp-list-table widefat wcs-item-table">
	<tr>
		<th class="check-column"></th>
		<th class="wcs-name-column">Name</th>
		<th class="wcs-description-column">Description</th>
		<th class='wcs-edit-column'></th>
	</tr>
	<?php if ( isset( $items ) && ! empty( $items ) ): ?>
		<?php foreach( $items as $item ): ?>
			<tr>
				<td><input type="checkbox" name="delete_<?php echo $item->id; ?>" value="<?php echo $item->id; ?>" /></td>
				<?php foreach ( $item as $key => $value ): ?>
				<?php if ( $key != 'id' ) echo '<td>' . stripslashes( $value ) . '</td>'; ?>
				<?php endforeach; ?>
				<td><a href="<?php echo $edit_url; ?>&wcsid=<?php echo $item->id; ?>">Edit</a></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<tr>
		<td></td>
		<td><input type="text" name="new_item_name" maxlength="50" placeholder="Add name"/></td>
		<td><textarea name="new_item_description" placeholder="Add description" cols="60" /></textarea></td>
		<td></td>
	</tr>
</table>

<p>
	<input id='wcs-submit-item' type='submit' class='button-primary' value="<?php esc_attr_e('Add Item'); ?>" name='add_item' />
	<input id='wcs-delete-item' type='submit' class='button-primary' value="<?php esc_attr_e('Delete Item'); ?>" name='delete_items' />
</p>