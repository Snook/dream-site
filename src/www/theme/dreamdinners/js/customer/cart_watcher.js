function CreateDebugView(objArray)
{


	var propCount = 0;

	for (prop in objArray)
	{
		propCount++;
	}

	if (propCount == 0)
	{
		return;
	}

	var str = '<ul>';

	for (prop in objArray)
	{
		if (objArray[prop] == null ||  objArray[prop] == undefined || objArray[prop] == 0 || objArray[prop] == "0" ||  objArray[prop] == "0.00")
		{
			continue;
		}

		str += '<li>';
		if (typeof objArray[prop] == 'object')
		{
			str += "<b>" + prop + "</b> " + CreateDebugView(objArray[prop]);
		}
		else
		{
			str += "<b>" + prop + "</b>: " + objArray[prop];
		}
		str += '</li>';
	}

	str += '</ul>';

	return str;
}

function doCartDebugging()
{
	var method = $("input[name='attach_method']:checked").val();

	var key = "none";
	if (method == 'USER_ID')
	{
		key = $("#user_id").val();

	}
	else if (method == 'CART_ID')
	{
		key = $("#cart_id").val();
	}

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'debug_cart',
			op: 'view',
			method: method,
			key: key
		},
		success: function (json)
		{
			if (json.processor_success)
			{
				var parsedData = CreateDebugView(json.data);
				$(".cart_watcher_overview").html(parsedData);
			}
			else
			{
				$(".cart_watcher_overview").html('<h4>Cart Data not Found</h4><h6>' + json.processor_message + '</h6>');
			}
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';
		}
	});
}



$(function () {

	doCartDebugging();

	$.doTimeout('cart_debug_timer', 5000, function () {

		doCartDebugging();

		return true;

	});

});