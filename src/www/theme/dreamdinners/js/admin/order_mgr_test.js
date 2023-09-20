var itemChanges = false;
var discountOrPaymentChanges = false;
var discountChanges = false;
var reportingChanges = false;
var paymentChanges = false;
var initialSessionDiscountSetting = "noSD";
var initialPreferredUserDiscountSetting = "noUP";
var changeList = null;
var isFactoringCredit = false;
var currentCreditAvailable = 0;
var stationNeedsAttention = false;
var relatedOrdersAreLoaded = false;
var currentlySavingOrder = false;
var onTimeShotAtSupplyingZeroPaymentHasOccurred = false;
var needPlatePointsMaxDiscountChangeWarning = false;
var lastMaxPPDiscountAmount = -1;

function admin_order_mgr_init()
{

	if (store_id != STORE_DETAILS.id)
	{
		STORE_DETAILS.id = store_id;

		//STORE_DETAILS.id is either the current fadmin store - in which case a match is guaranteed or
		// is the current Site Admin store (last selection from a store drop down.) However in this case if the order was accessed by the address
		// bar a match is not guaranteed so force STORE_DETAILS.id to store_id which is always the store of the order
	}

	if (orderState == 'NEW')
	{
		setupForNewOrder();
	}
	else if (orderState == 'SAVED')
	{
		setupForSavedOrder();
	}
	else
	{
		setupForActiveOrder();
	}

	if (hasBundle)
	{
		bundleSetup();
	}

	setupSiteAdminFunctions();

	initChangeList();

	if (orderState != 'NEW')
	{
		calculateTotal();

		setAllPaymentFieldsToUnrequired();

		setupCouponState();
	}

	setUpKeyHandlers();

	if (getQueryVariable("session_full") == "true")
	{
		var query = window.location.search.substring(1);
		var vars = query.split("&");
		var newQueryString = "?";

		var first = true;
		for (var i = 0; i < vars.length; i++)
		{

			var pair = vars[i].split("=");
			if (pair[0] != 'session_full')
			{
				if (!first)
				{
					newQueryString += "&";
				}

				newQueryString += vars[i];
			}

			first = false;
		}

		historyPush({url: newQueryString});
	}

	handle_special_instruction_notes();
	handle_cancel_order();
	handle_delete_saved_order();

	handle_free_menu_item_edit();
	handle_fundraiser_selection();
}

var intenseLoggingOn = true;

function intenseLogging(message)
{
	if (window.console && intenseLoggingOn)
	{
		console.log('OM Intense logging: ' + message);
	}
}

function saveSpecialInstructions()
{

	var special_instructions = strip_tags($("#order_user_notes").val());

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor',
			store_id: STORE_DETAILS.id,
			user_id: user_id,
			op: 'update_special_instructions',
			order_id: order_id,
			special_instructions: special_instructions
		},
		success: function (json)
		{
			if (json.processor_success)
			{
				updateInstructionsOrgValues();

			}
			else
			{
				intenseLogging("saveSpecialInstructions: " + json.processor_message);
				dd_message({
					title: 'Error',
					message: json.processor_message
				});

			}
		},
		error: function (objAJAXRequest, strError)
		{
			intenseLogging("saveSpecialInstructions: " + strError + " | " + objAJAXRequest.responseText);
			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});

		}
	});
}

function handle_special_instruction_notes()
{
	$('[id^="gd_special_instruction_note_button-"]').each(function ()
	{

		$(this).on('click', function (e)
		{

			var booking_id = $(this).data('booking_id');
			var user_id = $(this).data('user_id');
			var do_op = 'get';
			var special_instructions = '';

			if ($(this).data('edit_mode') == true)
			{
				do_op = 'save';

				special_instructions = strip_tags($('#gd_special_instruction_note-' + booking_id + ' > textarea').val());
			}

			$.ajax({
				url: '/processor',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_order_mgr_processor',
					op: 'update_special_instructions',
					'do': do_op,
					store_id: STORE_DETAILS.id,
					user_id: user_id,
					order_id: order_id,
					note: special_instructions
				},
				success: function (json)
				{
					if (json.processor_success)
					{
						if (do_op == 'get')
						{
							var textarea_elem = $('<textarea></textarea>').addClass('form-control').val((json.special_instruction_note ? json.special_instruction_note : "")).on('keyup', function (e)
							{

								if ($(this).val() != strip_tags($(this).val()))
								{
									$(this).val(strip_tags($(this).val()));
								}

							});

							$('#gd_special_instruction_note-' + booking_id).addClass('special_instruction_note_edit').html(textarea_elem);

							$('#gd_special_instruction_note_button-' + booking_id).data('edit_mode', true).html('Save Note');

							$('#gd_special_instruction_note_cancel_button-' + booking_id).data('special_instruction_note_value', (json.special_instruction_note ? json.special_instruction_note : "")).on('click', function (e)
							{

								$('#gd_special_instruction_note-' + $(this).data('booking_id')).removeClass('special_instruction_note_edit').html($(this).data('special_instruction_note_value'));

								$('#gd_special_instruction_note_button-' + $(this).data('booking_id')).data('edit_mode', false).html('Special Instructions');

								$(this).hide();

							}).show();
						}
						else
						{
							$('#gd_special_instruction_note-' + booking_id).removeClass('special_instruction_note_edit').html(nl2br(json.special_instruction_note));

							$('#gd_special_instruction_note_cancel_button-' + booking_id).hide();

							$('#gd_special_instruction_note_button-' + booking_id).data('edit_mode', false).html('Special Instructions');
						}
					}
				},
				error: function (objAJAXRequest, strError)
				{
					intenseLogging("handle_special_instruction_notes: " + strError + " | " + objAJAXRequest.responseText);
					response = 'Unexpected error';
				}
			});

		});

	});
}

var lastCheckedGiftCertNumber = null;

function setUpKeyHandlers()
{
	document.onkeypress = OMDefaultKeyHandler;

	$("#coupon_code").on('keypress', function (evt)
	{
		// the enter key will submit a coupon code
		evt = (evt) ? evt : event;
		var charCode = (evt.charCode) ? evt.charCode : ((evt.which) ? evt.which : evt.keyCode);

		if (charCode == 13)
		{
			processCode();
			evt.stopPropagation();
			return false;
		}

		return true;
	});

	$("#order_user_notes").on('keypress', function (evt)
	{
		evt.stopPropagation();
		return true;
	});

	$("#payment1_gc_payment_number").on('keyup', function (evt)
	{

		if ($(this).val().length == 15 && !isNaN($(this).val()) && $(this).val() != lastCheckedGiftCertNumber)
		{
			dd_message({
				title: 'Warning',
				message: "It appears that you may be entering a gift Card number. If so, select Gift Card from payment selector. This is the Certificate (Gift Certificate) payment type."
			});
		}

		lastCheckedGiftCertNumber = $(this).val();

		return true;
	});

}

function OMDefaultKeyHandler(evt)
{
	// by default the document just eats the enter key

	evt = (evt) ? evt : event;
	var charCode = (evt.charCode) ? evt.charCode : ((evt.which) ? evt.which : evt.keyCode);

	if (charCode == 13)
	{
		return false;
	}

	return true;

}

function initChangeList()
{
	$("#changeListTab").on('click', function ()
	{

		var html = getChangeListHTML();

		if (!html || html == "")
		{
			html = "<h3><i>No Changes</i></h3>"
		}

		dd_message({
			title: 'Change List',
			message: html,
			height: 620,
			width: 300,
			modal: false,
			div_id: 'changelist',
			dialogClass: 'fixedDialog',
			noOk: true,
			position: {
				my: "left top",
				at: "left bottom",
				of: this
			},
			close: function (event, ui)
			{
				$("#changelist").remove();
			}
		});
	});
}

function setupSiteAdminFunctions()
{
	$("#in-store_status, #plate_points_status").on('click', function ()
	{

		var flagName = this.id;
		var checkbox = $(this);
		if (orderState != 'ACTIVE')
		{
			dd_message({
				title: 'Error',
				message: "These flags can only be set on ACTIVE orders."
			});

		}
		else
		{
			var newState = "off";
			if ($(this).is(":checked"))
			{
				newState = "on";
			}

			var oldState = 1;
			if (newState == "on")
			{
				oldState = 0;
			}

			dd_message({
				title: 'Status Update',
				message: "Are you sure you want to update the order status?",
				cancel: function ()
				{
					checkbox.prop('checked', oldState ? true : false)

				},
				confirm: function ()
				{
					$.ajax({
						url: '/processor',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_order_mgr_processor',
							store_id: STORE_DETAILS.id,
							user_id: user_id,
							op: 'update_status_flag',
							order_id: order_id,
							flag: flagName,
							new_state: newState
						},
						success: function (json)
						{
							if (json.processor_success)
							{
								dd_message({
									title: 'Update Success',
									message: json.processor_message
								});
							}
							else
							{

								intenseLogging("update_status_flag: " + json.processor_message);

								$(this).attr("checked", oldState);

								dd_message({
									title: 'Error',
									message: json.processor_message
								});

							}
						},
						error: function (objAJAXRequest, strError)
						{

							intenseLogging("update_status_flag: " + strError + " | " + objAJAXRequest.responseText);

							$(this).attr("checked", oldState);

							response = 'Unexpected error: ' + strError;
							dd_message({
								title: 'Error',
								message: response
							});

						}

					});

				}
			});
		}
	});
}

function updateItemsOrgValues()
{

	$("[id^='qty_']").each(function ()
	{
		$(this).data('org_value', $(this).val());
	});

	$("[data-dd_type='item_field']").each(function ()
	{
		$(this).data('org_value', $(this).val());
	});

	$('#selectedBundle').data('org_value', $('#selectedBundle').is(':checked') ? "1" : "0");

	if (isDreamTaste || isFundraiser)
	{
		$("[id^='bnd_']").each(function ()
		{
			$(this).data('org_value', $(this).val());
		});
	}
	else
	{
		$("[id^='bnd_']").each(function ()
		{
			$(this).data('org_value', $(this).is(':checked') ? "1" : "0");
		});
	}

	updateChangeList();

}

function saveAll()
{
	saveItems(true);
}

function saveItems(saveDiscountsUponCompletion)
{

	intenseLogging("saveItems() called");

	if (currentlySavingOrder)
	{
		dd_toast({
			message: 'Items are still being saved',
			position: 'topcenter'
		});
		return;
	}

	dd_toast({
		message: 'Items Saved',
		position: 'topcenter'
	});

	var itemsList = {};
	var introItemsList = {};
	var subItemsList = {};

	var is_intro = "false";

	if (isDreamTaste || isFundraiser)
	{
		$("[id^=bnd_]").each(function ()
		{

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				itemsList[item_id] = $(this).val();
			}
		});

		// this will pick up any FT items
		$("[id^=qty_]").each(function ()
		{

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				itemsList[item_id] = $(this).val();
			}
		});

	}
	else
	{

		if ($("#selectedBundle").is(":checked"))
		{

			is_intro = "true";

			$("[id^=bnd_]").each(function ()
			{

				if ($(this).is(":checked"))
				{
					var item_id = this.id.split("_")[1];
					introItemsList[item_id] = "on";
				}
			});

		}

		$("[id^=qty_]").each(function ()
		{

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				itemsList[item_id] = $(this).val();
			}
		});

		$("[id^=sbi_]").each(function ()
		{

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				subItemsList[item_id] = $(this).val();
			}
		});

	}

	var misc_food_subtotal = $("#misc_food_subtotal").val();
	if (isNaN(misc_food_subtotal))
	{
		misc_food_subtotal = "0.00";
	}

	var misc_nonfood_subtotal = $("#misc_nonfood_subtotal").val();
	if (isNaN(misc_nonfood_subtotal))
	{
		misc_nonfood_subtotal = "0.00";
	}

	var subtotal_service_fee = $("#subtotal_service_fee").val();
	if (isNaN(subtotal_service_fee))
	{
		subtotal_service_fee = "0.00";
	}

	var misc_food_subtotal_desc = $("#misc_food_subtotal_desc").val();
	var misc_nonfood_subtotal_desc = $("#misc_nonfood_subtotal_desc").val();
	var service_fee_description = $("#service_fee_description").val();

	var special_instructions = $("#order_user_notes").val();

	currentlySavingOrder = true;

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor',
			store_id: STORE_DETAILS.id,
			user_id: user_id,
			op: 'update_items',
			order_id: order_id,
			items: itemsList,
			misc_food_subtotal: misc_food_subtotal,
			misc_nonfood_subtotal: misc_nonfood_subtotal,
			subtotal_service_fee: subtotal_service_fee,
			misc_food_subtotal_desc: misc_food_subtotal_desc,
			misc_nonfood_subtotal_desc: misc_nonfood_subtotal_desc,
			service_fee_description: service_fee_description,
			is_intro: is_intro,
			intro_items: introItemsList,
			sub_items: subItemsList,
			special_instructions: special_instructions
		},
		success: function (json)
		{
			if (json.processor_success)
			{
				itemTabIsDirty = false;
				updateItemsOrgValues();

				currentlySavingOrder = false;

				intenseLogging("saveItems() successful");

				if (saveDiscountsUponCompletion)
				{
					saveDiscounts(false);
				}
			}
			else
			{
				currentlySavingOrder = false;

				intenseLogging("update_items: " + json.processor_message);

				dd_message({
					title: 'Error',
					message: json.processor_message
				});

			}
		},
		error: function (objAJAXRequest, strError)
		{

			currentlySavingOrder = false;
			intenseLogging("update_items: " + strError + " | " + objAJAXRequest.responseText);

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});

		}

	});
}

function setSessionAndSave(session_id)
{

	intenseLogging("setSessionAndSave() called");

	displayModalWaitDialog('saving_div', "Setting session. Please wait ...");

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 90000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor',
			store_id: STORE_DETAILS.id,
			op: 'set_session_and_save',
			session_id: session_id,
			user_id: user_id,
			dd_csrf_token: $('input[name=dd_csrf_token]', '#editorForm').val()
		},
		success: function (json)
		{
			if (json.processor_success)
			{

				intenseLogging("setSessionAndSave() successful");

				if (json.full_session_warning_required)
				{
					bounce("/?page=admin_order_mgr&order=" + json.order_id + "&session_full=true");
				}
				else
				{
					bounce("/?page=admin_order_mgr&order=" + json.order_id);
				}
			}
			else
			{

				$("#saving_div").remove();
				$('input[name=dd_csrf_token]', '#editorForm').val(json.getTokenToken);

				intenseLogging("set_session_and_save: " + json.processor_message);

				dd_message({
					title: 'Error',
					message: json.processor_message
				});

			}
		},
		error: function (objAJAXRequest, strError)
		{
			$("#saving_div").remove();

			intenseLogging("set_session_and_save: " + strError + " | " + objAJAXRequest.responseText);

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});

		}

	});
}

function Reschedule(org_session_id)
{

	intenseLogging("Reschedule() called");

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor',
			store_id: STORE_DETAILS.id,
			op: 'reschedule',
			session_id: session_id,
			user_id: user_id,
			order_id: order_id,
			saved_booking_id: saved_booking_id,
			org_session_id: org_session_id
		},
		success: function (json)
		{
			if (json.processor_success)
			{

				intenseLogging("Reschedule() successful");

				session_id = json.new_session_id;
				$("#curSessionDate").html(json.new_session_time);

				RetrieveCalendar(0);

				if (!json.canDelayPayment)
				{
					$(".delayedPaymentSection").hideFlex();
				}
				else
				{
					$(".delayedPaymentSection").showFlex();
				}

				if (json.session_discount)
				{
					$("#noSessionDiscountRow").hide();
					$("#newSessionDiscountBody").show();
					$("#sessionDiscountTypeSpan").html(json.session_discount.type);
					$("#sessionDiscountValueSpan").html(json.session_discount.value);
					activeSessionDiscount.id = json.session_discount.id;
					activeSessionDiscount.type = json.session_discount.type;
					activeSessionDiscount.value = json.session_discount.value;

				}
				else
				{
					$("#noSessionDiscountRow").show();
					$("#newSessionDiscountBody").hide();
					activeSessionDiscount.id = 0;
					activeSessionDiscount.type = 'none';
					activeSessionDiscount.value = 0;

				}

				$("#curSessionTypeSpan").html(json.session_info.session_type_text);
				$("#curSessionRemainingSlotsSpan").html(json.session_info.remaining_slots);
				$("#curSessionRemainingIntroSlotsSpan").html(json.session_info.remaining_intro_slots);

				if (json.serviceFeeUpdate != -1)
				{

					//bounce(window.location.href);

					$("#subtotal_service_fee").val(formatAsMoney(json.serviceFeeUpdate));
					$("#service_fee_description").val(json.serviceFeeDescUpdate);
					calculateTotal();

				}

			}
			else
			{
				intenseLogging("reschedule: " + json.processor_message);

				dd_message({
					title: 'Error',
					message: json.processor_message
				});

			}
		},
		error: function (objAJAXRequest, strError)
		{
			response = 'Unexpected error: ' + strError;

			intenseLogging("reschedule: " + strError + " | " + objAJAXRequest.responseText);

			dd_message({
				title: 'Error',
				message: response
			});

		}

	});
}

function onSetSessionAndSave(obj)
{
	intenseLogging("onSetSessionAndSave() called");

	var session_id = $("#selected_session").attr("data-session_id");
	setSessionAndSave(session_id);
	return false;
}

function onSaveItems()
{
	intenseLogging("onSaveItems() called");

	saveItems();
	return false;

}

function setupForNewOrder()
{
	HideLineItemsIfZero();
	var timestamp = getQueryVariable('month');
	if (!timestamp)
	{
		timestamp = "0";
	}
	RetrieveCalendar(timestamp);
	$("#addPaymentAndActivate").show();

}

function setupForSavedOrder()
{
	$("#addPaymentAndActivate").show();

	if ($("#SessDiscnoSD").length)
	{
		initialSessionDiscountSetting = $("input[name='SessDisc']:checked", '#editorForm').val();
	}

	if ($("#PUDnoUP").length)
	{
		initialPreferredUserDiscountSetting = $("input[name='PUD']:checked", '#editorForm').val();
	}
}

function setupForActiveOrder()
{
	if (discountEligable.limited_access)
	{
		$("#payments_tab_li").click();

		$("#items_tab_li").addClass("disabled");
		$("#sessions_tab_li").addClass("disabled");
		$("#discountsDiv").find(":input").prop("disabled", true);
		$("#discountsDiv").find("*").addClass("om_disabled");

	}

	$("#addPaymentAndActivate").hide();

	if ($("#SessDiscnoSD").length)
	{
		initialSessionDiscountSetting = $("input[name='SessDisc']:checked", '#editorForm').val();
	}

	if ($("#PUDnoUP").length)
	{
		initialPreferredUserDiscountSetting = $("input[name='PUD']:checked", '#editorForm').val();
	}

}

function onSessionTabSelected()
{
	RetrieveCalendar(0);
}

