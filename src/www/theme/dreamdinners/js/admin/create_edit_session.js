let session_type;
let initial_load = true;

function addHostess(guest)
{
	// check if they already exist in the list
	$('#session_host').val($(guest).data('primary_email'));
}

$(document).on('change', '#menu', function (e) {

	bounce('?page=admin_create_session&menu=' + $(this).val());

});

$(document).on('change', '#session_type, #dream_taste_theme, #fundraiser_theme, #fundraiser_recipient', function (e) {

	$('#session_edit').removeClass('was-validated');

});

$(document).on('change', '#session_type', function (e) {

	session_type = $(this).val();

	switch (session_type)
	{
		case 'SPECIAL_EVENT':
			$('#dream_taste_theme').prop({
				'disabled': true,
				'required': false
			});
			$('#dream_taste_theme_selection').hideFlex();
			$('#fundraiser_theme').prop({
				'disabled': true,
				'required': false
			});
			$('#fundraiser_theme_selection').hideFlex();
			$('#fundraiser_recipient_selection').hideFlex();
			$('#fundraiser_recipient').prop({
				'disabled': true,
				'required': false
			});
			$('#private_session_row').hideFlex();
			$('#session_password').prop({
				'disabled': true,
				'required': false,
				'placeholder': ''
			});
			$('#privatePartyHost').hideFlex();
			$('#session_host').prop({
				'disabled': true,
				'required': false
			});
			$('#discount_row').showFlex();
			$('#session_assembly_fee_row').showFlex();
			$('#session_assembly_fee_enable').prop({
				'disabled': false,
				'required': false
			});
			$('#session_assembly_fee').prop({
				'disabled': true,
				'required': false
			});
			$('#session_delivery_fee_row').hideFlex();
			$('#session_delivery_fee_enable').prop({
				'disabled': true,
				'required': false
			});
			$('#session_delivery_fee').prop({
				'disabled': true,
				'required': false
			});
			$('#session_pickup_location_row').hideFlex();
			$('#session_pickup_location').prop({
				'disabled': true,
				'required': false
			}).valDefault();
			$('#discount_value').prop({
				'disabled': false,
				'required': false
			});
			$('#session_discount_div').hideFlex();
			$('#intro_slots_row').showFlex();
			$('#introductory_slots').prop({
				'disabled': false,
				'required': true
			});
			$('#session_title_row').showFlex();
			$('#session_title').prop({
				'disabled': false,
				'required': false
			}).valDefault();
			$('#session_title_warn, #session_details_warn').text('').hideFlex();
			$('#session_details_row').hideFlex();
			$('#session_details').prop({
				'disabled': true,
				'required': false
			});
			$('#session_type_subtype_row').showFlex();
			$('#session_type_subtype').prop({
				'disabled': false,
				'required': false
			});
			$('#standard_session_type_subtype_row').hideFlex();
			$('#standard_session_type_subtype').prop({
				'disabled': true,
				'required': false
			}).valDefault();

			$('#meal_customization_close_row').showFlex();
			$('#meal_customization_close_interval_type').prop({
				'disabled': false,
				'required': true
			});
			$('#meal_customization_close_interval').prop({
				'disabled': false,
				'required': true
			});
			$('#session_type_subtype, #session_pickup_location').trigger('change').valDefault();
			break;
		case 'DREAM_TASTE':
			if ($('#dream_taste_theme').data('dd_type') != 'disable_locked')
			{
				$('#dream_taste_theme').prop({
					'disabled': false,
					'required': true
				});
			}
			$('#dream_taste_theme_selection').showFlex();
			$('#fundraiser_theme').prop({
				'disabled': true,
				'required': false
			});
			$('#fundraiser_theme_selection').hideFlex();
			$('#fundraiser_recipient_selection').hideFlex();
			$('#fundraiser_recipient').prop({
				'disabled': true,
				'required': false
			});
			$('#private_session_row').showFlex();
			$('#session_password').prop({
				'disabled': false,
				'required': true,
				'placeholder': ''
			});
			$('#privatePartyHost').showFlex();
			$('#session_host').prop({
				'disabled': false,
				'required': true
			});
			$('#discount_row').hideFlex();
			$('#session_assembly_fee_row').hideFlex();
			$('#session_assembly_fee_enable').prop({
				'disabled': true,
				'required': false
			});
			$('#session_assembly_fee').prop({
				'disabled': true,
				'required': false
			});
			$('#session_delivery_fee_row').hideFlex();
			$('#session_delivery_fee_enable').prop({
				'disabled': true,
				'required': false
			});
			$('#session_delivery_fee').prop({
				'disabled': true,
				'required': false
			});
			$('#session_pickup_location_row').hideFlex();
			$('#session_pickup_location').prop({
				'disabled': true,
				'required': false
			}).valDefault();
			$('#discount_value').prop({
				'disabled': true,
				'required': false
			});
			$('#session_discount_div').hideFlex();
			$('#intro_slots_row').hideFlex();
			$('#introductory_slots').prop({
				'disabled': true,
				'required': false
			});
			$('#session_title_row').hideFlex();
			$('#session_title').prop({
				'disabled': true,
				'required': false
			}).valDefault();
			$('#session_title_warn, #session_details_warn').text('').hideFlex();
			$('#session_details_row').showFlex();
			$('#session_details').prop({
				'disabled': false,
				'required': false
			});
			$('#session_type_subtype_row').hideFlex();
			$('#session_type_subtype').prop({
				'disabled': true,
				'required': false
			});
			$('#standard_session_type_subtype_row').hideFlex();
			$('#standard_session_type_subtype').prop({
				'disabled': true,
				'required': false
			}).valDefault();
			$('#meal_customization_close_row').hideFlex();
			$('#meal_customization_close_interval_type').prop({
				'disabled': true,
				'required': false
			});
			$('#meal_customization_close_interval').prop({
				'disabled': true,
				'required': false
			});
			$('#dream_taste_theme').trigger('change');
			break;
		case 'FUNDRAISER':
			$('#dream_taste_theme').prop({
				'disabled': true,
				'required': false
			});
			$('#dream_taste_theme_selection').hideFlex();
			if ($('#fundraiser_theme').data('dd_type') != 'disable_locked')
			{
				$('#fundraiser_theme').prop({
					'disabled': false,
					'required': true
				});
			}
			$('#fundraiser_theme_selection').showFlex();
			$('#fundraiser_recipient_selection').showFlex();
			if ($('#fundraiser_recipient').data('dd_type') != 'disable_locked')
			{
				$('#fundraiser_recipient').prop({
					'disabled': false,
					'required': true
				});
			}
			$('#private_session_row').hideFlex();
			$('#session_password').prop({
				'disabled': true,
				'required': false,
				'placeholder': ''
			});
			$('#privatePartyHost').showFlex();
			$('#session_host').prop({
				'disabled': false,
				'required': false
			});
			$('#discount_row').hideFlex();
			$('#session_assembly_fee_row').hideFlex();
			$('#session_assembly_fee_enable').prop({
				'disabled': true,
				'required': false
			});
			$('#session_assembly_fee').prop({
				'disabled': true,
				'required': false
			});
			$('#session_delivery_fee_row').hideFlex();
			$('#session_delivery_fee_enable').prop({
				'disabled': true,
				'required': false
			});
			$('#session_delivery_fee').prop({
				'disabled': true,
				'required': false
			});
			$('#session_pickup_location_row').hideFlex();
			$('#session_pickup_location').prop({
				'disabled': true,
				'required': false
			}).valDefault();
			$('#discount_value').prop({
				'disabled': true,
				'required': false
			});
			$('#session_discount_div').hideFlex();
			$('#intro_slots_row').hideFlex();
			$('#introductory_slots').prop({
				'disabled': true,
				'required': false
			});
			$('#session_title_row').hideFlex();
			$('#session_title').prop({
				'disabled': true,
				'required': false
			}).valDefault();
			$('#session_title_warn, #session_details_warn').text('').hideFlex();
			$('#session_details_row').showFlex();
			$('#session_details').prop({
				'disabled': false,
				'required': false
			});
			$('#session_type_subtype_row').hideFlex();
			$('#session_type_subtype').prop({
				'disabled': true,
				'required': false
			});
			$('#standard_session_type_subtype_row').hideFlex();
			$('#standard_session_type_subtype').prop({
				'disabled': true,
				'required': false
			}).valDefault();
			$('#meal_customization_close_row').hideFlex();
			$('#meal_customization_close_interval_type').prop({
				'disabled': true,
				'required': false
			});
			$('#meal_customization_close_interval').prop({
				'disabled': true,
				'required': false
			});
			$('#fundraiser_theme, #fundraiser_recipient').trigger('change');
			break;
		case 'STANDARD':
		default:
			$('#dream_taste_theme').prop({
				'disabled': true,
				'required': false
			});
			$('#dream_taste_theme_selection').hideFlex();
			$('#fundraiser_theme').prop({
				'disabled': true,
				'required': false
			});

			$('#fundraiser_theme_selection').hideFlex();
			$('#fundraiser_recipient_selection').hideFlex();
			$('#fundraiser_recipient').prop({
				'disabled': true,
				'required': false
			});
			$('#private_session_row').showFlex();
			$('#session_password').prop({
				'disabled': false,
				'required': false,
				'placeholder': ''
			});
			$('#privatePartyHost').hideFlex();
			$('#session_host').prop({
				'disabled': true,
				'required': false
			});
			$('#discount_row').showFlex();
			$('#session_assembly_fee_row').hideFlex();
			$('#session_assembly_fee_enable').prop({
				'disabled': true,
				'required': false
			});
			$('#session_assembly_fee').prop({
				'disabled': true,
				'required': false
			});
			$('#session_delivery_fee_row').hideFlex();
			$('#session_delivery_fee_enable').prop({
				'disabled': true,
				'required': false
			});
			$('#session_delivery_fee').prop({
				'disabled': true,
				'required': false
			});
			$('#session_pickup_location_row').hideFlex();
			$('#session_pickup_location').prop({
				'disabled': true,
				'required': false
			}).valDefault();
			$('#discount_value').prop({
				'disabled': false,
				'required': false
			});
			$('#session_discount_div').hideFlex();
			$('#intro_slots_row').showFlex();
			$('#introductory_slots').prop({
				'disabled': false,
				'required': false
			});
			$('#session_title_row').showFlex();
			$('#session_title').prop({
				'disabled': false,
				'required': false
			});
			$('#session_title_warn, #session_details_warn').text('').hideFlex();
			$('#session_details_row').hideFlex();
			$('#session_details').prop({
				'disabled': true,
				'required': false
			});
			$('#session_type_subtype_row').hideFlex();
			$('#session_type_subtype').prop({
				'disabled': true,
				'required': false
			});
			$('#standard_session_type_subtype_row').showFlex();
			$('#standard_session_type_subtype').prop({
				'disabled': false,
				'required': true
			}).valDefault();

			$('#meal_customization_close_row').hideFlex();
			$('#meal_customization_close_interval_type').prop({
				'disabled': true,
				'required': false
			});
			$('#meal_customization_close_interval').prop({
				'disabled': true,
				'required': false
			});
			break;
	}

	$('#session_password, #standard_session_type_subtype, #session_title, #session_details, #admin_notes, #session_type_subtype, #session_assembly_fee_enable, #session_delivery_fee_enable').trigger('change');

	initial_load = false;
});

