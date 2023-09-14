
function markGiftReceived(level, user_id, gift_id)
{
	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data : {
			processor: 'plate_points_processor',
			user_id: user_id,
			op: 'mark_gift_received',
			level: level,
			gift_id: gift_id
		},
		success: function(json, status)
		{
			if(json.processor_success)
			{
				$("#gft_" + user_id).html("The guest has received their current level gift: " + json.giftDisplayString);
			}
			else
			{
				dd_message({
					title: 'Error',
					message: 'There was a problem marking the gift as received.'
				});
				
			}
		},
		error: function(objAJAXRequest, strError)
		{
			response = 'Unexpected error';
		}
	});
	
	
	

}

function print_GSRD()
{
	 var url = document.URL + "&print=true";
	 var win = window.open(url, '_blank');
	  win.focus();
}