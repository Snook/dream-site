var originalMaxPPDeduction = 0;
var selectedFreeMenuItem;
var couponFreeMenuItemRequired = false;

function handle_ltd_round_up()
{
	$('#add_ltd_round_up').prop('disabled', false);

	if (orderState == 'SAVED' && !$('#add_ltd_round_up').is(':checked') && GUEST_PREFERENCES[user_id].LTD_AUTO_ROUND_UP.value > 0 && orderInfo.ltd_round_up_value == '')
	{
		$('#add_ltd_round_up').prop('checked', true);

		if (GUEST_PREFERENCES[user_id].LTD_AUTO_ROUND_UP.value == 1)
		{
			$("#ltd_round_up_select option[id='round_up_nearest_dollar']").attr("selected", "selected");
			$('#ltd_round_up_select').prop('disabled', false);
		}
		else
		{
			$('#ltd_round_up_select').prop('disabled', false).val(GUEST_PREFERENCES[user_id].LTD_AUTO_ROUND_UP.value).trigger('change');
		}
	}
	else if ($('#add_ltd_round_up').is(':checked'))
	{
		$('#ltd_round_up_select').prop('disabled', false);
	}

	$('#add_ltd_round_up').on('change', function (e)
	{
		if ($(this).is(':checked'))
		{
			$('#ltd_round_up_select').prop('disabled', false);

			if (GUEST_PREFERENCES[user_id].LTD_AUTO_ROUND_UP.value == '' && (!$.cookie('ltdru') || $.cookie('ltdru') != user_id + '.' + session_id))
			{
				dd_message({
					title: 'My Preferences',
					message: 'Would <span style="font-weight: bold; color: red;">the guest</span> like to automatically round up every time they order? The guest can edit this setting any time in <span style="font-weight: bold;">My Preferences</span>.<div style="text-align: center; margin-top: 10px;"><select id="ltd-round_up_opt_in"><option>Select a Round Up value</option><option value="1">Nearest Dollar</option></select></div>',
					modal: true,
					noOk: true,
					closeOnEscape: false,
					open: function (event, ui)
					{
						$(this).parent().find('.ui-dialog-titlebar-close').hide();

						var rup_dialog = $(this);

						$('#ltd-round_up_opt_in').on('change', function (e)
						{

							var rup_value = $(this).val();

							if (rup_value > 0)
							{
								dd_message({
									div_id: 'rup_confirm',
									title: 'My Preferences',
									message: 'You have chosen to set your auto Round Up to ' + ((rup_value == 1) ? 'the nearest dollar.' : '$' + rup_value),
									confirm: function ()
									{
										$(rup_dialog).remove();

										set_user_pref('LTD_AUTO_ROUND_UP', rup_value, user_id);

										if (orderInfo.ltd_round_up_value == 0 && rup_value != 1 || ($('#ltd_round_up_select option:selected').index() == 0 && !$.cookie('ltdru')))
										{
											dd_message({
												title: 'My Preferences',
												message: 'Preference saved. Would you like to use the selected Round Up value on this order?',
												modal: true,
												noOk: true,
												closeOnEscape: false,
												open: function (event, ui)
												{
													$(this).parent().find('.ui-dialog-titlebar-close').hide();
												},
												buttons: {
													"Yes": function ()
													{
														if (rup_value == 1)
														{
															$('#ltd_round_up_select option:eq(0)').prop('selected', true).trigger('change');
														}
														else
														{
															$('#ltd_round_up_select').val(rup_value).trigger('change');
														}

														$(this).remove();
													},
													"No": function ()
													{
														$(this).remove();
													}
												}
											});
										}
									}
								});
							}
						});
					},
					buttons: {
						"No": function ()
						{
							$(this).remove();

							set_user_pref('LTD_AUTO_ROUND_UP', 0, user_id);
						},
						"Ask Again Later": function ()
						{
							$(this).remove();

							$.cookie('ltdru', user_id + '.' + session_id);
						}
					}
				});
			}
		}
		else
		{
			$('#ltd_round_up_select').prop('disabled', true);
		}

		$('#ltd_round_up_select').trigger('change');

		calculateTotal();
	});

	$('#ltd_round_up_select').on('change', function (e)
	{

		var round_up_val = $(this).val();

		$('#checkout_total-roundup_donation').text(formatAsMoney(round_up_val));

		if ($('#add_ltd_round_up').is(':checked'))
		{
			$('#ltd_round_up_div').slideDown();
		}
		else
		{
			$('#ltd_round_up_div').slideUp();

			round_up_val = 0;
		}

		/*

		 $.ajax({
		 url: '/processor',
		 type: 'POST',
		 timeout: 20000,
		 dataType: 'json',
		 data: {
		 processor: 'cart_add_payment',
		 payment_type: 'ltd_round_up',
		 ltd_round_up_value: round_up_val
		 },
		 success: function (json)
		 {
		 if (json.processor_success)
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
		 error: function (objAJAXRequest, strError)
		 {
		 dd_message({
		 title: 'Error',
		 message: 'Unexpected error: ' + strError
		 });
		 }
		 });

		 */

		calculateTotal();
	});

	calculateTotal();
}



function handleCouponCodeResult(json)
{
	if (json.validation_errors)
	{
		printString = "";
		for (anError in json.validation_errors)
		{
			if (json.validation_errors[anError] != "" && json.validation_errors[anError] != null)
			{
				printString += json.validation_errors[anError] + "<br />";
			}
		}

		$("#coupon_error").html(printString);
		$("#coupon_error").show();
		$('#couponCodeSubmitter').show();
		$('#proc_mess').hide();
	}
	else
	{
		$("#coupon_error").hide();
		$("#coupon_error").html("");

		$('#proc_mess').hide();
		$('#couponDeleter').show();
		$('#coupon_code').prop('disabled', true);

		$("#couponValue").val(json.coupon_code_discount_total);
		$("#coupon_id").val(json.code_id);

		$("#coupon_type").val(json.discount_method);

		if (json.discount_method == "FREE_MEAL")
		{
			$('#coupon_free_meal_row').show();
			$("#free_entree").html(json.entree_title);
			$('#free_entree_servings').val(json.entree_servings);
		}
		else if (json.discount_method == "FREE_MENU_ITEM")
		{
			handleFreeMenuItem(json.coupon_obj.coupon_code);
		}
		else if (json.discount_method == "BONUS_CREDIT")
		{
			createBonusCredit(0);
		}

		coupon = json.coupon;
		couponDiscountMethod = json.discount_method;
		couponDiscountVar = json.discount_var;
		couponlimitedToFT = json.limit_to_finishing_touch;
		couponIsValidWithPlatePoints = json.valid_with_plate_points_credits;
		couponFreeMenuItemRequired = true;

		if (couponlimitedToFT)
		{
			var maxPPDeduction = $("#max_plate_points_deduction").html() * 1;

			originalMaxPPDeduction = maxPPDeduction;

			maxPPDeduction -= json.coupon_code_discount_total;
			if (maxPPDeduction < 0)
			{
				maxPPDeduction = 0;
			}

			$("#max_plate_points_deduction").html(formatAsMoney(maxPPDeduction));
		}

		calculateTotal();
	}

}

function processCode()
{

	var code = $("#coupon_code").val();
	$("#couponCodeSubmitter").hide();

	var theForm = $('#editorForm').get()[0];

	var filterBundle = false;
	var bundleBox = document.getElementById('selectedBundle');
	if (bundleBox && !bundleBox.checked)
	{
		filterBundle = true;
	}

	var parameters = "";
	for (i = 0; i < theForm.elements.length; i++)
	{
		if (filterBundle && theForm.elements[i].name == 'selectedBundle')
		{
		}
		else
		{
			parameters += (theForm.elements[i].name + "=" + theForm.elements[i].value);
			if (i + 1 != theForm.elements.length)
			{
				parameters += "&";
			}
		}
	}

	parameters = parameters + "&store_id=" + STORE_DETAILS.id;

	var d = new Date();

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_orderMgrCouponCodeProcessorDelivered',
			user_id: user_id,
			op: 'add',
			org_ts: 'org_order_time',
			order_id: order_id,
			unique: d.getTime(),
			coupon_code: code,
			order_state: orderState,
			params: parameters
		},
		success: function (json)
		{
			if (json.processor_success)
			{
				handleCouponCodeResult(json);
			}
			else
			{
				dd_message({
					title: 'Error',
					message: json.processor_message
				});

			}

		},
		error: function (objAJAXRequest, strError)
		{

			$("#coupon_error").html("");
			$("#coupon_error").hide();
			$('#couponCodeSubmitter').show();
			$('#proc_mess').hide();

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});
		}
	});
}

