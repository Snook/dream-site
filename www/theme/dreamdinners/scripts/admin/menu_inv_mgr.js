let sidesDataArray = [];
let notShown = true;

function menu_editor_init()
{
	init_sales_mix_editing();
	init_week_inventory_editing();
	init_override_inventory_editing();
	init_inventory_calculation();
	init_saving_guest_counts();
	init_saving_store_sales_mix();
	init_resetting_store_sales_mix();
	init_finalize_preorder();
	init_save_sides_inventory();
	init_save_EFL_inventory();
	init_saving_override_inventory();
	init_saving_week_inventory();
	init_show_actuals();
	init_export_sales();
	init_reset_to_current();

	reconcile_data();

	calculatePage();

	initNavTab();

	$("#itemsTbl, #ctsItemsTbl").stickyTableHeaders();

	if (!menuState.hasSavedPreOrder)
	{
		$("[id^='finalize_week_']").each(function () {
			$(this).addClass('disabled');
		});
	}

}

function initNavTab()
{

	var oldUrl = $('#inv_mgr').attr("href"); // Get current url
	var newUrl = oldUrl.substring(0, oldUrl.indexOf('&tabs'));
	const params = new Proxy(new URLSearchParams(window.location.search), {
		get: (searchParams, prop) => searchParams.get(prop)
	});
	newUrl = newUrl + '&tabs=' + params.tabs;
	$('#inv_mgr').attr("href", newUrl);

	$('.tab').each(function () {
		$(this).on('click', function () {
			var oldUrl = $('#inv_mgr').attr("href"); // Get current url
			var newUrl = oldUrl.substring(0, oldUrl.indexOf('&tab'));
			newUrl = newUrl + '&tabs=menu.' + $(this).data('tabid');
			$('#inv_mgr').attr("href", newUrl);
		});
	});
}

function init_reset_to_current()
{
	$("#reset_to_current").on('click', function () {
		bootbox.confirm("Are you sure you wish to reset the Inventory Manager to last published values? You will lose any unsaved changes.", function (result) {
			if (result)
			{
				location.reload();
			}
		});
	});

}

function init_export_sales()
{
	$("#export_menu_sales").on('click', function () {

		let hasDirtyFields = false;
		$("[id^='wta']").each(function () {

			if ($(this).hasClass('dirty'))
			{
				hasDirtyFields = true;
			}
		});

		if (!menuState.hasSavedPreOrder || hasDirtyFields)
		{
			let message = "";
			if (!menuState.hasSavedPreOrder)
			{
				message = "The Pre-Order has not been finalized. This export function will export values currently stored in the database.";
			}

			if (hasDirtyFields)
			{
				if (message == "")
				{
					message = "There are unsaved weekly projections. The export will use values currently stored in the database. You may want to save these value first.";
				}
				else
				{
					message += "There are also unsaved weekly projections. You may want to save these value first.";
				}
			}

			message += "<br /><br />Do you want to continue with the export?";
			dd_message({
				title: 'Export PreOrder',
				message: message,
				modal: true,
				width: 400,
				height: 300,
				confirm: function () {
					let url = "main.php?page=admin_menu_inventory_mgr&op=export_month_sales_projection&store=" + gStore_ID + "&menu_id=" + menu_id;
					window.location = url;
				}
			});
		}
		else
		{
			let url = "main.php?page=admin_menu_inventory_mgr&op=export_month_sales_projection&store=" + gStore_ID + "&menu_id=" + menu_id;
			window.location = url;
		}
	});

	$("[id^='export_week_']").on('click', function () {

		let weekNumber = this.id.split("_")[2];
		let hasDirtyFields = false;
		$("[id^='wta" + weekNumber + "']").each(function () {

			if ($(this).hasClass('dirty'))
			{
				hasDirtyFields = true;
			}
		});

		if (!menuState.hasSavedPreOrder || hasDirtyFields)
		{
			let message = "";

			if (!menuState.hasSavedPreOrder)
			{
				message = "The Pre-Order has not been finalized. This export function will export values currently stored in the database.";
			}
			if (hasDirtyFields)
			{
				if (message == "")
				{
					message = "There are unsaved weekly projections. The export will use values currently stored in the database. You may want to save these value first.";
				}
				else
				{
					message += "There are also unsaved weekly projections. You may want to save these value first.";
				}
			}

			message += "<br /><br />Do you want to continue with the export?";
			dd_message({
				title: 'Export PreOrder',
				message: message,
				modal: true,
				width: 400,
				height: 300,
				confirm: function () {

					let weekStart = $(this).data("week_start");
					let url = "main.php?page=admin_menu_inventory_mgr&op=export_weekly_sales&store=" + gStore_ID + "&weekStart=" + weekStart + "&menu_id=" + menu_id;
					window.location = url;

				}
			});
		}
		else
		{
			let weekStart = $(this).data("week_start");
			let url = "main.php?page=admin_menu_inventory_mgr&op=export_weekly_sales&store=" + gStore_ID + "&weekStart=" + weekStart + "&menu_id=" + menu_id;
			window.location = url;
		}
	});

	$("[id^='export_by_range']").on('click', function () {

		let weekNumber = this.id.split("_")[2];
		let hasDirtyFields = false;
		$("[id^='wta" + weekNumber + "']").each(function () {

			if ($(this).hasClass('dirty'))
			{
				hasDirtyFields = true;
			}
		});

		if (!menuState.hasSavedPreOrder || hasDirtyFields)
		{
			let message = "";

			if (!menuState.hasSavedPreOrder)
			{
				message = "The Pre-Order has not been finalized. This export function will export values currently stored in the database.";
			}
			if (hasDirtyFields)
			{
				if (message == "")
				{
					message = "There are unsaved weekly projections. The export will use values currently stored in the database. You may want to save these value first.";
				}
				else
				{
					message += "There are also unsaved weekly projections. You may want to save these value first.";
				}
			}

			message += "<br /><br />Do you want to continue with the export?";
			dd_message({
				title: 'Export PreOrder By Range',
				message: message,
				modal: true,
				width: 400,
				height: 300,
				confirm: function () {

					let start = $("[name='range_day_start']").val();
					let end = $("[name='range_day_end']").val();
					let url = "main.php?page=admin_menu_inventory_mgr&op=export_sales_by_range&store=" + gStore_ID + "&start=" + start + "&end=" + end + "&menu_id=" + menu_id;
					window.location = url;

				}
			});
		}
		else
		{
			let start = $("[name='range_day_start']").val();
			let end = $("[name='range_day_end']").val();
			let url = "main.php?page=admin_menu_inventory_mgr&op=export_sales_by_range&store=" + gStore_ID + "&start=" + start + "&end=" + end + "&menu_id=" + menu_id;
			window.location = url;
		}
	});

	$("#export_EFL_sales").on('click', function () {

		let url = "main.php?page=admin_menu_inventory_mgr&op=export_EFL_sales&store=" + gStore_ID + "&menu_id=" + menu_id;
		window.location = url;
	});

	$(".expand_export").click(function () {
		$header = $(this);
		$content = $("#export_by_dates");
		$header.html(function () {
			return "<span class='button'>Export By Date</span>";
		});
		$content.slideToggle(500, function () {
		});

	});

}