$(document).on('change', '#custom_close_interval', function (e) {

	$('#close_interval_typeHOURS').prop('checked', true);

});

function minutesToTime(minutes)
{
	let newHour = Math.floor(minutes / 60);
	let newMinutes = minutes - (newHour * 60);

	newHour = newHour.toString();
	newMinutes = newMinutes.toString();

	if (newHour.length == 1)
	{
		newHour = "0" + newHour;
	}
	if (newMinutes.length == 1)
	{
		newMinutes = "0" + newMinutes;
	}

	return newHour + ":" + newMinutes;
}

$(document).on("change", "#session_time", function () {
	let startVal = $(this).val();
	let endVal = $("#session_end_time").val();
	let duration = $("#duration_minutes").val();

	let startMinutes = 0;
	let endMinutes = 0;
	if (startVal != "")
	{
		startMinutes = startVal.split(":")[0] * 60 + (startVal.split(":")[1] * 1);
	}

	if (startVal != "")
	{
		if (duration == 0)
		{
			$("#session_end_time").val(startVal);
		}
		else
		{
			endMinutes = startMinutes + (duration * 1);
			if (endMinutes >= 1440)
			{
				duration = 1439 - startMinutes;
				endMinutes = 1439;
				$("#duration_minutes").val(duration);
			}

			$("#session_end_time").val(minutesToTime(endMinutes));
		}
	}
});

