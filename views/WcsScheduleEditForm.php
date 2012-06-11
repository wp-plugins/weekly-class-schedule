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
		<td class="wcs-label-column">Class</td>
		<td class="wcs-entry-column">
			<?php echo WcsHtml::generateSelectList($classes, array( 'name' => 'class_select' ), FALSE, $entry->getClassName() ); ?>
		</td>
	</tr>
	<tr>
		<td class="wcs-label-column">Instructor</td>
		<td class="wcs-entry-column">
			<?php echo WcsHtml::generateSelectList($instructors, array( 'name' => 'instructor_select' ), FALSE, $entry->getInstructorName() ); ?>
		</td>
	</tr>
	<tr>
		<td class="wcs-label-column">Classroom</td>
		<td class="wcs-entry-column">
			<?php echo WcsHtml::generateSelectList($classrooms, array( 'name' => 'classroom_select' ), FALSE, $entry->getClassroomName() ); ?>
		</td>
	</tr>
	<tr>
		<td class="wcs-label-column">Day</td>
		<td class="wcs-entry-column"><?php echo WcsHtml::generateSelectList( $weekdays_array, array( 'name' => 'weekday_select' ), TRUE, $entry->getWeekday() ); ?></td>
	</tr>
	<tr>
		<td class="wcs-label-column">Start Hour </td>
		<td class="wcs-entry-column"><?php echo WcsTime::renderHourSelectList( 'start_hour', $entry->getStartHour() ); ?></td>
	</tr>
	<tr>
		<td class="wcs-label-column">End Hour</td>
		<td class="wcs-entry-column"><?php echo WcsTime::renderHourSelectList( 'end_hour', $entry->getEndHour() ); ?></td>
	</tr>
	<?php if ( get_option( 'wcs_use_timezones' ) == 'yes' ): ?>
	<tr>
		<td class="wcs-label-column">Timezone</td>
		<td class="wcs-entry-column"><?php echo WcsTime::renderTimezonesSelectList( 'timezone', $entry->getTimezone() ); ?></td>
	</tr>
	<?php endif; ?>
	<tr>
		<td class="wcs-label-column">Visibility</td>
		<td class="wcs-entry-column"><?php echo WcsHtml::generateSelectList( $visibility, array( 'name' => 'visibility_select' ), TRUE, $entry->getVisibility() ); ?></td>
	</tr>
	<tr>
		<td class="wcs-label-column">Notes</td>
		<td class="wcs-entry-column">
			<textarea name='notes' cols='30' placeholder='Notes'><?php echo $entry->getNotes( FALSE ); ?></textarea>
		</td>
	</tr>
</table>

<p>
	<input id='wcs-submit-item' type='submit' class='button-primary' value="<?php esc_attr_e('Save Item'); ?>" name='save_item' />
</p>