function init_show_actuals()
{
	$("#show_actuals").on('click', function () {

		let btn_name = $(this).html();

		if (btn_name == 'View Servings Sold')
		{
			$(this).html("Hide Servings Sold");

			$("[id^='tr_act_']").each(function () {
				let thisInv = $(this).val();
				let thisRecipe = this.id.split("_")[2];
				$(this).removeClass('collapse');
			});

		}
		else
		{
			$(this).html("View Servings Sold");

			$("[id^='tr_act_']").each(function () {
				let thisInv = $(this).val();
				let thisRecipe = this.id.split("_")[2];
				$(this).addClass('collapse');
			});

		}

	});
}

function init_save_EFL_inventory()
{
	$("#save_efl_inv").on('click', function () {

		dd_message({
			title: 'Finalize PreOrder',
			message: "Are you sure you wish to save EFL Available to Promise inventory?",
			modal: true,
			width: 400,
			height: 180,
			confirm: function () {

				let overrideValues = {};

				$("[id^='ofi_']").each(function () {

					let thisInv = $(this).val();
					let thisRecipe = this.id.split("_")[1];

					overrideValues[thisRecipe] = thisInv;
				});

				displayModalWaitDialog('MIM_progress', "Saving EFL Inventory ...");
				$.ajax({
					url: 'ddproc.php',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'admin_menuEditor',
						op: 'save_override_inventory',
						store_id: gStore_ID,
						menu_id: menu_id,
						overrideValues: overrideValues
					},
					success: function (json) {
						$("#MIM_progress").remove();

						if (json.processor_success)
						{
							$("[id^='ofi_']").each(function () {
								$(this).removeClass('unsaved');
								$(this).addClass('saved');
								let curVal = $(this).val() * 1;
								$(this).data('org_val', curVal);
								$(this).removeClass('dirty');
							});
						}
						else
						{
							dd_message({
								title: 'Error',
								message: json.processor_message
							});
						}
					},
					error: function (objAJAXRequest, strError) {
						$("#MIM_progress").remove();

						response = 'Unexpected error: ' + strError;
						dd_message({
							title: 'Error',
							message: response
						});

					}
				});
			}
		});
	});

}

function init_save_sides_inventory()
{
	$("#save_sides_inv").on('click', function () {

		dd_message({
			title: 'Finalize PreOrder',
			message: "Are you sure you wish to save Sides Available to Promise inventory?",
			modal: true,
			width: 400,
			height: 150,
			confirm: function () {

				var overrideValues = {};

				$("[id^='ori_']").each(function () {

					var thisInv = $(this).val();
					var thisRecipe = this.id.split("_")[1];

					overrideValues[thisRecipe] = thisInv;
				});

				displayModalWaitDialog('MIM_progress', "Saving Sides Inventory ...");
				$.ajax({
					url: 'ddproc.php',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'admin_menuEditor',
						op: 'save_override_inventory',
						store_id: gStore_ID,
						menu_id: menu_id,
						overrideValues: overrideValues,
						save_to_global: true
					},
					success: function (json) {
						$("#MIM_progress").remove();

						if (json.processor_success)
						{
							$("[id^='ori_']").each(function () {
								$(this).removeClass('unsaved');
								$(this).addClass('saved');
								var curVal = $(this).val() * 1;
								$(this).data('org_val', curVal);
								$(this).removeClass('dirty');
							});
						}
						else
						{
							dd_message({
								title: 'Error',
								message: json.processor_message
							});
						}
					},
					error: function (objAJAXRequest, strError) {
						$("#MIM_progress").remove();

						response = 'Unexpected error: ' + strError;
						dd_message({
							title: 'Error',
							message: response
						});

					}
				});
			}
		});

	});

}

