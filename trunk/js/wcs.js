jQuery(function() {
	
	jQuery(".wcs-schedule-table").parent().css("position", "relative");
	
	jQuery("td.weekday-column").each(function() {
		if( jQuery(this).children().size() > 0 ) {
			jQuery(this).addClass("active-box");
		}
	});
	
	jQuery(".class-box").each(function() {
		
		var infoBox = jQuery(this).siblings(".class-info");
		var boxHeight = jQuery(this).height();

		jQuery(this).hoverIntent(
			function() {
				var positionTop = jQuery(this).position().top;

				infoBox.fadeIn(200).css({
					top: positionTop,
					minHeight: boxHeight
				}).mouseleave(function(){
					jQuery(this).fadeOut(100);
			});
		}, function() {
			
		});
	});
	
	// IE7 Fix
	var zIndexNumber = 1000;
	jQuery('td.weekday-column').each(function() {
		jQuery('.ie-container div', this).css('zIndex', zIndexNumber);
		zIndexNumber -= 10;
	});
});
