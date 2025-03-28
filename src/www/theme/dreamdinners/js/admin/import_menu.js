$(document).on('change', '#menu', function (e) {

	$('.import_option').prop({'checked': true});

	if ($(this).find(':selected').data('imported') == true)
	{
		$('.import_option:not(.update_default)').prop({'checked': false});
	}

});

$(document).on('change', '#menu, #base_menu_import, #testmode', function (e) {

	if (!$('#processing_image').is(':visible') && $('#menu').val() != '0' && $('#base_menu_import').val() != '')
	{
		$('#submit_menu_import').prop("disabled", false);
	}
	else
	{
		$('#submit_menu_import').prop("disabled", true);
	}

});

$(document).on('click', '#submit_menu_import', function (e) {

	e.preventDefault();

	if ($("#testmode").is(":checked"))
	{
		$('#submit_menu_import').prop("disabled", true);
		$('#processing_image').show();
		$("#form_import_menu").submit();
	}
	else
	{
		bootbox.confirm({
			message: "Are you sure you want to update/create the menu. This cannot be undone (very easily).",
			callback: function (result) {
				if (result)
				{
					$('#submit_menu_import').prop("disabled", true);
					$('#processing_image').show();
					$("#form_import_menu").submit();
				}
			}
		});
	}

});

$(document).on('click', '#checkbox_select_all', function (e) {

	e.preventDefault();

	$('.import_option').prop({'checked': true});

});

$(document).on('click', '#checkbox_select_update_default', function (e) {

	e.preventDefault();

	$('.update_default').prop({'checked': true});

});

$(document).on('click', '#checkbox_select_tier_pricing', function (e) {

	e.preventDefault();

	$('.update_tier_pricing').prop({'checked': true});

});

$(document).on('click', '#checkbox_select_none', function (e) {

	e.preventDefault();

	$('.import_option').prop({'checked': false});

});