jQuery && jQuery(document).ready(function($) {
	
	// hack 'display-inline' for LI
	$('.menu-item-hbox > .menu-container > li').css({ 'display':'inline', 'zoom':1 })
	
	// calculate width of menu container
	$('.menu-item-hbox > .menu-container').each(function() {
		var totalWidth = 0;
		$(this).children('li').each(function() {
			totalWidth += $(this).outerWidth(true);	
		});
		$(this).css('width', totalWidth+'px');
	});
	
});