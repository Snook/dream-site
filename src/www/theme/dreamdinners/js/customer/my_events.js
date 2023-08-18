function emailIsNew(email)
{
	var isNew = true;

	$.each(eventReferrals, function (key, value) {
		if (value.referred_user_email === email)
		{
			isNew = false;
		}
	});

	return isNew;
}

function updateEmailCounter()
{
	var count = 0;

	$.each(eventReferrals, function (key, value) {
		if (value.referred_user_send_email == true)
		{
			count++;
		}
	});

	$('#number_of_emails').text('');
	$('.send-event-emails').addClass('disabled');

	if (count > 0)
	{
		$('#number_of_emails_plural').show();

		if (count == 1)
		{
			$('#number_of_emails_plural').hide();
		}

		$('#number_of_emails').text(count);
		$('.send-event-emails').removeClass('disabled');
	}
}

function setEventReferral(id, properties)
{
	if (typeof eventReferrals[id] == 'undefined')
	{
		eventReferrals[id] = properties;
	}
	else
	{
		$.each(properties, function (key, value) {
			eventReferrals[id][key] = value;
		});
	}

	updateEmailCounter();
}

function unsetEventReferral(id)
{
	if (typeof eventReferrals[id] != 'undefined')
	{
		delete eventReferrals[id];
	}

	updateEmailCounter();
}

function add_contact(name, email)
{
	// add if only if it hasn't already been added
	if (!$('[data-email-address-div="' + email + '"]').length)
	{
		var add_div = $('.add-event-contact-div').clone();

		$(add_div).find('.add-contact-email').removeClass('add-contact-email is-invalid').attr('disabled', true).val(email);
		$(add_div).find('.add-contact-name').removeClass('add-contact-name').attr('disabled', true).val(name);
		$(add_div).removeClass('add-event-contact-div').attr('data-email-address-div', email);

		$(add_div).find('.add-event-contact').data('email-address', email).removeClass('add-event-contact disabled btn-primary').addClass('remove-event-contact btn-warning').find('.fas').removeClass('fa-plus').addClass('fa-minus');

		$(add_div).insertBefore('.add-event-contact-div');

		setEventReferral(email, {
			'referred_user_name': name,
			'referred_user_email': email,
			'referred_user_send_email': true
		});
	}
}

function list_contacts(contacts)
{
	if (contacts.length > 0)
	{
		bootbox.dialog({
			scrollable: true,
			title: "Select Contacts",
			message: contacts,
			buttons: {
				add: {
					label: 'Add selected',
					className: 'btn-primary',
					callback: function () {

						$('[id^="import_id-"]').each(function (e) {

							if ($(this).is(':checked'))
							{
								var name = $(this).data('name');
								var email = $(this).data('email');

								add_contact(name, email);
							}

						});

					}
				}
			}
		});
	}
	else
	{
		modal_message({
			title: 'Error',
			message: 'No email addresses found in address book.'
		});
	}
}

