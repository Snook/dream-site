function status_init()
{
	check_status();
}

function check_status()
{
	$('[data-check_status]').each(function () {

		var check_status = $(this).data('check_status');
		var menu_id = $(this).data('menu_id');
		var this_element = $(this);

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data : {
				processor: 'admin_status',
				menu_id: menu_id,
				op: check_status
			},
			success: function(json, status)
			{
				if(json.processor_success)
				{
					if (json.status)
					{
						$(this_element).attr({'src': PATH.image_admin + '/icon/accept.png'});
					}
					else
					{
						$(this_element).attr({'src': PATH.image_admin + '/icon/delete.png'});
					}
				}
			},
			error: function(objAJAXRequest, strError)
			{
				response = 'Unexpected error';
			}
		});

	});


	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data : {
			processor: 'admin_status',
			op: 'cron_status'
		},
		success: function(json, status)
		{
			if(json.processor_success)
			{
				$("#cron_status_div").html(json.view);
			}
			else
			{
				$("#cron_status_div").html("An unexpected error occurred.");
			}
		},
		error: function(objAJAXRequest, strError)
		{
			response = 'Unexpected error';
		}
	});


}