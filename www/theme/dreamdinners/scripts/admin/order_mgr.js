var itemChanges = false;
var feeChanges = false;
var discountOrPaymentChanges = false;
var deliveryChanges = false;
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
var needRefferalRewardMaxDiscountChangeWarning = false;
var lastMaxPPDiscountAmount = -1;
var lastMaxReferralRewardDiscountAmount = -1;
let feesTabIsDirty = false;

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
		var newQueryString = "main.php?";

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
	handle_ltd_round_up();
	handleDirectOrderDiscountFocusing();

	$('#shipping_phone_number').trigger('change');

	handleAutoOrderButtons();

	$('#selectedBundle').trigger('change');

	initNoInventoryRows();

	handleDinnerDollarAccordion();
	handleAdminNoteAccordion();

	setMealCustomizationDefault();

}
function setMealCustomizationDefault()
{
	if( $('#opted_to_customize_recipes').length && default_meal_customization_to_selected == true && $('#opted_to_customize_recipes').prop('checked'))
	{
		$('#opted_to_customize_recipes').click();
	}
}

function handleDinnerDollarAccordion()
{
	$(".dd_accordion_header").click(function () {

		$header = $(this);
		$content = $(".dd_accordion_content");
		$header.html(function () {
			return ($content.is(":visible") ? "Show " : "Hide ") + "Recent Dinner Dollar History" + ($content.is(":visible") ? " &#8744;" : " &#8743;");
		});
		$content.slideToggle(500, function () {
		});

	});
}

function handleAdminNoteAccordion()
{
	$(".dod_accordion_header").click(function () {

		$header = $(this);
		$content = $(".dod_accordion_content");
		$header.html(function () {
			return ($content.is(":visible") ? "Show " : "Hide ") + "Admin Order Notes" + ($content.is(":visible") ? " &#8744;" : " &#8743;");
		});
		$content.slideToggle(500, function () {
		});

	});
}

function initNoInventoryRows()
{
	$('.inventory-row').each(function () {
		let remaining = $(this).data('orig-remaining');
		let servings = $(this).data('servings');
		let entree_id = $(this).data('entree');
		if (remaining < servings)
		{

			$itemCount = $('input[name="qty_' + entree_id + '"]').val();
			if ($itemCount == 0)
			{
				$(this).find("input,button,textarea,select,a,td").addClass('text-muted');
				$(this).find("img").hide();
			}
		}
	})
}

function handleAutoOrderButtons(e, size)
{

	$("#selectMediumStarterPackEntrees").on('click', function (e) {

		var countMissedMeals = 0;

		$("[id^='bnd_'] input").each(function () {

			if ($(this).data('pricing_type') == 'HALF')
			{
				var id = this.id.split("_")[1];

				$("#qty_" + id).each(function () {

					var entreeID = $(this).data('entreeid');

					var curInventory = entreeIDToInventoryMap[entreeID].remaining;

					if (3 > curInventory)
					{
						countMissedMeals++;
					}
					else
					{
						$(this).val(1);
					}
				});
			}
		});

	});

	$("#selectLargeStarterPackEntrees").on('click', function (e) {
		var countMissedMeals = 0;

		$("[id^='bnd_'] input").each(function () {

			if ($(this).data('pricing_type') == 'FULL')
			{
				var id = this.id.split("_")[1];

				$("#qty_" + id).each(function () {

					var entreeID = $(this).data('entreeid');

					var curInventory = entreeIDToInventoryMap[entreeID].remaining;

					if (6 > curInventory)
					{
						countMissedMeals++;
					}
					else
					{
						$(this).val(1);
					}
				});
			}
		});

		if (countMissedMeals > 0)
		{
			dd_message({
				title: 'Error',
				message: '1 or more items could not be selected due to lack of inventory.  Please review the selections and adjust to select a full 12 item order.'
			});
		}

		calculateTotal();
		setItemTabAsDirty();

		e.preventDefault();

	});

}

var intenseLoggingOn = true;

