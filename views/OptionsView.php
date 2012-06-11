<?php
/**
 * @file
 * Options form template.
 */
?>
<div id="wcs-options-form" class="wrap">
	<div class="icon32" id="icon-options-general"></div>
	<h2>Weekly Class Schedule Options</h2>
	<?php echo $test; ?>
	<form action="options.php" method="post">
		<?php do_settings_sections( 'wcs-options' ); ?>

		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>

	<?php settings_fields( 'wcs_options' ); ?>
	</form>
</div><!-- wrap -->