function removeCode()
{
	var theForm = $('#editorForm').get()[0];

	var filterBundle = false;
	var bundleBox = document.getElementById('selectedBundle');
	if (bundleBox && !bundleBox.checked)
	{
		filterBundle = true;
	}

	var parameters = "";
	for (i = 0; i < theForm.elements.length; i++)
	{
		if (filterBundle && theForm.elements[i].name == 'selectedBundle')
		{
		}
		else
		{
			parameters += (theForm.elements[i].name + "=" + theForm.elements[i].value);
			if (i + 1 != theForm.elements[i])
			{
				parameters += "&";
			}
		}
	}

	parameters = parameters + "&store_id=" + STORE_DETAILS.id;

	var d = new Date();

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_orderMgrCouponCodeProcessorDelivered',
			op: 'remove',
			user_id: user_id,
			org_ts: 'org_order_time',
			order_id: order_id,
			unique: d.getTime(),
			order_state: orderState,
			params: parameters
		},
		success: function (json)
		{
			if ($("#coupon_type").val() == "BONUS_CREDIT")
			{
				removeBonusCredit();
			}

			$('#couponCodeSubmitter').show();
			$('#proc_mess').hide();
			$('#couponDeleter').hide();
			$('#coupon_code').prop('disabled', false);
			$("#coupon_error").hide();
			$("#coupon_error").html("");
			$("#couponValue").val(0);
			$('#coupon_free_meal_row').hide();
			$("#free_entree").html("");
			$("#coupon_id").val("");
			$("#coupon_type").val("");

			$('#coupon_select_free_menu_item_row').hide();
			$('#free_menu_item_coupon option:not([value])').attr('data-dd_required', false);
			couponFreeMenuItem = false;
			couponFreeMenuItemRequired = false;

			couponDiscountMethod = 'NONE';
			couponDiscountVar = 0;
			couponlimitedToFT = false;
			couponIsValidWithPlatePoints = true;

			calculateTotal();
		},
		error: function (objAJAXRequest, strError)
		{

			$("#coupon_error").html("");
			$("#coupon_error").hide();
			$('#couponCodeSubmitter').show();
			$('#proc_mess').hide();

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});
		}
	});

}

function handle_free_menu_item_edit()
{
	if (couponFreeMenuItem)
	{
		handleFreeMenuItem($('#coupon_code').val());
	}
}

function handleFreeMenuItem(coupon_code)
{
	var menu_item_count = 0;

	if (coupon_code == 'SRDessert')
	{
		menu_item_count = buildFreeMenuItemList("menu_item.menu_category_id == 9");
	}
	else if (coupon_code == 'SRDinner')
	{
		menu_item_count = buildFreeMenuItemList("menu_item.menu_class == 'Specials' || menu_item.menu_class == 'Extended Fast Lane'");
	}
	else if (coupon_code == 'CC3RDFREE')
	{
		menu_item_count = buildFreeMenuItemList("(menu_item.menu_class == 'Specials' || menu_item.menu_class == 'Extended Fast Lane') && menu_item.price <= 25.00");
	}
	else if (coupon_code == 'SCFinishingTouch')
	{
		menu_item_count = buildFreeMenuItemList("menu_item.menu_category_id == 9 && menu_item.price <= 15.00");
	}
	else if (coupon_code == 'HCFastLane')
	{
		menu_item_count = buildFreeMenuItemList("menu_item.menu_class == 'Extended Fast Lane' && menu_item.pricing_type == 'HALF'");
	}
	else if (coupon_code == 'SCFriendDinner')
	{
		menu_item_count = buildFreeMenuItemList("menu_item.menu_class == 'Specials' && menu_item.intro_item == true");
	}

	if (menu_item_count > 0)
	{
		$('#coupon_select_free_menu_item_row').show();

		$('#free_menu_item_coupon').attr('data-dd_required', true).on('change', function (e)
		{
			var menu_item = $(this).find(':selected').data('menu_item');
			var price = $(this).find(':selected').data('price');

			$('#couponValue').val(price);
			selectedFreeMenuItem = menu_item;

			calculateTotal();
		});
	}
	else
	{
		dd_message({
			title: 'Alert',
			message: 'No valid free items found in cart for this coupon, please add the desired item and then submit the coupon.'
		});

		removeCode();
	}
}

function buildFreeMenuItemList(expression)
{
	rebuildOrderItemsArray();

	var menu_item_in_list = false;
	var selected_menu_item_in_list = false; // temp store, used for tab switching
	var menu_item_count = 0;

	// setup efl free item select
	var s = $('#free_menu_item_coupon').html('').attr('disabled', ((couponFreeMenuItem) ? true : false));

	$("<option />").val('').text('Select Free Item').appendTo(s);

	$.each(orderItems, function (index, menu_item)
	{
		if (!menu_item_in_list)
		{
			menu_item_in_list = ((couponFreeMenuItem == menu_item.id) ? true : false);
		}

		if (!selected_menu_item_in_list)
		{
			selected_menu_item_in_list = ((selectedFreeMenuItem == menu_item.id) ? true : false);
		}

		if (eval(expression))
		{
			menu_item_count++;

			$("<option />")
				.val(menu_item.id)
				.attr('data-menu_item', menu_item.id)
				.attr('data-price', menu_item.price)
				.attr('selected', ((couponFreeMenuItem == menu_item.id || selectedFreeMenuItem == menu_item.id) ? true : false))
				.text(menu_item.item_title + ' (' + menu_item.price + ')').appendTo(s);
		}
	});

	if (!menu_item_in_list)
	{
		$('#couponValue').val('0.00');

		$('#free_menu_item_coupon').attr('disabled', false);
	}

	calculateTotal();

	return menu_item_count;
}

function stripLeadingZeroes(val)
{
	var leadingZeroCount = 0;
	var retVal = val;

	for (var i = 0, len = val.length; i < len; i++)
	{
		if (val[i] != '0')
		{
			break;
		}

		leadingZeroCount++;
	}

	if (leadingZeroCount > 0)
	{
		retVal = val.slice(leadingZeroCount)
	}

	return retVal;

}

function handlePlatePointsDiscount(val)
{
	if (!couponIsValidWithPlatePoints)
	{
		document.getElementById('plate_points_discount').value = formatAsMoney(0);

		dd_message({
			title: 'Alert',
			message: 'PLATEPOINTS cannot be used with the attached coupon.'
		});

	}

	if ((isNaN(val) || val <= 0) && val != ".")
	{
		$('#plate_points_discount').val("0");
		$('#OEH_plate_points_order_discount_fee').html(formatAsMoney(0));
		calculateTotal();
		return;
	}

	needPlatePointsMaxDiscountChangeWarning = false;

	val = formatAsMoney(Math.abs(val)) * 1;

	var maxPPCredit = $("#plate_points_available").html() * 1;
	var maxPPDeduction = $("#max_plate_points_deduction").html() * 1;
	var curPPDiscount = $("#plate_points_discount").val() * 1;

	if (maxPPDeduction < curPPDiscount)
	{
		$("#plate_points_discount").val(maxPPDeduction);
	}

	if (maxPPCredit > maxPPDeduction)
	{
		$('#tbody_max_plate_points_deduction').show();
	}
	else
	{
		$('#tbody_max_plate_points_deduction').hide();
	}

	var cap = maxPPCredit;
	if (cap > maxPPDeduction)
	{
		cap = maxPPDeduction;
	}

	if (val > cap)
	{
		val = cap;
		$('#plate_points_discount').val(val);

	}

	$('#OEH_plate_points_order_discount_fee').html(formatAsMoney(val));


	calculateTotal();
}

