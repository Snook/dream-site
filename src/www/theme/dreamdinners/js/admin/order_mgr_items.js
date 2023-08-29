var itemTabIsDirty = false;

const EXCEED_INV_MSG = 'Your request would exceed the available inventory for this item. <br><br>The item amount has been limited to the amount currently available. <br><br>Please use the Inventory Manager to add inventory if required.';

function bundleSetup()
{
	$(document).on('change', '#selectedBundle', function (e) {

		if ($(this).is(':checked'))
		{
			$.each(bundleItemsBundle, function (id, item)
			{
				$("#bnd_" + id).prop('disabled', false);
			});

			originalAssemblyFee = $("#subtotal_service_fee").val();

			if (session_type == "SPECIAL_EVENT")
			{
				$("#subtotal_service_fee").val("0.00");
			}

		}
		else
		{
			$.each(bundleItemsBundle, function (id, item)
			{
				$("#bnd_" + id).prop('checked', false);
			});

			if (session_type == "SPECIAL_EVENT" && originalAssemblyFee != -1)
			{
				$("#subtotal_service_fee").val(originalAssemblyFee);
			}

		}

		calculateTotal();

	});
}

function bundleItemClick(obj)
{
	var numBundItems = countSelectedBundleItems();
	setItemTabAsDirty();
	if (obj.checked)
	{
		if (numBundItems > intro_servings_required)
		{
			obj.checked = false;
			dd_message({
				title: 'Notice',
				message: "You may only pick " + intro_servings_required + "-servings of Meal Prep Starter Pack items. " + countSelectedBundleItems() + " servings have been selected."
			});
		}
		else
		{
			var item_id = obj.id.substr(4);
			var inv_id = 'inv_' + bundleItemsBundle[item_id].entree_id;
			var curInventory = Number($("#" + inv_id).html());

			var servingsAmt = bundleItemsBundle[item_id].servings_per_item;

			if (curInventory < servingsAmt)
			{
				obj.checked = false;
				alert("There is not enough inventory for this item.");
			}

			var entree_id = inv_id.substr(4);

			$.each(EntreeMap[entree_id], function (size, menu_item)
			{
				if (EntreeMap[entree_id][size] != item_id)
				{
					$('#bnd_' + EntreeMap[entree_id][size]).prop('checked', false);
				}

			});
		}
	}
	else
	{

	}

	calculateTotal();
}

var originalAssemblyFee = -1;

function bundleClick(obj)
{

}

function countSelectedBundleItems()
{
	numBundItems = 0;

	$.each(bundleItemsBundle, function (id, item)
	{

		if ($("#bnd_" + id).is(":checked"))
		{
			numBundItems += parseInt(item.servings_per_item);
		}

	});

	return numBundItems;
}

function subtractPromoFromInventory(promo_id)
{
	if (promo_id && promo_id != 0)
	{
		var entree_id = promo_to_item_map[promo_id];
		var servings = promo_to_servings_map[promo_id];

		if (entree_id)
		{
			var curInventory = entreeIDToInventoryMap[entree_id].remaining;
			curInventory -= servings;
			entreeIDToInventoryMap[entree_id].remaining = curInventory;
		}
	}
}

function setItemTabAsDirty()
{
	itemTabIsDirty = true;
}

/*
 * Obsolete?
 *
 function modify(stepVal)
 {
 var stepElement = document.getElementById('step');
 stepElement.value  = stepVal;
 stepElement.form.submit();
 }
 */
function DTQtyUpdate(input)
{
	if (!input)
	{
		return;
	}

	setItemTabAsDirty();
	var lastQty = 0;
	if (input.value != "" && input.value != "0")
	{
		input.value = Math.abs(input.value);
		lastQty = Number(input.getAttribute("data-lastQty"));
		var entreeID = input.getAttribute("data-entreeID");
		var servings = input.getAttribute("data-servings");
		var curInventory = entreeIDToInventoryMap[entreeID].remaining;

		if (isNaN(input.value) == true || input.value - parseInt(input.value) != 0)
		{
			input.value = 0;
			input.setAttribute("data-lastQty", 0);
		}

		if (input.value > 0)
		{
			var qtyDiff = input.value - lastQty;

			var numAddservings = qtyDiff * servings;
			if (numAddservings > curInventory)
			{
				//over ordering - must cap
				var maxAvail = parseInt(curInventory / servings);
				inputAsNum = lastQty + maxAvail;
				input.value = inputAsNum;
				input.setAttribute("data-lastQty", input.value);

				bootbox.alert(EXCEED_INV_MSG);
			}
			else
			{
				input.setAttribute("data-lastQty", input.value);
			}
		}
	}
	else
	{
		input.setAttribute("data-lastQty", 0);
	}

	calculateTotal();

	var servingQty = $('#OEH_number_servings').html();
	var numberServingsRequired = Number(bundleInfo.number_servings_required);

	if (Number(servingQty) > numberServingsRequired)
	{
		input.value = lastQty;
		dd_message({
			title: 'Alert',
			message: 'Your request has exceeded serving limit of ' + numberServingsRequired + ' for this order.'
		});
		calculateTotal();
	}
}

var orderItems = [];

function rebuildOrderItemsArray()
{
	orderItems = []; // reset

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

			if ((isNaN(itemQty) == false))
			{
				if (itemQty > 0)
				{
					var obj = {
						id: itemId,
						entree_id: $(this).data('entreeid'),
						qty: itemQty,
						price: $(this).data('price'),
						serving_size: $(this).data('servings'),
						pricing_type: $(this).data('pricing_type'),
						intro_item: $(this).data('intro_item'),
						menu_class: $(this).data('menu_class'),
						menu_category_id: $(this).data('menu_category_id'),
						item_title: $(this).data('item_title'),
						dd_type: $(this).data('dd_type')
					};

					orderItems.push(obj);
				}
			}
		}
	});

	return orderItems;
}

