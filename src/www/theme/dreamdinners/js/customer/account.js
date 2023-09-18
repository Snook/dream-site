// How long to wait before sending ajax referral email check update in seconds
var timeout = 1;

var g_lastSearch = '';
var g_referralCheck = '';

function handle_account_form_validation()
{
	$("#birthday_year").mousedown(function (obj) {
		if (obj.target[1])
		{

			var firstYear = obj.target[1].value;
			firstYear = firstYear - 23;
			if ($("#birthday_year").val() == '')
			{
				$("#birthday_year").val(firstYear);
			}
		}
	});

	$('#customers_terms').change(function () {
		if ($('#customers_terms').is(":checked"))
		{
			$('#submit_account').removeClass('disabled').removeAttr('disabled');
		}
		else
		{
			$("#submit_account").addClass('disabled').attr('disabled', 'disabled');
		}
	});
	//data-required_checkbox="preferred_sessions"
	$('[data-required_checkbox="preferred_sessions"]').change(function () {
		monthly_dine = false;
		$('[data-required_checkbox="preferred_sessions"]').each(function () {
			if ($(this).is(':checked'))
			{
				monthly_dine = true;
				return true;
			}
		});
	});

	// Watch customer referral for change
	referral_update_init();

	// Watch for change on terms checkbox
	$('#customers_terms').change(function () {
		//can_create_account();
	});

	// Also check if the friend email is in the field already somehow, like refresh
	if ($("#refer_chk-customer_referral").is(':checked') && $("#customer_referral_email").val().length > 0)
	{
		check_email_address("customer_referral_email");
	}

	if ($('#password').length)
	{
		$('#password').attr('data-message_min', "A password must have a least 6 characters.");
	}
}

function handle_home_store_search()
{
	// USER_PREFERENCES will be set if we are editing
	// an existing account
	if (!USER_PREFERENCES)
	{
		$('#postal_code').bind('change keyup', function () {

			if ($(this).val().length == 5)
			{
				home_store_address_search();
			}
		});
		// Check if the postal code is in the field already somehow, like refresh
		if (typeof $("#postal_code").val() != 'undefined' && $("#postal_code").val().length == 5)
		{
			home_store_address_search();
		}
	}
}

function passedReferral()
{
	check_result = $('#refer_chk-customer_referral').is(":checked");

	if (check_result)
	{
		if (g_referralCheck === false)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	else
	{
		return true;
	}
}

function referral_update_init()
{
	// unbind it to ensure it's only bound once
	$('#customer_referral_email').unbind("keyup change").bind("keyup change", function (e) {

		// Clear existing timeout
		if (this.check_referral_email)
		{
			clearTimeout(this.check_referral_email);
		}

		// Start timeout for ajax update
		this.check_referral_email = setTimeout('check_email_address("customer_referral_email")', (timeout * 1000));
	});

	// setup notification container
	$('#refer_chk-customer_referral').bind('click', function (e) {
		if (!$('#refer_chk-customer_referral').is(":checked"))
		{
			$('#customer_referral_result').html('@');
		}
	});
}

function check_email_address(id)
{
	var email_address = $('#' + id).val().trim();

	if (email_address != '')
	{
		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'data_lookup',
				type: 'user',
				check: 'email',
				email: email_address
			},
			success: function (json, status) {
				if (json.processor_success)
				{
					$('#customer_referral_result').html('<img src="' + PATH.image + '/icon/accept.png" class="img-fluid"  />');
					$('#customer_referral_email').find(".temp_break").remove();
					$('#customer_referral_email').removeClass('input_in_error');
					$('#customer_referral_email_erm').remove();
					g_referralCheck = true;
				}
				else
				{
					$('#customer_referral_result').html('<img src="' + PATH.image + '/icon/error.png" data-toggle="tooltip" data-placement="top" title="Email Not Found" class="img-fluid" />');
					$('[data-toggle="tooltip"]').tooltip();
					g_referralCheck = false;
				}
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
			}
		});
	}
}

