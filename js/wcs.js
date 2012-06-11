(function ($) {
  
	$(document).ready(function() {
		// Adjust width and height
		$('.wcs-schedule td').each(function() {
			var td = $(this);
			var cont = td.children('.wcs-active-class-container');
			
			if (cont.height() > 0) {
				if (td.height() > cont.height()) {
					cont.children('div').height(td.height())
				}
			}
			
		});
		
		// Fix IE7 z-index issue
		if ($('html#ie7').length > 0) {
			var numberOfTd = ( $('.wcs-schedule td div').length + 10);
			
			$('.wcs-schedule td div').each(function() {
				$(this).css('z-index', numberOfTd);
				numberOfTd--;
			});
		}

		
		// Apply Hover Intent
		$('.wcs-active-div').hoverIntent(wcsOver, wcsOut);
		
		// Apply qTip
		$('a.wcs-qtip').each(function() {
			$(this).qtip({
				content: {
					text: $(this).attr('name')
				},
				show: {
				   delay: 0,
				   when: {
					   event: 'mousedown'
				   },
				   effect: {
					   length: 0
				   }
				},
	
				hide: { 
					delay: 300,
					when: {
						event: 'mouseout'
					}
				},
				position: {
					corner: {
						target: 'bottomMiddle',
						tooltip: 'topMiddle'
					}
				}
			});
		})
	});
	
	// Hover intent over callback function
	function wcsOver() {
		$(this).children('.wcs-class-details').fadeIn(200);
	}
	
	// Hover intet out callback function
	function wcsOut() {
		$(this).children('.wcs-class-details').hide();
	}

  
}) (jQuery);