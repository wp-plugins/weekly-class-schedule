/**
 * Scripts for the Weekly Class Schedule 3.0 front-end.
 */
(function($) {
	
	$(document).ready(function() {
		var locations = WCS3_DATA.locations,
			schedule_data;
				
		// Iterate over all schedules
		for (var i in locations) {
			schedule_data = locations[i];
			
			if (schedule_data.layout == 'normal') {
				WCS3_STD.draw_classes(schedule_data);
			}
		}
		
		// Apply qTip and hoverintent to .wcs3-class-container and .wcs3-qtip-box
		WCS3_LIB.apply_qtip();
	});
	
})(jQuery);


var WCS3_STD = {
			
	/**
	 * 	WCS3_DATA object available
	 */
		
	// Globals
	g_options: WCS3_DATA.options,
	
	/**
	 * Populates the normal layout table with classes.
	 */
	draw_classes: function(wcs3_data) {
		var classes = wcs3_data.classes,
			start_times = wcs3_data.unique_start_times,
			template = this.g_options.details_template;
				
		for (start_hour in classes) {
			var classes_data = classes[start_hour];
			
			for (class_data in classes_data) {
				var data = classes_data[class_data],
					item,
					item_all,
					style = '',
					html = '',
					template,
					output = '',
					location_slug = wcs3_data.location_slug,
					wrapper_id;
				
				if (typeof(location_slug) != 'undefined') {
					// Create location slug
					location_slug = location_slug.replace(/[^A-Za-z0-9]/g, '-').toLowerCase();
					wrapper_id = 'wcs3-location-' + location_slug;
					
					item = '#' + wrapper_id + ' td.wcs3-hour-row-' + data.start_hour_css + '.wcs3-day-col-' + data.weekday;
					
					output = WCS3_LIB.construct_template(template, data);
					
					if (data.color != null) {
						style = ' style="background-color: #' + data.color + '; "';
					}
					
					html += '<div class="wcs3-class-container"' + style + '>';
					html += '<div class="wcs3-class-name">' + data.class_title + '</div>';
					html += '<div class="wcs3-details-box-container">' + output + '</div>';
					html += '</div>';
					
					// Insert both to specific location table as well as to global table.
					jQuery(item).append(html);
				}
			}
		}
		
		this.wcs3_apply_parent_color();
	},
	
	/**
	 * Applied the primary color to the container td parent.
	 */
	wcs3_apply_parent_color: function() {
		jQuery('td.wcs3-cell').each(function() {
			var childs = jQuery('.wcs3-class-container', this),
				child_color;
			
			if (childs.length > 0) {
				// Got a child class, let's match color.
				child_color = jQuery(childs).css('background-color');
				jQuery(this).css('background-color', child_color);

				// And add a contain-class class :)
				jQuery(this).addClass('wcs3-active-cell');
				
			}
		});
	},
	
};