/**
 * Scripts for the Weekly Class Schedule 3.0 front-end.
 */
(function($) {
	
	/**
	 * 	WCS3_DATA object available
	 */
		
	$(document).ready(function() {
		var data = WCS3_DATA,
			schedules_count = data.length,
			schedule_data;
		
		// Iterate over all schedules
		for (var i = 0; i < schedules_count; i++) {
			schedule_data = data[i];
			
			if (schedule_data.layout == 'normal') {
				wcs3_populate_normal_layout(schedule_data);
			}
			else if (schedule_data.layout == 'list') {
				// Do nothing.
			} 
		}
		wcs3_apply_qtip();
		
	});
	
	/**
	 * Populates the normal layout table with classes.
	 */
	var wcs3_populate_normal_layout = function(wcs3_data) {
		var classes = wcs3_data.classes,
			start_times = wcs3_data.unique_start_times;
				
		for (start_hour in classes) {
			var classes_data = classes[start_hour];
			
			for (class_data in classes_data) {
				var data = classes_data[class_data],
					item,
					item_all,
					html = '',
					template,
					location_slug = data.location_title,
					wrapper_id;
				
				// Create location slug
				location_slug = location_slug.replace(/[^A-Za-z0-9]/g, '-').toLowerCase();
				wrapper_id = 'wcs3-location-' + location_slug;
				
				item = '#' + wrapper_id + ' td.wcs3-hour-row-' + data.start_hour_css + '.wcs3-day-col-' + data.weekday;
				item_all = '#wcs3-location-all td.wcs3-hour-row-' + data.start_hour_css + '.wcs3-day-col-' + data.weekday;
				
				template = wcs3_construct_template(wcs3_data, data);
				
				html += '<div class="wcs3-class-container">';
				html += '<div class="wcs3-class-name">' + data.class_title + '</div>';
				html += '<div class="wcs3-details-box-container">' + template + '</div>';
				html += '</div>';
				
				// Insert both to specific location table as well as to global table.
				$(item).append(html);
				$(item_all).append(html);
			}
		}
		
		wcs3_apply_parent_color();
	}
	
	/**
	 * builds the template object from WCS3_DATA and the class object
	 * passed from PHP.
	 */
	var wcs3_construct_template = function(wcs3_data, data) {
		var template = wcs3_data.options.details_template,
			class_a,
			instructor_a,
			location_a;
		
		class_a = '<span class="wcs3-qtip-box"><a href="#qtip" class="wcs3-qtip">' + data.class_title + '</a>';
		class_a += '<span class="wcs3-qtip-data">' + data.class_desc + '</span></span>';
		
		instructor_a = '<span class="wcs3-qtip-box"><a href="#qtip" class="wcs3-qtip">' + data.instructor_title + '</a>';
		instructor_a += '<span class="wcs3-qtip-data">' + data.instructor_desc + '</span></span>';
		
		location_a = '<span class="wcs3-qtip-box"><a href="#qtip" class="wcs3-qtip">' + data.location_title + '</a>';
		location_a += '<span class="wcs3-qtip-data">' + data.location_desc + '</span></span>';
		
		
		// Replace template placeholders
		template = template.replace('[class]', class_a);
		template = template.replace('[instructor]', instructor_a);
		template = template.replace('[location]', location_a);
		template = template.replace('[start hour]', data.start_hour);
		template = template.replace('[end hour]', data.end_hour);
		template = template.replace('[notes]', data.notes);
		
		return template;
	}
	
	/**
	 * Applies hover and qtip to normal layout.
	 */
	var wcs3_apply_qtip = function() {
		// Standard hover
		$('.wcs3-class-container').each(function() {
			$(this).hoverIntent(function() {
				// Hover on
				$('.wcs3-details-box-container', this).fadeIn(200);
			},
			function() {
				// Hover off
				$('.wcs3-details-box-container', this).hide()
			});
		});
		
		// qTip
		$('.wcs3-qtip-box').each(function() {
			var html = $('.wcs3-qtip-data', this).html();
			
			$('a.wcs3-qtip', this).qtip({ 
			    content: {
			        text: html
			    },
			    show: {
			        event: 'click',
			    },
			    style: { classes: 'wcs3-qtip-tip' }
			})
		});
	}
	
	/**
	 * Applied the primary color to the container td parent.
	 */
	var wcs3_apply_parent_color = function() {
		$('td.wcs3-cell').each(function() {
			var childs = $('.wcs3-class-container', this),
				child_color;
			
			if (childs.length > 0) {
				// Got a child class, let's match color.
				child_color = $(childs).css('background-color');
				$(this).css('background-color', child_color);
				
			}
		});
	}
	
})(jQuery)