function dd_round_amount(inAmount)
{
	inAmount += .000005;

	var tempVal = inAmount * 1000.0;
	tempVal = parseInt(tempVal);
	tempVal = tempVal / 10;
	var pennyFraction = tempVal - Math.floor(tempVal);
	if (pennyFraction >= .5)
	{
		tempVal = Math.floor(tempVal) + 1;
	}
	else
	{
		tempVal = Math.floor(tempVal);
	}

	return tempVal / 100;
}

function validateSalesMix()
{
	var sumPercents = 0;
	$("[id^='ssmx_']").each(function () {
		var newVal = dd_round_amount($(this).val() * 1);
		sumPercents += newVal;
		sumPercents = dd_round_amount(sumPercents);
		if (sumPercents > 10000)
		{
			alert("Invalid entry");
			return false;
		}
	});

	if (sumPercents * 100 != 10000)
	{
		var delta = dd_round_amount(Math.abs(sumPercents - 100.0));
		var guidance = "";
		if (sumPercents > 100.0)
		{
			guidance += "Remove a total of " + delta + " from one more items to establish a 100.0% total";
		}
		else
		{
			guidance += "Add a total of " + delta + " from one more items to establish a 100.0% total";
		}

		$("#page_error_display").html("The Store Sales Mix does not equal 100% (" + sumPercents + "). <br />" + guidance);
		return false;
	}

	$("#page_error_display").html("");
	return true;
}

function init_week_inventory_editing()
{
	$("[id^='wta']").on('change', function () {
		var orgVal = $(this).data("org_val") * 1;
		var curVal = $(this).val() * 1;
		if (orgVal != curVal)
		{
			$(this).addClass('dirty');
		}
		else
		{
			$(this).removeClass('dirty');
		}
		reconcile_weeks_to_monthly_projection();
	});

}

function init_override_inventory_editing()
{
	// Core
	/*
	$("[id^='ovi_']").on('change', function () {
		var orgVal = $(this).data("org_val") * 1;
		var curVal = $(this).val() * 1;
		if (orgVal != curVal)
		{
			$(this).addClass('dirty');
		}
		else
		{
			$(this).removeClass('dirty');
		}

		var numSold = Number($(this).data('numsold'));
		if ($(this).val() < numSold)
		{
			dd_message({
				title: 'Error',
				message: 'The Override Inventory must be greater than or equal to the number of future pickups.'
			});

			$(this).val(numSold);
		}

		var thisRecipe = this.id.split("_")[1];
		$("#ri_" + thisRecipe).html($(this).val() - numSold.valueOf());

	});
*/
	// EFL
	$("[id^='ofi_']").on('change', function () {
		var orgVal = $(this).data("org_val") * 1;
		var curVal = $(this).val() * 1;
		if (orgVal != curVal)
		{
			$(this).addClass('dirty');
		}
		else
		{
			$(this).removeClass('dirty');
		}

		var numSold = Number($(this).data('numsold'));
		if ($(this).val() < numSold)
		{
			dd_message({
				title: 'Error',
				message: 'The Override Inventory must be greater than or equal to the number sold.'
			});
			$(this).val(numSold);
		}

		var thisRecipe = this.id.split("_")[1];
		$("#atos_" + thisRecipe).html($(this).val() - numSold.valueOf());

	});

	//sides
	$("[id^='ori_']").on('change', function () {
		var orgVal = $(this).data("org_val") * 1;
		var curVal = $(this).val() * 1;
		if (orgVal != curVal)
		{
			$(this).addClass('dirty');
		}
		else
		{
			$(this).removeClass('dirty');
		}

		var number_sold = Number($(this).data('number_sold'));
		if ($(this).val() < number_sold)
		{
			dd_message({
				title: 'Error',
				message: 'The Override Inventory must be greater than or equal to the number sold.'
			});

			$(this).val(number_sold);
		}

		var thisRecipe = this.id.split("_")[1];
		$("#atos_" + thisRecipe).html($(this).val() - number_sold.valueOf());

	});

}

function reconcile_data()
{
	// detect total mix <> 100%
	validateSalesMix();
	reconcile_weeks_to_monthly_projection();
}

function reconcile_weeks_to_monthly_projection()
{
	$("[id^='poi_']").each(function () {
		// check for zero inventory
		var thisVal = $(this).html();
		var thisRecipe = this.id.split("_")[1];

		var weekly_sum = ($("#wta1_" + thisRecipe).val() * 1) + ($("#wta2_" + thisRecipe).val() * 1) + ($("#wta3_" + thisRecipe).val() * 1) + ($("#wta4_" + thisRecipe).val() * 1);

		if ($("#wta5_" + thisRecipe)[0])
		{
			weekly_sum += ($("#wta5_" + thisRecipe).val()) * 1;
		}

		$("#pwt_" + thisRecipe).html(weekly_sum);
		var numSold = Number($("#ovi_" + thisRecipe).data('numsold'));

		if (weekly_sum == 0)
		{
			weekly_sum = ($("#cur_adj_inv_" + thisRecipe).data('default_inventory') * 1);
		}

		$("#cur_adj_inv_" + thisRecipe).html(weekly_sum);
		$("#cur_rmg_" + thisRecipe).html(weekly_sum - numSold.valueOf());

		if (weekly_sum != thisVal && weekly_sum != 0)
		{
			let huh = USER_PREFERENCES.HAS_SEEN_ELEMENT.value.WEEKLY_INVENTORY_WARNING;
			if (!USER_PREFERENCES.HAS_SEEN_ELEMENT.value.WEEKLY_INVENTORY_WARNING)
			{

				let variance_msg = "The entered servings number for a particular menu item have deviated from the original preordered amount. Please note that deviations from the preorder can create excess or a shortage of inventory. If you have exceeded your preorder, please contact your AE to secure adequate inventory. By clicking 'Okay' you are acknowledging this information.";

				dd_message({
					title: 'Variance from Pre-order',
					message: "<div>" + variance_msg + "</div><div style='margin:5px;'><input type='checkbox' id='WEEKLY_INVENTORY_WARNING' name='WEEKLY_INVENTORY_WARNING' />&nbsp;<label for='WEEKLY_INVENTORY_WARNING'>Do not show this warning.</label></div>",
					closeCallback: function () {
						if ($("#WEEKLY_INVENTORY_WARNING").is(":checked"))
						{
							USER_PREFERENCES.HAS_SEEN_ELEMENT.value.WEEKLY_INVENTORY_WARNING = 1;
							set_user_pref('HAS_SEEN_ELEMENT', USER_PREFERENCES.HAS_SEEN_ELEMENT.value, USER_DETAILS.id, null)
						}
					}
				});
			}

			$(this).addClass("warning_text");
		}
		else
		{
			$(this).removeClass("warning_text");
		}

	});

}