function home_store_address_search()
{
	var address_line1 = '';
	var city = '';
	var state_id = '';
	var postal_code = '';

	if ($('#address_line1').val())
	{
		address_line1 = $('#address_line1').val() + ', ';
	}

	if ($('#city').val())
	{
		city = $('#city').val() + ', ';
	}

	if ($('#state_id').val())
	{
		state_id = $('#state_id').val() + ' ';
	}

	if ($('#postal_code').val())
	{
		postal_code = $('#postal_code').val();
	}

	address_search = address_line1 + city + state_id + postal_code;

	retrieve_stores_for_address({
		address: address_search,
		compact: true
	});
}

// Register click handler to select store
$(document).on('click', '[id^="select_location-"]', function (e) {
	$("#store_id").val(this.id.split("-")[1]);
	// Stop browser from following href
	e.preventDefault();
});


$(document).on('click', ".toggle-update_mobile_number", function (e) {

	e.preventDefault();

	$("[data-sms_dlog_comp=true]").slideToggle(function ()
	{
		if ($(this).is(':hidden'))
		{
			$('form#add_mobile_number').trigger("reset");
			$('#new_mobile_number_div').slideUp();
		}
		else
		{
			$("[data-sms_pref=true]").attr('disabled', false);
		}
	});

});

$(document).on('click', '[name="add_method"]', function (e) {

	var radioValue = $("input[name='add_method']:checked").val();
	if (radioValue == 'new')
	{
		$("#new_mobile_number_div").slideDown();
		$('#new_mobile_number').prop('required', true);
	}
	else
	{
		$("#new_mobile_number_div").slideUp();
		$('#new_mobile_number').prop('required', false);
	}

});

$(document).on('click', '.account-request-data:not(.disabled)', function (e) {

	bootbox.confirm({
		message: 'Are you sure you wish to create a support ticket to request your account information?',
		buttons: {
			confirm: {
				label: 'Yes',
			},
			cancel: {
				label: 'No',
			}
		},
		callback: function (result) {
			if (result)
			{
				$.ajax({
					url: '/processor',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'account',
						op: 'request_data'
					},
					success: function (json) {
						if (json.processor_success)
						{
							modal_message({
								title: 'Account Data Management',
								message: 'The request for a copy of your account information has been submitted.'
							});
						}
						else
						{
							modal_message({
								title: 'Processing Error',
								message: json.processor_message
							});
						}
					},
					error: function (objAJAXRequest, strError) {
						modal_message({
							title: 'Error',
							message: response = 'Unexpected error:' + strError
						});
					}
				});
			}
		}
	});

});

$(document).on('click', '.account-request-delete:not(.disabled)', function (e) {

	bootbox.prompt({
		title: "Account Deletion Request Notice",
		message: "<div class='font-size-small'><p>By submitting your password below, you are agreeing to the following terms and understand that this action cannot be undone.</p><ul><li>If you are enrolled in PLATEPOINTS, you will be unenrolled. Your current points, history, and badge status will be erased.</li><li>Any available store credits will be voided</li><li>Any available Dinner Dollars will be voided</li><li>Any available gift card credit will be voided</li><li>Any available or outstanding referral credit will be voided</li><li>Your account and order history will be removed</li></ul><p>Note: If you have a future order scheduled or an outstanding balance owed in your account, you will not be able to request deletion until the account is in good standing or your future order is cancelled. Please contact your store for help. Once those are resolved, you may request to have your account deleted.</p></div>",
		inputType: 'password',
		buttons: {
			confirm: {
				label: 'I Agree to the Terms. Delete My Account.',
				className: 'btn-danger'
			},
			cancel: {
				label: 'Cancel',
			}
		},
		callback: function (result) {
			if (result)
			{
				$.ajax({
					url: '/processor',
					type: 'POST',
					timeout: 20000,
					dataType: 'json',
					data: {
						processor: 'account',
						challenge: result,
						op: 'request_delete'
					},
					success: function (json) {
						if (json.processor_success)
						{
							if (json.delete_success)
							{
								bounce('/');
							}
							else
							{
								modal_message({
									title: 'Error',
									message: json.processor_message
								});
							}
						}
						else
						{
							modal_message({
								title: 'Processing Error',
								message: json.processor_message
							});
						}
					},
					error: function (objAJAXRequest, strError) {
						modal_message({
							title: 'Error',
							message: response = 'Unexpected error:' + strError
						});
					}
				});
			}
		}
	});

});



