var itemChanges = false;
var discountOrPaymentChanges = false;
var deliveryChanges = false;
var discountChanges = false;
var reportingChanges = false;
var paymentChanges = false;
var changeList = null;
var currentlySavingOrder = false;
var intenseLoggingOn = true;
var boxesDeletedFromActiveOrder = []; // for showing box deletions in change list

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

	initChangeList();
	init_inside_box_editing();

	if (orderState != 'NEW')
	{
		calculateTotal();
		setAllPaymentFieldsToUnrequired();
		setupCouponState();
	}

	setUpKeyHandlers();

	handle_special_instruction_notes();
	handle_cancel_order_delivered();
	handle_delete_saved_order_delivered();

	handle_free_menu_item_edit();
	handleDirectOrderDiscountFocusing();

	$('#shipping_phone_number').trigger('change');

	updateInventory();

	handle_is_gift();

}

function handleSessionSelection()
{
	$("[data-select_delivery_day]").on('click', function () {
		let selected_session_id = $(this).data("select_delivery_day");
		let sessionDate = $(this).data("date");
		doReschedule(selected_session_id, sessionDate);
	});

}

function handle_is_gift()
{
	if ($("#shipping_is_gift").is(':checked'))
	{
		$(".shipping_email_address_div").show();
	}
	else
	{
		$(".shipping_email_address_div").hide();
	}

	$("#shipping_is_gift").on('change', function () {
		if ($(this).is(':checked'))
		{
			$(".shipping_email_address_div").show();
		}
		else
		{
			$(".shipping_email_address_div").hide();
		}
	});

}

function validateBoxes()
{
	let hasAtLeastOneBox = false;
	let noCustomBoxesAreIncomplete = true;

	$("[id^='box_inst_id_']").each(function () {
		let list_id = this.id.split("_")[3];

		if ($(this).data("contents_are_fixed") == true)
		{
			hasAtLeastOneBox = true;
		}
		else
		{
			hasAtLeastOneBox = true;

			let itemCount = 0;
			$("input").filter("[data-box_inst_id='" + list_id + "']").each(function () {
				itemCount += ($(this).val() * 1);
			});

			let number_items_required = $("#box_inst_id_" + list_id).data("number_items_required");

			if (number_items_required != itemCount)
			{
				noCustomBoxesAreIncomplete = false;
			}
		}
	});

	return (hasAtLeastOneBox && noCustomBoxesAreIncomplete);
}

function countItemsInBox(box_instance_id)
{
	let retVal = 0;
	$("[id^='qty_" + box_instance_id + "_']").each(function () {
		retVal += ($(this).val() * 1);
	});

	return retVal;
}

function init_inside_box_editing()
{

	$("[id^='qty_']").off('click', function () {

	});

	$("[id^='qty_']").on('change', function () {
		let itemError = false;
		let boxError = false;

		let menu_item_id = this.id.split("_")[2];
		let box_inst_id = this.id.split("_")[1];
		let number_items_required = $("#box_inst_id_" + box_inst_id).data("number_items_required");
		let newQty = $(this).val();

		let curTotal = countItemsInBox(box_inst_id);
		if (curTotal > number_items_required)
		{
			let delta = curTotal - number_items_required;
			$(this).val(newQty - delta);
			boxError = true;
		}

		newQty = $(this).val();

		if (isNaN(newQty))
		{
			newQty = 0;
			$(this).val(0);
		}
		if (newQty < 0)
		{
			newQty = 0;
			$(this).val(0);
		}

		if (newQty - Math.floor(newQty) != 0)
		{
			newQty = Math.floor(newQty);
			$(this).val(newQty);
		}

		let lastQty = $(this).data('last_qty'); // last quantity is already removed from current_inventory array
		var servings = $(this).data('servings_per_item');
		var recipeID = $(this).data('recipe_id');

		if (newQty > 2)
		{
			itemError = true;
			$(this).val(2);
			newQty = 2;
		}

		let new_servings_needed = (newQty - lastQty) * servings;

		while (new_servings_needed > current_inventory[recipeID])
		{
			newQty--;
			new_servings_needed = (newQty - lastQty) * servings;
		}

		$(this).val(newQty);
		$(this).data('last_qty', newQty);
		current_inventory[recipeID] -= new_servings_needed;

		$("[id^='inv_']").filter("[data-recipe_id='" + recipeID + "']").html(current_inventory[recipeID] + "/serv");

		updateChangeList();

		if (boxError && itemError)
		{
			dd_message({
				title: 'Error',
				message: "Each box can only have up to 2 of the same menu item and a total of " + number_items_required + " items."
			});
		}
		else if (itemError)
		{
			dd_message({
				title: 'Error',
				message: "Each box can only have up to 2 of the same menu item"
			});
		}
		else if (boxError)
		{
			dd_message({
				title: 'Error',
				message: "You can only add a total of " + number_items_required + " items to this box."
			});
		}
	});
}

