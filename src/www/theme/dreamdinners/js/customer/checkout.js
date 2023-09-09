cart_subtotal = 0;

function handle_ltd_round_up()
{
	$(document).on('change', '#add_ltd_round_up', function (e) {

		if ($(this).is(':checked'))
		{
			$('#ltd_round_up_select').prop('disabled', false);

			if (USER_PREFERENCES.LTD_AUTO_ROUND_UP.value == '' && (!$.cookie('ltdru') || $.cookie('ltdru') != session_id))
			{
				modal_message({
					title: 'My Preferences',
					message: 'Would you like to automatically round up every time you order? You can edit this setting any time in <span class="font-weight-bold">My Preferences</span>.<div class="text-center; mt-1"><select id="ltd-round_up_opt_in"><option>Select a Round Up value</option><option value="1">Nearest Dollar</option><option value="2">2 Dollars</option><option value="5">5 Dollars</option><option value="10">10 Dollars</option><option value="35">35 Dollars</option><option value="54">54 Dollars</option></select></div>',
					modal: true,
					noOk: true,
					closeOnEscape: false,
					open: function (event, ui) {
						$(this).parent().find('.ui-dialog-titlebar-close').hide();

						var rup_dialog = $(this);

						$('#ltd-round_up_opt_in').on('change', function (e) {

							var rup_value = $(this).val();

							if (rup_value > 0)
							{
								modal_message({
									div_id: 'rup_confirm',
									title: 'My Preferences',
									message: 'You have chosen to set your auto Round Up to ' + ((rup_value == 1) ? 'the nearest dollar.' : '$' + rup_value),
									confirm: function () {
										$(rup_dialog).remove();

										set_user_pref('LTD_AUTO_ROUND_UP', rup_value);

										if (ltd_round_up_value == 0 && rup_value != 1 || ($('#ltd_round_up_select option:selected').index() == 0 && !$.cookie('ltdru')))
										{
											modal_message({
												title: 'My Preferences',
												message: 'Preference saved. Would you like to use your selected Round Up value on this order? This can be reviewed and edited.',
												modal: true,
												noOk: true,
												closeOnEscape: false,
												open: function (event, ui) {
													$(this).parent().find('.ui-dialog-titlebar-close').hide();
												},
												buttons: {
													"Yes": function () {
														if (rup_value == 1)
														{
															$('#ltd_round_up_select option:eq(0)').prop('selected', true).trigger('change');
														}
														else
														{
															$('#ltd_round_up_select').val(rup_value).trigger('change');
														}
													},
													"No": function () {
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
						"No": function () {
							set_user_pref('LTD_AUTO_ROUND_UP', 0);
						},
						"Ask Again Later": function () {
							$.cookie('ltdru', session_id);
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

	});

	$('#ltd_round_up_select').on('change', function (e) {

		var round_up_val = $(this).val();

		if (round_up_val != null)
		{
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

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'cart_add_payment',
					payment_type: 'ltd_round_up',
					ltd_round_up_value: round_up_val
				},
				success: function (json) {
					if (json.processor_success)
					{

					}
					else
					{
						modal_message({
							title: 'Error',
							message: json.processor_message
						});
					}
				},
				error: function (objAJAXRequest, strError) {
					//	console.log('OM Intense logging: ' + message);

					modal_message({
						title: 'Error',
						message: 'Unexpected error: ' + strError
					});
				}
			});
		}

		updateFinancials();
	});

	if (!$('#add_ltd_round_up').is(':checked') && USER_PREFERENCES.LTD_AUTO_ROUND_UP.value > 0 && ltd_round_up_value == null)
	{
		$('#add_ltd_round_up').prop('checked', true);

		if (USER_PREFERENCES.LTD_AUTO_ROUND_UP.value > 1)
		{
			$('#ltd_round_up_select').val(USER_PREFERENCES.LTD_AUTO_ROUND_UP.value).prop('disabled', false).trigger('change');
		}
		else
		{
			$('#ltd_round_up_select option:first-child').attr('selected', true);
			$('#ltd_round_up_select').prop('disabled', false).trigger('change');

		}
	}
	else if ($('#add_ltd_round_up').is(':checked'))
	{
		$('#ltd_round_up_select').prop('disabled', false).trigger('change');
	}
}

function handle_checkout_delayed_payment()
{
	if (USER_PREFERENCES)
	{
		get_user_pref('TC_DELAYED_PAYMENT_AGREE', null, function (json) {

			if (json.user_preferences['TC_DELAYED_PAYMENT_AGREE'].value == 1 || json.user_preferences['TC_DELAYED_PAYMENT_AGREE'].value == '1')
			{
				return;
			}

			$('#is_store_specific_flat_rate_delayed_payment1, #is_flat_rate_delayed_payment1, #is_delayed_payment1').on('click', function (e) {

				modal_message({
					title: lang.en.tc.terms_and_conditions,
					message: lang.en.tc.delayed_payment,
					modal: true,
					noOk: true,
					closeOnEscape: false,
					open: function (event, ui) {
						$(this).parent().find('.ui-dialog-titlebar-close').hide();
					},
					buttons: {
						"Agree": function () {

							$('#is_store_specific_flat_rate_delayed_payment1, #is_flat_rate_delayed_payment1, #is_delayed_payment1').unbind("click");

							set_user_pref('TC_DELAYED_PAYMENT_AGREE', 1);

						},
						"Decline": function () {

							$('#is_store_specific_flat_rate_delayed_payment0, #is_flat_rate_delayed_payment0, #is_delayed_payment0').click();

							modal_message({
								title: lang.en.tc.terms_and_conditions,
								message: lang.en.tc.delayed_payment_decline
							});

							set_user_pref('TC_DELAYED_PAYMENT_AGREE', 0);

						}
					}
				});

			});

		});
	}
}

function handle_special_instructions()
{
	$(document).on('keyup', '#special_insts', function (e) {

		if ($(this).val() != strip_tags($(this).val()))
		{
			$(this).val(strip_tags($(this).val()));
		}

	});
}

function submit_gift_card_purchase()
{
	var field = $('<input></input>');
	field.attr("type", "hidden");
	field.attr("name", "checkout_submit");
	field.attr("value", "true");
	$("#gift_card_checkout").append(field);

	$("#gift_card_checkout").submit();
}


function submit_order()
{
	create_and_submit_form({
		action: '/checkout',
		input: ({
			special_insts: $('#special_insts').val(),
			customers_terms: $('#customers_terms').val()
		})
	});
}

function handlePlatePointsDiscount(val)
{
	inVal = val;

	if ((isNaN(val) || val <= 0) && val != ".")
	{
		val = 0;
	}

	var cap = maxPPCredit;
	if (cap > maxPPDeduction)
	{
		cap = maxPPDeduction;
	}

	if (val > cap)
	{
		val = cap;
	}

	if (val < 0)
	{
		val = 0;
	}

	if (val != inVal || isNaN(inVal) || inVal == "")
	{
		$('#plate_points_discount').val(val);
	}

}

function editSaveAllPlatePointsCredits()
{
	$('#plate_points_discount').val(formatAsMoney(maxPPDeduction));
	editSavePlatePointsCredits(false);
}




function editSavePlatePointsCredits(force)
{
	if (force)
	{
		$("#max_plate_points_deduction").html(maxPPDeduction);
	}

	var val = $('#plate_points_discount').val();

	if (typeof val !== 'undefined')
	{
		var cap = maxPPCredit;
		if (cap > maxPPDeduction)
		{
			cap = maxPPDeduction;
		}

		if (val > cap)
		{
			val = cap;
		}

		if (val < 0)
		{
			val = 0;
		}

		$('#plate_points_discount').val(formatAsMoney(val));

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'cart_modify_plate_points_credits',
				op: 'modify',
				new_credit_value: val
			},
			success: function (json) {
				if (json.processor_success)
				{

					// update subtotal
					$('#sum_checkout_total-subtotal').html(formatAsMoney(json.orderInfo.grand_total));

					$('#checkout_total-tax').html(formatAsMoney(json.orderInfo.subtotal_all_taxes));

					if (val > 0)
					{
						$('#row-discount_plate_points').showFlex();
					}
					else
					{
						$('#row-discount_plate_points').hideFlex();
					}

					// update coupon total
					$('#checkout_total-coupon').html(formatAsMoney(json.orderInfo.coupon_code_discount_total));

					avgCostPerServingEntreeCost -= json.orderInfo.coupon_code_discount_total;

					$("#checkout_total-points_discount_total").html(formatAsMoney(json.orderInfo.points_discount_total));
					$("#checkout_total-membership_discount").html(formatAsMoney(json.orderInfo.membership_discount));



					// calculate the balance
					can_checkout();
				}
				else
				{
					modal_message({
						title: 'Error',
						message: json.processor_message
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				modal_message({
					title: 'Error',
					message: 'Unexpected error: ' + strError
				});
			}
		});
	}

}

function run_as_guest()
{
	create_and_submit_form({
		action: '/checkout',
		input: ({
			run_as_guest: "true"
		})
	});
}

function updateFinancials()
{
	var foodCost = 0;
	var bag_fee = 0;

	if ($('#checkout_total-food').length)
	{
		foodCost = Number($('#checkout_total-food').html());
	}

	var giftCardCost = 0;
	$('[id^="checkout_total-giftcard_purchase"]').each(function () {
		var thisCost = Number($(this).html());
		giftCardCost += thisCost;
	});

	var service_fee = 0;
	if ($('#checkout_total-service_fee').length)
	{
		service_fee = Number($('#checkout_total-service_fee').html());
	}

	var delivery_fee = 0;
	if ($('#checkout_total-delivery_fee').length)
	{
		delivery_fee = Number($('#checkout_total-delivery_fee').html());
	}


	var totalTax = 0;
	if ($('#checkout_total-tax').length)
	{
		totalTax = Number($('#checkout_total-tax').html());
	}

	var prediscountFoodTotal = foodCost;

	// Note: coupon code and points discount are the only discounts that can affect price
	// without a page refresh. If there is a change we need to recalculate tax
	var couponDiscount = 0;
	if ($('#checkout_total-coupon').length)
	{
		couponDiscount = Number($('#checkout_total-coupon').html());
	}

	var volumeDiscount = 0;
	if ($('#checkout_total-volume_discount').length)
	{
		volumeDiscount = Number($('#checkout_total-volume_discount').html());
	}

	var preferredDiscount = 0;
	if ($('#checkout_total-preferred_discount').length)
	{
		preferredDiscount = Number($('#checkout_total-preferred_discount').html());
	}

	var dreamRewardsDiscount = 0;
	if ($('#checkout_total-dream_rewards_discount').length)
	{
		dreamRewardsDiscount = Number($('#checkout_total-dream_rewards_discount').html());
	}

	var sessionDiscount = 0;
	if ($('#checkout_total-session_discount_total').length)
	{
		sessionDiscount = Number($('#checkout_total-session_discount_total').html());
	}

	var pointsDiscount = 0;
	if ($('#checkout_total-points_discount_total').length)
	{
		pointsDiscount = Number($('#checkout_total-points_discount_total').html());
	}

	var membershipDiscount = 0;
	if ($('#checkout_total-membership_discount').length)
	{
		membershipDiscount = Number($('#checkout_total-membership_discount').html());
	}

	var bag_fee = 0;
	if ($('#checkout_total-bag_fee').length)
	{
		bag_fee = Number($('#checkout_total-bag_fee').html());
	}

	var customization_fee = 0;
	if ($('#checkout_total-customization_fee').length)
	{
		customization_fee = Number($('#checkout_total-customization_fee').html());
	}

	var foodCost = prediscountFoodTotal - (couponDiscount + preferredDiscount + volumeDiscount + dreamRewardsDiscount + sessionDiscount + pointsDiscount + membershipDiscount);
	var grand_total = Number(formatAsMoney(foodCost + giftCardCost + service_fee + delivery_fee + bag_fee + totalTax + customization_fee));

	$('#sum_checkout_total-subtotal').html(formatAsMoney(grand_total));

	var paymentsTotal = 0;
	$('[id^="checkout_total_payment-"]').each(function () {

		if ($(this).is(':visible'))
		{
			paymentsTotal += Number($(this).html());
		}
	});

	if (paymentsTotal > foodCost + service_fee + delivery_fee + bag_fee + totalTax)
	{
		//more store credit than food cost so we must cap the store credit that can be used
		paymentsTotal = foodCost + service_fee + delivery_fee+ bag_fee + totalTax;
		$('[id^="checkout_total_payment-credits"]').html(formatAsMoney(paymentsTotal));
	}

	if (giftCardCost > 0)
	{
		//	$('[id^="gc_checkout_total_adj_payment-creditcard-"]').html(formatAsMoney(giftCardCost ));
	}

	var credit_card_food_total = grand_total - paymentsTotal - giftCardCost;

	var round_up_donation = 0;

	var subtotal_round_up = Math.ceil(credit_card_food_total + giftCardCost);
	var subtotal_round_up_diff = parseFloat((subtotal_round_up - (credit_card_food_total + giftCardCost)).toFixed(2));

	if (subtotal_round_up_diff == 0)
	{
		subtotal_round_up_diff = 1.00;
	}

	if ($('#round_up_nearest_dollar').val() != subtotal_round_up_diff)
	{
		$('#round_up_nearest_dollar').val(subtotal_round_up_diff).text('$ ' + formatAsMoney(subtotal_round_up_diff));
		$('#add_ltd_round_up').trigger('change');
	}

	if ($('#add_ltd_round_up').is(':checked'))
	{
		if ($('#ltd_round_up_select').find(':selected').attr('id') == 'round_up_nearest_dollar')
		{
			round_up_donation = subtotal_round_up_diff;
		}
		else
		{
			round_up_donation = parseFloat($('#ltd_round_up_select').val());
		}
	}

	$('[id^="credit_card_amount"]').html(formatAsMoney(credit_card_food_total + giftCardCost + round_up_donation));
	$('#debit_credit_card_amount').html(formatAsMoney(credit_card_food_total + giftCardCost + round_up_donation));

	var hasEnoughNonCCFunds = false;

	var isEditOrder = false;
	if ($('#isEditDeliveredOrder').length)
	{
		isEditOrder = true;
	}


	if( isEditOrder ){
		credit_card_food_total = $('#cc_amount_diff').html();

	}

	if (credit_card_food_total == 0 && giftCardCost == 0)
	{
		$('[id^="cc"]').prop('disabled', true);
		$('#billing_postal_code').prop('disabled', true);
		$('#billing_address').prop('disabled', true);
		$('#addCreditCard').prop('disabled', true);
		$('#save_cc_as_ref').prop('disabled', true);

		if ($('#is_store_specific_flat_rate_delayed_payment1').length)
		{
			$('#is_store_specific_flat_rate_delayed_payment1').prop('disabled', true);
			$('#is_store_specific_flat_rate_delayed_payment0').prop('disabled', true);
		}

		$('#enough_funds_alert').show();

		hasEnoughNonCCFunds = true;

	}
	else
	{
		$('[id^="cc"]').prop('disabled', false);
		$('#billing_postal_code').prop('disabled', false);
		$('#billing_address').prop('disabled', false);
		$('#addCreditCard').prop('disabled', false);
		$('#save_cc_as_ref').prop('disabled', false);

		if ($('#is_store_specific_flat_rate_delayed_payment1').length)
		{
			$('#is_store_specific_flat_rate_delayed_payment1').prop('disabled', false);
			$('#is_store_specific_flat_rate_delayed_payment0').prop('disabled', false);
		}

		$('#enough_funds_alert').hide();
	}

	//$('#debit_credit_card_amount').html(formatAsMoney(credit_card_food_total + giftCardCost));

	var cost_per_serving = formatAsMoney(avgCostPerServingEntreeCost / avgCostPerServingEntreeServings);

	$('#checkout-cost_per_serving').html(cost_per_serving);

	$('#sum_checkout_total-balance').html(formatAsMoney(0));

	return hasEnoughNonCCFunds;
}

function can_checkout()
{
	handleCardRefChange();

	var hasEnoughNonCCFunds = updateFinancials();

	if (is_discounts_page)
	{
		if (hasEnoughNonCCFunds)
		{
			$('#checkoutOption').addClass('show');
			$('#toPaymentOption').removeClass('show');

			// if balance is 0 and terms and conditions have been checked, enable checkout
			if ($('#customers_terms').is(':checked'))
			{
				$('#complete_order').attr('disabled', false);
			}
			else
			{
				$('#complete_order').attr('disabled', true);
			}

			$('#customers_terms').attr('required', 'required');
			$("#to_payment_or_checkout_form").prop("action", '/checkout');

		}
		else
		{
			$('#checkoutOption').removeClass('show');
			$('#toPaymentOption').addClass('show');

			$("#to_payment").attr('disabled', false);

			$('#customers_terms').attr('required', false);
			$("#to_payment_or_checkout_form").prop("action", '/payment');

		}

	}
	else
	{
		// if balance is 0 and terms and conditions have been checked, enable checkout
		if ($('#customers_terms').is(':checked'))
		{
			$('#complete_order').attr('disabled', false);
		}
		else
		{
			$('#complete_order').attr('disabled', true);
		}
	}
}

function remove_item_payment(settings)
{
	if (settings.item == 'food')
	{
		remove_food();
	}

	if (settings.item == 'nonfood')
	{
		// remove_item_nonfood(settings);
	}

	if (settings.item == 'credits')
	{
		removeAllStoreCredit(settings);
	}

	if (settings.item == 'coupon')
	{
		remove_payment_coupon();
	}

	if (settings.item == 'giftcard')
	{
		remove_payment(settings);
	}

	if (settings.item == 'giftcard_purchase')
	{
		remove_gift_card_purchase(settings);
	}

	if (settings.item == 'creditcard')
	{
		remove_payment(settings);
	}

	if (settings.item == 'dinner_dollars')
	{
		remove_dinner_dollars(settings);
	}

	if (settings.item == 'cc_ref')
	{
		remove_credit_card_reference(settings);
	}


	can_checkout();
}

function remove_food()
{
	var runAsGuest = "false";

	if (allowGuest)
	{
		runAsGuest = "true";
	}

	create_and_submit_form({
		action: '/checkout',
		input: ({
			remove: "food",
			run_as_guest: runAsGuest

		})
	});
}

function rewardsCreditClick(obj)
{
	if (obj.checked)
	{
		add_all_store_credit();
	}
	else
	{
		var settings = {
			item: "credits"
		};

		removeAllStoreCredit(settings);
	}

	can_checkout();
}

function remove_gift_card_purchase(settings)
{
	var runAsGuest = "false";

	if (allowGuest)
	{
		runAsGuest = "true";
	}

	create_and_submit_form({
		action: '/checkout-gift-card',
		input: ({
			remove: "gift_card_purchase",
			gcoid: settings.item_number,
			run_as_guest: runAsGuest
		})
	});
}


function remove_dinner_dollars()
{

	$('#plate_points_discount').val(formatAsMoney(0));

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_modify_plate_points_credits',
			op: 'modify',
			new_credit_value: 0
		},
		success: function (json) {
			if (json.processor_success)
			{

				// update subtotal
				$('#sum_checkout_total-subtotal').html(formatAsMoney(json.orderInfo.grand_total));

				$('#checkout_total-tax').html(formatAsMoney(json.orderInfo.subtotal_all_taxes));

				$('#row-discount_plate_points').hideFlex();

				// update coupon total
				$('#checkout_total-coupon').html(formatAsMoney(json.orderInfo.coupon_code_discount_total));

				avgCostPerServingEntreeCost -= json.orderInfo.coupon_code_discount_total;

				$("#checkout_total-points_discount_total").html(formatAsMoney(json.orderInfo.points_discount_total));
				$("#checkout_total-membership_discount").html(formatAsMoney(json.orderInfo.membership_discount));

				// calculate the balance
				can_checkout();
			}
			else
			{
				modal_message({
					title: 'Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			modal_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});
		}
	});
}



function remove_payment_coupon()
{

	var service_fee = Number($('#checkout_total-service_fee').html());

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_remove_payment',
			payment_type: 'coupon',
			page: 'checkout'
		},
		success: function (json) {
			if (json.processor_success)
			{
				// restore coupon input
				$('#add_coupon_code').prop('disabled', false);
				$('#add_coupon_add').prop('disabled', false);
				$('#checkout_total-tax').html(json.orderInfo.subtotal_all_taxes);

				// update coupon total
				$('#checkout_title-coupon_code').html('');

				if( json.is_edit_order !== 'undefined' && json.is_edit_order == true ) {
					location.reload();
				}

				var couponAmount = Number($('#checkout_total-coupon').html());

				avgCostPerServingEntreeCost += couponAmount;

				// update coupon total
				$('#checkout_total-coupon').html('0.00');

				// hide coupon row
				$('#row-coupon').slideUp();

				var couponCodeVal = $('#add_coupon_code').val().toUpperCase();

				if (coupon.limit_to_mfy_fee == '1')
				{
					maxPPDeduction += service_fee;
					editSavePlatePointsCredits(true);

					if (maxPPCredit > 0)
					{
						$("#edit_plate_points").attr("disabled", false);
					}

				}

				coupon = false;

				// can we checkout?
				can_checkout();
			}
			else
			{
				modal_message({
					title: 'Error',
					message: json.processor_message
				});
			}

		},
		error: function (objAJAXRequest, strError) {
			modal_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});
		}
	});
}

function update_special_instructions()
{
	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_item_processor',
			op: 'store_inst',
			special_inst: $('#special_insts').val()
		},
		success: function (json) {
			if (json.processor_success)
			{
				// nothing to do
			}
			else
			{
				modal_message({
					title: 'Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			modal_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});
		}
	});
}

function update_bag_opt_out()
{

	let opt_out = 0;
	if (supports_bag_fee)
	{
		if ($("#opted_to_bring_bags").is(":checked"))
		{
			opt_out = 1;
		}
	}

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_bag_fee',
			op: 'set_opt_out',
			opt_out: opt_out,
		},
		success: function (json) {
			if (json.processor_success)
			{
				// update subtotal
				$('#sum_checkout_total-subtotal').html(formatAsMoney(json.orderInfo.grand_total));
				$('#checkout_total-tax').html(formatAsMoney(json.orderInfo.subtotal_all_taxes));

				if ($("#opted_to_bring_bags").is(":checked"))
				{
					$("#checkout_total-bag_fee_info").html("(You will provide bags)");
					$("#checkout_total-bag_fee").html(formatAsMoney(0));
				}
				else
				{
					bag_fee = default_bag_fee * number_bags_required;
					let bagLabel = number_bags_required > 1 ? ' bags' :' bag';
					let cbagLabel = number_bags_required > 1 ? ' Bags' :' Bag';

					$("#checkout_total-bag_fee_info").html('(' +number_bags_required +bagLabel+" * $" + formatAsMoney(default_bag_fee) + ')');
					$("#checkout_total-bag_fee").html(formatAsMoney(bag_fee));
					$("#bag_text").html(number_bags_required + cbagLabel);


				}

				// calculate the balance
				can_checkout();

			}
			else
			{
				modal_message({
					title: 'Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			modal_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});
		}
	});
}


function add_payment_coupon()
{
	var add_coupon_code = $('#add_coupon_code').val().toUpperCase();
	var service_fee = Number($('#checkout_total-service_fee').html());

	if (!add_coupon_code)
	{
		modal_message({
			title: 'Error',
			message: 'Please enter a promo code.'
		});
	}
	else
	{
		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'cart_add_payment',
				payment_type: 'coupon',
				page: 'checkout',
				coupon_code: add_coupon_code,
			},
			success: function (json) {
				if (json.processor_success)
				{
					coupon = json.coupon;

					// disable input form
					$('#add_coupon_code').prop('disabled', true);
					$('#add_coupon_add').prop('disabled', true);

					// update subtotal
					$('#sum_checkout_total-subtotal').html(json.orderInfo.grand_total);

					if(typeof json.edit_order_diff !== 'undefined'){
						$('#cc_amount_diff').html(json.balance);
						$('#original_order_total').html(json.originalOrderInfo.grand_total);
						$('.edit-order-field').removeClass( "collapse" );
					}


					$('#checkout_total-tax').html(json.orderInfo.subtotal_all_taxes);

					// update coupon total
					$('#checkout_title-coupon_code').html(json.coupon_title);

					// update coupon total
					$('#checkout_total-coupon').html(formatAsMoney(json.orderInfo.coupon_code_discount_total));

					avgCostPerServingEntreeCost -= json.orderInfo.coupon_code_discount_total;

					// show coupon row
					$('#row-coupon').showFlex();

					if (coupon.limit_to_mfy_fee == '1' && maxPPDeduction > 0)
					{
						var dd_val = $('#plate_points_discount').val();
						if (maxPPDeduction >= service_fee && maxPPDeduction - dd_val < service_fee)
						{
							modal_message({
								title: 'Error',
								message: 'The Service fee is already discounted by using Dinner Dollars. If you wish to use this coupon please lower the amount of Dinner Dollars applied.'
							});

							remove_payment_coupon();
						}
						else
						{
							var service_fee = Number($('#checkout_total-service_fee').html());

							maxPPDeduction = maxPPDeduction - service_fee.valueOf();
							if (maxPPDeduction < 0)
							{
								maxPPDeduction = 0;
							}

							maxPPDeduction = formatAsMoney(maxPPDeduction);
							editSavePlatePointsCredits(true);
						}
					}

					// calculate the balance
					can_checkout();

				}
				else
				{
					modal_message({
						title: 'Error',
						message: json.processor_message
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				modal_message({
					title: 'Error',
					message: 'Unexpected error: ' + strError
				});
			}
		});
	}
}

function calculateMaxNewGiftCardPayment(newPaymentNumber)
{
	var grandtotal = Number($('#sum_checkout_total-subtotal').html());
	var existingPaymentTotal = 0;

	$('[id^="row-giftcard-"]').each(function () {

		var PaymentNumber = $(this).data("gc_number");
		var PaymentAmount = $(this).data("gc_amount");

		if (PaymentNumber != newPaymentNumber)
		{
			existingPaymentTotal += (Number(formatAsMoney(PaymentAmount)));
		}
	});

	if ($('#checkout_total_payment-credits').length)
	{
		existingPaymentTotal += (Number($('#checkout_total_payment-credits').html()).valueOf());
	}

	var maxNewPayment = grandtotal.valueOf() - existingPaymentTotal;

	return maxNewPayment;

}

function get_gift_card_balance()
{
	var gc_number = $('#debit_gift_card_number').val();
	gc_number = gc_number.trim();

	if (!gc_number)
	{
		modal_message({
			title: 'Error',
			message: 'Please enter a gift card number.'
		});
	}
	else
	{
		$.ajax({
			url: 'ddproc.php',
			type: 'GET',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'giftCardBalance',
				card_number: gc_number,
				output: 'json'
			},
			success: function (json) {
				if (json.processor_success)
				{
					modal_message({
						title: 'Gift Card Balance',
						message: "Balance for card number <b>" + json.card_number + "</b> is <b>" + json.card_balance + "</b>"
					});
				}
				else
				{
					modal_message({
						title: 'Gift Card Balance Error',
						message: json.processor_message
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				modal_message({
					title: 'Error',
					message: 'Unexpected error: ' + strError
				});
			}
		});
	}
}

function getGiftCardNumberAndAmount()
{
	var gc_number = $('#debit_gift_card_number').val();
	var new_amount = $('#debit_gift_card_amount').val();

	var returnData =
		{
			dataValid: true,
			gc_number: gc_number,
			gc_amount: new_amount,
			is_update: true
		};

	var didHandleDuplicate = false;

	// check if this card number was added already
	// Note: this is not adequate for handling rapid clicking
	// Additional checks are done in do_add_gift_card
	$('#giftcard_container').find('[data-gc_number="' + gc_number + '"]').each(function () {

		didHandleDuplicate = true;
		returnData.replacementObj = this;
		modal_message({
			title: 'Attention',
			message: 'You have already entered this gift card number. If you proceed the current amount will be replaced by the amount you are now submitting.',
			modal: true,
			confirm: function () {
				do_add_gift_card(returnData);
			},
			cancel: function () {
				return null;
			}
		});
	});

	if (!didHandleDuplicate)
	{
		do_add_gift_card(returnData);
	}
}

function do_add_gift_card(transaction_data)
{

	var is_processing = $('#debit_gift_card_number').data('processing');

	if (!transaction_data || is_processing)
	{
		return;
	}

	if(transaction_data.gc_number){
		transaction_data.gc_number = transaction_data.gc_number.trim();
	}

	if (!transaction_data.gc_number)
	{
		modal_message({
			title: 'Error',
			message: 'Please enter a gift card number.'
		});
	}
	else if (!transaction_data.gc_amount)
	{
		modal_message({
			title: 'Error',
			message: 'Please enter a gift card amount.'
		});
	}
	else
	{
		var replacingID = 0;

		if (transaction_data.replacementObj)
		{
			replacingID = transaction_data.replacementObj.id.split("-")[2];
		}

		$('#debit_gift_card_number').data('processing', true);


		$.ajax({
			url: 'ddproc.php',
			type: 'GET',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'giftCardBalance',
				card_number: transaction_data.gc_number,
				output: 'json'
			},
			success: function (json) {
				if (json.processor_success)
				{

					if (json.card_balance == "Invalid Card")
					{
						modal_message({
							title: 'Gift Card Error',
							message: 'The card number is invalid.'
						});
					}
					else if ((json.card_balance * 1) < (transaction_data.gc_amount * 1))
					{
						modal_message({
							title: 'Gift Card Error',
							message: 'There are not enough funds on the gift card for this amount. Available balance: ' + json.card_balance
						});
					}
					else
					{
						var maxNewPayment = calculateMaxNewGiftCardPayment(transaction_data.gc_number);
						if (transaction_data.gc_amount > maxNewPayment)
						{
							transaction_data.gc_amount = maxNewPayment.toString();
						}

						$.ajax({
							url: 'ddproc.php',
							type: 'POST',
							timeout: 20000,
							dataType: 'json',
							data: {
								processor: 'cart_add_payment',
								payment_type: 'gift_card',
								card_number: transaction_data.gc_number,
								amount: transaction_data.gc_amount,
								c_total: $('#sum_checkout_total-subtotal').html(),
								current_subtotal: $('#sum_checkout_total-subtotal').html(),
								original_order_total: $('#original_order_total').html(),
								payment_id: replacingID,
								output: 'json'
							},
							success: function (json) {
								if (json.processor_success)
								{
									//var payment_id = json.payment_id;
									if (transaction_data.replacementObj)
									{
										$(transaction_data.replacementObj).remove();
									}

									$('#debit_gift_card_number').val('');
									$('#debit_gift_card_amount').val('');

									$('#giftcard_container').append(json.html);

									// show coupon row
									$('#giftcard_container').slideDown();

									if(typeof json.edit_order_html !== 'undefined' && json.edit_order_html != null){
										//location.reload();
										$('#edit-order-total-container').replaceWith(json.edit_order_html);
									}


									// calculate the balance
									can_checkout();
								}
								else
								{
									modal_message({
										title: 'Gift Card Error',
										message: json.processor_message
									});
								}
							},
							error: function (objAJAXRequest, strError) {
								modal_message({
									title: 'Error',
									message: 'Unexpected error: ' + strError
								});
							}
						});
					}

					$('#debit_gift_card_number').data('processing', false);

				}
				else
				{
					$('#debit_gift_card_number').data('processing', false);

					modal_message({
						title: 'Gift Card Error',
						message: json.processor_message
					});
				}
			},
			error: function (objAJAXRequest, strError) {

				$('#debit_gift_card_number').data('processing', false);

				modal_message({
					title: 'Error',
					message: 'Unexpected error: ' + strError
				});
			}
		});
	}
}

function add_payment_gift_card()
{
	getGiftCardNumberAndAmount();
}

function getDomObjectToRemove(settings)
{
	var obj = null;
	if (settings.item == "giftcard")
	{
		obj = $('#row-giftcard-' + settings.item_number).get();

		return obj;
	}
	if (settings.item == "creditcard")
	{
		obj = $('#row-creditcard-' + settings.item_number).get();

		return obj;
	}
	if (settings.item == "credits")
	{
		obj = $('#row-credits').get();

		return obj;
	}
	if (settings.item == "cc_ref")
	{
		obj = $('#row-cc_ref-' + settings.item_number).get();
		return obj;
	}

	return null;
}

function add_all_store_credit()
{
	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_add_payment',
			payment_type: 'all_store_credit',
			store_id: store_id_of_order,
			output: 'json'
		},
		success: function (json) {
			if (json.processor_success)
			{
				$('#row-credits').html(json.html);
				$('#row-credits').slideDown();

				// calculate the balance
				can_checkout();
			}
			else
			{
				modal_message({
					title: 'Gift Card Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			modal_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});
		}
	});
}

function removeAllStoreCredit(settings)
{
	var domObjectToRemove = getDomObjectToRemove(settings);

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_remove_payment',
			payment_type: 'store_credit',
			payment_number: 'all'
		},
		success: function (json) {
			if (json.processor_success)
			{
				$(domObjectToRemove).slideUp();
				$(domObjectToRemove).empty();
				$('#SC-total').prop("checked", false);

				can_checkout();
			}
			else
			{
				modal_message({
					title: 'Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			modal_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});
		}
	});
}

function remove_payment(settings)
{
	var domObjectToRemove = getDomObjectToRemove(settings);

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_remove_payment',
			payment_type: settings.item,
			payment_number: settings.item_number
		},
		success: function (json) {
			if (json.processor_success)
			{
				if (settings.item == 'creditcard')
				{
					$('[id^="cc"]').prop('disabled', false);
					$('#billing_postal_code').prop('disabled', false);
					$('#billing_address').prop('disabled', false);
					$('#addCreditCard').prop('disabled', false);
				}

				if ($('#is_delayed_payment1').length)
				{
					$('#is_delayed_payment1').prop('disabled', false);
					$('#is_delayed_payment0').prop('disabled', false);
				}

				$(domObjectToRemove).slideUp();

				if (settings.item == 'creditcard' )
				{
					$(domObjectToRemove).remove();
				}

				if (settings.item == 'giftcard')
				{
					location.reload();
				}

				can_checkout();
			}
			else
			{
				modal_message({
					title: 'Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			modal_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});
		}
	});
}

function handleNewCardFormShown()
{
	$('[id^="cc_pay_id"]').prop({'disabled': true, 'checked' : false, 'required': false});
	$('[id^="gc_pay_id"]').prop({'disabled': true, 'checked' : false, 'required': false});


	requireCreditCardFields();
}

function handleNewCardFormHidden()
{
	$('[id^="cc_pay_id"]').prop({'disabled': false, 'required': true}).parents('form:first').removeClass('was-validated').find('.form-feedback').hide();
	$('[id^="gc_pay_id"]').prop({'disabled': false, 'required': true}).parents('form:first').removeClass('was-validated').find('.form-feedback').hide();


	doNotRequireCreditCardFields();
}

function handleOrderCustomizations(form){
	try
	{
		if (form.attr('id') == 'to_payment_or_checkout_form')
		{
			let requiredOptionSelected = false;
			if ($('#apply-customization').is(':checked'))
			{
				$('[data-user_pref_meal][type=checkbox]').each(function () {
					if ($(this).is(':checked'))
					{
						requiredOptionSelected = true;
					}
				});
				if (!requiredOptionSelected)
				{
					modal_message({
						title: 'Missing Customization Options',
						message: "At least one meal customization option is required if 'Customize this order' is checked."
					});
					$('#to_payment').removeClass('btn-spinning');

					setTimeout(function () {
						$('#to_payment').removeClass('disabled');
					}, 2000);
					return false;
				}
			}
		}
	}
	catch(error)
	{
		//not worth failing over
		console.log('Issue checking if meal customizations are selected')
	}

	return true;
}

function handleCardRefChange()
{
	var selectedRef = false;
	$('[id^="cc_pay_id"]').each( function() {
		if ($(this).is(':checked')) {
			selectedRef = $(this).val();
		}
	});

	$('[id^="gc_pay_id"]').each( function() {
		if ($(this).is(':checked')) {
			selectedRef = $(this).val();
		}
	});

	if (selectedRef)
	{
		doNotRequireCreditCardFields();
	}

}

function requireCreditCardFields()
{
	$('#ccNameOnCard, #ccType, #ccNumber, #ccMonth, #ccYear, #ccSecurityCode, #billing_address, #billing_postal_code, #billing_city').prop('required', true);
}

function doNotRequireCreditCardFields()
{
	$('#ccNameOnCard, #ccType, #ccNumber, #ccMonth, #ccYear, #ccSecurityCode, #billing_address, #billing_postal_code, #billing_city').prop('required', false);
}

function remove_credit_card_reference(settings)
{
	var domObjectToRemove = getDomObjectToRemove(settings);

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_remove_payment',
			payment_type: 'cc_ref',
			cc_ref_id: settings.item_number
		},
		success: function (json) {
			if (json.processor_success)
			{
				var numRefs = $(domObjectToRemove).data('num_cc_refs');
				$(domObjectToRemove).slideUp();
				$(domObjectToRemove).remove();

				if (numRefs > 1)
				{
					$('[id^="row-cc_ref"]').data('num_cc_refs', numRefs - 1);
				}
				else
				{
					// select add new card and expose - no refs left


				}

				can_checkout();
			}
			else
			{
				modal_message({
					title: 'Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			modal_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});
		}
	});
}

function add_payment_credit_card()
{
	var elementsToCheck = $('[id^="cc"]').get();

	if (!_check_elements(elementsToCheck))
	{
		return;
	}

	$('#addCreditCard').prop('disabled', true);

	var doDelayedPayment = false;

	if ($('#is_delayed_payment1').length)
	{
		if ($('#is_delayed_payment1').attr('checked'))
		{
			doDelayedPayment = true;
		}
	}

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_add_payment',
			payment_type: 'credit_card',
			card_number: $('#ccNumber').val(),
			name_on_card: $('#ccNameOnCard').val(),
			exp_month: $('#ccMonth').val(),
			exp_year: $('#ccYear').val(),
			security_code: $('#ccSecurityCode').val(),
			card_type: $('#ccType').val(),
			billing_zip: $('#billing_postal_code').val(),
			billing_addr: $('#billing_address').val(),
			do_delayed_payment: doDelayedPayment,
			output: 'json'
		},
		success: function (json) {
			if (json.processor_success)
			{
				$('[id^="cc"]').prop('disabled', true);
				$('#billing_postal_code').prop('disabled', true);
				$('#billing_address').prop('disabled', true);
				$('#addCreditCard').prop('disabled', true);

				if ($('#is_delayed_payment1').length)
				{
					$('#is_delayed_payment1').prop('disabled', true);
					$('#is_delayed_payment0').prop('disabled', true);
				}

				$('#creditcard_container').append(json.html);
				$('#creditcard_container').slideDown();

				// calculate the balance
				can_checkout();

				if (cart_subtotal == 0)
				{
					modal_message({
						title: 'Alert',
						message: 'You have added all required payments, you may now submit your order.'
					});
				}
			}
			else
			{
				modal_message({
					title: 'Credit Card Error',
					message: json.processor_message
				});
				$('#addCreditCard').prop('disabled', false);
			}
		},
		error: function (objAJAXRequest, strError) {
			modal_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});

			$('#addCreditCard').prop('disabled', false);
		}
	});
}

$(function () {

	if( $('#apply-customization').length && default_meal_customization_to_selected == true)
	{
		$('#apply-customization').click();
	}
	else
	{
		initMealCustomizationFields();
	}



	$("form").submit( function(e) {

		let canContinue = handleOrderCustomizations($(this));
		if ($(this).data('was_submitted') === true || !canContinue)
		{
			// Previously submitted - don't submit again
			e.preventDefault();
		}
		else
		{
			$(this).data('was_submitted', true);
		}
	});

	$(document).on('click', '.help-cvv', function (e) {
		modal_message({
			title: 'Help',
			message: 'The security code is a 3 digit number located on the back of MasterCard, Visa, Discover. On American Express cards, the security code is a group of 4 digits printed on the front.'
		})
	});

	$(document).on('change', '#ccType', function (e) {

		if ($(this).val() == 'American Express')
		{
			$('#ccSecurityCode').prop({'min': '0', 'max': '9999', 'maxlength': '4', 'pattern': '^[0-9]{4}$'});
		}
		else if ($(this).val() != '' && $(this).val() != null)
		{
			$('#ccSecurityCode').prop({'min': '0', 'max': '999', 'maxlength': '3', 'pattern': '^[0-9]{3}$'});
		}
	});

	if (USER_PREFERENCES && $('#ccNumber').length)
	{
		$('#ccNumber').validateCreditCard(function (result) {

				var validatorType = false;
				if (result.card_type != null && result.card_type.name != null)
				{
					validatorType = result.card_type.name;
				}

				if (($('#ccType').val() == '' || $('#ccType').val() == null) && validatorType)
				{
					switch (validatorType)
					{
						case 'visa':
							$('#ccType').val('Visa');
							break;
						case 'mastercard':
							$('#ccType').val('Mastercard');
							break;
						case 'amex':
							$('#ccType').val('American Express');
							break;
						case 'discover':
							$('#ccType').val('Discover');
							break;
						default:
							break;
					}
				}

				if ($('#ccType').val() != '')
				{
					// get validator type name of current type setting
					var currentTypeWidgetValue = false;
					switch ($('#ccType').val())
					{
						case 'Visa':
							currentTypeWidgetValue = 'visa';
							break;
						case 'Mastercard':
							currentTypeWidgetValue = 'mastercard';
							break;
						case 'American Express':
							currentTypeWidgetValue = 'amex';
							break;
						case 'Discover':
							currentTypeWidgetValue = 'discover';
							break;
						default:
							break;
					}

					if (currentTypeWidgetValue && currentTypeWidgetValue != validatorType && $("#ccNumber").val().length > 2)
					{
						$(".credit_card_warning").removeClass('collapse');
					}
					else
					{
						$(".credit_card_warning").addClass('collapse');
					}

				}
			},
			{
				accept: [
					'visa',
					'mastercard',
					'discover',
					'amex'
				]
			});
	}

	// Register click handler for CC logos
	$(document).on('click', '[data-credit_card_logo]', function (e) {
		$('#ccType').val($(this).data('credit_card_logo')).trigger('change');
	});

	// Click handler for remove items
	$(document).on('click', '[id^="remove-"]', function (e) {
		// store the id and some of its parts
		var remove = {
			id: this.id,
			item: this.id.split("-")[1],
			item_number: this.id.split("-")[2],
			type: 'Payment'
		};

		// figure out if it has multiples like gift cards
		var multiple = (remove.item_number != undefined) ? '-' + remove.item_number : '';

		// get the friendly item description to display in the popup
		remove.title = $('#checkout_title-' + remove.item + multiple).text();

		// it's payment by default, figure out if it is an item
		if (remove.item == 'nonfood' || remove.item == 'food' || remove.item == 'giftcard_purchase')
		{
			remove.type = 'Item';
		}

		// show removal confirmation
		modal_message({
			title: 'Remove ' + remove.type,
			message: 'Are you sure you wish to remove ' + remove.title + '.',
			confirm: function () {
				remove_item_payment(remove);
			}
		});
	});

	// Watch for change on terms checkbox
	$(document).on('change', '#customers_terms', function (e) {
		can_checkout();
	});

	// Coupon add handler
	$(document).on('click', '#add_coupon_add', function (e) {
		add_payment_coupon();
		e.preventDefault();
	});

	// Coupon add enter key
	$(document).on('keyup', '#add_coupon_code', function (e) {
		if (e.which == 13)
		{
			add_payment_coupon();
		}
	});

	// credit card
	$(document).on('click', '#addCreditCard', function (e) {
		add_payment_credit_card();
	});

	//Gift Card Add handler
	$(document).on('click', '#giftCardBalance', function (e) {
		get_gift_card_balance();
		e.preventDefault();
	});

	// Gift Card Button click
	$(document).on('click', '#giftCardRedeem', function (e) {
		add_payment_gift_card();
		e.preventDefault();
	});

	$(document).on('change', '#shipping_phone_number', function (e) {

		$('.shipping_phone_number_new_div').hideFlex();
		$('#shipping_phone_number_new').prop('required', false);

		if ($(this).val() == 'new')
		{
			$('.shipping_phone_number_new_div').showFlex();
			$('#shipping_phone_number_new').prop('required', true);

		}
	});

	$(document).on('change', '#address_book_select', function (e) {

		let id = $(this).val();
		let prev_id = (($('#address_book_update_update').val() == 'update') ? '' : $('#address_book_update_update').val());

		if (id == '')
		{
			$('#address_book_update_update').val('update');

			$('#shipping_firstname').valCheckDiffRemove('');
			$('#shipping_lastname').valCheckDiffRemove('');
			$('#shipping_address_line1').valCheckDiffRemove('');
			$('#shipping_address_line2').valCheckDiffRemove('');
			$('#shipping_city').valCheckDiffRemove('');
			$('#shipping_state_id').valCheckDiffRemove('');
			$('#shipping_address_note').valCheckDiffRemove('');
			$('#shipping_phone_number').valCheckDiffRemove('');
			$('#shipping_gift_email_address').valCheckDiffRemove('');

			$('#shipping_postal_code').valCheckDiffRemove();
		}
		else if (id)
		{
			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'delivered_box',
					op: 'update_address',
					id: id
				},
				success: function (json) {
					if (json.processor_success)
					{
						let addy = JSON.parse(json.address);

						switch (json.status)
						{
							case 'no_inventory':
								modal_message({
									message: json.processor_message,
									buttons: {
										"Cancel": function () {
											$('#address_book_select').val(prev_id);
										},
										"Change Address": function () {
											create_and_submit_form({
												action: '/box-select',
												input: ({
													delivered_zip: addy.postal_code
												})
											});
										}
									}
								});
								break;
							case 'not_eligible':
								modal_message({
									message: 'We are sorry, Dream Dinners does not currently ship to ' + addy.postal_code,
									buttons: {
										"Close": function () {
											$('#address_book_select').val(prev_id);
										}
									}
								});
								break;
							default:
								if (addy.location_type == 'ADDRESS_BOOK')
								{
									$('#address_book_update_update').val(addy.id);

									$('#shipping_firstname').valCheckDiff({value : addy.firstname, group : 'shipaddy'});
									$('#shipping_lastname').valCheckDiff({value : addy.lastname, group : 'shipaddy'});
									$('#shipping_address_line1').valCheckDiff({value : addy.address_line1, group : 'shipaddy'});
									$('#shipping_address_line2').valCheckDiff({value : addy.address_line2, group : 'shipaddy'});
									$('#shipping_city').valCheckDiff({value : addy.city, group : 'shipaddy'});
									$('#shipping_state_id').valCheckDiff({value : addy.state_id, group : 'shipaddy'});
									$('#shipping_address_note').valCheckDiff({value : addy.address_note, group : 'shipaddy'});
									$('#shipping_phone_number').valCheckDiff({value : addy.telephone_1, group : 'shipaddy'});
									$('#shipping_gift_email_address').valCheckDiff({value : addy.email_address, group : 'shipaddy'});

									$('#shipping_postal_code').valCheckDiff({value : addy.postal_code, group : 'shipaddy'});
								}
								else
								{
									$('#shipping_firstname').val(addy.firstname);
									$('#shipping_lastname').val(addy.lastname);
									$('#shipping_address_line1').val(addy.address_line1);
									$('#shipping_address_line2').val(addy.address_line2);
									$('#shipping_city').val(addy.city);
									$('#shipping_state_id').val(addy.state_id);
									$('#shipping_address_note').val(addy.address_note);
									$('#shipping_phone_number').val(addy.telephone_1);
									$('#shipping_gift_email_address').val(addy.email_address);

									$('#shipping_postal_code').val(addy.postal_code);
								}
								break;
						}
					}
				},
				error: function (objAJAXRequest, strError) {
					modal_message({
						title: 'Error',
						message: 'Unexpected error: ' + strError
					});
				}
			});
		}

	});

	$(document).on('dd:checkdiff', function (e) {

		let diffData = $(document).data().checkdiff;

		if (Object.keys(diffData['shipaddy']).length === 0)
		{
			$('.update_contact_row').hideFlex();
		}
		else
		{
			$('.update_contact_row').showFlex();
		}

	});

	$(document).on('shown.bs.collapse', '#add_new_cc', function(e) {
		handleNewCardFormShown();
	});

	$(document).on('hidden.bs.collapse', '#add_new_cc', function(e) {
		handleNewCardFormHidden();
	});

	$(document).on('change', '[id^="cc_pay_id"]', function(e) {
		handleCardRefChange();
	});

	$(document).on('change', '[id^="gc_pay_id"]', function(e) {
		handleCardRefChange();
	});

	$(document).on('click', '.box-change-zip', function (e) {

		e.preventDefault();

		let href = $(this).attr('href');

		modal_message({
			message : 'Menu item availability and taxes may be different for another delivery area. In order to change the zip code, we need to send you through the order process again.',
			buttons: {
				"Change Zip": function () {
					bounce(href);
				},
				"Cancel": function () {
				}
			}
		});

	});

	// Logged in functions
	if (USER_PREFERENCES)
	{
		handle_ltd_round_up();
		handle_checkout_delayed_payment();
		handle_special_instructions();
		handleMealCustomizationAccordion();

		$(document).on('click', '#apply_plate_points', function (e) {
			editSavePlatePointsCredits(false);
		});

		$(document).on('click', '#apply_all_plate_points', function (e) {
			editSaveAllPlatePointsCredits(false);
		});

		/*
				$(document).on('keyup', '#plate_points_discount', function (e) {
					if (e.which == 13)
					{
						$('#edit_plate_points').trigger('click');
					}
				});
		*/
		if ($("#apply_plate_points").length && maxPPDeduction <= 0)
		{
			$("#apply_plate_points").attr("disabled", true);
			$("#apply_all_plate_points").attr("disabled", true);

		}
	}

	can_checkout();

});

const checkbox = document.getElementById('opted_to_bring_bags');
if (checkbox)
{
	checkbox.addEventListener('click', function handleClick() {
		if (checkbox.checked)
		{
			$("#bag_text").hideFlex();
			$("#opted_to_bring_bags_hidden").val("1");

		}
		else
		{
			$("#bag_text").showFlex();
			$("#opted_to_bring_bags_hidden").val("0");
		}
		update_bag_opt_out();
	});
}

function handleMealCustomizationAccordion()
{
	$("#customization-header").click(function (e) {
		e.preventDefault();

		$header = $(this);
		$content = $("#customization-row");
		$header.html(function () {
			return "Meal Customizations" + ($content.is(":visible") ? " &#8744;" : " &#8743;");
		});
		$content.slideToggle(500, function () {
		});

	});
}

function initMealCustomizationFields()
{
	if(typeof has_meal_customization_selected === 'undefined'){
		has_meal_customization_selected = false;
	}
	toggleMealCustomizationOptions(has_meal_customization_selected);
}

function toggleMealCustomizationOptions(on){
	if(on){
		$('[data-user_pref_meal][type=checkbox]').removeAttr("disabled");
		$('.customization-readonly-option').removeClass('text-gray-400')
	}else{
		$('[data-user_pref_meal][type=checkbox]').attr("disabled", true);
		$('.customization-readonly-option').addClass('text-gray-400')
	}
}

function toggleMealCustomizationMasterCheckbox(on){
	if(on){
		$('#apply-customization').prop('checked', true);
		$('#apply-customization').attr('checked', true);
		has_meal_customization_selected = true;
	}else{
		$('#apply-customization').prop('checked', false);
		$('#apply-customization').attr('checked', true);
		has_meal_customization_selected = false;
		handleMealCustomizationMasterCheckbox(false);
	}
}

function handleMealCustomizationMasterCheckbox(allow_customization){
	has_meal_customization_selected = allow_customization;
	toggleMealCustomizationOptions(allow_customization);

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: "JSON",
		data: {
			processor: 'cart_order_customization_preference',
			op: 'set_customization_applies_to_order',
			allow_customization: allow_customization,
			customizations: JSON.stringify(meal_customization_preferences)
		},
		success: function (json) {
			if (json.processor_success)
			{
				customization = json;
				if(customization.opted_to_customize_recipes == true){
					$('#customization-fee-row').show();
				}else{
					$('#customization-fee-row').hide();
				}
				$('#checkout_total-customization_fee').text(formatAsMoney(customization.cost));
				$('#sum_checkout_total-subtotal').text(formatAsMoney(customization.orderInfo.grand_total));
				$('#credit_card_amount').text(formatAsMoney(customization.orderInfo.grand_total));
				$('#checkout_total-tax').text(formatAsMoney(customization.orderInfo.subtotal_all_taxes));

			}
			else
			{
				modal_message({
					title: 'Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			modal_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});
		}
	});
}

$(document).on('click', '#apply-customization', function (e) {
	let allow_customization = $(this).prop('checked');
	handleMealCustomizationMasterCheckbox(allow_customization);
});


function preferenceChangeListener(pref, setting, user_id, callback){

	pref = pref.toUpperCase();

	if(!pref.includes('MEAL'))
	{
		set_user_pref(pref, setting, user_id, callback);
	}
	else
	{
		let pref_value = USER_PREFERENCES[pref].value;

		if ($.isPlainObject(pref_value) && $.isPlainObject(setting))
		{
			setting = JSON.stringify($.extend(USER_PREFERENCES[pref].value, setting));
		}
		else
		{
			setting = setting.toString()
		}

		meal_customization_preferences[pref].value = setting;

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'cart_order_customization_preference',
				op: 'update_order_meal_customization',
				customizations: JSON.stringify(meal_customization_preferences)
			},
			success: function (json) {
				if (json.processor_success)
				{

					if(json.opted_to_customize_recipes == true){
						$('#customization-fee-row').show();
					}else{
						$('#customization-fee-row').hide();
						//toggleMealCustomizationMasterCheckbox(false);
					}
					$('#checkout_total-customization_fee').text(formatAsMoney(json.cost));
					$('#sum_checkout_total-subtotal').text(formatAsMoney(json.orderInfo.grand_total));
					$('#credit_card_amount').text(formatAsMoney(json.orderInfo.grand_total));

					if(typeof callback !== 'undefined'){
						callback(json);
					}

				}
				else
				{
					modal_message({
						title: 'Error',
						message: json.processor_message
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				modal_message({
					title: 'Error',
					message: 'Unexpected error: ' + strError
				});
			}
		});
	}

}