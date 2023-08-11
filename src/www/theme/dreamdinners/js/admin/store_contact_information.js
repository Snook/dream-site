function store_contact_information_init()
{
	$('.telephone').mask('999-999-9999');

	handle_same_as_store();

	handle_additional_contacts();
}

function handle_same_as_store()
{
	$('#pkg_ship_same_as_store, #letter_ship_same_as_store').on('click', function () {

		var contact = $(this).data('contact');
		var set_disabled = true;

		if (!$(this).is(":checked"))
		{
			set_disabled = false;
		}
		else
		{
			$('#' + contact + '_is_commercial, #' + contact + '_address_line1, #' + contact + '_address_line2, #' + contact + '_city, #' + contact + '_state_id, #' + contact + '_postal_code, #' + contact + '_telephone_day').val(function () {

				return $(this).data('store_value');

			});
		}

		$('#' + contact + '_is_commercial, #' + contact + '_address_line1, #' + contact + '_address_line2, #' + contact + '_city, #' + contact + '_state_id, #' + contact + '_postal_code, #' + contact + '_telephone_day').attr({'disabled' : set_disabled});

	});
}

function handle_additional_contacts()
{
	$('#owner_2_name, #owner_3_name, #owner_4_name, #manager_1_name').keyup(function () {

		var contact = $(this).data('contact');
		var set_disabled = true;

		if ($(this).val())
		{
			set_disabled = false;
		}

		$('#' + contact + '_nickname, #' + contact + '_address_line1, #' + contact + '_address_line2, #' + contact + '_city, #' + contact + '_state_id, #' + contact + '_postal_code, #' + contact + '_telephone_primary, #' + contact + '_telephone_secondary, #' + contact + '_email_address').attr({'disabled' : set_disabled});

	});
}