function onSessionTabDeselected()
{
	return true;
}

function onItemsTabSelected()
{
}

function onItemsTabDeselected()
{
	if (orderState != 'ACTIVE')
	{
		if (itemTabIsDirty)
		{
			/*
			 dd_message({
			 title: 'Attention',
			 message: 'You have unsaved changes. Do you wish to save changes to the items tab?',
			 confirm: function() {
			 }
			 });
			 */

			saveItems();
		}
	}

	return true;
}

function onPaymentTabSelected()
{
	handle_free_menu_item_edit();
}

function onPaymentTabDeselected()
{
	if (orderState != 'ACTIVE' && (discountChanges || reportingChanges))
	{
		saveDiscounts(false);
	}

	return true;
}

function onRelatedOrdersTabSelected()
{
	if (!relatedOrdersAreLoaded)
	{

		intenseLogging("Loading related Orders");

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor',
				store_id: STORE_DETAILS.id,
				op: 'retrieve_related_orders',
				session_id: session_id,
				order_id: order_id,
				user_id: user_id
			},
			success: function (json)
			{

				intenseLogging("Loading related Orders successful");

				$("#related_orders").html(json.data);
				$("#loading_related_orders").remove();
				relatedOrdersAreLoaded = true;
			},
			error: function (objAJAXRequest, strError)
			{
				$("#loading_related_orders").remove();

				intenseLogging("retrieve_related_orders: " + strError + " | " + objAJAXRequest.responseText);

				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});

			}

		});
	}

}

function onRelatedOrdersTabDeselected()
{
	return true;
}

function onNotesTabSelected()
{
	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor',
			store_id: STORE_DETAILS.id,
			op: 'get_history',
			session_id: session_id,
			user_id: user_id,
			order_id: order_id
		},
		success: function (json)
		{
			$("#history_div").html(json.html);

			handle_guest_account_notes();

			handle_admin_carryover_notes();

			handle_admin_order_notes();
		},
		error: function (objAJAXRequest, strError)
		{
			response = 'Unexpected error: ' + strError;

			intenseLogging("get_history: " + strError + " | " + objAJAXRequest.responseText);

			dd_message({
				title: 'Error',
				message: response
			});

		}

	});

}

function onNotesTabDeselected()
{
	return true;
}

function onPaymentSelected()
{
	if (orderState != 'ACTIVE')
	{
		$("#addPaymentAndActivateButton").attr("disabled", false);
		paymentChanges = true;
		updateChangeList();
	}
	else
	{
		paymentChanges = true;
		updateChangeList();
	}
}

function onPaymentDeselected()
{
	if (orderState != 'ACTIVE')
	{
		$("#addPaymentAndActivateButton").attr("disabled", true);
		updateChangeList();
		paymentChanges = false;
	}
	else
	{
		paymentChanges = false;
		updateChangeList();
	}

	return true;

}

function onSiteAdminTabSelected()
{

}

function onSiteAdminTabDeselected()
{
	return true;
}

function updateDiscountOrgValues()
{
	if ($("#SessDiscnoSD").length)
	{
		initialSessionDiscountSetting = $("input[name='SessDisc']:checked", '#editorForm').val();
	}

	if ($("#PUDnoUP").length)
	{
		initialPreferredUserDiscountSetting = $("input[name='PUD']:checked", '#editorForm').val();
	}

	$("#org_coupon_id").val($("#coupon_id").val());

	$("#plate_points_discount, #direct_order_discount").each(function ()
	{
		$(this).data('org_value', $(this).val());
	});

	updateChangeList();

}

function updateActiveOrder(payOnCompletion, go_to_confirm)
{
	intenseLogging("updateActiveOrder() called");

	var itemsList = {};
	var introItemsList = {};
	var subItemsList = {};

	var is_intro = "false";

	if (isDreamTaste || isFundraiser)
	{
		$("[id^=bnd_]").each(function ()
		{

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				itemsList[item_id] = $(this).val();
			}
		});

		//pick up any sides
		$("[id^=qty_]").each(function ()
		{

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				itemsList[item_id] = $(this).val();
			}
		});

	}
	else
	{

		if ($("#selectedBundle").is(":checked"))
		{

			is_intro = "true";

			$("[id^=bnd_]").each(function ()
			{

				if ($(this).is(":checked"))
				{
					var item_id = this.id.split("_")[1];
					introItemsList[item_id] = "on";
				}
			});

		}

		$("[id^=qty_]").each(function ()
		{

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				itemsList[item_id] = $(this).val();
			}
		});

		$("[id^=sbi_]").each(function ()
		{

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				subItemsList[item_id] = $(this).val();
			}
		});

	}

	var misc_food_subtotal = $("#misc_food_subtotal").val();
	if (isNaN(misc_food_subtotal))
	{
		misc_food_subtotal = "0.00";
	}

	var misc_nonfood_subtotal = $("#misc_nonfood_subtotal").val();
	if (isNaN(misc_nonfood_subtotal))
	{
		misc_nonfood_subtotal = "0.00";
	}

	var subtotal_service_fee = $("#subtotal_service_fee").val();
	if (isNaN(subtotal_service_fee))
	{
		subtotal_service_fee = "0.00";
	}

	var misc_food_subtotal_desc = $("#misc_food_subtotal_desc").val();
	var misc_nonfood_subtotal_desc = $("#misc_nonfood_subtotal_desc").val();
	var service_fee_description = $("#service_fee_description").val();

	var direct_order_discount = $("#direct_order_discount").val();
	if (isNaN(direct_order_discount))
	{
		direct_order_discount = "0.00";
	}

	var PUDSetting = $('input[name=PUD]:checked', '#editorForm').val();

	if (PUDSetting == null || PUDSetting == "")
	{
		PUDSetting = "noUP";
	}

	var plate_points_discount = $("#plate_points_discount").val();
	if (isNaN(plate_points_discount))
	{
		plate_points_discount = "0.00";
	}

	var SessDiscSetting = $('input[name=SessDisc]:checked', '#editorForm').val();

	if (SessDiscSetting == null || SessDiscSetting == "")
	{
		SessDiscSetting = "noSD";
	}

	var DR_level = 0;
	if ($("#dr_level_for_order").length)
	{
		var curOrderLevel = $("#dr_level_for_order").val();
		if (curOrderLevel > 0)
		{
			DR_level = curOrderLevel;
		}
	}

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor',
			store_id: STORE_DETAILS.id,
			user_id: user_id,
			op: 'update_active_order',
			order_id: order_id,
			items: itemsList,
			misc_food_subtotal: misc_food_subtotal,
			misc_nonfood_subtotal: misc_nonfood_subtotal,
			subtotal_service_fee: subtotal_service_fee,
			misc_food_subtotal_desc: misc_food_subtotal_desc,
			misc_nonfood_subtotal_desc: misc_nonfood_subtotal_desc,
			service_fee_description: service_fee_description,
			is_intro: is_intro,
			intro_items: introItemsList,
			sub_items: subItemsList,
			direct_order_discount: direct_order_discount,
			PUDSetting: PUDSetting,
			plate_points_discount: plate_points_discount,
			SessDiscSetting: SessDiscSetting,
			dream_rewards_level: DR_level,
			changeList: JSON.stringify(changeList),
			changeListStr: getChangeListHTML(),
			dd_csrf_token: $('input[name=dd_csrf_token]', '#editorForm').val()

		},
		success: function (json)
		{
			if (json.processor_success)
			{
				intenseLogging("updateActiveOrder() successful");

				itemTabIsDirty = false;
				updateItemsOrgValues();
				updateDiscountOrgValues();

				if (payOnCompletion)
				{
					onAddPaymentAndActivate(payOnCompletion, go_to_confirm, json.getTokenToken);
				}
			}
			else
			{
				intenseLogging("update_active_order: " + json.processor_message);

				dd_message({
					title: 'Error',
					message: json.processor_message
				});

			}
		},
		error: function (objAJAXRequest, strError)
		{

			intenseLogging("update_active_order: " + strError + " | " + objAJAXRequest.responseText);

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});
		}
	});
}

function _validate_discounts()
{
	// check to see if coupon requires free menu selection
	if (couponFreeMenuItemRequired)
	{
		return _check_elements([$('#free_menu_item_coupon').get(0)]);
	}

	return true;
}

function saveDiscounts(payOnCompletion)
{
	intenseLogging("saveDiscounts() called");

	if (!_validate_discounts())
	{
		return false;
	}

	if (!payOnCompletion)
	{
		dd_toast({
			message: 'Discounts Saved',
			position: 'topcenter'
		});
	}

	var direct_order_discount = $("#direct_order_discount").val();
	if (isNaN(direct_order_discount))
	{
		direct_order_discount = "0.00";
	}

	var PUDSetting = $('input[name=PUD]:checked', '#editorForm').val();

	if (PUDSetting == null || PUDSetting == "")
	{
		PUDSetting = "noUP";
	}

	var plate_points_discount = $("#plate_points_discount").val();
	if (isNaN(plate_points_discount))
	{
		plate_points_discount = "0.00";
	}

	var SessDiscSetting = $('input[name=SessDisc]:checked', '#editorForm').val();

	if (SessDiscSetting == null || SessDiscSetting == "")
	{
		SessDiscSetting = "noSD";
	}

	var DR_level = 0;
	if ($("#dr_level_for_order").length)
	{
		var curOrderLevel = $("#dr_level_for_order").val();
		if (curOrderLevel > 0)
		{
			DR_level = curOrderLevel;
		}
	}

	var coupon_id = 0;
	var coupon_free_menu_item = 0;
	if ($("#coupon_id").val())
	{
		if (couponFreeMenuItemRequired)
		{
			if ($('#free_menu_item_coupon').val())
			{
				coupon_free_menu_item = $('#free_menu_item_coupon').val();
			}
		}

		coupon_id = $("#coupon_id").val();
	}

	var fundraiser_id = $('#fundraiser_id').val();
	var fundraiser_value = $('#fundraiser_value').val();

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor',
			store_id: STORE_DETAILS.id,
			op: 'update_discounts',
			order_id: order_id,
			user_id: user_id,
			direct_order_discount: direct_order_discount,
			PUDSetting: PUDSetting,
			plate_points_discount: plate_points_discount,
			SessDiscSetting: SessDiscSetting,
			coupon_id: coupon_id,
			coupon_free_menu_item: coupon_free_menu_item,
			dream_rewards_level: DR_level,
			fundraiser_id: fundraiser_id,
			fundraiser_value: fundraiser_value

		},
		success: function (json)
		{
			if (json.processor_success)
			{
				intenseLogging("saveDiscounts() successful");

				updateDiscountOrgValues();
				updateReportingOrgValues();

				if (coupon_free_menu_item)
				{
					$('#free_menu_item_coupon').attr('disabled', true);
					couponFreeMenuItem = coupon_free_menu_item;
				}

				if (payOnCompletion)
				{
					onAddPaymentAndActivate(false, true, json.paymentToken);
				}
			}
			else
			{
				intenseLogging("update_discounts: " + json.processor_message);

				dd_message({
					title: 'Error',
					message: json.processor_message
				});

			}
		},
		error: function (objAJAXRequest, strError)
		{

			intenseLogging("update_discounts: " + strError + " | " + objAJAXRequest.responseText);

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});

		}

	});
}


function updateReportingOrgValues()
{
	$('#fundraiser_id').data('org_value', $('#fundraiser_id').val());
	$('#fundraiser_value').data('org_value', $('#fundraiser_value').val());

	updateChangeList();
}

function send(token, paymentNumber, warnOfOutstandingSavedOrdersOnFullSession, addOnly, go_to_confirm, payment1Data)
{

	intenseLogging("send() called for payment# " + paymentNumber);

	$('#paypal-result').on("load", function ()
	{

		intenseLogging("result iFrame loading");

		try
		{
			var next_dest = $('#paypal-result').get(0).contentWindow.next_dest;
			var success = $('#paypal-result').get(0).contentWindow.pp_success;

		}
		catch (e)
		{

			intenseLogging("exception occurred accessing result iframe");

			$("#paying_div").remove();

			dd_message({
				title: 'Exception',
				message: 'We are sorry but an unexpected error occurred during payment processing. Please try again or contact support.'
			});

			return;
		}

		// in Safari - exception is not thrown and success is undefined
		// the test is false and we drop into the success = false block
		// then error_code and message also are set to undefined

		if (success)
		{
			intenseLogging("success found in iframe");

			bounce(next_dest);
		}
		else
		{
			var error_code = $('#paypal-result').get(0).contentWindow.error_code;
			var message = $('#paypal-result').get(0).contentWindow.message;

			intenseLogging("error found in iframe: #" + error_code + " | " + message);

			if (typeof error_code == 'undefined')
			{
				error_code = "Generic";
				message = 'Some required information is missing or incorrect. Please correct the fields below and try again.';

				intenseLogging("error code undefined in iframe");

				$("#paying_div").remove();

				dd_message({
					title: 'Error: ' + error_code,
					message: message
				});

			}
			else if (next_dest && next_dest.length > 0)
			{

				$("#paying_div").remove();

				dd_message({
					title: 'Error: ' + error_code,
					message: message,
					noOk: true,
					buttons: {
						"Okay": function ()
						{
							bounce(next_dest);
						}
					}
				});
			}
			else
			{
				$("#paying_div").remove();

				var reload = $('#paypal-result').get(0).contentWindow.reloadOnFailure;

				if (reload)
				{

					intenseLogging("reloadOnFailure found in iframe: reloading");

					message += "<br /><br /><span style='color:red'>The credit card payment failed however the order was booked with the gift certificate. The page will now reload.</span>";

					dd_message({
						title: 'Error: ' + error_code,
						message: message,
						noOk: true,
						buttons: {
							"Okay": function ()
							{
								bounce(window.location.href);
							}
						}
					});
				}
				else
				{
					dd_message({
						title: 'Error: ' + error_code,
						message: message
					});
				}

			}

		}
	});

	var comment_payload = new PayFlow_Payload();

	comment_payload.addNameValuePair('UserID', user_id);
	comment_payload.addNameValuePair('StoreID', STORE_DETAILS.id);
	comment_payload.addNameValuePair('OrderID', order_id); // TODO: need id here of somekind so the payment can be tracked to an order. Either we create a "blank" order
	// or we use another id that is tied to the order when it is later inserted.
	comment_payload.addNameValuePair('Email', user_email);

	if (paymentNumber == 1)
	{
		var expdate = $("#payment1_ccMonth").val();
		expdate += $("#payment1_ccYear").val();
		var csc = $("#payment1_cc_security_code").val();
		var amt = $("#payment1_cc_total_amount").val();

		var payload = new PayFlow_Payload();

		payload.addNameValuePair('admin_user_id', USER_DETAILS.id);
		payload.addNameValuePair('user_id', user_id);
		payload.addNameValuePair('store_id', STORE_DETAILS.id);
		payload.addNameValuePair('order_id', order_id);
		payload.addNameValuePair('caller_id', 'fadmin_order');
		if (addOnly)
		{
			payload.addNameValuePair('add_only', 'true');
		}
		if (go_to_confirm)
		{
			payload.addNameValuePair('go_to_confirm', 'true');
		}
		payload.addNameValuePair('is_second_payment', 'false');

		var use_store_credits = false;
		var store_credit_amount = 0;
		if ($("#use_store_credits").length)
		{
			if ($("#use_store_credits").is(':checked'))
			{
				store_credit_amount = $("#store_credits_amount").val();
				payload.addNameValuePair('use_sc', 'true');
				payload.addNameValuePair('sc_amt', store_credit_amount);
			}
		}

		var DP_State = 0;
		if ($('#payment1_is_store_specific_flat_rate_deposit_delayed_payment1').length)
		{
			DP_State = $('input:radio[name=payment1_is_store_specific_flat_rate_deposit_delayed_payment]:checked').val();

			if (DP_State > 0)
			{
				payload.addNameValuePair('DP_State', DP_State);
				payload.addNameValuePair('org_amount', amt);


				var depositAmount = 20;
				if (DP_State == 1 && store_specific_deposit && store_specific_deposit > 20)
				{
					depositAmount = store_specific_deposit;
				}

				if (amt * 1 <= depositAmount * 1)
				{
					dd_message({
						title: 'Delayed Payment Error',
						message: "The amount of the payment is less than or equal to the deposit. Delayed Payment cannot be used."
					});
					return;
				}

				amt = depositAmount;

			}
		}

		if (typeof payflowErrorURL == 'undefined')
		{
			payflowErrorURL = PATH.https_server + "/processor?processor=admin_payflow_callback";
		}

		// make PARMLIST
		var parmList = 'ERRORURL=' + payflowErrorURL + "&ACCT=" + $("#payment1_ccNumber").val() + "&EXPDATE=" + expdate + "&CSC=" + csc + "&AMT=" + amt + "&USER1=" + payload.retrieveEncodedString() + "&COMMENT1=" + comment_payload.retrieveEncodedString();
	}
	else
	{
		var expdate = $("#payment2_ccMonth").val();
		expdate += $("#payment2_ccYear").val();
		var csc = $("#payment2_cc_security_code").val();
		var amt = $("#payment2_cc_total_amount").val();

		var payload = new PayFlow_Payload();

		payload.addNameValuePair('user_id', user_id);
		payload.addNameValuePair('store_id', STORE_DETAILS.id);
		payload.addNameValuePair('order_id', order_id);
		payload.addNameValuePair('caller_id', 'fadmin_order');
		payload.addNameValuePair('add_only', 'false');
		payload.addNameValuePair('is_second_payment', 'true');
		if (go_to_confirm)
		{
			payload.addNameValuePair('go_to_confirm', 'true');
		}

		if (payment1Data)
		{
			payload.addNameValuePair("hasPayment1", true);

			for (var key in payment1Data)
			{
				if (payment1Data.hasOwnProperty(key))
				{
					payload.addNameValuePair("p1_" + key, payment1Data[key]);
				}
			}
		}

		var DP_State = 0;
		if ($('#payment2_is_store_specific_flat_rate_deposit_delayed_payment1').length)
		{
			DP_State = $('input:radio[name=payment2_is_store_specific_flat_rate_deposit_delayed_payment]:checked').val();

			if (DP_State > 0)
			{
				payload.addNameValuePair('DP_State', DP_State);
				payload.addNameValuePair('org_amount', amt);

				var depositAmount = 20;
				if (DP_State == 1 && store_specific_deposit && store_specific_deposit > 20)
				{
					depositAmount = store_specific_deposit;
				}


				if (amt * 1 <= depositAmount * 1)
				{
					dd_message({
						title: 'Delayed Payment Error',
						message: "The amount of the payment is less than or equal to the deposit. Delayed Payment cannot be used."
					});
					return;
				}

				amt = depositAmount;

			}
		}

		if (typeof payflowErrorURL == 'undefined')
		{
			payflowErrorURL = PATH.https_server + "/processor?processor=admin_payflow_callback";
		}

		// make PARMLIST
		var parmList = 'ERRORURL=' + payflowErrorURL + "&ACCT=" + $("#payment2_ccNumber").val() + "&EXPDATE=" + expdate + "&CSC=" + csc + "&AMT=" + amt + "&USER1=" + payload.retrieveEncodedString() + "&COMMENT1=" + comment_payload.retrieveEncodedString();
	}

	var tr_action = 'https://payflowlink.paypal.com';
	if (typeof pfp_test_mode != 'undefined' && pfp_test_mode)
	{
		var tr_action = 'https://pilot-payflowlink.paypal.com';
	}

	if (typeof transparent_redirect_link != 'undefined')
	{
		tr_action = transparent_redirect_link;
	}

	intenseLogging("posting to PayFlow");

	// Create dynamic form to post and redirect to session_menu
	create_and_submit_form({
		action: tr_action,
		target: "paypal-result",
		input: ({
			SECURETOKEN: token.token,
			SECURETOKENID: token.tokenID,
			PARMLIST: parmList
		})
	});

}

