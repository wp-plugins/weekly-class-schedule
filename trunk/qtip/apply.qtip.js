jQuery(function() {
	jQuery("a.qtip-target[title]").qtip({
		show: 'click',
        move: 'hide',
        hide: {
        	delay: 100
        },
        position: {
        	my: 'top center',
        	adjust: {
        		y: 10
      		}
        }
	});
});

function debug() {
	alert("bam!");
}