function init_finalize_preorder()
{
	$("#finalize_preorder").on('click', function () {

		$("[id^='poi_']").each(function () {
			// check for zero inventory
			var thisVal = $(this).html();
			if (thisVal == '0')
			{
				dd_message({
					title: 'Error',
					message: "<h4>Zero Projected Inventory Encountered</h4> Please be sure that all items have a non-zero sales mix or that you have clicked the 'Calculate Projected Inventory' Button."
				});

			}
		});

		dd_message({
			title: 'Finalize PreOrder',
			message: "Are you sure you wish to Finalize the Pre-Order Projected  Inventory?",
			modal: true,
			width: 400,
			height: 150,
			confirm: function () {
				var preorderInventory = {};
				var week1Inv = {};
				var week2Inv = {};
				var week3Inv = {};
				var week4Inv = {};
				var week5Inv = {};

				var encounteredNegativeInventory = false;
				var thisVal = null;
				var thisRecipe = null;
				var curNumberSold = null;
				var recipeName = null;
				$("[id^='pwt_']").each(function () {
					// check for zero inventory
					thisVal = $(this).html();
					thisRecipe = this.id.split("_")[1];
					preorderInventory[thisRecipe] = thisVal;

					curNumberSold = $("#ovi_" + thisRecipe).data("numsold");

					if (curNumberSold > thisVal && !encounteredNegativeInventory)
					{
						recipeName = $("#rname_" + thisRecipe).html();
						encounteredNegativeInventory = true;
						return false;
					}
				});

				if (encounteredNegativeInventory)
				{
					dd_message({
						title: "Error",
						div_id: "neg_warn",
						height: 300,
						modal: true,
						message: "Recipe " + recipeName + " (ID " + thisRecipe + ") has currently sold " + curNumberSold + " servings and the projected amount is less than that." +
							" This would create negative inventory.  Please increase the projection."
					});

				}
				else
				{
					$("[id^='wta1_']").each(function () {
						// check for zero inventory
						var thisVal = $(this).val();
						var thisRecipe = this.id.split("_")[1];
						week1Inv[thisRecipe] = thisVal;
					});
					$("[id^='wta2_']").each(function () {
						// check for zero inventory
						var thisVal = $(this).val();
						var thisRecipe = this.id.split("_")[1];
						week2Inv[thisRecipe] = thisVal;
					});
					$("[id^='wta3_']").each(function () {
						// check for zero inventory
						var thisVal = $(this).val();
						var thisRecipe = this.id.split("_")[1];
						week3Inv[thisRecipe] = thisVal;
					});
					$("[id^='wta4_']").each(function () {
						// check for zero inventory
						var thisVal = $(this).val();
						var thisRecipe = this.id.split("_")[1];
						week4Inv[thisRecipe] = thisVal;
					});

					// TODO: make conditional
					$("[id^='wta5_']").each(function () {
						// check for zero inventory
						var thisVal = $(this).val();
						var thisRecipe = this.id.split("_")[1];
						week5Inv[thisRecipe] = thisVal;
					});

					displayModalWaitDialog('MIM_progress', "Finalizing Core Menu Projections ...");

					$.ajax({
						url: 'ddproc.php',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_menuEditor',
							op: 'save_preorder_inventory',
							store_id: gStore_ID,
							menu_id: menu_id,
							preorderInventory: preorderInventory,
							week1Inv: week1Inv,
							week2Inv: week2Inv,
							week3Inv: week3Inv,
							week4Inv: week4Inv,
							week5Inv: week5Inv
						},
						success: function (json) {
							$("#MIM_progress").remove();
							if (json.processor_success)
							{

								$("[id^='finalize_week_']").each(function () {
									$(this).removeClass('disabled');
								});

								$("[id^='td_poi_']").each(function () {
									// check for zero inventory
									$(this).removeClass('static_unsaved_even')
									$(this).addClass('static_saved_even');
								});
								$("[id^='td_pwt_']").each(function () {
									// check for zero inventory
									$(this).removeClass('static_unsaved_odd')
									$(this).addClass('static_saved_odd');
								});

								$("[id^='td_wt1_']").each(function () {
									// check for zero inventory
									$(this).removeClass('static_unsaved_even')
									$(this).addClass('static_saved_even');
								});
								$("[id^='wta1_']").each(function () {
									// check for zero inventory
									$(this).removeClass('unsaved')
									$(this).addClass('saved');
								});
								$("[id^='td_wt2_']").each(function () {
									// check for zero inventory
									$(this).removeClass('static_unsaved_even')
									$(this).addClass('static_saved_even');
								});
								$("[id^='wta2_']").each(function () {
									// check for zero inventory
									$(this).removeClass('unsaved')
									$(this).addClass('saved');
								});
								$("[id^='td_wt3_']").each(function () {
									// check for zero inventory
									$(this).removeClass('static_unsaved_even')
									$(this).addClass('static_saved_even');
								});
								$("[id^='wta3_']").each(function () {
									// check for zero inventory
									$(this).removeClass('unsaved')
									$(this).addClass('saved');
								});
								$("[id^='td_wt4_']").each(function () {
									// check for zero inventory
									$(this).removeClass('static_unsaved_even')
									$(this).addClass('static_saved_even');
								});
								$("[id^='wta4_']").each(function () {
									// check for zero inventory
									$(this).removeClass('unsaved')
									$(this).addClass('saved');
								});
								$("[id^='td_wt5_']").each(function () {
									// check for zero inventory
									$(this).removeClass('static_unsaved_even')
									$(this).addClass('static_saved_even');
								});
								$("[id^='wta5_']").each(function () {
									// check for zero inventory
									$(this).removeClass('unsaved')
									$(this).addClass('saved');
								});

								$("[id^='ovi_']").each(function () {
									// check for zero inventory
									$(this).removeClass('unsaved')
									$(this).addClass('saved');
								});

								// update Cur Inventory/Remaining and hidden vals
								$("[id^='pwt_']").each(function () {
									// check for zero inventory
									var thisVal = $(this).html();
									var thisRecipe = this.id.split("_")[1];
									$("#cur_adj_inv_" + thisRecipe).html(thisVal);
									$("#cur_adj_inv_" + thisRecipe).data("default_inventory", thisVal);
									var numSold = $("#ovi_" + thisRecipe).data("numsold");
									$("#cur_rmg_" + thisRecipe).html(thisVal - numSold);
									$("#ovi_" + thisRecipe).val(thisVal);
									$("#ovi_" + thisRecipe).data("org_val", thisVal);
									$("#pts-td_" + thisRecipe).data("tooltip", 'Stored inventory: ' + thisVal + ' Remaining: ' + (thisVal - numSold));
								});

								data_tooltips_init();

								// update org_val
								$("[id^='wta1_']").each(function () {
									var thisVal = $(this).val();
									$(this).data("org_val", thisVal)
								});
								$("[id^='wta2_']").each(function () {
									var thisVal = $(this).val();
									$(this).data("org_val", thisVal)
								});
								$("[id^='wta3_']").each(function () {
									var thisVal = $(this).val();
									$(this).data("org_val", thisVal)
								});
								$("[id^='wta4_']").each(function () {
									var thisVal = $(this).val();
									$(this).data("org_val", thisVal)
								});

								// TODO: make conditional
								$("[id^='wta5_']").each(function () {
									var thisVal = $(this).val();
									$(this).data("org_val", thisVal)
								});
							}
							else
							{
								dd_message({
									title: 'Error',
									message: json.processor_message
								});

							}
						},
						error: function (objAJAXRequest, strError) {
							$("#MIM_progress").remove();
							response = 'Unexpected error: ' + strError;
							dd_message({
								title: 'Error',
								message: response
							});

						}
					});

				}
			}
		});
	});
}

