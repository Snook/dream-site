function import_nutritionals_init()
{
	$('#menu, #base_menu_import').change(function ()
	{

		if ($(this).find(':selected').data('imported') == true)
		{
			/*
			dd_message({
				title: 'Alert',
				message: 'This menu has already been imported, importing items to this menu will append them to the existing items, it will not overwrite existing items.'
			});
			*/
		}

		toggle_submit();

	});

	
	$("#submit_nutritionals_import").click(function ()
	{
		
		if (can_submit())
		{
			if ($("#testmode").is(":checked"))
			{
				$('#submit_nutritionals_import').prop("disabled", true);
				$('#processing_image').show();
				$("#form_import_nutritionals").submit();
			}
			else
			{
				dd_message({
					title: 'Confirm',
					message: "Are you sure you want to update/create the nutritional data. This cannot be undone (very easily).",
					modal: true,
					confirm: function ()
					{
						$('#submit_nutritionals_import').prop("disabled", true);
						$('#processing_image').show();
						$("#form_import_nutritionals").submit();
					}
				});
				
			}

			return false;
		}

		event.preventDefault();

	});
}

function toggle_submit()
{
	if (can_submit())
	{
		$('#submit_nutritionals_import').prop("disabled", false);
	}
	else
	{
		$('#submit_nutritionals_import').prop("disabled", true);
	}
}

function can_submit()
{
	if (!$('#processing_image').is(':visible') && $('#menu').val() != '0' && $('#base_menu_import').val() != '')
	{
		return true;
	}
	else
	{
		return false;
	}
}