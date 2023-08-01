$(document).on('click', '#recipe_id_button', function (e) {

	e.preventDefault();

	if ($(this).is('[readonly]') || $(this).hasClass('disabled'))
	{
		return;
	}

	bootbox.prompt("Search recipe name or id", function (result) {

		if (!result)
		{
			return;
		}

		let search = result.trim();
		let menu_start = $('#valid_menuspan_start').val();
		let menu_end = $('#valid_menuspan_end').val();

		// search was an empty string, just close
		if (search.length === 0)
		{
			return;
		}

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_helpers',
				op: 'recipe_select',
				search: search,
				menu_start: menu_start,
				menu_end: menu_end,
			},
			success: function (json) {
				if (json.processor_success)
				{
					bootbox.dialog({
						message: json.html,
						scrollable: true,
						centerVertical: true,
						callback: function (result) {
							if (result)
							{
							}
						}
					});
				}
			},
			error: function (objAJAXRequest, strError) {
				strError = 'Unexpected error';
			}
		});

	});

});

$(document).on('click', '.select-recipe-id', function (e) {

	let recipe_id = $(this).data('recipe_id');
	let pricing_type = $(this).data('pricing_type');
	let menu_item_name = $(this).data('menu_item_name');
	let pricing_type_name_short = $(this).data('pricing_type_name_short');

	$('#recipe_id').val(recipe_id);
	$('#recipe_id_pricing_type').val(pricing_type);

	$('#recipe_id_button').text(menu_item_name + ' (' + pricing_type_name_short + ')');

	bootbox.hideAll();

});


$(document).on('change', 'input[type=radio][name=limit_coupon]', function (e) {

	if ($(this).val() === 'limit_to_recipe_id')
	{
		$('#recipe_id, #recipe_id_button').prop({'disabled': false});
	}
	else
	{
		$('#recipe_id, #recipe_id_button').prop({'disabled': true});
	}

});

$(document).on('click', '.valid_timespan_start_now', function (e) {

	let d = new Date(),
		month = d.getMonth() + 1,
		year = d.getFullYear();

	if (month < 10)
	{
		month = '0' + month;
	}

	let date_start = year + '-' + month + '-01';

	$('#valid_timespan_start').val(date_start);

});

$(document).on('click', '.valid_timespan_end_max', function (e) {

	$('#valid_timespan_end').val('2037-12-31');

});

$(document).on('click', '[id^="valid_corporate_crate_client_id"]', function (e) {

	if ($(this).data('client_id') == 'ALL')
	{
		$('[id^="valid_corporate_crate_client_id"]').each(function () {
			if ($(this).data('client_id') != 'ALL')
			{
				$(this).prop('checked', false);
			}
		});
	}
	else
	{
		$('#valid_corporate_crate_client_id\\[ALL\\]').prop('checked', false);
	}

});

$(document).on('change', '#is_store_specific', function (e) {

	if ($(this).val() == '1')
	{
		$('#multi_store_select_button').prop('disabled', false);
		$('#multi_store_select').prop('disabled', false);
	}
	else
	{
		$('#multi_store_select_button').prop('disabled', true);
		$('#multi_store_select').prop('disabled', true);
	}

});

$(document).on('change', '#is_store_coupon, #is_delivered_coupon, #is_product_coupon', function (e) {

	$('.section-store_coupon').hideFlex();
	$('.section-delivered_coupon').hideFlex();
	$('.section-product_coupon').hideFlex();

	switch ($(this).attr('id'))
	{
		case 'is_store_coupon':
			if ($(this).val() == '1')
			{
				$('#is_product_coupon').val('0');
				$('#valid_for_product_type_membership').val('0');
			}
			break;
		case 'is_delivered_coupon':
			if ($(this).val() == '1')
			{
				$('#is_product_coupon').val('0');
				$('#valid_for_product_type_membership').val('0');
			}
			break;
		case 'is_product_coupon':
			if ($(this).val() == '1')
			{
				$('#is_store_coupon').val('0');
				$('#is_delivered_coupon').val('0');
			}
			break;
	}

	if ($('#is_store_coupon').val() == '1')
	{
		$('.section-store_coupon').showFlex();
	}

	if ($('#is_delivered_coupon').val() == '1')
	{
		$('.section-delivered_coupon').showFlex();
	}

	if ($('#is_product_coupon').val() == '1')
	{
		$('.section-product_coupon').showFlex();
	}

	// disable hidden inputs
	$("form#coupon_edit :input").not("input[type='hidden']").each(function() {

		if (!$(this).is('[readonly]'))
		{
			if ($(this).is(":visible"))
			{
				$(this).prop('disabled', false);
			}
			else
			{
				$(this).prop('disabled', true);
			}
		}

	});

	$('input[type=radio][name=limit_coupon]:checked').trigger('change');

	$('#applicable_customer_type').trigger('change');

});