function editCashCheckAmount(id, type)
{
	// Show input form
	$('#check_total_input_' + id).show();
	$('#check_payment_number_input_' + id).show();
	// Hide edit link and text
	$('#check_total_' + id).hide();
	$('#check_payment_number_' + id).hide();
	$('#check_editlink_' + id).hide();
}

function editCashCheckUpdate(id, type)
{
	$('#payment_sum_value_' + id).html($('#check_total_input_form_' + id).val());
	$('#payment_sum_name_' + id).html('Check (#' + $('#check_payment_number_input_form_' + id).val() + ')');

	// Loop through payment summary to get new total
	new_total = 0;
	$('[id^="payment_sum_value_"]').each(function (index)
	{
		new_total = new_total + Number($(this).text());
	});

	// loop through and subtract refunds
	$('[id^="refund_payment_sum_value_"]').each(function (index)
	{
		new_total = new_total - Number($(this).text());
	});

	$('#OEH_paymentsTotal').html(formatAsMoney(new_total));

	calculateTotal();
}

// Check cift card balance
function getGiftCardBalance()
{
	$('#gc_proc_anim').show();
	$('#balance_target').hide();

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'giftCardBalance',
			card_number: $('#debit_gift_card_number').val(),
			output: 'json'
		},
		success: function (json)
		{
			if (json.processor_success)
			{
				$('#balance_target').show();
				$('#balance_target').html(json.card_balance);
				$('#gc_proc_anim').hide();
			}
			else if (json.message == "Not logged in")
			{
				$('#balance_target').show();
				$('#balance_target').html('<span style="color:red;font-weight:bold;">Session timed out, please <a href="' + request_uri + '">[login]</a> again.</span>');
				$('#gc_proc_anim').hide();
			}
			else
			{
				$('#balance_target').show();
				$('#balance_target').html('<span style="color:red;font-weight:bold;">' + json.message + '</span>');
				$('#gc_proc_anim').hide();
			}
		},
		error: function (objAJAXRequest, strError)
		{
			$('#balance_target').show();
			$('#balance_target').html('Error: ' + strError + '. Please <a href="javascript:getGiftCardBalance();">[try again]</a>.');
		}
	});
}

function editCashCheckAmountCommit(id, type)
{
	alert("You are only modifying the " + type + " details.\nNo other modifications will be made.");

	$('#check_total_mess_' + id).hide();
	new_total = $('#check_total_input_form_' + id).val();
	new_number = $('#check_payment_number_input_form_' + id).val();

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_editCashCheckAmount',
			id: id,
			type: type,
			new_total: new_total,
			new_number: new_number,
			order_id: order_id
		},
		success: function (json)
		{
			if (json.processor_success)
			{
				// Hide input form
				$('#check_total_input_' + id).hide();
				$('#check_payment_number_input_' + id).hide();
				// Show edit link and text
				$('#check_total_' + id).show();
				$('#check_payment_number_' + id).show();
				$('#check_editlink_' + id).show();

				// Update page text with new values
				$('#check_total_' + id).html(new_total);
				$('#check_payment_number_' + id).html(new_number);

				$('#check_total_mess_' + id).show();
				$('#check_total_mess_' + id).show().html('<img src="' + PATH.image_admin + '/icon/accept.png" alt="Accept" />').fadeOut(2000);
			}
			else if (json.message == "Not logged in")
			{
				$('#check_total_mess_' + id).show();
				$('#check_total_mess_' + id).html('<span style="color:red;font-weight:bold;">Session timed out, please <a href="' + +'">[login]</a> again.</span>');
			}
			else
			{
				$('#check_total_mess_' + id).show();
				$('#check_total_mess_' + id).html('<span style="color:red;font-weight:bold;">' + json.processor_message + '</span>');
			}
		},
		error: function (objAJAXRequest, strError)
		{
			$('#check_total_mess_' + id).show();
			$('#check_total_mess_' + id).html('Error: ' + strError + '. Please <a href="javascript:editCashCheckAmountCommit(' + id + ',\'' + type + '\');">[try again]</a>.');
		}
	});

}

// Proccess delayed payment now
function showButtonProcessDelayedPaymentNow(message)
{
	if (PendingDPOriginalStatus == 'PENDING')
	{
		$('#payment_proc_mess').show();
		$('#payment_proc_mess').html('<input type="button" class="btn btn-primary btn-sm" name="process_payment" value="Process delayed payment now" onclick="processPayment();">');
	}
	else if (message)
	{
		$('#payment_proc_mess').show();
		$('#payment_proc_mess').html(message);
	}
	else
	{
		$('#payment_proc_mess').hide();
	}
}

function processPayment()
{
	if (confirm("Are you sure you wish to process the delayed payment now? Click OK to process."))
	{
		$('#payment_proc_mess').show();
		$('#payment_proc_mess').html('<img src="' + PATH.image_admin + '/throbber_processing_noborder.gif" alt="Processing" />');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 60000,
			dataType: 'json',
			data: {
				processor: 'admin_delayedPaymentProcessor',
				order_number: order_id
			},
			success: function (json)
			{
				if (json.success)
				{
					if ($('#point_to_transaction').length)
					{
						var credit_card_number = $('#point_to_transaction option:selected').text();
						$('#cc_payment_number_other').html('XXXXXXXXXXXX' + credit_card_number.substr(-4));
					}

					$('#cc_delayed_tran_num_other').html(json.transaction_id);
					$('#cc_delayed_date_other').html(json.transaction_date);
					$('#cc_delayed_status_other').html('Status: Payment Processed');

					$('#payment_sum_row_' + json.payment_id).css({'color': ''});
					$('#payment_sum_type_' + json.payment_id).css({'color': ''});
					$('#payment_sum_name_' + json.payment_id).css({'color': ''});
					$('#payment_sum_value_' + json.payment_id).css({'color': ''});
					$('#payment_sum_date_' + json.payment_id).css({'color': ''});

					$('#payment_sum_date_' + json.payment_id).html(json.summary_date);

					canAdjustDelayedPayment = false;

					$('#payment_proc_mess').html('<span style="color:green;font-weight:bold;">Payment Processed.</span>').fadeOut(4000);
				}
				else if (json.message == "Not logged in")
				{
					$('#payment_proc_mess').html('<span style="color:red;font-weight:bold;">Session timed out, please <a href="' + back_path() + '">[login]</a> again.</span>');
				}
				else
				{
					$('#payment_proc_mess').html('<span style="color:red;font-weight:bold;">' + json.message + '</span>');
				}
			},
			error: function (objAJAXRequest, strError)
			{
				$('#payment_proc_mess').show();
				$('#payment_proc_mess').html('Error: ' + strError + '. Please <a href="javascript:processPayment();">[try again]</a>.');
			}
		});
	}
}

// Change delayed payment status
function setDelayedPaymentStatusTo()
{
	//Do not allow the user to reset to FAIL
	if ($('#change_delayed_payment_status').val() == 'FAIL' && PendingDPOriginalStatus != 'FAIL')
	{
		alert('Can not change transaction back to Fail. Please choose pending or canceled.');
		$('#change_delayed_payment_status').val(PendingDPOriginalStatus);
		return false;
	}
	// Reset transaction id dropdown, can only set the status of one dropdown a time.
	if ($('#change_delayed_payment_status'))
	{
		$('#point_to_transaction').val(PendingDPOriginalTransActionID);
	}

	if ($('#change_delayed_payment_status').val() == PendingDPOriginalStatus)
	{
		showButtonProcessDelayedPaymentNow();
	}
	else
	{
		$('#payment_proc_mess').show();
		$('#payment_proc_mess').html('<input type="button" class="btn btn-primary btn-sm" name="set_delayed_payment_status" value="Set delayed payment status to ' + $('#change_delayed_payment_status').val().toLowerCase() + '" onClick="processDelayedPaymentStatus();">');
	}
}