function addToInitialInventory(list_id)
{

	if ($(this).data("contents_are_fixed") == true)
	{
		$("[id^='qty_" + list_id + "_']").each(function () {
			var curAmount = $(this).val();
			var recipeID = $(this).data('recipe_id');
			var servings = $(this).data('servings_per_item');
			var curInv = initial_inventory[recipeID];
			var adjInv = curInv + (curAmount * servings);
			initial_inventory[recipeID] = adjInv;
		});
	}
	else
	{
		$("input").filter("[data-box_inst_id='" + list_id + "']").each(function () {
			var curAmount = $(this).val();
			var recipeID = $(this).data('recipe_id');
			var servings = $(this).data('servings_per_item');
			var curInv = current_inventory[recipeID];
			var adjInv = curInv + (curAmount * servings);
			initial_inventory[recipeID] = adjInv;
		});
	}
}

$(document).on('click', '.add_box', function (e) {

	let bundle_id = $(this).data("bundle_id");
	let box_id = $(this).data("box_id");
	let box_type = $(this).data("box_type");
	let box_label = $(this).data("box_label");

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor_delivered',
			op: 'add_box',
			bundle_id: bundle_id,
			box_id: box_id,
			box_type: box_type,
			box_label: box_label,
			menu_id: menu_id,
			store_id: store_id,
			order_id: order_id,
			user_id: user_id
		},
		success: function (json) {
			if (json.processor_success)
			{
				$("#box_editor").append(json.html);

				let currentDeliveryFee = $('#subtotal_delivery_fee').val();
				let newDeliveryFee = Number(formatAsMoney(currentDeliveryFee)) + Number(formatAsMoney(json.bundle_info.price_shipping));

				$('#subtotal_delivery_fee').val(newDeliveryFee);

				updateInventory();
				calculateTotal();
				init_inside_box_editing();

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
			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});
		}
	});
});

$(document).on('click', '.box-delete', function (e) {

	e.preventDefault();
	let box_inst_id = $(this).data('box_instance_id');
	//let price_shipping = $(this).data('price_shipping');

	let currentDeliveryFee = $('#subtotal_delivery_fee').val();
	let newDeliveryFee = Number(formatAsMoney(currentDeliveryFee)) - Number(formatAsMoney($("#box_inst_id_" + box_inst_id).data('price_shipping')));

	if (orderState == 'ACTIVE')
	{

		$('#subtotal_delivery_fee').val(newDeliveryFee);

		// active orders defer database updates until finalized
		addToInitialInventory(box_inst_id);
		boxesDeletedFromActiveOrder[box_inst_id] = $("#box_inst_id_" + box_inst_id).data('box_label');
		$("#bi_" + box_inst_id).remove();
		updateInventory();
		calculateTotal();
		return;
	}

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor_delivered',
			op: 'delete_box',
			box_inst_id: box_inst_id,
			order_id: order_id,
			user_id: user_id

		},
		success: function (json) {
			if (json.processor_success)
			{
				$("#bi_" + box_inst_id).remove();

				$('#subtotal_delivery_fee').val(newDeliveryFee);

				updateInventory();
				calculateTotal();
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
			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});

		}
	});

});

function updateInventory()
{

	// re init current values
	current_inventory = Object.assign({}, initial_inventory);

	// loop through all existing boxes and accumulate values in current_inventory
	// after items have been accumulated push numbers to the display

	$("[id^='box_inst_id_']").each(function () {

		let list_id = this.id.split("_")[3];

		if ($(this).data("contents_are_fixed") == true)
		{
			$("[id^='qty_" + list_id + "_']").each(function () {
				var curAmount = $(this).val();
				var claimedAmount = $(this).data('claimed_qty');
				curAmount -= claimedAmount;
				var recipeID = $(this).data('recipe_id');
				var servings = $(this).data('servings_per_item');
				var curInv = current_inventory[recipeID];
				var adjInv = curInv - (curAmount * servings);
				current_inventory[recipeID] = adjInv;
			});
		}
		else
		{
			$("input").filter("[data-box_inst_id='" + list_id + "']").each(function () {
				var curAmount = $(this).val();
				var claimedAmount = $(this).data('claimed_qty');
				curAmount -= claimedAmount;
				var recipeID = $(this).data('recipe_id');
				var servings = $(this).data('servings_per_item');
				var curInv = current_inventory[recipeID];
				var adjInv = curInv - (curAmount * servings);
				current_inventory[recipeID] = adjInv;
			});
		}
	});

	for (const recipe_id in current_inventory)
	{

		$("[id^='inv_']").filter("[data-recipe_id='" + recipe_id + "']").each(function () {

			// TODO:  item < serving per item is also out of stock
			if (current_inventory[recipe_id] == 0)
			{
				$(this).html('Out of stock');
			}
			else
			{
				$(this).html(current_inventory[recipe_id] + "/serv");
			}
		});

	}

}

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
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor_delivered',
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
				url: '/processor',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_order_mgr_processor_delivered',
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
			close: function (event, ui) {
				$("#changelist").remove();
			}
		});
	});
}

function updateItemsOrgValues()
{
	// TODO
	updateChangeList();
}

function saveAll()
{
	saveItems(true, false, true);
}

