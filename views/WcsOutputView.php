<?php
/**
 * @file
 * Output view template.
 *
 * Available Variables:
 * - $weekday_names: Array of weekday names to be used in table output.
 * - $weekdays: Array of used weekdays based on user preference.
 * - $start_hours: Array of unique start hours.
 * - $classes: Multi-dimensional array in the structure of $classes[weekday][start_hour].
 */
?>

<table class="wcs-schedule">
	<tr>
		<th></th>
		<?php foreach ( $weekday_names as $weekday ): ?>
		<th><?php echo $weekday; ?></th>
		<?php endforeach; ?>
	</tr>
	<?php foreach ( $start_hours as $start_hour ): ?>
		<tr>
			<th class="wcs-hour-title"><?php echo $start_hour; ?></th>
			<?php foreach ( $weekdays as $weekday ): ?>
			<?php echo WcsSchedule::model()->renderClassTd( $classes, $start_hour, $weekday ) ?>
  			<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
</table>