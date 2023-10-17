function _display_error_message(element, messageAttr, messageText)
{
	var elementLbl = document.getElementById(element.name + '_lbl');

	// auto append message if no _lbl method
	if (!elementLbl && ($(element).data('message') || $(element).data('message_match')))
	{
		var id = element.name;
		var errSpan = $('#' + id + '_erm');

		$(element).addClass('input_in_error');

		if (!errSpan.length)
		{
			var message = $('<span id="' + id + '_erm" class="warning_text temp_span">').html($(element).data('message'));
			$(message).insertAfter(element);
		}
		else
		{
			if (messageAttr == 'message_match')
			{
				$(errSpan).html($(element).data('message_match'));
			}
			else
			{
				$(errSpan).html($(element).data('message'));
			}

			$(errSpan).show();
		}

		return '';
	}

	// _lbl method
	if (elementLbl && ((messageAttr && elementLbl.getAttribute(messageAttr)) || messageText))
	{
		var originalText = '';

		if (!elementLbl.getAttribute('originalHTML'))
		{
			elementLbl.setAttribute('originalHTML', elementLbl.innerHTML);
		}

		if (elementLbl.getAttribute('originalHTML'))
		{
			originalText = elementLbl.getAttribute('originalHTML');
		}

		if (messageText)
		{
			elementLbl.innerHTML = '<span class="warning_text">' + messageText + '</span><br />' + originalText;
		}
		else
		{
			elementLbl.innerHTML = '<span class="warning_text">' + elementLbl.getAttribute(messageAttr) + '</span><br />' + originalText;
		}
	}
	else if (elementLbl && messageAttr && elementLbl.getAttribute(messageAttr))
	{
		return elementLbl.getAttribute(messageAttr) + '<br />';
	}
	else if (element && messageAttr && element.getAttribute(messageAttr))
	{
		return element.getAttribute(messageAttr) + '<br />';
	}
	else if (messageText)
	{
		return messageText + '<br />';
	}
	else
	{
		return ( element.name + ' is required. <br />' );
	}

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

	//reset everything
	for (var i = 0; i < elementsArray.length; i++)
	{
		var element = elementsArray[i];
		var elementLbl = document.getElementById(element.name + '_lbl');

		if (elementLbl && elementLbl.getAttribute('originalHTML') != null)
		{
			elementLbl.innerHTML = elementLbl.getAttribute('originalHTML');
			elementLbl.removeAttribute('originalHTML');
		}
	}

	//check for required
	for (var i = 0; i < elementsArray.length; i++)
	{
		var element = elementsArray[i];

		// if field required, and blank, throw error and cancel submit
		if (!element.disabled && (element.getAttribute('data-required') && (element.getAttribute('data-required') == 'true' || element.getAttribute('data-required') == '1')))
		{
			if (((element.type == 'text' || element.type == 'textarea' || element.type == 'password') && element.value.length < 1 ) ||
				(element.type == 'checkbox' && !element.checked))
			{
				statusText = statusText + _display_error_message(element, 'message', null);
				rtn = false;
			}
			else if (( element.type == 'select-one' ) && (element.options[0].value.length < 1) && (element.selectedIndex == 0))
			{
				statusText = statusText + _display_error_message(element, 'message', null);
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
					statusText = statusText + _display_error_message(element, 'message', null);
					rtn = false;
				}

				//check minimum length
			}
			else if (element.getAttribute('min'))
			{
				if ((element.type == 'text' || element.type == 'password') && elementsArray[i].value.length < elementsArray[i].getAttribute('min'))
				{
					statusText = statusText + _display_error_message(element, 'message_min', null);
					rtn = false;
				}
			}
		}
	}

	//also check for dd_required ... we must move away from "required" as that is defined by HTML5 and using causes .... issues
	for (var i = 0; i < elementsArray.length; i++)
	{
		var element = elementsArray[i];

		// if field required, and blank, throw error and cancel submit
		if (!element.disabled && (element.getAttribute('dd_required') || element.getAttribute('data-dd_required')) && (element.getAttribute('data-dd_required') == 'true' || element.getAttribute('dd_required') == 'true' || element.getAttribute('required') == '1'))
		{
			if (((element.type == 'text' || element.type == 'tel' || element.type == 'email' || element.type == 'textarea' || element.type == 'password') && element.value.length < 1 ) || (element.type == 'checkbox' && !element.checked))
			{
				statusText = statusText + _display_error_message(element, 'message', null);
				rtn = false;
			}
			else if (( element.type == 'select-one' ) && (element.options[0].value.length < 1) && (element.selectedIndex == 0))
			{
				statusText = statusText + _display_error_message(element, 'message', null);
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
					statusText = statusText + _display_error_message(element, 'message', null);
					rtn = false;
				}

				//check minimum length
			}
			else if (element.getAttribute('min'))
			{
				if ((element.type == 'text' || element.type == 'password') && elementsArray[i].value.length < elementsArray[i].getAttribute('min'))
				{
					statusText = statusText + _display_error_message(element, 'message_min', null);
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
		if (element.type == 'text' && element.getAttribute('email') && (element.getAttribute('email') == 'true' || element.getAttribute('email') == '1'))
		{
			var emailCheckMsg = emailCheck(element.value, false);

			if (emailCheckMsg != true)
			{
				statusText = statusText + _display_error_message(element, null, emailCheckMsg);
				rtn = false;
			}
		}

		// new optional email type can be empty
		if (element.type == 'text' && element.getAttribute('optional_email') && (element.getAttribute('optional_email') == 'true' || element.getAttribute('optional_email') == '1'))
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

		if (element.type == 'text' && (element.value.length > 0) && element.getAttribute('telephone') && (element.getAttribute('telephone') == 'true' || element.getAttribute('telephone') == '1'))
		{
			var telephoneCheckMsg = telephoneCheck(element.value);

			if (telephoneCheckMsg != true)
			{
				statusText = statusText + _display_error_message(element, null, telephoneCheckMsg);
				rtn = false;
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
				statusText = statusText + _display_error_message(element, 'message_match', null);
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

	//email = document.getElementById('primary_email');
	//cnfirm = document.getElementById('confirm_email_address');
	if (email && cnfirm)
	{
		if (cnfirm.value != email.value)
		{
			var cnfirmLbl = document.getElementById('confirm_email_address_lbl');
			statusText = statusText + _display_error_message(cnfirm, null, cnfirmLbl.getAttribute('message'));
			rtn = false;
		}
	}

	//check for valid hour fields
	for (var i = 0; i < elementsArray.length; i++)
	{
		element = elementsArray[i];

		if (element.type == 'text' && (element.value.length > 0) && element.getAttribute('hour') && (element.getAttribute('hour') == 'true' || element.getAttribute('hour') == '1'))
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

		if (element.type == 'text' && (element.value.length > 0) && element.getAttribute('minutes') && (element.getAttribute('minutes') == 'true' || element.getAttribute('minutes') == '1'))
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

		if (element.type == 'text' && (element.value.length > 0) && element.getAttribute('number') && (element.getAttribute('number') == 'true' || element.getAttribute('number') == '1'))
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

		if (element.type == 'text' && (element.value.length > 0) && element.getAttribute('data-money') && (element.getAttribute('data-money') == 'true' || element.getAttribute('data-money') == '1'))
		{
			var moneyCheckMsg = moneyCheck(element.value);

			if (moneyCheckMsg != true)
			{
				statusText = statusText + _display_error_message(element, null, moneyCheckMsg);
				rtn = false;
			}
		}
	}

	if (rtn == false)
	{
		$('#errorMsgText').html('Please correct the following errors on the page below. ' + statusText);
		$('#errorMsg').showFlex();
		scroll(0, 0);
	}
	else
	{
		$('#errorMsg').hideFlex();
	}

	return rtn;
}