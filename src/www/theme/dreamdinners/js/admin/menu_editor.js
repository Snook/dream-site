var $form, originForm;

$(document).ready(function () {

	$form = $('#menu_editor_form');
	originForm = $form.serialize();

	$('.menu-editor-ovr').trigger('change');

});

$(document).on('change', '#menu', function (e) {

	let menu_id = $(this).val();

	e.preventDefault()

	bootbox.dialog({
		title: 'Confirmation',
		message: "<p>Are you sure you wish to change menus? You will lose any unsaved changes.</p>",
		centerVertical: true,
		buttons: {
			cancel: {
				label: 'Cancel'
			},
			confirm: {
				label: 'Switch menu',
				callback: function () {

					create_and_submit_form({
						action: '/backoffice/menu-editor',
						input: ({
							'menu': menu_id
						})
					});

				}
			}
		}
	});

});

$(document).on('click', '.sides-sweets-save', function (e) {

	e.preventDefault()

	bootbox.dialog({
		title: 'Confirmation',
		message: "<p>Are you sure you wish to save all current Sides &amp; Sweets settings as the default pricing and visibility?</p>",
		centerVertical: true,
		buttons: {
			cancel: {
				label: 'Cancel'
			},
			confirm: {
				label: 'Safe defualts',
				className: 'btn-danger',
				callback: function () {

					let saving_sides = bootbox.dialog({
						message: '<p><i class="fa fa-spin fa-spinner"></i> Saving defaults, please wait.</p>',
						centerVertical: true,
						closeButton: false
					});

					let defaultData = {};

					$('.menu-editor-ovr').each(function (e) {

						let menu_item_id = $(this).data('menu_item_id');
						let recipe_id = $(this).data('recipe_id');

						defaultData[recipe_id] = {
							'ovr': $(this).val(),
							'vis': $('.menu-editor-vis[data-menu_item_id="' + menu_item_id + '"]').val(),
							'hid': $('.menu-editor-hid[data-menu_item_id="' + menu_item_id + '"]').val(),
							'pic': $('.menu-editor-pic[data-menu_item_id="' + menu_item_id + '"]').val(),
							'form': $('.menu-editor-form[data-menu_item_id="' + menu_item_id + '"]').val()
						}

					});

					$.ajax({
						url: '/processor',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_defaultPricingProcessor',
							store_id: STORE_DETAILS.id,
							action: 'save',
							data: defaultData
						},
						success: function (json) {
							if (json.processor_success)
							{
								saving_sides.modal('hide');
							}
							else
							{
								dd_message({
									title: 'Error',
									message: json.processor_message
								});
								return;
							}
						},
						error: function (objAJAXRequest, strError) {
							response = 'Unexpected error: ' + strError;
							dd_message({
								title: 'Error',
								message: response
							});
							return;
						}

					});

				}
			}
		}
	});

});

