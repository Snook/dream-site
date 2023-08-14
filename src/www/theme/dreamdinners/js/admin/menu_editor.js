$(function () {

	$("#menu_editor_form").submit(function (e) {

		e.preventDefault();
		let form = this;

		bootbox.confirm("Are you sure want to submit these changes to your menu? If the menu is active the changes will be immediately available to your customers.", function (result) {
			if (result)
			{
				form.submit();
				return true;
			}
			else
			{
				$("#menu_editor_form").removeClass('was-validated');
				return true;
			}
		});
	});

	$('#menus').on('change', function (e) {

		let menu_id = $(this).val();

		create_and_submit_form({
			action: 'main.php?page=admin_menu_editor_new',
			input: ({
				'action': 'changeMenu',
				'menus': menu_id
			})
		});

	});

	$('#store').on('change', function (e) {

		let store_id = $(this).val();

		create_and_submit_form({
			action: 'main.php?page=admin_menu_editor_new',
			input: ({
				'action': 'changeStore',
				'store': store_id
			})
		});

	});

	$(document).on('change keyup', '.override-price-input', function (e) {

		let new_value = formatAsMoney($.trim($(this).val()));
		let new_value_cents = new_value.toString().split('.')[1];
		let orgval = $(this).data('orgval');
		let ltd_menu_item_value = $(this).data('ltd_menu_item_value');
		let menu_item_id = $(this).data('menu_item_id');
		let tier_1_price = $(this).data('tier_1_price');
		let tier_2_price = $(this).data('tier_2_price');
		let tier_3_price = $(this).data('tier_3_price');
		let preview_price = null;

		$(this).removeClass([
			'border-green',
			'border-orange',
			'border-red',
			'border-width-2'
		]);

		$("#row_" + menu_item_id + " > .preview-price").removeClass([
			'text-green',
			'text-orange',
			'text-red'
		]).text('');

		if ($(this).val() && orgval != new_value)
		{
			let preview_price = Number(new_value) + Number(ltd_menu_item_value);

			$("#row_" + menu_item_id + " > .preview-price").text(preview_price);

			if ((tier_1_price && tier_3_price) && (new_value < tier_1_price || new_value > tier_3_price))
			{
				$("#row_" + menu_item_id + " > .preview-price").addClass('text-red');
				$(this).addClass('border-red border-width-2');
			}
			else if (new_value_cents != '49' && new_value_cents != '99')
			{
				$("#row_" + menu_item_id + " > .preview-price").addClass('text-orange');
				$(this).addClass('border-orange border-width-2');
			}
			else
			{
				$("#row_" + menu_item_id + " > .preview-price").addClass('text-green');
				$(this).addClass('border-green');
			}
		}

	});

	$(document).on('change keyup', '.markup-input', function (e) {

		let markup_type = $(this).attr('name');
		let markup_value = Number($(this).val());
		let pricing_type = $(this).data('pricing_type');

		switch (markup_type)
		{
			case 'markup_2_serving':
			case 'markup_3_serving':
			case 'markup_4_serving':
			case 'markup_6_serving':
				// loop price input that isn't a side or readonly
				$('.override-price-input[data-pricing_type="' + pricing_type + '"]:not([data-category_group="SIDE"][readonly])').each(function () {
					let menu_item_id = $(this).data('menu_item_id');
					let base_price = Number($(this).data('price'));
					let markup_price = formatAsMoney(base_price + (Math.round((base_price * markup_value) + 0.00000001) / 100));
					$('.markup-price[data-menu_item_id="' + menu_item_id + '"]').text(markup_price);
				});
				break;
			case 'markup_sides':
				// loop price input that is a side and not readonly
				$('.override-price-input[data-category_group="SIDE"]:not([readonly])').each(function () {
					let menu_item_id = $(this).data('menu_item_id');
					let base_price = Number($(this).data('price'));
					let markup_price = formatAsMoney(base_price + (Math.round((base_price * markup_value) + 0.00000001) / 100));
					$('.markup-price[data-menu_item_id="' + menu_item_id + '"]').text(markup_price);
				});
				break;
		}

	});
	$('.markup-input').trigger('change');

	// check for changes
	$(document).on('change keyup', '.visibility-select, .override-price-input, .markup-input', function (e) {

		let is_unsaved = false;
		$('.unsaved-message').hideFlex();

		// check if any options are changed
		$('.visibility-select').each(function () {

			$(this).removeClass([
				'border-orange'
			]);

			if (Number($(this).val()) != Number($(this).data('orgval')))
			{
				$(this).addClass([
					'border-orange'
				]);

				is_unsaved = true;

			}

		});

		// check if any override prices are changed
		$('.override-price-input').each(function () {

			let new_value = formatAsMoney($.trim($(this).val()));
			let new_value_cents = new_value.toString().split('.')[1];
			let orgval = $(this).data('orgval');

			if (orgval != '' && orgval != new_value)
			{
				is_unsaved = true;

			}

		});

		// check if any markup values are changed
		$('.markup-input').each(function () {

			let new_value = formatAsMoney($.trim($(this).val()));
			let new_value_cents = new_value.toString().split('.')[1];
			let orgval = $(this).data('orgval');

			if (orgval != '' && orgval != new_value)
			{
				is_unsaved = true;

			}

		});

		if (is_unsaved)
		{
			$('.unsaved-message').showFlex();
		}

	});

	// handle adding efl and side menu items to menu
	$(document).on('click', '[data-add_past_menu_item]:not(.disabled)', function (e) {

		e.preventDefault();

		let add_menu_item = {
			'menu_id': $('#menus').val(),
			'store_id': STORE_DETAILS.id,
			'item_type': $(this).data('add_past_menu_item'),
			'selected_items': {},
			'dialog': null
		}

		// create the modal ui with bootbox
		add_menu_item.dialog = bootbox.dialog({
			message: '<p><i class="fa fa-spin fa-spinner"></i> Loading menu items...</p>',
			size: 'large',
			closeButton: false,
			buttons: {
				add_items: {
					label: "Add selected menu items",
					className: 'btn-primary disabled add-menu-items-confirm',
					callback: function () {

						// nothing selected, close window
						if (Object.keys(add_menu_item.selected_items).length === 0)
						{
							return true;
						}

						// using native confirm since bootstrap doesn't support multiple modals
						if (confirm("This will immediately add the listed items to the menu. Are you sure you wish to add the selected menu items to the menu?"))
						{
							add_menu_item.dialog.find('.bootbox-body').html('<p><i class="fa fa-spin fa-spinner"></i> Adding menu items...</p>');
							add_menu_item.dialog.find('.modal-footer').hideFlex();

							$.ajax({
								url: 'ddproc.php',
								type: 'POST',
								dataType: 'json',
								timeout: 60000,
								data: {
									processor: 'admin_menu_editor',
									menu_id: add_menu_item.menu_id,
									store_id: add_menu_item.store_id,
									op: 'add_items',
									items: add_menu_item.selected_items
								},
								success: function (json) {
									if (json.processor_success)
									{
										bounce('main.php?page=admin_menu_editor_new&tab=nav-' + add_menu_item.item_type);
									}
									else
									{
										add_menu_item.dialog.find('.bootbox-body').html('<p>' + json.processor_message + '</p>');
									}
								},
								error: function (objAJAXRequest, strError) {
									add_menu_item.dialog.find('.bootbox-body').html('<p>Unexpected error: ' + strError + '</p>');
								}
							});
						}

						// confirmation dialog is presented above, prevent dialog from closing
						return false;
					}
				},
				cancel: {
					label: "Cancel",
					className: 'btn-danger',
					callback: function () {
						return true;
					}
				}
			},
			onShow: function (e) {
				// load the menu items
				$.ajax({
					url: 'ddproc.php',
					type: 'POST',
					dataType: 'json',
					timeout: 60000,
					data: {
						processor: 'admin_menu_editor',
						menu_id: add_menu_item.menu_id,
						store_id: add_menu_item.store_id,
						op: 'get_items',
						item_type: add_menu_item.item_type
					},
					success: function (json) {
						if (json.processor_success)
						{
							add_menu_item.dialog.find('.bootbox-body').html(json.menu_item_html);
						}
						else
						{
							add_menu_item.dialog.find('.bootbox-body').html('<p>' + json.processor_message + '</p>');
						}
					},
					error: function (objAJAXRequest, strError) {
						add_menu_item.dialog.find('.bootbox-body').html('<p>Unexpected error: ' + strError + '</p>');
					}
				});
			}
		});

		// handle add menu item button
		$(add_menu_item.dialog).on('click', '[data-add_menu_item]:not(.disabled)', function (e) {

			e.preventDefault();
			$(this).addClass('disabled');

			let menu_item_id = $(this).data('add_menu_item');
			let entree_id = $(this).data('entree_id');
			let recipe_id = $(this).data('recipe_id');

			// add this item to list to be saved
			add_menu_item.selected_items[entree_id] = entree_id;

			// enable the submit button
			add_menu_item.dialog.find('.modal-footer > .add-menu-items-confirm').removeClass('disabled');

			$('.row_menu_editor_add_item[data-recipe_id="' + recipe_id + '"]').showFlex();

		});

		// handel cancel adding a menu item
		$(add_menu_item.dialog).on('click', '[data-add_menu_item_cancel]:not(.disabled)', function (e) {

			e.preventDefault();
			let menu_item_id = $(this).data('add_menu_item_cancel');
			let entree_id = $(this).data('entree_id');
			let recipe_id = $(this).data('recipe_id');

			// remove this item from list to be saved
			delete add_menu_item.selected_items[entree_id];

			// no entries in unsaved_entries, disable the submit button
			if (Object.keys(add_menu_item.selected_items).length === 0)
			{
				add_menu_item.dialog.find('.modal-footer > .add-menu-items-confirm').addClass('disabled');
			}

			$('[data-add_menu_item="' + menu_item_id + '"]').removeClass('disabled');
			$('.row_menu_editor_add_item[data-recipe_id="' + recipe_id + '"]').hideFlex();

		});

		// handle menu item search
		$(add_menu_item.dialog).on('keyup change', '#add_menu_item_filter', function (e) {

			$.uiTableFilter($('#add_menu_item_recipe_list'), this.value);

		});

		// handle search input clearing
		$(add_menu_item.dialog).on('click', '#add_menu_item_clear_filter', function (e) {

			$('#add_menu_item_filter').val('').change();

		});

		// handel menu info lookup, we load within the modal becaues Bootstrap doesn't support multiple modals
		$(add_menu_item.dialog).on('click', '[data-add_menu_item_info]', function (e) {

			let entree_id = $(this).data('entree_id');
			let recipe_id = $(this).data('recipe_id');
			let fetched = $('.add_menu_item_info[data-recipe_id="' + recipe_id + '"]').data('fetched');

			if (fetched)
			{
				$('.add_menu_item_info[data-recipe_id="' + recipe_id + '"]').showFlex();
				return;
			}

			$('.add_menu_item_info[data-recipe_id="' + recipe_id + '"]').html('<span class="font-weight-bold"><i class="fa fa-spin fa-spinner"></i> Loading menu item info...</span>').showFlex();

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_menu_editor',
					menu_id: add_menu_item.menu_id,
					store_id: add_menu_item.store_id,
					op: 'menu_item_info',
					entree_id: entree_id,
					recipe_id: recipe_id
				},
				success: function (json) {
					$('.add_menu_item_info[data-recipe_id="' + recipe_id + '"]').data('fetched', true).html(json.data);
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

		// hadle closing the menu item info panel
		$(add_menu_item.dialog).on('click', '[data-add_menu_item_info_close]', function (e) {

			let recipe_id = $(this).data('add_menu_item_info_close');

			$('.add_menu_item_info[data-recipe_id="' + recipe_id + '"]').hideFlex();

		});

	}); // end adding efl and side menu items to menu

	// handle markup roundup
	$(document).on('click', '[data-round_up_markup]', function (e) {

		let item_type = $(this).data('round_up_markup');

		bootbox.confirm("Are you sure want to update all Override Prices with rounded Markup Prices? This action will replace existing override prices with the newly calculated price.", function (result) {
			if (result)
			{
				$('.override-price-input[data-category_group="' + item_type + '"]:not([readonly])').each(function () {

					if ($.trim($(this).val()) != '')
					{
						let o_input = Number($(this).val());
						let o_price = Math.ceil(price * 2) / 2;
						o_input.val(o_price.toFixed(2));
					}

				});

				return true;
			}
		});

	});

	// handle fetchin default pricing
	$(document).on('click', '.sides-sweets-get-pricing', function (e) {

		let operation = $(this).data('operation');

		if (operation === 'default_pricing_get')
		{
			bootbox.confirm("Are you sure want to update all Override Prices with rounded Markup Prices? This action will replace existing override prices with the newly calculated price.", function (result) {
				if (result)
				{
					$.ajax({
						url: 'ddproc.php',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_menu_editor',
							store_id: STORE_DETAILS.id,
							op: operation
						},
						success: function (json) {
							if (json.processor_success)
							{
								$.each(json.pricingData, function (recipe_id, pricing_type) {
									$.each(pricing_type, function (pricing_type, pricing) {
										$('.override-price-input[data-recipe_id="' + recipe_id + '"][data-pricing_type="' + pricing_type + '"]:not([readonly])').val(pricing.default_price).trigger('change');
									});
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
					return true;
				}
			});
		}
		else if (operation === 'default_pricing_save')
		{
			let prices = {};

			$('.override-price-input[data-category_group="SIDE"]:not([readonly])').each(function () {

				let save_price = 0;
				let recipe_id = $(this).data('recipe_id');
				let menu_item_id = $(this).data('menu_item_id');
				let price = Number($(this).data('price'));
				let markup_price = Number($('.markup-price[data-menu_item_id="' + menu_item_id + '"]').text());
				let override_price = Number($(this).val());
				let vis = $('.visibility-select[data-recipe_id="' + recipe_id + '"][data-visibility_type="vis"]').val();
				let hid = $('.visibility-select[data-recipe_id="' + recipe_id + '"][data-visibility_type="hid"]').val();
				let form = $('.visibility-select[data-recipe_id="' + recipe_id + '"][data-visibility_type="form"]').val();

				if (override_price !== 0)
				{
					save_price = override_price;
				}
				else if (markup_price !== 0)
				{
					save_price = markup_price;
				}
				else
				{
					save_price = price;
				}

				if (save_price !== 0)
				{
					prices[recipe_id] = {
						'price': save_price,
						'vis': vis,
						'hid': hid,
						'form': form
					};
				}

			});

			if (Object.keys(prices).length !== 0)
			{
				bootbox.confirm("Are you sure you want to save the default Sides and Sweets pricing and visibility? This cannot be undone.", function (result) {
					if (result)
					{
						$.ajax({
							url: 'ddproc.php',
							type: 'POST',
							timeout: 20000,
							dataType: 'json',
							data: {
								processor: 'admin_menu_editor',
								store_id: STORE_DETAILS.id,
								op: operation,
								prices: prices
							},
							success: function (json) {
								if (json.processor_success)
								{
									$.each(json.pricingData, function (recipe_id, pricing_type) {
										$.each(pricing_type, function (pricing_type, pricing) {
											$('.override-price-input[data-recipe_id="' + recipe_id + '"][data-pricing_type="' + pricing_type + '"]:not([readonly])').val(pricing.default_price).trigger('change');
										});
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
						return true;
					}
				});
			}

		}

	});

});