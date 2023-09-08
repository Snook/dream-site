let BOX_PROCESSING = false;
let BOX_QUEUE = [];

function inventoryCheck(menu_item_id, operation)
{
	let newInventory;
	let qty = 0;
	let qty_in_box = 0;
	// is item already in box - include in inventory check if so
	if (box_info.custom_box.items[menu_item_id] != undefined)
	{
		qty_in_box = box_info.custom_box.items[menu_item_id];
	}


	if (operation == 'add')
	{
		qty = qty_in_box + 1;
		newInventory = parseInt(menuItemInfo.mid[menu_item_id].remaining_servings) - (parseInt(menuItemInfo.mid[menu_item_id].servings_per_item * qty));
	}
	else
	{
		qty = qty_in_box - 1;
		newInventory = parseInt(menuItemInfo.mid[menu_item_id].remaining_servings) + parseInt(menuItemInfo.mid[menu_item_id].servings_per_item * qty);
	}

	if (newInventory < 0)
	{
		return false;
	}
	else
	{
		return newInventory;
	}
}

function incrementBox(menu_item_id, update_action)
{
	if (update_action === 'add')
	{
		if (box_info.custom_box.info.number_items == box_info.number_items_required && box_info.custom_box.info.number_servings == box_info.number_servings_required)
		{
			modal_message({
				message: 'The box is full, you may now add the box to your cart.',
				buttons: {
					"Add Box": function () {
						$('.box-add').trigger('click');
					},
					"Not Yet": function () {
					}
				}
			});
			return false;
		}

		if (typeof box_info.custom_box.items[menu_item_id] === 'undefined')
		{
			box_info.custom_box.items[menu_item_id] = 1;
		}
		else
		{
			box_info.custom_box.items[menu_item_id] += 1;
		}
	}
	else
	{
		if (typeof box_info.custom_box.items[menu_item_id] === 'undefined')
		{
			box_info.custom_box.items[menu_item_id] = 0;
		}
		else
		{
			box_info.custom_box.items[menu_item_id] -= 1;
		}

		if (box_info.custom_box.items[menu_item_id] <= 0)
		{
			delete box_info.custom_box.items[menu_item_id];
		}
	}

	recalculateBox();

	return true;
}

function recalculateBox()
{
	if (typeof box_info !== 'undefined')
	{
		if (box_info.box_type == 'DELIVERED_FIXED')
		{
			$('.progress-box').addClass('box-add').removeClass('progress-bar-striped disabled');
			$('.progress-box').css({'width': '100%'}).attr('aria-valuenow', 100).text('Add box to cart');
			$('.box-add').removeClass('disabled');
		}
		else if (box_info.box_type == 'DELIVERED_CUSTOM')
		{
			let number_items = 0;
			let number_servings_total = 0;
			let number_servings_per_item = [];

			// fresh start by disabling ability to add box
			$('.box-add').addClass('disabled');
			// fresh start by enabling all menu item buttons
			$('.box-item-update[data-box_update_action="add"]').removeClass('disabled').find('.title').text('Add item');

			if (box_info.custom_box.items)
			{
				let box_items = box_info.custom_box.items;

				$.each(box_items, function (mid, qty) {
					number_items += qty;
					number_servings_total += parseInt(menuItemInfo.mid[mid].servings_per_item) * qty;

					let number_servings_this_item = parseInt(menuItemInfo.mid[mid].servings_per_item) * qty;

					let newInventory = parseInt(menuItemInfo.mid[mid].remaining_servings) - number_servings_this_item;

					let button = $('.box-item-update[data-box_update_action="add"][data-menu_item_id="' + mid + '"]');

					// disable individual menu button state by max allowed or remaining inventory
					if (qty >= 2 || newInventory < menuItemInfo.mid[mid].servings_per_item)
					{
						button.addClass('disabled');

						// max allowed reached
						if (qty >= 2)
						{
							button.find('.title').text('Max allowed');
						}
						// out of inventory
						else
						{
							button.find('.title').text('Sold out');

						}
					}
				});
			}

			box_info.custom_box.info = {
				'number_items' : number_items,
				'number_servings' : number_servings_total
			};

			let progress_val = (box_info.custom_box.info.number_items / box_info.number_items_required) * 100;
			let progress_txt = ((progress_val >= 100) ? 'Add box to cart' : ((progress_val <= 0) ? '' : Math.round(progress_val) + '%'));

			if (progress_val >= 100)
			{
				$('.progress-box').addClass('box-add').removeClass('progress-bar-striped disabled');
				$('.cart-button').removeClass('fa-box-open').addClass('fa-box');
				$('.box-add').removeClass('disabled');
			}
			else
			{
				$('.progress-box').addClass('progress-bar-striped').removeClass('box-add disabled');
				$('.cart-button').removeClass('fa-box').addClass('fa-box-open');
			}

			$('.progress-box').css({'width': progress_val + '%'}).attr('aria-valuenow', progress_val).text(progress_txt);

			// enable add box button
			if (box_info.custom_box.info.number_items == box_info.number_items_required && box_info.custom_box.info.number_servings == box_info.number_servings_required)
			{
				$('.box-add').removeClass('disabled');
			}

		}

	}

	return true;
}