function getPaymentData(type, payment_number)
{

	if (type != 'CC')
	{
		var paymentData = {
			type: type,
			number: 0,
			amount: 0,
			cert_type: '',
			note: $("#payment_note").val()
		}
	}
	else
	{
		var paymentData = {
			type: type,
			number: 0,
			amount: 0,
			cert_type: '',
			note: $("#payment_note").val(),
			ccNameOnCard: "",
			ccType: 'CC',
			ccMonth: "",
			ccYear: "",
			cc_security_code: "",
			billing_address: "",
			billing_postal_code: "",
			is_store_specific_flat_rate_deposit_delayed_payment: 0,
			do_not_ref: 0
		}
	}

	if (payment_number == 1)
	{
		switch (type)
		{
			case "REFERENCE":
				paymentData.amount = $("#payment1_reference_total_amount").val();
				break;

			case "GIFT_CERT":
				paymentData.amount = $("#payment1_gc_total_amount").val();
				paymentData.cert_type = $("#payment1_gift_cert_type").val();
				paymentData.number = $("#payment1_gc_payment_number").val();
				break;

			case "CASH":
				paymentData.amount = $("#payment1_cash_total_amount").val();
				paymentData.number = $("#payment1_cash_payment_number").val();
				break;

			case "REFUND_CASH":
				paymentData.amount = $("#payment1_refund_cash_total_amount").val();
				paymentData.number = $("#payment1_refund_cash_payment_number").val();
				break;

			case "CHECK":
				paymentData.amount = $("#payment1_check_total_amount").val();
				paymentData.number = $("#payment1_check_payment_number").val();
				break;

			case "CREDIT":
				paymentData.amount = $("#OEH_remaing_balance").html();
				break;

			case "CC":
				paymentData.amount = $("#payment1_cc_total_amount").val();
				paymentData.number = $("#payment1_ccNumber").val();
				paymentData.ccNameOnCard = $("#payment1_ccNameOnCard").val();
				paymentData.ccType = $("#payment1_ccType").val();
				paymentData.ccMonth = $("#payment1_ccMonth").val();
				paymentData.ccYear = $("#payment1_ccYear").val();
				paymentData.cc_security_code = $("#payment1_cc_security_code").val();
				paymentData.billing_address = $("#billing_address_1").val();
				paymentData.billing_postal_code = $("#billing_postal_code_1").val();

				var DPSetting = $('input[name=payment1_is_store_specific_flat_rate_deposit_delayed_payment]:checked').val();

				paymentData.is_store_specific_flat_rate_deposit_delayed_payment = DPSetting;

				break;

			case "GIFT_CARD":
				paymentData.amount = $("#debit_gift_card_amount").val();
				paymentData.number = $("#debit_gift_card_number").val();
				break;

			case "DPADJUST":
				// TODO: ???
				break;

			case "REFERENCE_OTHER":
				alert('investigate REFERENCE_OTHER');
				break;

			default:

				if (type.indexOf("REF_") == 0)
				{
					paymentData.amount = $("#payment1_ref_total_amount").val();
					paymentData.reference_id = $("#p1_ref_id").html();
				}

		}

		return paymentData;

	}
	else if (payment_number == 2)
	{
		switch (type)
		{
			case "CASH":
				paymentData.amount = $("#payment2_cash_total_amount").val();
				paymentData.number = $("#payment2_cash_payment_number").val();
				break;

			case "CHECK":
				paymentData.amount = $("#payment2_check_total_amount").val();
				paymentData.number = $("#payment2_check_payment_number").val();
				break;

			case "CC":
				paymentData.amount = $("#payment2_cc_total_amount").val();
				paymentData.number = $("#payment2_ccNumber").val();
				paymentData.ccNameOnCard = $("#payment2_ccNameOnCard").val();
				paymentData.ccType = $("#payment2_ccType").val();
				paymentData.ccMonth = $("#payment2_ccMonth").val();
				paymentData.ccYear = $("#payment2_ccYear").val();
				paymentData.cc_security_code = $("#payment2_cc_security_code").val();
				paymentData.billing_address = $("#billing_address_2").val();
				paymentData.billing_postal_code = $("#billing_postal_code_2").val();

				var DPSetting = $('input[name=payment2_is_store_specific_flat_rate_deposit_delayed_payment]:checked').val();

				paymentData.is_store_specific_flat_rate_deposit_delayed_payment = DPSetting;

				break;

			case "REFERENCE_OTHER":
				alert('investigate REFERENCE_OTHER');
				break;

			default:

				if (type.indexOf("REF_") == 0)
				{
					paymentData.amount = $("#payment2_ref_total_amount").val();
					paymentData.reference_id = $("#p2_ref_id").html();
				}

		}

		return paymentData;
	}

}

function validatePayment1(type)
{

	var inputArray = null;
	var validates = true;

	if (type.substr(0, 4) == "REF_")
	{
		type = "REFERENCE_OTHER";
	}

	switch (type)
	{
		case "REFERENCE":
			inputArray = $("#payment1_reference :input");
			validates = _check_elements(inputArray);
			break;

		case "GIFT_CERT":
			inputArray = $("#payment1_gc :input");
			validates = _check_elements(inputArray);
			break;

		case "CASH":
			inputArray = $("#payment1_cash :input");
			validates = _check_elements(inputArray);
			break;

		case "REFUND_CASH":
			inputArray = $("#payment1_refund_cash :input");
			validates = _check_elements(inputArray);
			break;

		case "CHECK":
			inputArray = $("#payment1_check :input");
			validates = _check_elements(inputArray);
			break;

		case "CREDIT":
			inputArray = $("#payment1_credit :input");
			validates = _check_elements(inputArray);
			break;

		case "CC":
			inputArray = $("#payment1_cc :input");
			validates = _check_elements(inputArray);

			var depositWarningStr = "The deposit must be less than the payment amount entered. Note that the amount entered should be for the full payment (both deposit and delayed payment). The deposit will be deducted automatically.";

			if ($("#payment1_is_store_specific_flat_rate_deposit_delayed_payment1").is(":checked") || $("#payment1_is_store_specific_flat_rate_deposit_delayed_payment2").is(":checked"))
			{
				var CC1Amount = $("#payment1_cc_total_amount").val();
				if (CC1Amount * 1 <= store_specific_deposit * 1)
				{
					$("#payment1_CC_DP_note").html(depositWarningStr);
					$("#payment1_CC_DP_note").show();
					validates = false;
				}
			}

			break;

		case "GIFT_CARD":
			inputArray = $("#payment1_debit_gift_card :input");
			validates = _check_elements(inputArray);
			break;

		case "DPADJUST":
			inputArray = $("#dp_adjust_div :input");
			validates = _check_elements(inputArray);
			break;

		case "REFERENCE_OTHER":
			inputArray = $("#payment1_ref :input");
			validates = _check_elements(inputArray);

			var depositWarningStr = "The deposit must be less than the payment amount entered. Note that the amount entered should be for the full payment (both deposit and delayed payment). The deposit will be deducted automatically.";

			if ($("#ref_payment1_is_store_specific_flat_rate_deposit_delayed1").is(":checked") || $("#ref_payment1_is_store_specific_flat_rate_deposit_delayed2").is(":checked"))
			{
				var CC1Amount = $("#payment1_ref_total_amount").val();
				if (CC1Amount * 1 <= store_specific_deposit * 1)
				{
					$("#payment1_Ref_DP_note").html(depositWarningStr);
					$("#payment1_Ref_DP_note").show();
					validates = false;
				}
			}
			break;

		default:
	}

	return validates;

}

function validatePayment2(type)
{

	var inputArray = null;
	var validates = true;

	if (type.substr(0, 4) == "REF_")
	{
		type = "REFERENCE_OTHER";
	}

	switch (type)
	{

		case "CASH":
			inputArray = $("#payment2_check :input");
			validates = _check_elements(inputArray);
			break;

		case "CHECK":
			inputArray = $("#payment2_check :input");
			validates = _check_elements(inputArray);
			break;

		case "CC":
			inputArray = $("#payment2_cc :input");
			validates = _check_elements(inputArray);

			var depositWarningStr = "The deposit must be less than the payment amount entered. Note that the amount entered should be for the full payment (both deposit and delayed payment). The deposit will be deducted automatically.";

			if ($("#payment2_is_store_specific_flat_rate_deposit_delayed_payment1").is(":checked") || $("#payment2_is_store_specific_flat_rate_deposit_delayed_payment2").is(":checked"))
			{
				var CC1Amount = $("#payment2_cc_total_amount").val();
				if (CC1Amount * 1 <= store_specific_deposit * 1)
				{
					$("#payment2_CC_DP_note").html(depositWarningStr);
					$("#payment2_CC_DP_note").show();
					validates = false;
				}
			}

			break;

		case "REFERENCE_OTHER":
			inputArray = $("#payment2_ref :input");
			validates = _check_elements(inputArray);

			var depositWarningStr = "The deposit must be less than the payment amount entered. Note that the amount entered should be for the full payment (both deposit and delayed payment). The deposit will be deducted automatically.";

			if ($("#ref_payment2_is_store_specific_flat_rate_deposit_delayed1").is(":checked") || $("#ref_payment2_is_store_specific_flat_rate_deposit_delayed2").is(":checked"))
			{
				var CC1Amount = $("#payment2_ref_total_amount").val();
				if (CC1Amount * 1 <= store_specific_deposit * 1)
				{
					$("#payment2_Ref_DP_note").html(depositWarningStr);
					$("#payment2_Ref_DP_note").show();
					validates = false;
				}
			}

			break;

		default:
	}

	return validates;
}

function addPaymentAndActivate()
{

	intenseLogging("addPaymentAndActivate() called");

	var servingsCount = Number($('#OEH_number_servings').html());

	if (servingsCount.valueOf() < 36 && !isDreamTaste && !isFundraiser && !$("#selectedBundle").is(":checked"))
	{

		intenseLogging("addPaymentAndActivate() - warning for less than 36 servings");

		dd_message({
			title: 'Warning',
			message: 'This order is for less than 36 servings. Do you wish to continue?',
			confirm: function ()
			{
				// always first ensure that discounts are updated on server
				intenseLogging("addPaymentAndActivate() - confirm dialog");

				var message = "You are about to activate a Saved order. This will consume inventory and a session slot. Are you sure you want to continue?";
				var changesBlurb = getAbbreviatedSummaryString();
				message += "<br /><br />" + changesBlurb;

				dd_message({
					title: 'Add Payment and Book Order',
					div_id: 'aPaBO',
					message: message,
					modal: true,
					width: 400,
					height: 460,
					confirm: function ()
					{
						var discounts_are_valid = saveDiscounts(true);
					}
				});

			}
		});
	}
	else
	{

		intenseLogging("addPaymentAndActivate() - confirm dialog");

		if (hasBundle)
		{
			var bundleIsSelected = false;
			if ($('#selectedBundle').is(':checked'))
			{
				bundleIsSelected = true;
			}

			if (bundleIsSelected)
			{

				var numBundItems = countSelectedBundleItems();
				if (numBundItems < 18)
				{

					dd_message({
						title: 'Error',
						message: 'Please select at least 18-servings of Meal Prep Starter Pack items.'
					});

					return false;
				}
				else if (numBundItems > 18)
				{
					dd_message({
						title: 'Error',
						message: 'Please select no more than 18 servings of Meal Prep Starter Pack items.'
					});

					return false;
				}
			}
		}

		if (stationNeedsAttention)
		{
			dd_message({
				title: 'Error',
				message: 'Please review the Side Station and ensure the correct number of items are selected.'
			});
			return false;
		}


		// always first ensure that discounts are updated on server
		var message = "You are about to activate a Saved order. This will consume inventory and a session slot. Are you sure you want to continue?";
		var changesBlurb = getAbbreviatedSummaryString();
		message += "<br /><br />" + changesBlurb;

		dd_message({
			title: 'Add Payment and Book Order',
			message: message,
			modal: true,
			width: 400,
			height: 460,
			confirm: function ()
			{
				var discounts_are_valid = saveDiscounts(true);
			}
		});

	}

	return true;
}

function save2PaymentsAndBookOrder(payment2Type, payment1Data, token)
{

	intenseLogging("save2PaymentsAndBookOrder() called");

	var payment2Data = getPaymentData(payment2Type, 2);

	if (payment2Type != "CC" || !supports_transparent_redirect)
	{

		var DP_State = 0;
		if ($('#ref_payment2_is_store_specific_flat_rate_deposit_delayed0').length)
		{
			DP_State = $('input:radio[name=ref_payment2_is_store_specific_flat_rate_deposit_delayed]:checked').val();

			if (DP_State == 1)
			{
				DP_State = 4;
			} // translate to CPayment constants - for now
			if (DP_State == 2)
			{
				DP_State = 5;
			}

			if (DP_State > 0)
			{
				// TODO: validate amounts
			}
		}

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			async: true,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor',
				store_id: STORE_DETAILS.id,
				op: 'save_payment',
				order_id: order_id,
				session_id: session_id,
				user_id: user_id,
				payment_data: payment1Data,
				add_payment_data: payment2Data,
				delay_remainder: DP_State,
				dd_csrf_token: token
			},
			success: function (json)
			{
				if (json.processor_success)
				{
					intenseLogging("save2PaymentsAndBookOrder() save_payment successful");

					if (json.warnOfOutstandingSavedOrdersOnFullSession)
					{
						bounce("/?page=admin_order_mgr_thankyou&order=" + json.order_id + '&full_session=true');
					}
					else
					{
						bounce("/?page=admin_order_mgr_thankyou&order=" + json.order_id);
					}

				}
				else
				{
					$("#paying_div").remove();

					intenseLogging("save2PaymentsAndBookOrder save_payment: " + json.processor_message);

					dd_message({
						title: 'Error',
						message: json.processor_message
					});
				}
			},
			error: function (objAJAXRequest, strError)
			{
				$("#paying_div").remove();

				intenseLogging("save2PaymentsAndBookOrder save_payment: " + strError + " | " + objAJAXRequest.responseText);

				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});
			}
		});
	}
	else if (payment2Type == "CC")
	{

		var billing_name = $("#payment2_ccNameOnCard").val();
		var billing_address = $("#billing_address_2").val();
		var billing_zip = $("#billing_postal_code_2").val();

		var amt = $("#payment2_cc_total_amount").val();

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			async: true,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor',
				store_id: STORE_DETAILS.id,
				user_id: user_id,
				op: 'get_token',
				order_id: order_id,
				amount: amt,
				billing_name: billing_name,
				billing_address: billing_address,
				billing_zip: billing_zip,
				dd_csrf_token: token,
				chain_token: true
			},
			success: function (json)
			{
				$('input[name=dd_csrf_token]', '#editorForm').val(json.getTokenToken);

				if (json.processor_success)
				{

					intenseLogging("save2PaymentsAndBookOrder() get_token successful");

					var token = json.token;

					send(token, 2, json.warnOfOutstandingSavedOrdersOnFullSession, false, false, payment1Data);

				}
				else
				{
					$("#paying_div").remove();

					intenseLogging("save2PaymentsAndBookOrder get_token: " + json.processor_message);

					dd_message({
						title: 'Error',
						message: json.processor_message
					});

				}
			},
			error: function (objAJAXRequest, strError)
			{
				$("#paying_div").remove();

				intenseLogging("save2PaymentsAndBookOrder get_token: " + strError + " | " + objAJAXRequest.responseText);

				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});

			}
		});
	}
}

function savePayment2(payment2Type, warnOfOutstandingSavedOrdersOnFullSession, token)
{

	intenseLogging("savePayment2() called");

	var payment2Data = getPaymentData(payment2Type, 2);

	if (payment2Type != "CC" || !supports_transparent_redirect)
	{

		var DP_State = 0;
		if ($('#ref_payment2_is_store_specific_flat_rate_deposit_delayed0').length)
		{
			DP_State = $('input:radio[name=ref_payment2_is_store_specific_flat_rate_deposit_delayed]:checked').val();

			if (DP_State == 1)
			{
				DP_State = 4;
			} // translate to CPayment constants - for now
			if (DP_State == 2)
			{
				DP_State = 5;
			}

			if (DP_State > 0)
			{
				// TODO: validate amounts
			}
		}

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			async: true,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor',
				store_id: STORE_DETAILS.id,
				op: 'add_payment',
				order_id: order_id,
				user_id: user_id,
				payment_data: payment2Data,
				delay_remainder: DP_State,
				dd_csrf_token: token
			},
			success: function (json)
			{
				if (json.processor_success)
				{
					intenseLogging("savePayment2() add_payment successful");

					if (warnOfOutstandingSavedOrdersOnFullSession)
					{
						bounce("/?page=admin_order_mgr_thankyou&order=" + json.order_id + '&full_session=true');
					}
					else
					{
						bounce("/?page=admin_order_mgr_thankyou&order=" + json.order_id);
					}

				}
				else
				{
					$("#paying_div").remove();

					intenseLogging("savePayment2 add_payment: " + json.processor_message);

					dd_message({
						title: 'Error',
						message: json.processor_message
					});
				}
			},
			error: function (objAJAXRequest, strError)
			{
				$("#paying_div").remove();

				intenseLogging("savePayment2 add_payment: " + strError + " | " + objAJAXRequest.responseText);

				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});
			}
		});
	}
	else if (payment2Type == "CC")
	{

		var billing_name = $("#payment2_ccNameOnCard").val();
		var billing_address = $("#billing_address_2").val();
		var billing_zip = $("#billing_postal_code_2").val();

		var amt = $("#payment2_cc_total_amount").val();

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			async: true,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor',
				store_id: STORE_DETAILS.id,
				user_id: user_id,
				op: 'get_token',
				order_id: order_id,
				amount: amt,
				billing_name: billing_name,
				billing_address: billing_address,
				billing_zip: billing_zip,
				dd_csrf_token: token,
				chain_token: true
			},
			success: function (json)
			{
				$('input[name=dd_csrf_token]', '#editorForm').val(json.getTokenToken);

				if (json.processor_success)
				{

					intenseLogging("savePayment2() get_token successful");

					var token = json.token;

					send(token, 2, warnOfOutstandingSavedOrdersOnFullSession, false, false);

				}
				else
				{
					$("#paying_div").remove();

					intenseLogging("savePayment2 get_token: " + json.processor_message);

					dd_message({
						title: 'Error',
						message: json.processor_message
					});

				}
			},
			error: function (objAJAXRequest, strError)
			{
				$("#paying_div").remove();

				intenseLogging("savePayment2 get_token: " + strError + " | " + objAJAXRequest.responseText);

				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});

			}
		});
	}
}