function handleDirectOrderDiscountFocusing()
{

	$("#plate_points_discount, #direct_order_discount").focus(function (e) {

		if ($(this).val() == 0)
		{
			$(this).val("");
		}
		if ($(this).val().length > 0)
		{
			$(this).select();
		}
	});

	$("#plate_points_discount, #direct_order_discount").blur(function (e) {

		var curVal = $(this).val();
		curVal = (curVal.trim());

		if (curVal == "" || isNaN(curVal))
		{
			$(this).val("0");
		}

	});

}

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
		url: 'ddproc.php',
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
		success: function (json) {
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
		error: function (objAJAXRequest, strError) {
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
	$('[id^="gd_special_instruction_note_button-"]').each(function () {

		$(this).on('click', function (e) {

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
				url: 'ddproc.php',
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
				success: function (json) {
					if (json.processor_success)
					{
						if (do_op == 'get')
						{
							var textarea_elem = $('<textarea></textarea>').addClass('form-control').val((json.special_instruction_note ? json.special_instruction_note : "")).on('keyup', function (e) {

								if ($(this).val() != strip_tags($(this).val()))
								{
									$(this).val(strip_tags($(this).val()));
								}

							});

							$('#gd_special_instruction_note-' + booking_id).addClass('special_instruction_note_edit').html(textarea_elem);

							$('#gd_special_instruction_note_button-' + booking_id).data('edit_mode', true).html('Save Note');

							$('#gd_special_instruction_note_cancel_button-' + booking_id).data('special_instruction_note_value', (json.special_instruction_note ? json.special_instruction_note : "")).on('click', function (e) {

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
				error: function (objAJAXRequest, strError) {
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

	$("#coupon_code").on('keypress', function (evt) {
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

	$("#order_user_notes").on('keypress', function (evt) {
		evt.stopPropagation();
		return true;
	});

	$("#payment1_gc_payment_number").on('keyup', function (evt) {

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
	$("#changeListTab").on('click', function () {

		let html = getChangeListHTML();

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
			close: function (event, ui) {
				$("#changelist").remove();
			}
		});
	});
}

function setupSiteAdminFunctions()
{
	$("#in-store_status, #plate_points_status").on('click', function () {

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
				cancel: function () {
					checkbox.prop('checked', oldState ? true : false)

				},
				confirm: function () {
					$.ajax({
						url: 'ddproc.php',
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
						success: function (json) {
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
						error: function (objAJAXRequest, strError) {

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

function updateMealCustomizationOrgValues()
{
	dd_toast({
		message: 'Meal Customization Fee Saved',
		position: 'topcenter'
	});
	$("#subtotal_meal_customization_fee").data('org_value', $("#subtotal_meal_customization_fee").val());

}
function updateItemsOrgValues()
{

	$("[id^='qty_']").each(function () {
		$(this).data('org_value', $(this).val());
	});

	$("[data-dd_type='item_field']").each(function () {
		$(this).data('org_value', $(this).val());
	});

	$('#selectedBundle').data('org_value', $('#selectedBundle').is(':checked') ? "1" : "0");

	if (isDreamTaste || isFundraiser)
	{
		$("[id^='bnd_']").each(function () {
			$(this).data('org_value', $(this).val());
		});
	}
	else
	{
		$("[id^='bnd_']").each(function () {
			$(this).data('org_value', $(this).is(':checked') ? "1" : "0");
		});
	}

	updateChangeList();

}

function saveAll()
{
	saveItems(true, false, true);
}

function saveItems(saveDiscountsUponCompletion, activateOnSaveDiscountsCompletion, saveDeliveryAddressUponCompletion)
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
		$("[id^=bnd_]").each(function () {

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				itemsList[item_id] = $(this).val();
			}
		});

		// this will pick up any FT items
		$("[id^=qty_]").each(function () {

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

			$("[id^=bnd_]").each(function () {

				if ($(this).is(":checked"))
				{
					var item_id = this.id.split("_")[1];
					introItemsList[item_id] = "on";
				}
			});

		}

		$("[id^=qty_]").each(function () {

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				itemsList[item_id] = $(this).val();
			}

		});

		$("[id^=sbi_]").each(function () {

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

	var subtotal_delivery_fee = $("#subtotal_delivery_fee").val();
	if (isNaN(subtotal_delivery_fee))
	{
		subtotal_delivery_fee = "0.00";
	}

	var misc_food_subtotal_desc = $("#misc_food_subtotal_desc").val();
	var misc_nonfood_subtotal_desc = $("#misc_nonfood_subtotal_desc").val();
	var service_fee_description = $("#service_fee_description").val();

	var special_instructions = $("#order_user_notes").val();

	var ltdOrderedMealsArray = $("#ltdOrderedMealsArray").val();

	var bagFeeTotal = 0;
	var bagFeeCount = 0;
	if (storeSupportsBagFees)
	{
		var bagFeeCount = $("#total_bag_count").val();

		if (isNaN(bagFeeCount))
		{
			bagFeeCount = 0;
		}

		bagFeeTotal = storeDefaultBagFee * bagFeeCount;
	}

	let opted_to_bring_bags = $("#opted_to_bring_bags").is(":checked");

	let manual_customization_fee = $("#manual_customization_fee").val();
	let opted_to_customize = ($("#opted_to_customize_recipes").length > 0 && !$("#opted_to_customize_recipes").is(":checked"));
	let mealCustomizationFee = $("#subtotal_meal_customization_fee").val();

	currentlySavingOrder = true;

	$.ajax({
		url: 'ddproc.php',
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
			subtotal_delivery_fee: subtotal_delivery_fee,
			manual_customization_fee: manual_customization_fee,
			opted_to_customize: opted_to_customize,
			meal_customization_fee: mealCustomizationFee,
			is_intro: is_intro,
			intro_items: introItemsList,
			sub_items: subItemsList,
			ltdOrderedMealsArray: ltdOrderedMealsArray,
			special_instructions: special_instructions,
			storeSupportsBagFees: storeSupportsBagFees,
			bagFeeTotal: bagFeeTotal,
			bagFeeCount: bagFeeCount,
			opted_to_bring_bags: opted_to_bring_bags
		},
		success: function (json) {
			if (json.processor_success)
			{
				itemTabIsDirty = false;
				feesTabIsDirty = false;
				if(opted_to_customize){
					updateMealCustomizationOrgValues();
				}
				updateItemsOrgValues();

				currentlySavingOrder = false;

				intenseLogging("saveItems() successful");

				if (saveDiscountsUponCompletion)
				{
					saveDiscounts(activateOnSaveDiscountsCompletion, saveDeliveryAddressUponCompletion);
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
		error: function (objAJAXRequest, strError) {

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
		url: 'ddproc.php',
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
		success: function (json) {
			if (json.processor_success)
			{

				intenseLogging("setSessionAndSave() successful");

				if (json.full_session_warning_required)
				{
					bounce("main.php?page=admin_order_mgr&order=" + json.order_id + "&session_full=true");
				}
				else
				{
					bounce("main.php?page=admin_order_mgr&order=" + json.order_id);
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
		error: function (objAJAXRequest, strError) {
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

function Reschedule(org_session_id, transition_type, suppressEmail)
{

	intenseLogging("Reschedule() called");

	$.ajax({
		url: 'ddproc.php',
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
			org_session_id: org_session_id,
			order_state: orderState,
			transition_type: transition_type,
			suppressEmail: suppressEmail
		},
		success: function (json) {
			if (json.processor_success)
			{

				intenseLogging("Reschedule() successful");

				session_id = json.new_session_id;
				$("#curSessionDate").html(json.new_session_time);

				$("#session").val(json.new_session_id);

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
					calculateTotal();

				}
				else
				{
					$("#noSessionDiscountRow").show();
					$("#newSessionDiscountBody").hide();
					activeSessionDiscount.id = 0;
					activeSessionDiscount.type = 'none';
					activeSessionDiscount.value = 0;

				}

				$("#curSessionTypeSpan").html(json.session_info.session_type_title);
				$("#curSessionRemainingSlotsSpan").html(json.session_info.remaining_slots);
				$("#curSessionRemainingIntroSlotsSpan").html(json.session_info.remaining_intro_slots);

				if (json.client_ops.hasOwnProperty('require_delivery_address') && !$("#shipping_address_line1").length)
				{
					// rather than load a new tab for now just reload the page
					bounce(window.location.href);

				}

				for (var key in json.client_ops)
				{
					if (json.client_ops.hasOwnProperty(key))
					{
						switch (key)
						{
							case 'adj_service_fee':
							{
								$("#subtotal_service_fee").val(json.client_ops[key]);
								$("#OEH_subtotal_service_fee_org").html(formatAsMoney(json.client_ops[key]));
								id = "OEH_subtotal_service_fee_org"
								calculateTotal();
							}
								break;
							case 'update_svc_fee_desc':
							{
								$("#service_fee_description").val(json.client_ops[key]);
								calculateTotal();
							}
								break;
							case 'adj_delivery_fee':
							{
								if (!$("#subtotal_delivery_fee").length)
								{
									// The current DOM has no delivery fee field so for now causea refresh
									bounce(window.location.href);
								}

								$("#subtotal_delivery_fee").val(json.client_ops[key]);
								$("#OEH_subtotal_delivery_fee_org").html(formatAsMoney(json.client_ops[key]));
								calculateTotal();
							}
								break;
							case 'require_delivery_address':
							{
								$("[id^='shipping_']").each(function () {
									$(this).attr('required', 'required');
								});
							}
								break;
							case 'delete_delivery_address':
							{
								$("#delivery_tab_li").remove();
								$("#delivery").remove();
							}
								break;
						}
					}
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
		error: function (objAJAXRequest, strError) {
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

	saveItems(false, false, false);
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
		$("#fees_tab_li").addClass("disabled");
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

function isDeliveryAddressComplete()
{
	var validated = true;

	$("[id^='shipping_']").each(function () {

		if (!$(this).val() && $(this).attr('required'))
		{
			validated = false;
		}
	});
	return validated;
}

function saveDeliveryAddress(payOnCompletion, token)
{

	var shipping_data = {};
	$("[id^='shipping_']").each(function () {
		shipping_data[this.id] = $(this).val();
	});

	intenseLogging("saveDeliveryAddress() called");

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 200000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor',
			store_id: STORE_DETAILS.id,
			user_id: user_id,
			op: 'save_delivery_address',
			order_id: order_id,
			shipping_data: shipping_data
		},
		success: function (json) {
			if (json.processor_success)
			{
				intenseLogging("saveDeliveryAddress() successful");

				dd_toast({
					message: 'Delivery Address Saved',
					position: 'topcenter'
				});

				if (payOnCompletion)
				{
					onAddPaymentAndActivate(false, true, token);
				}

			}
			else
			{
				intenseLogging("save_delivery_address: " + json.processor_message);

				dd_message({
					title: 'Error',
					message: json.processor_message
				});

			}
		},
		error: function (objAJAXRequest, strError) {

			intenseLogging("save_delivery_address: " + strError + " | " + objAJAXRequest.responseText);

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});

		}

	});

}

function onDeliveryTabSelected()
{

}

function onDeliveryTabDeselected()
{
	if (!isDeliveryAddressComplete())
	{
		dd_message({
			title: 'Error',
			message: 'Please check the Delivery Address and phone number fields for completeness.'
		});

		return false;
	}
	else
	{
		saveDeliveryAddress(false, false);
		return true;
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

			saveItems(false, false, false);
		}
	}

	return true;
}

function onFeesTabSelected()
{

}

function onFeesTabDeselected()
{
	return true;
}

function onPaymentTabSelected()
{
	handle_admin_order_notes();
	handle_free_menu_item_edit();
}

function onPaymentTabDeselected()
{
	if (orderState != 'ACTIVE' && (discountChanges || reportingChanges))
	{
		saveDiscounts(false, false);
	}

	return true;
}

function onRelatedOrdersTabSelected()
{
	if (!relatedOrdersAreLoaded)
	{

		intenseLogging("Loading related Orders");

		$.ajax({
			url: 'ddproc.php',
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
			success: function (json) {

				intenseLogging("Loading related Orders successful");

				$("#related_orders").html(json.data);
				$("#loading_related_orders").remove();
				relatedOrdersAreLoaded = true;
			},
			error: function (objAJAXRequest, strError) {
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
		url: 'ddproc.php',
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
		success: function (json) {
			$("#history_div").html(json.html);

			handle_guest_account_notes();

			handle_admin_carryover_notes();

			handle_admin_order_notes();
		},
		error: function (objAJAXRequest, strError) {
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

	$("#plate_points_discount, #direct_order_discount").each(function () {
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
		$("[id^=bnd_]").each(function () {

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				itemsList[item_id] = $(this).val();
			}
		});

		//pick up any sides
		$("[id^=qty_]").each(function () {

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

			$("[id^=bnd_]").each(function () {

				if ($(this).is(":checked"))
				{
					var item_id = this.id.split("_")[1];
					introItemsList[item_id] = "on";
				}
			});

		}

		$("[id^=qty_]").each(function () {

			if ($(this).val() > 0)
			{
				var item_id = this.id.split("_")[1];
				itemsList[item_id] = $(this).val();
			}
		});

		$("[id^=sbi_]").each(function () {

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

	var subtotal_delivery_fee = $("#subtotal_delivery_fee").val();
	if (isNaN(subtotal_delivery_fee))
	{
		subtotal_delivery_fee = "0.00";
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

	var ltdOrderedMealsArray = $("#ltdOrderedMealsArray").val();

	$.ajax({
		url: 'ddproc.php',
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
			subtotal_delivery_fee: subtotal_delivery_fee,
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
			ltdOrderedMealsArray: ltdOrderedMealsArray,
			dd_csrf_token: $('input[name=dd_csrf_token]', '#editorForm').val()

		},
		success: function (json) {
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
		error: function (objAJAXRequest, strError) {

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

function saveDiscounts(payOnCompletion, saveDeliveryAddressUponCompletion)
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
	var add_ltd_round_up = ($('#add_ltd_round_up').is(':checked') ? true : false);
	var ltd_round_up_select = $('#ltd_round_up_select').val();

	var bagFeeTotal = 0;
	var bagFeeCount = 0;
	if (storeSupportsBagFees)
	{
		var bagFeeCount = $("#total_bag_count").val();

		if (isNaN(bagFeeCount))
		{
			bagFeeCount = 0;
		}

		bagFeeTotal = storeDefaultBagFee * bagFeeCount;
	}

	let opted_to_bring_bags = $("#opted_to_bring_bags").is(":checked");

	$.ajax({
		url: 'ddproc.php',
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
			fundraiser_value: fundraiser_value,
			add_ltd_round_up: add_ltd_round_up,
			ltd_round_up_select: ltd_round_up_select,
			storeSupportsBagFees: storeSupportsBagFees,
			bagFeeTotal: bagFeeTotal,
			bagFeeCount: bagFeeCount,
			opted_to_bring_bags: opted_to_bring_bags

		},
		success: function (json) {
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

				if (saveDeliveryAddressUponCompletion && $("#shipping_firstname")[0])
				{
					saveDeliveryAddress(payOnCompletion, json.paymentToken);
				}
				else if (payOnCompletion)
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
		error: function (objAJAXRequest, strError) {

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
	$('#ltd_round_up_select').data('org_value', $('#ltd_round_up_select').val());
	$('#OEH_ltd_round_up_org').data('org_value', $('#ltd_round_up_select').val());

	if ($('#add_ltd_round_up').is(':checked'))
	{
		$('#add_ltd_round_up').data('org_value', true);
	}
	else
	{
		$('#add_ltd_round_up').data('org_value', false);
	}

	updateChangeList();
}

function send(token, paymentNumber, warnOfOutstandingSavedOrdersOnFullSession, addOnly, go_to_confirm, payment1Data)
{

	intenseLogging("send() called for payment# " + paymentNumber);

	$('#paypal-result').on("load", function () {

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
						"Okay": function () {
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
							"Okay": function () {
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
			payflowErrorURL = "https://dreamdinners.com/ddproc.php?processor=admin_payflow_callback";
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
			payflowErrorURL = "https://dreamdinners.com/ddproc.php?processor=admin_payflow_callback";
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
			billing_city: "",
			billing_state_id: "",
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
				paymentData.billing_city = $("#billing_city_1").val();
				paymentData.billing_state_id = $("#billing_state_id_1").val();

				if ($('#save_cc_as_ref_1').is(':checked'))
				{
					paymentData.save_card = true;
				}

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

				if ($('#save_cc_as_ref_2').is(':checked'))
				{
					paymentData.save_card = true;
				}

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
	let minimumVal = null;
	let minimumType = null;

	if (typeof order_minimum !== 'undefined' && typeof order_minimum.minimum !== 'undefined')
	{
		minimumVal = order_minimum.minimum;
		minimumType = order_minimum.minimum_type
	}

	intenseLogging("addPaymentAndActivate() called");
	let servingsCount = 0;
	let enforce36ServingMinimum = false;
	let underFilledMessage = 'This order is for less than 36 servings. Do you wish to continue?';

	if (minimumVal != null)
	{
		underFilledMessage = 'This order is for less than ' + minimumVal + ' ' + minimumType.toLowerCase() + 's. Do you wish to continue?';
	}

	servingsCount = Number($('#OEH_number_servings').html());
	let itemCount = Number($('#OEH_number_core_servings').html());

	if (orderIsEligibleForMembershipDiscount && storeSupportsMembership)
	{
		if (membership_status.eligible_menus[menu_id].number_qualifying_orders == 0)
		{
			underFilledMessage = 'This Meal Prep+ order has less than 36 core servings. Because this guest does not have a consecutive standard order for this menu month, this order is required to have at least 36 servings before a smaller order can be added.';
			enforce36ServingMinimum = true;
		}
	}

	let quantityOrdered = servingsCount.valueOf();
	let requiredMinimum = 36;
	if (minimumVal != null)
	{
		requiredMinimum = minimumVal;
		if (minimumType === 'ITEM')
		{
			quantityOrdered = itemCount.valueOf();
		}
	}

	if (quantityOrdered < requiredMinimum && enforce36ServingMinimum)
	{
		intenseLogging("addPaymentAndActivate() - warning for less than " + requiredMinimum + '' + minimumType);

		dd_message({
			title: 'Warning',
			message: underFilledMessage
		});

		return false;

	}
	else if (quantityOrdered < requiredMinimum && !isDreamTaste && !isFundraiser && !$("#selectedBundle").is(":checked"))
	{

		intenseLogging("addPaymentAndActivate() - warning for less than " + requiredMinimum + '' + minimumType);

		dd_message({
			title: 'Warning',
			message: underFilledMessage,
			confirm: function () {
				// always first ensure that discounts are updated on server
				intenseLogging("addPaymentAndActivate() - confirm dialog");

				let message = "You are about to activate a Saved order. This will consume inventory and a session slot. Are you sure you want to continue?";
				let changesBlurb = getAbbreviatedSummaryString();
				message += "<br /><br />" + changesBlurb;

				dd_message({
					title: 'Add Payment and Book Order',
					div_id: 'aPaBO',
					message: message,
					modal: true,
					width: 400,
					height: 460,
					confirm: function () {
						var discounts_are_valid = saveItems(true, true, true);
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
			let bundleIsSelected = false;
			if ($('#selectedBundle').is(':checked'))
			{
				bundleIsSelected = true;
			}

			if (bundleIsSelected)
			{

				let numBundItems = countSelectedBundleItems();
				if (numBundItems < intro_servings_required)
				{

					dd_message({
						title: 'Error',
						message: 'Please select at least ' + intro_servings_required + ' servings of Meal Prep Starter Pack items.'
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
		let message = "You are about to activate a Saved order. This will consume inventory and a session slot. Are you sure you want to continue?";
		let changesBlurb = getAbbreviatedSummaryString();
		message += "<br /><br />" + changesBlurb;

		dd_message({
			title: 'Add Payment and Book Order',
			message: message,
			modal: true,
			width: 400,
			height: 460,
			confirm: function () {
				let discounts_are_valid = saveItems(true, true, true);
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

		var max_plate_points_deduction = $("#max_plate_points_deduction").html();
		if (!max_plate_points_deduction)
		{
			max_plate_points_deduction = 0;
		}

		$.ajax({
			url: 'ddproc.php',
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
				max_plate_points_deduction: max_plate_points_deduction,
				delay_remainder: DP_State,
				dd_csrf_token: token
			},
			success: function (json) {
				if (json.processor_success)
				{
					intenseLogging("save2PaymentsAndBookOrder() save_payment successful");

					if (json.warnOfOutstandingSavedOrdersOnFullSession)
					{
						bounce("main.php?page=admin_order_mgr_thankyou&order=" + json.order_id + '&full_session=true');
					}
					else
					{
						bounce("main.php?page=admin_order_mgr_thankyou&order=" + json.order_id);
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
			error: function (objAJAXRequest, strError) {
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
			url: 'ddproc.php',
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
			success: function (json) {
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
			error: function (objAJAXRequest, strError) {
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
			url: 'ddproc.php',
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
			success: function (json) {
				if (json.processor_success)
				{
					intenseLogging("savePayment2() add_payment successful");

					if (warnOfOutstandingSavedOrdersOnFullSession)
					{
						bounce("main.php?page=admin_order_mgr_thankyou&order=" + json.order_id + '&full_session=true');
					}
					else
					{
						bounce("main.php?page=admin_order_mgr_thankyou&order=" + json.order_id);
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
			error: function (objAJAXRequest, strError) {
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
			url: 'ddproc.php',
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
			success: function (json) {
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
			error: function (objAJAXRequest, strError) {
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
		var DP_State = 0;
		if ($('#ref_payment2_is_store_specific_flat_rate_deposit_delayed0').length)
		{
			DP_State = $('input:radio[name=ref_payment2_is_store_specific_flat_rate_deposit_delayed]:checked').val();

			if (DP_State == 1)
			{
				DP_State = 4;
			}
		}
		additionalPaymentData = payment1Data;
	}
	else
	{
		mainPaymentData = payment1Data;
	}

	var max_plate_points_deduction = $("#max_plate_points_deduction").html();
	if (!max_plate_points_deduction)
	{
		max_plate_points_deduction = 0;
	}

	$.ajax({
		url: 'ddproc.php',
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
			max_plate_points_deduction: max_plate_points_deduction,
			store_credits_amount: store_credit_amount,
			dd_csrf_token: token,
			payment2Type: payment2Type
		},
		success: function (json) {
			if (json.processor_success)
			{
				intenseLogging("handleDirectPayment() save_payment successful");

				if (json.warnOfOutstandingSavedOrdersOnFullSession)
				{
					bounce("main.php?page=admin_order_mgr_thankyou&order=" + json.order_id + '&full_session=true');
				}
				else
				{
					bounce("main.php?page=admin_order_mgr_thankyou&order=" + json.order_id);
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
		error: function (objAJAXRequest, strError) {
			if (strError == 'timeout')
			{

				dd_message({
					title: 'Order Processing Timed Out',
					message: "The request to finalized the order has timed out. This may be due to network congestion. The order may have been successful. The page will refresh and show the actual current status.",
					modal: true,
					noOk: true,
					closeOnEscape: false,
					buttons: {
						'Refresh': function () {
							$(this).remove();
							bounce(window.location.href);

						}
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

		var max_plate_points_deduction = $("#max_plate_points_deduction").html();
		if (!max_plate_points_deduction)
		{
			max_plate_points_deduction = 0;
		}

		$.ajax({
			url: 'ddproc.php',
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
				max_plate_points_deduction: max_plate_points_deduction,
				store_credits_amount: store_credit_amount,
				dd_csrf_token: token
			},
			success: function (json) {
				if (json.processor_success)
				{

					intenseLogging("onAddPaymentAndActivate() save_payment successful");

					if (json.warnOfOutstandingSavedOrdersOnFullSession)
					{
						bounce("main.php?page=admin_order_mgr_thankyou&order=" + json.order_id + '&full_session=true');
					}
					else
					{
						bounce("main.php?page=admin_order_mgr_thankyou&order=" + json.order_id);
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
			error: function (objAJAXRequest, strError) {
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
			url: 'ddproc.php',
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
			success: function (json) {
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
			error: function (objAJAXRequest, strError) {
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
		url: 'ddproc.php',
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
		success: function (json) {
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
		error: function (objAJAXRequest, strError) {
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

function doReschedule(id, sessionTime, transition_type)
{
	var curSessionDate = $("#curSessionDate").html();

	dd_message({
		title: 'Reschedule',
		message: '<div style="text-align: center;"><div>From</div><div style="font-weight: bold;">' + curSessionDate + '</div><div>to</div><div style="font-weight: bold;">' + sessionTime + '</div>'
			+ '<div style="margin-top:5px;"><input type="checkbox" name="reschedule_suppress_email" id="reschedule_suppress_email" />&nbsp;<label for="reschedule_suppress_email">Suppress Sending Reschedule Email to Guest</label></div>',
		modal: true,
		confirm: function () {
			var suppressEmail = false;
			if ($("#reschedule_suppress_email").is(":checked"))
			{
				suppressEmail = true;
			}

			var org_session_id = session_id;
			session_id = id;
			Reschedule(org_session_id, transition_type, suppressEmail);

		},
		open: function () {

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
				message: "This order cannot be rescheduled as it is in the previous calendar month and that month has been locked and finalized."
			});
		}
		else if (control_message == "MFY_to_Assembled")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Pick Up to an Assembly.</span><br /><br />The Service Fee will be removed. " +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "tononpreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "MFY_to_Assembled");
						$("#tononpreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "Assembly_to_MFY")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Assembly to Pickup.</span><br /><br />The Service Fee will be applied. " +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "Assembly_to_MFY");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "MFY_to_HD")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Pick Up to Home Delivery.</span><br /><br />The Service Fee will be adjusted, the Delivery Fee will be added and a Delivery Address is now required." +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments, Add the Delivery Address and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "MFY_to_HD");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "MFY_to_CPU")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Pick Up to Community Pick Up.</span><br />",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "MFY_to_CPU");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "CPU_to_MFY")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Community Pick Up to Pick Up.</span><br />",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "CPU_to_MFY");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "HD_to_Assembled")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Home Delivery to Assembly.</span><br /><br />The Delivery Fee, Service Fee, and Delivery Address will be removed." +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "HD_to_Assembled");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "HD_to_MFY")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Home Delivery to Pick Up.</span><br /><br />The Delivery Fee will be removed. The Service Fee will be adjusted. The Delivery Address will be removed." +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "HD_to_MFY");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "HD_to_CPU")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Home Delivery to Community Pick Up.</span><br /><br />The Delivery Address will be removed and the Delivery Fee and Service Fee may be adjusted." +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "HD_to_CPU");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "CPU_to_Assembled")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Community Pick Up to Assembly.</span><br /><br />The Delivery Fee and Service Fee will be removed." +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "CPU_to_Assembled");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "CPU_to_HD")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Community Pick Up to Home Delivery.</span><br /><br />The Delivery Fee and Service Fee may be adjusted. The Delivery Address is now required." +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments, Add the Delivery Address and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "CPU_to_HD");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "Assembly_to_CPU")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Assembly to Community Pick Up.</span><br /><br />A Delivery Fee and Service Fee may be added." +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "Assembly_to_CPU");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "Assembly_to_HD")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Assembly to Home Delivery.</span><br /><br />The Service and Delivery Fees will be added. The Delivery Address is now required." +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments, Add the Delivery Address and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, "Assembly_to_HD");
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "MFY_to_WI")
		{
			sessionTime = sessionTime.substring(0, sessionTime.indexOf(' - '));
			sessionTime = 'Walk-In on ' + sessionTime;
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Pick Up to Walk-in.</span><br /><br /> " +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, control_message);
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "Assembly_to_WI")
		{
			sessionTime = sessionTime.substring(0, sessionTime.indexOf(' - '));
			sessionTime = 'Walk-In on ' + sessionTime;
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Assembly to Walk-in.</span><br /><br /> " +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, control_message);
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "HD_to_WI")
		{
			sessionTime = sessionTime.substring(0, sessionTime.indexOf(' - '));
			sessionTime = 'Walk-In on ' + sessionTime;
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Home Delivery to Walk-in.</span><br /><br />The Delivery Fee will be removed. The Service Fee will be adjusted. The Delivery Address will be removed." +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, control_message);
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "WI_to_Assembled")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Walk-in to Pickup.</span><br /><br />The Service Fee will be adjusted. " +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, control_message);
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "WI_to_MFY")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Walk-in to Pick Up.</span><br /><br />The Service Fee will be adjusted. " +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, control_message);
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "WI_to_CPU")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Walk-In to Community Pick Up.</span><br />",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, control_message);
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "WI_to_HD")
		{
			dd_message({
				title: 'Reschedule',
				message: "<br /><span style='font-weight:bold;'>You are rescheduling from Walk-in to Home Delivery.</span><br /><br />The Service Fee will be adjusted, the Delivery Fee will be added and a Delivery Address is now required." +
					"<br /><br /><span style='color:red; font-weight:bold;'>After Rescheduling this Order, Please Review this Order, Adjust Payments, Add the Delivery Address and Finalize to complete the Reschedule.</span>",
				modal: true,
				div_id: "topreasm_div",
				noOk: true,
				buttons: {
					Continue: function () {
						doReschedule(id, sessionTime, control_message);
						$("#topreasm_div").remove();
					},
					Cancel: function () {
						$(this).remove();
					}
				}
			});
		}
		else if (control_message == "none_wi")
		{
			sessionTime = sessionTime.substring(0, sessionTime.indexOf(' - '));
			sessionTime = 'Walk-In on ' + sessionTime;
			doReschedule(id, sessionTime, "None");
		}
		else
		{
			doReschedule(id, sessionTime, "None");
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
	if (GUEST_PREFERENCES[user_id].TC_DELAYED_PAYMENT_AGREE.value == 1)
	{
		return;
	}

	$('#payment1_is_store_specific_flat_rate_deposit_delayed_payment1, #payment1_is_store_specific_flat_rate_deposit_delayed_payment2, ' +
		' #ref_payment1_is_store_specific_flat_rate_deposit_delayed1, #ref_payment1_is_store_specific_flat_rate_deposit_delayed2, ' +
		' #payment2_is_store_specific_flat_rate_deposit_delayed_payment1, #payment2_is_store_specific_flat_rate_deposit_delayed_payment2, ' +
		' #ref_payment2_is_store_specific_flat_rate_deposit_delayed1, #ref_payment2_is_store_specific_flat_rate_deposit_delayed2').on('click', function (e) {

		dd_message({
			title: lang.en.tc.terms_and_conditions,
			message: lang.en.tc.delayed_payment,
			modal: true,
			noOk: true,
			closeOnEscape: false,
			open: function (event, ui) {
				$(this).parent().find('.ui-dialog-titlebar-close').hide();
			},
			buttons: {
				'Agree': function () {

					$(this).remove();

					$('#payment1_is_store_specific_flat_rate_deposit_delayed_payment1, #payment1_is_store_specific_flat_rate_deposit_delayed_payment2, ' +
						' #ref_payment1_is_store_specific_flat_rate_deposit_delayed1, #ref_payment1_is_store_specific_flat_rate_deposit_delayed2, ' +
						' #payment2_is_store_specific_flat_rate_deposit_delayed_payment1, #payment2_is_store_specific_flat_rate_deposit_delayed_payment2, ' +
						' #ref_payment2_is_store_specific_flat_rate_deposit_delayed1, #ref_payment2_is_store_specific_flat_rate_deposit_delayed2').off('click');

					set_user_pref('TC_DELAYED_PAYMENT_AGREE', 1, user_id);

				},
				'Decline': function () {

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
	var bagFeeOrg = Number($('#OEH_subtotal_bag_fee_org').html());
	var bagFee = Number($('#OEH_subtotal_bag_fee').html());

	if (bagFeeOrg == 0 && bagFee == 0)
	{
		$('#BagFeeRow').hide();
	}
	else
	{
		$('#BagFeeRow').show();
	}

	var bagFeeTaxOrg = Number($('#OEH_bag_fee_tax_subtotal_org').html());
	var bagFeeTax = Number($('#OEH_bag_fee_tax_subtotal').html());

	if (bagFeeTaxOrg == 0 && bagFeeTax == 0)
	{
		$('#BagFeeTaxRow').hide();
	}
	else
	{
		$('#BagFeeTaxRow').show();
	}

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

	var deliveryFeeOrg = Number($('#OEH_subtotal_delivery_fee_org').html());
	var deliveryFee = Number($('#OEH_subtotal_delivery_fee').html());

	if (deliveryFeeOrg == 0 && deliveryFee == 0)
	{
		$('#DeliveryFeeRow').hide();
	}
	else
	{
		$('#DeliveryFeeRow').show();
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

	var miscMealCustomizationFeeOrg = Number($('#OEH_subtotal_meal_customization_fee').html());
	var miscMealCustomizationFee = Number($('#OEH_subtotal_meal_customization_fee_org').html());

	if (miscMealCustomizationFeeOrg == 0 && miscMealCustomizationFee == 0)
	{
		$('#MealCustomizationFeeRow').hide();
	}
	else
	{
		$('#MealCustomizationFeeRow').show();
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

	var referralRewardDiscountOrg = Number($('#OEH_referral_reward_order_discount_orig').html());
	var referralRewardDiscount = Number($('#OEH_referral_reward_order_discount').html());

	if (referralRewardDiscountOrg == 0 && referralRewardDiscount == 0)
	{
		$('#referralRewardDiscountRow').hide();
	}
	else
	{
		$('#referralRewardDiscountRow').show();
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

	var membershipDiscountOrg = Number($('#OEH_membership_discount_org').html());
	var membershipDiscount = Number($('#OEH_membership_discount').html());

	if (membershipDiscountOrg == 0 && membershipDiscount == 0)
	{
		$('#membershipDiscountRow').hide();
	}
	else
	{
		$('#membershipDiscountRow').show();
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

	let deliveryTaxOrg = Number($('#OEH_delivery_tax_subtotal_org').html());
	let deliveryTax = Number($('#OEH_delivery_tax_subtotal').html());

	if (deliveryTaxOrg == 0 && deliveryTax == 0)
	{
		$('#DeliveryTaxRow').hide();
	}
	else
	{
		$('#DeliveryTaxRow').show();
	}

	let productsSubtotalOrg = Number($('#OEH_products_subtotal_org').html());
	let productsSubtotal = Number($('#OEH_products_subtotal').html());

	if (productsSubtotalOrg == 0 && productsSubtotal == 0)
	{
		$('#productSubtotalRow').hide();
	}
	else
	{
		$('#productSubtotalRow').show();
	}

	if (!$('#add_ltd_round_up').is(':checked'))
	{
		$('#LTDRoundUpRow').hide();
	}
	else
	{
		$('#LTDRoundUpRow').show();
	}
}

function ShowAllLineItems()
{
	$('#ServiceFeeRow').show();
	$('#DeliveryFeeRow').show();
	$('#ServiceTaxRow').show();
	$('#miscFoodRow').show();
	$('#miscNonFoodRow').show();
	$('#directDiscountRow').show();
	$('#couponDiscountRow').show();
	$('#foodTaxRow').show();
	$('#nonFoodTaxRow').show();
	$('#productSubtotalRow').show();
	$('#platePointsDiscountRow').show();
	$('#referralRewardDiscountRow').show();
	$('#preferredUserDiscountRow').show();
	$('#sessionDiscountRow').show();
	$('#LTDRoundUpRow').show();
	$('#MealCustomizationFeeRow').show();
	$('#BagFeeRow').show();
}

function sort_by_price(a, b)
{
	if (a.price == b.price)
	{
		return 0;
	}

	return (a.price < b.price) ? 1 : -1;
}

function drawChangeList()
{
	var html = getChangeListHTML();
	$("#changelist").html(html);

}

function getChangeListHTML()
{

	let htmlStr = "";
	let first = true;

	for (let item in changeList.stdMenuItems)
	{
		if (first)
		{
			htmlStr += "<h3>Standard Items <span id='changed-item-count'> - " + localStorage.getItem('core-item-count') + " total</span></h3><ul style='margin: 4px;'>";
			first = false;
		}

		htmlStr += "<li>";

		if (changeList.stdMenuItems.hasOwnProperty(item))
		{
			let title = $("#qty_" + item).data("item_title");

			let pricing_type = translateServingSize($("#qty_" + item).data("servings"));

			pricing_type = "(" + pricing_type + ")";

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

	let indexedItems = [];
	for (let item in changeList.FTMenuItems)
	{
		if (changeList.FTMenuItems.hasOwnProperty(item))
		{
			var index = $("#qty_" + item).data("index");
			indexedItems[index] = item;

		}
	}

	for (let key in indexedItems)
	{
		let item = indexedItems[key];
		if (first)
		{
			htmlStr += "<h3>Sides <span id='changed-sides-count'> - " + localStorage.getItem('side-item-count') + " total</span></h3><ul style='margin: 4px;'>";
			first = false;
		}

		htmlStr += "<li>";

		if (changeList.FTMenuItems.hasOwnProperty(item))
		{
			let title = $("#qty_" + item).data("item_title");

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
	let hadMiscCostChanges = false;
	for (let item in changeList.miscCosts)
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

	if (!first)
	{
		htmlStr += "</ul>";
	}

	first = true;
	for (let item in changeList.sideBundleItem)
	{
		if (first)
		{
			htmlStr += "<h3>Sides Bundle Item List</h3><ul style='margin: 4px;'>";
			first = false;
		}

		htmlStr += "<li>";

		if (changeList.sideBundleItem.hasOwnProperty(item))
		{
			let title = $("#sbi_" + item).data("item_title");

			if (changeList.sideBundleItem[item] > 0)
			{
				htmlStr += "Added " + changeList.sideBundleItem[item] + " <i>" + title + "</i>";
			}
			else
			{
				htmlStr += "Removed " + changeList.sideBundleItem[item] * -1 + " <i>" + title + "</i>";
			}
		}

		htmlStr += "</li>";

	}

	if (!first)
	{
		htmlStr += "</ul>";
	}

	first = true;
	for (let item in changeList.miscDescs)
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
	for (let item in changeList.bundleItems)
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

			let title = $("#bnd_" + item).data("item_title");
			let pricing_type = "(" + $("#bnd_" + item).data("servings") + " Serving)";

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
	for (let item in changeList.discounts)
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
	for (let item in changeList.reporting)
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

			if (item == 'ltd_roundup')
			{
				if (changeList.reporting[item]["newVal"] == false)
				{
					htmlStr += getHumanReadableName(item) + ' was removed from the order.'
				}
				else
				{
					htmlStr += getHumanReadableName(item) + ' was added to the order.'
				}
			}

			if (item == 'ltd_roundup_value')
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

		if (changeList.coupon.change_type == 'added')
		{
			htmlStr += "Added coupon code: " + changeList.coupon.code + "<br />";
		}
		else if (changeList.coupon.change_type == 'removed')
		{
			htmlStr += "Removed coupon code: " + changeList.coupon.code + "<br />";
		}
		else if (changeList.coupon.change_type == 'added')
		{
			htmlStr += "Change coupon code to: " + changeList.coupon.code + "<br />";
		}
	}

	if (changeList.bagFee['Bag Count'])
	{
		if (first)
		{
			htmlStr += "<h3>Bag Fee</h3>";
			first = false;
		}

		htmlStr += "Updated Bag Count: Was " + changeList.bagFee['Bag Count']['orgVal'] + " bags. Updated to " + changeList.bagFee['Bag Count']['newVal'] + " bags<br />";
	}

	if (changeList.mealCustomizationFee['Meal Customization Fee'] != null)
	{
		if (first)
		{
			htmlStr += "<h3>Meal Customization Fee</h3>";
			first = false;
		}

		htmlStr += "Meal Customization Fee: Was $" + changeList.mealCustomizationFee['Meal Customization Fee']['orgVal'] + ". Updated to $" + changeList.mealCustomizationFee['Meal Customization Fee']['newVal'] + "<br />";
	}

	if (typeof changeList.mealCustomizationFee['Meal Customization Selection'] != 'undefined' && typeof changeList.mealCustomizationFee['Meal Customization Selection']['newVal'] != 'undefined' && changeList.mealCustomizationFee['Meal Customization Selection']['newVal'] != null)
	{
		if (first)
		{
			htmlStr += "<h3>Meal Customization Selection</h3>";
			first = false;
		}

		let previous = changeList.mealCustomizationFee['Meal Customization Selection']['orgVal'] == 1 ? " not selected" : " selected";
		let newVal = changeList.mealCustomizationFee['Meal Customization Selection']['newVal'] == "on" ? " selected" : " not selected";


		htmlStr += "Meal Customization was" + previous + ". Updated to " + newVal+ ".<br />";
	}



	if (changeList.mealCustomizationFee['Meal Customization'] != null)
	{
		if (first)
		{
			htmlStr += "<h3>Meal Customization</h3>";
			first = false;
		}

		for (let key in changeList.mealCustomizationFee['Meal Customization'])
		{
			if(typeof changeList.mealCustomizationFee['Meal Customization'][key] !== 'undefined' && typeof changeList.mealCustomizationFee['Meal Customization'][key]['name'] !== 'undefined')
			{
				if(changeList.mealCustomizationFee['Meal Customization'][key]['newVal'].toUpperCase() == 'OPTED IN')
				{
					htmlStr += changeList.mealCustomizationFee['Meal Customization'][key]['newVal'] + " to "+changeList.mealCustomizationFee['Meal Customization'][key]['name'] +"<br />";
				}
				else
				{
					htmlStr += changeList.mealCustomizationFee['Meal Customization'][key]['newVal'] + " of "+changeList.mealCustomizationFee['Meal Customization'][key]['name'] +"<br />";
				}
			}
		}
	}

	if (paymentChanges || $('#use_store_credit').is(':checked'))
	{
		htmlStr += "<h3>Payments</h3>";

		let payment1Type = $('#payment1_type').val();

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

				let ccRefundTotal = 0;
				$("[id^='Cr_RT_']").each(function () {
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
			let payment2Type = $('#payment2_type').val();
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

function translateServingSize(servings)
{
	var result = '';
	switch (servings)
	{
		case 6:
			result = 'Large - ' + servings + '';
			break;
		case 3:
		case 4:
			result = 'Medium - ' + servings + '';
			break;
		case 2:
			result = 'Small - ' + servings + '';
			break;
		default:
			result = servings;
	}

	return result;

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
		case "subtotal_delivery_fee":
			return "Delivery Fee";
		case 'misc_food_subtotal_desc':
			return 'Misc Food Description';
		case 'misc_nonfood_subtotal_desc':
			return 'Misc Non-food Description';
		case 'service_fee_description':
			return 'Service Fee Description';
		case 'session_discount':
			return 'Session Discount';
		case 'fundraiser':
			return 'Fundraiser';
		case 'fundraiser_value':
			return 'Fundraiser Value';
		case 'ltd_roundup_value':
			return 'Dream Dinners Foundation Round Up Value';
		case 'ltd_roundup':
			return 'Dream Dinners Foundation Round Up';
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
	else if (isFundraiser)
	{
		htmlStr += "<h3>Fundraiser Order</h3>";
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
	$("[id^='qty_']").each(function () {
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

	$("[data-dd_type='item_field']").each(function () {
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

				var desc = null;
				if (descIDStr != "")
				{
					desc = $("#" + descIDStr).val();
				}

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
	$("[id^='bnd_']").each(function () {

		if ($(this).is(":checked"))
		{
			bundleItems++;
		}
	});

	if (bundleItems > 0)
	{
		htmlStr += bundleItems + " Intro Items Selected.<br />"
	}

	if (isDreamTaste || isFundraiser)
	{
		var bundleItems = 0;
		$("[id^='bnd_']").each(function () {

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

	$("#plate_points_discount, #direct_order_discount").each(function () {

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
				$("[id^='Cr_RT_']").each(function () {
					ccRefundTotal += parseFloat($(this).val());
				});

				if (ccRefundTotal > 0)
				{
					htmlStr += "Refunding to customer credit card(s).<br />";
				}
			}
		}

		if ((payment1Type == 'GIFT_CERT' || payment1Type == 'GIFT_CARD') && $('#payment1_type').val() != "")
		{
			var payment2Type = $('#payment2_type').val();
			if (payment2Type && payment2Type != "")
			{
				htmlStr += "Payment of type " + payment2Type + " selected.<br />";
			}
		}

		if (changeList.bagFee['Bag Count'])
		{
			htmlStr += "Bag Count set to " + changeList.bagFee['Bag Count']['newVal'] + " bags<br />";
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
	reportingChanges = false;
	feeChanges = false;

	discountOrPaymentChanges = false;
	let showSaveButton = false;

	changeList = {};
	changeList.stdMenuItems = {};
	changeList.FTMenuItems = {};
	changeList.miscCosts = {};
	changeList.miscDescs = {};
	changeList.bundleItems = {};
	changeList.order_type = "";
	changeList.discounts = {};
	changeList.reporting = {};
	changeList.delivery = {};
	changeList.bagFee = {};
	changeList.mealCustomizationFee = {'Meal Customization':null,'Meal Customization Fee':null};


	changeList.sideBundleItem = {};

	// Items
	$("[id^='qty_']").each(function () {
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

	// bundle items
	$("[name^='sbi_']").each(function () {
		if ($(this).val() != $(this).data('lastqty'))
		{
			changeList.sideBundleItem[$(this).attr('id').split("_")[1]] = $(this).val() - $(this).data('lastqty');
			$(this).addClass('unsaved_data');
			itemChanges = true;
		}
		else
		{
			$(this).removeClass('unsaved_data');
		}
	});

	// Misc Costs
	$("[data-dd_type='item_field']").each(function () {
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

	if (isDreamTaste || isFundraiser)
	{

		$("[id^='bnd_']").each(function () {

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

		$("[id^='bnd_']").each(function () {

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

	// bag fees
	if ($("#total_bag_count").length)
	{
		if ($("#total_bag_count").val() != $("#total_bag_count").data("org_value"))
		{
			changeList.bagFee['Bag Count'] = {
				newVal: $("#total_bag_count").val(),
				orgVal: $("#total_bag_count").data("org_value")
			}
			feeChanges = true;
			$("#total_bag_count").addClass('unsaved_data');
		}
		else
		{
			$("#total_bag_count").removeClass('unsaved_data');
		}

	}

	// meal customization fees
	if ($("#subtotal_meal_customization_fee").length)
	{
		if($("#subtotal_meal_customization_fee").val() == ''){
			$("#subtotal_meal_customization_fee").val('0.00');
		}
		if ($("#subtotal_meal_customization_fee").val() != $("#subtotal_meal_customization_fee").data("org_value"))
		{
			changeList.mealCustomizationFee['Meal Customization Fee'] = {
				newVal: $("#subtotal_meal_customization_fee").val(),
				orgVal: $("#subtotal_meal_customization_fee").data("org_value")
			}
			feeChanges = true;
			$("#subtotal_meal_customization_fee").addClass('unsaved_data');
		}
		else
		{
			$("#subtotal_meal_customization_fee").removeClass('unsaved_data');
		}
	}

	let customizationSelection = $("#opted_to_customize_recipes").is(":checked")  ? '1':'0';
	let origCustomizationSelection = (typeof $("#opted_to_customize_recipes").data("org_value") == 'undefined' || $("#opted_to_customize_recipes").data("org_value") == '' || $("#opted_to_customize_recipes").data("org_value") == '0') ? '0':'1';
	if( origCustomizationSelection != customizationSelection){
		changeList.mealCustomizationFee['Meal Customization Selection'] = {
			newVal: $("#opted_to_customize_recipes").val(),
			orgVal: $("#opted_to_customize_recipes").data("org_value")
		}
		feeChanges = true;
		$("#opted_to_customize_recipes").addClass('unsaved_data');
	}

	$("[data-dd_type='fee_field']").each(function () {
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

			feeChanges = true;
		}
		else
		{
			$(this).removeClass('unsaved_data');
		}
	});

	$("[data-dd_type='customization_fee_toggle']").each(function () {
		var curVal = $(this).is(":checked") ? 'Opted in' : 'Opted out';
		var orgVal = $(this).data('org_value');
		var name = $(this).data('name');


		if (curVal.toLowerCase() != orgVal.toLowerCase() )
		{
			//$(this).addClass('unsaved_data');
			if( changeList.mealCustomizationFee['Meal Customization'] == null){
				changeList.mealCustomizationFee['Meal Customization'] = [];
			}


			changeList.mealCustomizationFee['Meal Customization'][this.id] = {
				name : name,
				newVal: curVal,
				orgVal: orgVal
			}

			//feeChanges = true;
		}
		else
		{
			if( changeList.mealCustomizationFee['Meal Customization'] != null)
			{
				changeList.mealCustomizationFee['Meal Customization'][this.id] = '';
			}
			$(this).removeClass('unsaved_data');
		}
	});

	$("[data-dd_type='customization_fee_detail']").each(function () {
		let curVal = $.trim($(this).val());
		let orgVal = $(this).data('org_value');
		let name = $(this).data('name');

		let key = this.id + 'detail';

		if (curVal.toLowerCase() != orgVal.toLowerCase() )
		{
			if( changeList.mealCustomizationFee['Meal Customization'] == null){
				changeList.mealCustomizationFee['Meal Customization'] = [];
			}


			changeList.mealCustomizationFee['Meal Customization'][key] = {
				name : name,
				newVal: curVal,
				orgVal: orgVal
			}
		}
		else
		{
			if( changeList.mealCustomizationFee['Meal Customization'] != null)
			{
				changeList.mealCustomizationFee['Meal Customization'][key] = '';
			}
		}
	});


	if (feeChanges)
	{
		$("#fees_tab_li").addClass("unsaved_data_on_tab");
		showSaveButton = true;
	}
	else
	{
		$("#fees_tab_li").removeClass("unsaved_data_on_tab");
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

	// Reporting Fundraising
	$("#fundraiser_id").each(function () {
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
			changeList.reporting['fundraiser'] = {
				newVal: $(this).val(),
				orgVal: $(this).data('org_value'),
				title: $(this).find(':selected').text()
			};

			$(this).addClass('unsaved_data');
			discountOrPaymentChanges = true;
			reportingChanges = true;
		}
		else
		{
			$(this).removeClass('unsaved_data');
		}
	});

	$("#fundraiser_value").each(function () {
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
			changeList.reporting['fundraiser_value'] = {
				newVal: curVal,
				orgVal: orgVal,
				diff: curVal - orgVal
			};

			$(this).addClass('unsaved_data');
			discountOrPaymentChanges = true;
			reportingChanges = true;
		}
		else
		{
			$(this).removeClass('unsaved_data');
		}
	});

	// Reporting, LTD Roundup
	$("#add_ltd_round_up").each(function () {
		var curVal = false;

		if ($(this).is(':checked'))
		{
			curVal = true;
		}

		var orgVal = $(this).data('org_value');

		if (curVal != orgVal)
		{
			changeList.reporting['ltd_roundup'] = {
				newVal: curVal,
				orgVal: orgVal
			};

			$(this).addClass('unsaved_data');
			discountOrPaymentChanges = true;
			reportingChanges = true;
		}
		else
		{
			$(this).removeClass('unsaved_data');
		}
	});

	$("#ltd_round_up_select").each(function () {
		if (!$("#add_ltd_round_up").is(':checked'))
		{
			return;
		}

		var curVal = $(this).val();
		var orgVal = $(this).data('org_value');

		if ($(this).data("number") == true)
		{
			// reduce to similar formatting for numeric vs textual equivalency
			curVal = parseFloat(curVal).toFixed(2);
			orgVal = parseFloat(orgVal).toFixed(2);

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
			changeList.reporting['ltd_roundup_value'] = {
				newVal: $(this).val(),
				orgVal: $(this).data('org_value'),
				diff: curVal - orgVal
			};

			$(this).addClass('unsaved_data');
			discountOrPaymentChanges = true;
			reportingChanges = true;
		}
		else
		{
			$(this).removeClass('unsaved_data');
		}
	});

	// Discounts
	$("#plate_points_discount, #direct_order_discount").each(function () {
		var curVal = $(this).val();
		var orgVal = $(this).data('org_value');

		// reduce to similar formatting for numeric vs textual equivalency
		curVal = parseFloat(curVal);
		orgVal = parseFloat(orgVal);
		if (isNaN(curVal))
		{
			curVal = Number(0);
		}
		if (isNaN(orgVal))
		{
			orgVal = Number(0);
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
	var curCouponCode = $("#coupon_code").val();

	if (orgCouponVal != curCouponVal)
	{
		discountOrPaymentChanges = true;

		if (orgCouponVal == "")
		{
			//added
			changeList.coupon = {
				newVal: curCouponVal,
				orgVal: orgCouponVal,
				code: curCouponCode,
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
				code: curCouponCode,
				change_type: "removed"
			};
		}
		else
		{
			//added
			changeList.coupon = {
				newVal: curCouponVal,
				orgVal: orgCouponVal,
				code: curCouponCode,
				change_type: "changed"
			};
		}

	}

	if (deliveryChanges)
	{
		$("#delivery_tab_li").addClass("unsaved_data_on_tab");
		showSaveButton = true;
	}
	else
	{
		$("#delivery_tab_li").removeClass("unsaved_data_on_tab");
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

	if (orderState == 'ACTIVE' || discountEligable.limited_access)
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

	if (needRefferalRewardMaxDiscountChangeWarning)
	{
		htmlStr += "<div style='color:red;'>Warning: The amount discountable by Referral Rewards has changed. You may wish to review and adjust the Referral Rewards discount.</div>";

	}


	for (var item in changeList.stdMenuItems)
	{
		if (changeList.stdMenuItems.hasOwnProperty(item))
		{

			if (changeList.stdMenuItems[item] > 0)
			{
				addedStd += (changeList.stdMenuItems[item] * 1);
			}
			else
			{
				removedStd += (changeList.stdMenuItems[item] * -1);
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
				addedFT += (changeList.FTMenuItems[item] * 1);
			}
			else
			{
				removedFT += (changeList.FTMenuItems[item] * -1);
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

	var addedSBI = 0;
	var removedSBI = 0;

	for (var item in changeList.sideBundleItem)
	{
		if (changeList.sideBundleItem.hasOwnProperty(item))
		{
			if (changeList.sideBundleItem[item] > 0)
			{
				addedSBI += (changeList.sideBundleItem[item] * 1);
			}
			else
			{
				removedSBI += (changeList.sideBundleItem[item] * -1);
			}
		}
	}

	if (addedSBI)
	{
		htmlStr += addedSBI + " Sides &amp; Sweets bundle items were added.<br />"
	}
	if (removedSBI)
	{
		htmlStr += removedSBI + " Sides &amp; Sweets bundle items were removed.<br />"
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
			else if (isFundraiser)
			{
				htmlStr += "<h3>Fundraiser Event Items List</h3>";
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
				addedBI += (changeList.bundleItems[item] * 1);
			}
			else
			{
				removedBI += (changeList.bundleItems[item] * -1);
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

	if (changeList.bagFee['Bag Count'])
	{
		if (first)
		{
			htmlStr += "<h3>Bag Fee</h3>";
			first = false;
		}

		htmlStr += "Updated Bag Count: Was " + changeList.bagFee['Bag Count']['orgVal'] + " bags. Updated to " + changeList.bagFee['Bag Count']['newVal'] + " bags<br />";
	}

	if (typeof changeList.mealCustomizationFee['Meal Customization Selection'] != 'undefined' && changeList.mealCustomizationFee['Meal Customization Selection']['newVal'] != null)
	{
		if (first)
		{
			htmlStr += "<h3>Meal Customization Selection</h3>";
			first = false;
		}

		let previous = changeList.mealCustomizationFee['Meal Customization Selection']['orgVal'] == 1 ? " not selected" : " selected";
		let newVal = changeList.mealCustomizationFee['Meal Customization Selection']['newVal'] == "on" ? " selected" : " not selected";


		htmlStr += "Meal Customization was" + previous + ". Updated to " + newVal+ ".<br />";

	}
	if (changeList.mealCustomizationFee['Meal Customization Fee'] != null)
	{
		if (first)
		{
			htmlStr += "<h3>Meal Customization Fee</h3>";
			first = false;
		}

		htmlStr += "Updated Meal Customization Fee: Was $" + changeList.mealCustomizationFee['Meal Customization Fee']['orgVal'] + ". Updated to $" + changeList.mealCustomizationFee['Meal Customization Fee']['newVal'] + "<br />";
	}
	if (changeList.mealCustomizationFee['Meal Customization'] != null)
	{
		if (first)
		{
			htmlStr += "<h3>Meal Customization</h3>";
			first = false;
		}

		for (let key in changeList.mealCustomizationFee['Meal Customization'])
		{
			if(typeof changeList.mealCustomizationFee['Meal Customization'][key] !== 'undefined' && typeof changeList.mealCustomizationFee['Meal Customization'][key]['name'] !== 'undefined')
			{
				if(changeList.mealCustomizationFee['Meal Customization'][key]['newVal'].toUpperCase() == 'OPTED IN')
				{
					htmlStr += changeList.mealCustomizationFee['Meal Customization'][key]['newVal'] + " to "+changeList.mealCustomizationFee['Meal Customization'][key]['name'] +"<br />";
				}
				else
				{
					htmlStr += changeList.mealCustomizationFee['Meal Customization'][key]['newVal'] + " of "+changeList.mealCustomizationFee['Meal Customization'][key]['name'] +"<br />";
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
		url: 'ddproc.php',
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
		success: function (json) {
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
		error: function (objAJAXRequest, strError) {
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
			confirm: function () {

				if (supports_transparent_redirect && $("#payment1_type").val() == 'CC')
				{
					intenseLogging("submitPage() confirmed");

					if (!discountEligable.limited_access)
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
			if (numBundItems < intro_servings_required)
			{

				dd_message({
					title: 'Error',
					message: 'Please select at least ' + intro_servings_required + ' servings of Meal Prep Starter Pack items.'
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
	qty = Number(qty);
	stationNeedsAttention = false;

	if (qty == 0)
	{
		$('#desc' + entree_id).slideUp();
		$("#sidestation_help_" + entree_id).css('color', "#b00").html("You must set the Bundle quantity to greater than zero.");
		$('.bundle-subitem-qty[data-parent_item="' + entree_id + '"]').val(0);
		return;
	}
	else
	{
		$('#desc' + entree_id).slideDown();
	}

	let requiredItemCount = qty * sideStationBundleInfo[entree_id].number_items_required;
	$("#required_items_station_" + entree_id).text(requiredItemCount);

	let numSelected = 0;

	$.each(sideStationBundleInfo[entree_id].bundle, function (mid, menu_item) {

		let itemQty = $('#sbi_' + mid);

		if (itemQty && itemQty.val() != "")
		{
			if (isNaN(itemQty.val()) == true || itemQty.val() - parseInt(itemQty.val()) != 0)
			{
				itemQty.val(0);
			}

			// if it is a fixed quantity, the input is read only so update the value times the master quantity
			if (menu_item.fixed_quantity !== '0')
			{
				itemQty.val(qty * Number(menu_item.fixed_quantity));
			}

			if (itemQty.val() > 0)
			{
				itemQty.val(parseInt(itemQty.val()));
				numSelected += (itemQty.val() * 1);
			}

			let inv_qty = entreeIDToInventoryMap[menu_item.entree_id].remaining;
			inv_qty -= (itemQty.val() * menu_item.servings_per_item);

			$('[data-entree_inventory_remaining="' + menu_item.entree_id + '"]').text(inv_qty);

			entreeIDToInventoryMap[menu_item.entree_id].remaining = inv_qty;
		}

	});

	$.each(sideStationBundleInfo[entree_id].bundle_groups, function (bid, group_info) {
		$('.group-select-num[data-group_id="' + group_info.id + '"]').text(parseInt(group_info.number_items_required) * parseInt(qty));
	});

	$("#selected_items_station_" + entree_id).html(numSelected);

	let stationHelpElem = $("#sidestation_help_" + entree_id);
	let help_message = "";
	if (numSelected == 0)
	{
		help_message = "Please select " + requiredItemCount + " items.";
		stationHelpElem.css('color', "#b00");
		stationNeedsAttention = true;
	}
	else if (numSelected > requiredItemCount)
	{
		help_message = "You have selected " + (numSelected - requiredItemCount) + " items more than the required amount. Please remove " + (numSelected - requiredItemCount) + " items.";
		stationHelpElem.css('color', "#b00");
		stationNeedsAttention = true;
	}
	else if (numSelected < requiredItemCount)
	{
		help_message = "You have selected " + (requiredItemCount - numSelected) + " items less than the required amount. Please add " + (requiredItemCount - numSelected) + " items.";
		stationHelpElem.css('color', "#b00");
		stationNeedsAttention = true;
	}
	else
	{
		help_message = "You have selected the correct number of sidestation items. You may continue with item selection or checkout.";
		stationHelpElem.css('color', "#0b0");
		stationNeedsAttention = false;
	}

	stationHelpElem.html(help_message);
}

function handleMealCustomizationFeeInput()
{
	$('#manual_customization_fee').val('true');
	calculateTotal();
}


function handleBagCountInput()
{
	bagCountLockedToField = true;
	calculateTotal();
}

function handleBagWaiverInput()
{
	if (!$("#opted_to_bring_bags").is(":checked"))
	{
		bagCountLockedToField = false;
	}
	calculateTotal();
}

function handleMealCustomizationWaiverInput()
{
	if ($("#opted_to_customize_recipes").is(":checked"))
	{
		mealCustomizationFeeFieldLocked = true;

		$('.icon-customize').hide();

	}else{
		let mealCustomizationFeeTotal = $("#subtotal_meal_customization_fee").data('org_value');
		$("#subtotal_meal_customization_fee").prop("disabled", false);
		$("#subtotal_meal_customization_fee").val(formatAsMoney(mealCustomizationFeeTotal));

		$("#customization-row-container").show();
		mealCustomizationFeeFieldLocked = false;
		$('.icon-customize').show();

		if (true)
		{
			feeChanges = true;
			$("#submit_button").attr("disabled", false);
			$("#finalize_msg").show();
		}
	}
	calculateTotal();
}

function calculateMealCustomizationFee(customizableMealQty){
	let priceConfig = meal_customization_cost;

	if(typeof priceConfig === 'undefined' || priceConfig.length == 0){
		return 0;
	}

	let fee = 0;
	for (var key in priceConfig) {

		switch(priceConfig[key].operator) {
			case 'EQUAL_OR_LESS':
				if(customizableMealQty <= parseInt(priceConfig[key].value)){
					fee = priceConfig[key].cost;
				}
				break;
			case 'BETWEEN_INCLUSIVE':
				let limit = priceConfig[key].value.split('-');
				let limitLow = parseInt(limit[0]);
				let limitHigh = parseInt(limit[1]);
				if(customizableMealQty >= limitLow && customizableMealQty <= limitHigh ){
					fee = priceConfig[key].cost;
				}
				break;
			case 'GREATER':
				if(customizableMealQty > parseInt(priceConfig[key].value)){
					fee = priceConfig[key].cost;
				}
				break;
			default:
			// code block
		}
	}

	return fee;

}

function getNumberBagsRequiredFromItems(entreeCount, sidesCount)
{

	let entreesPerBag = 4;
	//	let sidesPerBag = 6;

	let bagsRequired = Math.floor(entreeCount / entreesPerBag);
	let entreeRemainder = 0;
	if (entreeCount % entreesPerBag > 0)
	{
		entreeRemainder = entreesPerBag - (entreeCount % entreesPerBag);
	}

	if (entreeRemainder > 0)
	{
		bagsRequired++;
	}
	/*
		// simple for now since we are assuming 2 sides fit the space of 1 entree. this could change

		let sidesCapacityOfRemainder = entreeRemainder * 2;
		sidesCount -= sidesCapacityOfRemainder;

		if (sidesCount <= 0)
		{
			return bagsRequired;
		}

		let sidesBagsRequired = Math.floor(sidesCount / sidesPerBag);
		let sidesRemainder = 0;
		if (sidesCount % sidesPerBag > 0)
		{
			sidesRemainder = sidesPerBag - (sidesCount % sidesPerBag);
		}

		if (sidesRemainder > 0)
		{
			sidesBagsRequired++;
		}
	*/

	//return bagsRequired + sidesBagsRequired;

	return bagsRequired;
}

// this massive function is called anytime something that affects costs or payments is altered
function calculateTotal()
{
	let total = 0;
	let entrees = 0;
	let servings = 0;
	let core_servings = 0;
	let halfQty = 0;
	let wholeQty = 0;
	let customizableMealQty = 0;
	let introQty = 0;
	let sideDishQty = 0;
	let sideDishSubTotal = 0;
	let bundlesSubTotal = 0;
	let productsSubTotal = 0;
	let coreItemsSubtotal = 0;
	let bundlesQty = 0;
	let discounted_total = 0;
	let creditContribution = 0;
	let main_items_count = 0;
	let bundleIsSelected = false;
	let PUDExcludedItemsTotal = 0;
	let PUDExcludedFullItemsCount = 0;
	let PUDExcludedHalfItemsCount = 0;
	let creditConsumed = 0;
	let hasServiceFeeWithMFYCoupon = false;
	let hasDeliveryFeeWithCoupon = false;

	let sortedCoreItemList = [];

	let itemsSelected = [];

	if (hasBundle)
	{
		if ($('#selectedBundle').is(':checked'))
		{
			bundleIsSelected = true;
		}
	}

	for (let x in entreeIDToInventoryMap)
	{
		entreeIDToInventoryMap[x].remaining = entreeIDToInventoryMap[x].org_remaining;
		$('[data-entree_inventory_remaining="' + x + '"]').text(entreeIDToInventoryMap[x].org_remaining);

	}

	// ---------------------------------------------------------- Items
	// loop through all the quantity input boxes and gather up the totals

	// var count = 0;
	$("[id^='qty_']").each(function () {
		//  count++;

		if ($(this).val() != "")
		{
			if (isNaN($(this).val()) == true || $(this).val() - parseInt($(this).val(), 10) != 0)
			{
				$(this).val(0);
			}

			let itemQty = Number($(this).val());
			let itemId = this.id.split("_")[1];

			if (isFactoringCredit)
			{
				let orgAmount = $(this).data('org_value');
				if ($(this).data('dd_type') == 'std')
				{
					if (orgAmount < itemQty)
					{
						diff = itemQty - orgAmount;
						creditContribution += (diff * $(this).data('price'));
					}
					else if (itemQty < orgAmount)
					{
						diff = orgAmount - itemQty;
						creditContribution -= (diff * $(this).data('price'));
					}
				}
				else // addon
				{
					let orgAmount = itemQty.getAttribute('data-org_value');
					if (orgAmount < itemQty)
					{
						diff = itemQty - orgAmount;
						creditConsumed += (diff * $(this).data('price'));
					}
					else if (itemQty < orgAmount)
					{
						diff = orgAmount - itemQty;
						creditContribution -= (diff * $(this).data('price'));
					}
				}
			}

			if ((isNaN(itemQty) == false))
			{
				let entree_id = $(this).data('entreeid');
				let inv_qty = entreeIDToInventoryMap[entree_id].remaining;
				let temp_servings = Number($(this).data('servings'));
				inv_qty = inv_qty - (itemQty * temp_servings);
				$('[data-entree_inventory_remaining="' + entree_id + '"]').text(inv_qty);
				entreeIDToInventoryMap[entree_id].remaining = inv_qty;

				if ($(this).data('dd_type') != 'cts' && ($(this).data('menu_class') != 'Extended Fast Lane' || PlatePointsRulesVersion == 1))
				{

					if (itemQty > 0)
					{
						let obj = {
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
					entrees += itemQty * $(this).data('item_count_per_item');
					servings += itemQty * $(this).data('servings');

					if ($(this).data('menu_class') != 'Extended Fast Lane' || PlatePointsRulesVersion == 1)
					{
						core_servings += itemQty * $(this).data('servings');
						coreItemsSubtotal += itemQty * $(this).data('price');
					}

					if ($(this).data('pricing_type') == 'FULL')
					{
						wholeQty += itemQty * $(this).data('item_count_per_item');
					}
					else
					{
						halfQty += itemQty * $(this).data('item_count_per_item');
					}

					if($(this).data('item_is_customizable') == '1'){
						customizableMealQty += itemQty * $(this).data('item_count_per_item');
					}
				}
				else
				{
					sideDishQty += itemQty;
					sideDishSubTotal += itemQty * $(this).data('price');
				}

				if ($(this).data('is_bundle') == true)
				{
					bundlesSubTotal += itemQty * $(this).data('price');
					bundlesQty += itemQty;
				}

				itemsSelected[itemId] = itemQty;

				$('#sbi_inv_' + entree_id).html(inv_qty);
			}

			if ($(this).data('is_bundle') == true)
			{
				updateSideStationStatus(itemId, itemQty);
			}
		}
	});


	if (hasBundle)
	{
		// bundle support
		let selectedBundleItemCount = 0;
		let selectedBundleServingsCount = 0;

		if (originallyHadBundle)
		{
			if (bundleIsSelected)
			{
				$("[id^='bnd_']").each(function () {
					if ($(this).data('org_value') == "1") //was initially chosen
					{
						if (!$(this).is(":checked"))
						{
							let entree_id = $(this).data('entreeid');
							let inv_qty = entreeIDToInventoryMap[entree_id].remaining;
							inv_qty = inv_qty + $(this).data('servings');
							$('[data-entree_inventory_remaining="' + entree_id + '"]').text(inv_qty);

							entreeIDToInventoryMap[entree_id].remaining = inv_qty;
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
							let entree_id = $(this).data('entreeid');
							let inv_qty = entreeIDToInventoryMap[entree_id].remaining;
							inv_qty = inv_qty - $(this).data('servings');
							$('[data-entree_inventory_remaining="' + entree_id + '"]').text(inv_qty);

							entreeIDToInventoryMap[entree_id].remaining = inv_qty;
							selectedBundleItemCount++;
							selectedBundleServingsCount += $(this).data('servings');
						}
					}
				});
			}
			else
			{ //bundle is not selected

				$("[id^='bnd_']").each(function () {
					if ($(this).data('org_value') == "1") //was initially chosen
					{
						let entree_id = $(this).data('entreeid');
						let inv_qty = entreeIDToInventoryMap[entree_id].remaining;
						inv_qty = inv_qty + $(this).data('servings');
						$('[data-entree_inventory_remaining="' + entree_id + '"]').text(inv_qty);
						entreeIDToInventoryMap[entree_id].remaining = inv_qty;

					}
				});
			}

		}
		else
		{
			if (bundleIsSelected)
			{
				$("[id^='bnd_']").each(function () {
					if ($(this).is(":checked"))
					{
						let entree_id = $(this).data('entreeid');
						let inv_qty = entreeIDToInventoryMap[entree_id].remaining;
						inv_qty = inv_qty - $(this).data('servings');
						$('[data-entree_inventory_remaining="' + entree_id + '"]').text(inv_qty);
						entreeIDToInventoryMap[entree_id].remaining = inv_qty;
						selectedBundleItemCount++;
						selectedBundleServingsCount += $(this).data('servings');
					}
				});
			}
		}

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
		if (isDreamTaste || isFundraiser)
		{

			$("#OEH_number_core_servings").html(bundleInfo.number_servings_required);

			// bundle support
			let selectedWholeBundleItemCount = 0;
			let selectedHalfBundleItemCount = 0;
			let selectedBundleServingsCount = 0;

			total += Number(bundleInfo.price);

			$("[id^='bnd_']").each(function () {

				let tasteItemQty = $(this).val();
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

					let entree_id = $(this).data('entreeid');
					let inv_qty = entreeIDToInventoryMap[entree_id].remaining;
					inv_qty = inv_qty - (tasteItemQty * 3);
					$('[data-entree_inventory_remaining="' + entree_id + '"]').text(inv_qty);
					entreeIDToInventoryMap[entree_id].remaining = inv_qty;
				}
			});

			wholeQty += (selectedWholeBundleItemCount * 1);
			halfQty += (selectedHalfBundleItemCount * 1);

		}
	}

	let discountablePlatePointsAmount = 0;

	if ($("#selectedBundle").is(':checked'))
	{
		$("#max_plate_points_deduction").html(formatAsMoney(0));

		if (typeof coupon != 'undefined' && coupon.limit_to_mfy_fee == '1' && coupon.discount_method == 'FLAT')
		{
			let currentServiceFee = $("#subtotal_service_fee").val();

			if (currentServiceFee > 0)
			{
				hasServiceFeeWithMFYCoupon = true;
			}
		}

		if (typeof coupon != 'undefined' && coupon.limit_to_delivery_fee == '1' && coupon.discount_method == 'FLAT')
		{
			let currentDeliveryFee = $("#subtotal_delivery_fee").val();

			if (currentDeliveryFee > 0)
			{
				hasDeliveryFeeWithCoupon = true;
			}
		}

	}
	else
	{
		let serviceFee = Number(document.getElementById('subtotal_service_fee').value);
		discountablePlatePointsAmount = total + serviceFee.valueOf();

		if (typeof coupon != 'undefined' && coupon.limit_to_mfy_fee == '1' && coupon.discount_method == 'FLAT')
		{

			let currentServiceFee = $("#subtotal_service_fee").val();

			// adjust the discountablePlatePointsAmount and service to match coupon
			discountablePlatePointsAmount = formatAsMoney(discountablePlatePointsAmount - currentServiceFee);
			$("#OEH_subtotal_service_fee").val(currentServiceFee);

			hasServiceFeeWithMFYCoupon = true;
			if (discountablePlatePointsAmount < 0)
			{
				discountablePlatePointsAmount = 0;
			}

		}

		if (typeof coupon != 'undefined' && coupon.limit_to_delivery_fee == '1' && coupon.discount_method == 'FLAT')
		{
			let currentDeliveryFee = $("#subtotal_delivery_fee").val();
			hasDeliveryFeeWithCoupon = true;
		}

		needPlatePointsMaxDiscountChangeWarning = false;

		if (discountablePlatePointsAmount != lastMaxPPDiscountAmount && lastMaxPPDiscountAmount != -1)
		{
			needPlatePointsMaxDiscountChangeWarning = true;
		}

		lastMaxPPDiscountAmount = discountablePlatePointsAmount;

		if (couponlimitedToFT)
		{
			let tempCouponVal = Number(document.getElementById('couponValue').value);
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
	else if (!discountEligable.limited_access)
	{
		$("#plate_points_discount").removeAttr("disabled");
		$("#plate_points_discount").css({
			"background-color": "#fff",
			"color": "#000"
		});
	}


	let discountableReferralRewardAmount = total + Number(document.getElementById('subtotal_service_fee').value);

	if (typeof coupon != 'undefined' && coupon.limit_to_mfy_fee == '1' && coupon.discount_method == 'FLAT')
	{

		let currentServiceFee = $("#subtotal_service_fee").val();

		// adjust the discountablePlatePointsAmount and service to match coupon
		discountableReferralRewardAmount = formatAsMoney(discountableReferralRewardAmount - currentServiceFee);
		$("#OEH_subtotal_service_fee").val(currentServiceFee);

		hasServiceFeeWithMFYCoupon = true;
		if (discountableReferralRewardAmount < 0)
		{
			discountableReferralRewardAmount = 0;
		}

	}

	needRefferalRewardMaxDiscountChangeWarning = false;

	if (discountableReferralRewardAmount != lastMaxReferralRewardDiscountAmount && lastMaxReferralRewardDiscountAmount != -1)
	{
		needRefferalRewardMaxDiscountChangeWarning = true;
	}

	lastMaxReferralRewardDiscountAmount = discountableReferralRewardAmount;

	if (couponlimitedToFT)
	{
		let tempCouponVal = Number(document.getElementById('couponValue').value);
		discountableReferralRewardAmount -= tempCouponVal;
		if (discountableReferralRewardAmount < 0)
		{
			discountableReferralRewardAmount = 0;
		}

	}

	$("#referral_reward_discount").html(formatAsMoney(discountableReferralRewardAmount))


	if (discountableReferralRewardAmount <= 0)
	{
		$("#referral_reward_discount").attr("disabled", "disabled");
		$("#referral_reward_discount").css({
			"background-color": "#c0c0c0",
			"color": "#060606"
		});
		$("#rr_discountable_cost_msg").show();

	}
	else if (!discountEligable.limited_access)
	{
		$("#referral_reward_discount").removeAttr("disabled");
		$("#referral_reward_discount").css({
			"background-color": "#fff",
			"color": "#000"
		});
		$("#rr_discountable_cost_msg").hide();

	}

	let maxPPCredit = $("#plate_points_available").html() * 1;
	let maxPPDeduction = $("#max_plate_points_deduction").html() * 1;
	let curPPDiscount = $("#plate_points_discount").val() * 1;

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

	let maxRRCredit = $("#referral_reward_available").html() * 1;
	let maxRRDeduction = $("#max_referral_reward_deduction").html() * 1;
	let curRRDiscount = $("#referral_reward_discount").val() * 1;

	if (maxRRDeduction < curRRDiscount)
	{
		$("#referral_reward_discount").val(maxRRDeduction);
	}

	if (maxRRCredit > maxRRDeduction)
	{
		$('#tbody_max_plate_points_deduction').show();
	}
	else
	{
		$('#tbody_max_plate_points_deduction').hide();
	}

	discounted_total = total;

	let addonsSubtotal = 0;
	let addonsQty = 0;

	//var itemRows = document.getElementById('itemsTbl').rows;
	//for (i = 0; i < itemRows.length; i++)

	$("#itemsTbl tr").each(function () {

		if (this.id.indexOf('row_') != -1)
		{
			let itemNumber = this.id.substr(4);

			let qty_str = 'qna_' + itemNumber;
			let prc_str = 'prc_' + itemNumber;

			let addQty = $('#' + qty_str).val();
			let addonsQtyNumber = Number(addQty);
			let addPrc = $("#" + prc_str).html();
			let addPrcNumber = Number(addPrc);
			addonsSubtotal += (Number(addPrc) * addQty);
			addonsQty += addonsQtyNumber;

			/// var inv_qty =  Number(document.getElementById('inv_org_' + itemNumber).innerHTML);
			let inv_qty = entreeIDToInventoryMap[itemNumber].remaining;

			inv_qty = inv_qty - addQty;
			$('[data-entree_inventory_remaining="' + itemNumber + '"]').text(inv_qty);
			entreeIDToInventoryMap[itemNumber].remaining = inv_qty;

			if (isFactoringCredit)
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

	main_items_count = halfQty + wholeQty + sideDishQty;
	core_items_count = halfQty + wholeQty;

	let totalMealQuantity = Number(halfQty + wholeQty);

	let itemCountLabel = $('#OEH_item_count_label');

	let itemCountAdjustmentAmount = 0;
	let displayServingsAdjustment = 0;

	// coupon code free meal count contribution
	let couponTypeElem = $('#coupon_type');
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
		displayMealCount = introQty + wholeQty + sideDishQty + halfQty + itemCountAdjustmentAmount + addonsQty;
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

	if (typeof (order_minimum) !== 'undefined' && order_minimum.minimum_type == 'ITEM')
	{
		core_servings = core_items_count;
	}
	localStorage.setItem('core-item-count', core_items_count);
	localStorage.setItem('side-item-count', sideDishQty);

	if (PlatePointsRulesVersion > 1)
	{
		$('#OEH_number_core_servings').html(core_servings);
	}

	let premarkup_total = 0;

	let freeMealValue = 0;

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

	discounted_total = Math.round(discounted_total * 100) / 100;

	// --------------------------------------------------------------------- menu items subtotal
	$('#orderSubTotal').val(formatAsMoney(Math.round(total * 100) / 100));
	$('#OEH_menu_subtotal').html(formatAsMoney(discounted_total));
	$('#orderTotal').val(formatAsMoney(discounted_total));

	// ------------------------------------------------------ food subtotal

	let miscFoodSubtotal = Number(document.getElementById('misc_food_subtotal').value);

	// menu items total plus misc food cost
	let food_costs = (discounted_total * 1) + (miscFoodSubtotal * 1);
	$('#OEH_food_cost_subtotal').html(formatAsMoney((discounted_total * 1) + (miscFoodSubtotal * 1)));

	let newGrandTotal = discounted_total;
	let pre_discounts_grand_total = newGrandTotal;

	// -------------------------------DISCOUNTS-----------------------------------------

	curPPDiscount = 0;
	// ----------------------------------------------  PLATEPOINTS Discount
	if ($('#plate_points_discount').length)
	{
		curPPDiscount = $('#plate_points_discount').val();
		if (curPPDiscount * 1 > discountablePlatePointsAmount * 1)
		{
			curPPDiscount = discountablePlatePointsAmount;

			if (curPPDiscount == 0)
			{
				$('#plate_points_discount').val("");
			}
			else
			{
				$('#plate_points_discount').val(formatAsMoney(curPPDiscount));
			}
		}

		//$('#OEH_plate_points_order_discount').html(formatAsMoney(curPPDiscount));
		// This is now done in the taxesd section

		newGrandTotal = formatAsMoney(newGrandTotal - curPPDiscount);
	}

	let curReferralRewardDiscount = 0;
	// ----------------------------------------------  Referral Reward Discount
	if ($('#referral_reward_discount').length)
	{
		curReferralRewardDiscount = $('#referral_reward_discount').val();
		if (curReferralRewardDiscount * 1 > discountablePlatePointsAmount * 1)
		{
			curReferralRewardDiscount = discountablePlatePointsAmount;

			if (curReferralRewardDiscount == 0)
			{
				$('#referral_reward_discount').val("");
			}
			else
			{
				$('#referral_reward_discount').val(formatAsMoney(curReferralRewardDiscount));
			}
		}

		$('#OEH_referral_reward_order_discount').html(formatAsMoney(curReferralRewardDiscount));

		newGrandTotal = formatAsMoney(newGrandTotal - curReferralRewardDiscount);
	}

	// ---------------------------------------------- Direct Order
	let directDiscountVal = Number(0);

	directDiscount = $('#direct_order_discount');

	if (directDiscount.length)
	{
		directDiscountVal = directDiscount.val();

		if (isNaN(directDiscountVal))
		{
			directDiscountVal = 0;
		}

		if (directDiscountVal > food_costs)
		{
			directDiscountVal = food_costs;
			directDiscount.val(directDiscountVal);
		}

		if (directDiscountVal)
		{
			newGrandTotal = formatAsMoney(newGrandTotal - directDiscountVal);
		}

		$('#OEH_direct_order_discount').html(formatAsMoney(directDiscountVal));

	}

	// Adjust Bonus Credit and discount
	if (isFactoringCredit)
	{
		//alert(creditContribution);
		//alert(creditConsumed);
		creditBasis += creditContribution;
		createBonusCredit(creditConsumed);
	}

	// ------------------------------------------------------------------- coupon Discount

	if (couponDiscountMethod == 'PERCENT')
	{

		let CouponCodeStr = $('#coupon_code').val();
		var newDiscountAmount = 0;
		try
		{
			//this method correctly handles rounding in a way that matches server side
			let formatterUSD = new Intl.NumberFormat('en-US', {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2
			});
			if (CouponCodeStr == 'VOLUME' || couponlimitedToCore)
			{
				newDiscountAmount =  formatterUSD.format(coreItemsSubtotal * (couponDiscountVar / 100))
			}
			else
			{
				newDiscountAmount = formatterUSD.format(pre_discounts_grand_total * (couponDiscountVar / 100))
			}
		}
		catch(e)
		{
			//original way which lead to off-by-one issue compared to server's method of rounding
			if (CouponCodeStr == 'VOLUME' || couponlimitedToCore )
			{
				newDiscountAmount = Math.floor(coreItemsSubtotal * (couponDiscountVar));
			}
			else
			{
				newDiscountAmount = Math.floor(pre_discounts_grand_total * (couponDiscountVar));
			}

			newDiscountAmount /= 100;
		}


		$('#couponValue').val(newDiscountAmount);
	}

	if (couponlimitedToFT)
	{
		if (couponDiscountMethod == 'FLAT')
		{
			let newDiscountVal = sideDishSubTotal;

			if (newDiscountVal > couponDiscountVar)
			{
				newDiscountAmount = couponDiscountVar;
			}
			else
			{
				newDiscountAmount = newDiscountVal;
			}
		}

		if (couponDiscountMethod == 'PERCENT')
		{
			let newDiscountVal = sideDishSubTotal;

			var newDiscountAmount = Math.floor(newDiscountVal * (couponDiscountVar));

			newDiscountAmount /= 100;
		}

		$('#couponValue').val(newDiscountAmount);
	}

	let couponDiscountVal = Number(0);
	couponDiscount = $('#couponValue');

	if (couponDiscount.length)
	{
		if (couponDiscount.val() == "")
		{
			couponDiscount.val('0.00');
		}
		couponDiscountVal = couponDiscount.val();
	}

	let couponDiscountValIsServiceFee = false;
	let couponDiscountValIsDeliveryFee = false;

	if (typeof coupon != 'undefined')
	{
		if (coupon.limit_to_mfy_fee == '1' && coupon.discount_method == 'FLAT')
		{
			let currentServiceFee = $("#subtotal_service_fee").val();
			if (couponDiscountVal != currentServiceFee)
			{
				couponDiscountVal = currentServiceFee;
			}

			couponDiscountValIsServiceFee = true;
		}

		if (coupon.limit_to_delivery_fee == '1' && coupon.discount_method == 'FLAT')
		{
			let currentDeliveryFee = $("#subtotal_delivery_fee").val();
			if (couponDiscountVal != currentDeliveryFee)
			{
				if(parseFloat(couponDiscountVal) > parseFloat(currentDeliveryFee)){
					couponDiscountVal = currentDeliveryFee;
					couponDiscountValIsDeliveryFee = true;
				}
			}

			//couponDiscountValIsDeliveryFee = true;
		}

		if (coupon.limit_to_recipe_id == '1')
		{
			let prc_str = $('#prc_' + coupon.menu_item_id).text();

			if (coupon.discount_method == 'FLAT')
			{
				if (prc_str > coupon.discount_var)
				{
					couponDiscountVal = formatAsMoney(prc_str - coupon.discount_var);
				}
				else
				{
					couponDiscountVal = formatAsMoney(prc_str);
				}
			}
			else if (coupon.discount_method == 'PERCENT')
			{
				couponDiscountVal = formatAsMoney(prc_str * (coupon.discount_var / 100));
			}
		}

		$('#couponValue').val(couponDiscountVal);
	}

	if (couponDiscountVal)
	{
		newGrandTotal = formatAsMoney(newGrandTotal - couponDiscountVal);
		$('#OEH_coupon_discount').html(formatAsMoney(couponDiscountVal));
	}

	// ------------------------------------------------ Preferred Customer
	let PUDElem = $('#PUDnoUP');
	let UPDiscountElem;
	if (PUDElem.length && !bundleIsSelected)
	{
		let selectedUP = $('input[name=PUD]:checked', '#editorForm').val();

		if (selectedUP == null || selectedUP == "")
		{
			selectedUP = "noUP";
		}

		let UPDiscount = -1;

		let UPType = 'none';
		let PcntTypeValue = 0.0;

		if (originalUP != false || activeUP != false)
		{
			let preferredData = false;

			if (selectedUP == "activeUP")
			{
				preferredData = activeUP;
			}
			else if (selectedUP == "originalUP")
			{
				preferredData = originalUP;
			}

			let preferredCapRemaining = preferredData.preferred_cap_value;

			if (capacityUP.remainingObj.countRemaining !== null)
			{
				preferredCapRemaining = capacityUP.remainingObj.countRemaining
			}

			if (preferredData.type == "FLAT")//Only allows ITEMS type
			{

				if ((preferredData.preferred_cap_type == 'ITEMS' && totalMealQuantity <= preferredCapRemaining) ||
					preferredData.preferred_cap_type == 'NONE')
				{
					let cost = (wholeQty - PUDExcludedFullItemsCount) * preferredData.value;
					let halfCost = Number(formatAsMoney(((halfQty - PUDExcludedHalfItemsCount) * (preferredData.value / 2))));
					cost += halfCost;
					let basis = discounted_total - cost - addonsSubtotal - productsSubTotal - PUDExcludedItemsTotal;
					if (preferredData.include_sides === '0')
					{
						basis -= sideDishSubTotal;
					}
					UPDiscount = formatAsMoney(basis);
					UPType = 'flat';
				}else if((preferredData.preferred_cap_type == 'ITEMS' && totalMealQuantity > preferredCapRemaining)){

					let cost = 0;
					let halfCost = 0;
					let itemCount = 0;
					sortedCoreItemList.sort((a, b) => (a.price < b.price) ? 1 : -1);
					for(let i = 0; i < totalMealQuantity; i++){
						itemCount += sortedCoreItemList[i].qty;
						if(itemCount <= preferredCapRemaining){
							if(sortedCoreItemList[i].serving_size > 5)
							{
								cost += sortedCoreItemList[i].qty * preferredData.value;
							}else{
								halfCost += Number(formatAsMoney(((sortedCoreItemList[i].qty - PUDExcludedHalfItemsCount) * (preferredData.value / 2))))
							}
						}
						else
						{
							cost += sortedCoreItemList[i].qty * sortedCoreItemList[i].price;
						}
					}

					cost += halfCost;


					let basis = discounted_total - cost - addonsSubtotal - productsSubTotal - PUDExcludedItemsTotal;
					if (preferredData.include_sides === '0')
					{
						basis -= sideDishSubTotal;
					}
					UPDiscount = formatAsMoney(basis);
					UPType = 'flat';
				}
			}
			else if (preferredData.type == "PERCENT")
			{
				if ((preferredData.preferred_cap_type == 'ITEMS' && totalMealQuantity <= preferredCapRemaining) ||
					(preferredData.preferred_cap_type == 'SERVINGS' && servings <= preferredCapRemaining) ||
					preferredData.preferred_cap_type == 'NONE')
				{
					let basis = pre_discounts_grand_total - addonsSubtotal - productsSubTotal;
					if (preferredData.include_sides === '0')
					{
						basis -= sideDishSubTotal;
					}
					let multiplier = preferredData.value / 100;
					let pud_result = basis * multiplier;
					let rounded_pud_result = dd_round_amount(pud_result);
					UPDiscount = formatAsMoney(rounded_pud_result);
					UPType = 'pcnt';
					PcntTypeValue = preferredData.value / 100;
				}

			}
		}

		if (selectedUP == "noUP")
		{
			UPDiscount = 0;
		}

		if (UPType == 'flat')
		{
			if (typeof promoVal != 'undefined' && promoVal > 0 && UPDiscount > 0)
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
			if (typeof promoVal != 'undefined' && promoVal > 0 && UPDiscount > 0)
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
			}
		}

	}

	// ----------------------------------------------------------Membership Discount

	if (orderIsEligibleForMembershipDiscount && storeSupportsMembership)
	{
		let couponAdjustment = Number(0);
		if (couponDiscountValIsServiceFee || couponDiscountValIsDeliveryFee)
		{
			couponAdjustment = couponDiscountVal;
		}

		let membership_discount_basis = Number((newGrandTotal * 1) + (couponAdjustment * 1));
		let memberDiscountAmount = Number(membership_discount_basis * (membership_status.discount_var / 100));
		memberDiscountAmount = Math.round(memberDiscountAmount * 100) / 100;

		$("#OEH_membership_discount").html(formatAsMoney(memberDiscountAmount));
		newGrandTotal = formatAsMoney(newGrandTotal - memberDiscountAmount);

	}

	// -------------------------------------------------------- Session Discount

	SessDisc = $('#SessDiscnoSD');
	if (SessDisc.length && !orderIsEligibleForMembershipDiscount)
	{
		let couponAdjustment = Number(0);
		if (couponDiscountValIsServiceFee || couponDiscountValIsDeliveryFee)
		{
			couponAdjustment = couponDiscountVal;
		}

		let session_discount_basis = Number((newGrandTotal * 1) + (directDiscountVal * 1) + (miscFoodSubtotal * 1) + (couponAdjustment * 1));

		let selectedSD = $('input[name=SessDisc]:checked', '#editorForm').val();

		if (selectedSD == null || selectedSD == "")
		{
			selectedSD = "noSD";
		}

		let sessionDiscount = -1;

		if (selectedSD == "originalSD")
		{

			let rawAmount = (session_discount_basis * originalSD.value) / 100;
			rawAmount += .00000001; // salt to avoid rounding error
			// can't do this in formatAsMoney without a full validation of order manager as odd things happen
			sessionDiscount = formatAsMoney(rawAmount);
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

	newGrandTotal = Number((newGrandTotal * 1) + (miscFoodSubtotal * 1));
	$('#OEH_misc_food_subtotal').html(formatAsMoney(miscFoodSubtotal));

	let miscNonFoodSubtotal = Number($('#misc_nonfood_subtotal').val());
	$('#OEH_misc_nonfood_subtotal').html(formatAsMoney(miscNonFoodSubtotal));
	newGrandTotal = Number((newGrandTotal * 1) + (miscNonFoodSubtotal * 1));

	let serviceFee = Number($('#subtotal_service_fee').val());
	$('#OEH_subtotal_service_fee').html(formatAsMoney(serviceFee));

	newGrandTotal = Number((newGrandTotal * 1) + (serviceFee * 1));

	let deliveryFee = Number(0);
	if ($('#subtotal_delivery_fee')[0])
	{
		deliveryFee = Number($('#subtotal_delivery_fee').val());
		$('#OEH_subtotal_delivery_fee').html(formatAsMoney(deliveryFee));

		newGrandTotal = Number((newGrandTotal * 1) + (deliveryFee * 1));
	}

	let BagFeeTotal = Number(0);
	// Bag Fees
	if (storeSupportsBagFees)
	{
		let calculatedBagCount = getNumberBagsRequiredFromItems(halfQty + wholeQty, sideDishQty);

		if (!$("#opted_to_bring_bags").is(":checked"))
		{
			$("#total_bag_count").prop("disabled", false);

			if (bagCountLockedToField)
			{
				numBagsRequired = $("#total_bag_count").val();
			}
			else
			{
				numBagsRequired = calculatedBagCount;
				$("#total_bag_count").val(calculatedBagCount);
			}

			if (isNaN(numBagsRequired))
			{
				numBagsRequired = 0;
			}

			BagFeeTotal = storeDefaultBagFee * numBagsRequired;
		}
		else
		{
			$("#total_bag_count").prop("disabled", "disabled");
			$("#total_bag_count").val("0");
		}

		$("#OEH_subtotal_bag_fee").html(formatAsMoney(BagFeeTotal));

	}
	let mealCustomizationFeeTotal = 0;
	// Meal Customization Fees
	if (storeSupportsMealCustomization)
	{

		if($('#manual_customization_fee').val() == 'true')
		{
			//use manually entered value
			mealCustomizationFeeTotal = Number($("#subtotal_meal_customization_fee").val());
		}
		else
		{
			mealCustomizationFeeTotal =  calculateMealCustomizationFee(customizableMealQty);
			$("#subtotal_meal_customization_fee").val(formatAsMoney(mealCustomizationFeeTotal));
		}

		if ($("#opted_to_customize_recipes").is(":checked"))
		{
			$("#subtotal_meal_customization_fee").prop("disabled", "disabled");
			$("#subtotal_meal_customization_fee").val("");
			mealCustomizationFeeTotal = 0;
			$("#customization-row-container").hide();
		}

		$("#OEH_subtotal_meal_customization_fee").html(formatAsMoney(mealCustomizationFeeTotal));
	}

	// --------------------------------------------------------------------- tax

	// Currently the only taxed product is the enrollment fee which is taxed at a special rate
	let newNonFoodTax = formatAsMoney(miscNonFoodSubtotal * (curNonFoodTax / 100));
	let enrollmentTax = formatAsMoney(productsSubTotal * (curEnrollmentTax / 100));

	let newDeliveryTax = 0;
	if (!hasDeliveryFeeWithCoupon)
	{
		newDeliveryTax = formatAsMoney(deliveryFee * (curDeliveryTax / 100));
	}

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

	let newFoodTax = 0;
	let newServiceTax = 0;

	if (curPPDiscount > 0)
	{
		let food_portion_of_points_credit = 0;
		let fee_portion_of_points_credit = 0;

		if (hasServiceFeeWithMFYCoupon)
		{
			food_portion_of_points_credit = curPPDiscount;
			fee_portion_of_points_credit = 0;
		}
		else if (discountMFYFeeFirst && serviceFee > 0)
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
			let foodPortionOfMaxDeduction = discountablePlatePointsAmount - serviceFee;

			if (curPPDiscount > foodPortionOfMaxDeduction)
			{
				let remainderAfterFoodDiscount = curPPDiscount - foodPortionOfMaxDeduction;
				food_portion_of_points_credit = foodPortionOfMaxDeduction;
				fee_portion_of_points_credit = remainderAfterFoodDiscount;
			}
			else
			{
				food_portion_of_points_credit = curPPDiscount;
				fee_portion_of_points_credit = 0;
			}
		}

		if (hasServiceFeeWithMFYCoupon)
		{
			newFoodTax = formatAsMoney(((newGrandTotal + (curPPDiscount * 1) - miscNonFoodSubtotal - food_portion_of_points_credit - deliveryFee) * (curFoodTax / 100)) + .000001);
			newServiceTax = 0;
		}
		else if (hasDeliveryFeeWithCoupon)
		{
			newFoodTax = formatAsMoney(((newGrandTotal + (curPPDiscount * 1) - miscNonFoodSubtotal - food_portion_of_points_credit - serviceFee) * (curFoodTax / 100)) + .000001);
			newDeliveryTax = 0;
		}
		else
		{
			newFoodTax = formatAsMoney(((newGrandTotal + (curPPDiscount * 1) - miscNonFoodSubtotal - serviceFee - food_portion_of_points_credit - deliveryFee) * (curFoodTax / 100)) + .000001);
			let mealCustomizationFee = Number($('#subtotal_meal_customization_fee').val())
			if (isNaN(mealCustomizationFee)) { mealCustomizationFee = 0;}
			newServiceTax = formatAsMoney(((((serviceFee + mealCustomizationFee) - fee_portion_of_points_credit)) * (curServiceTax / 100)) + .000001);
		}

		$('#OEH_plate_points_order_discount_food').html(formatAsMoney(food_portion_of_points_credit));
		$('#OEH_plate_points_order_discount_fee').html(formatAsMoney(fee_portion_of_points_credit));
	}
	else
	{
		if (hasServiceFeeWithMFYCoupon)
		{
			newFoodTax = formatAsMoney(((newGrandTotal - miscNonFoodSubtotal - deliveryFee) * (curFoodTax / 100)) + .000001);
			newServiceTax = 0;
		}
		else if (hasDeliveryFeeWithCoupon)
		{
			newFoodTax = formatAsMoney(((newGrandTotal - miscNonFoodSubtotal - serviceFee) * (curFoodTax / 100)) + .000001);
			newDeliveryTax = 0;
		}
		else
		{
			newFoodTax = formatAsMoney(((newGrandTotal - miscNonFoodSubtotal - serviceFee - deliveryFee) * (curFoodTax / 100)) + .000001);
			let mealCustomizationFee = Number($('#subtotal_meal_customization_fee').val())
			if (isNaN(mealCustomizationFee)) { mealCustomizationFee = 0;}
			newServiceTax = formatAsMoney((serviceFee + mealCustomizationFee) * (curServiceTax / 100) + .000001);
		}

		$('#OEH_plate_points_order_discount_food').html(formatAsMoney(0));
		$('#OEH_plate_points_order_discount_fee').html(formatAsMoney(0));
	}

	let newBagFeeTax = formatAsMoney(BagFeeTotal * (curBagFeeTax / 100) + .000001);

	taxElem = document.getElementById('OEH_tax_subtotal');
	if (taxElem)
	{
		taxElem.innerHTML = formatAsMoney(newNonFoodTax);
	}

	taxElem = document.getElementById('OEH_food_tax_subtotal');
	if (taxElem)
	{
		taxElem.innerHTML = formatAsMoney(newFoodTax);
	}

	taxElem = document.getElementById('OEH_service_tax_subtotal');
	if (taxElem)
	{
		taxElem.innerHTML = formatAsMoney(newServiceTax);
	}

	taxElem = document.getElementById('OEH_delivery_tax_subtotal');
	if (taxElem)
	{
		taxElem.innerHTML = formatAsMoney(newDeliveryTax);
	}

	taxElem = document.getElementById('OEH_bag_fee_tax_subtotal');
	if (taxElem)
	{
		taxElem.innerHTML = formatAsMoney(newBagFeeTax);
	}

	newGrandTotal = Number((newGrandTotal * 1) + (BagFeeTotal * 1) + (mealCustomizationFeeTotal * 1));

	let preTaxTotal = Number(newGrandTotal);
	newGrandTotal = (newGrandTotal * 1) + (newFoodTax * 1) + (newNonFoodTax * 1) + (newServiceTax * 1) + (newDeliveryTax * 1) + (newBagFeeTax * 1);

	/*
	 * *
	 * LTD ROUND UP, MUST BE AFTER GRAND TOTAL
	 * *
	 */
	let round_up_donation = 0;

	let subtotal_round_up = Math.ceil(newGrandTotal);
	let subtotal_round_up_diff = parseFloat((subtotal_round_up - (newGrandTotal)).toFixed(2));

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

	if (isNaN(round_up_donation))
	{
		round_up_donation = 0;
	}

	$('#OEH_ltd_round_up').html(formatAsMoney(round_up_donation));

	newGrandTotal += Number(round_up_donation);
	/*
	 * end Round UP
	 */

	// -------------------------------------------------------------------- grand total
	$('#OEH_grandtotal').html(formatAsMoney(newGrandTotal));
	$('#OEH_new_total').html(formatAsMoney(newGrandTotal));
	$('#OEH_delta').html(formatAsMoney((orderInfoGrandTotal - newGrandTotal) * -1));

	let paymentTotal = 0;
	if ($('#OEH_paymentsTotal').length)
	{
		paymentTotal = $('#OEH_paymentsTotal').html();
	}

	let remainingBalance = Number(formatAsMoney(paymentTotal - newGrandTotal));
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

		let doNewLivePaymentAdjustments = true;
		if (doNewLivePaymentAdjustments)
		{

			let defaultPaymentAmount = remainingBalance * -1;

			if ($("#use_store_credits").is(":checked"))
			{

				let amountStoreCredit = Number($("#store_credits_amount").val());
				if (isNaN(amountStoreCredit.valueOf()))
				{
					amountStoreCredit = Number(0);
				}

				defaultPaymentAmount -= amountStoreCredit.valueOf();
			}

			defaultPaymentAmount = formatAsMoney(defaultPaymentAmount);

			if ($('#payment1_type').val() == 'CC')
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
				let payment1Amount = null;
				if ($('#payment1_type').val() == 'GIFT_CERT')
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
				if ($('#payment2_type').val() == 'CC')
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

	let autoAdjustDiv = $('#autoAdjustDiv');
	if (autoAdjustDiv.length)
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
	if ($('#submit_changes').length)
	{

		if (servings > 0 || sideDishQty > 0 || addonsSubtotal > 0)
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
			$('#help_msg').html($('#help_msg').html() + "You cannot use a Direct Order Discount that is equal to or greater than the food cost total. Please use the \"No Charge\" payment type to give away an order.");
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

$(document).on('focusin', '.bundle-subitem-qty', function (e) {

	$(this).data('org_val', $(this).val());

}).on('keyup change', '.bundle-subitem-qty', function (e) {

	let canIncrement = canIncrementBundleSubItem($(this), '.bundle-subitem-qty');

	if (canIncrement !== true)
	{
		$(this).val($(this).data('org_val'));

		dd_message({
			title: 'Max Items Reached',
			message: canIncrement
		});
	}
	else
	{
		$(this).data('org_val', $(this).val());

		qtyUpdate(this);
	}

	calculateTotal();

});

function canIncrementBundleSubItem(input, selector)
{
	let mid = $(input).data('menu_item_id');
	let pid = $(input).data('parent_item');

	let itemInfo = sideStationBundleInfo[pid].bundle[mid];
	let parentItemInfo = sideStationBundleInfo[pid];
	let parentNumItemsRequired = parseInt(parentItemInfo.number_items_required);
	let groupID = parseInt(itemInfo.bundle_to_menu_item_group_id) || 0;

	// check that we aren't going over the total
	let total_org = 0;
	let total_current = 0;
	let groupInfo = [];

	$(selector + '[data-parent_item="' + pid + '"]').each(function () {

		let mid = $(this).data('menu_item_id');
		let pid = $(this).data('parent_item');

		let inputItemInfo = sideStationBundleInfo[pid].bundle[mid];
		let inputGroupID = parseInt(inputItemInfo.bundle_to_menu_item_group_id) || 0;

		let this_org = parseInt($(this).data('org_val')) || 0;
		let this_cur = parseInt($(this).val()) || 0;

		total_org += this_org;
		total_current += this_cur;

		if (typeof groupInfo[inputGroupID] == 'undefined')
		{
			groupInfo[inputGroupID] = {
				total_org: 0,
				total_current: 0
			}
		}

		groupInfo[inputGroupID].total_org += this_org;
		groupInfo[inputGroupID].total_current += this_cur;

	});

	// check if over the bundle total
	if (total_current > (parentNumItemsRequired * parseInt($('#qty_' + pid).val())))
	{
		return 'Number of items for the bundle has been reached';
	}

	// check if over the group total
	if (parentItemInfo.bundle_groups.length > 0)
	{
		if (groupInfo[groupID].total_current > (parseInt(parentItemInfo.bundle_groups[groupID].number_items_required) * parseInt($('#qty_' + pid).val())))
		{
			return 'Number of items for the group has been reached';
		}
	}

	// all else
	return true;

}


function preferenceChangeListener(pref, setting, user, callback){

	pref = pref.toUpperCase();
	let pref_value = USER_PREFERENCES[pref].value;

	if ($.isPlainObject(pref_value) && $.isPlainObject(setting))
	{
		setting = JSON.stringify($.extend(USER_PREFERENCES[pref].value, setting));
	}
	else
	{
		setting = setting.toString()
	}

	if(pref.includes('_DETAILS')){
		let key = 'MEAL_EXCLUDE_'+pref.replace('MEAL_','').replace('_DETAILS','');
		meal_customization_preferences[key].details = setting;
	}else{
		meal_customization_preferences[pref].value = setting;
	}

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_orderCustomization',
			op: 'update_order_meal_customization',
			order_id: order_id,
			customizations: JSON.stringify(meal_customization_preferences)
		},
		success: function (json) {
			if (json.processor_success)
			{
				if(json.cost > 0){
					$('#customization-fee-row').show();
					$('#MealCustomizationFeeRow').show();
				}else{
					$('#customization-fee-row').hide();
					$('#MealCustomizationFeeRow').hide();
				}

				if($('#manual_customization_fee').val() != 'true')
				{
					$('#subtotal_meal_customization_fee').val(formatAsMoney(json.cost));
					$("#OEH_subtotal_meal_customization_fee").html(formatAsMoney(json.cost));
				}
				handleMealCustomizationFeeInput();

				if(typeof callback !== 'undefined'){
					callback(json);
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
		error: function (objAJAXRequest, strError) {
			dd_message({
				title: 'Error',
				message: 'Unexpected error: ' + strError
			});
		}
	});
}

$(document).on('change', '#shipping_phone_number', function (e) {

	$('.shipping_phone_number_new_div').hideFlex();
	$('#shipping_phone_number_new').prop('required', false);

	if ($(this).val() == 'new')
	{
		$('.shipping_phone_number_new_div').showFlex();
		$('#shipping_phone_number_new').prop('required', true);

	}
});

$(document).on('change', '#hide-zero-sides', function (e) {

	if ($(this).is(':checked'))
	{
		$('#sidesTbl [data-orig-remaining="0"]').hideFlex();
	}
	else
	{
		$('#sidesTbl [data-orig-remaining]').showFlex();
	}
});

$(document).on('change keyup', '.delivery-input', function (e) {

	deliveryChanges = false;

	$('.delivery-input').each(function () {

		if ($(this).data('org_value'))
		{
			let val = $(this).val();
			let org_value = $(this).data('org_value');

			if (val != org_value)
			{
				deliveryChanges = true;

			}
		}

	});

	updateChangeList();

});

function costInputFeesTab(obj)
{
	// TODO: validation

	setFeesTabAsDirty();

	calculateTotal();
}

function setFeesTabAsDirty()
{
	feesTabIsDirty = true;
}