$(function () {

	recalculateBox();

	$(document).on('click', '[data-select_delivery_day]:not(.disabled)', function (e) {

		e.preventDefault();

		let sid = this.getAttribute('data-select_delivery_day');

		create_and_submit_form({
			input: ({
				sid: sid
			})
		});

	});

	$(document).on('click', '[data-view_box]:not(.disabled)', function (e) {

		e.preventDefault();

		let box_id = this.getAttribute('data-view_box');
		let bundle_id = this.getAttribute('data-view_bundle');

		create_and_submit_form({
			action: '?page=box_menu',
			input: ({
				view_box: box_id,
				view_bundle: bundle_id
			})
		});

	});

	// handle cart addition
	$(document).on('click', '.box-item-update:not(.disabled)', function (e) {

		e.preventDefault();

		// new box instance, haven't retrieved a new id yet
		if (BOX_PROCESSING && box_info.box_instance_id == 0)
		{
			// hang onto this and process after we get an id
			BOX_QUEUE.push($(this));
			return false;
		}

		let menu_item_id = $(this).data('menu_item_id');
		let update_action = $(this).data('box_update_action');

		if (inventoryCheck(menu_item_id, update_action) !== false)
		{
			if (incrementBox(menu_item_id, update_action))
			{
				BOX_PROCESSING = true;

				$.ajax({
					url: 'ddproc.php',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'delivered_box',
						op: 'update',
						menu_item_id: menu_item_id,
						box_id: box_info.box_id,
						box_instance_id: box_info.box_instance_id,
						bundle_id: box_info.box_bundle_id,
						qty: box_info.custom_box.items[menu_item_id]
					},
					success: function (json) {

						if (json.processor_success)
						{
							box_info.box_instance_id = json.cart_box_instance_id;
							BOX_PROCESSING = false;

							if (BOX_QUEUE.length != 0)
							{
								$.each(BOX_QUEUE, function (key, item) {

									$(item).trigger('click');
									delete BOX_QUEUE[key];

								});
							}

							if ($('[data-cart_menu_item="' + menu_item_id + '"]').length)
							{
								$('[data-cart_menu_item="' + menu_item_id + '"]').replaceWith(json.cart_update);
							}
							else
							{
								$(json.cart_update).appendTo($('.meals-list')).hide().slideDown();
							}

							if (!$('.meals-list').html().trim().length)
							{
								$('.cart-list-div').slideUp('slow', function () {
									$('.msg_empty_cart').slideDown();
								});
							}
							else
							{
								$('.msg_empty_cart').slideUp('slow', function () {
									$('.cart-list-div').slideDown();
								});
							}

							recalculateBox();
						}
						else
						{

						}
					},
					error: function (objAJAXRequest, strError) {


					}
				});
			}
		}
	});

	$(document).on('click', '.box-add:not(.disabled)', function (e) {

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'delivered_box',
				op: 'add',
				box_id: box_info.box_id,
				bundle_id: box_info.box_bundle_id,
				box_instance_id: box_info.box_instance_id
			},
			success: function (json) {

				if (json.processor_success)
				{
					bounce('?page=box_select');
				}
				else
				{

				}
			},
			error: function (objAJAXRequest, strError) {


			}
		});


	});

	$(document).on('click', '.box-remove:not(.disabled)', function (e) {

		let box_instance_id = $(this).data('box_instance_id');

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'delivered_box',
				op: 'remove',
				box_instance_id: box_instance_id
			},
			success: function (json) {

				if (json.processor_success)
				{
					let boxButton = $('[data-view_box="' + json.data.box_info.box_id + '"][data-view_bundle="' + json.data.box_info.bundle_id + '"]');

					if (boxButton.hasClass('disabled') && json.data.box_info.out_of_stock != 'false')
					{
						boxButton.removeClass('disabled').text('$' + json.data.box_info.price + ' ' + json.data.box_info.bundle_name);
					}

					$('[data-cart_box_id="' + box_instance_id + '"]').slideUp(function(e) {
						$(this).remove();

						$('.cart-total').text(json.data.total_items_price);
					});
				}
				else
				{

				}
			},
			error: function (objAJAXRequest, strError) {


			}
		});

	});

	$(document).on('keyup change', '.add-coupon-code', function (e) {

		let code = $(this).val();

		$('.add-coupon-code').val(code);

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

	$(document).on('click', '[data-dinner_details]', function (e) {

		e.preventDefault();

		let recipe_id = $(this).data('dinner_details');

		create_and_submit_form({
			action: '?page=item&recipe=' + recipe_id,
			input: ({
				'box_id': box_info.box_id
			})
		});


	});
});