function handleDirectPayment(addOnly, go_to_confirm, token)
{

	intenseLogging("handleDirectPayment() called");

	var payment1Type = $("#payment1_type").val();
	var payment2Type = false;

	if (payment1Type == 'GIFT_CERT' || payment1Type == 'GIFT_CARD')
	{
		payment2Type = $("#payment2_type").val();
	}

	// validation
	if (!validatePayment1(payment1Type))
	{
		return false;
	}

	// validation
	if (payment2Type && payment2Type != "" && !validatePayment2(payment2Type))
	{
		return false;
	}

	displayModalWaitDialog('paying_div', "Sending Payment. Please wait ...");

	var payment1Data = getPaymentData(payment1Type, 1);

	// Handle Payment 1 for certain
	// Then handle Payment 2 unless it's a CC payment.

	var DP_State = 0;
	if ($('#ref_payment1_is_store_specific_flat_rate_deposit_delayed0').length)
	{
		DP_State = $('input:radio[name=ref_payment1_is_store_specific_flat_rate_deposit_delayed]:checked').val();

		if (DP_State == 1)
		{
			DP_State = 4;
		} // translate to CPayment constants - for now
		if (DP_State == 2)
		{
			DP_State = 5;
		}

		if (DP_State > 0)
		{
			// TODO: validate amounts
		}
	}

	var use_store_credits = false;
	var store_credit_amount = 0;
	if ($("#use_store_credits").length)
	{
		if ($("#use_store_credits").is(':checked'))
		{
			store_credit_amount = $("#store_credits_amount").val();
			use_store_credits = true;
		}
	}

	var mainPaymentData = null;
	var additionalPaymentData = null;

	if (payment2Type)
	{
		mainPaymentData = getPaymentData(payment2Type, 2);
		additionalPaymentData = payment1Data;
	}
	else
	{
		mainPaymentData = payment1Data;
	}

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 90000,
		async: true,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor',
			store_id: store_id,
			op: 'save_payment',
			order_id: order_id,
			user_id: user_id,
			payment_data: mainPaymentData,
			add_payment_data: additionalPaymentData,
			delay_remainder: DP_State,
			use_store_credits: use_store_credits,
			store_credits_amount: store_credit_amount,
			dd_csrf_token: token,
			payment2Type: payment2Type
		},
		success: function (json)
		{
			if (json.processor_success)
			{
				intenseLogging("handleDirectPayment() save_payment successful");

				if (json.warnOfOutstandingSavedOrdersOnFullSession)
				{
					bounce("/?page=admin_order_mgr_thankyou&order=" + json.order_id + '&full_session=true');
				}
				else
				{
					bounce("/?page=admin_order_mgr_thankyou&order=" + json.order_id);
				}
			}
			else
			{
				$("#paying_div").remove();

				intenseLogging("handleDirectPayment save_payment: " + json.processor_message);

				dd_message({
					title: 'Error',
					message: json.processor_message
				});
			}
		},
		error: function (objAJAXRequest, strError)
		{
			if (strError == 'timeout')
			{

				dd_message({
					title: 'Order Processing Timed Out',
					message: "The request to finalized the order has timed out. This may be due to network congestion. The order may have been successful. The page will refresh and show the actual current status.",
					modal: true,
					noOk: true,
					closeOnEscape: false,
					buttons: {
						'Refresh': function ()
						{
							$(this).remove();
							bounce(window.location.href);

						},
					}
				});
			}
			else
			{
			$("#paying_div").remove();

			intenseLogging("handleDirectPayment save_payment: " + strError + " | " + objAJAXRequest.responseText);

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});
		}
		}
	});
}

function onAddPaymentAndActivate(addOnly, go_to_confirm, token)
{

	intenseLogging("onAddPaymentAndActivate() called");

	if (!supports_transparent_redirect)
	{
		return handleDirectPayment(addOnly, go_to_confirm, token);
	}

	var payment1Type = $("#payment1_type").val();
	var payment2Type = false;

	if (payment1Type == 'GIFT_CERT' || payment1Type == 'GIFT_CARD')
	{
		payment2Type = $("#payment2_type").val();
	}

	// validation
	if (!validatePayment1(payment1Type))
	{
		return false;
	}

	// validation
	if (payment2Type && payment2Type != "" && !validatePayment2(payment2Type))
	{
		return false;
	}

	displayModalWaitDialog('paying_div', "Sending Payment. Please wait ...");

	var payment1Data = getPaymentData(payment1Type, 1);

	if (payment2Type && payment2Type != "")
	{
		save2PaymentsAndBookOrder(payment2Type, payment1Data, token);
		return;
	}

	if (payment1Type != "" && payment1Type != "CC")
	{
		// Handle Payment 1 for certain
		// Then handle Payment 2 unless it's a CC payment.

		var DP_State = 0;
		if ($('#ref_payment1_is_store_specific_flat_rate_deposit_delayed0').length)
		{
			DP_State = $('input:radio[name=ref_payment1_is_store_specific_flat_rate_deposit_delayed]:checked').val();

			if (DP_State == 1)
			{
				DP_State = 4;
			} // translate to CPayment constants - for now
			if (DP_State == 2)
			{
				DP_State = 5;
			}

			if (DP_State > 0)
			{
				// TODO: validate amounts
			}
		}

		var use_store_credits = false;
		var store_credit_amount = 0;
		if ($("#use_store_credits").length)
		{
			if ($("#use_store_credits").is(':checked'))
			{
				store_credit_amount = $("#store_credits_amount").val();
				use_store_credits = true;
			}
		}

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			async: true,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor',
				store_id: STORE_DETAILS.id,
				op: 'save_payment',
				order_id: order_id,
				user_id: user_id,
				payment_data: payment1Data,
				delay_remainder: DP_State,
				use_store_credits: use_store_credits,
				store_credits_amount: store_credit_amount,
				dd_csrf_token: token
			},
			success: function (json)
			{
				if (json.processor_success)
				{

					intenseLogging("onAddPaymentAndActivate() save_payment successful");

					if (json.warnOfOutstandingSavedOrdersOnFullSession)
					{
						bounce("/?page=admin_order_mgr_thankyou&order=" + json.order_id + '&full_session=true');
					}
					else
					{
						bounce("/?page=admin_order_mgr_thankyou&order=" + json.order_id);
					}
				}
				else
				{
					$("#paying_div").remove();

					intenseLogging("onAddPaymentAndActivate save_payment: " + json.processor_message);

					dd_message({
						title: 'Error',
						message: json.processor_message
					});

				}
			},
			error: function (objAJAXRequest, strError)
			{
				$("#paying_div").remove();

				intenseLogging("onAddPaymentAndActivate save_payment: " + strError + " | " + objAJAXRequest.responseText);

				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});
			}
		});

	}
	else if (payment1Type != "" && payment1Type == "CC")
	{
		/*

		 '#payment1_is_store_specific_flat_rate_deposit_delayed_payment1, #payment1_is_store_specific_flat_rate_deposit_delayed_payment2, ' +
		 ' #ref_payment1_is_store_specific_flat_rate_deposit_delayed1, #ref_payment1_is_store_specific_flat_rate_deposit_delayed2, ' +
		 ' #payment2_is_store_specific_flat_rate_deposit_delayed_payment1, #payment2_is_store_specific_flat_rate_deposit_delayed_payment2, ' +
		 ' #ref_payment2_is_store_specific_flat_rate_deposit_delayed1, #ref_payment2_is_store_specific_flat_rate_deposit_delayed2'
		 */

		var billing_name = $("#payment1_ccNameOnCard").val();
		var billing_address = $("#billing_address_1").val();
		var billing_zip = $("#billing_postal_code_1").val();
		var amt = $("#payment1_cc_total_amount").val();

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			async: true,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor',
				store_id: STORE_DETAILS.id,
				op: 'get_token',
				order_id: order_id,
				amount: amt,
				user_id: user_id,
				billing_name: billing_name,
				billing_address: billing_address,
				billing_zip: billing_zip,
				dd_csrf_token: token,
				chain_token: true
			},
			success: function (json)
			{
				$('input[name=dd_csrf_token]', '#editorForm').val(json.getTokenToken);

				if (json.processor_success)
				{

					intenseLogging("onAddPaymentAndActivate() get_token successful");

					var token = json.token;

					send(token, 1, false, addOnly, go_to_confirm, false);

				}
				else
				{
					$("#paying_div").remove();

					intenseLogging("onAddPaymentAndActivate get_token: " + json.processor_message);

					dd_message({
						title: 'Error',
						message: json.processor_message
					});

				}
			},
			error: function (objAJAXRequest, strError)
			{
				$("#paying_div").remove();

				intenseLogging("onAddPaymentAndActivate get_token: " + strError + " | " + objAJAXRequest.responseText);

				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});

			}

		});
	}
}

function RetrieveCalendar(timestamp)
{

	intenseLogging("RetrieveCalendar() called");

	var op = 'retrieve';
	if (orderState != 'NEW')
	{
		op = 'retrieve_for_reschedule';
	}

	$.ajax({
		url: '/processor',
		type: 'GET',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_calendarProcessor',
			store_id: store_id,
			action: op,
			cur_session_id: session_id,
			timestamp: timestamp

		},
		success: function (json)
		{
			if (json.processor_success)
			{

				intenseLogging("RetrieveCalendar() successful");

				$("#calendar_holder").html(json.data);
			}
			else
			{

				intenseLogging("RetrieveCalendar: " + json.processor_message);

				dd_message({
					title: 'Error',
					message: json.processor_message
				});

			}
		},
		error: function (objAJAXRequest, strError)
		{
			intenseLogging("RetrieveCalendar: " + strError + " | " + objAJAXRequest.responseText);

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});

		}

	});

}

function onDayClick(obj)
{
}

function doReschedule(id, sessionTime)
{
	var curSessionDate = $("#curSessionDate").html();

	dd_message({
		title: 'Reschedule',
		message: '<div style="text-align: center;"><div>From</div><div style="font-weight: bold;">' + curSessionDate + '</div><div>to</div><div style="font-weight: bold;">' + sessionTime + '</div>',
		modal: true,
		confirm: function ()
		{

			var org_session_id = session_id;
			session_id = id;
			Reschedule(org_session_id);

		},
		open: function ()
		{

			$(this).siblings('.ui-dialog-buttonpane').find("button:contains('Confirm')").focus();
		}

	});

}

function onRescheduleClick(id, sessionTime, isDiscounted, control_message)
{
	if (orderState != 'NEW')
	{
		if (control_message == "locked")
		{
			dd_message({
				title: 'Reschedule',
				message: "You cannot reschedule to this session as it is in the previous calendar month and all activity for that month has been locked and finalized."
			});


		}
		else if (control_message == "tononpreasm")
		{

			dd_message({
				title: 'Reschedule',
				message: "This session is a standard customer assembly session; your order is for Made for You items. Are you sure you want to select a Standard session? The service fee will be removed.",
				modal: true,
				div_id: "tononpreasm_div",
				confirm: function ()
				{
					doReschedule(id, sessionTime);
					$("#tononpreasm_div").remove();
				}

			});

		}
		else if (control_message == "topreasm")
		{

			dd_message({
				title: 'Reschedule',
				message: "This session is a Made for You session; your order is for standard customer assembly items. Are you sure you want to select a Special Event session? A service fee will be applied.",
				modal: true,
				div_id: "topreasm_div",
				confirm: function ()
				{
					doReschedule(id, sessionTime);
					$("#topreasm_div").remove();
				}

			});
		}
		else
		{
			doReschedule(id, sessionTime);
		}
	}
}

function onSessionClick(id, sessionTime, isDiscounted)
{
	if (orderState == 'NEW')
	{
		setSessionAndSave(id);
	}
}

function monthChange(monthVal)
{
	RetrieveCalendar(monthVal);
}

function handle_delayed_payment()
{
	if (tc_delayed_payment_agree)
	{
		return;
	}

	$('#payment1_is_store_specific_flat_rate_deposit_delayed_payment1, #payment1_is_store_specific_flat_rate_deposit_delayed_payment2, ' +
	' #ref_payment1_is_store_specific_flat_rate_deposit_delayed1, #ref_payment1_is_store_specific_flat_rate_deposit_delayed2, ' +
	' #payment2_is_store_specific_flat_rate_deposit_delayed_payment1, #payment2_is_store_specific_flat_rate_deposit_delayed_payment2, ' +
	' #ref_payment2_is_store_specific_flat_rate_deposit_delayed1, #ref_payment2_is_store_specific_flat_rate_deposit_delayed2').on('click', function (e)
	{

		dd_message({
			title: lang.en.tc.terms_and_conditions,
			message: lang.en.tc.delayed_payment,
			modal: true,
			noOk: true,
			closeOnEscape: false,
			open: function (event, ui)
			{
				$(this).parent().find('.ui-dialog-titlebar-close').hide();
			},
			buttons: {
				'Agree': function ()
				{

					$(this).remove();

					$('#payment1_is_store_specific_flat_rate_deposit_delayed_payment1, #payment1_is_store_specific_flat_rate_deposit_delayed_payment2, ' +
					' #ref_payment1_is_store_specific_flat_rate_deposit_delayed1, #ref_payment1_is_store_specific_flat_rate_deposit_delayed2, ' +
					' #payment2_is_store_specific_flat_rate_deposit_delayed_payment1, #payment2_is_store_specific_flat_rate_deposit_delayed_payment2, ' +
					' #ref_payment2_is_store_specific_flat_rate_deposit_delayed1, #ref_payment2_is_store_specific_flat_rate_deposit_delayed2').off('click');

					set_user_pref('TC_DELAYED_PAYMENT_AGREE', 1, user_id);

				},
				'Decline': function ()
				{

					$('#payment1_is_store_specific_flat_rate_deposit_delayed_payment0, #payment2_is_store_specific_flat_rate_deposit_delayed_payment0, ' +
					'#ref_payment1_is_store_specific_flat_rate_deposit_delayed0, #ref_payment2_is_store_specific_flat_rate_deposit_delayed0').trigger('click');

					dd_message({
						title: lang.en.tc.terms_and_conditions,
						message: lang.en.tc.delayed_payment_decline
					});

					set_user_pref('TC_DELAYED_PAYMENT_AGREE', 0, user_id);

				}
			}
		});

	});
}

function createBonusCredit(creditConsumed)
{
	isFactoringCredit = true;
	currentCreditAvailable = creditBasis * 0.10;

	if (creditConsumed > currentCreditAvailable)
	{
		creditConsumed = currentCreditAvailable;
		currentCreditAvailable = 0;
	}
	else
	{
		currentCreditAvailable -= creditConsumed;

	}

	$('#couponValue').val(creditConsumed);
	$("#OEH_bonus_credit").html(" ( Bonus Credit: " + formatAsMoney(currentCreditAvailable) + " )");
}

function removeBonusCredit()
{
	isFactoringCredit = false;
	$("#OEH_bonus_credit").html("");
}

function handleSummaryDisplayChange(obj)
{
	if (obj.checked)
	{
		ShowAllLineItems();
	}
	else
	{
		HideLineItemsIfZero();
	}
}

function HideLineItemsIfZero()
{
	var serviceFeeOrg = Number($('#OEH_subtotal_service_fee_org').html());
	var serviceFee = Number($('#OEH_subtotal_service_fee').html());

	if (serviceFeeOrg == 0 && serviceFee == 0)
	{
		$('#ServiceFeeRow').hide();
	}
	else
	{
		$('#ServiceFeeRow').show();
	}

	var serviceTaxOrg = Number($('#OEH_service_tax_subtotal_org').html());
	var serviceTax = Number($('#OEH_service_tax_subtotal').html());

	if (serviceTaxOrg == 0 && serviceTax == 0)
	{
		$('#ServiceTaxRow').hide();
	}
	else
	{
		$('#ServiceTaxRow').show();
	}

	var miscFoodOrg = Number($('#OEH_misc_food_subtotal_org').html());
	var miscFood = Number($('#OEH_misc_food_subtotal').html());

	if (miscFoodOrg == 0 && miscFood == 0)
	{
		$('#miscFoodRow').hide();
	}
	else
	{
		$('#miscFoodRow').show();
	}

	var miscNonFoodOrg = Number($('#OEH_misc_nonfood_subtotal_org').html());
	var miscNonFood = Number($('#OEH_misc_nonfood_subtotal').html());

	if (miscNonFoodOrg == 0 && miscNonFood == 0)
	{
		$('#miscNonFoodRow').hide();
	}
	else
	{
		$('#miscNonFoodRow').show();
	}

	var doDiscountOrg = Number($('#OEH_direct_order_discount_org').html());
	var doDiscount = Number($('#OEH_direct_order_discount').html());

	if (doDiscountOrg == 0 && doDiscount == 0)
	{
		$('#directDiscountRow').hide();
	}
	else
	{
		$('#directDiscountRow').show();
	}

	var couponDiscountOrg = Number($('#OEH_coupon_discount_org').html());
	var couponDiscount = Number($('#OEH_coupon_discount').html());

	if (couponDiscountOrg == 0 && couponDiscount == 0)
	{
		$('#couponDiscountRow').hide();
	}
	else
	{
		$('#couponDiscountRow').show();
	}

	var platePointsDiscountOrg = Number($('#OEH_plate_points_discount_org_food').html());
	var platePointsDiscount = Number($('#OEH_plate_points_order_discount_food').html());

	if (platePointsDiscountOrg == 0 && platePointsDiscount == 0)
	{
		$('#platePointsDiscountRow').hide();
	}
	else
	{
		$('#platePointsDiscountRow').show();
	}

	var platePointsDiscountOrg = Number($('#OEH_plate_points_discount_org_fee').html());
	var platePointsDiscount = Number($('#OEH_plate_points_order_discount_fee').html());

	if (platePointsDiscountOrg == 0 && platePointsDiscount == 0)
	{
		$('#platePointsFeeDiscountRow').hide();
	}
	else
	{
		$('#platePointsFeeDiscountRow').show();
	}

	var sessionDiscountOrg = Number($('#OEH_session_discount').html());

	if (sessionDiscountOrg == 0)
	{
		$('#sessionDiscountRow').hide();
	}
	else
	{
		$('#sessionDiscountRow').show();
	}

	var pudDiscount = Number($('#OEH_preferred').html());

	if (pudDiscount == 0)
	{
		$('#preferredUserDiscountRow').hide();
	}
	else
	{
		$('#preferredUserDiscountRow').show();
	}

	var foodTaxOrg = Number($('#OEH_food_tax_subtotal_org').html());
	var foodTax = Number($('#OEH_food_tax_subtotal').html());

	if (foodTaxOrg == 0 && foodTax == 0)
	{
		$('#foodTaxRow').hide();
	}
	else
	{
		$('#foodTaxRow').show();
	}

	var nonFoodTaxOrg = Number($('#OEH_tax_subtotal_org').html());
	var nonFoodTax = Number($('#OEH_tax_subtotal').html());

	if (nonFoodTaxOrg == 0 && nonFoodTax == 0)
	{
		$('#nonFoodTaxRow').hide();
	}
	else
	{
		$('#nonFoodTaxRow').show();
	}

	var productsSubtotalOrg = Number($('#OEH_products_subtotal_org').html());
	var productsSubtotal = Number($('#OEH_products_subtotal').html());

	if (productsSubtotalOrg == 0 && productsSubtotal == 0)
	{
		$('#productSubtotalRow').hide();
	}
	else
	{
		$('#productSubtotalRow').show();
	}
}

