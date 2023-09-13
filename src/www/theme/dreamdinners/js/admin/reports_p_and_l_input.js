var hasChangedCOGS = false;

function reports_p_and_l_init()
{

	$('html').bind('keypress', function(e)
	{
	   if(e.keyCode == 13)
	   {
	      return false;
	   }
	});


	init_money_formatting();

	init_update_p_and_l_data();


	$(".gt_input").each(function() {
		$(this).val(addFormatting($(this).val()));
	});


	window.onbeforeunload = function (event) {
		if (detectChanges())
		{
		    var message = 'Important: Please click on \'Save\' button to leave this page.';
		    if (typeof event == 'undefined') {
		        event = window.event;
		    }
		    if (event) {
		        event.returnValue = message;
		    }
		    return message;
		}
	};


}

function init_money_formatting()
{
	$(".gt_input").focus(function() {
		$(this).val(removeFormatting($(this).val()));
	});


	$(".gt_input").blur(function() {
		$(this).val(addFormatting($(this).val()));
	});


	$(".gt_input").keypress(function(e) {
		if (e.which == 188 ||  // comma
			e.which == 190 ||  //  ...? ...
			e.which  == 46 || // decimal
			e.which == 45  || // negative sign for FireFox
			e.keyCode == 8 ||  // delete
			e.keyCode == 9 ||  // tab
			e.keyCode == 37 || //left arrow
			e.keyCode == 39 || // right arrow
			e.keyCode == 45 || // negative sign
			(e.which > 47 && e.which < 58 )) // numbers
		{
				return true;
		}
		else if (e.keyCode == 13)
		{
			$(this).blur();
			return true;
		}
		else
			return false;
	});

	$(".gt_input").change(function() {
			if (isNaN($(this).val()))
				return false;
	});


}

function detectChanges()
{
	var foundChange = false;

	$("#finance_div input").each(function() {

		if (this.id != 'other_expenses')
		{
			var org = $(this).data('org_value');
			var cur = removeFormatting($(this).val());

			if (org * 1 != cur * 1)
			{
				$(this).addClass('unsaved_field');
				foundChange = true;
			}
			else
			{
				$(this).removeClass('unsaved_field');
			}
		}
	});

	return foundChange;

}


function init_update_p_and_l_data()
{

	var total_expenses = Number(0);
	$('[data-dd_subtype="expense"]').each(function() {
		total_expenses += Number(removeFormatting($(this).val()));
	});

	var agr = Number(removeFormatting($("#p_and_l_total_agr").html()));
	var marketing_total = Number(removeFormatting($("#p_and_l_marketing_total").html()));
	var royalty_total = Number(removeFormatting($("#p_and_l_royalty_total").html()));
	var net_income = Number(removeFormatting($("#net_income").val()));
	var salesforce_fee =  Number(removeFormatting($("#p_and_l_salesforce_fee").html()));


	$("#other_expenses").val(addFormatting(agr - (total_expenses + marketing_total + royalty_total + salesforce_fee) - net_income));



	$(".gt_input").keyup(function() {

		var net_income = Number(removeFormatting($("#net_income").val()));

		var total_expenses =  Number(0);
		$('[data-dd_subtype="expense"]').each(function() {
			total_expenses += Number(removeFormatting($(this).val()));
		});

		var agr = Number(removeFormatting($("#p_and_l_total_agr").html()));

		$("#other_expenses").val(addFormatting(agr - (total_expenses + marketing_total + royalty_total + salesforce_fee)- net_income));
	});


	$("#update_p_and_l").click(function()
	{

		var validation_error_occurred = false;

		$("#owner_hours, #employee_hours, #manager_hours").each(function()
		{
			if (isNaN($(this).val()))
			{
				dd_message({
					title: 'Error',
					message: "The hours fields must be a number."
				});

				validation_error_occurred = true;

			}
		});

		if (validation_error_occurred)
		{
			return false;
		}

		var dataObj = {};

		$("#finance_div input").each(function() {
			dataObj[this.id] = removeFormatting($(this).val());
		});

		$("#finance_div input").each(function() {
			if (dataObj[this.id] == "") dataObj[this.id] = "not supplied";
		});

		if ($("#cost_of_goods_and_services").data("org_value") != $("#cost_of_goods_and_services").val())
		{
			hasChangedCOGS = true;
		}

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data : {
				processor: 'admin_p_and_l_data',
				store_id: store_id,
				month: month,
				year: year,
				p_and_l_data: dataObj,
			},
			success: function(json)
			{
				if(json.processor_success)
				{
					dd_toast({message: "Your data was saved."});

					if (hasChangedCOGS)
					{
						hasChangedCOGS = false;
						$("#cogs_msg").html("");
					}

					$("#finance_div input").each(function() {

						if (this.id != 'other_expenses')
						{
							var cur = removeFormatting($(this).val());
							$(this).data('org_value', cur);
							$(this).removeClass('unsaved_field');
						}
					});

				}
				else
				{
					dd_message({
						title: 'Error',
						message: json.processor_message
					});

					detectChanges();

				}
			},
			error: function(objAJAXRequest, strError)
			{
				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});
			}

		});

		return false;

	});
}


function roundToTenths(mnt)
{
	mnt -= 0;
	mnt = (Math.round(mnt*10))/10;
	return (mnt == Math.floor(mnt)) ? mnt + '.0'
			 : ( (mnt == Math.floor(mnt)) ?
					 mnt + '0' : mnt);
}

function doRunReport()
{
	var year = parseInt($("#year_field_001").val());

	if (isNaN(year))
	{
		// error
		dd_message({
			title: 'Error',
			message: 'The year is not an integer.'
		});
		return false;
	}
	else if (year < 2011)
	{
		//error
		dd_message({
			title: 'Error',
			message: 'The year must be greater than 2010.'
		});

		return false;
	}

 	 $("#avg_ticket_goal").remove();
	 $("#finishing_touch_goal").remove();
	 $("#taste_sessions_goal").remove();
	 $("#regular_guest_count_goal").remove();
	 $("#taste_guest_count_goal").remove();
	 $("#intro_guest_count_goal").remove();

	 $("[data-dd_type='p&l_widget']").remove();


	$("#p_and_l_form").submit();

}

function _report_submitClick(form)
{
	var canProceed = true;
	if (detectChanges())
	{
		canProceed = false;

		dd_message({
			title: 'Unsaved Changes',
			message: "You have some unsaved changes. Click 'Cancel' to return and save your changes.",
			cancel: function ()
			{
			},
			confirm: function ()
			{
				return doRunReport();
			}
		});
	}

	if (canProceed)
	{
		return doRunReport();
	}
}

function removeFormatting(stringVal)
{

	if (stringVal == null || stringVal == "") return stringVal;

	stringVal = stringVal.replace(/,/, "");
	stringVal = stringVal.replace(/\$/, "");

	return stringVal;
}


function addFormatting(numericVal)
{
	if (numericVal == null || numericVal == "") return "";

	numericVal = accounting.formatMoney(numericVal,"$",2,",",".","%s%v");

	return numericVal;
}