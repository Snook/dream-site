function _display_error_message(element, messageAttr, messageText)
{
	var errSpan = document.getElementById(element.name + '_erm');

	var theMessage = null;
	if (messageText)
	{
		theMessage = messageText;
	}
	else
	{
		theMessage = element.getAttribute(messageAttr);
	}

	if (!theMessage)
	{
		return;
	}

	if (!errSpan)
	{
		var brElement = document.createElement("br");
		brElement.setAttribute("class", "temp_break");

		errSpan = document.createElement("span");
		errSpan.setAttribute("id", element.name + "_erm");
		errSpan.setAttribute("class", "warning_text temp_span");

		var messTextNode = document.createTextNode(theMessage);
		errSpan.appendChild(messTextNode);
		dd_insertAfter(errSpan, element);
		errSpan.parentNode.insertBefore(brElement, errSpan);
	}
	else
	{
		errSpan.innerHTML = theMessage;
		$(errSpan).show();
	}

	$(element).addClass('input_in_error');

	return '';
}

function _check_elements(elementsArray)
{
	var rtn = true;
	var statusText = '';

	//reset everything
	for (var i = 0; i < elementsArray.length; i++)
	{
		var element = elementsArray[i];
		$('#' + element.name + '_erm').hide();
		$(element).removeClass('input_in_error');
	}

	$('.temp_break').remove();
	$('.temp_span').remove();

	//check for required
	for (var i = 0; i < elementsArray.length; i++)
	{
		var element = elementsArray[i];

		// if field required, and blank, throw error and cancel submit
		if (element.getAttribute('required') && (element.getAttribute('required') == 'true' || element.getAttribute('required') == '1' || element.getAttribute('required') == 'required'))
		{
			if (((element.type == 'text' || element.type == 'textarea' || element.type == 'password' || element.type == 'tel' || element.type == 'email' ) && (element.value.length < 1 || element.value == element.getAttribute('placeholder'))) ||
				(element.type == 'checkbox' && !element.checked))
			{
				statusText = statusText + _display_error_message(element, 'data-message', null);
				rtn = false;
			}
			else if (( element.type == 'select-one' ) && ((element.options[0].value.length < 1) && (element.selectedIndex == 0)))
			{

				statusText = statusText + _display_error_message(element, 'data-message', null);
				rtn = false;

			}
			else if (element.type == 'radio')
			{
				radioGroup = elementsArray[element.name];
				isChecked = false;
				for (j = 0; j < radioGroup.length; j++)
				{
					if (radioGroup[j].checked)
					{
						isChecked = true;
					}
				}

				if (!isChecked)
				{
					statusText = statusText + _display_error_message(element, 'data-message', null);
					rtn = false;
				}

				//check minimum length
			}
			else if (element.getAttribute('data-min'))
			{
				if ((element.type == 'text' || element.type == 'password') && elementsArray[i].value.length < elementsArray[i].getAttribute('data-min'))
				{
					statusText = statusText + _display_error_message(element, 'data-message_min', null);
					rtn = false;
				}
			}
		}
	}

	//also check for dd_required ... we must move away from "required" as that is defined by HTML5 and using causes .... issues
	for (var i = 0; i < elementsArray.length; i++)
	{
		var element = elementsArray[i];

		// In IE = < 9 there is a dummy text input used to facilitate placeholder text switching.
		// we need to skip this element
		if (element.type == 'text' && (element.name == 'password' || element.name == 'password_confirm'))
		{
			continue;
		}

		// if field required, and blank, throw error and cancel submit
		if (element.getAttribute('data-dd_required') && (element.getAttribute('data-dd_required') == 'true' || element.getAttribute('data-dd_required') == '1' || element.getAttribute('data-dd_required') == 'required'))
		{
			if (((element.type == 'text' || element.type == 'textarea' || element.type == 'password' || element.type == 'tel' || element.type == 'email' ) && (element.value.length < 1 || element.value == element.getAttribute('placeholder'))) ||
				(element.type == 'checkbox' && !element.checked))
			{
				statusText = statusText + _display_error_message(element, 'data-message', null);
				rtn = false;
			}
			else if (( element.type == 'select-one' ) && ((element.options[0].value.length < 1) && (element.selectedIndex == 0)))
			{

				statusText = statusText + _display_error_message(element, 'data-message', null);
				rtn = false;

			}
			else if (element.type == 'radio')
			{
				radioGroup = elementsArray[element.name];
				isChecked = false;
				for (var j = 0; j < radioGroup.length; j++)
				{
					if (radioGroup[j].checked)
					{
						isChecked = true;
					}
				}

				if (!isChecked)
				{
					statusText = statusText + _display_error_message(element, 'data-message', null);
					rtn = false;
				}

				//check minimum length
			}
			else if (element.getAttribute('data-min'))
			{
				if ((element.type == 'text' || element.type == 'password') && elementsArray[i].value.length < elementsArray[i].getAttribute('data-min'))
				{
					statusText = statusText + _display_error_message(element, 'data-message_min', null);
					rtn = false;
				}
			}
		}
	}

	//check for valid email address fields
	for (var i = 0; i < elementsArray.length; i++)
	{
		// if field required, and blank, throw error and cancel submit
		element = elementsArray[i];
		if (!$(element).attr("disabled") && (element.type == 'text' || element.type == 'email') && element.getAttribute('data-email') && (element.getAttribute('data-email') == 'true' || element.getAttribute('data-email') == '1'))
		{
			var emailCheckMsg = emailCheck(element.value, false);
			if (emailCheckMsg != true)
			{
				statusText = statusText + _display_error_message(element, null, emailCheckMsg);
				rtn = false;
			}
		}

		// new optional email type can be empty
		if ((element.type == 'text' || element.type == 'email') && element.getAttribute('data-optional_email') && (element.getAttribute('data-optional_email') == 'true' || element.getAttribute('data-optional_email') == '1'))
		{
			var emailCheckMsg = emailCheck(element.value, true);
			if (emailCheckMsg != true)
			{
				statusText = statusText + _display_error_message(element, null, emailCheckMsg);
				rtn = false;
			}
		}
	}

	//check for valid telephone fields
	for (var i = 0; i < elementsArray.length; i++)
	{
		element = elementsArray[i];
		if (element.type == 'text' && (element.value.length > 0) && element.getAttribute('data-telephone') && (element.getAttribute('data-telephone') == 'true' || element.getAttribute('data-telephone') == '1'))
		{

			// CES 1/2/13: On some browsers the value is the placeholder. If the value is equal to the placeholder (only matters with the 2nd number since the first one is required)
			// then continue
			if (element.value != "Secondary Telephone")
			{
				var telephoneCheckMsg = telephoneCheck(element.value);
				if (telephoneCheckMsg != true)
				{
					statusText = statusText + _display_error_message(element, null, telephoneCheckMsg);
					rtn = false;
				}
			}
		}
	}

	//check for matching passwords; assumes there are only 2 password fields on the page
	var firstPassword = null;
	for (var i = 0; i < elementsArray.length; i++)
	{
		// if field required, and blank, throw error and cancel submit
		element = elementsArray[i];
		if (element.type == 'password')
		{
			if (!firstPassword)
			{
				firstPassword = element.value;
			}
			else if (element.value != firstPassword)
			{

				if ((element.value == '*Password' || element.value == '*Password Confirm') && (firstPassword == '*Password' || firstPassword == '*Password Confirm'))
				{
					break;
				}

				var passwordMessage = "The passwords do not match";

				if (element.value.toLowerCase() == firstPassword.toLowerCase())
				{
					passwordMessage += " Passwords are case-sensitive.";
				}
				statusText = statusText + _display_error_message(element, null, passwordMessage);
				rtn = false;
			}
		}
	}

	//check for matching email addresses
	//search this form for an email field
	var email = null;
	var cnfirm = null;
	for (var i = 0; i < elementsArray.length; i++)
	{
		var element = elementsArray[i];
		if (element.name == 'primary_email')
		{
			email = element;
		}
		if (element.name == 'confirm_email_address')
		{
			cnfirm = element;
		}
	}

	if (email && cnfirm)
	{
		if (cnfirm.value.toLowerCase() != email.value.toLowerCase())
		{
			var cnfirmLbl = document.getElementById('confirm_email_address_lbl');
			var messageConfirm = "The email addresses do not match.";
			if (cnfirmLbl)
			{
				messageConfirm = cnfirmLbl.getAttribute('data-message');
			}

			statusText = statusText + _display_error_message(cnfirm, null, messageConfirm);
			rtn = false;
		}
	}

	//check for valid hour fields
	for (var i = 0; i < elementsArray.length; i++)
	{
		element = elementsArray[i];
		if (element.type == 'text' && (element.value.length > 0) &&
			element.getAttribute('data-hour') && (element.getAttribute('data-hour') == 'true' ||
			element.getAttribute('data-hour') == '1'))
		{
			var hourCheckMsg = hourCheck(element.value);
			if (hourCheckMsg != true)
			{
				statusText = statusText + _display_error_message(element, null, hourCheckMsg);
				rtn = false;
			}
		}
	}

	//check for valid minutes fields
	for (var i = 0; i < elementsArray.length; i++)
	{
		element = elementsArray[i];
		if (element.type == 'text' && (element.value.length > 0) &&
			element.getAttribute('data-minutes') && (element.getAttribute('data-minutes') == 'true' ||
			element.getAttribute('data-minutes') == '1'))
		{
			var minutesCheckMsg = minutesCheck(element.value);
			if (minutesCheckMsg != true)
			{
				statusText = statusText + _display_error_message(element, null, minutesCheckMsg);
				rtn = false;
			}
		}
	}

	//check for valid number fields
	for (var i = 0; i < elementsArray.length; i++)
	{
		element = elementsArray[i];
		if (element.type == 'text' && (element.value.length > 0) &&
			element.getAttribute('data-number') && (element.getAttribute('data-number') == 'true' ||
			element.getAttribute('data-number') == '1'))
		{
			var numberCheckMsg = numberCheck(element.value);
			if (numberCheckMsg != true)
			{
				statusText = statusText + _display_error_message(element, null, numberCheckMsg);
				rtn = false;
			}
		}
	}

	//check for valid money - positive and non-zero
	for (var i = 0; i < elementsArray.length; i++)
	{
		element = elementsArray[i];
		if (element.type == 'text' && (element.value.length > 0) &&
			element.getAttribute('data-money') && (element.getAttribute('data-money') == 'true' ||
			element.getAttribute('data-money') == '1'))
		{
			var moneyCheckMsg = moneyCheck(element.value);
			if (moneyCheckMsg != true)
			{
				statusText = statusText + _display_error_message(element, null, moneyCheckMsg);
				rtn = false;
			}
		}
	}

	// check if field has regex pattern
	for (var i = 0; i < elementsArray.length; i++)
	{
		element = elementsArray[i];

		if ($(element).attr('pattern') && ($(element).prop('required') || $(element).data('dd_required') == true))
		{
			var pattern = new RegExp($(element).attr('pattern'));

			if (!pattern.test($(element).val()))
			{
				if ($(element).data('message_pattern'))
				{
					messageText = $(element).data('message_pattern');
				}
				else
				{
					messageText = 'Improper format'
				}

				statusText = statusText + _display_error_message(element, null, messageText);
				rtn = false;
			}
		}
	}

	if (rtn == false && !allErrorsAreDisplayedInLine)
	{

		/*
		 var statusDiv = document.getElementById('errorMsg');

		 if (statusDiv)
		 {
		 statusDiv.style.display = 'block';
		 statusTextDiv = document.getElementById('errorMsgText');
		 statusTextDiv.innerHTML = 'Please correct the following errors on the page below.  ' + statusText;
		 scroll(0,0);
		 }
		 */

		dd_message({
			title: 'Validation Error',
			message: 'Please correct the following errors on the page below.<br /><br />' + statusText
		});

	}

	return rtn;
}