$(document).on("change", "#session_end_time", function () {
	let startVal = $("#session_time").val();
	let endVal = $(this).val();
	let duration = $("#duration_minutes").val();
	let startMinutes = 0;
	let endMinutes = 0;
	if (endVal != "")
	{
		endMinutes = endVal.split(":")[0] * 60 + (endVal.split(":")[1] * 1);
	}

	if (startVal != "" && endVal != "")
	{
		startMinutes = startVal.split(":")[0] * 60 + (startVal.split(":")[1] * 1);
		if (startVal > endVal)
		{
			if (duration == 0)
			{
				$("#session_end_time").val(startVal);
			}
			else
			{
				endMinutes = startMinutes + (duration * 1);
				if (endMinutes >= 1440)
				{
					$("#duration_minutes").val(1439 - startMinutes);
					endMinutes = 1439;
					$("#session_end_time").val(minutesToTime(endMinutes));
				}
				else
				{
					$("#session_end_time").val(minutesToTime(endMinutes));
				}
			}
		}
		else
		{
			$("#duration_minutes").val(endMinutes - startMinutes);
		}
	}
});

$(document).on("change", "#duration_minutes", function () {

	let newDuration = $(this).val();

	if (newDuration < 0)
	{
		newDuration = 0;
		$("#duration_minutes").val(0);
	}
	let startVal = $("#session_time").val();
	if (startVal.length == 8)
	{
		//drop seconds
		startVal = startVal.substr(0, 5);
	}

	if (startVal != "")
	{
		let startMinutes = startVal.split(":")[0] * 60 + (startVal.split(":")[1] * 1);
		let endMinutes = startMinutes + (newDuration * 1);

		if (endMinutes >= 1440)
		{
			duration = 1439 - startMinutes;
			endMinutes = 1439;
			$("#duration_minutes").val(duration);
		}

		$("#session_end_time").val(minutesToTime(endMinutes));
	}
});

