jQuery.noConflict();
jQuery(function($) {
	$.ajaxSetup({
		error: function(jqXHR, exception) {

			$('span.ajax_loading').hide();

			var msg = '';
			if (jqXHR.status === 0) {
				msg = 'Not connect.\n Verify Network.';
			} else if (jqXHR.status == 404) {
				msg = 'Requested page not found. [404]';
			} else if (jqXHR.status == 500) {
				msg = 'Internal Server Error [500].';
			} else if (exception === 'parsererror') {
				msg  = 'Requested JSON parse failed.';
			} else if (exception === 'timeout') {
				msg = 'Time out error.';
			} else if (exception === 'abort') {
				msg = 'Ajax request aborted.';
			} else {
				msg = 'Uncaught Error.\n' + jqXHR.responseText;
			}

			$.fancybox({
				padding: 10,
				'modal' : true,
				title: 'Ajax error',
				content: '<div style="background:#ffffff; width:300px; height: 75px;"><div class="f-right"><a href="javascript:;" onclick="jQuery.fancybox.close();">Close</a></div><div class="clear"></div><br/><div align="center"><b>'+msg+'</b></div></div>'
			});

		}
	});
});