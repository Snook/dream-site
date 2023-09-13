
function onSessionClick(session_id, date, time)
{
	$("#SALES_ADJUSTMENTS").attr('disabled', false);
	$("#FUNDRAISER_DOLLARS").attr('disabled', false);
	$("#adjustment_note").attr('disabled', false);
	$("#fundraising_note").attr('disabled', false);
	$('[data-section="for_HO"]').fadeTo('slow', 1.0);

	$("#LABOR").val("");
	$("#SYSCO").val("");
	$("#labor_note").val("");
	$("#food_note").val("");

	$("#cost_inputter_save").attr('disabled', false);
	$("#cost_inputter_status_msg").html("");
	$("#cost_inputter_status_msg").hide();

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data : {
			processor: 'admin_expensesData',
			store_id: store_id,
			date: date,
			session_id: session_id,
			op: 'load_adjustment'
		},
		success: function(json)
		{
			if(json.processor_success)
			{
				$("#selected_day").html(json.formattedDate + " session at " + time);
				$("#selected_id").html(date);
				$('#selected_session_id').html(session_id);

				$("#SALES_ADJUSTMENTS").val("");
				$("#FUNDRAISER_DOLLARS").val("");
				$("#adjustment_note").val("");
				$("#fundraising_note").val("");

				$("#SYSCO").attr('disabled', true);
				$("#LABOR").attr('disabled', true);
				$("#food_note").attr('disabled', true);
				$("#labor_note").attr('disabled', true);
				$('[data-section="for_store"]').fadeTo('slow', 0.5);


				for (var i in json.entries)
				{
					var thisItem = json.entries[i];
					var typeName = thisItem.type;
					$("#" + typeName).val(thisItem.amount);

					if (typeName == 'SALES_ADJUSTMENTS')
					{
						$("#adjustment_note").val(thisItem.notes);
					}
					else if (typeName == 'FUNDRAISER_DOLLARS')
					{
						$("#fundraising_note").val(thisItem.notes);
					}
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
			response = 'Unexpected error';
		}

	});

}

