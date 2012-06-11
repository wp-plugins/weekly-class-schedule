(function ($) {
  
	$(document).ready(function() {
		// Base color picker
		$('.wcs_colorpicker').each(function(index) {
			var elementName = $(this).attr('id');
			$(this).ColorPicker({
				onChange: function (hsb, hex, rgb) {
					$('#' + elementName).val(hex);
					$('.' + elementName).css('background', '#' + hex);
				},
				onBeforeShow: function (hsb, hex, rgb) {
					$(this).ColorPickerSetColor(this.value);
				}
			});
		});
	});
  
}) (jQuery);