function setupCouponState()
{
	// if a coupon is attached we need to update the UI
	// 1) set up the coupon code box - disabled with code
	if (coupon)
	{
		document.getElementById('couponCodeSubmitter').style.display = "none";
		document.getElementById('couponDeleter').style.display = "inline";
		document.getElementById('coupon_code').value = coupon.coupon_code;
		document.getElementById('coupon_code').disabled = true;
	}
}

function processDelayedPaymentStatus()
{
	$('#payment_proc_mess').show();
	$('#payment_proc_mess').html('<img src="' + PATH.image_admin + '/throbber_processing_noborder.gif" alt="Processing" />');

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_delayedPaymentStatusProcessor',
			order_number: order_id,
			old_status: PendingDPOriginalStatus,
			new_status: $('#change_delayed_payment_status').val(),
			store_id: store_id
		},
		success: function (json)
		{
			if (json.success)
			{
				// Keep dropdown in sync with processed result in case it was changed during processing
				PendingDPOriginalStatus = json.status;
				$('#change_delayed_payment_status').val(PendingDPOriginalStatus);

				// Reset transaction id dropdown, only set one at a time.
				if ($('#change_delayed_payment_status'))
				{
					$('#point_to_transaction').val(PendingDPOriginalTransActionID);
				}

				// Voodoo, editing cash/check totals element id payment_sum_value_*, so change
				// element id to cancelled_payment_sum_value_* which will be skipped over in the total caclulation.
				if (json.status == 'CANCELLED' && $('#payment_sum_value_' + json.payment_id))
				{
					$('#payment_sum_value_' + json.payment_id).attr('id', 'cancelled_payment_sum_value_' + json.payment_id);
				}
				else if (json.status == 'PENDING' && $('#cancelled_payment_sum_value_' + json.payment_id))
				{
					$('#cancelled_payment_sum_value_' + json.payment_id).attr('id', 'payment_sum_value_' + json.payment_id);
				}

				// Update payment status and details
				if (json.status == 'CANCELLED')
				{
					// Can only adjust pending payments
					canAdjustDelayedPayment = false;

					// Update summary rows
					$('#payment_sum_row_' + json.payment_id).css({'text-decoration': 'line-through'});
					$('#payment_sum_type_' + json.payment_id).css({'text-decoration': 'line-through'});
					$('#payment_sum_name_' + json.payment_id).css({'text-decoration': 'line-through'});
					$('#cancelled_payment_sum_value_' + json.payment_id).css({'text-decoration': 'line-through'});
					$('#payment_sum_date_' + json.payment_id).css({'text-decoration': 'line-through'});
					$('#OEH_paymentsTotal').html(formatAsMoney(Number($('#OEH_paymentsTotal').html()) - Number($('#cancelled_payment_sum_value_' + json.payment_id).html())));

					// Reset new payment if they have auto adjust selected
					$('#dp_adjust_div').hide();
					//	$('#autoAdjust').attr({ 'checked' : 'checked' });
					//	handleAutoAdjust($('#autoAdjust'));
				}
				else
				{
					// Payment pending, we are allowed to edit it
					canAdjustDelayedPayment = true;

					// Update summary rows
					$('#payment_sum_row_' + json.payment_id).css({'text-decoration': 'none'});
					$('#payment_sum_type_' + json.payment_id).css({'text-decoration': 'none'});
					$('#payment_sum_name_' + json.payment_id).css({'text-decoration': 'none'});
					$('#payment_sum_value_' + json.payment_id).css({'text-decoration': 'none'});
					$('#payment_sum_date_' + json.payment_id).css({'text-decoration': 'none'});
					$('#OEH_paymentsTotal').html(formatAsMoney(Number($('#OEH_paymentsTotal').html()) + Number($('#payment_sum_value_' + json.payment_id).html())));

					// Allow auto adjust to be used
					canAutoAdjust = true;
				}

				calculateTotal();

				showButtonProcessDelayedPaymentNow('<span style="color:green;font-weight:bold;">Delayed Payment set to ' + PendingDPOriginalStatus + '.</span>');
				$('#change_delayed_payment_status_img').attr({
					src: PATH.image_admin + '/icon/accept.png',
					alt: 'Accept'
				});
				$('#change_delayed_payment_status_img').css({display: 'inline'}).fadeOut(2000);
			}
			else if (json.message == "Not logged in")
			{
				$('#payment_proc_mess').html('<span style="color:red;font-weight:bold;">Session timed out, please <a href="' + back_path() + '">[login]</a> again.</span>');
			}
			else
			{
				$('#payment_proc_mess').html('<span style="color:red;font-weight:bold;">' + json.message + '</span>');
			}
		},
		error: function (objAJAXRequest, strError)
		{
			$('#payment_proc_mess').show();
			$('#payment_proc_mess').html('Error: ' + strError + '. Please <a href="javascript:processDelayedPaymentStatus();">[try again]</a>.');
		}
	});
}

// Point to new transaction ID
function pointToTransaction()
{
	// Reset payment status dropdown, only set one at a time.
	$('#change_delayed_payment_status').val(PendingDPOriginalStatus);

	if ($('#point_to_transaction').val() == PendingDPOriginalTransActionID)
	{
		$('#payment_proc_mess').show();
		showButtonProcessDelayedPaymentNow();
	}
	else
	{
		$('#payment_proc_mess').show();
		$('#payment_proc_mess').html('<input type="button" class="btn btn-primary btn-sm" name="point_to_payment" value="Point payment to selected Credit Card Number" onClick="processPointToTransaction();">');
	}
}

function processPointToTransaction()
{
	$('#payment_proc_mess').show();
	$('#payment_proc_mess').html('<img src="' + PATH.image_admin + '/throbber_processing_noborder.gif" alt="Processing" />');

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_processPointToTransaction',
			order_number: order_id,
			store_id: store_id,
			old_ref_number: PendingDPOriginalTransActionID,
			new_ref_number: $('#point_to_transaction').val(),
			new_card_number: $('#point_to_transaction option:selected').text()
		},
		success: function (json)
		{
			if (json.success)
			{
				// Keep dropdown in sync with processed result in case it was changed during processing
				PendingDPOriginalTransActionID = json.transactionid;
				$('#point_to_transaction').val(PendingDPOriginalTransActionID);

				// Reset payment status dropdown, only set one at a time.
				$('#change_delayed_payment_status').val(PendingDPOriginalStatus);

				$('#payment_sum_type_' + json.payment_id).html(json.new_card_type);
				$('#payment_sum_name_' + json.payment_id).html('Delayed CC Payment (#' + json.new_card_number + ')');

				$('#cc_credit_card_type_other').html(json.new_card_type);
				$('#cc_payment_transaction_number_other').html(json.transactionid);
				showButtonProcessDelayedPaymentNow('<span style="color:green;font-weight:bold;">Credit card set to ' + json.new_card_type + '.</span>');
				$('#point_to_transaction_img').css({display: 'inline'}).fadeOut(2000);
			}
			else if (json.message == "Not logged in")
			{
				$('#payment_proc_mess').html('<span style="color:red;font-weight:bold;">Session timed out, please <a href="' + back_path() + '">[login]</a> again.</span>');
			}
			else
			{
				$('#payment_proc_mess').html('<span style="color:red;font-weight:bold;">' + json.message + '</span>');
			}
		},
		error: function (objAJAXRequest, strError)
		{
			$('#payment_proc_mess').show();
			$('#payment_proc_mess').html('Error: ' + strError + '. Please <a href="javascript:processPointToTransaction();">[try again]</a>.');
		}
	});
}

function unsetAutoAdjust()
{
	if ($('#autoAdjust').length)
	{
		document.getElementById('autoAdjust').checked = false;
	}
}