function setUpExistingReferrralSource()
{
	if ($('#referral_source').val() == 'OTHER')
	{
		$("#referral_source_details_div").showFlex();
	}
	else if ($('#referral_source').val() == 'CUSTOMER_REFERRAL')
	{
		$("#customer_referral_email_div").showFlex();
	}
	else if ($('#referral_source').val() == 'VIRTUAL_PARTY')
	{
		$("#virtual_party_source_details_div").showFlex();
	}
}

$(document).on('change', '#referral_source', function (e) {

	$('#customer_referral_result').text('@');

	// defaults
	$("#referral_source_details_div").hideFlex();
	$("#referral_source_details").prop({
		'disabled': true,
		'required': false
	}).val('');

	$("#virtual_party_source_details_div").hideFlex();
	$("#virtual_party_source_details").prop({
		'disabled': true,
		'required': false
	}).val('');

	$("#customer_referral_email_div").hideFlex();
	$("#customer_referral_email").prop({
		'disabled': true,
		'required': false
	}).val('');


	if ($(this).val() == 'OTHER')
	{
		$("#referral_source_details_div").showFlex();
		$("#referral_source_details").prop({
			'disabled': false,
			'required': true
		}).val('');
	}
	else if ($(this).val() == 'CUSTOMER_REFERRAL')
	{

		$("#customer_referral_email_div").showFlex();
		$("#customer_referral_email").prop({
			'disabled': false,
			'required': true
		}).val('');

	}
	else if ($(this).val() == 'VIRTUAL_PARTY')
	{
		$("#virtual_party_source_details_div").showFlex();
		$("#virtual_party_source_details").prop({
			'disabled': false,
			'required': true
		}).val('');
	}
});

$(document).on('submit', "#add_mobile_number", function (e) {

	e.preventDefault();

	if ($(this)['0'].checkValidity() !== false)
	{
		var number = false;
		var method = $("input[name='add_method']:checked").val();

		var selectedCategories = {};
		var somethingChecked = false;
		$("[data-sms_pref=true]").each(function () {
			if ($(this).is(":checked"))
			{
				somethingChecked = true;
				selectedCategories[$(this).attr('id')] = 1;
			}
		});

		if (!somethingChecked && method != 'delete')
		{
			$("#add_mobile_number").removeClass('was-validated');
			$('#confirm_number_update').removeSpinner();

			bootbox.alert({
				size: "small",
				title: "Error",
				message: "Please opt into at least one type of message."
			});

			return false;
		}

		if (method == 'new')
		{

			var phoneno = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
			if ($("#new_mobile_number").val().match(phoneno))
			{
				number = $("#new_mobile_number").val();
			}
			else
			{
				$("#add_mobile_number").removeClass('was-validated');
				$('#confirm_number_update').removeSpinner();

				bootbox.alert({
					size: "small",
					title: "Error",
					message: "Please enter a valid phone number (xxx-xxx-xxxx)."
				});

				return false;
			}

		}
		else if (method == 'delete')
		{

			$.ajax({
				url: '/processor',
				type: 'POST',
				timeout: 100000000,
				dataType: 'json',
				data: {
					processor: 'account',
					op: 'remove_cur_number'
				},
				success: function (json, status) {

					$('#confirm_number_update').removeSpinner();
					$("#add_mobile_number").removeClass('was-validated');

					if (json.processor_success)
					{
						$("#current_mobile_target").html("Current Mobile number: ");
						$("[data-sms_pref=true]").prop('checked', false);
						$("[data-sms_pref=true]").attr('disabled', 'disabled');
						$("#add_update_mobile_number").html("Add Mobile Number");
						$("#add_update_mobile_number").data('op', 'add');
						$("#remove_mobile_number_div").slideUp();
						$("[data-sms_dlog_comp=true]").slideUp();
						$("#add_update_mobile_number").attr("disabled", false);

						bootbox.alert({
							size: "small",
							title: "Success",
							message: "Your SMS phone number has been removed. You will no longer receive text messages. (Note: It may take 60 minutes for the change to complete.)"
						});

						return false;
					}
					else
					{

						$('#confirm_number_update').removeSpinner();
						$("#add_mobile_number").removeClass('was-validated');

						// error when processing new number
						// leave dialog open
						bootbox.alert({
							size: "small",
							title: "Error",
							message: json.processor_message
						});

						return false;
					}

				},
				error: function (objAJAXRequest, strError) {
					// low level error when processing new number
					// leave dialog open
					$("#add_update_mobile_number").removeSpinner();
					$("#add_update_mobile_number").attr("disabled", false);
					$("#add_mobile_number").removeClass('was-validated');


					bootbox.alert({
						size: "small",
						title: "Error",
						message: "Unexpected Error"
					});
					return false;
				}
			});

			$("#add_update_mobile_number").removeSpinner();
			$("#add_update_mobile_number").attr("disabled", false);
			$("#add_mobile_number").removeClass('was-validated');

			return false;
		}

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				processor: 'account',
				op: 'add_or_change_sms_number',
				method: method,
				number: number,
				selectedCategories: selectedCategories,
				special_case: sms_special_case
			},
			success: function (json, status) {

				$('#confirm_number_update').removeSpinner();

				if (json.processor_success)
				{
					$("#current_mobile_target").html("Current Mobile number: " + json.new_number);
					$("[data-sms_pref=true]").attr('disabled', false);
					$("#add_update_mobile_number").html("Update or Remove Mobile Number");
					$("#add_update_mobile_number").data('op', 'update');
					$("#remove_mobile_number_div").slideDown();
					$("#add_mobile_number").removeClass('was-validated');

					$.each(json.newPrefsState, function (key, value) {
						$("#" + key).attr('checked', value);
					});

					var message = "Your SMS phone number is updated.";
					if (json.override_message)
					{
						message = json.override_message;
					}

					bootbox.alert({
						size: "small",
						title: "Success",
						message: message
					});

					$("[data-sms_dlog_comp=true]").slideUp();
					$("#add_update_mobile_number").attr("disabled", false);

					return false;
				}
				else
				{
					bootbox.alert({
						size: "small",
						title: "Error",
						message: json.processor_message
					});

					return false;
				}

			},
			error: function (objAJAXRequest, strError) {
				bootbox.alert({
					size: "small",
					title: "Error",
					message: "Unexpected Error"
				});

				$("#add_update_mobile_number").removeSpinner();

				return false;
			}
		});

	}

});

