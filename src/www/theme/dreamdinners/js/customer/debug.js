DEBUG = true;

/*
var BugHerdConfig = {
	metadata: {
		username: ANALYTICS.email,
		default_store_id: $.cookie('default_store_id')
	}
};


(function (d, t) {
	var bh = d.createElement(t), s = d.getElementsByTagName(t)[0];
	bh.type = 'text/javascript';
	bh.src = 'https://www.bugherd.com/sidebarv2.js?apikey=k2anixcctsomeyit9olxrg';
	s.parentNode.insertBefore(bh, s);
})(document, 'script');
 */

function dd_debug(settings)
{
	dd_message(settings);
}


(function() {

	$(document).on('click', '.watch_cart', function (e) {
		var win = window.open('main.php?page=cart_watcher', '_blank');
	});

	if (typeof $.feedback == 'function')
	{
		$.feedback({
			ajaxURL: 'ddproc.php?processor=feedback',
			html2canvasURL: PATH.script + '/vendor/html2canvas/html2canvas.js',
			onClose: function () {
				window.location.reload();
			}
		});
	}

	$(document).on('click', '.return-fauid', function (e) {

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'debug_cart',
				op: 'return_fauid',
				fauid: $.cookie('FAUID'),
				dduid: $.cookie('DDUID')
			},
			success: function (json) {
				if (json.processor_success)
				{
					bounce(json.bounce);
				}
			},
			error: function (objAJAXRequest, strError) {
				strError = 'Unexpected error';
			}
		});

	});

})();