function addContact(guest)
{
	$('#contact').val($(guest).data('primary_email'));
}

$(function () {

	$(document).on('click', '#show_detailed_reporting', function (e) {

		$('#detailed_reporting').slideToggle();

	});

	$(document).on('click', '#add_offsitelocation, #add_offsite_cancel', function (e) {

		$('#add_offsitelocation_content').toggleFlex();

		$('#offiste_location_form').trigger('reset');

	});

	$(document).on('reset', '#offiste_location_form, #add_offsite_cancel', function (e) {

		$('#edit_location').val('false');
		$('#default_session_override').prop({'disabled': true}).val('');
		$('#offiste_location_form').removeClass('was-validated');
		$('[data-offsite_id]').removeClass('bg-green-light');

	});

	$(document).on('change', '#default_session_override_enable', function (e) {

		if ($(this).is(':checked'))
		{
			$('#default_session_override').prop({'disabled': false});
		}
		else
		{
			$('#default_session_override').prop({'disabled': true}).val('');
		}

	});

	$(document).on('submit', '#offiste_location_form', function (e) {

		e.preventDefault();

		let confirm_msg = 'Are you sure you wish to add a new location?';

		if ($('#edit_location').val() !== 'false')
		{
			confirm_msg = 'Are you sure you wish to edit this existing location?';
		}

		if ($(this)[0].checkValidity() === false)
		{
			e.stopPropagation();

			$('.btn-spinning').removeClass('btn-spinning');
			$('.ld-spin').remove();

		}
		else
		{
			bootbox.confirm(confirm_msg, function(result){
				if (result)
				{
					let context = {
						edit_location: $('#edit_location').val(),
						location: $('#location_name').val(),
						address_line1: $('#address_line1').val(),
						address_line2: $('#address_line2').val(),
						city: $('#city').val(),
						state: $('#state').val(),
						postal_code: $('#postal_code').val(),
						address_latitude: $('#address_latitude').val(),
						address_longitude: $('#address_longitude').val(),
						contact: $('#contact').val(),
						do_override: $('#default_session_override_enable').is(':checked'),
						default_override: $('#default_session_override').val(),
					};

					$.ajax({
						url: '/processor',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'admin_offsitelocation',
							store_id: STORE_DETAILS.id,
							op: 'add_offsite_info',
							data: context
						},
						success: function (json, status) {
							if (json.processor_success)
							{
								location.reload();
							}
							else
							{

							}
						},
						error: function (objAJAXRequest, strError) {
							response = 'Unexpected error';
						}
					});
				}
			});
		}

	});

	$(document).on('click', '[data-offsite_id_edit]', function (e) {

		$('#offiste_location_form').trigger('reset');

		let offsite_id = $(this).data('offsite_id_edit');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_offsitelocation',
				offsite_id: offsite_id,
				store_id: STORE_DETAILS.id,
				op: 'get_offsite_info'
			},
			success: function (json, status) {
				if (json.processor_success)
				{
					let data = $.parseJSON(json.data);

					$('#add_offsitelocation_content').showFlex();

					$('[data-offsite_id="' + offsite_id + '"]').addClass('bg-green-light');

					$('#edit_location').val(offsite_id);
					$('#location_name').val(data.location_title);
					$('#address_line1').val(data.address_line1);
					$('#address_line2').val(data.address_line2);
					$('#city').val(data.city);
					$('#state').val(data.state_id);
					$('#postal_code').val(data.postal_code);
					$('#address_latitude').val(data.address_latitude);
					$('#address_longitude').val(data.address_longitude);
					$('#contact').val(data.contact);

					if (data.default_session_override != null)
					{
						$('#default_session_override_enable').prop({'checked': true}).trigger('change');
						$('#default_session_override').val(data.default_session_override);
					}

				}
				else
				{

				}
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
			}

		});

	});

	$(document).on('click', '[data-enable_location]', function (e) {

		let location_id = $(this).data('enable_location');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_offsitelocation',
				store_id: STORE_DETAILS.id,
				op: 'toggle_offsitelocation',
				location_id: location_id
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
			}
		});

	});

});