function ShowAllLineItems()
{
	$('#ServiceFeeRow').show();
	$('#ServiceTaxRow').show();
	$('#miscFoodRow').show();
	$('#miscNonFoodRow').show();
	$('#directDiscountRow').show();
	$('#couponDiscountRow').show();
	$('#foodTaxRow').show();
	$('#nonFoodTaxRow').show();
	$('#productSubtotalRow').show();
	$('#platePointsDiscountRow').show();
	$('#preferredUserDiscountRow').show();
	$('#sessionDiscountRow').show();
}

function sort_by_price(a, b)
{
	if (a.price == b.price)
	{
		return 0;
	}

	return (a.price < b.price) ? 1 : -1;
}

function getPlatePointsDiscountableAmount(items, total)
{
	retVal = 0;

	var nonDiscountableAmount = 0;
	var servingsCount = 0;

	if (items.length > 0)
	{
		items.sort(sort_by_price);

		for (var i = 0; i < items.length; i++)
		{

			var thisItem = items[i];

			var servingThisItem = thisItem.qty * thisItem.serving_size;
			var costThisItem = thisItem.qty * thisItem.price;
			servingsCount += servingThisItem;

			if (servingsCount == 36)
			{
				nonDiscountableAmount += costThisItem;
				break;
			}
			else if (servingsCount > 36)
			{
				var orgAmount = servingsCount - servingThisItem;
				var remainingServings = 36 - orgAmount;
				var remainingItems = remainingServings / thisItem.serving_size;
				nonDiscountableAmount += (remainingItems * thisItem.price);
				break;
			}
			else
			{
				nonDiscountableAmount += costThisItem;
			}
		}

		retVal = total - nonDiscountableAmount;

	}

	if (servingsCount < 36)
	{
		return 0;
	}

	var serviceFee = Number(document.getElementById('subtotal_service_fee').value);
	retVal += serviceFee.valueOf();

	var miscFoodSubtotal = Number(document.getElementById('misc_food_subtotal').value);
	retVal += miscFoodSubtotal.valueOf();

	return retVal;
}

function drawChangeList()
{
	var html = getChangeListHTML();
	$("#changelist").html(html);

}

function getChangeListHTML()
{

	var htmlStr = "";
	var first = true;
	for (var item in changeList.stdMenuItems)
	{
		if (first)
		{
			htmlStr += "<h3>Standard Items List</h3><ul style='margin: 4px;'>";
			first = false;
		}

		htmlStr += "<li>";

		if (changeList.stdMenuItems.hasOwnProperty(item))
		{
			var title = $("#qty_" + item).data("item_title");
			var pricing_type = "(" + $("#qty_" + item).data("servings") + " Serving)";

			if (changeList.stdMenuItems[item] > 0)
			{
				htmlStr += "Added " + changeList.stdMenuItems[item] + " " + pricing_type + " <i>" + title + "</i>";
			}
			else
			{
				htmlStr += "Removed " + changeList.stdMenuItems[item] * -1 + " " + pricing_type + " <i>" + title + "</i>";
			}
		}

		htmlStr += "</li>";

	}

	if (!first)
	{
		htmlStr += "</ul>";
	}

	first = true;
	for (var item in changeList.FTMenuItems)
	{
		if (first)
		{
			htmlStr += "<h3>Sides List</h3><ul style='margin: 4px;'>";
			first = false;
		}

		htmlStr += "<li>";

		if (changeList.FTMenuItems.hasOwnProperty(item))
		{
			var title = $("#qty_" + item).data("item_title");

			if (changeList.FTMenuItems[item] > 0)
			{
				htmlStr += "Added " + changeList.FTMenuItems[item] + " <i>" + title + "</i>";
			}
			else
			{
				htmlStr += "Removed " + changeList.FTMenuItems[item] * -1 + " <i>" + title + "</i>";
			}
		}

		htmlStr += "</li>";

	}

	if (!first)
	{
		htmlStr += "</ul>";
	}

	first = true;
	var hadMiscCostChanges = false;
	for (var item in changeList.miscCosts)
	{
		hadMiscCostChanges = true;
		if (first)
		{
			htmlStr += "<h3>Misc Costs</h3>";
			first = false;
		}

		if (changeList.miscCosts.hasOwnProperty(item))
		{

			if (changeList.miscCosts[item]["newVal"] > changeList.miscCosts[item]["orgVal"])
			{
				if (changeList.miscCosts[item]["orgVal"] == 0)
				{
					htmlStr += "Added " + getHumanReadableName(item) + " of $" + formatAsMoney(changeList.miscCosts[item]["newVal"]) + "<br />";
				}
				else
				{
					htmlStr += getHumanReadableName(item) + " was increased by $" + formatAsMoney(changeList.miscCosts[item]["diff"]) + " to $" + formatAsMoney(changeList.miscCosts[item]["newVal"]) + "<br />";
				}

			}
			else
			{
				if (changeList.miscCosts[item]["newVal"] == 0)
				{
					htmlStr += "Removed " + getHumanReadableName(item) + " of $" + formatAsMoney(changeList.miscCosts[item]["orgVal"]) + "<br />";
				}
				else
				{
					htmlStr += getHumanReadableName(item) + " was decreased by $" + formatAsMoney(changeList.miscCosts[item]["diff"] * -1) + " to $" + formatAsMoney(changeList.miscCosts[item]["newVal"]) + "<br />";
				}
			}

		}
	}

	first = true;
	for (var item in changeList.miscDescs)
	{
		if (!hadMiscCostChanges && first)
		{
			htmlStr += "<h3>Misc Costs</h3>";
			first = false;
		}

		if (changeList.miscDescs.hasOwnProperty(item))
		{
			htmlStr += getHumanReadableName(item) + " updated<br />";
		}
	}

	if (changeList.order_type != "")
	{
		htmlStr += "<h3>Order changed " + changeList.order_type + "</h3>";
	}

	first = true;
	for (var item in changeList.bundleItems)
	{
		if (first)
		{
			if (isDreamTaste)
			{
				htmlStr += "<h3>Meal Prep Workshop Items List</h3><ul style='margin: 4px;'>";

			}
			else if (isFundraiser)
			{
				htmlStr += "<h3>Fundraiser Event Items List</h3><ul style='margin: 4px;'>";
			}
			else
			{
				htmlStr += "<h3>Intro Items List</h3><ul style='margin: 4px;'>";
			}
			first = false;
		}

		htmlStr += "<li>";

		if (changeList.bundleItems.hasOwnProperty(item))
		{

			var title = $("#bnd_" + item).data("item_title");
			var pricing_type = "(" + $("#bnd_" + item).data("servings") + " Serving)";

			if (changeList.bundleItems[item] > 0)
			{
				htmlStr += "Added " + changeList.bundleItems[item] + " " + pricing_type + " <i>" + title + "</i>";
			}
			else
			{
				htmlStr += "Removed " + changeList.bundleItems[item] * -1 + " " + pricing_type + " <i>" + title + "</i>";
			}

		}

		htmlStr += "</li>";

	}

	if (!first)
	{
		htmlStr += "</ul>";
	}

	first = true;
	for (var item in changeList.discounts)
	{
		if (first)
		{
			htmlStr += "<h3>Discounts</h3>";
			first = false;
		}

		if (changeList.discounts.hasOwnProperty(item))
		{
			if (changeList.discounts[item]["newVal"] > changeList.discounts[item]["orgVal"])
			{
				if (changeList.discounts[item]["orgVal"] == 0)
				{
					htmlStr += "Added " + getHumanReadableName(item) + " of $" + formatAsMoney(changeList.discounts[item]["newVal"]) + "<br />";
				}
				else
				{
					htmlStr += getHumanReadableName(item) + " was increased by $" + formatAsMoney(changeList.discounts[item]["diff"]) + " to $" + formatAsMoney(changeList.discounts[item]["newVal"]) + "<br />";
				}

			}
			else
			{
				if (changeList.discounts[item]["newVal"] == 0)
				{
					htmlStr += "Removed " + getHumanReadableName(item) + " of $" + formatAsMoney(changeList.discounts[item]["orgVal"]) + "<br />";
				}
				else
				{
					htmlStr += getHumanReadableName(item) + " was decreased by $" + formatAsMoney(changeList.discounts[item]["diff"] * -1) + " to $" + formatAsMoney(changeList.discounts[item]["newVal"]) + "<br />";
				}
			}

		}
	}

	first = true;
	for (var item in changeList.reporting)
	{
		if (first)
		{
			htmlStr += "<h3>Reporting</h3>";
			first = false;
		}

		if (changeList.reporting.hasOwnProperty(item))
		{
			if (item == 'fundraiser')
			{
				if (changeList.reporting[item]["newVal"] == 0)
				{
					htmlStr += "Removed " + getHumanReadableName(item) + " " + changeList.reporting[item]["title"] + "<br />";
				}
				else if (changeList.reporting[item]["orgVal"] > 0 && changeList.reporting[item]["orgVal"] != changeList.reporting[item]["newVal"])
				{
					htmlStr += "Changed " + getHumanReadableName(item) + " to " + changeList.reporting[item]["title"] + "<br />";
				}
				else
				{
					htmlStr += "Added " + getHumanReadableName(item) + " " + changeList.reporting[item]["title"] + "<br />";
				}
			}
			else if (item == 'fundraiser_value')
			{
				if (changeList.reporting[item]["diff"] > 0)
				{
					htmlStr += getHumanReadableName(item) + " was increased by $" + formatAsMoney(changeList.reporting[item]["diff"]) + " to $" + formatAsMoney(changeList.reporting[item]["newVal"]) + "<br />";
				}
				else
				{
					htmlStr += getHumanReadableName(item) + " was decreased by $" + formatAsMoney(changeList.reporting[item]["diff"] * -1) + " to $" + formatAsMoney(changeList.reporting[item]["newVal"]) + "<br />";
				}
			}
		}
	}

	if (changeList.coupon)
	{
		if (first)
		{
			htmlStr += "<h3>Discounts</h3>";
			first = false;
		}

		if (changeList.coupon.change_type = 'added')
		{
			htmlStr += "Added coupon code: " + "<br />";
		}
		else if (changeList.coupon.change_type = 'removed')
		{
			htmlStr += "Removed coupon code: " + "<br />";
		}
		else if (changeList.coupon.change_type = 'added')
		{
			htmlStr += "Change coupon code to: " + "<br />";
		}
	}

	if (paymentChanges || $('#use_store_credit').is(':checked'))
	{
		htmlStr += "<h3>Payments</h3>";

		var payment1Type = $('#payment1_type').val();

		if (payment1Type != '')
		{
		htmlStr += "Payment of type " + payment1Type + " selected.<br />";
		}
		else
		{
			$('#creditDiv').is(":visible");
			{
				if ($("#credit_to_customer_refund_cash_amount").val() > 0)
				{
					htmlStr += "Refunding Cash to customer.<br />";
				}

				var ccRefundTotal = 0;
				$("[id^='Cr_RT_'").each(function() {
					ccRefundTotal += parseFloat($(this).val());
				});

				if (ccRefundTotal > 0)
				{
					htmlStr += "Refunding to customer credit card(s).<br />";
				}
			}
		}


		if ((payment1Type == 'GIFT_CERT' || payment1Type == 'GIFT_CARD') && $('#payment2_type').val() != "")
		{
			var payment2Type = $('#payment2_type').val();
			if (payment2Type && payment2Type != "")
			{
				htmlStr += "Payment of type " + payment2Type + " selected.<br />";
			}
		}

		if ($('#use_store_credit').is(':checked'))
		{
			htmlStr += "Store Credit selected for payment.<br />";
		}
	}
	return htmlStr;
}

function getHumanReadableName(id)
{
	switch (id)
	{
		case 'direct_order_discount':
			return "Direct Order Discount";
		case 'plate_points_discount':
			return "Dinner Dollars";
		case "misc_food_subtotal":
			return "Miscellaneous Food Cost";
		case "misc_nonfood_subtotal":
			return "Miscellaneous Non-food Cost";
		case "subtotal_service_fee":
			return "Service Fee";
		case 'misc_food_subtotal_desc':
			return 'Misc Food Description';
		case 'misc_nonfood_subtotal_desc':
			return 'Misc Non-food Description';
		case 'service_fee_description':
			return 'Service Fee Description';
		case 'session_discount':
			return 'Session Discount';
		default:
			return 'error';
	}
}

function getAbbreviatedSummaryString()
{
	var htmlStr = "";

	var bundleChecked = $('#selectedBundle').is(':checked');

	if (isDreamTaste)
	{
		htmlStr += "<h3>Meal Prep Workshop Order</h3>";
	}
	else if (bundleChecked)
	{
		htmlStr += "<h3>Starter Pack Order</h3>";
	}
	else
	{
		htmlStr += "<h3>Standard Order</h3>";
	}

	var stdItemCount = 0;
	var FTItemCount = 0;
	// Items
	$("[id^='qty_']").each(function ()
	{
		if ($(this).val() > 0)
		{
			if ($(this).data('dd_type') == 'cts')
			{
				FTItemCount += ($(this).val() * 1);
			}
			else
			{
				stdItemCount += ($(this).val() * 1);
			}
		}
	});

	if (stdItemCount > 0)
	{
		htmlStr += stdItemCount + " Standard Items Selected.<br />"
	}
	if (FTItemCount > 0)
	{
		htmlStr += FTItemCount + " Sides &amp; Sweets Items Selected.<br />"
	}

	$("[data-dd_type='item_field']").each(function ()
	{
		var curVal = $(this).val();

		if ($(this).data("number") == true)
		{
			// reduce to similar formatting for numeric vs textual equivalency
			curVal = parseFloat(curVal);
			if (isNaN(curVal))
			{
				curVal = 0;
			}

			if (curVal > 0)
			{
				htmlStr += getHumanReadableName(this.id) + " costs of $" + formatAsMoney(curVal);

				var descIDStr = "";

				switch (this.id)
				{
					case "misc_food_subtotal":
						descIDStr = "misc_food_subtotal_desc";
						break;
					case "misc_nonfood_subtotal":
						descIDStr = "misc_nonfood_subtotal_desc";
						break;
					case "subtotal_service_fee":
						descIDStr = "service_fee_description";
						break;
				}

				var desc = $("#" + descIDStr).val();

				if (desc)
				{
					htmlStr += ". <span>( " + desc + " )</span><br />";
				}
				else
				{
					htmlStr += ".<br />";
				}
			}
		}
	});

	var bundleChecked = $('#selectedBundle').is(':checked');
	var bundleOrgVal = $('#selectedBundle').data('org_value');

	var bundleItems = 0;
	$("[id^='bnd_']").each(function ()
	{

		if ($(this).is(":checked"))
		{
			bundleItems++;
		}
	});

	if (bundleItems > 0)
	{
		htmlStr += bundleItems + " Intro Items Selected.<br />"
	}

	if (isDreamTaste)
	{
		var bundleItems = 0;
		$("[id^='bnd_']").each(function ()
		{

			if ($(this).val() > 0)
			{
				bundleItems += ($(this).val() * 1);
			}
		});

		if (bundleItems > 0)
		{
			htmlStr += bundleItems + " Items Selected.<br />"
		}
	}

	// Discounts

	var discountsHTML = "";

	$("#plate_points_discount, #direct_order_discount").each(function ()
	{

		var curVal = $(this).val();

		if ($(this).data("number") == true)
		{
			// reduce to similar formatting for numeric vs textual equivalency
			curVal = parseFloat(curVal);
			if (isNaN(curVal))
			{
				curVal = 0;
			}
		}

		if (curVal.valueOf() > 0)
		{
			discountsHTML += getHumanReadableName(this.id) + " applied.<br />";
		}
	});

	if ($("#SessDiscnoSD").length)
	{

		var newSDValue = $("input[name='SessDisc']:checked", '#editorForm').val();

		if (newSDValue && newSDValue != "noSD")
		{
			discountsHTML += "Session discount is applied.<br />";
		}

	}

	if ($("#PUDnoUP").length)
	{
		var newPUDValue = $("input[name='PUD']:checked", '#editorForm').val();

		if (newPUDValue && newPUDValue != "noUP")
		{
			discountsHTML += "Preferred User Discount is applied.<br />";
		}
	}

	if (discountsHTML != "")
	{
		htmlStr += "<h3>Discounts</h3>" + discountsHTML;
	}

	if (paymentChanges || $('#use_store_credit').is(':checked'))
	{
		htmlStr += "<h3>Payments</h3>";

		var payment1Type = $('#payment1_type').val();
		htmlStr += "Payment of type " + payment1Type + " selected.<br />";

		if ((payment1Type == 'GIFT_CERT' || payment1Type == 'GIFT_CARD') && $('#payment1_type').val() != "")
		{
			var payment2Type = $('#payment2_type').val();
			if (payment2Type && payment2Type != "")
			{
				htmlStr += "Payment of type " + payment2Type + " selected.<br />";
			}
		}

		if ($('#use_store_credit').is(':checked'))
		{
			htmlStr += "Store Credit selected for payment.<br />";
		}

	}

	return htmlStr;
}