function init_saving_guest_counts()
{
	$("#save_guest_counts").on('click', function () {

		var numRegGuests = $("#regular_guest_count_goal").val();
		var numTasteGuests = $("#taste_guest_count_goal").val();
		var numIntroGuests = $("#intro_guest_count_goal").val();

		displayModalWaitDialog('MIM_progress', "Saving  Guest Count Projections ...");

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_menuEditor',
				op: 'save_guest_counts',
				store_id: gStore_ID,
				menu_anchor_date: menu_anchor_date,
				numRegGuests: numRegGuests,
				numTasteGuests: numTasteGuests,
				numIntroGuests: numIntroGuests
			},
			success: function (json) {
				$("#MIM_progress").remove();
				if (json.processor_success)
				{
					$("#regular_guest_count_goal").removeClass('unsaved');
					$("#regular_guest_count_goal").addClass('saved');
					$("#intro_guest_count_goal").removeClass('unsaved');
					$("#intro_guest_count_goal").addClass('saved');
					$("#taste_guest_count_goal").removeClass('unsaved');
					$("#taste_guest_count_goal").addClass('saved');

				}
				else
				{
					dd_message({
						title: 'Error',
						message: json.processor_message
					});

				}
			},
			error: function (objAJAXRequest, strError) {
				$("#MIM_progress").remove();
				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});

			}
		});

	});
}

