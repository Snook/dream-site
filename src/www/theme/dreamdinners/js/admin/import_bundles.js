function import_bundles_init()
{
	$('#menu, #bundles').change(function ()
	{
		if ($(this).find(':selected').data('imported') == true)
		{
			//dd_message({title: 'Alert', message: 'This menu has already been imported, importing items to this menu will append them to the existing items, it will not overwrite existing items.'});
		}

		toggle_submit();

	});

	$("#submit_menu_import").click(function ()
	{

		if (can_submit())
		{
			
			
			if ($("#testmode").is(":checked"))
			{
				$('#submit_menu_import').prop("disabled", true);
				$('#processing_image').show();
				$("#form_import_bundles").submit();
			}
			else
			{
				dd_message({
					title: 'Confirm',
					message: "Are you sure you want to create the bundles? This cannot be undone (very easily).",
					modal: true,
					confirm: function ()
					{
						$('#submit_menu_import').prop("disabled", true);
						$('#processing_image').show();
						$("#form_import_bundles").submit();
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
		$('#submit_menu_import').prop("disabled", false);
	}
	else
	{
		$('#submit_menu_import').prop("disabled", true);
	}
}

function can_submit()
{
	if (!$('#processing_image').is(':visible') && $('#menu').val() != '0' && $('#bundles').val() != '')
	{
		return true;
	}
	else
	{
		return false;
	}
}