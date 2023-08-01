var currentMediaType = false;
var currentDesignType = false;

function updateInfo()
{
	if (document.getElementById('sameInfo').checked == 1)
	{

		document.getElementById('billing_name').value = document.getElementById('shipping_first_name').value + ' ' + document.getElementById('shipping_last_name').value;
		document.getElementById('billing_address').value = document.getElementById('shipping_address_1').value + ' ' + document.getElementById('shipping_address_2').value;
		document.getElementById('billing_zip').value = document.getElementById('shipping_zip').value;
	}
	else
	{
		document.getElementById('billing_name').value = '';
		document.getElementById('billing_address').value = '';
		document.getElementById('billing_zip').value = '';
	}

}

function modifyDesign(obj)
{
	if (currentMediaType == 'virt')
	{
		document.getElementById('virt_design_type_div_expanded').style.display = "block";
	}
	else
	{
		document.getElementById('phys_design_type_div_expanded').style.display = "block";
	}

	document.getElementById('design_type_div_collapsed').style.display = "none";
}



function mediaClick(obj)
{
	document.getElementById('media_type_div_expanded').style.display = "none";
	document.getElementById('media_type_div_collapsed').style.display = "block";

	if (!currentDesignType || document.getElementById('phys_design_type_div_expanded').style.display == "block" ||
		document.getElementById('virt_design_type_div_expanded').style.display == "block")
	{
		if (obj.id == "physical_card" || obj.id == "physical_card_img")
		{
			document.getElementById('phys_design_type_div_expanded').style.display = "block";
			document.getElementById('virt_design_type_div_expanded').style.display = "none";
		}
		else
		{
			document.getElementById('virt_design_type_div_expanded').style.display = "block";
			document.getElementById('phys_design_type_div_expanded').style.display = "none";
		}
	}

	if (obj.id == "physical_card" || obj.id == "physical_card_img")
	{
		document.getElementById('selected_media_desc').innerHTML = "Traditional Card (Sent via standard mail)";
		currentMediaType = "phys";

		document.getElementById("expanded_type_message").innerHTML = "&nbsp;&nbsp;Currently Selected > Traditional Card (Sent via standard mail)";

		document.getElementById("phys_div").style.backgroundColor = "#b5cfb5";
		document.getElementById("virt_div").style.backgroundColor = "#f8f6f5";

		if (currentDesignType) // design has been picked so show correct form
		{
			document.getElementById('physical_card_form').style.display = "block";
			document.getElementById('use_shipping').style.display = "inline";
			document.getElementById('s_and_h_note').style.display = "block";

			document.getElementById('virtual_card_form').style.display = "none";
			document.getElementById('procCardOrderBtn').style.display = "block";

			// also switch design image
			if (designImageNames[currentDesignType])
			{
				document.getElementById('selectedDesignImg').src = "<?= ADMIN_IMAGES_PATH ?>/gift_card/" + designImageNames[currentDesignType];
				document.getElementById('selected_design_desc').innerHTML = designDescription[currentDesignType];
			}
			else
			{
				currentDesignType = false;
				document.getElementById('phys_design_type_div_expanded').style.display = "block";
				document.getElementById('virt_design_type_div_expanded').style.display = "none";
				document.getElementById('design_type_div_collapsed').style.display = "none";
				document.getElementById('physical_card_form').style.display = "none";
				document.getElementById('card_details_form').style.display = "none";
				document.getElementById('payment_form_div').style.display = "none";

				document.getElementById('procCardOrderBtn').style.display = "none";
			}

		}

	}
	else
	{
		document.getElementById('selected_media_desc').innerHTML = "Virtual eGift Card (Sent via email)";
		currentMediaType = "virt";

		document.getElementById('expanded_type_message').innerHTML = "&nbsp;&nbsp;Currently Selected > Virtual eGift Card (Sent via email)";

		document.getElementById("phys_div").style.backgroundColor = "#f8f6f5";
		document.getElementById("virt_div").style.backgroundColor = "#b5cfb5";

		if (currentDesignType) // design has been picked so show correct form
		{
			document.getElementById('physical_card_form').style.display = "none";
			document.getElementById('use_shipping').style.display = "none";
			document.getElementById('s_and_h_note').style.display = "none";

			document.getElementById('virtual_card_form').style.display = "block";

			document.getElementById('procCardOrderBtn').style.display = "block";

			document.getElementById('selectedDesignImg').src = "<?= ADMIN_IMAGES_PATH ?>/gift_card/" + designImageNamesVirt[currentDesignType];
			document.getElementById('selected_design_desc').innerHTML = designDescriptionVirt[currentDesignType];

			// also switch design image
			if (designImageNamesVirt[currentDesignType])
			{
				document.getElementById('selectedDesignImg').src = "<?= ADMIN_IMAGES_PATH ?>/gift_card/" + designImageNamesVirt[currentDesignType];
				document.getElementById('selected_design_desc').innerHTML = designDescriptionVirt[currentDesignType];
			}
			else
			{
				currentDesignType = false;
				document.getElementById('virt_design_type_div_expanded').style.display = "block";
				document.getElementById('phys_design_type_div_expanded').style.display = "none";
				document.getElementById('design_type_div_collapsed').style.display = "none";
				document.getElementById('virtual_card_form').style.display = "none";
				document.getElementById('card_details_form').style.display = "none";
				document.getElementById('payment_form_div').style.display = "none";

				document.getElementById('procCardOrderBtn').style.display = "none";

			}

		}

	}

}

function modifyMedia(obj)
{
	document.getElementById('media_type_div_expanded').style.display = "block";
	document.getElementById('media_type_div_collapsed').style.display = "none";
}