function init_saving_week_inventory()
{
	$("[id^='finalize_week_']").on('click', function () {

		if ($(this).hasClass('disabled'))
		{
			return;
		}

		var thisWeek = this.id.split("_")[2];

		var preFlightResult = sumAndStoreOverrideInventory(thisWeek, true);

		if (preFlightResult !== false)
		{
			var thisRecipe = preFlightResult[0];
			var curNumberSold = preFlightResult[1];
			var recipeName = $("#rname_" + thisRecipe).html();

			dd_message({
				title: "Error",
				div_id: "neg_warn",
				height: 300,
				modal: true,
				message: "Recipe " + recipeName + " (ID " + thisRecipe + ") has currently sold " + curNumberSold + " servings and the projected amount is less than that." +
					" This would create negative inventory.  Please increase the projection."
			});

			return false;
		}

		dd_message({
			title: 'Finalize Week',
			message: "Are you sure you wish to Finalize the Weekly Projected Inventory for week" + thisWeek + "?<br /><br />(This will increase the Available to Promise total that controls availability on your menus.)",
			modal: true,
			width: 400,
			height: 150,
			confirm: function () {

				var overrideValues = {};

				$("[id^='wta" + thisWeek + "_']").each(function () {

					var thisInv = $(this).val();
					var thisRecipe = this.id.split("_")[1];

					overrideValues[thisRecipe] = thisInv;
				});

				displayModalWaitDialog('MIM_progress', "Saving Week " + thisWeek + " Inventory ...");

				$.ajax({
					url: 'ddproc.php',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'admin_menuEditor',
						op: 'finalize_week',
						week_number: thisWeek,
						store_id: gStore_ID,
						menu_id: menu_id,
						overrideValues: overrideValues
					},
					success: function (json) {
						$("#MIM_progress").remove();
						if (json.processor_success)
						{
							$("[id^='wta" + thisWeek + "_']").each(function () {
								$(this).removeClass('unsaved');
								$(this).addClass('saved');
								var curVal = $(this).val() * 1;
								$(this).data('org_val', curVal);
								$(this).removeClass('dirty');
							});

							sumAndStoreOverrideInventory(thisWeek, false);
						}
						else
						{
							dd_message({
								title: 'Error',
								message: json.processor_message
							});
						}
					},
					error: function (objAJAXRequest, strError) {
						$("#MIM_progress").remove();
						response = 'Unexpected error: ' + strError;
						dd_message({
							title: 'Error',
							message: response
						});

					}
				});
			}
		});
	});
}

function sumAndStoreOverrideInventory(thisWeek, preflight)
{
	var overrideValues = {};

	$("[id^='poi_']").each(function () {
		// check for zero inventory
		var thisRecipe = this.id.split("_")[1];
		var weekly_sum = 0;

		if (thisWeek == 1)
		{
			weekly_sum += ($("#wta1_" + thisRecipe).val() * 1);
		}
		else
		{
			weekly_sum += ($("#wta1_" + thisRecipe).data("org_val") * 1);
		}
		if (thisWeek == 2)
		{
			weekly_sum += ($("#wta2_" + thisRecipe).val() * 1);
		}
		else
		{
			weekly_sum += ($("#wta2_" + thisRecipe).data("org_val") * 1);
		}
		if (thisWeek == 3)
		{
			weekly_sum += ($("#wta3_" + thisRecipe).val() * 1);
		}
		else
		{
			weekly_sum += ($("#wta3_" + thisRecipe).data("org_val") * 1);
		}

		if (thisWeek == 4)
		{
			weekly_sum += ($("#wta4_" + thisRecipe).val() * 1);
		}
		else
		{
			weekly_sum += ($("#wta4_" + thisRecipe).data("org_val") * 1);
		}

		if ($("#wta5_" + thisRecipe)[0])
		{
			if (thisWeek == 5)
			{
				weekly_sum += ($("#wta5_" + thisRecipe).val() * 1);

			}
			else
			{
				weekly_sum += ($("#wta5_" + thisRecipe).data("org_val") * 1);
			}

		}

		overrideValues[thisRecipe] = weekly_sum;

		var numSold = Number($("#ovi_" + thisRecipe).data('numsold'));
		$("cur_rmg_" + thisRecipe).html(weekly_sum - numSold.valueOf());

	});

	if (preflight)
	{
		var hasNegativeInventory = false;
		var badRecipe = null;
		var numSold = null;
		$("[id^='ovi_']").each(function () {
			badRecipe = this.id.split("_")[1];
			var newVal = overrideValues[badRecipe];
			numSold = $(this).data("numsold");

			if (numSold > newVal)
			{
				hasNegativeInventory = true;
				return false;
			}
		});

		if (hasNegativeInventory)
		{
			return [
				badRecipe,
				numSold
			];
		}
		else
		{
			return false;
		}
	}

	displayModalWaitDialog('MIM_progress', "Saving Override Inventory ...");

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_menuEditor',
			op: 'save_override_inventory',
			store_id: gStore_ID,
			menu_id: menu_id,
			overrideValues: overrideValues
		},
		success: function (json) {
			$("#MIM_progress").remove();
			if (json.processor_success)
			{
				$("[id^='ovi_']").each(function () {
					var thisRecipe = this.id.split("_")[1];
					var newVal = overrideValues[thisRecipe];
					$(this).data('org_val', newVal);
					$(this).val(newVal);
					$("#cur_adj_inv_" + thisRecipe).html(newVal);
					var numSold = $(this).data("numsold");
					$("#cur_rmg_" + thisRecipe).html(newVal - numSold);
					$("#pts-td_" + thisRecipe).data("tooltip", 'Stored inventory: ' + newVal + ' Remaining: ' + (newVal - numSold));
					$("#cur_adj_inv_" + thisRecipe).data("default_inventory", newVal);

				});
			}
			else
			{
				dd_message({
					title: 'Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			$("#MIM_progress").remove();
			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});

		}
	});
}