$(document).on('change', '#valid_menuspan_start, #valid_menuspan_end', function (e) {

	switch ($(this).attr('id'))
	{
		case 'valid_menuspan_start':
			if ($(this).val() == '')
			{
				$('#valid_menuspan_end').val('');
			}
			else if ($(this).val() > $('#valid_menuspan_end').val() || $('#valid_menuspan_end').val() == '')
			{
				$('#valid_menuspan_end').val($(this).val());
			}
			break;
		case 'valid_menuspan_end':
			if ($(this).val() == '')
			{
				$('#valid_menuspan_start').val('');
			}
			else if ($(this).val() < $('#valid_menuspan_start').val() || $('#valid_menuspan_start').val() == '')
			{
				$('#valid_menuspan_start').val($(this).val());
			}
			break;
	}

});

$(document).on('click', '#valid_session_timespan_start_clear, #valid_session_timespan_end_clear', function (e) {

	e.preventDefault();

	switch ($(this).attr('id'))
	{
		case 'valid_session_timespan_start_clear':
			$('#valid_session_timespan_start').val('');
			$('#valid_session_timespan_end').val('');
			break;
		case 'valid_session_timespan_end_clear':
			$('#valid_session_timespan_end').val('');
			break;
	}

});

$(document).on('change', '#applicable_customer_type', function (e) {

	switch ($(this).val())
	{
		case 'REMISS_N_MONTHS':
			$('#applicable_customer_type_description').text("A guest who has previously ordered but has not attended a session within the specified number of months. New guests can not use this coupon.");
			$('#remiss_number_of_months').prop({'required': true, 'disabled': false});
			$('#remiss_cutoff_date').prop({'required': false, 'disabled': true}).val('');
			break;
		case 'NEW_OR_REMISS_N_MONTHS':
			$('#applicable_customer_type_description').text("The guest is new, or, a guest who has previously ordered but has not attended a session within the specified number of months.");
			$('#remiss_number_of_months').prop({'required': true, 'disabled': false});
			$('#remiss_cutoff_date').prop({'required': false, 'disabled': true}).val('');
			break;
		case 'REMISS_SINCE_DATE':
			$('#applicable_customer_type_description').text("A guest who has previously ordered but has not attended a session since the specified date. New guests can not use this coupon.");
			$('#remiss_number_of_months').prop({'required': false, 'disabled': true}).val('');
			$('#remiss_cutoff_date').prop({'required': true, 'disabled': false});
			break;
		case 'NEW_OR_REMISS':
			$('#applicable_customer_type_description').text("The guest is new, or, a guest who has previously ordered but has not attended a session since the specified date.");
			$('#remiss_number_of_months').prop({'required': false, 'disabled': true}).val('');
			$('#remiss_cutoff_date').prop({'required': true, 'disabled': false});
			break;
		case 'NEW':
			$('#applicable_customer_type_description').text("The guest is new, has never ordered before.");
			$('#remiss_number_of_months').prop({'required': false, 'disabled': true}).val('');
			$('#remiss_cutoff_date').prop({'required': false, 'disabled': true}).val('');
			break;
		case 'EXISTING':
			$('#applicable_customer_type_description').text("The guest must have attended a session any time in the past.");
			$('#remiss_number_of_months').prop({'required': false, 'disabled': true}).val('');
			$('#remiss_cutoff_date').prop({'required': false, 'disabled': true}).val('');
			break;
		case 'ALL':
			$('#applicable_customer_type_description').text("All guests will be able to use the coupon.");
			$('#remiss_number_of_months').prop({'required': false, 'disabled': true}).val('');
			$('#remiss_cutoff_date').prop({'required': false, 'disabled': true}).val('');
			break;
		default:
			$('#remiss_number_of_months').prop({'required': false, 'disabled': true}).val('');
			$('#remiss_cutoff_date').prop({'required': false, 'disabled': true}).val('');
	}

});

$(document).on('change', '#discount_method', function (e) {

	switch ($(this).val())
	{
		case 'FLAT':
			$('#menu_item_id').prop('required', false).val('');
			$('#discount_var').prop('required', true);
			break;
		case 'PERCENT':
			$('#menu_item_id').prop('required', false).val('');
			$('#discount_var').prop('required', true);
			break;
		case 'FREE_MEAL':
			$('#menu_item_id').prop('required', true);
			$('#discount_var').prop('required', false).val('');
			break;
		case 'FREE_MENU_ITEM':
			$('#menu_item_id').prop('required', true);
			$('#discount_var').prop('required', false).val('');
			break;
		case 'BONUS_CREDIT':
			$('#menu_item_id').prop('required', false).val('');
			$('#discount_var').prop('required', true);
			break;
		default:
			$('#menu_item_id').prop('required', false).val('');
			$('#discount_var').prop('required', false).val('');
	}

});

$(document).on('click', '.coupon-search', function (e) {

	e.preventDefault();

	let code = $('#edit_coupon_code').val().trim();

	if (code.length !== 0)
	{
		create_and_submit_form({
			action: 'main.php?page=admin_manage_coupon_codes',
			input: ({
				'search_code': code
			})
		});
	}

});

$(document).ready(function () {
	$('#is_store_coupon, #is_delivered_coupon, #is_product_coupon').trigger('change');
});