function updateChangeList()
{

	itemChanges = false;
	discountChanges = false;

	discountOrPaymentChanges = false;
	var showSaveButton = false;

	changeList = {};
	changeList.stdMenuItems = {};
	changeList.FTMenuItems = {};
	changeList.miscCosts = {};
	changeList.miscDescs = {};
	changeList.bundleItems = {};
	changeList.order_type = "";
	changeList.discounts = {};

	// Items
	$("[id^='qty_']").each(function ()
	{
		if ($(this).val() != $(this).data('org_value'))
		{
			if ($(this).data('dd_type') == 'cts')
			{
				changeList.FTMenuItems[this.id.split("_")[1]] = $(this).val() - $(this).data('org_value');
			}
			else
			{
				changeList.stdMenuItems[this.id.split("_")[1]] = $(this).val() - $(this).data('org_value');
			}

			$(this).addClass('unsaved_data');
			itemChanges = true;
		}
		else
		{
			$(this).removeClass('unsaved_data');
		}
	});

	// Misc Costs
	$("[data-dd_type='item_field']").each(function ()
	{
		var curVal = $(this).val();
		var orgVal = $(this).data('org_value');

		if ($(this).data("number") == true)
		{
			// reduce to similar formatting for numeric vs textual equivalency
			curVal = parseFloat(curVal);
			orgVal = parseFloat(orgVal);

			if (isNaN(curVal))
			{
				curVal = 0;
			}
			if (isNaN(orgVal))
			{
				orgVal = 0;
			}

		}

		if (curVal.valueOf() != orgVal.valueOf())
		{
			$(this).addClass('unsaved_data');

			if ($(this).data("number") == true)
			{
				// we're not tracking description fields for now
				changeList.miscCosts[this.id] = {
					newVal: $(this).val(),
					orgVal: $(this).data('org_value'),
					diff: $(this).val() - $(this).data('org_value')
				};

			}
			else
			{
				changeList.miscDescs[this.id] = 1;
			}

			itemChanges = true;
		}
		else
		{
			$(this).removeClass('unsaved_data');
		}
	});

	var bundleChecked = $('#selectedBundle').is(':checked');
	var bundleOrgVal = $('#selectedBundle').data('org_value');

	if (bundleChecked)
	{
		if (bundleOrgVal == "0")
		{
			$('#bundle_header_div').addClass('unsaved_data_cb');
			changeList.order_type = "to Intro";
			itemChanges = true;
		}
		else
		{
			$('#bundle_header_div').removeClass('unsaved_data_cb');
		}
	}
	else
	{
		if (bundleOrgVal == "1")
		{
			$('#bundle_header_div').addClass('unsaved_data_cb');
			changeList.order_type = "to Standard";
			itemChanges = true;
		}
		else
		{
			$('#bundle_header_div').removeClass('unsaved_data_cb');
		}
	}

	if (isDreamTaste)
	{

		$("[id^='bnd_']").each(function ()
		{

			if ($(this).val() != $(this).data('org_value'))
			{
				if ($(this).data('dd_type') == 'cts')
				{
					changeList.bundleItems[this.id.split("_")[1]] = $(this).val() - $(this).data('org_value');
				}
				else
				{
					changeList.bundleItems[this.id.split("_")[1]] = $(this).val() - $(this).data('org_value');
				}

				$(this).addClass('unsaved_data');
				itemChanges = true;
			}
			else
			{
				$(this).removeClass('unsaved_data');
			}
		});

	}
	else
	{

		$("[id^='bnd_']").each(function ()
		{

			if ($(this).is(":checked"))
			{
				if ($(this).data('org_value') == "0")
				{
					$(this).parent().addClass('unsaved_data_cb');
					changeList.bundleItems[this.id.split("_")[1]] = 1;
					itemChanges = true;
				}
				else
				{
					$(this).parent().removeClass('unsaved_data_cb');
				}
			}
			else
			{
				if ($(this).data('org_value') == "1")
				{
					$(this).parent().addClass('unsaved_data_cb');
					changeList.bundleItems[this.id.split("_")[1]] = -1;
					itemChanges = true;
				}
				else
				{
					$(this).parent().removeClass('unsaved_data_cb');
				}
			}
		});

	}

	if (itemChanges)
	{
		$("#items_tab_li").addClass("unsaved_data_on_tab");
		showSaveButton = true;
	}
	else
	{
		$("#items_tab_li").removeClass("unsaved_data_on_tab");
	}

	// Discounts

	$("#plate_points_discount, #direct_order_discount").each(function ()
	{

		var curVal = $(this).val();
		var orgVal = $(this).data('org_value');

		if ($(this).data("number") == true)
		{
			// reduce to similar formatting for numeric vs textual equivalency
			curVal = parseFloat(curVal);
			orgVal = parseFloat(orgVal);

			if (isNaN(curVal))
			{
				curVal = 0;
			}
			if (isNaN(orgVal))
			{
				orgVal = 0;
			}

		}

		if (curVal.valueOf() != orgVal.valueOf())
		{

			// we're not  tracking description fields for now

			changeList.discounts[this.id] = {
				newVal: $(this).val(),
				orgVal: $(this).data('org_value'),
				diff: $(this).val() - $(this).data('org_value')
			};

			$(this).addClass('unsaved_data');
			discountOrPaymentChanges = true;
			discountChanges = true;
		}
		else
		{
			$(this).removeClass('unsaved_data');
		}
	});

	if ($("#SessDiscnoSD").length)
	{
		if (initialSessionDiscountSetting != $("input[name='SessDisc']:checked", '#editorForm').val())
		{
			$(".SD_area").addClass('unsaved_data_cb');

			var newSDValue = $("input[name='SessDisc']:checked", '#editorForm').val();

			if (newSDValue == "noSD")
			{
				changeList.discounts['session_discount'] = {
					newVal: 0,
					orgVal: $("#OEH_session_discount_org").html(),
					diff: $("#OEH_session_discount_org").html() * -1
				};
			}
			else
			{
				changeList.discounts['session_discount'] = {
					newVal: $("#OEH_session_discount").html(),
					orgVal: 0,
					diff: $("#OEH_session_discount").html()
				};
			}

			discountOrPaymentChanges = true;
			discountChanges = true;
		}
		else
		{
			$(".SD_area").removeClass('unsaved_data_cb');
		}
	}

	if ($("#PUDnoUP").length)
	{
		if (initialPreferredUserDiscountSetting != $("input[name='PUD']:checked", '#editorForm').val())
		{
			$("#PUD_area").addClass('unsaved_data_cb');

			var newPUDValue = $("input[name='PUD']:checked", '#editorForm').val();

			if (newPUDValue == "noUP")
			{
				changeList.discounts['preferred_user_discount'] = {
					newVal: 0,
					orgVal: $("#OEH_preferred_org").html(),
					diff: $("#OEH_preferred_org").html() * -1
				};
			}
			else
			{
				changeList.discounts['preferred_user_discount'] = {
					newVal: $("#OEH_preferred").html(),
					orgVal: 0,
					diff: $("#OEH_preferred").html()
				};
			}

			discountOrPaymentChanges = true;
			discountChanges = true;
		}
		else
		{
			$("#PUD_area").removeClass('unsaved_data_cb');
		}
	}

	// coupons
	var orgCouponVal = $("#org_coupon_id").val();
	var curCouponVal = $("#coupon_id").val();

	if (orgCouponVal != curCouponVal)
	{
		discountOrPaymentChanges = true;

		if (orgCouponVal == "")
		{
			//added
			changeList.coupon = {
				newVal: curCouponVal,
				orgVal: orgCouponVal,
				change_type: "added"
			};
		}
		else if (curCouponVal == "")
		{
			//removed
			//added
			changeList.coupon = {
				newVal: curCouponVal,
				orgVal: orgCouponVal,
				change_type: "removed"
			};
		}
		else
		{
			//added
			changeList.coupon = {
				newVal: curCouponVal,
				orgVal: orgCouponVal,
				change_type: "changed"
			};
		}

	}

	if (discountOrPaymentChanges)
	{
		$("#payments_tab_li").addClass("unsaved_data_on_tab");
		showSaveButton = true;
	}
	else
	{
		$("#payments_tab_li").removeClass("unsaved_data_on_tab");
	}

	if (orderState == 'SAVED' && (showSaveButton || $('#use_store_credit').is(':checked')))
	{
		$("#saveOrderSpan").show();
	}
	else
	{
		$("#saveOrderSpan").hide();
	}

	if (orderState == 'SAVED' && ($('#use_store_credit').is(':checked') || paymentChanges))
	{
		$("#addPaymentAndActivateButton").attr("disabled", false);
	}
	else
	{
		$("#addPaymentAndActivateButton").attr("disabled", true);

	}

	if (orderState == 'ACTIVE' || allowLimitedAccess)
	{
		$("#finalizeOrderSpan").show();
		if (showSaveButton || paymentChanges || $('#use_store_credits').is(':checked'))
		{
			discountOrPaymentChanges = true;
			$("#submit_button").attr("disabled", false);
			$("#finalize_msg").show();
		}
		else
		{
			$("#submit_button").attr("disabled", true);
			$("#finalize_msg").hide();
		}
	}
	else
	{
		$("#finalizeOrderSpan").hide();
	}

	if (itemChanges || discountOrPaymentChanges)
	{
		$("#changeListTab").addClass('unsaved_data_list');
	}
	else
	{
		$("#changeListTab").removeClass('unsaved_data_list');
	}

	drawChangeList();
}

function handleAutoAdjust(obj)
{
	// called when the document is loaded or when the "autoAdjust" check box changes

	// if checked then setup the amounts to be credited/debited
	if (obj.checked)
	{
		var balance = Number($('#OEH_remaing_balance').html());

		if (canAdjustDelayedPayment && (currentDPAmount > 0 || balance > 0))
		{
			if (balance > 0)
			{
				$('#payment1_type').val('DPADJUST');
				changePayment1('DPADJUST');
			}
			else
			{
				adjustPendingDelayedPayment(balance);
			}
		}
		else
		{

			if (balance > 0)
			{
				$('#payment1_type').val('REFERENCE');
				changePayment1('REFERENCE');
			}

			assignBalanceToRefTransactions(balance);
		}
	}
	else
	{

		if ($('#payment1_type').val() != "")
		{
			$('#payment1_type').val('');
			changePayment1('');
		}

		// also clear any credits
		for (ident in existingPaymentAmountArray)
		{
			$('#Cr_RT_' + ident).val("");
		}

		// or if trying to credit

	}

	reportPaymentStatus(false);
}

function getAbbreviatedChangeString()
{
	var htmlStr = "";
	var addedStd = 0;
	var removedStd = 0;

	if (needPlatePointsMaxDiscountChangeWarning)
	{
		htmlStr += "<div style='color:red;'>Warning: The amount discountable by Dinner Dollars has changed. You may wish to review and adjust the Dinner Dollars discount.</div>";

	}

	for (var item in changeList.stdMenuItems)
	{
		if (changeList.stdMenuItems.hasOwnProperty(item))
		{

			if (changeList.stdMenuItems[item] > 0)
			{
				addedStd++;
			}
			else
			{
				removedStd++;
			}
		}
	}

	if (addedStd)
	{
		htmlStr += addedStd + " Standard items were added.<br />"
	}
	if (removedStd)
	{
		htmlStr += removedStd + " Standard items were removed.<br />"
	}

	var addedFT = 0;
	var removedFT = 0;

	for (var item in changeList.FTMenuItems)
	{
		if (changeList.FTMenuItems.hasOwnProperty(item))
		{
			if (changeList.FTMenuItems[item] > 0)
			{
				addedFT++;
			}
			else
			{
				removedFT++;
			}
		}
	}

	if (addedFT)
	{
		htmlStr += addedFT + " Sides &amp; Sweets items were added.<br />"
	}
	if (removedFT)
	{
		htmlStr += removedFT + " Sides &amp; Sweets items were removed.<br />"
	}

	for (var item in changeList.miscCosts)
	{

		if (changeList.miscCosts.hasOwnProperty(item))
		{

			if (changeList.miscCosts[item]["newVal"] > changeList.miscCosts[item]["orgVal"])
			{
				if (changeList.miscCosts[item]["orgVal"] == 0)
				{
					htmlStr += "Added " + getHumanReadableName(item) + " of $" + formatAsMoney(changeList.miscCosts[item]["newVal"]) + "<br />";
				}
				else
				{
					htmlStr += getHumanReadableName(item) + " was increased by $" + formatAsMoney(changeList.miscCosts[item]["diff"]) + " to $" + formatAsMoney(changeList.miscCosts[item]["newVal"]) + "<br />";
				}

			}
			else
			{
				if (changeList.miscCosts[item]["newVal"] == 0)
				{
					htmlStr += "Removed " + getHumanReadableName(item) + " of $" + formatAsMoney(changeList.miscCosts[item]["orgVal"]) + "<br />";
				}
				else
				{
					htmlStr += getHumanReadableName(item) + " was decreased by $" + formatAsMoney(changeList.miscCosts[item]["diff"] * -1) + " to $" + formatAsMoney(changeList.miscCosts[item]["newVal"]) + "<br />";
				}
			}

		}
	}

	for (var item in changeList.miscDescs)
	{
		if (changeList.miscDescs.hasOwnProperty(item))
		{
			htmlStr += getHumanReadableName(item) + " updated<br />";
		}
	}

	if (changeList.order_type != "")
	{
		htmlStr += "<h3>Order changed " + changeList.order_type + "</h3>";
	}

	var addedBI = 0;
	var removedBI = 0;

	first = true;
	for (var item in changeList.bundleItems)
	{
		if (first)
		{
			if (isDreamTaste)
			{
				htmlStr += "<h3>Meal Prep Workshop Items List</h3>";
			}
			else
			{
				htmlStr += "<h3>Intro Items List</h3>";
			}

			first = false;
		}

		if (changeList.bundleItems.hasOwnProperty(item))
		{
			if (changeList.bundleItems[item] > 0)
			{
				addedBI++;
			}
			else
			{
				removedBI++;
			}
		}
	}
	if (addedBI)
	{
		htmlStr += addedBI + " items were added.<br />"
	}
	if (removedBI)
	{
		htmlStr += removedBI + " items were removed.<br />"
	}

	if (htmlStr != "")
	{
		htmlStr = "<h3>Purchase Changes</h3>" + htmlStr;
	}

	first = true;
	for (var item in changeList.discounts)
	{
		if (first)
		{
			htmlStr += "<h3>Discounts</h3>";
			first = false;
		}

		if (changeList.discounts.hasOwnProperty(item))
		{
			if (changeList.discounts[item]["newVal"] > changeList.discounts[item]["orgVal"])
			{
				if (changeList.discounts[item]["orgVal"] == 0)
				{
					htmlStr += "Added " + getHumanReadableName(item) + " of $" + formatAsMoney(changeList.discounts[item]["newVal"]) + "<br />";
				}
				else
				{
					htmlStr += getHumanReadableName(item) + " was increased by $" + formatAsMoney(changeList.discounts[item]["diff"]) + " to $" + formatAsMoney(changeList.discounts[item]["newVal"]) + "<br />";
				}

			}
			else
			{
				if (changeList.discounts[item]["newVal"] == 0)
				{
					htmlStr += "Removed " + getHumanReadableName(item) + " of $" + formatAsMoney(changeList.discounts[item]["orgVal"]) + "<br />";
				}
				else
				{
					htmlStr += getHumanReadableName(item) + " was decreased by $" + formatAsMoney(changeList.discounts[item]["diff"] * -1) + " to $" + formatAsMoney(changeList.discounts[item]["newVal"]) + "<br />";
				}
			}

		}
	}

	if ($('#autoAdjust').is(":checked"))
	{
		htmlStr += "<span style='color:red'>Auto-Adjust is checked and will " + $("#adj_action").html() + "</span>";

	}

	htmlStr = "<div id='finalize_changes_div'>" + htmlStr + "</div>";
	return htmlStr;
}

function addPaymentToLockedOrder()
{
	var billing_name = $("#payment1_ccNameOnCard").val();
	var billing_address = $("#billing_address_1").val();
	var billing_zip = $("#billing_postal_code_1").val();
	var amt = $("#payment1_cc_total_amount").val();

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		async: true,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor',
			store_id: STORE_DETAILS.id,
			op: 'get_token',
			order_id: order_id,
			amount: amt,
			user_id: user_id,
			billing_name: billing_name,
			billing_address: billing_address,
			billing_zip: billing_zip,
			dd_csrf_token: $('input[name=dd_csrf_token]', '#editorForm').val(),
			chain_token: true
		},
		success: function (json)
		{
			$('input[name=dd_csrf_token]', '#editorForm').val(json.getTokenToken);

			if (json.processor_success)
			{

				intenseLogging("addPaymentToLockedOrder() get_token successful");

				var token = json.token;

				send(token, 1, false, true, true, false);

			}
			else
			{
				$("#paying_div").remove();

				intenseLogging("addPaymentToLockedOrder get_token: " + json.processor_message);

				dd_message({
					title: 'Error',
					message: json.processor_message
				});

			}
		},
		error: function (objAJAXRequest, strError)
		{
			$("#paying_div").remove();

			intenseLogging("addPaymentToLockedOrder get_token: " + strError + " | " + objAJAXRequest.responseText);

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});

		}

	});

}

function submitPage()
{
	intenseLogging("submitPage() called");

	var form = $("#editorForm")[0];

	var is_valid = confirm_and_check_form(form);

	if (is_valid)
	{
		var message = "This can not be undone. Are you sure you want to submit the changes?";
		var changesBlurb = getAbbreviatedChangeString();
		message += "<br /><br />" + changesBlurb;

		var dialogHeight = 460;

		if (message.length < 128)
		{
			dialogHeight = 160;
		}
		else if (message.length < 256)
		{
			dialogHeight = 260;
		}

		dd_message({
			title: 'Finalize',
			message: message,
			modal: true,
			width: 400,
			height: dialogHeight,
			confirm: function ()
			{

				if (supports_transparent_redirect && $("#payment1_type").val() == 'CC')
				{
					intenseLogging("submitPage() confirmed");

					if (!allowLimitedAccess)
					{
						updateActiveOrder(true, true);
					}
					else
					{
						addPaymentToLockedOrder(true, true);
					}
				}
				else
				{
					$('<input>').attr({
						type: 'hidden',
						name: 'changeList',
						value: JSON.stringify(changeList)
					}).appendTo($(form));

					$('<input>').attr({
						type: 'hidden',
						name: 'changeListStr',
						value: getChangeListHTML()
					}).appendTo($(form));

					$("#submit_changes").val("true");
					$("#editorForm").submit();
				}
			}
		});

	}
}

function resetPage()
{
	intenseLogging("resetPage() called");

	bounce(window.location.href);
}

function confirm_and_check_form(form)
{

	intenseLogging("confirm_and_check_form() called");

	if (orderState == 'NEW' || orderState == 'SAVED')
	{
		return false;
	}

	if (hasBundle)
	{
		var bundleIsSelected = false;
		if ($('#selectedBundle').is(':checked'))
		{
			bundleIsSelected = true;
		}

		if (bundleIsSelected)
		{

			var numBundItems = countSelectedBundleItems();
			if (numBundItems < 18)
			{

				dd_message({
					title: 'Error',
					message: 'Please select at least 18-servings of Meal Prep Starter Pack items.'
				});

				return false;
			}
			else if (numBundItems > 18)
			{
				dd_message({
					title: 'Error',
					message: 'Please select no more than 18 servings of Meal Prep Starter Pack items.'
				});

				return false;
			}
		}
	}

	if (stationNeedsAttention)
	{
		dd_message({
			title: 'Error',
			message: 'Please review the Side Station and ensure the correct number of items are selected.'
		});
		return false;
	}

	return _check_form(form);
}

// rounds to the nearest penny
// move to global once this is found to be reliable
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