function saveItems(saveDiscountsUponCompletion, activateOnSaveDiscountsCompletion)
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

	if (activateOnSaveDiscountsCompletion)
	{
		if (!validateBoxes())
		{
			$("#activate_confirm").remove();
			dd_message({
				title: 'Problem with boxes',
				message: "Please review the selected box(es) and ensure item counts are correct."
			});
			return;
		}
	}
	var boxList = {};

	$("[id^='box_inst_id_']").each(function () {
		let list_id = this.id.split("_")[3];
		boxList[list_id] = {};

		if ($(this).data("contents_are_fixed") == true)
		{
			$("li[data-box_inst_id|='" + list_id + "']").each(function () {
				boxList[list_id][$(this).data("menu_item_id")] = 1;
			});
		}
		else
		{
			$("input").filter("[data-box_inst_id='" + list_id + "']").each(function () {
				boxList[list_id][$(this).data("menu_item_id")] = $(this).val();
			});

		}

	});

	var subtotal_delivery_fee = $("#subtotal_delivery_fee").val();
	if (isNaN(subtotal_delivery_fee))
	{
		subtotal_delivery_fee = "0.00";
	}

	var special_instructions = $("#order_user_notes").val();

	currentlySavingOrder = true;

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor_delivered',
			store_id: STORE_DETAILS.id,
			user_id: user_id,
			op: 'update_items',
			order_id: order_id,
			items: boxList,
			subtotal_delivery_fee: subtotal_delivery_fee,
			special_instructions: special_instructions
		},
		success: function (json) {
			if (json.processor_success)
			{
				itemTabIsDirty = false;
				updateItemsOrgValues();

				currentlySavingOrder = false;

				intenseLogging("saveItems() successful");

				if (saveDiscountsUponCompletion)
				{
					saveDiscounts(activateOnSaveDiscountsCompletion);
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

/*
function saveDeliveryAddress(payOnCompletion, token)
{

	var shipping_data = {};
	$("[id^='shipping_']").each(function(){
		shipping_data[this.id] = $(this).val();
	});

	intenseLogging("saveDeliveryAddress() called");

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 200000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor_delivered',
			store_id: STORE_DETAILS.id,
			user_id: user_id,
			op: 'save_delivery_address',
			order_id: order_id,
			shipping_data: shipping_data
		},
		success: function (json)
		{
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
		error: function (objAJAXRequest, strError)
		{

			intenseLogging("save_delivery_address: " + strError + " | " + objAJAXRequest.responseText);

			response = 'Unexpected error: ' + strError;
			dd_message({
				title: 'Error',
				message: response
			});

		}

	});

}

 */

function setShipToAddressAndSaveOrder()
{

	var shipping_data = {};
	$("[id^='shipping_']").each(function () {
		shipping_data[this.id] = $(this).val();
	});

	if ($("#shipping_is_gift").is(":checked"))
	{
		shipping_data["shipping_is_gift"] = "on";
	}
	else
	{
		shipping_data["shipping_is_gift"] = "0";
	}

	intenseLogging("setShipToAddressAndSaveOrder() called");

	// similar to saveDeliveryAddress but no order id is passed
	// and the response to success is to expose the calendar
	// I.E., this is part of initializing a new order
	// A SAVED order will have been created when this call completes.
	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 200000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor_delivered',
			store_id: STORE_DETAILS.id,
			user_id: user_id,
			op: 'save_delivery_address',
			shipping_data: shipping_data,
			order_state: orderState,
			order_id: order_id
		},
		success: function (json) {
			if (json.processor_success)
			{
				intenseLogging("setShipToAddressAndSaveOrder() successful");
				bounce("/backoffice/order-mgr-delivered?order=" + json.order_id + "&atabs=mgr.sessionsTab");
			}
			else
			{
				intenseLogging("setShipToAddressAndSaveOrder: " + json.processor_message);

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

function Reschedule(org_session_id)
{

	intenseLogging("Reschedule() called");

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor_delivered',
			store_id: STORE_DETAILS.id,
			op: 'reschedule',
			session_id: session_id,
			user_id: user_id,
			order_id: order_id,
			saved_booking_id: saved_booking_id,
			org_session_id: org_session_id,
			order_state: orderState
		},
		success: function (json) {
			if (json.processor_success)
			{
				intenseLogging("Reschedule() successful");

				session_id = json.new_session_id;
				$("#curSessionDate").html(json.new_session_time);
				$("#curShipDate").html(json.new_ship_time);
				$("#session").val(json.new_session_id);

				RetrieveCalendar(0);
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

}

function setupForSavedOrder()
{
	RetrieveCalendar(0);
	$("#addPaymentAndActivate").show();
	$("#addPaymentAndActivateButton").attr('disabled', 'disabled');
	$("#items_tab_li").removeClass("disabled");
	$("#items_tab_li").removeClass("unsaved_data_on_tab");
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

function onDeliveryTabSelected()
{

}

function onDeliveryTabDeselected()
{
	/*
	if (!isDeliveryAddressComplete())
	{
		dd_message({
			title: 'Error',
			message: 'Please check the Delivery Address and phone number fields for completeness.'
		});

		return false;
	}
*/
	return true;

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
			saveItems(false, false, false);
		}
	}

	return true;
}

function onPaymentTabSelected()
{
}

function onPaymentTabDeselected()
{
	if (orderState != 'ACTIVE' && (discountChanges || reportingChanges))
	{
		saveDiscounts(false, false);
	}

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
			processor: 'admin_order_mgr_processor_delivered',
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

function updateDiscountOrgValues()
{

	$("#org_coupon_id").val($("#coupon_id").val());

	updateChangeList();

}

function _validate_discounts()
{
	return true;
}

function _validate_address()
{/*
	$thisOrder->orderAddress->firstname = $shippingData['shipping_firstname'];
	$thisOrder->orderAddress->lastname = $shippingData['shipping_lastname'];
	$thisOrder->orderAddress->address_line1 = $shippingData['shipping_address_line1'];
	$thisOrder->orderAddress->address_line2 = $shippingData['shipping_address_line2'];
	$thisOrder->orderAddress->city = $shippingData['shipping_city'];
	$thisOrder->orderAddress->state_id = $shippingData['shipping_state_id'];
	$thisOrder->orderAddress->postal_code = $shippingData['shipping_postal_code'];
	$thisOrder->orderAddress->telephone_1 = (($shipping_phone_number == 'new') ? $shipping_phone_number_new : $shipping_phone_number);
	$thisOrder->orderAddress->address_note = trim(strip_tags($shipping_address_note));
	$thisOrder->orderAddress->email_address = $shipping_email_address;
	$thisOrder->orderAddress->is_gift = (!empty($shippingData['shipping_is_gift']) ? 1 : 0);
*/
	// TODO: phone and email validation

	validationSuccess = true;

	if ($("#shipping_firstname").val().length == 0)
	{
		$("#shipping_firstname").removeClass("is-valid");
		$("#shipping_firstname").addClass("is-invalid");
		validationSuccess = false;
	}
	else
	{
		$("#shipping_firstname").removeClass("is-invalid");
		$("#shipping_firstname").addClass("is-valid");
	}

	if ($("#shipping_lastname").val().length == 0)
	{
		$("#shipping_lastname").removeClass("is-valid");
		$("#shipping_lastname").addClass("is-invalid");
		validationSuccess = false;
	}
	else
	{
		$("#shipping_lastname").removeClass("is-invalid");
		$("#shipping_lastname").addClass("is-valid");
	}

	if ($("#shipping_address_line1").val().length == 0)
	{
		$("#shipping_address_line1").removeClass("is-valid");
		$("#shipping_address_line1").addClass("is-invalid");
		validationSuccess = false;
	}
	else
	{
		$("#shipping_address_line1").removeClass("is-invalid");
		$("#shipping_address_line1").addClass("is-valid");
	}

	if ($("#shipping_city").val().length == 0)
	{
		$("#shipping_city").removeClass("is-valid");
		$("#shipping_city").addClass("is-invalid");
		validationSuccess = false;
	}
	else
	{
		$("#shipping_city").removeClass("is-invalid");
		$("#shipping_city").addClass("is-valid");
	}

	if ($("#shipping_state_id").val().length == 0)
	{
		$("#shipping_state_id").removeClass("is-valid");
		$("#shipping_state_id").addClass("is-invalid");
		validationSuccess = false;
	}
	else
	{
		$("#shipping_state_id").removeClass("is-invalid");
		$("#shipping_state_id").addClass("is-valid");
	}

	if ($("#shipping_state_id").val().length == 0)
	{
		$("#shipping_state_id").removeClass("is-valid");
		$("#shipping_state_id").addClass("is-invalid");
		validationSuccess = false;
	}
	else
	{
		$("#shipping_state_id").removeClass("is-invalid");
		$("#shipping_state_id").addClass("is-valid");
	}

	if ($("#shipping_postal_code").val().length != 5)
	{
		$("#shipping_postal_code").removeClass("is-valid");
		$("#shipping_postal_code").addClass("is-invalid");
		validationSuccess = false;
	}
	else
	{
		$("#shipping_postal_code").removeClass("is-invalid");
		$("#shipping_postal_code").addClass("is-valid");
	}

	return validationSuccess;
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

	var dinner_dollars_discount = $("#plate_points_discount").val();
	if (isNaN(dinner_dollars_discount))
	{
		dinner_dollars_discount = "0.00";
	}

	var coupon_id = 0;
	var coupon_free_menu_item = 0;
	if ($("#coupon_id").val())
	{

		coupon_id = $("#coupon_id").val();
	}

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor_delivered',
			store_id: STORE_DETAILS.id,
			op: 'update_discounts',
			order_id: order_id,
			user_id: user_id,
			direct_order_discount: direct_order_discount,
			plate_points_discount: dinner_dollars_discount,
			coupon_id: coupon_id
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

				if (payOnCompletion)
				{

					if (!_validate_address())
					{
						dd_message({
							title: 'Error',
							message: "Please review and correct the shipping address."
						});

						return;
					}

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
	updateChangeList();
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
	intenseLogging("addPaymentAndActivate() called");

	// always first ensure that discounts are updated on server
	var message = "You are about to activate a Saved order. This will consume inventory and a session slot. Are you sure you want to continue?";
	var changesBlurb = getAbbreviatedSummaryString();
	//message += "<br /><br />" + changesBlurb;

	dd_message({
		div_id: 'activate_confirm',
		title: 'Add Payment and Book Order',
		message: message,
		modal: true,
		width: 400,
		height: 180,
		confirm: function () {
			var discounts_are_valid = saveItems(true, true);
		}
	});

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
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			async: true,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor_delivered',
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
						bounce("/backoffice/order-mgr-thankyou?order=" + json.order_id + '&full_session=true');
					}
					else
					{
						bounce("/backoffice/order-mgr-thankyou?order=" + json.order_id);
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
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			async: true,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor_delivered',
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
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			async: true,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor_delivered',
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
						bounce("/backoffice/order-mgr-thankyou?order=" + json.order_id + '&full_session=true');
					}
					else
					{
						bounce("/backoffice/order-mgr-thankyou?order=" + json.order_id);
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
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			async: true,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor_delivered',
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
		url: '/processor',
		type: 'POST',
		timeout: 90000,
		async: true,
		dataType: 'json',
		data: {
			processor: 'admin_order_mgr_processor_delivered',
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
					bounce("/backoffice/order-mgr-thankyou?order=" + json.order_id + '&full_session=true');
				}
				else
				{
					bounce("/backoffice/order-mgr-thankyou?order=" + json.order_id);
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
	return handleDirectPayment(addOnly, go_to_confirm, token);
}

function RetrieveCalendar(timestamp)
{
	intenseLogging("RetrieveCalendar() called");

	var op = 'retrieve_new';
	if (orderState != 'SAVED')
	{
		op = 'retrieve_for_reschedule';
	}

	$.ajax({
		url: '/processor',
		type: 'GET',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_calendarProcessorDelivered',
			store_id: store_id,
			action: op,
			cur_session_id: session_id,
			timestamp: timestamp,
			service_days: service_days_for_current_zip
		},
		success: function (json) {
			if (json.processor_success)
			{
				intenseLogging("RetrieveCalendar() successful");
				$("#calendar_holder").html(json.data);
				handleSessionSelection();
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

function doReschedule(id, sessionTime)
{
	var curSessionDate = $("#curSessionDate").html();

	dd_message({
		title: 'Reschedule',
		message: '<div style="text-align: center;"><div>From</div><div style="font-weight: bold;">' + curSessionDate + '</div><div>to</div><div style="font-weight: bold;">' + sessionTime + '</div>',
		modal: true,
		confirm: function () {

			var org_session_id = session_id;
			session_id = id;
			Reschedule(org_session_id);

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
				message: "You cannot reschedule to this session as it is in the previous calendar month and all activity for that month has been locked and finalized."
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
	// Obsolete - delivered orders always set a session based on current date and service days
	intenseLogging("onSessionClick() called .................... please investiage should not occur for Shipping order");

}

function monthChange(monthVal)
{
	RetrieveCalendar(monthVal);
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

	var deliveryTaxOrg = Number($('#OEH_delivery_tax_subtotal_org').html());
	var deliveryTax = Number($('#OEH_delivery_tax_subtotal').html());

	if (deliveryTaxOrg == 0 && deliveryTax == 0)
	{
		$('#DeliveryTaxRow').hide();
	}
	else
	{
		$('#DeliveryTaxRow').show();
	}

	var ddOrg = Number($('#OEH_plate_points_order_discount_fee_org').html());
	var dd = Number($('#OEH_plate_points_order_discount_fee').html());
	dd = isNaN(dd) ? 0 : dd;
	ddOrg = isNaN(ddOrg) ? 0 : ddOrg;

	if (ddOrg == 0 && dd == 0)
	{
		$('#dinnerDollarsDiscountRow').hide();
	}
	else
	{
		$('#dinnerDollarsDiscountRow').show();
	}

}

function ShowAllLineItems()
{
	$('#DeliveryFeeRow').show();
	$('#directDiscountRow').show();
	$('#couponDiscountRow').show();
	$('#foodTaxRow').show();
	$('#nonFoodTaxRow').show();
	$('#dinnerDollarsDiscountRow').show();
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

	var htmlStr = "";
	var first = true;

	let editedExistingBoxHTML = {};
	let existingNewBoxHTML = {};

	for (var thisBox in changeList.box_contents)
	{
		if (changeList.box_contents.hasOwnProperty(thisBox))
		{
			let tempHTML = "";

			for (var thisItem in changeList.box_contents[thisBox])
			{
				let item_name = $("#qty_" + thisBox + "_" + thisItem).data('title');

				if (changeList.box_contents[thisBox][thisItem] > 0)
				{
					tempHTML += "-Added " + changeList.box_contents[thisBox][thisItem] + " " + item_name + "<br />";
				}
				else
				{
					tempHTML += "-Removed " + changeList.box_contents[thisBox][thisItem] + " " + item_name + "<br />";
				}
			}

			if (!changeList.boxes[thisBox])
			{
				// an existing boxes contents have changed

				if (first)
				{
					htmlStr += "<h3>Edited Boxes</h3><ul style='margin: 4px;'>";
					first = false;
				}

				var title = $("#box_inst_id_" + box_inst_id).data('box_label');

				editedExistingBoxHTML[thisBox] = "Box: " + title + "<br />" + tempHTML;
			}
			else
			{
				existingNewBoxHTML[thisBox] = tempHTML;
			}
		}

	}

	var first = true;

	for (var box_inst_id in changeList.boxes)
	{
		if (first)
		{
			htmlStr += "<h3>Added Boxes</h3><ul style='margin: 0px; padding: 0px;'>";
			first = false;
		}

		htmlStr += "<li>";

		if (changeList.boxes.hasOwnProperty(box_inst_id))
		{
			var title = $("#box_inst_id_" + box_inst_id).data('box_label');

			if (changeList.boxes[box_inst_id] > 0)
			{
				htmlStr += "Added " + changeList.boxes[box_inst_id] + " <i>" + title + "</i> ";

				if (existingNewBoxHTML[box_inst_id])
				{
					htmlStr += "<br />" + existingNewBoxHTML[box_inst_id];
				}
			}
			else
			{
				htmlStr += "Removed " + changeList.boxes[box_inst_id] * -1 + "<i>" + boxesDeletedFromActiveOrder[box_inst_id] + "</i>";
			}
		}

		htmlStr += "</li>";

	}

	for (var box_inst_id in editedExistingBoxHTML)
	{
		if (editedExistingBoxHTML.hasOwnProperty(box_inst_id))
		{
			htmlStr += editedExistingBoxHTML[box_inst_id];
		}
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

	htmlStr += "<h3>Shipping Order</h3>";

	var first = true;
	for (var box_inst_id in changeList.boxes)
	{
		if (first)
		{
			htmlStr += "<h3>Added Boxes</h3><ul style='margin: 4px;'>";
			first = false;
		}

		htmlStr += "<li>";

		if (changeList.boxes.hasOwnProperty(box_inst_id))
		{
			var title = $("#box_inst_id_" + box_inst_id).data('box_label');

			if (changeList.boxes[box_inst_id] > 0)
			{
				htmlStr += "Added " + changeList.boxes[box_inst_id] + "<i>" + title + "</i>";
			}
			else
			{
				htmlStr += "Removed " + changeList.boxes[box_inst_id] * -1 + "<i>" + boxesDeletedFromActiveOrder[box_inst_id] + "</i>";
			}
		}

		htmlStr += "</li>";

	}

	if (!first)
	{
		htmlStr += "</ul>";
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

		if ($('#use_store_credit').is(':checked'))
		{
			htmlStr += "Store Credit selected for payment.<br />";
		}

	}

	return htmlStr;
}

function updateChangeList()
{

	let itemChanges = false;
	let discountChanges = false;
	let reportingChanges = false;

	let discountOrPaymentChanges = false;
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
	changeList.boxes = {}
	changeList.box_contents = {}

	for (var box_inst in current_box_ids)
	{
		current_box_ids[box_inst] = 0;
	}

	$("[id^='box_inst_id_']").each(function () {
		let list_id = this.id.split("_")[3];
		current_box_ids[list_id] = 1;

		if ($(this).data("contents_are_fixed") == true)
		{
			if (!$(this).data("is_saved"))
			{
				changeList.boxes[list_id] = 1;
				itemChanges = true;
			}
		}
		else
		{
			if (!$(this).data("is_saved"))
			{
				changeList.boxes[list_id] = 1;
				itemChanges = true;

				// also need to see it contents changed
				$("input").filter("[data-box_inst_id='" + list_id + "']").each(function () {
					let curQty = $(this).val();
					let savedQty = $(this).data('claimed_qty');
					let menu_item_id = $(this).data('menu_item_id');

					if (curQty != savedQty)
					{
						if (!changeList.box_contents[list_id])
						{
							changeList.box_contents[list_id] = {};
						}
						changeList.box_contents[list_id][menu_item_id] = curQty - savedQty
					}
				});

			}
			else
			{
				// also need to see it contents changed
				$("input").filter("[data-box_inst_id='" + list_id + "']").each(function () {
					let curQty = $(this).val();
					let savedQty = $(this).data('claimed_qty');
					let menu_item_id = $(this).data('menu_item_id');

					if (curQty != savedQty)
					{
						if (!changeList.box_contents.list_id)
						{
							changeList.box_contents.list_id = {};
						}

						changeList.box_contents.list_id[menu_item_id] = curQty - savedQty
						itemChanges = true;
					}
				});
			}

		}

	});

	for (var box_inst in current_box_ids)
	{
		if (current_box_ids[box_inst] == 0)
		{
			changeList.boxes[box_inst] = -1;
		}
	}

	/*
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
	*/

	// Misc Costs
	$("[data-dd_type='item_field']").each(function () {
		var curVal = $(this).val();
		var orgVal = $(this).data('org_value');

		if (curVal == undefined || curVal == "")
		{
			curVal = "0.00";
		}

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

			itemChanges = true;
		}
		else
		{
			$(this).removeClass('unsaved_data');
		}
	});

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
	$("#plate_points_discount, #direct_order_discount").each(function () {
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

	var htmlStr = "";

	if (needPlatePointsMaxDiscountChangeWarning)
	{
		htmlStr += "<div style='color:red;'>Warning: The amount discountable by Dinner Dollars has changed. You may wish to review and adjust the Dinner Dollars discount.</div>";

	}
	var first = true;
	for (var box_inst_id in changeList.boxes)
	{
		if (first)
		{
			htmlStr += "<h3>Added Boxes</h3><ul style='margin: 4px;'>";
			first = false;
		}

		htmlStr += "<li>";

		if (changeList.boxes.hasOwnProperty(box_inst_id))
		{
			var title = $("#box_inst_id_" + box_inst_id).data('box_label');

			if (changeList.boxes[box_inst_id] > 0)
			{
				htmlStr += "Added " + changeList.boxes[box_inst_id] + "<i>" + title + "</i>";
			}
			else
			{
				htmlStr += "Removed " + changeList.boxes[box_inst_id] * -1 + "<i>" + boxesDeletedFromActiveOrder[box_inst_id] + "</i>";
			}
		}

		htmlStr += "</li>";

	}

	if (!first)
	{
		htmlStr += "</ul>";
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
			processor: 'admin_order_mgr_processor_delivered',
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
	let miscNonFoodSubtotal = 0;
	let serviceFee = 0;
	var num_boxes = 0;

	for (var x in entreeIDToInventoryMap)
	{
		entreeIDToInventoryMap[x].remaining = entreeIDToInventoryMap[x].org_remaining;
		$('#inv_' + x).html(entreeIDToInventoryMap[x].org_remaining + "/serv");
	}

	// ---------------------------------------------------------- Items
	$("[id^='box_inst_id_']").each(function () {
		let box_local_id = this.id.split("_")[2];

		let cost = $(this).data("box_price");
		let items_in_box = $(this).data("number_items_required");
		let servings_in_box = $(this).data("number_servings_required");
		total += (cost * 1);
		main_items_count += items_in_box;
		servings += servings_in_box;
		num_boxes++;
	});

	discounted_total = total;

	// update the entree and serving totals display
	$('#OEH_item_count').html(main_items_count);
	$('#OEH_number_servings').html(servings);
	$('#OEH_number_boxes').html(num_boxes);

	discounted_total = Math.round(discounted_total * 100) / 100;

	// --------------------------------------------------------------------- menu items subtotal
	$('#orderSubTotal').val(formatAsMoney(Math.round(total * 100) / 100));
	$('#OEH_menu_subtotal').html(formatAsMoney(discounted_total));
	$('#orderTotal').val(formatAsMoney(discounted_total));

	// ------------------------------------------------------ food subtotal

	$('#OEH_menu_subtotal').html(formatAsMoney((discounted_total * 1)));

	var newGrandTotal = discounted_total;
	var pre_discounts_grand_total = newGrandTotal;

	// -------------------------------DISCOUNTS-----------------------------------------
	// ---------------------------------------------- Direct Order
	var directDiscountVal = Number(0);

	directDiscount = $('#direct_order_discount');

	if (directDiscount.length)
	{
		directDiscountVal = directDiscount.val();

		if (isNaN(directDiscountVal))
		{
			directDiscountVal = 0;
		}

		if (directDiscountVal > pre_discounts_grand_total)
		{
			directDiscountVal = pre_discounts_grand_total;
			directDiscount.val(directDiscountVal);
		}

		if (directDiscountVal)
		{
			newGrandTotal = formatAsMoney(newGrandTotal - directDiscountVal);
		}

		$('#OEH_direct_order_discount').html(formatAsMoney(directDiscountVal));

	}

	//----------------------------------------------------Dinner Dollars
	discountablePlatePointsAmount = handleDinnerDollars(newGrandTotal);

	// ------------------------------------------------------------------- coupon Discount

	if (couponDiscountMethod == 'PERCENT')
	{
		var newDiscountAmount = Math.floor(pre_discounts_grand_total * (couponDiscountVar));
		newDiscountAmount /= 100;
		$('#couponValue').val(newDiscountAmount);
	}

	var couponDiscountVal = Number(0);
	couponDiscount = $('#couponValue');

	if (couponDiscount.length)
	{
		if (couponDiscount.val() == "")
		{
			couponDiscount.val('0.00');
		}
		couponDiscountVal = couponDiscount.val();
	}

	var couponDiscountValIsDeliveryFee = false;

	if (typeof coupon != 'undefined' && coupon.limit_to_delivery_fee == '1' && coupon.discount_method == 'FLAT')
	{
		var currentDeliveryFee = $("#subtotal_delivery_fee").val();
		if (couponDiscountVal != currentDeliveryFee)
		{
			couponDiscountVal = currentDeliveryFee;
			$('#couponValue').val(couponDiscountVal);

		}

		couponDiscountValIsDeliveryFee = true;
	}

	if (couponDiscountVal)
	{
		newGrandTotal = formatAsMoney(newGrandTotal - couponDiscountVal);
		$('#OEH_coupon_discount').html(formatAsMoney(couponDiscountVal));
	}

	var curPPDiscount = 0;
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

		$('#OEH_plate_points_order_discount_fee').html(formatAsMoney(curPPDiscount));

		newGrandTotal = formatAsMoney(newGrandTotal - curPPDiscount);
	}

	// ----------------------------------------------------------Membership Discount

	if (orderIsEligibleForMembershipDiscount && storeSupportsMembership)
	{
		var couponAdjustment = Number(0);
		if (couponDiscountValIsServiceFee || couponDiscountValIsDeliveryFee)
		{
			couponAdjustment = couponDiscountVal;
		}

		var membership_discount_basis = Number((newGrandTotal * 1) + (couponAdjustment * 1));
		var memberDiscountAmount = Number(membership_discount_basis * (membership_status.discount_var / 100));
		memberDiscountAmount = Math.round(memberDiscountAmount * 100) / 100;

		$("#OEH_membership_discount").html(formatAsMoney(memberDiscountAmount));
		newGrandTotal = formatAsMoney(newGrandTotal - memberDiscountAmount);

	}

	var deliveryFee = Number(0);
	if ($('#subtotal_delivery_fee')[0])
	{
		deliveryFee = Number($('#subtotal_delivery_fee').val());
		$('#OEH_subtotal_delivery_fee').html(formatAsMoney(deliveryFee));

		newGrandTotal = Number((newGrandTotal * 1) + (deliveryFee * 1));

		var newFoodTotal = newGrandTotal - (deliveryFee * 1);
	}

	// --------------------------------------------------------------------- tax
	var newDeliveryTax = 0;
	var newNonFoodTax = 0; // convert to float

	if (!hasDeliveryFeeWithCoupon)
	{
		newDeliveryTax = formatAsMoney(deliveryFee * (curDeliveryTax / 100));
	}

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
			if (isNaN(mealCustomizationFee))
			{
				mealCustomizationFee = 0;
			}
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
			if (isNaN(mealCustomizationFee))
			{
				mealCustomizationFee = 0;
			}
			newServiceTax = formatAsMoney((serviceFee + mealCustomizationFee) * (curServiceTax / 100) + .000001);
		}

		$('#OEH_plate_points_order_discount_food').html(formatAsMoney(0));
		$('#OEH_plate_points_order_discount_fee').html(formatAsMoney(0));
	}

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

	taxElem = document.getElementById('OEH_delivery_tax_subtotal');
	if (taxElem)
	{
		taxElem.innerHTML = formatAsMoney(newDeliveryTax);
	}

	var preTaxTotal = Number(newGrandTotal);
	newGrandTotal = (newGrandTotal * 1) + (newFoodTax * 1) + (newNonFoodTax * 1) + (newDeliveryTax * 1);

	// -------------------------------------------------------------------- grand total
	$('#OEH_grandtotal').html(formatAsMoney(newGrandTotal));
	$('#OEH_new_total').html(formatAsMoney(newGrandTotal));
	$('#OEH_delta').html(formatAsMoney((orderInfoGrandTotal - newGrandTotal) * -1));

	var paymentTotal = 0;
	if ($('#OEH_paymentsTotal').length)
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
				var payment1Amount = null;
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

	var autoAdjustDiv = $('#autoAdjustDiv');
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

		if (servings > 0)
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

function handleDinnerDollars(total)
{

	//var serviceFee = Number(document.getElementById('OEH_subtotal_delivery_fee').value);
	var discountablePlatePointsAmount = total;// + serviceFee.valueOf();

	if (discountablePlatePointsAmount != lastMaxPPDiscountAmount && lastMaxPPDiscountAmount != -1)
	{
		needPlatePointsMaxDiscountChangeWarning = true;
	}

	lastMaxPPDiscountAmount = discountablePlatePointsAmount;

	if (discountablePlatePointsAmount < 0)
	{
		discountablePlatePointsAmount = 0;
	}

	$("#max_plate_points_deduction").html(formatAsMoney(discountablePlatePointsAmount))

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

	var platePointsDiscountOrg = Number($('#OEH_plate_points_discount_org_fee').html());
	var platePointsDiscount = Number($('#OEH_plate_points_order_discount_fee').html());

	if (platePointsDiscountOrg == 0 && platePointsDiscount == 0)
	{
		$('#dinnerDollarsDiscountRow').hide();
	}
	else
	{
		$('#dinnerDollarsDiscountRow').show();
	}

	return discountablePlatePointsAmount;
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

$(document).on('change keyup', '.delivery-input', function (e) {

	deliveryChanges = false;

	$('.delivery-input').each(function () {

		if ($(this).data('org_value'))
		{
			var val = $(this).val();
			var org_value = $(this).data('org_value');

			if (val != org_value)
			{
				deliveryChanges = true;

			}
		}

	});

	updateChangeList();

});
$(document).on('change', '#address_book_select', function (e) {

	let id = $(this).val();

	if (id)
	{
		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_order_mgr_processor_delivered',
				op: 'retrieve_address_from_address_book',
				address_id: id,
				user_id: user_id
			},
			success: function (json) {
				if (json.processor_success)
				{
					$('#shipping_firstname').val(json.address.firstname);
					$('#shipping_lastname').val(json.address.lastname);
					$('#shipping_address_line1').val(json.address.address_line1);
					$('#shipping_address_line2').val(json.address.address_line2);
					$('#shipping_city').val(json.address.city);
					$('#shipping_state_id').val(json.address.state_id);
					$('#shipping_postal_code').val(json.address.postal_code);
					$('#shipping_address_note').val(json.address.address_note);
					$('#shipping_phone_number').val(json.address.telephone_1);
					$('#shipping_gift_email_address').val(json.address.email_address);
				}
				else
				{
					dd_message({
						title: 'Address Error',
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

});