function setAllPaymentFieldsToUnrequired()
{
	var reqField;

	reqField = document.getElementById('payment1_gc_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_gc_payment_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ccNameOnCard');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ccNumber');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ccType');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ccMonth');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ccYear');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}

	reqField = document.getElementById('payment1_cash_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_cash_total_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_refund_cash_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_refund_cash_total_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_check_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_check_total_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_cc_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_reference_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ref_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}

	reqField = document.getElementById('payment2_ccType');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ccMonth');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ccYear');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}

	reqField = document.getElementById('payment2_gc_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_gc_payment_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ccNameOnCard');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ccNumber');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_cash_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_check_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_cc_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ref_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}

	reqField = document.getElementById('debit_gift_card_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('debit_gift_card_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}

}

var lastPayment1Type = false;

function changePayment1(mySelect)
{

	if (lastPayment1Type && lastPayment1Type != mySelect)
	{
		if (lastPayment1Type == 'CC')
		{
			$('#payment1_gc_total_amount').val("");
		}
		else if (lastPayment1Type == 'REFERENCE')
		{
			$('#payment1_reference_total_amount').val("");
		}

	}

	lastPayment1Type = mySelect;

	var divGC = document.getElementById('payment1_gc');
	var divCash = document.getElementById('payment1_cash');
	var divRefundCash = document.getElementById('payment1_refund_cash');
	var divCheck = document.getElementById('payment1_check');
	var divCC = document.getElementById('payment1_cc');
	var divCredit = document.getElementById('payment1_credit');
	var divRef = document.getElementById('payment1_reference');
	var divRefOther = document.getElementById('payment1_ref');
	var refundDiv = document.getElementById('payment1RefundCCDiv');

	var dpAdjust = document.getElementById('dp_adjust_div'); // can be null

	document.getElementById('new_payments_total').value = 0;
	if (document.getElementById('use_store_credits') && document.getElementById('use_store_credits').checked)
	{
		document.getElementById('new_payments_total').value = formatAsMoney(document.getElementById('store_credits_amount').value);
	}

	var divDebitGiftCard = document.getElementById('payment1_debit_gift_card');

	var payment2 = document.getElementById('payment2');

	var selected_ref = false;
	if (mySelect.substr(0, 4) == 'REF_')
	{
		selected_ref = mySelect.substr(4);
		mySelect = 'REFERENCE_OTHER';
	}

	resetPendingDelayedPayment();

	// reset so validation does not fail when using payment types other than CC
	var amount = document.getElementById('payment1_cc_total_amount');
	amount.value = '';

	var reqField;
	reqField = document.getElementById('payment1_gc_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_gc_payment_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ccNameOnCard');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ccNumber');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_cash_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_refund_cash_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_check_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_cash_total_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_refund_cash_total_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_check_total_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_cc_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_reference_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('debit_gift_card_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('debit_gift_card_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ref_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ccType');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ccMonth');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment1_ccYear');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}

	switch (mySelect)
	{
		case "REFERENCE":
			refundDiv.style.display = 'none';
			divGC.style.display = 'none';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'none';
			divRef.style.display = 'block';
			divCredit.style.display = 'none';
			divCC.style.display = 'none';
			divRefOther.style.display = 'none';

			divDebitGiftCard.style.display = 'none';

			if (dpAdjust && canAdjustDelayedPayment)
			{
				dpAdjust.style.display = 'none';
				unsetAutoAdjust();
			}
			payment2.style.display = 'none';

			reqField = document.getElementById('payment1_reference_total_amount');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			changePayment2('');

			onPaymentSelected();
			break;

		case "GIFT_CERT":
			unsetAutoAdjust();
			refundDiv.style.display = 'none';
			divGC.style.display = 'block';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'none';
			divRef.style.display = 'none';
			divCC.style.display = 'none';
			divCredit.style.display = 'none';
			if (dpAdjust)
			{
				dpAdjust.style.display = 'none';
			}

			if (orderState != 'ACTIVE')
			{
				payment2.style.display = 'block';
			}

			divDebitGiftCard.style.display = 'none';
			divRefOther.style.display = 'none';

			reqField = document.getElementById('payment1_gc_total_amount');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment1_gc_payment_number');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			onPaymentSelected();

			break;

		case "CASH":
			unsetAutoAdjust();
			divGC.style.display = 'none';
			refundDiv.style.display = 'none';
			divCash.style.display = 'block';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
			divRef.style.display = 'none';
			divCredit.style.display = 'none';
			if (dpAdjust)
			{
				dpAdjust.style.display = 'none';
			}
			payment2.style.display = 'none';
			divDebitGiftCard.style.display = 'none';
			divRefOther.style.display = 'none';

			if (orderState == 'ACTIVE')
			{
				amount = document.getElementById('payment1_cash_total_amount');
				balance = Number(document.getElementById('OEH_remaing_balance').innerHTML);
				amount.value = formatAsMoney(balance - document.getElementById('new_payments_total').value);
			}
			else
			{
				amount = document.getElementById('payment1_cash_total_amount');
				amount.value = formatAsMoney(0);
			}

			reqField = document.getElementById('payment1_cash_total_amount');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment1_cash_total_number');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'false');
			}
			changePayment2('');

			onPaymentSelected();

			break;

		case "REFUND_CASH":
			unsetAutoAdjust();
			refundDiv.style.display = 'none';
			divGC.style.display = 'none';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'block';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
			divRef.style.display = 'none';
			divCredit.style.display = 'none';
			if (dpAdjust)
			{
				dpAdjust.style.display = 'none';
			}
			payment2.style.display = 'none';
			divDebitGiftCard.style.display = 'none';
			divRefOther.style.display = 'none';

			reqField = document.getElementById('payment1_refund_cash_total_amount');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment1_refund_cash_total_number');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'false');
			}
			changePayment2('');
			onPaymentSelected();

			break;

		case "CHECK":
			unsetAutoAdjust();
			refundDiv.style.display = 'none';
			divGC.style.display = 'none';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'block';
			divCC.style.display = 'none';
			divRef.style.display = 'none';
			divCredit.style.display = 'none';
			if (dpAdjust)
			{
				dpAdjust.style.display = 'none';
			}
			payment2.style.display = 'none';
			divDebitGiftCard.style.display = 'none';
			divRefOther.style.display = 'none';

			amount = document.getElementById('payment1_check_total_amount');
			balance = Number(document.getElementById('OEH_remaing_balance').innerHTML);

			amount.value = formatAsMoney(balance - document.getElementById('new_payments_total').value);

			reqField = document.getElementById('payment1_check_total_amount');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment1_check_total_number');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'false');
			}
			changePayment2('');
			onPaymentSelected();

			break;

		case "CREDIT":
			refundDiv.style.display = 'none';

			paymentsTotal = Number(document.getElementById('OEH_paymentsTotal').innerHTML);

			if (paymentsTotal != 0)
			{
				alert("'No Charge' can only be selected if there are no existing payments. It is intended to be used only when you are giving an order away.");
				paySel = document.getElementById('payment1_type');
				paySel.value = '';
				changePayment1('');
				return;
			}

			unsetAutoAdjust();
			divGC.style.display = 'none';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
			divRef.style.display = 'none';
			divCredit.style.display = 'block';
			if (dpAdjust)
			{
				dpAdjust.style.display = 'none';
			}
			payment2.style.display = 'none';
			divDebitGiftCard.style.display = 'none';
			divRefOther.style.display = 'none';

			changePayment2('');
			onPaymentSelected();

			break;

		case "CC":
			unsetAutoAdjust();
			refundDiv.style.display = 'none';
			divGC.style.display = 'none';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'block';
			divRef.style.display = 'none';
			divCredit.style.display = 'none';
			if (dpAdjust)
			{
				dpAdjust.style.display = 'none';
			}
			payment2.style.display = 'none';
			divDebitGiftCard.style.display = 'none';
			divRefOther.style.display = 'none';

			amount = document.getElementById('payment1_cc_total_amount');
			balance = Number(document.getElementById('OEH_remaing_balance').innerHTML);

			amount.value = formatAsMoney(balance - document.getElementById('new_payments_total').value);

			reqField = document.getElementById('payment1_ccNameOnCard');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment1_ccNumber');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment1_cc_total_amount');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment1_ccType');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment1_ccMonth');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment1_ccYear');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}

			changePayment2('');
			onPaymentSelected();

			break;

		case "GIFT_CARD":
			unsetAutoAdjust();
			refundDiv.style.display = 'none';
			divGC.style.display = 'none';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
			divRef.style.display = 'none';
			divCredit.style.display = 'none';
			if (dpAdjust)
			{
				dpAdjust.style.display = 'none';
			}
			payment2.style.display = 'none';
			divDebitGiftCard.style.display = 'block';
			divRefOther.style.display = 'none';

			reqField = document.getElementById('debit_gift_card_amount');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('debit_gift_card_number');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			if (orderState != 'ACTIVE')
			{
				payment2.style.display = 'block';
			}
			onPaymentSelected();

			break;

		case "DPADJUST":
			if (!dpAdjust)
			{
				return;
			} // should never happen

			if (!canAdjustDelayedPayment)
			{
				alert('Can not adjust delayed payment.\n\nDelayed payment status: ' + PendingDPOriginalStatus);
				autoAdjBox = document.getElementById('autoAdjust');
				if (!forceManualAutoAdjust)
				{
					autoAdjBox.checked = true;
					handleAutoAdjust(autoAdjBox);
				}
				return false;
			}
			refundDiv.style.display = 'none';
			divGC.style.display = 'none';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
			divRef.style.display = 'none';
			divCredit.style.display = 'none';
			payment2.style.display = 'none';
			dpAdjust.style.display = 'block';
			divDebitGiftCard.style.display = 'none';
			divRefOther.style.display = 'none';

			balance = Number(document.getElementById('OEH_remaing_balance').innerHTML);
			balance = formatAsMoney(balance - document.getElementById('new_payments_total').value);
			adjustPendingDelayedPayment(balance);

			changePayment2('');
			onPaymentSelected();

			break;

		case "REFERENCE_OTHER":
			refundDiv.style.display = 'none';
			divGC.style.display = 'none';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
			divRef.style.display = 'none';
			divCredit.style.display = 'none';
			payment2.style.display = 'none';
			divDebitGiftCard.style.display = 'none';
			divRefOther.style.display = 'block';

			document.getElementById('p1_card_num').innerHTML = externalPaymentAmountArray[selected_ref]['cc_number'];
			document.getElementById('p1_ref_id').innerHTML = selected_ref;

			reqField = document.getElementById('payment1_ref_total_amount');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
				balance = Number(document.getElementById('OEH_remaing_balance').innerHTML);
				reqField.value = formatAsMoney(balance - document.getElementById('new_payments_total').value);
			}

			changePayment2('');

			onPaymentSelected();

			break;
		case 'CC_REFUND':
			unsetAutoAdjust();
			refundDiv.style.display = 'block';
			divGC.style.display = 'none';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
			divRef.style.display = 'none';
			divCredit.style.display = 'none';
			if (dpAdjust)
			{
				dpAdjust.style.display = 'none';
			}
			payment2.style.display = 'none';
			divDebitGiftCard.style.display = 'none';
			divRefOther.style.display = 'none';

			onPaymentSelected();

			break;

		default:
			unsetAutoAdjust();
			refundDiv.style.display = 'none';
			divGC.style.display = 'none';
			divCash.style.display = 'none';
			divRefundCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
			divCredit.style.display = 'none';
			payment2.style.display = 'none';
			divRef.style.display = 'none';
			divRefOther.style.display = 'none';
			divDebitGiftCard.style.display = 'none';

			resetPendingDelayedPayment();
			if (dpAdjust)
			{
				dpAdjust.style.display = 'none';
			}

			onPaymentDeselected();

	}

	reportPaymentStatus(false);
}

