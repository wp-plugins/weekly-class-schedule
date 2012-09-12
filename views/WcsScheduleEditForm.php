<?php
/**
 * @file
 * Schedule entry edit form template.
 *
 * Available variables:
 * - $classes
 * - $instructors
 * - $classrooms
 * - $weekdays_array: Full list of weekdays to be used in select lists.
 * - $visibility: Status of entry (visibile or hidden)
 * - $weekdays: Only days which contain entries.
 * - $items: Array of entry objects (WcsSchedule)
 */
?>
<table id="schedule-entry-form" class="wp-list-table widefat fixed">
	<tr>
		<td class="wcs-label-column"><?php _e('Class', 'weekly-class-schedule'); ?></td>
		<td class="wcs-entry-column">
			<?php echo WcsHtml::generateSelectList($classes, array( 'name' => 'class_select' ), FALSE, $entry->class_id ); ?>
		</td>
	</tr>
	<tr>
		<td class="wcs-label-column"><?php _e('Instructor', 'weekly-class-schedule'); ?></td>
		<td class="wcs-entry-column">
			<?php echo WcsHtml::generateSelectList($instructors, array( 'name' => 'instructor_select' ), FALSE, $entry->instructor_id ); ?>
		</td>
	</tr>
	<tr>
		<td class="wcs-label-column"><?php _e('Classroom', 'weekly-class-schedule'); ?></td>
		<td class="wcs-entry-column">
			<?php echo WcsHtml::generateSelectList($classrooms, array( 'name' => 'classroom_select' ), FALSE, $entry->classroom_id ); ?>
		</td>
	</tr>
	<tr>
		<td class="wcs-label-column"><?php _e('Day', 'weekly-class-schedule'); ?></td>
		<td class="wcs-entry-column"><?php echo WcsHtml::generateSelectList( $weekdays_array, array( 'name' => 'weekday_select' ), TRUE, $entry->getWeekday() ); ?></td>
	</tr>
	<tr>
		<td class="wcs-label-column"><?php _e('Start Hour', 'weekly-class-schedule'); ?></td>
		<td class="wcs-entry-column"><?php echo WcsTime::renderHourSelectList( 'start_hour', $entry->getStartHour() ); ?></td>
	</tr>
	<tr>
		<td class="wcs-label-column"><?php _e('End Hour', 'weekly-class-schedule'); ?></td>
		<td class="wcs-entry-column"><?php echo WcsTime::renderHourSelectList( 'end_hour', $entry->getEndHour() ); ?></td>
	</tr>
	<?php if ( get_option( 'wcs_use_timezones' ) == 'yes' ): ?>
	<tr>
		<td class="wcs-label-column"><?php _e('Timezone', 'weekly-class-schedule'); ?></td>
		<td class="wcs-entry-column"><?php echo WcsTime::renderTimezonesSelectList( 'timezone', $entry->getTimezone() ); ?></td>
	</tr>
	<?php endif; ?>
	<tr>
		<td class="wcs-label-column"><?php _e('Visibility', 'weekly-class-schedule'); ?></td>
		<td class="wcs-entry-column"><?php echo WcsHtml::generateSelectList( $visibility, array( 'name' => 'visibility_select' ), TRUE, $entry->getVisibility() ); ?></td>
	</tr>
	<tr>
		<td class="wcs-label-column"><?php _e('Notes', 'weekly-class-schedule'); ?></td>
		<td class="wcs-entry-column">
			<textarea name='notes' cols='30' placeholder='Notes'><?php echo $entry->getNotes( FALSE ); ?></textarea>
		</td>
	</tr>
</table>

<p>
	<input id='wcs-submit-item' type='submit' class='button-primary' value="<?php esc_attr_e('Save Item', 'weekly-class-schedule'); ?>" name='save_item' />
</p>