/*

 function reportTestFailure(testNum, actual, correct)
 {
 if (actual != correct)
 {
 alert("Test #: " + testNum + " actual: " + actual + " correct: " + correct);
 }
 }

 function doTests()
 {

 var testmultiplier = ".5";

 var testNum = "260.09";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(1, newResult, 130.05);

 var testNum = "209.10";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(2, newResult, 104.55);

 testNum = "209.09";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(3, newResult, 104.55);

 testNum = "209";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(4, newResult, 104.5);

 testNum = "1";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(5, newResult, .5);

 testNum = "0";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(6, newResult, 0);

 testNum = ".05";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(7, newResult, .03);

 testNum = ".005";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(8, newResult, 0);

 testNum = "499.99";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(9, newResult, 250);

 var testNum = "209.10";
 var testmultiplier = ".12";

 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(10, newResult, 25.09);

 testNum = "209.09";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(11, newResult, 25.09);

 testNum = "209";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(12, newResult, 25.08);

 testNum = "1";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(13, newResult, .12);

 testNum = "0";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(14, newResult, 0);

 testNum = ".05";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(15, newResult, .01);

 testNum = ".005";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(16, newResult, 0);

 testNum = "499.99";
 var testResult = testNum * testmultiplier;
 var newResult = dd_round_amount(testResult);
 console.log('testResult: ' + testResult + '   New Result: ' + newResult);
 reportTestFailure(17, newResult, 60);

 }

 doTests();

 */

function updateSideStationStatus(entree_id, qty)
{
	if (qty == 0)
	{
		stationNeedsAttention = false;
		help_message = "You must set the Bundle quantity to greater than zero.";
		var stationHelpName = "sidestation_help_" + entree_id;
		var stationHelpElem = $("#" + stationHelpName);
		stationHelpElem.css('color', "#bb0000");
		stationHelpElem.html(help_message);

		return;
	}

	var requiredItemCount = qty * sideStationBundleInfo[entree_id].number_items_required;
	var numRequiredSpanName = "required_items_station_" + entree_id;
	$("#" + numRequiredSpanName).html(requiredItemCount);

	var numSelected = 0;

	$.each(sideStationBundleInfo[entree_id].bundle, function (sid, sinfo)
	{

		var itemQty = $('#sbi_' + sid);

		if (itemQty && itemQty.val() != "")
		{
			if (isNaN(itemQty.val()) == true || itemQty.val() - parseInt(itemQty.val()) != 0)
			{
				itemQty.val(0);
			}

			if (itemQty.val() > 0)
			{
				itemQty.val(parseInt(itemQty.val()));
				numSelected += (itemQty.val() * 1);
			}

			var inv_qty = entreeIDToInventoryMap[sid].remaining;
			inv_qty -= (itemQty.val() * sinfo.servings_per_item);

			$('#sbi_inv_' + sid).html(inv_qty);

			$('#inv_' + sid).html(inv_qty);

			entreeIDToInventoryMap[sid].remaining = inv_qty;

		}

	});

	var numSelectedSpanName = "selected_items_station_" + entree_id;
	$("#" + numSelectedSpanName).html(numSelected);

	var stationHelpName = "sidestation_help_" + entree_id;
	var stationHelpElem = $("#" + stationHelpName);
	var help_message = "";
	if (numSelected == 0)
	{
		help_message = "Please select " + requiredItemCount + " items.";
		stationHelpElem.css('color', "#bb0000");
		stationNeedsAttention = true;
	}
	else if (numSelected > requiredItemCount)
	{
		help_message = "You have selected " + (numSelected - requiredItemCount) + " items more than the required amount. Please remove " + (numSelected - requiredItemCount) + " items.";
		stationHelpElem.css('color', "#bb0000");
		stationNeedsAttention = true;
	}
	else if (numSelected < requiredItemCount)
	{
		help_message = "You have selected " + (requiredItemCount - numSelected) + " items less than the required amount. Please add " + (requiredItemCount - numSelected ) + " items.";
		stationHelpElem.css('color', "#bb0000");
		stationNeedsAttention = true;
	}
	else
	{
		help_message = "You have selected the correct number of sidestation items. You may continue with item selection or checkout.";
		stationHelpElem.css('color', "#00bb00");
		stationNeedsAttention = false;
	}

	stationHelpElem.html(help_message);

}

