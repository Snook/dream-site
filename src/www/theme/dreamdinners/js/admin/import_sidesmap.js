function import_sidesmap_init()
{
	$('#menu, #base_menu_sidesmap').change(function () {

		toggle_submit();

	});

	$("#form_import_menu").submit(function () {

		if (can_submit())
		{
			$('#submit_menu_import').prop("disabled", true);

			$('#processing_image').show();

			return;
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
	if (!$('#processing_image').is(':visible') && $('#menu').val() != '0' && $('#base_menu_sidesmap').val() != '')
	{
		return true;
	}
	else
	{
		return false;
	}
}