function changePayment2(mySelect)
{

	reportPaymentStatus(true);

	var textBal = $("#bal_due").html();
	var balance = Number($("#bal_due").html());
	if (isNaN(balance.valueOf()))
	{
		balance = "";
	}
	if (balance.valueOf() == 0)
	{
		balance = "";
	}

	var divCash = document.getElementById('payment2_cash');
	var divRefundCash = document.getElementById('payment2_refund)_cash');
	var divCheck = document.getElementById('payment2_check');
	var divCC = document.getElementById('payment2_cc');
	var divref2 = document.getElementById('payment2_ref');

	var reqField;

	reqField = document.getElementById('payment2_gc_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_gc_payment_number');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ccNameOnCard');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ccNumber');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_cash_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_cc_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ref_total_amount');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ccType');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ccMonth');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}
	reqField = document.getElementById('payment2_ccYear');
	if (reqField)
	{
		reqField.setAttribute('data-dd_required', 'false');
	}

	var selected_ref = false;
	if (mySelect.substr(0, 4) == 'REF_')
	{
		selected_ref = mySelect.substr(4);
		mySelect = 'REFERENCE_OTHER';
	}

	switch (mySelect)
	{
		case "CASH":
			divCash.style.display = 'block';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
			divref2.style.display = 'none';

			reqField = document.getElementById('payment2_cash_total_amount');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}

			$("#payment2_cash_total_amount").val(balance);

			break;

		case "CHECK":
			divCash.style.display = 'none';
			divCheck.style.display = 'block';
			divCC.style.display = 'none';
			divref2.style.display = 'none';

			reqField = document.getElementById('payment2_check_total_amount');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}

			$("#payment2_check_total_amount").val(balance);

			break;

		case "CC":
			divCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'block';
			divref2.style.display = 'none';

			reqField = document.getElementById('payment2_ccNameOnCard');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment2_ccNumber');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment2_ccType');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment2_ccMonth');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			reqField = document.getElementById('payment2_ccYear');
			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}

			$("#payment2_cc_total_amount").val(balance);

			break;

		case "REFERENCE_OTHER":
			divCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
			divref2.style.display = 'block';

			document.getElementById('p2_card_num').innerHTML = externalPaymentAmountArray[selected_ref]['cc_number'];
			document.getElementById('p2_ref_id').innerHTML = selected_ref;

			reqField = document.getElementById('payment2_ref_total_amount');

			$("#payment2_ref_total_amount").val(balance);

			if (reqField)
			{
				reqField.setAttribute('data-dd_required', 'true');
			}
			break;

		default:
			reqField = document.getElementById('payment2_type');
			reqField.selectedItem = 0;
			divCash.style.display = 'none';
			divCheck.style.display = 'none';
			divCC.style.display = 'none';
	}

	reportPaymentStatus(false);
}

function validateRefTrans(field, limit)
{
	if (field.value > limit)
	{
		alert('Limited to ' + limit + '.');
		field.value = limit;
	}

	if (field.value < 0)
	{
		alert('Must be a positive number.');
		field.value = 0;
	}

	reportPaymentStatus(false);
}

function payAmountChange(value)
{
	reportPaymentStatus(false);
}

function resetPendingDelayedPayment(balance)
{
	var idElem = document.getElementById('PendingDP');

	if (idElem)
	{
		// get original amount
		var org_dp_amount = Number(document.getElementById('OriginalPendingDPAmount').value);

		document.getElementById('PendingDPAmount').value = formatAsMoney(org_dp_amount);

		document.getElementById('adj_dp_amount_up').value = formatAsMoney(org_dp_amount);
		document.getElementById('adjustment_amount_up').innerHTML = formatAsMoney(0);

		document.getElementById('adj_dp_amount_down').value = formatAsMoney(org_dp_amount);
		document.getElementById('adjustment_amount_down').innerHTML = formatAsMoney(0);

	}

}