function _override_check_form(form)
{

	debugger;

	if (currentMediaType == "phys")
	{
		document.getElementById('shipping_address_1').setAttribute('required', true);
		document.getElementById('shipping_city').setAttribute('required', true);
		document.getElementById('shipping_state').setAttribute('required', true);
		document.getElementById('shipping_zip').setAttribute('required', true);
		document.getElementById('shipping_first_name').setAttribute('required', true);
		document.getElementById('shipping_last_name').setAttribute('required', true);
		document.getElementById('physical_card').setAttribute('required', true);
		document.getElementById('confirm_recipient_email').setAttribute('optional_email', false);
		document.getElementById('recipient_email').setAttribute('optional_email', false);
		document.getElementById('confirm_recipient_email').setAttribute('email', false);
		document.getElementById('recipient_email').setAttribute('email', false);
		document.getElementById('confirm_recipient_email').setAttribute('required', false);
		document.getElementById('recipient_email').setAttribute('required', false);

	}
	else
	{
		document.getElementById('shipping_address_1').setAttribute('required', false);
		document.getElementById('shipping_city').setAttribute('required', false);
		document.getElementById('shipping_state').setAttribute('required', false);
		document.getElementById('shipping_zip').setAttribute('required', false);
		document.getElementById('shipping_first_name').setAttribute('required', false);
		document.getElementById('shipping_last_name').setAttribute('required', false);
		document.getElementById('physical_card').setAttribute('required', false);
		document.getElementById('confirm_recipient_email').setAttribute('optional_email', false);
		document.getElementById('recipient_email').setAttribute('optional_email', false);
		document.getElementById('confirm_recipient_email').setAttribute('email', true);
		document.getElementById('recipient_email').setAttribute('email', true);
		document.getElementById('confirm_recipient_email').setAttribute('required', true);
		document.getElementById('recipient_email').setAttribute('required', true);

		if (!document.getElementById('shipping_zip').value || document.getElementById('shipping_zip').value == "")
		{
			document.getElementById('shipping_zip').value = "11111";
		}

	}

	if (currentMediaType)
	{
		document.getElementById("media_type").value = currentMediaType;
	}
	else
	{
		//error!
		return false;
	}

	if (currentDesignType)
	{
		document.getElementById("design_id").value = currentDesignType;
	}
	else
	{
		//error!
		return false;
	}

	var form_valid = _check_form(form);

	if (currentMediaType == "phys")
	{
		var zipStr = document.getElementById('shipping_zip').value;
		if (zipStr.length != 5)
		{
			_display_error_message(form.shipping_zip, 'message', 'The ZIP code must be 5 digits long.');
			return false;
		}
	}

	var zipStr = document.getElementById('billing_zip').value;
	if (zipStr.length != 5)
	{
		_display_error_message(form.billing_zip, 'message', 'The ZIP code must be 5 digits long.');
		return false;
	}

	return form_valid;

}

function _submit_click(obj)
{
	debugger;

	obj.style.display = "none";

	var frmObj = $("#gift_card_order")[0];

	var validated = _override_check_form(frmObj);

	if (!validated)
	{
		document.getElementById('procCardOrderBtn').style.display = "block"
	}
	else
	{
		var recipient = "";
		if (currentMediaType == "virt")
		{
			recipient = $('#recipient_email').val();
		}
		else
		{
			recipient = $('#shipping_first_name').val() + " " + $('#shipping_last_name').val();
		}

		var msg = "Order a " + (currentMediaType == "virt" ? "VIRTUAL" : "PHYSICAL") + " Gift Card for the amount of $" + formatAsMoney($("#amount").val()) + " to be sent to <i>" + recipient + "</i>?";

		dd_message({
			title: 'Submit Gift Card Order',
			message: msg,
			modal: true,
			confirm: function ()
			{
				processOrder();

			},
			cancel: function ()
			{
				$('#procCardOrderBtn').show();
			}

		});

	}

	return true;
}


function processOrder()
{
	$("#gift_card_order").submit();
}

(function() {

	$(document).on('change', '#credit_card_number', function (e) {

		$(this).validateCreditCard(function (result) {

				var validatorType = false;
				if (result.card_type != null && result.card_type.name != null)
				{
					validatorType = result.card_type.name;
				}

				if (($('#credit_card_type').val() == '' || $('#credit_card_type').val() == null) && validatorType)
				{
					switch (validatorType)
					{
						case 'Visa':
							$('#credit_card_type').val('Visa');
							break;
						case 'Mastercard':
							$('#credit_card_type').val('Mastercard');
							break;
						case 'American Express':
							$('#credit_card_type').val('American Express');
							break;
						case 'Discover':
							$('#credit_card_type').val('Discover');
							break;
						default:
							break;
					}
				}

				if ($('#credit_card_type').val() != '')
				{
					// get validator type name of current type setting
					var currentTypeWidgetValue = false;
					switch ($('#credit_card_type').val())
					{
						case 'Visa':
							currentTypeWidgetValue = 'Visa';
							break;
						case 'Mastercard':
							currentTypeWidgetValue = 'Mastercard';
							break;
						case 'American Express':
							currentTypeWidgetValue = 'American Express';
							break;
						case 'Discover':
							currentTypeWidgetValue = 'Discover';
							break;
						default:
							break;
					}

					if (currentTypeWidgetValue && currentTypeWidgetValue != validatorType && $(this).val().length > 2)
					{
						$(".credit_card_warning").removeClass('collapse');
					}
					else
					{
						$(".credit_card_warning").addClass('collapse');
					}

				}
			},
			{
				accept: [
					'visa',
					'mastercard',
					'discover',
					'amex'
				]
			});

	});

})();