$(document).on('change', '#dream_taste_theme, #fundraiser_theme', function (e) {

	let password_required, host_required;

	switch (session_type)
	{
		case 'FUNDRAISER':
			password_required = $(this).find(':selected').data('password_required');
			host_required = $(this).find(':selected').data('host_required');
			break;
		case 'DREAM_TASTE':
		default:
			password_required = $(this).find(':selected').data('password_required');
			host_required = $(this).find(':selected').data('host_required');
			break;
	}

	switch (password_required)
	{
		case 1:
			$('#session_password').prop({
				'disabled': false,
				'required': true,
				'placeholder': '*Required'
			});
			break;
		case 2:
			$('#session_password').prop({
				'disabled': false,
				'required': false,
				'placeholder': '*Optional'
			});
			break;
		default:
			$('#session_password').prop({
				'disabled': true,
				'required': false,
				'placeholder': '*Optional'
			});
			break;
	}

	switch (host_required)
	{
		case 1:
			$('#privatePartyHost').showFlex();
			$('#session_host').prop({
				'disabled': false,
				'required': true,
				'placeholder': '*Required email address or user id'
			});
			break;
		case 2:
			$('#privatePartyHost').showFlex();
			$('#session_host').prop({
				'disabled': false,
				'required': false,
				'placeholder': '*Optional email address or user id'
			});
			break;
		default:
			$('#privatePartyHost').hideFlex();
			$('#session_host').prop({
				'disabled': true,
				'required': false,
				'placeholder': '*Optional Email address or user id'
			});
			break;
	}

});

$(document).on('change', '#standard_session_type_subtype', function (e) {

	let standard_session_type_subtype = $(this).val();

	if (session_type == 'STANDARD')
	{
		switch (standard_session_type_subtype)
		{
			case 'PRIVATE_SESSION':
				$('#private_session_row').showFlex();
				$('#session_password').prop({
					'disabled': false,
					'required': true
				});
				$('#privatePartyHost').showFlex();
				$('#session_host').prop({
					'disabled': false,
					'required': true
				});
				$('#session_title_row').hideFlex();
				$('#session_title').prop({
					'disabled': true,
					'required': false
				});
				$('#session_details_row').showFlex();
				$('#session_details').prop({
					'disabled': false,
					'required': false
				});
				break;
			case 'STANDARD':
			default:
				$('#private_session_row').hideFlex();
				$('#session_password').prop({
					'disabled': true,
					'required': false
				});
				$('#privatePartyHost').hideFlex();
				$('#session_host').prop({
					'disabled': true,
					'required': false
				});
				$('#session_title_row').showFlex();
				$('#session_title').prop({
					'disabled': false,
					'required': false
				});
				$('#session_details_row').hideFlex();
				$('#session_details').prop({
					'disabled': true,
					'required': false
				});
		}
	}

});