function init_saving_override_inventory()
{
	$("#save_override_inventory").on('click', function () {

		dd_message({
			title: 'Finalize PreOrder',
			message: "Are you sure you wish to save the Core Menu Available to Promise inventory?",
			modal: true,
			width: 400,
			height: 150,
			confirm: function () {

				var overrideValues = {};

				$("[id^='ovi_']").each(function () {

					var thisInv = $(this).val();
					var thisRecipe = this.id.split("_")[1];

					overrideValues[thisRecipe] = thisInv;
				});

				displayModalWaitDialog('MIM_progress', "Saving Core Inventory ...");

				$.ajax({
					url: 'ddproc.php',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'admin_menuEditor',
						op: 'save_override_inventory',
						store_id: gStore_ID,
						menu_id: menu_id,
						overrideValues: overrideValues
					},
					success: function (json) {
						$("#MIM_progress").remove();
						if (json.processor_success)
						{
							$("[id^='ovi_']").each(function () {
								$(this).removeClass('unsaved');
								$(this).addClass('saved');
								var curVal = $(this).val() * 1;
								$(this).data('org_val', curVal);
								$(this).removeClass('dirty');
							});
						}
						else
						{
							dd_message({
								title: 'Error',
								message: json.processor_message
							});
						}
					},
					error: function (objAJAXRequest, strError) {
						$("#MIM_progress").remove();
						response = 'Unexpected error: ' + strError;
						dd_message({
							title: 'Error',
							message: response
						});

					}
				});
			}
		});
	});
}

function init_resetting_store_sales_mix()
{
	$("#reset_sales_mix").on('click', function () {
		dd_message({
			title: 'Finalize Week',
			message: "Are you sure you want to reset your store's sales mix to national values?",
			modal: true,
			width: 400,
			height: 150,
			confirm: function () {

				$("[id^='nsm_']").each(function () {

					var thisMix = $(this).html();
					var thisRecipe = this.id.split("_")[1];
					$("#ssmx_" + thisRecipe).val(thisMix);
				});

				validateSalesMix();

				$("[id^='ssmx_']").each(function () {
					var orgVal = $(this).data("org_val") * 1;
					var curVal = $(this).val() * 1;
					if (orgVal != curVal)
					{
						$(this).addClass('dirty');
					}
					else
					{
						$(this).removeClass('dirty');
					}
				});

			}
		});
	});
}

function init_saving_store_sales_mix()
{
	$("#save_sales_mix").on('click', function () {

		var storeSalesMix = {};

		$("[id^='ssmx_']").each(function () {

			var thisMix = $(this).val();
			var thisRecipe = this.id.split("_")[1];

			storeSalesMix[thisRecipe] = thisMix;
		});

		displayModalWaitDialog('MIM_progress', "Saving Store Sales Mix  ...");

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_menuEditor',
				op: 'save_store_sales_mix',
				store_id: gStore_ID,
				menu_id: menu_id,
				storeSalesMix: storeSalesMix
			},
			success: function (json) {
				$("#MIM_progress").remove();
				if (json.processor_success)
				{
					$("[id^='ssmx_']").each(function () {
						$(this).removeClass('unsaved');
						$(this).addClass('saved');
						var curVal = $(this).val() * 1;
						$(this).data('org_val', curVal);
						$(this).removeClass('dirty');
					});
					menuState.hasSavedStoreMix = true;
				}
				else
				{
					dd_message({
						title: 'Error',
						message: json.processor_message
					});

				}
			},
			error: function (objAJAXRequest, strError) {
				$("#MIM_progress").remove();
				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});

			}
		});

	});
}

let starterPackMix = {};

function setNormalizedStarterPackMix()
{

	$("[id^='ssmx_']").each(function () {
		var thisMix = $(this).val() / 100.0;
		var thisRecipe = this.id.split("_")[1];
		var isIntro = $(this).data('is_intro');

		if (isIntro)
		{
			starterPackMix[thisRecipe] = thisMix;
		}
	});

	let totalTastePercent = 0.0;
	for (let key in starterPackMix)
	{
		totalTastePercent += starterPackMix[key];
	}

	let scaleFactor = 1.0 / totalTastePercent;

	for (let key in starterPackMix)
	{
		starterPackMix[key] = starterPackMix[key] * scaleFactor;
	}
}

let workShopMix = {};

function setNormalizedWorkshopMix()
{

	$("[id^='ssmx_']").each(function () {
		var thisMix = $(this).val() / 100.0;
		var thisRecipe = this.id.split("_")[1];
		var isTaste = $(this).data('is_taste');

		if (isTaste)
		{
			workShopMix[thisRecipe] = thisMix;
		}
	});

	let totalTastePercent = 0.0;
	for (let key in workShopMix)
	{
		totalTastePercent += workShopMix[key];
	}

	let scaleFactor = 1.0 / totalTastePercent;

	for (let key in workShopMix)
	{
		workShopMix[key] = workShopMix[key] * scaleFactor;
	}

}