function qtyUpdate(input)
{
	if (event.keyCode == 9)
	{
		return;
	}

	if (!input)
	{
		return;
	}

	if (input.max != '')
	{
		if (Number(input.value) > Number(input.max))
		{
			input.value = input.max;
		}
	}

	setItemTabAsDirty();

	if (input.value != "")
	{
		input.value = Math.abs(input.value);
		var lastQty = Number(input.getAttribute("data-lastQty"));
		var entreeID = input.getAttribute("data-entreeID");
		var servings = input.getAttribute("data-servings");
		var curInventory = entreeIDToInventoryMap[entreeID].remaining;
		var menu_item_id = $(input).data('menu_item_id');

		if (isNaN(input.value) == true || input.value - parseInt(input.value) != 0)
		{
			input.value = 0;
			input.setAttribute("data-lastQty", 0);
		}

		if (input.value > 0)
		{
			var qtyDiff = input.value - lastQty;

			var numAddservings = qtyDiff * servings;
			if (numAddservings > curInventory)
			{
				//over ordering - must cap
				var maxAvail = parseInt(curInventory / servings);
				inputAsNum = lastQty + maxAvail;
				input.value = inputAsNum;
				input.setAttribute("data-lastQty", input.value);

				bootbox.alert(EXCEED_INV_MSG);
			}
			else
			{
				input.setAttribute("data-lastQty", input.value);
			}
		}

		if (couponFreeMenuItem == menu_item_id && input.value < 1)
		{
			input.value = 1;
			input.setAttribute("data-lastQty", input.value);

			dd_alert("This item has a coupon attached to it, please remove the coupon first.");
		}

		if (selectedFreeMenuItem == menu_item_id && input.value < 1)
		{
			handleFreeMenuItem($('#coupon_code').val());
		}
	}
	else
	{
		input.setAttribute("data-lastQty", 0);
	}

	calculateTotal();
}

$(document).on('change', '[id^="qty_"], [id^="sbi_"]', function (e) {
	if (e.type != 'keyup')
	{
		// trigger qtyUpdate()
		$(this).trigger('keyup');
	}
});

$(document).on('click', '.btn-inc_qty:not(.disabled)', function (e) {

	let id = $(this).data('menu_item_id');
	let entree_id = $(this).data('entree_id');
	let servings = $(this).data('servings_per_item');
	let box_type = $(this).data('box_type');

	var curInventory = entreeIDToInventoryMap[entree_id].remaining;

	if (servings > curInventory)
	{
		bootbox.alert(EXCEED_INV_MSG);

		calculateTotal();
		return;
	}
	var qtyBox = document.getElementById(box_type + id);
	if (qtyBox)
	{
		setItemTabAsDirty();

		val = parseInt(qtyBox.value);
		if (isNaN(val))
		{
			val = 0;
		}

		if (val + 1 >= qtyBox.max)
		{
			qtyBox.value = qtyBox.max;
		}
		else
		{
			qtyBox.value = val + 1;
		}

	}

	calculateTotal();

	if (isDreamTaste || isFundraiser)
	{
		var numberServingsRequired = Number(bundleInfo.number_servings_required);

		if (numberServingsRequired)
		{
			var servingQty = $('#OEH_number_servings').html();
			if (Number(servingQty) > numberServingsRequired)
			{
				qtyBox.value--;
				dd_message({
					title: 'Alert',
					message: 'Your request has exceeded serving limit of ' + numberServingsRequired + ' for this order.'
				});
				calculateTotal();
			}
		}
	}

});

$(document).on('click', '.btn-dec_qty:not(.disabled)', function (e) {

	let id = $(this).data('menu_item_id');
	let entree_id = $(this).data('entree_id');
	let servings = $(this).data('servings_per_item');
	let box_type = $(this).data('box_type');

	var qtyBox = document.getElementById(box_type + id);
	if (qtyBox)
	{
		setItemTabAsDirty();

		val = parseInt(qtyBox.value);
		if (isNaN(val))
		{
			val = 0;
		}
		qtyBox.value = val - 1;
	}

	if (parseInt(qtyBox.value) == -1)
	{
		qtyBox.value = 0;
	}

	if (couponFreeMenuItem == id && qtyBox.value < 1)
	{
		qtyBox.value = 1;

		dd_alert("This item has a coupon attached to it, please remove the coupon first.");
	}

	if (selectedFreeMenuItem == id && qtyBox.value < 1)
	{
		handleFreeMenuItem($('#coupon_code').val());
	}

	calculateTotal();

});

function incAddonQty(id)
{
	var qtyBox = document.getElementById("qna_" + id);

	var curInventory = entreeIDToInventoryMap[id].remaining;

	if (1 > curInventory)
	{
		bootbox.alert(EXCEED_INV_MSG);

		calculateTotal();
		return;
	}

	if (qtyBox)
	{
		val = parseInt(qtyBox.value);
		if (isNaN(val))
		{
			val = 0;
		}

		if (val + 1 == 100)
		{
			qtyBox.value = 99;
		}
		else
		{
			qtyBox.value = val + 1;
		}
	}
	calculateTotal();
}

function costInput(obj)
{
	// TODO: validation

	setItemTabAsDirty();

	calculateTotal();
}

function decAddonQty(id)
{
	var qtyBox = document.getElementById("qna_" + id);
	if (qtyBox)
	{
		val = parseInt(qtyBox.value);
		if (isNaN(val))
		{
			val = 0;
		}
		qtyBox.value = val - 1;
	}

	if (parseInt(qtyBox.value) == -1)
	{
		qtyBox.value = 0;
	}
	calculateTotal();
}