// this massive function is called anytime something that affects costs or payments is altered
function calculateTotal()
{

	var total = 0;
	var entrees = 0;
	var servings = 0;
	var halfQty = 0;
	var wholeQty = 0;
	var introQty = 0;
	var sideDishQty = 0;
	var sideDishSubTotal = 0;
	var bundlesSubTotal = 0;
	var productsSubTotal = 0;
	var bundlesQty = 0;
	var discounted_total = 0;
	var creditContribution = 0;
	var DRSubjectCreditContribution = 0;
	var main_items_count = 0;
	var aux_items_count = 0;
	var bundleIsSelected = false;
	var PUDExcludedItemsTotal = 0;
	var PUDExcludedFullItemsCount = 0;
	var PUDExcludedHalfItemsCount = 0;
	var creditConsumed = 0;

	var sortedCoreItemList = [];
	var itemsSelected = [];

	if (hasBundle)
	{
		if ($('#selectedBundle').is(':checked'))
		{
			bundleIsSelected = true;
		}
	}


	for (var x in entreeIDToInventoryMap)
	{
		entreeIDToInventoryMap[x].remaining = entreeIDToInventoryMap[x].org_remaining;
		$('#inv_' + x).html(entreeIDToInventoryMap[x].org_remaining);

	}

	// ---------------------------------------------------------- Items
	// loop through all the quantity input boxes and gather up the totals


	// var count = 0;
	$("[id^='qty_']").each(function ()
	{
		//  count++;

		if ($(this).val() != "")
		{
			if (isNaN($(this).val()) == true || $(this).val() - parseInt($(this).val(), 10) != 0)
			{
				$(this).val(0);
			}

			var itemQty = $(this).val();
			var itemId = this.id.split("_")[1];

			if ( isFactoringCredit )
			{
				var orgAmount = $(this).data('org_value');
				if ($(this).data('dd_type') == 'std')
				{
					if (orgAmount < itemQty)
					{
						diff = itemQty - orgAmount;
						creditContribution += (diff * $(this).data('price'));
					}
					else if (itemQty  < orgAmount)
					{
						diff = orgAmount - itemQty ;
						creditContribution -= (diff * $(this).data('price'));
					}
				}
				else // addon
				{
					var orgAmount = itemQty.getAttribute('data-org_value');
					if (orgAmount < itemQty )
					{
						diff = itemQty  - orgAmount;
						creditConsumed += (diff * $(this).data('price'));
					}
					else if (itemQty < orgAmount)
					{
						diff = orgAmount - itemQty;
						creditContribution -= (diff * $(this).data('price'));
					}
				}
			}

			if ( (isNaN(itemQty) == false) )
			{
				var entree_id = $(this).data('entreeid');
				var inv_qty = entreeIDToInventoryMap[entree_id].remaining;
				var temp_servings = Number($(this).data('servings'));
				inv_qty = inv_qty - (itemQty * temp_servings);
				$('#inv_' + entree_id).html(inv_qty);
				entreeIDToInventoryMap[entree_id].remaining = inv_qty;

				if ($(this).data('dd_type') != 'cts')
				{

					if (itemQty > 0)
					{
						var obj = {
							id: itemId,
							qty: itemQty,
							price: $(this).data('price'),
							serving_size: $(this).data('servings')
						};

						sortedCoreItemList.push(obj)

					}
				}


				total += itemQty * $(this).data('price');

				if ($(this).data('dd_type') != 'cts')
				{
					entrees += itemQty * 1;
					servings += itemQty * $(this).data('servings');

					if ($(this).data('pricing_type') == 'FULL')
					{
						wholeQty += itemQty * 1;
					}
					else
					{
						halfQty += itemQty * 1;

					}

				}
				else
				{
					sideDishQty += itemQty * 1;
					sideDishSubTotal += itemQty * $(this).data('price');
				}

				if ($(this).data('is_bundle') == 'true')
				{
					bundlesSubTotal += itemQty * $(this).data('price');
					bundlesQty += (itemQty * 1);
				}

				itemsSelected[itemId] = itemQty;

				$('#sbi_inv_' + entree_id).html(inv_qty);
			}

			if ($(this).data('is_bundle') == 'true')
			{
				updateSideStationStatus(entree_id, itemQty);
			}
		}
	});

	main_items_count = halfQty + wholeQty  + sideDishQty;

	if (hasBundle)
	{
		if (originallyHadBundle)
		{
			// bundle support
			var selectedBundleItemCount = 0;
			var selectedBundleServingsCount = 0;

			if (bundleIsSelected)
			{

				$("[id^='bnd_']").each(function ()
				{
					if ($(this).data('org_value') == "1") //was initially chosen
					{
						if (!$(this).is(":checked"))
						{
							var inv_qty = entreeIDToInventoryMap[$(this).data('entreeid')].remaining;
							inv_qty = inv_qty + $(this).data('servings');
							$('#inv_' + $(this).data('entreeid')).html(inv_qty);
							entreeIDToInventoryMap[$(this).data('entreeid')].remaining = inv_qty;
						}
						else
						{
							selectedBundleItemCount++;
							selectedBundleServingsCount += $(this).data('servings');
						}
					}
					else
					{
						if ($(this).is(":checked"))
						{
							var inv_qty = entreeIDToInventoryMap[$(this).data('entreeid')].remaining;
							inv_qty = inv_qty - $(this).data('servings');
							$('#inv_ ' + $(this).data('entreeid')).html(inv_qty);
							entreeIDToInventoryMap[$(this).data('entreeid')].remaining = inv_qty;
							selectedBundleItemCount++;
							selectedBundleServingsCount += $(this).data('servings');
						}
					}

				});

			}
			else
			{ //bundle is not selected

				$("[id^='bnd_']").each(function ()
				{
					if ($(this).data('org_value') == "1") //was initially chosen
					{
						var inv_qty = entreeIDToInventoryMap[$(this).data('entreeid')].remaining;
						inv_qty = inv_qty + $(this).data('servings');
						$('#inv_' + $(this).data('entreeid')).html(inv_qty);
						entreeIDToInventoryMap[$(this).data('entreeid')].remaining = inv_qty;

					}
				});
			}

		}
		else
		{
			var selectedBundleItemCount = 0;
			var selectedBundleServingsCount = 0;
			if (bundleIsSelected)
			{

				$("[id^='bnd_']").each(function ()
				{
					if ($(this).is(":checked"))
					{
						var inv_qty = entreeIDToInventoryMap[$(this).data('entreeid')].remaining;
						inv_qty = inv_qty - $(this).data('servings');
						$('#inv_' + $(this).data('entreeid')).html(inv_qty);
						entreeIDToInventoryMap[$(this).data('entreeid')].remaining = inv_qty;
						selectedBundleItemCount++;
						selectedBundleServingsCount += $(this).data('servings');
					}

				});

			}

		}

	}

	if (hasBundle)
	{
		// bundle support
		if (bundleIsSelected)
		{
			total += (bundleInfo.price * 1);
			wholeQty += selectedBundleItemCount;
			servings += selectedBundleServingsCount;
		}
	}

	if (tasteBundleMenuData)
	{
		if (hasDreamTasteBundle)
		{
			// bundle support
			var selectedWholeBundleItemCount = 0;
			var selectedHalfBundleItemCount = 0;
			var selectedBundleServingsCount = 0;

			total += dreamTastePrice;

			$("[id^='bnd_']").each(function ()
			{

				var tasteItemQty = $(this).val();
				if (tasteItemQty > 0)
				{
					servings += ($(this).data('servings') * tasteItemQty);
					if ($(this).data('servings') == 3)
					{
						selectedHalfBundleItemCount += (tasteItemQty * 1);
					}
					else
					{
						selectedWholeBundleItemCount += (tasteItemQty * 1);
					}

					var inv_qty = entreeIDToInventoryMap[$(this).data('entreeid')].remaining;
					inv_qty = inv_qty - (tasteItemQty * 3);
					$('#inv_' + $(this).data('entreeid')).html(inv_qty);
					entreeIDToInventoryMap[$(this).data('entreeid')].remaining = inv_qty;
				}
			});

			wholeQty += (selectedWholeBundleItemCount * 1);
			halfQty += (selectedHalfBundleItemCount * 1);

		}
	}

	var discountablePlatePointsAmount = 0;

	if ($("#selectedBundle").is(':checked') )
	{
		$("#max_plate_points_deduction").html(formatAsMoney(0))
	}
	else
	{
		discountablePlatePointsAmount = getPlatePointsDiscountableAmount(sortedCoreItemList, total);

	    needPlatePointsMaxDiscountChangeWarning = false;

	    if (discountablePlatePointsAmount != lastMaxPPDiscountAmount && lastMaxPPDiscountAmount != -1)
	    {
	    	needPlatePointsMaxDiscountChangeWarning = true;
	    }

	    lastMaxPPDiscountAmount = discountablePlatePointsAmount;


		if (couponlimitedToFT)
		{
			var tempCouponVal = Number(document.getElementById('couponValue').value);
			discountablePlatePointsAmount -= tempCouponVal;
			if (discountablePlatePointsAmount < 0)
			{
				discountablePlatePointsAmount = 0;
			}

		}

		$("#max_plate_points_deduction").html(formatAsMoney(discountablePlatePointsAmount))
	}

	if (discountablePlatePointsAmount <= 0)
	{
		$("#plate_points_discount").attr("disabled", "disabled");
		$("#plate_points_discount").css({
			"background-color": "#c0c0c0",
			"color": "#060606"
		});
		$("#pp_discountable_cost_msg").show();

	}
	else if (!allowLimitedAccess)
	{
		$("#plate_points_discount").removeAttr("disabled");
		$("#plate_points_discount").css({
			"background-color": "#ffffff",
			"color": "#000000"
		});
		$("#pp_discountable_cost_msg").hide();

	}


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

	discounted_total = total;

	var addonsSubtotal = 0;
	var addonsQty = 0;


	//var itemRows = document.getElementById('itemsTbl').rows;
	//for (i = 0; i < itemRows.length; i++)

	$("#itemsTbl tr").each(function ()
	{

		if (this.id.indexOf('row_') != -1)
		{
			var itemNumber = this.id.substr(4);

			var qty_str = 'qna_' + itemNumber;
			var prc_str = 'prc_' + itemNumber;

			var addQty = $('#' + qty_str).val();
			var addonsQtyNumber = Number(addQty);
			var addPrc = $("#" + prc_str).html();
			var addPrcNumber = Number(addPrc);
			addonsSubtotal += (Number(addPrc) * addQty);
			addonsQty += addonsQtyNumber;

			/// var inv_qty =  Number(document.getElementById('inv_org_' + itemNumber).innerHTML);
			var inv_qty = entreeIDToInventoryMap[itemNumber].remaining;

			inv_qty = inv_qty - addQty;
			$('#inv_' + itemNumber).html(inv_qty.toString());
			entreeIDToInventoryMap[itemNumber].remaining = inv_qty;

			if ( isFactoringCredit )
			{
				orgAmount = document.getElementById(qty_str).getAttribute('data-org_value');
				if (orgAmount < addonsQtyNumber)
				{
					diff = addonsQtyNumber - orgAmount;
					creditConsumed += (diff * addPrcNumber);
				}
				else if (addQty < orgAmount)
				{
					diff = orgAmount - addQty;
					creditContribution -= (diff * addPrcNumber);
				}
			}
		}
	});

	var totalMealQuantity = Number(halfQty + wholeQty);

	var itemCountLabel = $('#OEH_item_count_label');

	var itemCountAdjustmentAmount = 0;
	var displayServingsAdjustment = 0;

	// coupon code free meal count contribution
	var couponTypeElem = $('#coupon_type');
	if (couponTypeElem.length && couponTypeElem.val() == 'FREE_MEAL')
	{
		cfm_size = Number($('#free_entree_servings').val());

		if (cfm_size > 0)
		{
			displayServingsAdjustment += cfm_size;
			itemCountAdjustmentAmount++;
		}
	}

	if (itemCountAdjustmentAmount == 0)
	{
		displayMealCount = introQty + wholeQty + halfQty + sideDishQty + addonsQty;
		displayServingCount = servings;
		itemCountLabel.html("Total Item Count");
	}
	else
	{
		displayMealCount = introQty + wholeQty +  sideDishQty + halfQty + itemCountAdjustmentAmount + addonsQty;
		displayServingCount = servings + displayServingsAdjustment;
		itemCountLabel.html("Total # Items (incl. free meals)");
	}

	if (orderEditSuccess)
	{
		displayMealCount += newAddonQty;
	}

	// update the entree and serving totals display
	$('#OEH_item_count').html(displayMealCount);
	$('#OEH_number_servings').html(displayServingCount);

	var premarkup_total = 0;

	var freeMealValue = 0;

	// also must add coupon code free meal
	// coupon code free meal count contribution
	couponTypeElem = $('#coupon_type');
	if (couponTypeElem.length && couponTypeElem.val() == 'FREE_MEAL')
	{
		freeMealValue = Number($('#couponValue').val());

		if (freeMealValue > 0)
		{
			discounted_total += freeMealValue;
		}
	}

	discounted_total += addonsSubtotal;

	if (orderEditSuccess)
	{
		discounted_total += newAddonAmount;
	}

	discounted_total = Math.round(discounted_total*100)/100;

	// --------------------------------------------------------------------- menu items subtotal
	$('#orderSubTotal').val(formatAsMoney(Math.round(total*100)/100));
	$('#OEH_menu_subtotal').html(formatAsMoney(discounted_total));
	$('#orderTotal').val(formatAsMoney(discounted_total));

	// ------------------------------------------------------ food subtotal

	var miscFoodSubtotal = Number(document.getElementById('misc_food_subtotal').value);

	// menu items total plus misc food cost
	//(Note that discountedTotal is misleading here. This reflects legacy of Family savings discount on menu items which no longer applies)
	var food_costs = (discounted_total * 1) + (miscFoodSubtotal *1);
	var session_discount_basis = food_costs;
	$('#OEH_food_cost_subtotal').html(formatAsMoney((discounted_total * 1) + (miscFoodSubtotal *1)));

	var newGrandTotal = discounted_total;
	var pre_discounts_grand_total = newGrandTotal;


	// -------------------------------DISCOUNTS-----------------------------------------


	var curPPDiscount = 0;
	// ----------------------------------------------  PLATEPOINTS Discount
	if ( $('#plate_points_discount').length)
	{
		curPPDiscount = $('#plate_points_discount').val();
		if (curPPDiscount > discountablePlatePointsAmount)
		{
			curPPDiscount = discountablePlatePointsAmount;
			$('#plate_points_discount').val(formatAsMoney(curPPDiscount));
		}


		//$('#OEH_plate_points_order_discount').html(formatAsMoney(curPPDiscount));
		// This is now done in the taxesd section


		newGrandTotal = formatAsMoney(newGrandTotal - curPPDiscount);
	}

	// ---------------------------------------------- Direct Order
	var directDiscountVal = Number(0);

	directDiscount = $('#direct_order_discount');
	if (directDiscount.val() == "")
	{
		directDiscount.val('0.00');
	}
	if ( directDiscount.length )
	{
		directDiscountVal = directDiscount.val();

		if (directDiscountVal > food_costs)
		{
			directDiscountVal = food_costs;
			directDiscount.val(directDiscountVal);
		}

		if (directDiscountVal)
		{
			newGrandTotal = formatAsMoney(newGrandTotal - directDiscountVal);
			$('#OEH_direct_order_discount').html(formatAsMoney(directDiscountVal));
		}
	}


	// Adjust Bonus Credit and discount
	if (isFactoringCredit)
	{
		//alert(creditContribution);
		//alert(creditConsumed);
		creditBasis += 	creditContribution;
		createBonusCredit(creditConsumed);
	}


	// ------------------------------------------------------------------- coupon Discount

	if (couponDiscountMethod == 'PERCENT')
	{
		var newDiscountAmount = pre_discounts_grand_total * (couponDiscountVar / 100);

		$('#couponValue').val(newDiscountAmount);
	}

	if (couponlimitedToFT)
	{
		var newDiscountVal = sideDishSubTotal;
		if (newDiscountVal > couponDiscountVar)
		{
			newDiscountVal = couponDiscountVar;
		}

		$('#couponValue').val(newDiscountVal);

	}

	var couponDiscountVal = Number(0);
	couponDiscount = $('#couponValue');

	if ( couponDiscount.length)
	{
		if (couponDiscount.val() == "")
		{
			couponDiscount.val('0.00');
		}
		couponDiscountVal = couponDiscount.val();

		if (couponDiscountVal)
		{
			newGrandTotal = formatAsMoney(newGrandTotal - couponDiscountVal);
			$('#OEH_coupon_discount').html(formatAsMoney(couponDiscountVal));
			session_discount_basis -= couponDiscountVal;
		}
	}

	// ------------------------------------------------ Preferred Customer
	PUDElem = $('#PUDnoUP');
	if (PUDElem.length)
	{
		var selectedUP = $('input[name=PUD]:checked', '#editorForm').val();

		if (selectedUP == null || selectedUP == "")
		{
			selectedUP = "noUP";
		}

		var UPDiscount = -1;

		premarkup_discounted_total = premarkup_total - (total - discounted_total);
		var UPType = 'none';
		var PcntTypeValue = 0.0;

	if (originalUP != false)
	{
		if (selectedUP == "originalUP")
		{
			if (originalUP.type == "FLAT")
			{
				var cost = (wholeQty - PUDExcludedFullItemsCount) * originalUP.value;
				var halfCost = Number(formatAsMoney(((halfQty - PUDExcludedHalfItemsCount) * (originalUP.value / 2))));
				cost += halfCost;
				UPDiscount = formatAsMoney(discounted_total - cost - sideDishSubTotal - addonsSubtotal - productsSubTotal - PUDExcludedItemsTotal);
				UPType = 'flat';

			}
			else if (originalUP.type == "PERCENT")
			{
				var basis = pre_discounts_grand_total - sideDishSubTotal - addonsSubtotal - productsSubTotal;
				var multiplier = originalUP.value / 100;
				var pud_result = basis * multiplier;
				var rounded_pud_result = dd_round_amount(pud_result);
				UPDiscount = formatAsMoney( rounded_pud_result );
				UPType = 'pcnt';
				PcntTypeValue = originalUP.value / 100;
			}
			}
		}

		if (activeUP != false)
		{
			if (selectedUP == "activeUP")
			{
				if (activeUP.type == "FLAT")
				{
					var cost = (wholeQty - PUDExcludedFullItemsCount) * activeUP.value;
					var halfCost2 = Number(formatAsMoney(((halfQty - PUDExcludedHalfItemsCount) * (originalUP.value / 2))));
					cost += halfCost2;

					UPDiscount = formatAsMoney((premarkup_discounted_total - cost - sideDishSubTotal- addonsSubtotal - productsSubTotal - PUDExcludedItemsTotal) * -1);
					UPType = 'flat';
				}
				else if (activeUP.type == "PERCENT")
				{
					var basis = pre_discounts_grand_total - sideDishSubTotal - addonsSubtotal - productsSubTotal;
					var multiplier = activeUP.value / 100;
					var pud_result = basis * multiplier;
					var rounded_pud_result = dd_round_amount(pud_result);
					UPDiscount = formatAsMoney( rounded_pud_result );
					UPType = 'pcnt';
					PcntTypeValue = activeUP.value / 100;
				}
			}
		}


		if (selectedUP == "noUP")
		{
			UPDiscount = 0;
		}

		if (UPType == 'flat')
		{
			if (typeof promoVal  != 'undefined' && promoVal > 0 && UPDiscount > 0)
			{
				UPDiscount -= promoVal;
			}

			if (freeMealValue > 0 && UPDiscount > 0)
			{
				UPDiscount -= freeMealValue;
			}
		}
		else if (UPType == 'pcnt')
		{
			if (typeof promoVal  != 'undefined' && promoVal > 0 && UPDiscount > 0)
			{
				UPDiscount -= (promoVal * PcntTypeValue);
				UPDiscount = Math.round(UPDiscount * 100) / 100;
			}

			if (freeMealValue > 0 && UPDiscount > 0)
			{
				UPDiscount -= (freeMealValue * PcntTypeValue);
				UPDiscount = Math.round(UPDiscount * 100) / 100;
			}
		}

		if (UPDiscount != -1)
		{
			UPDiscountElem = $('#OEH_preferred');
			if (UPDiscountElem.length)
			{
				UPDiscountElem.html(formatAsMoney(UPDiscount));
				newGrandTotal = formatAsMoney(newGrandTotal - UPDiscount);
				session_discount_basis -= UPDiscount;
			}
		}

	}

	// Need to break food and service fee portions early enough to
	// calculate session discount
	newGrandTotal = Number((newGrandTotal * 1) + (miscFoodSubtotal * 1));
	$('#OEH_misc_food_subtotal').html(formatAsMoney(miscFoodSubtotal));

	var serviceFee = Number($('#subtotal_service_fee').val());
	$('#OEH_subtotal_service_fee').html(formatAsMoney(serviceFee));
	newGrandTotal = Number((newGrandTotal * 1) + (serviceFee * 1));

	if (curPPDiscount > 0)
	{
		var food_portion_of_points_credit = 0;
		var fee_portion_of_points_credit = 0;

		if (discountMFYFeeFirst && serviceFee > 0)
		{

			if (curPPDiscount > serviceFee)
			{
				food_portion_of_points_credit = curPPDiscount - serviceFee;
				fee_portion_of_points_credit = serviceFee;
			}
			else
			{
				fee_portion_of_points_credit = curPPDiscount;
				food_portion_of_points_credit = 0;
			}
		}
		else
		{
			var foodPortionOfMaxDeduction = discountablePlatePointsAmount - serviceFee;

			if (curPPDiscount > foodPortionOfMaxDeduction)
			{
				var remainderAfterFoodDiscount = curPPDiscount - foodPortionOfMaxDeduction;
				food_portion_of_points_credit = foodPortionOfMaxDeduction;
				fee_portion_of_points_credit = remainderAfterFoodDiscount;
			}
			else
			{
				food_portion_of_points_credit = curPPDiscount;
				fee_portion_of_points_credit = 0;
			}
		}

		$('#OEH_plate_points_order_discount_food').html(formatAsMoney(food_portion_of_points_credit));
		$('#OEH_plate_points_order_discount_fee').html(formatAsMoney(fee_portion_of_points_credit));
	}
	else
	{
		$('#OEH_plate_points_order_discount_food').html(formatAsMoney(0));
		$('#OEH_plate_points_order_discount_fee').html(formatAsMoney(0));
	}



	// -------------------------------------------------------- Session Discount

	SessDisc = $('#SessDiscnoSD');
	if (SessDisc.length)
	{

		session_discount_basis = Number((session_discount_basis * 1) - (directDiscountVal * 1) - (food_portion_of_points_credit * 1));

		var selectedSD = $('input[name=SessDisc]:checked', '#editorForm').val();

		if (selectedSD == null || selectedSD == "")
			selectedSD = "noSD";

		var sessionDiscount = -1;

		if (selectedSD == "originalSD")
		{
			sessionDiscount = formatAsMoney((session_discount_basis * (originalSD) ? originalSD.value : "0") / 100);
		}
		else if (selectedSD == "activeSD")
		{
			sessionDiscount = formatAsMoney((session_discount_basis * activeSessionDiscount.value) / 100);
		}
		else
		{
			sessionDiscount = 0;
		}


		SDiscountElem = $('#OEH_session_discount');
		if (SDiscountElem.length)
		{
			SDiscountElem.html(formatAsMoney(sessionDiscount));
			newGrandTotal = formatAsMoney(newGrandTotal - sessionDiscount);
		}

	}

	// nonFood
	var miscNonFoodSubtotal = Number($('#misc_nonfood_subtotal').val());
	$('#OEH_misc_nonfood_subtotal').html(formatAsMoney(miscNonFoodSubtotal));
	newGrandTotal = Number((newGrandTotal * 1) + (miscNonFoodSubtotal * 1));


	// --------------------------------------------------------------------- tax

	// Currently the only taxed product is the enrollment fee which is taxed at a special rate
	var newNonFoodTax = formatAsMoney(miscNonFoodSubtotal * (curNonFoodTax / 100 ));
	var enrollmentTax = formatAsMoney(productsSubTotal * (curEnrollmentTax / 100 ));
	newNonFoodTax *= 1; // convert to float
	newNonFoodTax += (enrollmentTax * 1);
	//TODO: will have other products than enrollment someday.
	if (productsSubTotal)
	{
		$('#nonFoodTaxLabel').html('Non-Food & Enrollment Tax');
	}
	else
	{
		$('#nonFoodTaxLabel').html('Non-Food Tax');
	}

	var newFoodTax = 0;
	var newServiceTax = 0;


	if (curPPDiscount > 0)
	{
		newFoodTax = formatAsMoney(((newGrandTotal + (curPPDiscount * 1) - miscNonFoodSubtotal - serviceFee - food_portion_of_points_credit) * (curFoodTax / 100 )) + .000001);
		newServiceTax = formatAsMoney(((serviceFee -  fee_portion_of_points_credit) * (curServiceTax / 100 )) + .000001);
	}
	else
	{
		newFoodTax = formatAsMoney(((newGrandTotal - miscNonFoodSubtotal - serviceFee) * (curFoodTax / 100 )) + .000001);
		newServiceTax = formatAsMoney(serviceFee * (curServiceTax / 100 ) + .000001);
	}

	taxElem = document.getElementById('OEH_tax_subtotal');
	if ( taxElem )
		taxElem.innerHTML = formatAsMoney(newNonFoodTax);

	taxElem = document.getElementById('OEH_food_tax_subtotal');
	if ( taxElem )
		taxElem.innerHTML = formatAsMoney(newFoodTax);

	taxElem = document.getElementById('OEH_service_tax_subtotal');
	if ( taxElem )
		taxElem.innerHTML = formatAsMoney(newServiceTax);

	var preTaxTotal = Number(newGrandTotal);
	newGrandTotal = (newGrandTotal * 1) + (newFoodTax * 1) + (newNonFoodTax * 1) + (newServiceTax * 1);

	// -------------------------------------------------------------------- grand total
	$('#OEH_grandtotal').html(formatAsMoney(newGrandTotal));
	$('#OEH_new_total').html(formatAsMoney(newGrandTotal));
	$('#OEH_delta').html(formatAsMoney((orderInfoGrandTotal - newGrandTotal) * -1));

	var paymentTotal = 0;
	if($('#OEH_paymentsTotal').length)
	{
		paymentTotal = $('#OEH_paymentsTotal').html();
	}

	var remainingBalance = Number(formatAsMoney(paymentTotal - newGrandTotal));
	$('#OEH_remaing_balance').html(formatAsMoney(remainingBalance * -1));

	// Values are now calculated and the summary display updated
	// Next Display "balance" messages and set up payments

	if (orderState == 'CANCELLED')
	{
		remainingBalance = paymentTotal;
		$('#OEH_remaing_balance').html(formatAsMoney(remainingBalance));

	}

	if (remainingBalance > 0)
	{
		$("#balance_msg").html(formatAsMoney(remainingBalance * -1) + " owed to the customer.");
		$("#balanceRow").addClass('balance_msg_customer_owed');
		$("#newPaymentDiv").hide();

		if (canAdjustDelayedPayment && currentDPAmount > 0)
		{
			$('#dp_adjust_down_div').show();
		}
		else
		{
			$('#creditDiv').show();
		}


		$('#payment1_type').val('');
		$('#payment2_type').val('');
		changePayment1('');
		changePayment2('');

		paymentChanges = true;

		$("#use_store_credits").prop('checked', false);
		$("#use_store_credits").prop('disabled', true);

	}
	else if (remainingBalance < 0)
	{
		$("#balance_msg").html(formatAsMoney(remainingBalance * -1) + " owed to your store.");
		$("#balanceRow").addClass('balance_msg_store_owed');
		$("#newPaymentDiv").show();
		$('#creditDiv').hide();

		if (canAdjustDelayedPayment)
		{
			$('#dp_adjust_down_div').hide();
		}

		$("#use_store_credits").prop('disabled', false);


		var doNewLivePaymentAdjustments = true;
		if (doNewLivePaymentAdjustments)
		{

			var defaultPaymentAmount = remainingBalance * -1;

			if ($("#use_store_credits").is(":checked"))
			{

				var amountStoreCredit = Number($("#store_credits_amount").val());
				if (isNaN(amountStoreCredit.valueOf()))
				{
					amountStoreCredit = Number(0);
				}

				defaultPaymentAmount -= amountStoreCredit.valueOf();
			}

			defaultPaymentAmount = formatAsMoney(defaultPaymentAmount);

			if ($('#payment1_type').val()  == 'CC')
			{
				$('#payment1_cc_total_amount').val(defaultPaymentAmount);
			}
			else if ($('#payment1_type').val() == 'CHECK')
			{
				$('#payment1_check_total_amount').val(defaultPaymentAmount);
			}
			else if ($('#payment1_type').val().split("_")[0] == 'REF')
			{
				$('#payment1_ref_total_amount').val(defaultPaymentAmount);
			}
			else if (($('#payment1_type').val() == 'GIFT_CERT' || $('#payment1_type').val() == "GIFT_CARD") && $('#payment2_type').val() != "")
			{
				var payment1Amount = null;
    			if ($('#payment1_type').val()  == 'GIFT_CERT')
    			{
					payment1Amount = Number($('#payment1_gc_total_amount').val());
    			}
    			else if ($('#payment1_type').val() == 'GIFT_CARD')
    			{
					payment1Amount = Number($('#debit_gift_card_amount').val());
				}

				if (isNaN(payment1Amount.valueOf()))
				{
					payment1Amount = Number(0);
				}

				defaultPaymentAmount = formatAsMoney(defaultPaymentAmount - payment1Amount.valueOf());
    			if ($('#payment2_type').val()  == 'CC')
    			{
    				$('#payment2_cc_total_amount').val(defaultPaymentAmount);
    			}
    			else if ($('#payment2_type').val() == 'CHECK')
    			{
    				$('#payment2_check_total_amount').val(defaultPaymentAmount);
				}
    			else if ($('#payment2_type').val() == 'CASH')
    			{
    				$('#payment2_cash_total_amount').val(defaultPaymentAmount);
				}
    			else if ($('#payment2_type').val().split("_")[0] == 'REF')
    			{
    				$('#payment2_ref_total_amount').val(defaultPaymentAmount);
				}

			}
		}

	}
	else // == 0
	{
		$("#balance_msg").html("0.00");
		$("#balanceRow").addClass('balance_msg_balanced');
		$("#newPaymentDiv").show();
		$('#creditDiv').hide();

		if (canAdjustDelayedPayment)
		{
			$('#dp_adjust_down_div').hide();
		}

		$("#use_store_credits").prop('disabled', false);

		assignBalanceToRefTransactions(0);
	}

	if (newGrandTotal == 0 && $('#payment1_type').val() == "" && $('#payment2_type').val() == "" && !onTimeShotAtSupplyingZeroPaymentHasOccurred && orderState == 'SAVED')
	{
		$('#payment1_type').val('CASH');
		changePayment1('CASH');
		$('#payment1_cash_total_amount').val("0.00");

		onTimeShotAtSupplyingZeroPaymentHasOccurred = true;
	}


	var autoAdjustDiv = $('#autoAdjustDiv');
	if 	(autoAdjustDiv.length)
	{

		if (remainingBalance != 0 && canAutoAdjust)
		{
			if (canAdjustDelayedPayment && (currentDPAmount > 0 || remainingBalance < 0))
			{
				$('#OEH_auto_pay_msg').html("Check this to <span id='adj_action'><b>adjust customer's delayed payment.</b></span>");
			}
			else
			{
				if (remainingBalance > 0)
				{
					$('#OEH_auto_pay_msg').html("Check this to <span id='adj_action'><b>credit customer's credit card now.</b></span>");
				}
				else
				{
					$('#OEH_auto_pay_msg').html("Check this to <span id='adj_action'><b>charge original credit card now.</b></span>");
				}
			}

			autoAdjustDiv.show();
			if (!forceManualAutoAdjust)
			{
				$('#autoAdjust').prop('checked', true);
				handleAutoAdjust($('#autoAdjust').get()[0]);
			}
		}
		else
		{
		    // if the div is hidden skip these steps

		    if (autoAdjustDiv.is(":visible"))
		    {
				autoAdjustDiv.hide();
				$('#autoAdjust').prop('checked', false);
				$('#payment1_type').val('');
				$('#payment2_type').val('');
				changePayment1('');
				changePayment2('');
		    }
		}
	}

	$('#help_msg').html("");

	// enforce a few rules by disallowing checkout
	if ( $('#submit_changes').length )
	{

		if ( servings > 0 || sideDishQty > 0 || addonsSubtotal > 0)
		{
			$('#submit_changes').prop("disabled", false);
		}
		else
		{
			$('#submit_changes').prop("disabled", true);
		}

		if (directDiscountVal >= (Number(preTaxTotal) + Number(directDiscountVal)) && preTaxTotal != 0)
		{
			$('#submit_changes').prop("disabled", true);
			$('#help_msg').html($('#help_msg').html() + "You cannot use a Direct Order Discount that is equal to or greater than the new grand total. Please use the \"No Charge\" payment type to give away an order.");
		}

	}

	if ($('#help_msg').length)
	{
		if ($('#help_msg').html() != "")
		{
			$('#help_msg').show();
		}
		else
		{
			$('#help_msg').hide();
		}
	}

	reportPaymentStatus(false);

	HideLineItemsIfZero();

	if (orderState != 'NEW')
	{
		updateChangeList();
	}

}