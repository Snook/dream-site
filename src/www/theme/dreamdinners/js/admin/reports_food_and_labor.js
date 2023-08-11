var da_open_state_cookie_name = "";
var da_select_state_cookie_name = "";

function reports_food_and_labor_init(emptyset, pickdate)
{
	if (!emptyset)
	{
		$("#corp_store").hide();
	}
	else
	{
		$("input[name=pickDate][value=" + pickdate + "]").attr('checked', 'checked');
	}

	handleReportTypeSelect();
}

function handleReportTypeSelect()
{
	$("#report_type").on('change', function (){

		if ($(this).val() == '1')
		{
			$("#corp_store").show();
		}
		else
		{
			$("#corp_store").hide();
		}
	});
}

