<?php
/**
 * @file
 * Item edit form template.
 *
 * Available variables:
 * - $item_name
 * - $item_description
 */
?>
<table class="wp-list-table widefat wcs-item-table">
	<tr>
		<th class="check-column"></th>
		<th class="wcs-name-column"><?php _e('Name', 'weekly-class-schedule'); ?></th>
		<th class="wcs-description-column"><?php _e('Description', 'weekly-class-schedule'); ?></th>
		<th class='edit-button-column'></th>
	</tr>

	<tr>
	<td></td>
		<td><input type="text" name="new_item_name" maxlength="50" placeholder="Add name" value="<?php echo $item_name; ?>"/></td>
		<td><textarea name="new_item_description" placeholder="Add description" cols="60" /><?php echo $item_description; ?></textarea></td>
		<td class='edit-button-column'></td>
	</tr>
</table>

<p>
	<input id='wcs-submit-item' type='submit' class='button-primary' value='<?php esc_attr_e('Save Item', 'weekly-class-schedule'); ?>' name='save_item' />
</p>