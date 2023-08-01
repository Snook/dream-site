function test_order_init()
{
	create_and_submit_form({
		action: 'main.php?page=admin_order_mgr&user=' + params.user_id,
		input: ({
			'session': params.session_id,
			'request': 'savedTasteOrder',
		})
	});
}

function click_dd_message_button(div_id, button_title)
{
	var topDiv = $("#" + div_id).parent();
	
	topDiv.find("[type=button]").each(function()
	{
		var buttonText = $(this).find("span").html();
		if (buttonText == button_title)
		{
			$(this).click();		
		}
	});
}
