function handle_tc_delayed_payment()
{
	$('#tc_delayed_payment').on('click', function () {

		if ($('#tc_delayed_payment').is(':checked'))
		{

			dd_message({
				title: lang.en.tc.terms_and_conditions,
				message: lang.en.tc.delayed_payment,
				modal: true,
				noOk: true,
				buttons: {
					"Agree": function () {

						$(this).remove();

						$('#tc_delayed_payment').prop("checked", true);

					},
					"Decline": function () {

						dd_message({
							title: lang.en.tc.terms_and_conditions,
							message: lang.en.tc.delayed_payment_decline
						});

						$('#tc_delayed_payment').prop("checked", false);

					}
				}
			});

		}
		else
		{
			dd_message({
				title: lang.en.tc.terms_and_conditions,
				message: lang.en.tc.delayed_payment_decline
			});
		}

	});
}

function handle_platepoints_enrollment()
{
	$('#enroll_in_plate_points').on('click', function () {
		set_req = false;
		if ($(this).is(":checked"))
		{
			set_req = true;
		}
		$('#referral_source, #gender, #birthday_month, #birthday_year, #number_of_kids, #number_of_kids, #desired_homemade_meals_per_week, #number_of_adults, #contribute_income, #use_lists, #number_monthly_dine_outs').attr('data-dd_required', set_req).attr('required', set_req);
		$('#prefer_daytime_sessions, #prefer_evening_sessions, #prefer_weekend_sessions').data('checkbox_group_required', set_req);
		//$('#prefer_daytime_sessions, #prefer_evening_sessions, #prefer_weekend_sessions').data('checkbox_group_required', false).attr('required', false);
	});
	if (getQueryVariable('pp_enroll'))
	{
		$('#enroll_in_plate_points').prop('checked', true).triggerHandler('click');
	}
}

function how_did_you_hear_init()
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

	$(document).on('change', '#referral_source', function (e) {

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
}

function opt_out_platepoints(user_id)
{
	dd_message({
		div_id: 'optoutconfirm',
		title: 'Confirm PLATEPOINTS Opt Out',
		message: "The guest will receive an email explaining the consequences of opting out. Are you sure you want to opt out this guest?",
		zIndex: 2,
		resizable: false,
		cancel: function () {
		},
		confirm: function () {
			handle_opt_out_platepoints(user_id);
		}
	});
}

function handle_opt_out_platepoints(user_id)
{
	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'plate_points_processor',
			user_id: user_id,
			op: 'opt_out_user'
		},
		success: function (json, status) {
			if (json.processor_success)
			{
				$("#enroll_in_plate_points").attr('checked', false);
				$("#dr_guest_opt_out_message").show();
				dd_message({
					title: 'Success',
					message: 'The guest was opted out of the PLATEPOINTS program.'
				});

				$("#dr2convertConfirm").remove();
			}
			else
			{
				dd_message({
					title: 'Error',
					message: 'There was a problem opting the guest out of the PLATEPOINTS program.'
				});
				$(checkboxObj).prop('checked', false);
			}
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';
		}
	});

}

$(document).on('change', '#generateLogin', function (e) {

	if ($(this).is(':checked'))
	{
		$('#email_main_row, #email_confirm_row, #main_pw_row, #confirm_pw_row').hideFlex();
		$('#primary_email, #confirm_email_address, #password, #password_confirm').prop({'disabled': true, 'required': false}).val('');
	}
	else
	{
		$('#email_main_row, #email_confirm_row, #main_pw_row, #confirm_pw_row').showFlex();
		$('#primary_email, #confirm_email_address, #password, #password_confirm').prop({'disabled': false, 'required': true});
	}

});

$(document).on('click', '.email-unsubscribe', function (e) {

	let unsub_email = $(this).data('email');

	bootbox.confirm("Are you sure you wish to invalidate this guest's email address? This will affect other stores' ability to search by the original email address and the guest's ability to login if this is changed without their knowledge.", function (result) {
		if (result)
		{
			$('#primary_email, #confirm_email_address').val(unsub_email);
		}
	});

});