$(function () {

	$(document).on('change keyup', '.add-contact-email', function (e) {

		var email = $(this).val().trim();
		var isNewEmail = emailIsNew(email);

		$(this).removeClass('is-invalid');

		if (!isNewEmail)
		{
			$(this).addClass('is-invalid');
		}

		if (isNewEmail && email != '')
		{
			var key = e.which;

			if (key == 13)
			{
				$('.add-event-contact').trigger('click');
				return false;
			}

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'data_lookup',
					type: 'valid_email',
					check: 'email',
					rules: {
						not_self: true
					},
					email: email
				},
				success: function (json, status) {
					if (json.processor_success && emailIsNew(email))
					{
						$('.add-event-contact').removeClass('disabled');
					}
					else
					{
						$('.add-event-contact').addClass('disabled');
					}
				},
				error: function (objAJAXRequest, strError) {
					response = 'Unexpected error';
				}
			});
		}
		else
		{
			$('.add-event-contact').addClass('disabled');
		}

	});

	$(document).on('change keyup', '#session_message, #session_host', function (e) {

		$('.save-event-details').removeClass('disabled');

	});

	$(document).on('click', '.save-event-details', function (e) {

		var host = $('#session_host').val();
		var message = $('#session_message').val();

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'my_events',
				op: 'save_details',
				event_id: manageEventID,
				message: message,
				host: host
			},
			success: function (json, status) {
				if (json.processor_success)
				{
					$('.save-event-details').addClass('disabled');

					$('.btn-spinning').removeClass('btn-spinning');
					$('.ld-spin').remove();
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

	$(document).on('click', '.add-event-contact:not(.disabled)', function (e) {

		var name = $('.add-event-contact-div').find('.add-contact-name').val().trim();
		var email = $('.add-event-contact-div').find('.add-contact-email').val().trim();

		var isNewEmail = emailIsNew(email);

		if (isNewEmail)
		{
			if (name == '')
			{
				name = email;

				$('.add-event-contact-div').find('.add-contact-name').val(name);
			}

			$('.add-event-contact-div').find('.add-contact-email').removeClass('add-contact-email is-invalid').attr('disabled', true);
			$('.add-event-contact-div').find('.add-contact-name').removeClass('add-contact-name').attr('disabled', true);
			$('.add-event-contact-div').removeClass('add-event-contact-div').attr('data-email-address-div', email);

			$(this).data('email-address', email).removeClass('add-event-contact btn-primary').addClass('remove-event-contact btn-warning').find('.fas').removeClass('fa-plus').addClass('fa-minus');

			setEventReferral(email, {
				'referred_user_name': name,
				'referred_user_email': email,
				'referred_user_send_email': true
			});

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'my_events',
					op: 'get_template',
					template: 'manage_invites'
				},
				success: function (json, status) {
					if (json.processor_success)
					{
						$('.event-referrals-div').append(json.html);

						var isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;

						if (!isMobile)
						{
							$('.add-event-contact-div').find('.add-contact-name').focus();
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

		}
		else
		{
			$(this).addClass('disabled');
		}

	});

	$(document).on('change', '.referred_user_send_email', function (e) {

		var referral_id = $(this).data('referral_id');

		setEventReferral(referral_id, {'referred_user_send_email': false});

		if ($(this).is(':checked'))
		{
			setEventReferral(referral_id, {'referred_user_send_email': true});
		}

	});

	$(document).on('click', '.remove-event-contact', function (e) {

		var email = $(this).data('email-address');

		unsetEventReferral(email);

		$('[data-email-address-div="' + email + '"]').slideUp("normal", function () {
			$(this).remove();
		});

	});

	$(document).on('click', '.send-event-emails:not(.disabled)', function (e) {

		$('.save-event-details').trigger('click');

		var personal_message = $('#session_message').val().trim();
		var session_host = $('#session_host').val();

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'my_events',
				op: 'send_invites',
				event_id: manageEventID,
				message: personal_message,
				name: session_host,
				emails: JSON.stringify(eventReferrals)
			},
			success: function (json, status) {
				if (json.processor_success)
				{
					eventReferrals = $.parseJSON(json.eventReferralsJS);

					$('.event-referrals-div').html(json.html);

					$('.btn-spinning').removeClass('btn-spinning');
					$('.ld-spin').remove();

					updateEmailCounter();
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

	$(document).on("click", ".import_contacts_previous", function (e) {
		e.preventDefault();

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'my_events',
				op: 'import_contacts',
				client: 'previous'
			},
			success: function (json, status) {
				if (json.processor_success)
				{
					list_contacts(json.html);
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

	$(document).on("click", ".import_contacts_outlook", function (e) {
		e.preventDefault();

		getGraphContacts(function (data) {

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'my_events',
					op: 'import_contacts',
					client: 'msgraph',
					contacts: JSON.stringify(data)
				},
				success: function (json, status) {
					if (json.processor_success)
					{
						list_contacts(json.html);
					}
					else
					{

					}
				},
				error: function (objAJAXRequest, strError) {
					response = 'Unexpected error';
				}
			});

		})

	});

	$(document).on("click", ".import_contacts_google", function (e) {
		e.preventDefault();
		gapi.client.setApiKey(CLIENT.google.api);
		gapi.auth.authorize({
			client_id: CLIENT.google.id,
			scope: 'https://www.googleapis.com/auth/contacts.readonly',
			immediate: false
		}, getGoogleContacts);
	});

	function getGoogleContacts(authorizationResult)
	{
		if (authorizationResult && !authorizationResult.error)
		{
			$.ajax({
				url: "https://www.google.com/m8/feeds/contacts/default/thin?access_token=" + authorizationResult.access_token + "&alt=json&max-results=10000",
				dataType: "json",
				success: function (data) {

					$.ajax({
						url: 'ddproc.php',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'my_events',
							op: 'import_contacts',
							client: 'google',
							contacts: JSON.stringify(data)
						},
						success: function (json, status) {
							if (json.processor_success)
							{
								list_contacts(json.html);
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
	}

});