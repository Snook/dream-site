var manualSearchTypeChange = false;

function list_users_init()
{
	showHelpText();
	$('#q').focus();
	$('#q').bind('input', function() { detectType(); });
}

function searchTypeChange()
{
	showHelpText();
	$('#q').focus();
	manualSearchTypeChange = true;
}

function showHelpText()
{
	var helpString = "";
	var value = $('#search_type').val();

	$('#q').removeClass('telephone').unmask();

	switch(value)
	{
		case 'firstname':
			helpString = "Enter full or partial first name";
		break;

		case 'lastname':
			helpString = "Enter full or partial last name";
		break;

		case 'firstlast':
			helpString = "Search Text Example: John Smith (Enter full or partial first and last name)";
		break;

		case 'email':
		helpString = "Instruction Text: Enter full or partial email address";
		break;

		case 'phone':
			helpString = "Instruction Text: Enter phone number in 555-555-5555 format";
			$('#q').addClass('telephone');
			$('.telephone').mask('999-999-9999');
			break;

		case 'id':
			helpString = "Enter the exact Guest ID (No partial entries)";
		break;
	}

	$('#search_help').show();
	$('#search_help').html(helpString);
}

var codes = [];
var target_prefix = "payment1_";
var scanPosition = 0;
var charIndex = 0;

function keyHandler2(evt)
{
	evt = (evt) ? evt : event;
	var charCode = (evt.charCode) ? evt.charCode : ((evt.which) ? evt.which : evt.keyCode);

	if(charCode == 13)
	{
		return false;
	}

	codes[charIndex++] = String.fromCharCode(charCode);

	if((String.fromCharCode(charCode) == '%') && (scanPosition == 0))
	{
		scanPosition = 1; //start of data
	}
	else if((String.fromCharCode(charCode) == 'B') && (scanPosition == 1))
	{
		scanPosition = 2; // start of track 1
	}
	else if((String.fromCharCode(charCode) == '?') && (scanPosition == 2))
	{
		scanPosition = 3;  // end of track 1
	}
	else if((String.fromCharCode(charCode) == '?') && (scanPosition == 3))
	{
		scanPosition = 0;  // end of track 2
		charIndex = 0;
		handleSwipeCompletion();
	}
}

function endScanHandling()
{
	document.getElementById('scanArea').style.display = "none";
	codes.length = 0;
	scanPosition = 0;
	charIndex = 0;
	document.getElementById('hidden_text_store').value= "";
}

function prepareForCCSwipe(paymentNumber)
{
	document.onkeypress = keyHandler2;
	document.getElementById('scanArea').style.display = "block";
	document.getElementById('hidden_text_store').focus();
	document.getElementById('hidden_text_store').value= "";
}

function handleSwipeCompletion( )
{
	var receivedText = codes.join("");

	var p = new SwipeParserObj(receivedText);

	document.getElementById('q').value = p.surname;

	endScanHandling();

	document.getElementById('list_users_form').submit();
}

function detectType()
{
	// user manually changed search type, don't automatically change it
	if (manualSearchTypeChange == true)
	{
		return;
	}

	var value = $('#q').val();

	// not numeric entry, start with last name
	if (!$.isNumeric(value))
	{
		$('#search_type').val('lastname');
	}

	// numeric entry, probably guest id
	if ($.isNumeric(value))
	{
		$('#search_type').val('id');
	}

	// has @ symbol, probably email
	if (value.indexOf("@") != -1)
	{
		$('#search_type').val('email');
	}

	// has space, probably first and last name
	if (value.indexOf(" ") != -1)
	{
		$('#search_type').val('firstlast');
	}

	showHelpText();
}

// ------------------------------------ Processor Support

function processorGuestSearch()
{

	var searchtype = $('#search_type').val();
	var searchvalue = $('#q').val();
	var store_filter = $('#all_stores').prop("checked") ? 1 : 0;
	var currentStore = $('#currentStore').val();

	$.ajax({
		url: 'ddproc.php?processor=admin_guestSearch',
		type: 'POST',
		dataType : 'json',
		data : {
			search_type: searchtype,
			q: searchvalue,
			results_type:'compact_list',
			store: (currentStore ? currentStore : 'none'),
			filter: store_filter
		},
		success: function(json)
		{
			if (json.result_code == 1)
			{
				$('#user_list_target').html(json.data);
			}
			else
			{
				dd_message({ title: 'Error', message: json.processor_message});

			}
	    },
		error: function(objAJAXRequest, strError)
		{
			dd_message({ title: 'Error', message: strError});
		}
	});
}


var user_ref_action = "none";

function user_referral_init()
{

	$(":submit").on('click', function () { user_ref_action = this.name; });

	// If enter key is pressed on input handle zipcode search
	$("#q").keypress(function(e) {
		if(e.which == 13)
		{
			processorGuestSearch();
		}
	});


}

function checkReferralSubmission(formElem)
{
	var retVal = true;

	if ($('#operation').val() == 'ineligible' && user_ref_action == 'submit_referral')
	{
		if (active_referral_email == $('#overrideReferral').val())
		{
			retVal = false;
			dd_message({
				modal : true,
				message : 'The referral source is aleady set to this user account.'
			});
		}

	}


	return retVal;
}


function useGuestAccount(ref_id, email, id, isPending, isInPlatePoints )
{
	$('#ur_select_message').hide();
	$('#user_list_target').html("");
	$('#customer_referral_result').html(email);
	$('#overrideReferral').val(email);
	$('#customer_referral_id').val(ref_id);
	$('#submit_referral').prop('disabled', false);
	$('#optionsDiv').show();

	var foundReferralOption = false;

	if (isInPlatePoints)
	{
		$("span[data-reward_type='points']").show();
		$("span[data-reward_type='credit']").hide();

	}
	else
	{
		$("span[data-reward_type='points']").hide();
		$("span[data-reward_type='credit']").show();
	}

	if (isPending)
	{
		if ($('#pendingOptions').length)
		{
			foundReferralOption = true;

			$('#pendingOptions').show();
			if ($('#leave_queued').length)
				$('#leave_queued').prop("checked", true);
		}

		if ($('#nonpendingOptions').length)
		{
			$('#nonpendingOptions').hide();

			if ($('#queue_new').length)
				$('#queue_new').prop("checked", false);
		}
	}
	else
	{
		if ($('#pendingOptions').length)
		{
			$('#pendingOptions').hide();
			if ($('#leave_queued').length)
				$('#leave_queued').prop("checked", false);
		}

		if ($('#nonpendingOptions').length)
		{
			foundReferralOption = true;
			$('#nonpendingOptions').show();
			if ($('#queue_new').length)
				$('#queue_new').prop("checked", true);
		}
	}

	if (!foundReferralOption)
	{
		$('#operation').val("referral_source_update_only");
	}


}