function adjustPendingDelayedPayment(balance)
{
	var idElem = document.getElementById('PendingDP');

	if (idElem)
	{
		// get original amount
		var org_dp_amount = Number(document.getElementById('OriginalPendingDPAmount').value);

		var adjVal = org_dp_amount + (balance * 1);
		if (adjVal < 0)
		{
			balance = adjVal * -1;
			adjVal = 0;
		}
		else
		{
			balance = 0;
		}

		document.getElementById('PendingDPAmount').value = formatAsMoney(adjVal);
		var adjustmentAmount = getPendingDPAdjustment();

		if (adjustmentAmount > 0)
		{
			document.getElementById('adj_dp_amount_up').value = formatAsMoney(adjVal);
			document.getElementById('adjustment_amount_up').innerHTML = formatAsMoney(adjustmentAmount);
		}
		else
		{
			document.getElementById('adj_dp_amount_down').value = formatAsMoney(adjVal);
			document.getElementById('adjustment_amount_down').innerHTML = formatAsMoney(adjustmentAmount * -1);
		}

		return balance;
	}
	else
	{
		return balance;
	}

}

function getPendingDPAdjustment()
{
	var idElem = document.getElementById('PendingDP');
	if (idElem)
	{
		// get original amount
		var org_dp_amount = Number(document.getElementById('OriginalPendingDPAmount').value);
		var adj_dp_amount = Number(document.getElementById('PendingDPAmount').value);
		return formatAsMoney(adj_dp_amount - org_dp_amount);
	}
	else
	{
		return 0;
	}

	return 0;

}

function assignBalanceToRefTransactions(balance)
{
	var paymentCount = 0;

	var prefix = 'RT_';
	if (balance < 0)
	{
		prefix = 'Cr_RT_';
	}

	// clear any previous values
	for (ident in existingPaymentAmountArray)
	{
		var elname = 'RT_' + ident;
		paymentCount++;
		document.getElementById('RT_' + ident).value = "";
	}

	// clear any previous values
	for (ident in existingPaymentAmountArray)
	{

		// alert('nuulify');
		var elname = 'Cr_RT_' + ident;
		//	alert(elname);
		document.getElementById('Cr_RT_' + ident).value = "";
	}

	if (balance == 0)
	{
		reportPaymentStatus(false);
		return;
	}

	if (balance > 0)
	{
		//alert("doing it");
		// first, use store credit
		if (document.getElementById('use_store_credits') && document.getElementById('use_store_credits').checked)
		{
			var amountCredit = 0;
			var maxStoreCredit = Number($('#eoTotalStoreCredit').html());
			//alert("max: " + maxStoreCredit);

			if (balance > maxStoreCredit)
			{
				amountCredit = maxStoreCredit;
			}
			else
			{
				amountCredit = balance;
			}

			// alert("amountCredit: " + amountCredit);

			document.getElementById('store_credits_amount').value = formatAsMoney(amountCredit);
			balance -= amountCredit;

			// alert("balance: " + balance);
		}

		// second, look for a payment that could handle the full credit/debit
		for (ident in existingPaymentAmountArray)
		{
			if (balance <= existingPaymentAmountArray[ident].amount)
			{
				refTransField = document.getElementById(prefix + ident);
				refTransField.value = formatAsMoney(balance);
				reportPaymentStatus(false);
				return;
			}
		}

		// otherwise just assign to the first one
		for (ident in existingPaymentAmountArray)
		{
			document.getElementById(prefix + ident).value = formatAsMoney(balance);
			break;
		}
	}
	else
	{
		// balance < 0
		// first, look for a payment that could handle the full credit/debit
		for (ident in existingPaymentAmountArray)
		{
			var remainingFundsThisPayment = existingPaymentAmountArray[ident].amount;
			if (existingPaymentAmountArray[ident].current_refunded_amount)
			{
				remainingFundsThisPayment = existingPaymentAmountArray[ident].amount - existingPaymentAmountArray[ident].current_refunded_amount;
			}

			if (balance >= (remainingFundsThisPayment * -1))
			{
				refTransField = document.getElementById(prefix + ident);
				refTransField.value = formatAsMoney(Math.abs(balance));
				reportPaymentStatus(false);
				return;
			}
		}

		//if not found then we must spread the balance across multiple payments
		if (paymentCount > 1)
		{
			for (ident in existingPaymentAmountArray)
			{

				var remainingFundsThisPayment = existingPaymentAmountArray[ident].amount;
				if (existingPaymentAmountArray[ident].current_refunded_amount)
				{
					remainingFundsThisPayment = existingPaymentAmountArray[ident].amount - existingPaymentAmountArray[ident].current_refunded_amount;
				}

				var thisAmount = (remainingFundsThisPayment * -1);

				if (thisAmount < balance)
				{
					thisAmount = balance;
				}

				document.getElementById(prefix + ident).value = formatAsMoney(Math.abs(thisAmount));
				balance -= thisAmount;

				if (balance >= 0)
				{
					break;
				}
			}
		}
		else if (paymentCount == 1)
		{
			// just assign the max amount, the remainder will need to be paid through another method
			for (ident in existingPaymentAmountArray)
			{

				var remainingFundsThisPayment = existingPaymentAmountArray[ident].amount;
				if (existingPaymentAmountArray[ident].current_refunded_amount)
				{
					remainingFundsThisPayment = existingPaymentAmountArray[ident].amount - existingPaymentAmountArray[ident].current_refunded_amount;
				}

				document.getElementById(prefix + ident).value = formatAsMoney(remainingFundsThisPayment);
				break;
			}
		}
	}

	reportPaymentStatus(false);
}

function storeCreditAmountChange(val)
{
	var amountCredit = Number($('#eoTotalStoreCredit').html());

	if (amountCredit.valueOf() < val)
	{
		$('#store_credits_amount').val(amountCredit.valueOf());

	}

	payAmountChange();
}

function storeCreditClick(obj)
{
	var amountElem = document.getElementById('store_credits_amount');

	var amountCredit = Number($('#eoTotalStoreCredit').html());

	if (obj.checked && amountCredit > 0)
	{

		amountElem.disabled = false;

		var balance_elem = document.getElementById('OEH_remaing_balance');
		if (balance_elem)
		{
			balance = Number(balance_elem.innerHTML);
		}

		if (Number(amountElem.value) > balance && balance > 0)
		{

			amountElem.value = formatAsMoney(balance);
		}

		updateChangeList();

	}
	else
	{
		amountElem.disabled = true;
	}

	calculateTotal();

	reportPaymentStatus(false);

}