function remove_credit_card_reference(settings)
{
	var domObjectToRemove = $('#row-cc_ref-' + settings.item_number).get();

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_remove_payment',
			payment_type: 'cc_ref',
			cc_ref_id: settings.item_number
		},
		success: function (json) {
			if (json.processor_success)
			{
				var numRefs = $(domObjectToRemove).data('num_cc_refs');
				$(domObjectToRemove).slideUp();
				$(domObjectToRemove).remove();

				if (numRefs > 1)
				{
					$('[id^="row-cc_ref"]').data('num_cc_refs', numRefs - 1);
				}
				else
				{
					// select add new card and expose - no refs left
				}
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


$(function () {

	handle_account_form_validation();
	handle_home_store_search();
	setUpExistingReferrralSource();

	if (getQueryVariable('pp_enroll'))
	{
		if ($('#enroll_in_plate_points').length)
		{
			$('#enroll_in_plate_points').prop('checked', true).triggerHandler('click');

			if (typeof is_create == 'undefined' || !is_create)
			{
				$('html, body').animate({
					scrollTop: $('#enroll_in_plate_points_head').offset().top - 60
				}, 2000);
			}
		}
	}

	if(scroll != '' && scroll.length > 0){
		$('html, body').animate({
			scrollTop: $('#'+scroll).offset().top - 60
		}, 2000);
	}

	// Click handler for remove items
	$(document).on('click', '[id^="remove-cc_ref"]', function (e) {
		// store the id and some of its parts
		var remove = {
			id: this.id,
			item: this.id.split("-")[1],
			item_number: this.id.split("-")[2],
		};

		// get the friendly item description to display in the popup
		remove.title = $('#checkout_title-' + remove.item + "-" + remove.item_number).text();
		remove.type = 'Item';

		// show removal confirmation
		modal_message({
			title: 'Remove ' + remove.type,
			message: 'Are you sure you wish to remove ' + remove.title + '.',
			confirm: function () {
				remove_credit_card_reference(remove);
			}
		});
	});



});