function init_inventory_calculation()
{
	$("#calc_inv").on('click', function () {

		// validate percentages
		if (!validateSalesMix())
		{
			return;
		}

		setNormalizedWorkshopMix();
		setNormalizedStarterPackMix();

		var averageServings = 29;
		var numRegGuests = $("#regular_guest_count_goal").val();
		var numTasteGuests = $("#taste_guest_count_goal").val();
		var numIntroGuests = $("#intro_guest_count_goal").val();

		$("[id^='ssmx_']").not('[data-is_bundle="1"]').each(function () {

			var thisMix = $(this).val() / 100.0;
			var thisRecipe = this.id.split("_")[1];
			var isIntro = $(this).data('is_intro');
			var isTaste = $(this).data('is_taste');

			var total_servingsNeeded = numRegGuests * averageServings * thisMix;

			if (isIntro)
			{
				total_servingsNeeded += numIntroGuests * 12 * starterPackMix[thisRecipe];
			}

			if (isTaste)
			{
				total_servingsNeeded += numTasteGuests * 9 * workShopMix[thisRecipe];
			}

			total_servingsNeeded = Math.round(total_servingsNeeded);

			$("#poi_" + thisRecipe).html(total_servingsNeeded);
			$("#pwt_" + thisRecipe).html(total_servingsNeeded);
			$("#cur_adj_inv_" + thisRecipe).html(total_servingsNeeded);
			$("#ovi_" + thisRecipe).val(total_servingsNeeded);

			var weeksTotal = 0;
			for (x in weekInfo)
			{
				var number = weekInfo[x]['menu_week_number'];
				var targetID = "wt" + number + "_" + thisRecipe;
				var targetIDEditable = "wta" + number + "_" + thisRecipe;
				var distributionPercent = weekInfo[x]['distribution_percent'] / 100;
				var thisProjection = Math.round(distributionPercent * total_servingsNeeded);
				weeksTotal += thisProjection;

				$("#" + targetID).html(thisProjection);
				$("#" + targetIDEditable).val(thisProjection);

			}

			if (weeksTotal != total_servingsNeeded)
			{
				var diff = weeksTotal - total_servingsNeeded;
				thisProjection -= diff;
				$("#" + targetID).html(thisProjection);
				$("#" + targetIDEditable).val(thisProjection);
			}

			reconcile_weeks_to_monthly_projection();
		});
	});

}

function init_sales_mix_editing()
{

	$("[id^='ssmx_']").on('change', function () {
		validateSalesMix();
		var orgVal = $(this).data("org_val") * 1;
		var curVal = $(this).val() * 1;
		if (orgVal != curVal)
		{
			$(this).addClass('dirty');
		}
		else
		{
			$(this).removeClass('dirty');
		}

	});
}

function resetPage()
{
	document.getElementById('action').value = "menuChange";
	document.getElementById('menu_editor_form').submit();
}

function displayValidationErrorMsg(msg)
{
	msg = "<span style='color:red; font-size:larger;'>" + msg + "</span>";

	dd_message({
		title: 'Error',
		message: msg
	});
}

function clearErrors()
{
	var statusDiv = document.getElementById('errorMsg');
	statusDiv.style.display = 'none';
	statusTextDiv = document.getElementById('errorMsgText');
	statusTextDiv.innerHTML = "";
}

function storeChange(obj)
{
	document.getElementById('action').value = "storeChange";
	document.getElementById('menu_editor_form').submit();
}

function menuChange(obj)
{
	document.getElementById('action').value = "menuChange";
	document.getElementById('menu_editor_form').submit();
}

function calculatePage()
{

	var is_unsaved = false;
	var sidesCount = 0;

	var itemRows = document.getElementById('itemsTbl').rows;

	for (var i = 0; i < itemRows.length; i++)
	{
		if (itemRows[i].id.indexOf('row_') == -1)
		{
			continue;
		}

		var itemNumber = null;
		if (itemRows[i].id == 'addonEditorRow')
		{
			itemNumber = itemRows[i].getAttribute('item_id');
			if (!itemNumber)
			{

			}
		}
		else
		{
			itemNumber = itemRows[i].id.substr(4);
		}

	}
	// EFL Items

	var EFL_itemRows = document.getElementById('EFLitemsTbl').rows;

	for (var i = 0; i < EFL_itemRows.length; i++)
	{
		if (EFL_itemRows[i].id.indexOf('row_') == -1)
		{
			continue;
		}

		var itemNumber = null;
		if (EFL_itemRows[i].id == 'addonEditorRow')
		{
			itemNumber = EFL_itemRows[i].getAttribute('item_id');
			if (!itemNumber)
			{

			}
		}
		else
		{
			itemNumber = EFL_itemRows[i].id.substr(4);
		}

	}
	// Chef Touched Selections
	var ctsItemRows = document.getElementById('ctsItemsTbl').rows;
	for (i = 0; i < ctsItemRows.length; i++)
	{
		if (ctsItemRows[i].id.indexOf('row_') == -1)
		{
			continue;
		}

		var itemNumber = null;
		itemNumber = ctsItemRows[i].id.substr(4);
	}

}

function confirm_and_check_form()
{

	var form = $("#menu_editor_form")[0];

	clearErrors();

	validated = _check_form(form);

	var itemRows = document.getElementById('itemsTbl').rows;

	for (var i = 0; i < itemRows.length; i++)
	{
		if (itemRows[i].id.indexOf('row_') == -1)
		{
			continue;
		}

		var itemNumber = itemRows[i].id.substr(4);

	}

	var EFL_itemRows = document.getElementById('EFLitemsTbl').rows;

	for (var i = 0; i < EFL_itemRows.length; i++)
	{
		if (EFL_itemRows[i].id.indexOf('row_') == -1)
		{
			continue;
		}

		var itemNumber = EFL_itemRows[i].id.substr(4);
	}

	var ctsItemRows = document.getElementById('ctsItemsTbl').rows;

	for (i = 0; i < ctsItemRows.length; i++)
	{
		if (ctsItemRows[i].id.indexOf('row_') == -1)
		{
			continue;
		}

		var itemNumber = ctsItemRows[i].id.substr(4);
	}

	if (message != "")
	{
		validated = false;
		displayValidationErrorMsg(message);
	}

	if (validated)
	{
		var finalizeMessage = "Are you sure want to submit these changes to your menu? If the menu is active the changes will be immediately available to your customers.";

		dd_message({
			title: 'Attention',
			message: finalizeMessage,
			modal: true,
			confirm: function () {
				document.getElementById('action').value = "finalize";
				$("#menu_editor_form").submit();
				return false;
			},
			cancel: function () {
				return false;
			}
		});

		``
	}
	return false;
}