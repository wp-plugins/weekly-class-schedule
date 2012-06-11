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
		<?php foreach ( $start_hours as $start_hour ):  ?>
		<th><?php echo $start_hour; ?></th>
		<?php endforeach; ?>
	</tr>
	<?php foreach ( $weekday_names as $key => $weekday ):?>
	<tr>
		<th class="wcs-weekday-title"><?php echo $weekday; ?></th>
		<?php foreach ( $start_hours as $start_hour ):  ?>
		<?php echo WcsSchedule::model()->renderClassTd( $classes, $start_hour, $weekdays[$key], 'horizontal', $start_hours ) ?>
		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
</table>