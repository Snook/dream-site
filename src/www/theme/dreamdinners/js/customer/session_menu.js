var neededQuantity = 36;

if (typeof order_minimum !== 'undefined' && typeof order_minimum.minimum !== 'undefined')
{
	neededQuantity = order_minimum.minimum;
}

function canIncrementBundleSubItem(input, selector)
{

	let parentItemInfo = menuItemInfo.mid[$(input).data('parent_item')];
	let parentNumItemsRequired = parseInt(parentItemInfo.number_items_required);
	let itemInfo = menuItemInfo.mid[parentItemInfo.menu_item_id].sub_item[$(input).data('menu_item_id')];
	let groupID = parseInt(itemInfo.bundle_to_menu_item_group_id) || 0;

	// check that we aren't going over the total
	let total_org = 0;
	let total_current = 0;
	let groupInfo = [];

	$(selector + '[data-parent_item="' + parentItemInfo.menu_item_id + '"]').each(function () {

		let inputItemInfo = menuItemInfo.mid[parentItemInfo.menu_item_id].sub_item[$(this).data('menu_item_id')];
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
	if (total_current > parentNumItemsRequired)
	{
		return 'Number of items for the bundle has been reached';
	}

	// check if over the group total
	if (parentItemInfo.bundle_groups.length > 0)
	{
		if (groupInfo[groupID].total_current > parentItemInfo.bundle_groups[groupID].number_items_required)
		{
			return 'Number of items for the group has been reached';
		}
	}

	// all else
	return true;

}

function restoreButtonState(menu_item_id)
{
	var entree_id = menuItemInfo['mid'][menu_item_id].entree_id;
	var total_servings = 0;

	var myTypes = menuItemInfo['entree'][entree_id];

	// total servings used so far
	for (key in myTypes)
	{

		var thisType = menuItemInfo['mid'][key];
		var servingSize = (thisType.servings_per_item * 1);
		var thisTypeServings = thisType.qty_in_cart * servingSize;
		total_servings += thisTypeServings;
	}

	var left = menuItemInfo['mid'][menu_item_id].remaining_servings - total_servings;

	for (key in myTypes)
	{
		var thisType = menuItemInfo['mid'][key];
		var servingSize = (thisType.servings_per_item * 1);

		if (servingSize <= left)
		{
			$('.add-to-cart[data-menu_item_id="' + key + '"]').removeClass('disabled');
			$('.add-to-cart[data-menu_item_id="' + key + '"] > .price').html(menuItemInfo['mid'][key].price);

			// only restore the + icon on the button not being clicked
			if (key != menu_item_id)
			{
				$('.add-to-cart[data-menu_item_id="' + key + '"] > .fas').show();
			}
		}
	}
}

function inventoryCheck(menu_item_id)
{
	var hasInventory = true;

	// sanity check anytime this function is called
	if (menuItemInfo['mid'][menu_item_id].qty_in_cart < 0)
	{
		menuItemInfo['mid'][menu_item_id].qty_in_cart = 0;
	}

	var entree_id = menuItemInfo['mid'][menu_item_id].entree_id;
	var total_servings = 0;
	var newServingsThisInstance = menuItemInfo['mid'][menu_item_id].servings_per_item * 1; // only adding 1 at a time so no " * qty "

	var myTypes = menuItemInfo['entree'][entree_id];

	// total servings used so far
	for (key in myTypes)
	{

		var thisType = menuItemInfo['mid'][key];
		var servingSize = (thisType.servings_per_item * 1);
		var thisTypeServings = thisType.qty_in_cart * servingSize;
		total_servings += thisTypeServings;
	}

	if (total_servings + newServingsThisInstance > menuItemInfo['mid'][menu_item_id].remaining_servings)
	{
		modal_message({
			title: 'Inventory Limit Reached',
			message: "Please contact your store if you wish to purchase more of this item. The store's available inventory limit has been reached."
		});

		menuItemInfo['mid'][menu_item_id].qty_in_cart += 1;

		hasInventory = false;
	}
	else
	{
		menuItemInfo['mid'][menu_item_id].qty_in_cart += 1;
	}

	var left = menuItemInfo['mid'][menu_item_id].remaining_servings - (total_servings + newServingsThisInstance);

	for (key in myTypes)
	{
		var thisType = menuItemInfo['mid'][key];
		var servingSize = (thisType.servings_per_item * 1);

		if (servingSize > left)
		{
			$('.add-to-cart[data-menu_item_id="' + key + '"]').addClass('disabled');
			$('.add-to-cart[data-menu_item_id="' + key + '"] > .price').text('Sold out');
			$('.add-to-cart[data-menu_item_id="' + key + '"] > .fas').hide();
		}
	}

	return hasInventory;
}

function countCoreItemsServings()
{
	var servingsOrdered = 0;

	$.each(menuItemInfo['mid'], function (key, item_info) {

		// sanity check anytime this function is called
		if (item_info['qty_in_cart'] < 0)
		{
			item_info['qty_in_cart'] = 0;

			menuItemInfo['mid'][key].qty_in_cart = 0;
		}

		if (item_info['qty_in_cart'] > 0)
		{
			if (item_info['category_id'] != '9')
			{
				if (item_info['item_contributes_to_minimum_order'])
				{
					servingsOrdered += Number(item_info['servings_per_item']) * Number(item_info['qty_in_cart']);
				}
			}
		}

	});

	return servingsOrdered;
}

function countItemsServings(includeFreezer)
{
	var servingsOrdered = 0;
	var totalMealNights = 0;

	if (typeof includeFreezer === 'undefined')
	{
		includeFreezer = false;
	}

	$.each(menuItemInfo['mid'], function (key, item_info) {

		// sanity check anytime this function is called
		if (item_info['qty_in_cart'] < 0)
		{
			item_info['qty_in_cart'] = 0;

			menuItemInfo['mid'][key].qty_in_cart = 0;
		}

		if (item_info['qty_in_cart'] > 0)
		{

			if (includeFreezer)
			{
				servingsOrdered += Number(item_info['servings_per_item'] * item_info['qty_in_cart']);
			}
			else
			{
				if (item_info['category_id'] != 9)
				{
					if (item_info['item_contributes_to_minimum_order'])
					{
						if (item_info['is_bundle'] === '1')
						{
							servingsOrdered += Number(item_info['qty_in_cart']) * Number(item_info['servings_per_item']);
						}
						else
						{
							servingsOrdered = Number(item_info['servings_per_item']) * Number(item_info['qty_in_cart']);
						}
					}
				}
			}
			if (item_info['category_id'] != 9)
			{
				if (item_info['item_contributes_to_minimum_order'])
				{
					if (item_info['is_bundle'] === '1')
					{
						totalMealNights += Number(item_info['qty_in_cart']) * Number(item_info['number_items_required']);
					}
					else
					{
						totalMealNights += Number(item_info['qty_in_cart']);
					}
				}
			}

		}

	});

	if (totalMealNights > 0)
	{
		$('.total-meal-nights-plural').show();
		$('.total-meal-nights').text(totalMealNights);

		if (totalMealNights == 1)
		{
			$('.total-meal-nights-plural').hide();
		}

		$('.meal-nights').show();
		$('.meal-select').hide();
	}
	else
	{
		$('.total-meal-nights').text('0');
		$('.total-meal-nights-plural').show();

		$('.meal-select').show();
		$('.meal-nights').hide();
	}

	return servingsOrdered;
}

function countItems(includeFreezer)
{
	var itemsOrdered = 0;
	var totalMealNights = 0;

	if (typeof includeFreezer === 'undefined')
	{
		includeFreezer = false;
	}

	$.each(menuItemInfo['mid'], function (key, item_info) {

		// sanity check anytime this function is called
		if (item_info['qty_in_cart'] < 0)
		{
			item_info['qty_in_cart'] = 0;

			menuItemInfo['mid'][key].qty_in_cart = 0;
		}

		if (item_info['qty_in_cart'] > 0)
		{
			if (includeFreezer)
			{
				itemsOrdered += Number(item_info['qty_in_cart']);
			}
			else
			{
				if (item_info['category_id'] != 9)
				{
					if (item_info['item_contributes_to_minimum_order'])
					{
						if (item_info['is_bundle'] === '1')
						{
							itemsOrdered += Number(item_info['qty_in_cart']) * Number(item_info['number_items_required']);
						}
						else
						{

							itemsOrdered += Number(item_info['qty_in_cart']) * Number(item_info['item_count_per_item']) ;
						}
					}
				}
			}

			if (item_info['category_id'] != 9)
			{
				if (item_info['item_contributes_to_minimum_order'])
				{
					if (item_info['is_bundle'] === '1')
					{
						totalMealNights += Number(item_info['qty_in_cart']) * Number(item_info['number_items_required']);
					}
					else
					{
						totalMealNights += Number(item_info['qty_in_cart']) * Number(item_info['item_count_per_item']) ;
					}
				}
			}
		}
	});

	if (totalMealNights > 0)
	{
		$('.total-meal-nights-plural').show();
		$('.total-meal-nights').text(totalMealNights);

		if (totalMealNights == 1)
		{
			$('.total-meal-nights-plural').hide();
		}

		$('.meal-nights').show();
		$('.meal-select').hide();
	}
	else
	{
		$('.total-meal-nights').text('0');
		$('.total-meal-nights-plural').show();

		$('.meal-select').show();
		$('.meal-nights').hide();
	}

	return itemsOrdered;
}

function countCoreItems()
{
	var itemsOrdered = 0;

	$.each(menuItemInfo['mid'], function (key, item_info) {

		// sanity check anytime this function is called
		if (item_info['qty_in_cart'] < 0)
		{
			item_info['qty_in_cart'] = 0;
			menuItemInfo['mid'][key].qty_in_cart = 0;
		}

		if (item_info['qty_in_cart'] > 0)
		{
			if (item_info['category_id'] != 9)
			{
				if (item_info['item_contributes_to_minimum_order'])
				{
					if (item_info['is_bundle'] === '1')
					{
						itemsOrdered += Number(item_info['qty_in_cart']) * Number(item_info['number_items_required']);
					}
					else
					{
						itemsOrdered += Number(item_info['qty_in_cart']) * Number(item_info['item_count_per_item']) ;
					}
				}
			}
		}
	});

	return itemsOrdered;
}

function updateServingsCountAndCheckoutButton()
{
	// update bundle item state
	$('.add-bundle-to-cart').each(function () {

		$(this).addClass('disabled');

		let menu_item_id = $(this).data('menu_item_id');
		let parentItemInfo = menuItemInfo.mid[menu_item_id];
		let parentNumItemsRequired = parseInt(parentItemInfo.number_items_required);

		let total_current = 0;

		$('.bundle-subitem-qty[data-parent_item="' + menu_item_id + '"]').each(function () {

			let this_cur = parseInt($(this).val()) || 0;

			total_current += this_cur;

		});

		$('.bundle-subitem-total[data-menu_item_id="' + menu_item_id + '"]').text(total_current);

		if (parentNumItemsRequired == total_current)
		{
			$(this).removeClass('disabled');
		}

	});

	if (typeof menuItemInfo != "undefined")
	{
		var quantityOrdered = 0;

		var coreQuantityOrdered = 0;

		if (typeof menu_view === 'undefined' || menu_view == null)
		{
			menu_view = '';
		}

		if (typeof (order_minimum) != 'undefined' && typeof (order_minimum.minimum) != 'undefined' && order_minimum.minimum_type == 'ITEM')
		{

			quantityOrdered = countItems(menu_view == 'session_menu_freezer');
			coreQuantityOrdered = countCoreItems();
		}
		else
		{
			quantityOrdered = countItemsServings(menu_view == 'session_menu_freezer');
			coreQuantityOrdered = countCoreItemsServings();
		}

		var shouldEnableCheckoutButton = false;

		if (typeof (order_minimum) == 'undefined')
		{
			order_minimum = {};
		}
		var minServings = typeof (order_minimum.minimum) == 'undefined' ? 36 : order_minimum.minimum;
		if (order_type == 'INTRO' || order_type == 'DREAM_TASTE' || order_type == 'FUNDRAISER')
		{
			minServings = number_servings_required;
		}
		else
		{
			if (order_minimum.is_applicable == false)
			{
				if (order_minimum.has_freezer_inventory == true)
				{
					if (menu_view == 'session_menu_freezer')
					{
						minServings = 1;
					}
					else
					{
						minServings = 0;
					}

				}
				else
				{
					minServings = 1;
				}
			}
		}

		/* handle progress bar */
		// var recommended_servings = $('.progress-min').data('recommended_servings');

		var recommended_servings = (typeof order_minimum.minimum) == 'undefined' ? 36 : order_minimum.minimum;
		var progress_min = 0;

		var remaining = recommended_servings - quantityOrdered;
		var more = ' more '
		if (quantityOrdered == 0)
		{
			more = ' ';
		}
		if (remaining > 0)
		{
			$('.remaining-note').showFlex();
			$('.remaining').text(remaining + more);
			if (remaining == 1)
			{
				$('.plural').hide();
			}
			else
			{
				$('.plural').show();
			}
		}
		else
		{
			$('.remaining-note').hideFlex();
		}

		if (quantityOrdered > 0)
		{

			progress_min = Math.round((quantityOrdered / minServings) * 100);

			if (progress_min >= 100)
			{
				progress_min = 100;

				$('.progress-min').removeClass('bg-gray-dark').addClass('bg-green');
				$('.progress-star').removeClass('text-gray').addClass('text-green');
				$('.required-servings-note').hideFlex();
			}
			else
			{
				$('.progress-min').removeClass('bg-green').addClass('bg-gray-dark');
				$('.progress-star').removeClass('text-green').addClass('text-gray');
				$('.required-servings-note').showFlex();
			}
		}

		var progress_rec = 0;
		if (progress_min == 100)
		{
			var rec_servings = recommended_servings - minServings;

			if (quantityOrdered > minServings)
			{
				var quantityOrdered_rec = quantityOrdered - minServings;

				if (quantityOrdered_rec > 0)
				{
					progress_rec = Math.round((quantityOrdered_rec / rec_servings) * 100);

					if (progress_rec > 100)
					{
						progress_rec = 100;
					}
				}
			}
		}

		var progress_add = 0;
		if (progress_rec == 100)
		{
			var quantityOrdered_add = quantityOrdered - recommended_servings;

			if (quantityOrdered_add > 0)
			{
				progress_add = Math.round((quantityOrdered_add / (minServings * 3)) * 100);

				if (progress_add > 100)
				{
					progress_add = 100;
				}
			}
		}

		if (progress_min > 0)
		{
			$('.progress-min-value').text(progress_min + '%');
		}
		else
		{
			$('.progress-min-value').text('');
		}

		$('.progress-min').css({
			'width': progress_min + '%'
		}).attr('aria-valuenow', progress_min);

		$('.progress-rec').css({
			'width': progress_rec + '%'
		}).attr('aria-valuenow', progress_rec);

		$('.progress-add').css({
			'width': progress_add + '%'
		}).attr('aria-valuenow', progress_add);

		/* end handle progress bar */

		if (menu_view == 'session_menu_freezer' && order_minimum.is_applicable == false)
		{
			//Core not required
			neededQuantity = minServings - quantityOrdered;
		}
		else
		{
			neededQuantity = minServings - coreQuantityOrdered;
		}

		if (order_minimum.is_applicable == false)
		{
			if (order_minimum.has_freezer_inventory == true)
			{
				if (menu_view == 'session_menu_freezer')
				{
					neededQuantity = neededQuantity;
				}
				else
				{
					neededQuantity = 0;
				}
				$('.meals-list, .cart-total-div').slideDown();
				$('.progress-star').hide();
			}
		}

		if (neededQuantity <= 0)
		{
			neededQuantity = 0;

			shouldEnableCheckoutButton = true;
		}

		if (shouldEnableCheckoutButton)
		{
			$('.continue-btn').removeClass('disabled');
			$('.continue-btn-div').slideDown();
		}
		else
		{
			$('.continue-btn').addClass('disabled');
			$('.continue-btn-div').slideUp();
		}

		if (quantityOrdered == "")
		{
			quantityOrdered = "0";
		}

		//update subtotal
		let cart_total = 0;

		$.each(menuItemInfo['mid'], function (key, item_info) {

			if (item_info['qty_in_cart'] > 0)
			{
				cart_total += Number(item_info['price'] * item_info['qty_in_cart']);
			}

			if (typeof item_info['sub_item'] !== 'undefined')
			{
				$.each(item_info['sub_item'], function (key, sub_item_info) {

					cart_total -= Number(sub_item_info['price'] * sub_item_info['qty_in_cart']);

				});
			}

		});

		if (coupon != false && coupon.limit_to_mfy_fee != 1 && coupon.limit_to_delivery_fee != 1)
		{
			cart_total = cart_total - coupon.coupon_code_discount_total;
			$('.coupon-code-total').text(formatAsMoney(coupon.coupon_code_discount_total));
		}

		if (customization != false && typeof customization.cost != 'undefined' )
		{
			let cost = parseFloat(customization.cost);
			cart_total = cart_total + cost;
		}



		if (order_type == 'STANDARD' || order_type == 'SPECIAL_EVENT')
		{
			$('.cart-total').text(formatAsMoney(cart_total));
		}

		// update coupon display
		if (coupon.limit_to_mfy_fee == 1 || coupon.limit_to_delivery_fee == 1)
		{
			$('.coupon-code-total-service-div').show();
			$('.coupon-code-total-div').hide();
		}
		else
		{
			$('.coupon-code-total-service-div').hide();
			$('.coupon-code-total-div').show();
		}

		return quantityOrdered;

	}

}

function update_cart(menu_item_id, action)
{
	if (action == 'del')
	{
		menuItemInfo['mid'][menu_item_id].qty_in_cart -= 1;

		if (menuItemInfo['mid'][menu_item_id].qty_in_cart < 0)
		{
			menuItemInfo['mid'][menu_item_id].qty_in_cart = 0;
		}

		restoreButtonState(menu_item_id);
	}

	let menuItem = menuItemInfo['mid'][menu_item_id];
	let menuItemEntree = menuItemInfo['entree'][menuItem.entree_id];
	let sub_items = {};

	if (!$.isNumeric(menuItem.qty_in_cart))
	{
		menuItem.qty_in_cart = 0;
	}

	if (menuItem.is_bundle == '1')
	{
		$('.bundle-subitem-qty[data-parent_item="' + menu_item_id + '"]').each(function () {

			let mid = $(this).data('menu_item_id');
			let qty = parseInt($(this).val()) || 0;

			menuItemInfo['mid'][menu_item_id].sub_item[mid].qty_in_cart += parseInt($(this).val()) || 0;

			if (qty > 0)
			{
				sub_items[mid] = qty;
			}

		});
	}

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_item_processor',
			op: 'update',
			item: menu_item_id,
			is_bundle: sub_items,
			qty: menuItem.qty_in_cart,
			order_type: order_type
		},
		success: function (json) {

			if (json.processor_success)
			{
				if (order_type != json.order_type)
				{
					order_type = json.order_type;
				}

				customization.cost = json.subtotal_meal_customization_fee;
				coupon.coupon_code_discount_total = json.coupon_code_discount_total;

				if (order_type == 'STANDARD' || order_type == 'SPECIAL_EVENT')
				{
					updateServingsCountAndCheckoutButton();

					if (menuItem.qty_in_cart > 0)
					{
						$('.add-to-cart[data-menu_item_id="' + menu_item_id + '"] > .fas, .configure-bundle[data-menu_item_id="' + menu_item_id + '"] > .fas').hide();
						$('.add-to-cart[data-menu_item_id="' + menu_item_id + '"] > .cart-amount, .configure-bundle[data-menu_item_id="' + menu_item_id + '"] > .cart-amount').text('(' + menuItem.qty_in_cart + ')').show();
					}
					else
					{
						$('.add-to-cart[data-menu_item_id="' + menu_item_id + '"] > .fas, .configure-bundle[data-menu_item_id="' + menu_item_id + '"] > .fas').show();
						$('.add-to-cart[data-menu_item_id="' + menu_item_id + '"] > .cart-amount, .configure-bundle[data-menu_item_id="' + menu_item_id + '"] > .cart-amount').text('0').hide();
					}

				}
				else if (order_type == 'INTRO')
				{
					// handle allowing only one size choice
					$.each(menuItemEntree, function (mid, pricing_type) {

						if (menuItem.qty_in_cart > 0)
						{
							$('.add-to-cart[data-menu_item_id="' + mid + '"]').addClass('disabled');
							$('.add-to-cart[data-menu_item_id="' + mid + '"] > .fas').removeClass('fa-plus text-white');
							$('.add-to-cart[data-menu_item_id="' + menu_item_id + '"] .fas').addClass('fa-check text-dark');
						}
						else
						{
							$('.add-to-cart[data-menu_item_id="' + mid + '"]').removeClass('disabled');
							$('.add-to-cart[data-menu_item_id="' + mid + '"] > .fas').removeClass('fa-check text-dark').addClass('fa-plus text-white');
						}

					});
				}

				if (menuItem.qty_in_cart > 0)
				{
					if ($('[data-cart_menu_item="' + menu_item_id + '"]').length)
					{
						$('[data-cart_menu_item="' + menu_item_id + '"]').replaceWith(json.cart_update);
					}
					else
					{
						$(json.cart_update).appendTo($('.meals-list')).hide().slideDown();
						$('.meals-list, .cart-total-div').slideDown();
					}
					$('.clear-all-cart-items-div').show();
				}
				else
				{
					$('.add-to-cart[data-menu_item_id="' + menu_item_id + '"] > .fas').show();

					$('[data-cart_menu_item="' + menu_item_id + '"]').slideUp('slow', function () {

						$(this).remove();

						if (!$('.meals-list').html().trim().length)
						{
							if (order_minimum.is_applicable == false)
							{
								if (order_minimum.has_freezer_inventory != true)
								{
									$('.meals-list, .cart-total-div').slideUp('slow', function () {
										$('.msg_empty_cart').slideDown();
									});
								}
							}
						}
					});
				}

				if ($('.meals-list').html().trim().length)
				{
					$('.msg_empty_cart').slideUp('slow', function () {
						$('.meals-list, .cart-total-div').slideDown();
					});
				}

				//	$('.desktop-cart-div').find(".meals-list").animate({scrollTop: $('.desktop-cart-div').find(".meals-list").prop("scrollHeight")}, 2000);

				$('.btn-spinning').removeClass('btn-spinning');
				$('.ld-spin').remove();

				updateServingsCountAndCheckoutButton();
			}
			else
			{
				// action failed, return qty in cart to pre-click state
				if (action == 'del')
				{
					menuItemInfo['mid'][menu_item_id].qty_in_cart += 1;
				}
				else
				{
					menuItemInfo['mid'][menu_item_id].qty_in_cart -= 1;

					if (menuItemInfo['mid'][menu_item_id].qty_in_cart < 0)
					{
						menuItemInfo['mid'][menu_item_id].qty_in_cart = 0;
					}
				}

				$('.btn-spinning').removeClass('btn-spinning');
				$('.ld-spin').remove();

				var err_message = "Error Adding item to cart. If you continue to receive this message please contact your store.";
				if (json.result_code == 5)
				{
					err_message = "You have added the number of dinners allowed for this special event or session type. Please remove a dinner from your cart to add this dinner.";
				}

				modal_message({
					title: "Error",
					message: err_message,
					callback: function () {
						//reload the page so out of stock or other issues will be made visible.
						bounce(window.location.href);
					}
				});
			}
		},
		error: function (objAJAXRequest, strError) {

			// action failed, return qty in cart to pre-click state
			if (action == 'del')
			{
				menuItemInfo['mid'][menu_item_id].qty_in_cart += 1;
			}
			else
			{
				menuItemInfo['mid'][menu_item_id].qty_in_cart -= 1;

				if (menuItemInfo['mid'][menu_item_id].qty_in_cart < 0)
				{
					menuItemInfo['mid'][menu_item_id].qty_in_cart = 0;
				}
			}

			$('.btn-spinning').removeClass('btn-spinning');
			$('.ld-spin').remove();

			modal_message({
				title: "Error",
				message: 'Unexpected error, if you continue to receive this message please contact your store.'
			})
		}
	});

}

