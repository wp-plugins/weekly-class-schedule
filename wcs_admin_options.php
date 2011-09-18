<?php
global $wpdb;

if ( ! empty( $_POST ) ) { // verify a submission occured
	if ( check_admin_referer( 'wcs_admin_options_page' ) ) { // verify nonce
		
		if ( isset( $_POST['uninstall_wcs'])) {
			wcs_uninstall();
			
			$message = "All the database tables have been deleted.";
			show_wp_message( $message, 'updated' );
		}
	}
}
?>

<div class='wrap'>
	<h1>Options</h1>
	
	<form method="post" action="options.php">
		<?php settings_fields('wcs_options'); ?>
		<?php do_settings_sections('wcs_options_page'); ?>
    <p class="submit">
   		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
	</form>
	
	<hr />
	
	<form action="" method="post" id="options_page_form">
	<h3>Uninstall Class Schedule</h3>
	<p>
		<strong>WARNING: </strong>Make sure you know what you're doing before using this option.
	</p>
		<table class="wp-list-table widefat narrowfat fixed">
			<tr>
				<td class="button-column">
					<input id='uninstall_wcs' onclick="return show_confirm('uninstall this plugin')" type='submit' class='button-primary' value='Uninstall Plugin' name='uninstall_wcs' />
				</td>
				<td>
					This will delete all the plugin tables from the database. <strong>Only</strong> use this option if you mean to remove the plugin from your WordPress installation.
				</td>
			</tr>
		</table>
	<?php wp_nonce_field('wcs_admin_options_page'); ?>
	</form>
</div>
