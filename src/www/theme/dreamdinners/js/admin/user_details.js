function enrollInPlatePoints(user_id)
{
	bounce('?page=admin_account&pp_enroll=1&id=' + user_id, false);
}

function viewPlatePoints(user_id)
{
	var config = {
		action: '?page=admin_user_plate_points',
		input: {
			user_id: user_id,
			back: back_path()

		}
	};

	create_and_submit_form(config);
}

function printEnrollmentForm(user_id)
{
	/*
	var config = {
			action: '?page=admin_user_plate_points',
			input: {
				user_id: user_id,
				back: back_path(),
				print_enrollment_form: 'true'

			}
		};
	create_and_submit_form(config);

	*/
	bounce('?page=admin_user_plate_points&user_id=' + user_id + '&print_enrollment_form=true', '_blank');
}

function markAccountDataRequestCompleteConfirm(user_id)
{
	dd_message({
		title: 'Account Data Request Complete confirmation',
		message: 'Are you sure you want to mark the account data request as complete for this guest?',
		modal: true,
		confirm: function () {

			bounce('?page=admin_user_details&id=' + user_id + '&action=confirmAccountInfoSent');

		}
	});
}

function deleteUserConfirm(user_id)
{
	dd_message({
		title: 'Delete guest confirmation',
		message: 'Are you sure you want to delete this guest?',
		modal: true,
		confirm: function () {

			bounce('?page=admin_user_details&id=' + user_id + '&action=delete');

		}
	});
}

function unsetHomeStore(user_id)
{
	dd_message({
		title: 'Remove guest confirmation',
		message: 'Are you sure you want to remove this guests home store?',
		modal: true,
		confirm: function () {

			bounce('?page=admin_user_details&id=' + user_id + '&action=unsethome');

		}
	});
}

$(document).on('click', '[data-delayed_payment_tc]', function () {

	var user_id = $(this).data('delayed_payment_tc');

	dd_message({
		title: lang.en.tc.terms_and_conditions,
		message: lang.en.tc.delayed_payment,
		modal: true,
		noOk: true,
		closeOnEscape: false,
		open: function (event, ui) {
			$(this).parent().find('.ui-dialog-titlebar-close').hide();
		},
		buttons: {
			"Agree": function () {

				$(this).remove();

				set_user_pref('TC_DELAYED_PAYMENT_AGREE', 1, user_id, function (json) {

					$('#tc_delayed_payment_status').html('Accepted - ' + json.date_updated);

				});

			},
			"Decline": function () {

				set_user_pref('TC_DELAYED_PAYMENT_AGREE', 0, user_id, function (json) {

					$('#tc_delayed_payment_status').html('Declined - ' + json.date_updated);

					dd_message({
						title: lang.en.tc.terms_and_conditions,
						message: lang.en.tc.delayed_payment_decline
					});

				});

			}
		}
	});

});

$(document).on('click', ".toggle-update_mobile_number", function (e) {

	e.preventDefault();

	$("[data-sms_dlog_comp=true]").slideToggle(function () {

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
		$("#new_mobile_number_div").show();
		$('#new_mobile_number').prop('required', true);
	}
	else
	{
		$("#new_mobile_number_div").hide();
		$('#new_mobile_number').prop('required', false);
	}

});

$(document).on('click', "#cancel_number_update", function (e) {
	e.preventDefault();
	$("#add_or_change_SMS_number_div").hide();
	$("#add_update_mobile_number").attr("disabled", false);
	return false;
});

$(document).on('submit', '#add_mobile_number', function (e) {

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

			dd_message({
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

				$('#confirm_number_update').removeSpinner();

				dd_message({
					title: "Error",
					message: "Please enter a valid phone number (xxx-xxx-xxxx)."
				});

				return false;
			}

		}
		else if (method == 'delete')
		{

			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 100000000,
				dataType: 'json',
				data: {
					user_id: user_id,
					processor: 'account',
					op: 'remove_cur_number'
				},
				success: function (json, status) {

					$('#confirm_number_update').removeSpinner();

					if (json.processor_success)
					{
						$("#current_mobile_target").html("Current Mobile number: ");
						$("[data-sms_pref=true]").attr('disabled', 'disabled');
						$("[data-sms_pref=true]").prop('checked', false);
						$("#add_update_mobile_number").html("Add Mobile Number");
						$("#add_update_mobile_number").data('op', 'add');

						$("#add_or_change_SMS_number_div").hide();
						$("#add_update_mobile_number").attr("disabled", false);
						$("#remove_mobile_number_div").hide();
						$("[data-sms_dlog_comp=true]").hide();
						dd_message({
							title: "Success",
							message: "The SMS phone number has been removed. They will no longer receive text messages. (Note: It may take 60 minutes for the change to complete.)"
						});

						return false;
					}
					else
					{
						// error when processing new number
						// leave dialog open
						dd_message({
							title: "Error",
							message: json.processor_message
						});

						return false;
					}

				},
				error: function (objAJAXRequest, strError) {

					$("#add_update_mobile_number").removeSpinner();
					$("#add_update_mobile_number").attr("disabled", false);

					// low level error when processing new number
					// leave dialog open
					dd_message({
						title: "Error",
						message: "Unexpected Error"
					});
					return false;
				}
			});

			$("#add_update_mobile_number").removeSpinner();
			$("#add_update_mobile_number").attr("disabled", false);

			return false;
		}

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				processor: 'account',
				op: 'add_or_change_sms_number',
				method: method,
				number: number,
				selectedCategories: selectedCategories,
				user_id: user_id,
				is_fadmin: true
			},
			success: function (json, status) {

				$('#confirm_number_update').removeSpinner();

				if (json.processor_success)
				{
					$("#current_mobile_target").html("Current Mobile number: " + json.new_number);
					$("[data-sms_pref=true]").attr('disabled', false);
					$("#add_update_mobile_number").html("Update or Remove Mobile Number");
					$("#add_update_mobile_number").data('op', 'update');
					$("#remove_mobile_number_div").show();

					$.each(json.newPrefsState, function (key, value) {
						$("#" + key).attr('checked', value);
					});

					dd_message({
						title: "Success",
						message: "The SMS phone number is updated."
					});

					$("[data-sms_dlog_comp=true]").hide();
					$("#add_update_mobile_number").attr("disabled", false);

					return false;
				}
				else
				{
					dd_message({
						title: "Error",
						message: json.processor_message
					});

					return false;
				}

			},
			error: function (objAJAXRequest, strError) {
				dd_message({
					title: "Error",
					message: "Unexpected Error"
				});

				return false;
			}
		});
		return false;

	}

});