$(document).on('change', '#add_email', function (e) {

	if (!$(this).is(':checked'))
	{
		$('#email_main_row, #email_confirm_row, #secondary_email_row').hideFlex();
		$('#primary_email, #confirm_email_address').prop({'disabled': true, 'required': false}).val('');
	}
	else
	{
		$('#email_main_row, #email_confirm_row, #secondary_email_row').showFlex();
		$('#primary_email, #confirm_email_address').prop({'disabled': false, 'required': true});
	}

});

var fullAccountOnlyFieldList = {};
fullAccountOnlyFieldList[0] = "address_line1";
fullAccountOnlyFieldList[1] = "address_line2";
fullAccountOnlyFieldList[2] = "city";
fullAccountOnlyFieldList[3] = "state_id";
fullAccountOnlyFieldList[4] = "postal_code";
fullAccountOnlyFieldList[5] = "telephone_1_call_time";
fullAccountOnlyFieldList[6] = "telephone_1";
fullAccountOnlyFieldList[7] = "telephone_2_call_time";
fullAccountOnlyFieldList[7] = "telephone_2";
fullAccountOnlyFieldList[8] = "genderF";
fullAccountOnlyFieldList[9] = "genderM";
fullAccountOnlyFieldList[10] = "password";
fullAccountOnlyFieldList[12] = "password_confirm";
fullAccountOnlyFieldList[13] = "fax";
fullAccountOnlyFieldList[14] = "birthday_month";
fullAccountOnlyFieldList[15] = "number_of_kids";
fullAccountOnlyFieldList[16] = "family_size";
fullAccountOnlyFieldList[17] = "favorite_meals";
fullAccountOnlyFieldList[18] = "why_works";
fullAccountOnlyFieldList[19] = "guest_employer";
fullAccountOnlyFieldList[20] = "spouse_employer";
fullAccountOnlyFieldList[21] = "upcoming_events";
fullAccountOnlyFieldList[22] = "misc_notes";
fullAccountOnlyFieldList[23] = "genderX";

var requiredFieldList = {};
requiredFieldList[0] = "address_line1";
requiredFieldList[2] = "city";
requiredFieldList[3] = "state_id";
requiredFieldList[4] = "postal_code";
requiredFieldList[5] = "telephone_1_call_time";
requiredFieldList[6] = "telephone_1";
requiredFieldList[7] = "genderF";
requiredFieldList[8] = "genderM";
requiredFieldList[9] = "genderX";

function updateConversionState(obj)
{
	var disabledVal = true;
	if (obj.checked)
	{
		disabledVal = false;
	}

	for (var thisFieldName in fullAccountOnlyFieldList)
	{
		var thisElem = document.getElementById(fullAccountOnlyFieldList[thisFieldName]);
		if (thisElem)
		{
			thisElem.disabled = disabledVal;
		}
	}

	if (disabledVal)
	{
		for (var thisFieldName in requiredFieldList)
		{
			var thisElem = document.getElementById(requiredFieldList[thisFieldName]);
			if (thisElem)
			{
				thisElem.setAttribute('required', 'false');
			}
		}
	}
	else
	{
		for (var thisFieldName in requiredFieldList)
		{
			var thisElem = document.getElementById(requiredFieldList[thisFieldName]);
			if (thisElem)
			{
				thisElem.setAttribute('required', 'true');
			}
		}
	}
}

$(function () {

	if (isPartialAccount)
	{
		updateConversionState(document.getElementById("convertToFull"));
	}

	how_did_you_hear_init();

	handle_platepoints_enrollment();

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

	if (isCreate)
	{
		handle_tc_delayed_payment();
	}

	$('#generateLogin').trigger('change');
});