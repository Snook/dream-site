var hasChangedCOGS = false;

function reports_goal_management_init()
{

	$('html').bind('keypress', function(e)
	{
	   if(e.keyCode == 13)
	   {
	      return false;
	   }
	});

	init_exposure_controls();

	init_session_lead_dropdowns();

	init_goal_inputs();

	init_food_cost_inputs();
	init_labor_cost_inputs();

	init_money_formatting();

	init_update_p_and_l_data();

	calculatePage();


	$(".gt_input").each(function() {
		$(this).val(addFormatting($(this).val()));
	});

    $("#session_summary_table").stickyTableHeaders();
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

	$("#other_expenses").val(addFormatting(agr - (total_expenses + marketing_total + royalty_total) - net_income));



	$(".gt_input").keyup(function() {

		var net_income = Number(removeFormatting($("#net_income").val()));

		var total_expenses =  Number(0);
		$('[data-dd_subtype="expense"]').each(function() {
			total_expenses += Number(removeFormatting($(this).val()));
		});

		var agr = Number(removeFormatting($("#p_and_l_total_agr").html()));

		$("#other_expenses").val(addFormatting(agr - (total_expenses + marketing_total + royalty_total)- net_income));
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

		$("#finance_tab input").each(function() {
			dataObj[this.id] = removeFormatting($(this).val());
		});

		$("#finance_tab input").each(function() {
			if (dataObj[this.id] == "") dataObj[this.id] = "not supplied";
		});

		if ($("#cost_of_goods_and_services").data("org_value") != $("#cost_of_goods_and_services").val())
		{
			hasChangedCOGS = true;
		}

		$.ajax({
			url: 'ddproc.php',
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

				}
				else
				{
					dd_message({
						title: 'Error',
						message: json.processor_message
					});

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


function init_goal_inputs()
{
	$("#avg_ticket_goal,#finishing_touch_goal,#taste_sessions_goal,#regular_guest_count_goal,#taste_guest_count_goal,#intro_guest_count_goal").change(function()
	{

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data : {
				processor: 'admin_sessionGoals',
				store_id: store_id,
				month: month,
				year: year,
				goal: this.id,
				value: removeFormatting($(this).val())
			},
			success: function(json)
			{
				if(json.processor_success)
				{

				}
				else
				{
					dd_message({
						title: 'Error',
						message: json.processor_message
					});

				}
			},
			error: function(objAJAXRequest, strError)
			{
				response = 'Unexpected error';
			}

		});

	});
}

function updateGOGSfields(month, year)
{

	var dateStr = month + "-1-" + year;

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data : {
			processor: 'admin_expensesData',
			store_id: store_id,
			date: dateStr,
			op: 'retrieveForMonth'
		},
		success: function(json)
		{
			// set all to zero so deleted entries aren't left at the original value
			$("[id^='fc_']").val(addFormatting("0.00"));
			 $("[id^='lc_']").val(addFormatting("0.00"));

			if (json.processor_success)
			{
				for (var weekNum in json.food_costs)
				{

					 var thisCost = json.food_costs[weekNum];

					if (weekNum.length == 1)
						weekNum = "0" + weekNum;

					if (weekNum == "00")
						weekNum = '53';

					 $("#fc_" + weekNum + "_" + year).val(addFormatting(thisCost));
				}

				for (var weekNum in json.labor_costs)
				{
					 var thisCost = json.labor_costs[weekNum];

						if (weekNum.length == 1)
							weekNum = "0" + weekNum;

						if (weekNum == "00")
							weekNum = '53';


					 $("#lc_" + weekNum + "_" + year).val(addFormatting(thisCost));


				}

				calculatePage();

			}
			else
			{
				dd_message({
					title: 'Error',
					message: json.processor_message
				});
			}
		},
		error: function(objAJAXRequest, strError)
		{
			dd_message({
				title: 'Error',
				message: json.strError
			});
		}
	});
}


function init_food_cost_inputs()
{


	$("[id^=fc_]").on('click', function ()
	{

		$.ajax({
			url: 'main.php?page=admin_store_expenses_v2',
			type: 'POST',
			once: false,
			data : {
				store_id: store_id,
				month: month,
				year: year
			},
			success: function(data, status)
			{

				dd_message({
					title: 'Input Expenses',
					message: data,
					height: 'auto',
					width: 740,
					div_id: 'cost_inputter',
					closeOnEscape: true,
					noOk: true
				});


				dayClickHandler = SE_dayClickHandler;



				initExpensesDialog();


			},
			error: function(objAJAXRequest, strError)
			{
				response = 'Unexpected error';
			}

		});

	});


}

function init_labor_cost_inputs()
{

	$("[id^=lc_]").on('click', function ()
	{
		$.ajax({
			url: 'main.php?page=admin_store_expenses_v2',
			type: 'POST',
			once: false,
			data : {
				store_id: store_id,
				month: month,
				year: year
			},
			success: function(data, status)
			{

				dd_message({
					title: 'Input Expenses',
					message: data,
					height: 'auto',
					width: 740,
					div_id: 'cost_inputter',
					closeOnEscape: true,
					noOk: true
				});

				dayClickHandler = SE_dayClickHandler;
				initExpensesDialog();

			},
			error: function(objAJAXRequest, strError)
			{
				response = 'Unexpected error';
			}

		});

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


function _report_submitClick(form)
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


	return true;

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


function calculatePage()
{

	//var revenue_goal = removeFormatting($("#gross_revenue_goal").val());
	// var org_revenue_goal = revenue_goal;

	var total_sessions = $("#nonTasteSessionCount").val();
	var average_ticket = removeFormatting($("#avg_ticket_goal").val());
	var ft_goal = removeFormatting($("#finishing_touch_goal").val());

	var regular_guest_count_goal = removeFormatting($("#regular_guest_count_goal").val());
	var taste_guest_count_goal = removeFormatting($("#taste_guest_count_goal").val());
	var intro_guest_count_goal = removeFormatting($("#intro_guest_count_goal").val());

	var standard_session_guest_goal = (regular_guest_count_goal * 1) + (intro_guest_count_goal * 1);

	var revenue_goal = (regular_guest_count_goal * average_ticket) + (taste_guest_count_goal * 34.99) + (intro_guest_count_goal * 84.95);
	var org_revenue_goal = revenue_goal;

	$("#gross_revenue_goal").html(addFormatting(revenue_goal));

	$("#calendar_month_goal").html(addFormatting(revenue_goal));
	$("#FT_months_total_goal").html(addFormatting(ft_goal));

	var dateObj = new Date();

	var monthisCurrent = $("#isCurrentMonth").val() == "true";

	//dd_console_log("Date: " + dateObj.getDate());

	var todaysDayNumber = dateObj.getDate();

	var lastGoalAmount = 0;
	var lastFTGoalAmount = 0;
	var todate_SessionCount = 0;
	var todate_GrossRevenue = 0;
	var todate_GuestCount = 0;
	var todate_FT_Total = 0;
	var updatedSessionGoal = false;
	var updatedSessionFTGoal = false;
	var remaining_session_count = 0;
	var totalMonthGrossRevenue = 0;
	var totalMonthFTRevenue = 0;
	var totalMonthGrossRevenueMinusTaste = 0;
	var encounteredFirstFutureSession = false;
	//	var totalMonthFTRevenue = 0; ?
	var totalCurrentGuests = $('#guest_count_months_total').html();

	/*
	if (average_ticket > 0)
		$("#guests_goal").html(Math.round(revenue_goal / average_ticket));
	else
		$("#guests_goal").html("0");
	*/

	$("#sg_gross_revenue_goal").html(addFormatting(revenue_goal / total_sessions));
	$("#sg_avg_ticket_goal").html(addFormatting($("#avg_ticket_goal").val()));
	$('#sg_guests_goal').html(roundToTenths($("#guests_goal").html() / total_sessions));
	$('#sg_finishing_touch_goal').html(addFormatting(ft_goal / total_sessions));
	var remainingFTGoal = ft_goal;
	var per_customer_goal = 0;


	$("[id^=dre_]").each(function()
	{

		if ($(this).attr('data-in_month') == 'true')
		{
			var this_sessions_goal = 0;
			var this_sessions_ft_goal  = 0;
			var session_id = this.id.substr(4);
		//	var ordPos = new Number($(this).attr('data-ord_pos'));
			var dayNumber = $(this).attr('data-day_number');

			var thisSessionActual = removeFormatting($("#dac_" + session_id).html());
			var thisSessionActualFT = removeFormatting($("#ftt_" + session_id).html());

			var isTaste = ($(this).attr('data-is_taste') == 'true');

			totalMonthGrossRevenue += (thisSessionActual * 1);
			totalMonthFTRevenue += (thisSessionActualFT * 1);

			if (!isTaste)
				totalMonthGrossRevenueMinusTaste += (thisSessionActual * 1);


			if ((monthisCurrent && dayNumber > todaysDayNumber) || month_is_future)
			{
				dd_console_log("Pos: " + session_id + " | toDataGuestCount: " +  todate_GuestCount);

				if (!isTaste)
				{

					if (!encounteredFirstFutureSession)
					{
						this_sessions_goal = revenue_goal / total_sessions;
						lastGoalAmount = this_sessions_goal;

						//this_sessions_ft_goal = remainingFTGoal / total_sessions;
						//lastFTGoalAmount = this_sessions_ft_goal;

						per_customer_goal = remainingFTGoal / (standard_session_guest_goal - todate_GuestCount)

					}

					encounteredFirstFutureSession = true;


					var thisFutureSessionTotalGuestCount = $("#sgc_" + session_id).html();


					this_sessions_ft_goal = formatAsMoney(per_customer_goal * thisFutureSessionTotalGuestCount)
					lastFTGoalAmount = this_sessions_ft_goal

					$(this).html(addFormatting(lastGoalAmount));

					$("#fttg_" + session_id).html(addFormatting(lastFTGoalAmount));

					if (thisSessionActual <  lastGoalAmount)
					{
						$("#dac_" + session_id).addClass("goal_not_met");
						$("#dac_" + session_id).removeClass("goal_met");
					}
					else
					{
						$("#dac_" + session_id).addClass("goal_met");
						$("#dac_" + session_id).removeClass("goal_not_met");
					}

					if (thisSessionActualFT <  lastFTGoalAmount)
					{
						$("#fft_" + session_id).addClass("goal_not_met");
						$("#ftt_" + session_id).removeClass("goal_met");
					}
					else
					{
						$("#ftt_" + session_id).addClass("goal_met");
						$("#ftt_" + session_id).removeClass("goal_not_met");
					}

					updatedSessionFTGoal = lastFTGoalAmount;
					updatedSessionGoal = lastGoalAmount;

					remaining_session_count++;

				}

			}
			else
			{


				//dd_console_log("Pos: " + session_id + "; sessionActual: " +  thisSessionActual + "; Remaining: " + revenue_goal + ";");

				if (!isTaste)
				{
					this_sessions_goal = revenue_goal / total_sessions;
					$(this).html(addFormatting(this_sessions_goal));

					this_sessions_ft_goal = remainingFTGoal / total_sessions;
					$("#fttg_" + session_id).html(addFormatting(this_sessions_ft_goal));

					//dd_console_log("Pos: " + session_id + "; this_sessions_goal: " +  this_sessions_goal + "; sessions_remaining: " + total_sessions + ";");

					lastGoalAmount = this_sessions_goal;
					lastFTGoalAmount = this_sessions_ft_goal;

					if (thisSessionActual <  this_sessions_goal)
					{
						$("#dac_" + session_id).addClass("goal_not_met");
						$("#dac_" + session_id).removeClass("goal_met");
					}
					else
					{
						$("#dac_" + session_id).addClass("goal_met");
						$("#dac_" + session_id).removeClass("goal_not_met");
					}

					if (thisSessionActualFT <  this_sessions_ft_goal)
					{
						$("#ftt_" + session_id).addClass("goal_not_met");
						$("#ftt_" + session_id).removeClass("goal_met");
					}
					else
					{
						$("#ftt_" + session_id).addClass("goal_met");
						$("#ftt_" + session_id).removeClass("goal_not_met");
					}

					todate_SessionCount++;
					total_sessions--;

					var thisSessionTotalGuestCount = $("#sgc_" + session_id).html();
					todate_GuestCount += (thisSessionTotalGuestCount * 1);

				}

				todate_GrossRevenue += (thisSessionActual * 1);

				//var cur = $("#ses_" + session_id).html();
				//$("#ses_" + session_id).html(cur + "<br />(" + todate_GrossRevenue + "; " + total_sessions + ")");

				revenue_goal -= thisSessionActual;
				var thisSessionFTTotal = removeFormatting($("#ftt_" + session_id).html());

				todate_FT_Total += (thisSessionFTTotal * 1);

			}
			remainingFTGoal = ft_goal - totalMonthFTRevenue;
		}
	});



	$('#aam_gross_revenue_goal').html(addFormatting(todate_GrossRevenue / todate_SessionCount));
	$('#aam_guests_goal').html(formatAsMoney(todate_GuestCount / todate_SessionCount));
	$('#aam_finishing_touch_goal').html(addFormatting(todate_FT_Total / todate_SessionCount));
	$('#aam_avg_ticket_goal').html(addFormatting(removeFormatting($('#aam_gross_revenue_goal').html()) / $('#aam_guests_goal').html()));

	$('#rgrs_gross_revenue_goal').html(addFormatting(updatedSessionGoal));


	if (remaining_session_count  > 0)
		$('#rgrs_guests_goal').html(roundToTenths((standard_session_guest_goal - todate_GuestCount) / remaining_session_count));
	else
		$('#rgrs_guests_goal').html("0");

	$('#rgrs_avg_ticket_goal').html(addFormatting(removeFormatting($('#rgrs_gross_revenue_goal').html()) / $('#rgrs_guests_goal').html()));


	var FT_months_total = removeFormatting($("#FT_months_total").html());

	if (remaining_session_count  > 0)
		$('#rgrs_finishing_touch_goal').html(addFormatting((ft_goal - FT_months_total) / remaining_session_count));
	else
		$('#rgrs_finishing_touch_goal').html("$0.00");

	$('#ma_gross_revenue_goal').html(addFormatting(totalMonthGrossRevenue ));

	var ma_guest_count_months_total = $('#guest_count_months_total').html();
	$('#ma_guests_goal').html(ma_guest_count_months_total);

	$('#ma_avg_ticket_goal').html(addFormatting(removeFormatting($('#ma_gross_revenue_goal').html()) / $('#ma_guests_goal').html()));

	$('#ma_finishing_touch_goal').html(addFormatting(FT_months_total));



	$('#mat_gross_revenue_goal').html(addFormatting(totalMonthGrossRevenueMinusTaste ));

	var guestCountTotalMinusTaste = $('#guestCountTotalMinusTaste').val();

	$('#mat_guests_goal').html(guestCountTotalMinusTaste);

	$('#mat_avg_ticket_goal').html(addFormatting(totalMonthGrossRevenueMinusTaste / guestCountTotalMinusTaste));

	$('#mat_finishing_touch_goal').html(addFormatting($('#allFTTotalMinusTaste').val()));




	var monthly_food_costs = 0;

	$("[id^=fc_]").each(function()
	{
		var params = this.id.split("_");

		var percentage_name = "fcp_" + params[1] + "_" + params[2];
		var total_name = "wkt_" + params[1];

		var foodCost = removeFormatting($(this).val());
		var total = removeFormatting($("#" + total_name).html());

		monthly_food_costs += (foodCost * 1);

		var percent = (foodCost * 100) / total;

		$("#" + percentage_name).html(formatAsMoney(percent));


	});


	$("#total_food_cost").html(addFormatting(monthly_food_costs));

	$("#total_food_cost_percentage").html(formatAsMoney(monthly_food_costs * 100 / totalMonthGrossRevenue) + "%");


	var monthly_labor_costs = 0;

	$("[id^=lc_]").each(function()
	{
		var params = this.id.split("_");

		var percentage_name = "lcp_" + params[1] + "_" + params[2];
		var total_name = "wkt_" + params[1];

		var laborCost = removeFormatting($(this).val());
		var total = removeFormatting($("#" + total_name).html());

		monthly_labor_costs += (laborCost * 1);

		var percent = (laborCost * 100) / total;

		$("#" + percentage_name).html(formatAsMoney(percent));


	});


	$("#total_labor_cost").html(addFormatting(monthly_labor_costs));

	$("#total_labor_cost_percentage").html(formatAsMoney(monthly_labor_costs * 100 / totalMonthGrossRevenue) + "%");



	// adjust revised goals if month is in past
	// if goal is met insert 0
	// if not insert the difference
	if (month_is_passed)
	{
		if (totalMonthGrossRevenue >= org_revenue_goal)
			$('#rgrs_gross_revenue_goal').html(addFormatting(0));
		else
			$('#rgrs_gross_revenue_goal').html(addFormatting(totalMonthGrossRevenue - org_revenue_goal));


		var actual_avg_ticket = removeFormatting($('#ma_avg_ticket_goal').html());

		if (actual_avg_ticket >= average_ticket)
			$('#rgrs_avg_ticket_goal').html(addFormatting(0));
		else
			$('#rgrs_avg_ticket_goal').html(addFormatting(actual_avg_ticket - average_ticket));


		if (FT_months_total >= ft_goal)
			$('#rgrs_finishing_touch_goal').html(addFormatting(0));
		else
			$('#rgrs_finishing_touch_goal').html(addFormatting(FT_months_total - ft_goal));


		var guestsGoal = $("#guests_goal").html();

		if (ma_guest_count_months_total >= guestsGoal)
			$('#rgrs_guests_goal').html(formatAsMoney(0));
		else
			$('#rgrs_guests_goal').html(formatAsMoney(ma_guest_count_months_total - guestsGoal));

	}

	// take a pass through and fill in daily and weekly goal totals
	var lastDay = -1;
	var lastWeek = -1;

	var dailyTotal = 0;
	var weeklyTotal = 0;

	var dailyFTTotal = 0;
	var weeklyFTTotal = 0;

	var curCalWeekTotal = 0;
	var curCalWeekFTTotal = 0;

	$("[id^=dre_]").each(function()
	{


		var thisDay = $(this).attr('data-day_number');
		var thisWeek = $(this).attr('data-week_number');

		if (lastDay != -1 && thisDay != lastDay)
		{
			// new day so total last day
			$("#dce_" + lastDay + "-" +  lastWeek).html(addFormatting(dailyTotal));
			$("#ftdtg_" + lastDay + "-" +  lastWeek).html(addFormatting(dailyFTTotal));

			dailyTotal = 0;
			dailyFTTotal = 0;
		}

		if (lastWeek!= -1 && thisWeek != lastWeek)
		{


			if (hasPreviousMonthSessions && lastWeek == hasPreviousMonthSessions)
			{
				$("#wcec_" +  lastWeek).html(addFormatting(curCalWeekTotal));
				$("#ftwtgc_" +  lastWeek).html(addFormatting(curCalWeekFTTotal));

				$("#wce_" +  lastWeek).html(addFormatting(weeklyTotal));
				$("#ftwtg_" +  lastWeek).html(addFormatting(weeklyFTTotal));

			}
			else if (hasFutureMonthSessions && lastWeek == hasFutureMonthSessions)
			{
				$("#wcec_" +  lastWeek).html(addFormatting(curCalWeekTotal));
				$("#ftwtgc_" +  lastWeek).html(addFormatting(curCalWeekFTTotal));

				$("#wce_" +  lastWeek).html(addFormatting(weeklyTotal));
				$("#ftwtg_" +  lastWeek).html(addFormatting(weeklyFTTotal));
			}
			else
			{
				// new week so total last week
				$("#wcec_" +  lastWeek).html(addFormatting(weeklyTotal));
				$("#ftwtgc_" +  lastWeek).html(addFormatting(weeklyFTTotal));
			}

			weeklyTotal = 0;
			weeklyFTTotal = 0;
			curCalWeekTotal = 0;
			curCalWeekFTTotal = 0;
		}

		var session_id = this.id.substr(4);


		if ($(this).attr('data-in_month') == 'true')
		{

			var temp = removeFormatting($(this).html());
			curCalWeekTotal += (temp * 1);

			var tempFT = removeFormatting($('#fttg_' + session_id).html());
			curCalWeekFTTotal += (tempFT * 1);

		}


		lastDay = thisDay;
		lastWeek = thisWeek;

		var amount = removeFormatting($(this).html());
		var amountFT = removeFormatting($('#fttg_' + session_id).html());

		dailyTotal += (amount * 1);
		weeklyTotal += (amount * 1);

		dailyFTTotal += (amountFT * 1);
		weeklyFTTotal += (amountFT * 1);



	});

	$("#dce_" + lastDay + "-" +  lastWeek).html(addFormatting(dailyTotal));
	$("#ftdtg_" + lastDay + "-" +  lastWeek).html(addFormatting(dailyFTTotal));

	$("#wcec_" +  lastWeek).html(addFormatting(weeklyTotal));
	$("#ftwtgc_" +  lastWeek).html(addFormatting(weeklyFTTotal));

	if (hasPreviousMonthSessions && parseInt(lastWeek) == hasPreviousMonthSessions)
	{
		$("#wce_" +  lastWeek).html(addFormatting(curCalWeekTotal));
		$("#ftwtg_" +  lastWeek).html(addFormatting(curCalWeekFTTotal));
	}

	if (hasFutureMonthSessions && parseInt(lastWeek) == hasFutureMonthSessions)
	{
		$("#wce_" +  lastWeek).html(addFormatting(curCalWeekTotal));
		$("#ftwtg_" +  lastWeek).html(addFormatting(curCalWeekFTTotal));
	}

}

function init_session_lead_dropdowns()
{
	$("[id^=sl_]").change(function()
	{
		var session_id = this.id.substr(3);

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data : {
				processor: 'admin_sessionLead',
				user_id: $(this).val(),
				session_id: session_id
			},
			success: function(json)
			{
				if(json.processor_success)
				{
					/*
					dd_message({
						title: 'Yas',
						message: json.processor_message
					}); */

				}
				else
				{
					dd_message({
						title: 'Error',
						message: json.processor_message
					});

				}
			},
			error: function(objAJAXRequest, strError)
			{
				response = 'Unexpected error';
			}

		});

	});
}

function init_exposure_controls()
{
	// hide/show week
	$("[id^=wc_]").on('click', function ()
	{
		var week_num = this.id.substr(3);

		var alt_num_display_name = "wcan_" + week_num;

		if ($(this).html() == 'Hide')
		{

			var hideStr = "wid_" + week_num;
			$("[id^=" + hideStr + "]").hide();

			$("#" + alt_num_display_name).show();
			$(this).html('Show');
		}
		else
		{
			var showStr = "wid_" + week_num;
			$("[id^=" + showStr + "]").show();

			$("#" + alt_num_display_name).hide();
			$(this).html('Hide');

			$("[id^=dn_" + week_num +  "]").hide();
			$("[id^=dc_" + week_num +  "]").html('Hide');


		}


	});


	// hide/show day
	$("[id^=dc_]").on('click', function ()
	{

		var theRow = $(this).parent().parent();

		var parts = this.id.split("-");
		var week_num = parts[0].substr(3);
		var day_num = parts[1];

		var alt_week_num_display_name = "wcan_" + week_num;
		var alt_day_num_display_name = "dn_" + week_num + "-" + day_num;


		if ($(this).html() == 'Hide')
		{

			var hideStr = "wid_" + week_num + "-did_" + day_num + "-";
			$("[id^=" + hideStr + "]").hide();
			$(this).html('Show');
			$("#" + alt_day_num_display_name).show();

			if (theRow.attr("data-interweek_day_count") == 1)
			{
				// so we are hiding the first row of the week and need to show the week hider in a new spot
				$("[id^=" + alt_week_num_display_name + "]").show();
			}


		}
		else
		{
			var showStr = "wid_" + week_num + "-did_" + day_num + "-";
			$("[id^=" + showStr + "]").show();
			$(this).html('Hide');
			$("#" + alt_day_num_display_name).hide();

			if (theRow.attr("data-interweek_day_count") == 1)
			{
				// so we are hiding the first row of the week and need to show the week hider in a new spot
				$("[id^=" + alt_week_num_display_name + "]").hide();

			}

		}

	});


}