$(function () {

	//	var type = typeof menuItemInfo;

	if (typeof menuItemInfo != 'undefined' && menuItemInfo != null)
	{
		// Note: initial sold out state does not factor the items in the cart so

		//  check for insufficiency for each item.  This will udpate and disable any items that are out
		$.each(menuItemInfo['entree'], function (key, item_info) {
			var total_servings = 0;
			var myTypes = menuItemInfo['entree'][key];

			var hasHalf = false;
			var hasFull = false;
			var halfID = false;
			var fullID = false;
			// total servings used so far
			for (itemkey in myTypes)
			{
				if (myTypes[itemkey] == "HALF")
				{
					hasHalf = true;
					halfID = itemkey;
				}

				if (myTypes[itemkey] == "FULL")
				{
					hasFull = true;
					fullID = itemkey;
				}

				var thisType = menuItemInfo['mid'][itemkey];
				var servingSize = (thisType.servings_per_item * 1);
				var thisTypeServings = thisType.qty_in_cart * servingSize;
				total_servings += thisTypeServings;
			}

			let left = menuItemInfo['mid'][itemkey].remaining_servings - total_servings;

			for (itemkey in myTypes)
			{
				var thisType = menuItemInfo['mid'][itemkey];
				var servingSize = (thisType.servings_per_item * 1);

				if (left < servingSize)
				{
					$('.add-to-cart[data-menu_item_id="' + itemkey + '"]').addClass('disabled');
					$('.add-to-cart[data-menu_item_id="' + itemkey + '"] > .price').text("Sold out");
				}
			}
		});
	}
	// Calculate page
	updateServingsCountAndCheckoutButton();

	// handle cart addition
	$(document).on('click change', '.add-to-cart:not(.disabled)', function (e) {

		var menu_item_id = $(this).data('menu_item_id');
		var menuItem = menuItemInfo['mid'][menu_item_id];
		var menuItemEntree = menuItemInfo['entree'][menuItem.entree_id];

		if (inventoryCheck(menu_item_id))
		{
			continue_process = true;

			if (order_type == 'DREAM_TASTE' || order_type == "FUNDRAISER")
			{
				servings = countItemsServings();

				if (servings > number_servings_required)
				{
					this.checked = false;

					if (order_type == 'DREAM_TASTE')
					{
						modal_message({
							title: 'Event Offer',
							message: 'You have completed your Meal Prep Workshop order and are ready to checkout.'
						});
					}
					else
					{
						modal_message({
							title: 'Event Offer',
							message: 'You have completed your Fundraiser order and are ready to checkout.'
						});
					}

					menuItemInfo['mid'][menu_item_id].qty_in_cart -= 1;
					servings = countItemsServings(); // force display to correct meal number

					continue_process = false;
				}
			}

			if (order_type == 'INTRO')
			{
				var entree_total = 0;

				$.each(menuItemEntree, function (mid, pricing_type) {

					entree_total += menuItemInfo['mid'][mid].qty_in_cart;

				});

				if (entree_total >= 2)
				{
					modal_message({
						title: 'Meal Prep Starter Pack',
						message: 'Meal Prep Starter Pack orders are limited to only 1 large or 1 medium selection per meal.'
					});

					menuItemInfo['mid'][menu_item_id].qty_in_cart -= 1;
					continue_process = false;
				}

				servings = countItemsServings();
				servings_before = servings - menuItemInfo['mid'][menu_item_id].servings_per_item;

				if (servings_before == number_servings_required)
				{
					$(this).removeClass('disabled');

					modal_message({
						title: 'Meal Prep Starter Pack',
						message: 'You have completed your Meal Prep Starter Pack and are ready to checkout.'
					});

					menuItemInfo['mid'][menu_item_id].qty_in_cart -= 1;
					continue_process = false;
				}
				else if (servings > number_servings_required)
				{
					modal_message({
						title: 'Meal Prep Starter Pack',
						message: 'You have 1 medium meal left to complete your Meal Prep Starter Pack. You may order six medium dinners or three large dinners (or a combination of both sizes).'
					});

					menuItemInfo['mid'][menu_item_id].qty_in_cart -= 1;
					continue_process = false;
				}
			}

			if (continue_process)
			{
				update_cart(menu_item_id, 'add');

				/* animate cart */
				if ((!USER_PREFERENCES || USER_PREFERENCES.SESSION_MENU_CART_FLY_TO.value > 0) && menuItemInfo['mid'][menu_item_id].category_id != 9)
				{
					var cart = false;

					$('.progress-min').each(function (e) {

						if ($(this).is(':visible'))
						{
							cart = $(this).parent();
						}

					});

					if (cart)
					{
						var imgtodrag = $('[data-recipe_img="' + menuItemInfo['mid'][menu_item_id].recipe_id + '"]').eq(0);

						if (imgtodrag)
						{
							var imgclone = imgtodrag.clone();

							$(imgclone).removeClass('card-img-top').addClass('shadow border-green-light');

							imgclone.offset({
								top: e.pageY - 20,
								left: e.pageX - 40
							})
								.css({
									'position': 'absolute',
									'width': 80,
									'border': '2px solid',
									'z-index': '1500',
									'pointer-events': 'none'
								})
								.appendTo($('body'))
								.animate({
									'top': cart.offset().top + 1,
									'left': cart.offset().left + ((cart.width() / 2) - 20),
									'width': 40
								}, 1000, 'easeInOutExpo');

							imgclone.hide("puff", {}, 500, function () {
								$(this).detach()
							});
						}
					}
				}
			}
			else
			{
				$('.btn-spinning').removeClass('btn-spinning');
				$('.ld-spin').remove();
			}
		}
	});

	// handle item removal
	$(document).on('click', '.remove-from-cart', function (e) {
		var menu_item_id = $(this).data('menu_item_id');

		if (menuItemInfo.mid[menu_item_id].is_bundle == '1' && menuItemInfo.mid[menu_item_id].qty_in_cart > 1)
		{
			bootbox.confirm("Due to the complexity of this menu item you will need to re-add this menu item with your desired selections. Do you wish to continue?", function (result) {
				if (result == true)
				{
					menuItemInfo.mid[menu_item_id].qty_in_cart = 0;

					$('.bundle-subitem-qty[data-parent_item="' + menu_item_id + '"]').each(function () {

						let mid = $(this).data('menu_item_id');

						menuItemInfo['mid'][mid].qty_in_cart = 0;

					});

					update_cart(menu_item_id, 'del');
				}
			});
		}
		else
		{
			update_cart(menu_item_id, 'del');
		}

		if ($('.session_menu-meals-list').children().length <= 2)
		{
			$('.clear-all-cart-items-div').hide();
		}

	});


	$(document).on('click', '.sm-change-menu', function (e) {
		$('.sm-row-change-menu').toggleFlex();
	});

	$(document).on('click', '.sm-change-menu-starter', function (e) {
		$('.sm-row-change-menu-starter').toggleFlex();
	});

	$(document).on('click', '.sm-customization-available', function (e) {
		$('.sm-row-customization-available').toggleFlex();
	});

	$(document).on('click', '.link-dinner-details', function (e) {

		e.preventDefault();

		var menu_id = $(this).data('menu_id');
		var menu_item_id = $(this).data('menu_item_id');
		var store_id = $(this).data('store_id');

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'menu_item',
				op: 'find_item',
				menu_id: menu_id,
				menu_item_id: menu_item_id,
				store_id: store_id
			},
			success: function (json) {
				if (json.processor_success)
				{
					bootbox.dialog({
						message: json.html,
						buttons: {
							"Full details": function () {
								bounce('main.php?page=item&recipe=' + json.recipe_id + '&ov_menu=' + json.menu_id);
							},
							cancel: {
								label: "Close",
							}
						}
					})
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
				response = 'Unexpected error';
			}
		});

	});

	// handle private party intro
	$(document).on('click', '.pp-view-intro, .pp-view-standard', function (e) {
		var view_menu = $(this).data('view_menu');

		create_and_submit_form({
			action: 'ddproc.php?processor=session_type',
			input: ({
				type: view_menu
			})
		});
	});

	// handle item page play button
	$(document).on('click', '.play-video', function (e) {

		$('html, body').animate({
			scrollTop: $('#video-tab').offset().top
		}, 2000);

		$('#video-tab').tab('show');

	});

	/*
	* Session Selection
	*/
	// handle session selection
	$(document).on('click', 'input[name="sessionRadio"]', function (e) {

		var session_id = $(this).val();

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'cart_session_processor',
				op: 'save',
				sid: session_id
			},
			success: function (json) {
				if (json.result_code == 1)
				{
					$('[for^="sessionRadio-"]').removeClass('bg-green font-weight-bold text-white');
					$('[data-day]').removeClass('bg-green font-weight-bold text-white').addClass('bg-gray-light');

					$('[for="sessionRadio-' + session_id + '"]').addClass('bg-green font-weight-bold text-white');
					$('[data-day="' + json.result_day + '"]').removeClass('bg-gray-light').addClass('bg-green text-white');

					bounce(json.bounce_to);
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
				response = 'Unexpected error';
			}
		});

	});

	// session filter
	$(document).on('change', 'input[name^="filter-show_"]', function (e) {

		$('[data-row_session_types]').hideFlex();
		$('.no-filter-selected').showFlex();

		$('input[name^="filter-show_"]').each(function (e) {

			var filter = $(this).data('filter');

			if ($(this).is(':checked'))
			{
				$('.no-filter-selected').hideFlex();

				$('[data-filter_session_type="' + filter + '"]').showFlex();

				$('[data-row_session_types]').each(function (e) {

					var session_types_array = $(this).data('row_session_types').split(',');

					if ($.inArray(filter, session_types_array) !== -1)
					{
						$(this).showFlex();
					}

				});
			}
			else
			{
				$('[data-filter_session_type="' + filter + '"]').hideFlex();
			}

		});

	});

	// handle session reschedule selection
	$(document).on('click', 'input[name="sessionRescheduleRadio"]', function (e) {

		if ($("#rescheduleSessionDialog").data("is_posed") != "true")
		{
			var session_id = $(this).val();
			var target = $(this).data('friendly_date_time');
			var source = $("#sessionRescheduleRadio-" + current_session_id).data('friendly_date_time');

			$('#rescheduleSessionDialog').find('#reschedule_from_session').html(source);
			$('#rescheduleSessionDialog').find('#reschedule_to_session').html(target);

			var options = {};
			$('#rescheduleSessionDialog').data("target_session_id", session_id);
			$("#rescheduleSessionDialog").data("is_posed", "true");
			$('#rescheduleSessionDialog').modal(options);

			$('#rescheduleSessionDialog').on('hide.bs.modal', function (e) {
				$('input[name="sessionRescheduleRadio"]').prop('checked', false);
				$("#rescheduleSessionDialog").data("is_posed", "false");
			});
		}
	});

	$(document).on('click', '#do_reschedule', function (e) {

		var target_id = $('#rescheduleSessionDialog').data("target_session_id");

		$("#do_reschedule").attr("disabled", "true");

		create_and_submit_form({
			action: "main.php?page=session&reschedule=" + current_order_id,
			input: ({
				target: target_id
			})
		});
	});

	$(document).on('click', '.add_event_session', function (e) {

		var session_id = $(this).data('session');
		var pwd_input = $('[data-event_session="' + session_id + '"]');

		var pwd = null;
		if ($(pwd_input).val())
		{
			pwd = $(pwd_input).val().trim();
		}

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'cart_session_processor',
				op: 'save',
				pwd: pwd,
				sid: session_id
			},
			success: function (json) {
				if (json.result_code == 1)
				{
					bounce('main.php?page=session_menu');
				}
				else
				{
					$(pwd_input).addClass('is-invalid').parent().find('.invalid-feedback').text(json.processor_message);

					$('.btn-spinning').removeClass('btn-spinning');
					$('.ld-spin').remove();
				}
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';

				$('.btn-spinning').removeClass('btn-spinning');
				$('.ld-spin').remove();
			}
		});

	});

	$(document).on('keyup', '[data-event_session]', function (e) {

		if ($(this).val().trim() != '' && e.which == 13)
		{
			$(this).parent().find('.add_event_session').trigger('click');
		}
		else
		{
			$(this).removeClass('is-invalid').parent().find('.invalid-feedback').text('Invalid code');
		}

	});

	// session type change
	$(document).on('click', '.change_session_type', function (e) {

		var session_type = $(this).data('session_type');
		var bounce = $(this).data('bounce');

		create_and_submit_form({
			action: 'ddproc.php?processor=session_type',
			input: ({
				'type': session_type,
				'bounce_to': bounce
			})
		});

	});

	// handle rsvp only
	$(document).on('click', '.rsvp_upgrade_btn', function (e) {

		$(".session_menu_div").slideDown();

		$('html, body').animate({
			scrollTop: $(".session_menu_div").offset().top
		}, 2000);

	});

	$(document).on('keyup change', '.add-coupon-code', function (e) {

		let code = $(this).val();

		$('.add-coupon-code').val(code);

	});

	$(document).on('click', '.configure-bundle:not(.disabled)', function (e) {

		let mid = $(this).data('menu_item_id');

		$('[data-master_item_id]').not('[data-master_item_id="' + mid + '"]').slideUp();

		$('[data-master_item_id="' + mid + '"]').slideToggle();

	});

	$(document).on('focusin', '.bundle-subitem-qty', function (e) {

		$(this).data('org_val', $(this).val());

	}).on('keyup change', '.bundle-subitem-qty', function (e) {

		let menu_item_id = $(this).data('menu_item_id');

		let canIncrement = canIncrementBundleSubItem($(this), '.bundle-subitem-qty');
		let passinventoryCheck = inventoryCheck(menu_item_id);

		if (!passinventoryCheck || canIncrement !== true)
		{
			$(this).val($(this).data('org_val'));

			if (canIncrement !== true)
			{
				modal_message({
					title: 'Max Items Reached',
					message: canIncrement
				});
			}
		}
		else
		{
			$(this).data('org_val', $(this).val());
		}

		updateServingsCountAndCheckoutButton();

	});

	$(document).on('click', '.add-bundle-to-cart:not(.disabled)', function (e) {

		let mid = $(this).data('menu_item_id');

		if (inventoryCheck(mid))
		{
			update_cart(mid, 'add');
		}

		// on success clear fields
		$('.bundle-subitem-qty[data-parent_item="' + mid + '"]:not([readonly])').val(0);

		// close subitems
		$('[data-master_item_id="' + mid + '"]').slideToggle();

		updateServingsCountAndCheckoutButton();

	});

	$(document).on('click', '.add-coupon-add:not(.disabled)', function (e) {

		let add_coupon_code = $(this).parent().parent().find('.add-coupon-code').val();

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
					coupon_code: add_coupon_code
				},
				success: function (json) {
					if (json.processor_success)
					{
						coupon = json.coupon;

						$('.add-coupon-code').prop('disabled', true);
						$('.add-coupon-add').addClass('disabled');

						// update coupon title
						$('.coupon-code-title').text(coupon.coupon_code_short_title);

						// update coupon total
						$('.coupon-code-total').text(formatAsMoney(coupon.coupon_code_discount_total));

						// show coupon row
						$('.coupon-code-row').showFlex();

						// add menu item if applicable
						if (json.cart_update)
						{
							if ($('[data-cart_menu_item="' + json.menu_item_id + '"]').length)
							{
								$('[data-cart_menu_item="' + json.menu_item_id + '"]').replaceWith(json.cart_update);
							}
							else
							{
								$(json.cart_update).appendTo($('.meals-list')).hide().slideDown();
								$('.meals-list, .cart-total-div').slideDown();
							}
							$('.clear-all-cart-items-div').show();

							menuItemInfo['mid'][json.menu_item_id].qty_in_cart = json.qty_in_cart;
						}

						updateServingsCountAndCheckoutButton();
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
	});

	$(document).on('click', '.clear-cart-session', function (e) {

		e.preventDefault();

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'cart_clear_category',
				op: 'do_clear_session',
				output: 'json'
			},
			success: function (json) {
				window.location.reload();
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
			}
		});

	});

	$(document).on('click', '.clear-all-cart-items', function (e) {

		var category = 'all';
		if (window.location.href.indexOf('gift_card_cart') != -1)
		{
			category = 'giftcard';
		}

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'cart_clear_category',
				op: 'get_clear_form',
				output: 'json'
			},
			success: function (json) {
				modal_message({
					title: 'Clear your Cart',
					message: json.html,
					height: 300,
					width: 300,
					resizable: false,
					confirm: function () {

						$.ajax({
							url: 'ddproc.php',
							type: 'POST',
							timeout: 20000,
							dataType: 'json',
							data: {
								processor: 'cart_item_processor',
								op: 'remove_all_items',
								output: 'json'
							},
							success: function (json) {
								// salesforce clear cart
								if (typeof _etmc !== 'undefined')
								{
									_etmc.push([
										"trackCart",
										{"clear_cart": true}
									]);
								}

								// reload page
								window.location.reload();
							},
							error: function (objAJAXRequest, strError) {
								response = 'Unexpected error';
							}
						});
					},
					cancel: function () {
					}
				});
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
			}
		});
	});

	$(document).on('click', '.add-coupon-remove', function (e) {

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'cart_remove_payment',
				payment_type: 'coupon'
			},
			success: function (json) {
				if (json.processor_success)
				{
					// restore coupon input
					$('.add-coupon-code').prop('disabled', false);
					$('.add-coupon-add').removeClass('disabled');

					coupon = false;

					// update coupon title
					$('.coupon-code-title').text('');

					// update coupon total
					$('.coupon-code-total').text(formatAsMoney(0.00));

					// hide coupon row
					$('.coupon-code-row').hideFlex();


					if (json.limit_to_recipe_id)
					{
						menuItemInfo['mid'][json.limit_to_recipe_id].qty_in_cart = json.qty_in_cart;

						if (json.qty_in_cart > 0)
						{
							if ($('[data-cart_menu_item="' + json.limit_to_recipe_id + '"]').length)
							{
								$('[data-cart_menu_item="' + json.limit_to_recipe_id + '"]').replaceWith(json.cart_update);
							}
							else
							{
								$(json.cart_update).appendTo($('.meals-list')).hide().slideDown();
								$('.meals-list, .cart-total-div').slideDown();
							}
							$('.clear-all-cart-items-div').show();
						}
						else
						{
							$('.add-to-cart[data-menu_item_id="' + json.limit_to_recipe_id + '"] > .fas').show();

							$('[data-cart_menu_item="' + json.limit_to_recipe_id + '"]').slideUp('slow', function () {

								$(this).remove();

								if (!$('.meals-list').html().trim().length)
								{
									if (order_minimum.is_applicable == false)
									{
										if (order_minimum.has_freezer_inventory != true)
										{
											$('.meals-list, .cart-total-div').slideUp('slow', function () {
												$('.msg_empty_cart').slideDown();
											});
										}
									}
								}
							});
						}
					}

					updateServingsCountAndCheckoutButton();
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
	});

	// handle rsvp submit
	$(document).on('click', '.rsvp_submit_btn', function (e) {

		e.preventDefault();

		if ($('#rsvp_only_form')[0].checkValidity() === false)
		{
			e.stopPropagation();

			$('.btn-spinning').removeClass('btn-spinning');
			$('.ld-spin').remove();
		}
		else
		{
			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'session_rsvp',
					op: 'rsvp_dream_taste',
					primary_email_login: $('#rsvp_primary_email_login').val(),
					password_login: $('#rsvp_password_login').val(),
					firstname: $('#rsvp_firstname').val(),
					lastname: $('#rsvp_lastname').val(),
					telephone_1: $('#rsvp_telephone_1').val(),
					nobo: 1,
					submit_login: 1,
					remember_login: 1
				},
				success: function (json) {
					if (json.processor_success)
					{
						$('.rsvp_only_form_incomplete').slideUp();
						$('.rsvp_only_form_complete').slideDown();

						$('.login_first_name').text($('#rsvp_firstname').val());
						$('.login_sign_in').hide();
						$('.login_signed_in').show();

						$('.btn-spinning').removeClass('btn-spinning');
						$('.ld-spin').remove();
					}
					else if (!json.processor_success && json.processor_message)
					{
						modal_message({
							message: json.processor_message
						});

						$('.btn-spinning').removeClass('btn-spinning');
						$('.ld-spin').remove();
					}
				},
				error: function (objAJAXRequest, strError) {
					response = 'Unexpected error';
				}

			});
		}

		$('#rsvp_only_form').addClass('was-validated');

	});

	// scroll cart into view on mobile
	$('.mobile-cart-div').find('.menuCart').on('show.bs.collapse', function () {

		$('.show-cart-button > .fas').removeClass('fa-shopping-cart');
		$('.show-cart-button > .fas').addClass('fa-chevron-up');

		//$('body').addClass('modal-open').append('<div class="menuCart-modal modal-backdrop fade show"></div>');
		$('body').addClass('modal-open');
		$('.menuCart').addClass('.panel-fullscreen');

		updateServingsCountAndCheckoutButton();
	});

	// scroll cart into view on mobile
	$('.mobile-cart-div').find('.menuCart').on('shown.bs.collapse', function () {

		$('.mobile-cart-div')[0].scrollIntoView({
			behavior: "smooth",
			block: "start"
		});

	});

	// close cart if they click outside it
	$(document).on('click', '.menuCart-modal, .mobile-cart-div > * .msg_empty_cart', function (e) {

		$('.show-cart-button').trigger('click');

	});

	$('.mobile-cart-div').find('.menuCart').on('hide.bs.collapse', function () {

		$('.show-cart-button > .fas').removeClass('fa-chevron-up');
		$('.show-cart-button > .fas').addClass('fa-shopping-cart');
		$('.continue-btn-div').toggle();

		$('body').removeClass('modal-open');
		$('.menuCart-modal').remove();

		updateServingsCountAndCheckoutButton();
	});

	// setup back button to handle closing cart window
	/*
	$(document).on('click', '.show-cart-button', function (e) {

		// opening cart
		if ($(this).attr('aria-expanded') == 'true' && location.hash != "#cart")
		{
			historyPush({url: 'main.php?page=session_menu#cart'});
		}
		else // closing cart
		{
			if (location.hash === "#cart")
			{
				history.replaceState(null, null, ' ');
			}
		}

	});

	// handle close cart when #cart is removed via back button
	$(window).on('hashchange', function (e) {

		if (location.hash === "")
		{
			$('.mobile-cart-div').find('.menuCart').collapse('hide');
			$('.show-cart-button').attr('aria-expanded', false);
		}

	});
	*/

	// handle sessions that expire while page is loaded
	if ($('[data-unix_expiry]').length > 0)
	{
		// cancel existing timer if there is one
		$.doTimeout('unix_expiry_timer');

		// check every minute and set radio to disabled based on session/day expiry
		$.doTimeout('unix_expiry_timer', 60000, function () {
			var unixtime = Math.round(new Date().getTime() / 1000);

			$('[data-unix_expiry]').filter(function () {

				return $(this).data('unix_expiry') < unixtime && !$(this).hasClass('disabled');

			}).addClass('disabled');

			return true;
		});

		// call it immediately
		$.doTimeout('unix_expiry_timer', true);
	}
	/*
	* End Session Selection
	*/

	// fix for IE
	if (isIE())
	{
		var mobileCartElem = $('.mobile-cart-div');
		var fixMobileTop = $(mobileCartElem).offset().top;
		$(window).scroll(function () {
			var currentScroll = $(window).scrollTop();
			if (currentScroll >= fixMobileTop)
			{
				$(mobileCartElem).css({
					position: 'fixed',
					left: 0,
					top: 0
				}).removeClass('px-0');
			}
			else
			{
				$(mobileCartElem).css({
					position: 'static',
					left: 'auto',
					top: 'auto'
				}).addClass('px-0');
			}
		});

		/*
		var desktopCartElem = $('.desktop-cart-div');
		var fixDesktopTop = $(desktopCartElem).offset().top;
		$(window).scroll(function () {
			var currentScroll = $(window).scrollTop();
			if (currentScroll >= fixDesktopTop)
			{
				$(desktopCartElem).css({
					position: 'fixed',
					top: 0
				});
			}
			else
			{
				$(desktopCartElem).css({
					position: 'static',
					top: 'auto'
				});
			}
		});
		*/
	}

});