$(document).on('change', '#session_type_subtype', function (e) {

	let session_type_subtype = $(this).val();

	if (session_type == 'SPECIAL_EVENT')
	{

		switch (session_type_subtype)
		{
			case 'REMOTE_PICKUP':
				$('#session_pickup_location_row').showFlex();
				$('#session_pickup_location').prop({
					'disabled': false,
					'required': true
				});
				$('#private_session_row').hideFlex();
				$('#session_password').prop({
					'disabled': true,
					'required': false
				});
				$('#privatePartyHost').hideFlex();
				$('#session_host').prop({
					'disabled': true,
					'required': false
				});
				$('#session_title_row').showFlex();
				$('#session_title').prop({
					'disabled': false,
					'required': false
				});
				$('#session_details_row').hideFlex();
				$('#session_details').prop({
					'disabled': true,
					'required': false
				});
				$('#session_delivery_fee_row').showFlex();
				$('#session_delivery_fee_enable').prop({
					'disabled': false,
					'required': false
				});
				$('#session_delivery_fee').prop({
					'disabled': true,
					'required': false
				});
				break;
			case 'REMOTE_PICKUP_PRIVATE':
				$('#session_pickup_location_row').showFlex();
				$('#session_pickup_location').prop({
					'disabled': false,
					'required': true
				});
				$('#private_session_row').showFlex();
				$('#session_password').prop({
					'disabled': false,
					'required': true
				});
				$('#privatePartyHost').showFlex();
				$('#session_host').prop({
					'disabled': false,
					'required': true
				});
				$('#session_title_row').hideFlex();
				$('#session_title').prop({
					'disabled': true,
					'required': false
				}).val('');
				$('#session_details_row').showFlex();
				$('#session_details').prop({
					'disabled': false,
					'required': false
				});
				$('#session_delivery_fee_row').showFlex();
				$('#session_delivery_fee_enable').prop({
					'disabled': false,
					'required': false
				});
				$('#session_delivery_fee').prop({
					'disabled': true,
					'required': false
				});
				break;
			case 'DELIVERY':
				$('#session_pickup_location_row').hideFlex();
				$('#session_pickup_location').prop({
					'disabled': true,
					'required': false
				});
				$('#private_session_row').hideFlex();
				$('#session_password').prop({
					'disabled': true,
					'required': false,
					'placeholder': ''
				});
				$('#privatePartyHost').hideFlex();
				$('#session_host').prop({
					'disabled': true,
					'required': false
				});
				$('#session_title_row').showFlex();
				$('#session_title').prop({
					'disabled': false,
					'required': false
				});
				$('#session_details_row').hideFlex();
				$('#session_details').prop({
					'disabled': true,
					'required': false
				});
				$('#session_delivery_fee_row').showFlex();
				$('#session_delivery_fee_enable').prop({
					'disabled': false,
					'required': false
				});
				$('#session_delivery_fee').prop({
					'disabled': true,
					'required': false
				});
				break;
			case 'DELIVERY_PRIVATE':
				$('#session_pickup_location_row').hideFlex();
				$('#session_pickup_location').prop({
					'disabled': true,
					'required': false
				});
				$('#private_session_row').showFlex();
				$('#session_password').prop({
					'disabled': false,
					'required': true,
					'placeholder': ''
				});
				$('#privatePartyHost').showFlex();
				$('#session_host').prop({
					'disabled': false,
					'required': false
				});
				$('#session_title_row').showFlex();
				$('#session_title').prop({
					'disabled': false,
					'required': false
				});
				$('#session_details_row').showFlex();
				$('#session_details').prop({
					'disabled': false,
					'required': false
				});
				$('#session_delivery_fee_row').showFlex();
				$('#session_delivery_fee_enable').prop({
					'disabled': false,
					'required': false
				});
				$('#session_delivery_fee').prop({
					'disabled': true,
					'required': false
				});
				break;
			case 'WALK_IN':
				$('#session_pickup_location_row').hideFlex();
				$('#session_pickup_location').prop({
					'disabled': true,
					'required': false
				});
				$('#private_session_row').showFlex();
				$('#session_password').prop({
					'disabled': false,
					'required': true,
					'placeholder': ''
				});
				$('#privatePartyHost').showFlex();
				$('#session_host').prop({
					'disabled': false,
					'required': false
				});
				$('#session_title_row').showFlex();
				$('#session_title').prop({
					'disabled': false,
					'required': false
				});
				$('#session_details_row').showFlex();
				$('#session_details').prop({
					'disabled': false,
					'required': false
				});
				$('#session_delivery_fee_row').showFlex();
				$('#session_delivery_fee_enable').prop({
					'disabled': false,
					'required': false
				});
				$('#session_delivery_fee').prop({
					'disabled': true,
					'required': false
				});
				break;
			case 'PICKUP':
			default:
				$('#session_pickup_location_row').hideFlex();
				$('#session_pickup_location').prop({
					'disabled': true,
					'required': false
				});
				$('#private_session_row').hideFlex();
				$('#session_password').prop({
					'disabled': true,
					'required': false,
					'placeholder': ''
				});
				$('#privatePartyHost').hideFlex();
				$('#session_host').prop({
					'disabled': true,
					'required': false
				});
				$('#session_title_row').showFlex();
				$('#session_title').prop({
					'disabled': false,
					'required': false
				});
				$('#session_details_row').hideFlex();
				$('#session_details').prop({
					'disabled': true,
					'required': false
				});
				$('#session_delivery_fee_row').hideFlex();
				$('#session_delivery_fee_enable').prop({
					'disabled': true,
					'required': false
				});
				$('#session_delivery_fee').prop({
					'disabled': true,
					'required': false
				});
				break;
		}

	}

});