function reportPaymentStatus(ignorePayment2)
{
	var balance_elem = document.getElementById('OEH_remaing_balance');

	var balance = 0;
	if (balance_elem)
	{
		balance = Number(balance_elem.innerHTML);
	}

	var paymentsTotal = Number(0);

	// Total up any store credit
	if (document.getElementById('use_store_credits') && document.getElementById('use_store_credits').checked)
	{
		paymentsTotal = (paymentsTotal * 1) + (1 * formatAsMoney(document.getElementById('store_credits_amount').value));
	}

	var paySelElem = document.getElementById('payment1_type');

	if (!paySelElem)
	{
		return;
	}

	var paySel = paySelElem.value;

	var pay1Amount = Number(0);

	switch (paySel)
	{
		case "REFERENCE":
			for (ident in existingPaymentAmountArray)
			{
				pay1Amount += Number(document.getElementById('RT_' + ident).value);
			}
			break;

		case "CC":
			pay1Amount = Number(document.getElementById('payment1_cc_total_amount').value);
			break;

		case "CC_REFUND":
			var DirCreditCCAmount = Number(0);
			for (ident in existingPaymentAmountArray)
			{
				DirCreditCCAmount += Number(document.getElementById('Dir_Cr_RT_' + ident).value);
			}
			pay1Amount = DirCreditCCAmount * -1;
			break;

		case "CASH":
			pay1Amount = Number(document.getElementById('payment1_cash_total_amount').value);
			break;

		case "REFUND_CASH":
			pay1Amount = Number(document.getElementById('payment1_refund_cash_total_amount').value);
			pay1Amount = -pay1Amount;
			break;

		case "CHECK":
			pay1Amount = Number(document.getElementById('payment1_check_total_amount').value);
			break;

		case "GIFT_CERT":
			pay1Amount = Number(document.getElementById('payment1_gc_total_amount').value);
			break;

		case "GIFT_CARD":
			pay1Amount = Number(document.getElementById('debit_gift_card_amount').value);
			break;

		case "CREDIT":
			pay1Amount = Number(document.getElementById('debit_gift_card_amount').value);

			break;
	}

	if (ignorePayment2)
	{
		pay2Amount = Number(0);
	}
	else
	{
		paySel2 = document.getElementById('payment2_type').value;
		var pay2Amount = Number(0);
		switch (paySel2)
		{
			case "CC":
				pay2Amount = Number(document.getElementById('payment2_cc_total_amount').value);
				break;

			case "CASH":
				pay2Amount = Number(document.getElementById('payment2_cash_total_amount').value);
				break;

			case "CHECK":
				pay2Amount = Number(document.getElementById('payment2_check_total_amount').value);
				break;
		}

	}

	var storeCreditRefundElem = document.getElementById('storeCreditRefund');
	if (storeCreditRefundElem)
	{
		returnToStoreCredit = Number(storeCreditRefundElem.value * -1);
	}
	else
	{
		returnToStoreCredit = Number(0);
	}

	var cashRefund = document.getElementById('credit_to_customer_refund_cash_amount');
	if (cashRefund)
	{
		cashRefund = Number(cashRefund.value * -1);
	}
	else
	{
		cashRefund = Number(0);
	}

	// Credit Card Refunds
	var CreditCCAmount = Number(0);
	for (ident in existingPaymentAmountArray)
	{
		CreditCCAmount += Number(document.getElementById('Cr_RT_' + ident).value);
	}

	if (isNaN(CreditCCAmount))
	{
		CreditCCAmount = Number(0);
	}
	else
	{
		CreditCCAmount = Number(CreditCCAmount * -1);
	}

	var PenndingDPAdjustment = Number(getPendingDPAdjustment());

	// Note: refunds are now expressed as a negative. Payments total can be negative
	var paymentsTotal = Number(paymentsTotal + pay1Amount + pay2Amount +
	returnToStoreCredit + CreditCCAmount + PenndingDPAdjustment + cashRefund);

	$('#payment_help_msg').removeClass('overpaid');
	// current costs are greater than current payments - money owed to store

	if (orderState != 'CANCELLED')
	{

		var diff = Number((paymentsTotal * -1) + balance);

		if (diff < 0)
		{
			$('#payment_help_msg').addClass('overpaid').html('Overpaid by: $<span id="bal_due">' + formatAsMoney(Math.abs(diff))).show() + "</span>";
		}
		else if (diff > 0)
		{
			$('#payment_help_msg').html('Balance Due: $<span id="bal_due">' + formatAsMoney(Math.abs(diff))).show() + "</span>";
		}
		else
		{
			$('#payment_help_msg').html('').hide();
		}
	}
	else
	{

		var diff = Number(paymentsTotal + balance);

		if (diff > 0)
		{
			$('#payment_help_msg').addClass('overpaid').html('Overpaid by: $<span id="bal_due">' + formatAsMoney(Math.abs(diff))).show() + "</span>";
		}
		else if (diff < 0)
		{
			$('#payment_help_msg').html('Balance Due: $<span id="bal_due">' + formatAsMoney(Math.abs(diff))).show() + "</span>";
		}
		else
		{
			$('#payment_help_msg').html('').hide();
		}
	}

	/*
	 balance = Math.abs(balance);
	 //	alert("balance: " + balance);

	 var diff = new Number(paymentsTotal - balance);
	 //	alert("diff: " + diff);

	 $('#new_payments_total').val(paymentsTotal);

	 $('#payment_help_msg').removeClass('overpaid').html('<span id="bal_due">0.0</span>').show();


	 if (orderState == 'CANCELLED')
	 {
	 if (diff < 0)
	 {
	 $('#payment_help_msg').addClass('overpaid').html('Overpaid by: $<span id="bal_due">' + formatAsMoney(Math.abs(diff))).show() + "</span>";
	 }
	 else if (diff > 0)
	 {
	 $('#payment_help_msg').html('Balance Due: $<span id="bal_due">' + formatAsMoney(Math.abs(diff))).show() + "</span>";
	 }
	 else
	 {
	 $('#payment_help_msg').html('').hide();
	 }
	 }
	 else
	 {
	 if (paySel == 'CREDIT')
	 {
	 $('#payment_help_msg').html('<span id="bal_due">No Charge</span>').show();
	 }
	 else if (diff < 0)
	 {
	 $('#payment_help_msg').addClass('overpaid').html('Overpaid by: $<span id="bal_due">' + formatAsMoney(Math.abs(diff))).show() + "</span>";
	 }
	 else if (diff > 0)
	 {
	 $('#payment_help_msg').html('Balance Due: $<span id="bal_due">' + formatAsMoney(Math.abs(diff))).show() + "</span>";
	 }
	 else
	 {
	 $('#payment_help_msg').html('').hide();
	 }
	 }
	 */
	updateChangeList();
}

function toggle(obj)
{
	var el = document.getElementById(obj);
	el.style.display = (el.style.display != 'none' ? 'none' : '' );
}

var target_prefix = "payment1_";
var scanPosition = 0;
var charIndex = 0;
var codes = [];

function keyHandler2(evt)
{

	evt = (evt) ? evt : event;
	var charCode = (evt.charCode) ? evt.charCode : ((evt.which) ? evt.which : evt.keyCode);

	if (charCode == 13)
	{
		return false;
	}

	codes[charIndex++] = String.fromCharCode(charCode);

	if ((String.fromCharCode(charCode) == '%') && (scanPosition == 0))
	{
		scanPosition = 1; //start of data
	}
	else if ((String.fromCharCode(charCode) == 'B') && (scanPosition == 1))
	{
		scanPosition = 2; // start of track 1
	}
	else if ((String.fromCharCode(charCode) == '?') && (scanPosition == 2))
	{
		scanPosition = 3;  // end of track 1
	}
	else if ((String.fromCharCode(charCode) == '?') && (scanPosition == 3))
	{
		scanPosition = 0;  // end of track 2
		charIndex = 0;
		handleSwipeCompletion();
	}
}

function endScanHandling()
{
	document.getElementById('scanArea').style.display = "none";
	codes.length = 0;
	scanPosition = 0;
	charIndex = 0;
	document.getElementById('hidden_text_store').value = "";

	document.onkeypress = OMDefaultKeyHandler;

}

function prepareForCCSwipe(paymentNumber)
{

	if (paymentNumber == 2)
	{
		target_prefix = 'payment2_';
	}
	else
	{
		target_prefix = 'payment1_';
	}

	document.onkeypress = keyHandler2;
	document.getElementById('scanArea').style.display = "block";
	document.getElementById('hidden_text_store').focus();
	document.getElementById('hidden_text_store').value = "";

}

function handleSwipeCompletion()
{
	var receivedText = codes.join("");

	var p = new SwipeParserObj(receivedText);
	var parsedVersion = p.dump();

	var re_visa = /^4[0-9]{12}([0-9]{3})?$/;
	var re_mc = /^5[1-5][0-9]{14}$/;
	var re_amex = /^3[47][0-9]{13}$/;
	var re_disc = /^6011[0-9]{12}$/;

	var card_type = false;

	if (re_visa.test(p.account))
	{
		card_type = 'Visa';
	}
	else if (re_mc.test(p.account))
	{
		card_type = 'Mastercard';
	}
	else if (re_amex.test(p.account))
	{
		card_type = 'American Express';
	}
	else if (re_disc.test(p.account))
	{
		card_type = 'Discover';
	}

	if (card_type)
	{
		document.getElementById(target_prefix + 'ccType').value = card_type;
	}

	document.getElementById(target_prefix + 'ccNumber').value = p.account;
	document.getElementById(target_prefix + 'ccNameOnCard').value = p.firstname + ' ' + p.surname;
	document.getElementById(target_prefix + 'ccMonth').value = p.exp_month;

	var exp_year = p.exp_year;
	if (exp_year.length == 2)
	{
		document.getElementById(target_prefix + 'ccYear').value = exp_year;
	}
	else if (exp_year.length == 4)
	{
		document.getElementById(target_prefix + 'ccYear').value = exp_year.substr(2);
	}

	endScanHandling();
}