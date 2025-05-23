$(document).on('change', '#preferred_type', function (e) {

	const selectedType = $("#preferred_type").val();

	if (selectedType === 'FLAT')
	{
		$("#preferred_cap_type option[value*='SERVINGS']").hideFlex();
		$('#include_sides').prop({disabled: true});
	}

	$('#preferred_type').change(function () {

		if ($(this).val() == 'PERCENT')
		{
			$('#preferred_flat_div').hide();
			$('#preferred_percent_div').show();
			$("#preferred_cap_type option[value*='SERVINGS']").showFlex();
			$('#include_sides').prop({disabled: false});
		}
		else
		{
			$('#preferred_flat_div').show();
			$('#preferred_percent_div').hide();
			$("#preferred_cap_type option[value*='SERVINGS']").hideFlex();
			$('#include_sides').prop({disabled: true});
		}

	});

	$('#preferred_cap_type').change(function () {

		if ($(this).val() == 'NONE')
		{
			$('#preferred_cap_items_div').hide();
			$('#preferred_cap_servings_div').hide();
			$('#preferred_cap_none_div').show();
		}
		else if ($(this).val() == 'SERVINGS')
		{
			$('#preferred_cap_items_div').hide();
			$('#preferred_cap_servings_div').show();
			$('#preferred_cap_none_div').hide();
		}
		else if ($(this).val() == 'ITEMS')
		{
			$('#preferred_cap_items_div').show();
			$('#preferred_cap_servings_div').hide();
			$('#preferred_cap_none_div').hide();
		}

	});

	if ($("#store").val() == 'all')
	{
		$("#submit_preferred").attr("disabled", "disabled");
	}
	else
	{
		$("#submit_preferred").attr("disabled", false);
	}

	$('#store').on('change', function () {

		if ($(this).val() == 'all')
		{
			$("#submit_preferred").attr("disabled", "disabled");
		}
		else
		{
			$("#submit_preferred").attr("disabled", false);
		}

		$('#store_form').submit();

	});

});

function submitDeleteRequest(upid)
{
	$("#upid").val(upid);

	$("#paction").val('delete');
	$("#preferred").submit();

}

$(document).ready(function () {
	$('#preferred_type').trigger('change');
});