$(document).on('click', '.sides-sweets-retrieve', function (e) {

	e.preventDefault()

	bootbox.prompt({
		title: "Which settings do you wish to retrieve?",
		inputType: 'checkbox',
		inputOptions: [
			{
				text: 'Show on customer menu',
				value: 'vis'
			},
			{
				text: 'Show on Sides & Sweets Forms',
				value: 'form'
			},
			{
				text: 'Price',
				value: 'ovr'
			}
		],
		centerVertical: true,
		callback: function (result) {

			if (result)
			{
				$.ajax({
					url: '/processor',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'admin_defaultPricingProcessor',
						store_id: STORE_DETAILS.id,
						action: 'retrieve'
					},
					success: function (json) {
						if (json.processor_success)
						{
							$.each(json.settings, function (recipe_id, setting) {

								if (result.includes('ovr'))
								{
									$('.menu-editor-ovr[data-recipe_id="' + recipe_id + '"][data-category_id="9"]').val(setting['ovr']).trigger('change');
								}

								if (result.includes('vis'))
								{
									$('.menu-editor-vis[data-recipe_id="' + recipe_id + '"][data-category_id="9"]').val(setting['vis']).trigger('change');
									$('.menu-editor-hid[data-recipe_id="' + recipe_id + '"][data-category_id="9"]').val(setting['hid']).trigger('change');
								}

								if (result.includes('form'))
								{
									$('.menu-editor-form[data-recipe_id="' + recipe_id + '"][data-category_id="9"]').val(setting['form']).trigger('change');
									$('.menu-editor-hid[data-recipe_id="' + recipe_id + '"][data-category_id="9"]').val(setting['hid']).trigger('change');
								}

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

$(document).on('change', '.menu-editor-hid', function (e) {

	let menu_item_id = $(this).data('menu_item_id');

	if (this.value == 1)
	{
		$('.menu-editor-vis[data-menu_item_id="' + menu_item_id + '"], .menu-editor-form[data-menu_item_id="' + menu_item_id + '"], .menu-editor-pic[data-menu_item_id="' + menu_item_id + '"]').val(0).trigger('change');
	}

	if ($(this).valNotDefault())
	{
		$(this).addClass('border-orange');
	}
	else
	{
		$(this).removeClass('border-orange');
	}

	$('.menu-editor-unsaved-alert').hideFlex();
	if ($form.serialize() !== originForm)
	{
		$('.menu-editor-unsaved-alert').showFlex();
	}

});

$(document).on('change', '.menu-editor-vis, .menu-editor-form, .menu-editor-pic', function (e) {

	let menu_item_id = $(this).data('menu_item_id');

	if (this.value == 1)
	{
		$('.menu-editor-hid[data-menu_item_id="' + menu_item_id + '"]').val(0).trigger('change');
	}

	if ($(this).valNotDefault())
	{
		$(this).addClass('border-orange');
	}
	else
	{
		$(this).removeClass('border-orange');
	}

	$('.menu-editor-unsaved-alert').hideFlex();
	if ($form.serialize() !== originForm)
	{
		$('.menu-editor-unsaved-alert').showFlex();
	}

});

$(document).on('change keyup', '.menu-editor-ovr', function (e) {

	let menu_item_id = $(this).data('menu_item_id');
	let lowest_tier_price = Number($(this).data('lowest_tier_price'));
	let highest_tier_price = Number($(this).data('highest_tier_price'))
	let ovr_value = Number(Number($(this).val()).toFixed(2));
	var priceArray = Number($(this).val()).toFixed(2).split('.');

	$(this).removeClass('border-orange');
	$('.ovr-alert-danger[data-menu_item_id="' + menu_item_id + '"]').hideFlex();
	$('.ovr-alert-warning[data-menu_item_id="' + menu_item_id + '"]').hideFlex();

	if ($(this).valNotDefault())
	{
		$(this).addClass('border-orange');
	}

	// If the tier prices are zero, nothing to do
	if (lowest_tier_price > 0 && highest_tier_price > 0)
	{
		if (ovr_value < lowest_tier_price || ovr_value > highest_tier_price)
		{
			$('.ovr-alert-danger[data-menu_item_id="' + menu_item_id + '"]').showFlex();
		}
	}

	// Only if the price isn't zero
	if (ovr_value > 0 && priceArray[1] != '99' && priceArray[1] != '49')
	{
		$('.ovr-alert-warning[data-menu_item_id="' + menu_item_id + '"]').showFlex();
	}

	$('.menu-editor-unsaved-alert').hideFlex();
	if ($form.serialize() !== originForm)
	{
		$('.menu-editor-unsaved-alert').showFlex();
	}

});

$(document).on('click', '.menu-editor-reset', function (e) {

	bootbox.dialog({
		title: 'Confirmation',
		message: "<p>Are you sure you wish to reset all currently pending changes?</p>",
		centerVertical: true,
		buttons: {
			cancel: {
				label: 'Cancel'
			},
			confirm: {
				label: 'Reset changes',
				className: 'btn-danger',
				callback: function () {

					$("#menu_editor_form").trigger('reset');

					// Timeout ensures form is reset first
					setTimeout(function () {
						$('#menu_editor_form').removeClass('was-validated');
						$('.menu-editor-vis, .menu-editor-form, .menu-editor-pic, .menu-editor-hid, .menu-editor-ovr').removeClass('border-orange').trigger('change');
					}, 1);
				}
			}
		}
	});

});

$(document).on('submit keydown keyup', '#menu_editor_form', function (e) {

	if (e.keyCode == 13)
	{
		e.preventDefault();
		return false;
	}

});

$(document).on('click', '.menu-editor-finalize', function (e) {

	if ($('#menu_editor_form')['0'].checkValidity() !== false)
	{
		bootbox.dialog({
			title: 'Confirmation',
			message: "<p>Are you sure you wish to save all menu editor changes?</p>",
			centerVertical: true,
			buttons: {
				cancel: {
					label: 'Cancel'
				},
				confirm: {
					label: 'Finalize changes',
					className: 'btn-danger',
					callback: function () {

						$('#action').val('finalize')
						$("#menu_editor_form").trigger('submit');

						bootbox.dialog({
							message: '<p><i class="fa fa-spin fa-spinner"></i> Finalizing changes, please wait.</p>',
							centerVertical: true,
							closeButton: false
						});
					}
				}
			}
		});
	}
	else
	{
		bootbox.alert("<p>Issue with submission, check tabs for required fields.</p>");
	}

});

$(document).on('click', '#add_past_menu_item:not(.disabled)', function (e) {

	displayModalWaitDialog('wait_for_adding_item_div', "Retrieving items. Please wait ...");

	let menu_id = $(this).data('menu_id');

	showPopup({
		modal: true,
		title: 'Add EFL Item',
		noOk: true,
		closeOnEscape: false,
		height: 720,
		div_id: 'add_menu_item_popup_div',
		module: 'page=admin_menu_editor_add_item&menu_id=' + menu_id,
		open: function (event, ui) {
			$(this).parent().find('.ui-dialog-titlebar-close').hide();
			$("#wait_for_adding_item_div").remove();
		},
		buttons: {
			Cancel: function () {
				$(this).remove();
			},
			Okay: function () {

				let entreeList = {};

				$('#sel_list').find('[data-rmv_menu_item]').each(function () {

					let thisEntree = $(this).data('entree_id');
					entreeList[thisEntree] = thisEntree;

				});

				dd_message({
					title: '',
					message: 'This will immediately add the listed items to the menu. Are you sure?',
					noOk: true,
					height: 180,
					buttons: {
						'Add Items to Menu': function () {

							bootbox.dialog({
								message: '<p><i class="fa fa-spin fa-spinner"></i> Adding menu items, please wait.</p>',
								centerVertical: true,
								closeButton: false
							});

							$.ajax({
								url: '/processor',
								type: 'POST',
								timeout: 20000,
								dataType: 'json',
								data: {
									processor: 'admin_menuEditor',
									store_id: STORE_DETAILS.id,
									op: 'add_menu_item',
									menu_id: menu_id,
									entree_ids: entreeList
								},
								success: function (json) {
									if (json.processor_success)
									{
										bounce('/backoffice/menu-editor?tab=nav-efl');
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

							$(this).remove();
							$('#add_menu_item_popup_div').remove();

						},
						'Cancel': function () {
							$(this).remove();
						}
					}
				});
			}

		},
		close: function () {
			bounce('/backoffice/menu-editor?tab=nav-efl');
		}
	});

});

$(document).on('click', '[data-add_menu_item]', function (e) {
	// prevent multiple submissions
	if ($(this).hasClass('disabled'))
	{

	}
	else
	{
		$(this).addClass('disabled');

		let entree_id = $(this).data('entree_id');
		let recipe_id = $(this).data('recipe_id');

		let tr = $('[data-recipe_id_row="' + recipe_id + '"]').clone();

		tr.find("[data-rmv_menu_item=" + entree_id + "]").show();
		tr.find("[data-add_menu_item=" + entree_id + "]").remove();

		$("#selected_items tbody").append(tr);
	}

});

$(document).on('click', '[data-rmv_menu_item]', function (e) {

	let entree_id = $(this).data('entree_id');
	let recipe_id = $(this).data('recipe_id');

	$('#selected_items tbody [data-recipe_id_row="' + recipe_id + '"]').remove();
	let org_tr = $('#recipe_list tbody [data-recipe_id_row="' + recipe_id + '"]');
	org_tr.find('[data-add_menu_item="' + entree_id + '"]').removeClass('disabled');

});

$(document).on('click', '[data-info_menu_item]', function (e) {

	let entree_id = $(this).data('entree_id');
	let recipe_id = $(this).data('recipe_id');

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_menuEditor',
			store_id: STORE_DETAILS.id,
			op: 'menu_item_info',
			menu_id: menu_id,
			entree_id: entree_id,
			recipe_id: recipe_id
		},
		success: function (json) {
			dd_message({
				modal: true,
				title: 'Item Information',
				height: 720,
				width: 720,
				message: json.data,
				open: function () {
					handle_tabbed_content();
				}
			});

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

$(document).on('click', '#add_past_menu_item_sides:not(.disabled)', function (e) {

	displayModalWaitDialog('wait_for_adding_item_div', "Retrieving Sides & Sweets. Please wait ...");

	let menu_id = $(this).data('menu_id');

	showPopup({
		modal: true,
		title: 'Add Sides & Sweets',
		noOk: true,
		closeOnEscape: false,
		height: 720,
		div_id: 'add_menu_item_popup_div',
		module: 'page=admin_menu_editor_add_sides_sweets&menu_id=' + menu_id,
		open: function (event, ui) {
			$(this).parent().find('.ui-dialog-titlebar-close').hide();
			$("#wait_for_adding_item_div").remove();
		},
		buttons: {
			Cancel: function () {
				$(this).remove();
			},
			Okay: function () {

				let entreeList = {};

				let errorMissingCategory = false;
				$('#sel_list').find('[data-rmv_menu_side]').each(function () {

					let thisEntree = $(this).data('entree_id');
					entreeList[thisEntree] = thisEntree;
					let categoryLabel = $("[data-recipe_category=" + thisEntree + "]:visible").find(":selected").val();
					if (typeof categoryLabel == 'undefined')
					{
						categoryLabel = $("[data-recipe_category=" + thisEntree + "]:visible").html();
					}

					if (typeof categoryLabel == 'undefined')
					{

						$("[data-recipe_category=" + thisEntree + "]").css("border-color", "red");
						errorMissingCategory = true;
					}
					else
					{
						entreeList[thisEntree] = categoryLabel;
					}
				});

				if (errorMissingCategory)
				{
					console.log('Missing');

					dd_message({
						title: 'Please select a category for all items',
						message: 'Please select a category for all items'
					});
				}
				else
				{
					dd_message({
						title: '',
						message: 'This will immediately add the listed Sides & Sweets to the menu. Are you sure?',
						noOk: true,
						height: 180,
						buttons: {
							'Add Sides & Sweets to Menu': function () {

								bootbox.dialog({
									message: '<p><i class="fa fa-spin fa-spinner"></i> Adding menu items, please wait.</p>',
									centerVertical: true,
									closeButton: false
								});

								$.ajax({
									url: '/processor',
									type: 'POST',
									timeout: 20000,
									dataType: 'json',
									data: {
										processor: 'admin_menuEditor',
										store_id: STORE_DETAILS.id,
										op: 'add_menu_side',
										menu_id: menu_id,
										entree_ids: entreeList
									},
									success: function (json) {
										if (json.processor_success)
										{
											bounce('/backoffice/menu-editor?tab=nav-sides-hidden');
										}
										else
										{
											dd_message({
												title: 'Error adding Sides & Sweets',
												message: json.processor_message
											});
										}
									},
									error: function (objAJAXRequest, strError) {
										response = 'Unexpected error: ' + strError;
										dd_message({
											title: 'Network Error adding Sides and Sweets',
											message: response
										});

									}

								});

								$(this).remove();
								$('#add_menu_item_popup_div').remove();

							},
							'Cancel': function () {
								$(this).remove();
							}
						}
					});
				}
			}

		},
		close: function () {
			bounce('/backoffice/menu-editor?tab=nav-sides-hidden');
		}
	});

});

$(document).on('change', '[data-recipe_category]', function (e) {
	let categoryLabel = $(this).find(":selected").val();

	if (typeof categoryLabel == 'undefined')
	{
		$(this).css("border-color", "red");
	}
	else
	{
		$(this).css("border-color", "black");
	}
});

$(document).on('click', '[data-add_menu_side]', function (e) {
	// prevent multiple submissions
	if ($(this).hasClass('disabled'))
	{

	}
	else
	{
		$(this).addClass('disabled');

		let entree_id = $(this).data('entree_id');
		let recipe_id = $(this).data('recipe_id');

		let tr = $('[data-recipe_id_row="' + recipe_id + '"]').clone();

		tr.find("[data-rmv_menu_side=" + entree_id + "]").show();
		tr.find("[data-add_menu_side=" + entree_id + "]").remove();

		tr.find("[data-recipe_category=" + entree_id + "]").show();

		let oldCategory = tr.find("[data-old_category=" + entree_id + "]").html();

		tr.find("[data-old_category=" + entree_id + "]").remove();

		$("#selected_items tbody").append(tr);

		$("[data-recipe_category=" + entree_id + "]").val(oldCategory);
	}

});

$(document).on('click', '[data-rmv_menu_side]', function (e) {

	let entree_id = $(this).data('entree_id');
	let recipe_id = $(this).data('recipe_id');

	$('#selected_items tbody [data-recipe_id_row="' + recipe_id + '"]').remove();
	let org_tr = $('#recipe_list tbody [data-recipe_id_row="' + recipe_id + '"]');
	org_tr.find('[data-add_menu_side="' + entree_id + '"]').removeClass('disabled');

});

$(document).on('click', '[data-info_menu_side]', function (e) {

	let entree_id = $(this).data('entree_id');
	let recipe_id = $(this).data('recipe_id');

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'admin_menuEditor',
			store_id: STORE_DETAILS.id,
			op: 'menu_item_info',
			menu_id: menu_id,
			entree_id: entree_id,
			recipe_id: recipe_id
		},
		success: function (json) {
			dd_message({
				modal: true,
				title: 'Item Information',
				height: 720,
				width: 720,
				message: json.data,
				open: function () {
					handle_tabbed_content();
				}
			});

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

$(document).on('keyup change', '#filter', function (e) {
	$.uiTableFilter($('#recipe_list'), this.value);
});

$(document).on('click', '#clear_filter', function (e) {

	$('#filter').val('').change();

	if (!Modernizr.input.placeholder)
	{
		// trick to restore placeholder on IE
		$('#filter').focus().blur();
	}

});

function showPopup(config)
{
	var settings = { //defaults
		type: 'GET',
		height: 500,
		width: 600,
		modal: false,
		callBack: false,
		resizable: true
	};

	$.extend(settings, config);

	$.ajax({
		url: '?' + settings.module,
		type: settings.type,
		success: function (data, status) {
			settings.message = data;

			dd_message(settings);

			if (typeof settings.callBack == 'function')
			{
				settings.callBack();
			}
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';
		}
	});
}