$(document).on('change', '#session_assembly_fee_enable', function (e) {

	if ($(this).is(':checked'))
	{
		$('#session_assembly_fee').prop({
			'disabled': false,
			'required': true
		});
	}
	else
	{
		$('#session_assembly_fee').prop({
			'disabled': true,
			'required': false
		}).valDefault();
	}

});

$(document).on('change', '#session_delivery_fee_enable', function (e) {

	if ($(this).is(':checked'))
	{
		$('#session_delivery_fee').prop({
			'disabled': false,
			'required': true
		});
	}
	else
	{
		$('#session_delivery_fee').prop({
			'disabled': true,
			'required': false
		}).valDefault();
	}

});

$(document).on('change', '#session_pickup_location', function (e) {

	let location_title = $(this).find(':selected').data('location_title');
	let default_session_override = $(this).find(':selected').data('default_session_override');

	if (!initial_load || (initial_load && !$('#session_assembly_fee_enable').is(':checked')))
	{
		if ($(this)[0].selectedIndex == 0)
		{
			$('#session_assembly_fee_enable').prop({'checked': false}).trigger('change');
		}
		else
		{
			if (typeof location_title !== 'undefined')
			{
				$('#session_title').val(location_title);
			}

			if (typeof location_title !== 'undefined' && default_session_override !== false)
			{
				$('#session_assembly_fee_enable').prop({'checked': true}).trigger('change');
				$('#session_assembly_fee').val(default_session_override);
			}
			else if (typeof location_title !== 'undefined' && default_session_override === false)
			{
				$('#session_assembly_fee_enable').prop({'checked': false}).trigger('change');
			}
		}
	}

});

$(document).on('keyup change', '#session_title, #session_details, #admin_notes', function (e) {

	$('#session_title_warn, #session_details_warn').text('').hideFlex();

	if ((session_type == 'SPECIAL_EVENT'))
	{
		match = $('#session_title').val().match(new RegExp("\\b" + [
			'home delivery',
			'made for you',
			'made 4 you',
			'm4u',
			'mfy',
			'assembly fee',
			'delivery fee',
			'pre(.*)assembled'
		].join('|') + "\\b", 'gi'));

		if (match != null && match.length)
		{
			$('#session_title_warn').text('The title mentions "' + match.join(', ') + '", this is likely unnecessary and may appear redundant or add clutter on small devices when displayed on the customer site.').showFlex();
		}
	}

	if ((session_type == 'DREAM_TASTE' || session_type == 'STANDARD'))
	{
		match = $('#session_details').val().match(new RegExp("\\b" + [
			'pass(.*)word',
			'invite code'
		].join('|') + "\\b", 'gi'));

		if (match != null && match.length)
		{
			$('#session_details_warn').text('The details mention "' + match.join(', ') + '", this may be unnecessary to display publicly, another session type may be more appropriate.').showFlex();
		}
	}

});

$(document).on('click', '#submit_delete', function (e) {

	e.stopPropagation();
	e.preventDefault();

	bootbox.confirm("Are you sure you wish to delete this session?", function (result) {

		if (result)
		{
			$('#session_edit').removeClass('needs-validation');

			$('<input>').attr({
				type: 'hidden',
				name: 'submit_delete',
				value: true
			}).appendTo('#session_edit');

			$('#session_edit').submit();
		}

	});

});

$('#session_type').trigger('change'); // initial_load
$('#session_submit').prop('disabled', false);