function initExpensesDialog()
{

	$("#cost_inputter_close").on('click', function (){
		$('#cost_inputter').remove();
		return false;
	});


	$('[data-section="for_store"]').fadeTo('slow', 0.5);
	$('[data-section="for_HO"]').fadeTo('slow', 0.5);

	$("#SYSCO").attr('disabled', true);
	$("#LABOR").attr('disabled', true);
	$("#food_note").attr('disabled', true);
	$("#labor_note").attr('disabled', true);

	$("#SALES_ADJUSTMENTS").attr('disabled', true);
	$("#FUNDRAISER_DOLLARS").attr('disabled', true);
	$("#adjustment_note").attr('disabled', true);
	$("#fundraising_note").attr('disabled', true);


	$("#cost_inputter_save").off('click').on('click', function ()
	{
		var updateAdjustments = false;

		if (isHomeOfficeAccess)
		{
			if ($("#SYSCO").attr('disabled'))
				updateAdjustments = true;
		}

		if (updateAdjustments)
		{
			var adjustment = $("#SALES_ADJUSTMENTS").val();
			var fundraising = $("#FUNDRAISER_DOLLARS").val();

			var adjustment_notation = $("#adjustment_note").val();
			var fundraising_notation = $("#fundraising_note").val();

			var date_id = $("#selected_id").html();
			var session_id = $('#selected_session_id').html();


			// update database
			$.ajax({
				url: '/processor',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data : {
					processor: 'admin_expensesData',
					store_id: store_id,
					date: date_id,
					op: 'store_adjustment',
					fundraising: fundraising,
					sales_adjustment: adjustment,
					session_id: session_id,
					fundraising_note: fundraising_notation,
					sales_adjustment_note: adjustment_notation
				},
				success: function(json)
				{
					if(json.processor_success)
					{
						// update Calendar
						var TeeDee = document.getElementById(json.date);

						if (TeeDee)
						{
							$(TeeDee).each(function() {

								$(TeeDee).children('.itemRow').each(function() {

									$(this).children('a').each(function() {

										var thisItemSessionId = $(this).attr('data-session_id');

										if (thisItemSessionId == json.session_id)
										{

											$(this).find('img').remove();

											for (var i in json.entries)
											{
												var thisItem = json.entries[i];
												var typeName = thisItem.type;


												var toolTip = "";


												if (typeName == 'SALES_ADJUSTMENTS')
												{
													if (thisItem.notes == 'null') thisItem.notes = '';
													toolTip = 'Sales Adjustment: $' + thisItem.amount + " - " + thisItem.notes;

								 					var htmlStr =  "<img data-tooltip=\"" + toolTip + "\" src=\"" + PATH.image + "/admin/icon/dollar_sign.png\"  width=\"12\" height=\"12\" class=\"img_valign\" />";
													$(this).append(htmlStr);
												}
												else if (typeName == 'FUNDRAISER_DOLLARS')
												{
													if (thisItem.notes == 'null') thisItem.notes = '';
													toolTip = 'Fundraiser Dollars: $' + thisItem.amount + " - " + thisItem.notes;

								 					var htmlStr =  "<img data-tooltip=\"" + toolTip + "\" src=\"" + PATH.image + "/admin/icon/star_gold.png\"  width=\"12\" height=\"12\" class=\"img_valign\" />";
													$(this).append(htmlStr);
												}
											}
										}
									});
								});
							});
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
					response = 'Unexpected error';
				}

			});
		}
		else
		{

			var food_cost = $("#SYSCO").val();
			var labor_cost = $("#LABOR").val();

			var food_notation = $("#food_note").val();
			var labor_notation = $("#labor_note").val();

			var date_id = $("#selected_id").html();

			// update database
			$.ajax({
				url: '/processor',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data : {
					processor: 'admin_expensesData',
					store_id: store_id,
					date: date_id,
					op: 'store',
					labor: labor_cost,
					food: food_cost,
					labor_note: labor_notation,
					food_note: food_notation
				},
				success: function(json)
				{
					if(json.processor_success)
					{
						// update Calendar

						if (isHomeOfficeAccess)
						{
							var TeeDee = document.getElementById(json.date);

							if (TeeDee)
							{
								$(TeeDee).each(function() {
									$(this).children('[data-header]').children('img').remove();
								});

								var htmlStr = "";

								for (var i in json.entries)
								{
									var thisItem = json.entries[i];
									var typeName = thisItem.type;

									var toolTip = "";

									if (typeName == 'SYSCO')
									{
										if (thisItem.notes == 'null') thisItem.notes = '';
										toolTip = 'Food and Packaging Costs: $' + thisItem.amount + " - " + thisItem.notes;

					 					htmlStr +=  "<img data-tooltip=\"" + toolTip + "\" src=\"" + PATH.image + "/admin/icon/turkey.png\" class=\"img_valign\" />";
									}
									else if (typeName == 'LABOR')
									{
										if (thisItem.notes == 'null') thisItem.notes = '';
										toolTip = 'Labor Costs: $' + thisItem.amount + " - " + thisItem.notes;

					 					htmlStr +=  "<img data-tooltip=\"" + toolTip + "\" src=\"" + PATH.image + "/admin/icon/user.png\" class=\"img_valign\" />";
									}
								}

								$(TeeDee).each(function() {
									$(this).children('[data-header]').append(htmlStr);
								});
							}
						}
						else
						{
							var TeeDee = document.getElementById(json.date);

							if (TeeDee)
							{
								$(TeeDee).each(function() {
									$(this).children('.itemRow').remove();
								});

								for (var i in json.entries)
								{
									var thisItem = json.entries[i];
									var typeName = thisItem.type;
									var amount = thisItem.amount;

									var toolTip = "";

									if (typeName == 'SYSCO')
									{
										if (thisItem.notes == 'null') thisItem.notes = '';
										toolTip = 'Food and Packaging Costs: $' + thisItem.amount + " - " + thisItem.notes;

					 					var htmlStr =  "<div class=\"itemRow\" ><img data-tooltip=\"" + toolTip + "\" src=\"" + PATH.image + "/admin/icon/turkey.png\" class=\"img_valign\">$" + amount + "</div>";
										$(TeeDee).append(htmlStr);
									}
									else if (typeName == 'LABOR')
									{
										if (thisItem.notes == 'null') thisItem.notes = '';
										toolTip = 'Labor Costs: $' + thisItem.amount + " - " + thisItem.notes;

					 					var htmlStr =  "<div class=\"itemRow\" ><img data-tooltip=\"" + toolTip + "\" src=\"" + PATH.image + "/admin/icon/user.png\" class=\"img_valign\">$" + amount + "</div>";
										$(TeeDee).append(htmlStr);
									}
								}

							}
						}

						//update SSAGT report
						updateGOGSfields(json.month, json.year);
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
		}

		return false;
	});

}

function SE_dayClickHandler(element)
{
	$("#SYSCO").attr('disabled', false);
	$("#LABOR").attr('disabled', false);
	$("#food_note").attr('disabled', false);
	$("#labor_note").attr('disabled', false);
	$('[data-section="for_store"]').fadeTo('slow', 1.0);

	$("#cost_inputter_save").attr('disabled', false);
	$("#cost_inputter_status_msg").html("");
	$("#cost_inputter_status_msg").hide();

	if (isHomeOfficeAccess)
	{
		$("#SALES_ADJUSTMENTS").attr('disabled', true);
		$("#FUNDRAISER_DOLLARS").attr('disabled', true);
		$("#adjustment_note").attr('disabled', true);
		$("#fundraising_note").attr('disabled', true);
		$('[data-section="for_HO"]').fadeTo('slow', .5);


		$("#SALES_ADJUSTMENTS").val('');
		$("#FUNDRAISER_DOLLARS").val('');
		$("#adjustment_note").val('');
		$("#fundraising_note").val('');

	}

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data : {
			processor: 'admin_expensesData',
			store_id: store_id,
			date: element.id,
			op: 'load'
		},
		success: function(json)
		{
			if(json.processor_success)
			{
				$("#selected_day").html(json.formattedDate);
				$("#selected_id").html(element.id);


				$("#LABOR").val("");
				$("#SYSCO").val("");
				$("#labor_note").val("");
				$("#food_note").val("");

				for (var i in json.entries)
				{
					var thisItem = json.entries[i];
					var typeName = thisItem.type;
					$("#" + typeName).val(thisItem.amount);

					if (typeName == 'SYSCO')
					{
						$("#food_note").val(thisItem.notes);
					}
					else if (typeName == 'LABOR')
					{
						$("#labor_note").val(thisItem.notes);
					}
				}


				if (!json.can_edit)
				{
					$("#cost_inputter_status_msg").html("The selected date cannot be edited because the month is locked down.");

					$("#cost_inputter_status_msg").show();


					$("#SYSCO").attr('disabled', true);
					$("#LABOR").attr('disabled', true);
					$("#food_note").attr('disabled', true);
					$("#labor_note").attr('disabled', true);
					$("#cost_inputter_save").attr('disabled', true);
					$('[data-section="for_store"]').fadeTo('slow', 0.5);
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
			response = 'Unexpected error';
		}

	});

}