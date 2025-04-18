var curZipCodeLength = 5;

function designClick(obj)
{
	$('#virt_design_type_div_expanded').slideUp();
	$('#phys_design_type_div_expanded').slideUp();
	$('#design_type_div_collapsed').slideDown();

	currentDesignType = $(obj).data('design_id');

	if (currentMediaType == 'virt')
	{
		$('#virtual_card_form').slideDown();
		$('#selectedDesignImg').html('<img class="img-fluid" style="height: 2rem;" src="' + PATH.image + '/gift_cards/' + card_designs[currentDesignType].image_path_egift + '">');

		$('#selected_design_desc').text(card_designs[currentDesignType].title);
		$('#expanded_virt_design_message').text("Currently Selected > " + card_designs[currentDesignType].title);
		$('#expanded_phys_design_message').text("Currently Selected > " + card_designs[currentDesignType].title);
	}
	else
	{
		$('#physical_card_form').slideDown();
		$('#selectedDesignImg').html('<img class="img-fluid" style="height: 2rem;" src="' + PATH.image + '/gift_cards/' + card_designs[currentDesignType].image_path + '">');

		$('#selected_design_desc').text(card_designs[currentDesignType].title);
		$('#expanded_virt_design_message').text("Currently Selected > " + card_designs[currentDesignType].title);
		$('#expanded_phys_design_message').text("Currently Selected > " + card_designs[currentDesignType].title);
	}

	$('#card_details_form').show();

	if (is_edit)
	{
		$('#edit_submit').show();
	}
	else
	{
		$('#add_submit').show();
	}

	update_required(currentMediaType);

}

function mediaClick(obj)
{
	$('#media_type_div_expanded').slideUp();
	$('#media_type_div_collapsed').show();

	if (!currentDesignType || currentDesignType == 'none' || $('#phys_design_type_div_expanded').is(":visible") || $('#virt_design_type_div_expanded').is(":visible"))
	{
		if (obj.id == "physical_card" || obj.id == "physical_card_img")
		{
			$('#phys_design_type_div_expanded').slideDown();
			$('#virt_design_type_div_expanded').slideUp();
		}
		else
		{
			$('#virt_design_type_div_expanded').slideDown();
			$('#phys_design_type_div_expanded').slideUp();
		}
	}

	if (obj.id == "physical_card" || obj.id == "physical_card_img")
	{
		$('#selected_media_desc').text("Traditional Card (Sent via standard mail)");
		currentMediaType = "phys";

		$("#expanded_type_message").text("Currently Selected > Traditional Card");

		if (currentDesignType && currentDesignType != "none") // design has been picked so show correct form
		{
			$('#physical_card_form').show();
			$('#virtual_card_form').hide();

			if (is_edit)
			{
				$('#edit_submit').show();
			}
			else
			{
				$('#add_submit').show();
			}

			// also switch design image
			if (card_designs[currentDesignType])
			{
				document.getElementById('selectedDesignImg').src = PATH.image + '/gift_cards/' + card_designs[currentDesignType].image_path;
				$('#selected_design_desc').text(card_designs[currentDesignType].title);
			}
			else
			{
				currentDesignType = false;

				$('#phys_design_type_div_expanded').show();
				$('#virt_design_type_div_expanded').hide();
				$('#design_type_div_collapsed').hide();
				$('#physical_card_form').hide();
				$('#card_details_form').hide();

				if (is_edit)
				{
					$('#edit_submit').show();
				}
				else
				{
					$('#add_submit').show();
				}

			}

		}

	}
	else
	{
		$('#selected_media_desc').text("Virtual eGift Card (Sent via email)");
		currentMediaType = "virt";

		$('#expanded_type_message').text("Currently Selected > Virtual eGift Card");

		if (currentDesignType && currentDesignType != "none") // design has been picked so show correct form
		{
			document.getElementById('physical_card_form').style.display = "none";
			document.getElementById('virtual_card_form').style.display = "block";

			if (is_edit)
			{
				document.getElementById('edit_submit').style.display = "block";
			}
			else
			{
				document.getElementById('add_submit').style.display = "block";
			}

			document.getElementById('selectedDesignImg').src = PATH.image + '/gift_cards/' + card_designs[currentDesignType].image_path;
			document.getElementById('selected_design_desc').innerHTML = card_designs[currentDesignType].title;

			// also switch design image
			if (card_designs[currentDesignType].image_path_egift)
			{
				document.getElementById('selectedDesignImg').src = PATH.image + '/gift_cards/' + card_designs[currentDesignType].image_path_egift;
				document.getElementById('selected_design_desc').innerHTML = card_designs[currentDesignType].title;
			}
			else
			{
				currentDesignType = false;
				document.getElementById('virt_design_type_div_expanded').style.display = "block";
				document.getElementById('phys_design_type_div_expanded').style.display = "none";
				document.getElementById('design_type_div_collapsed').style.display = "none";
				document.getElementById('virtual_card_form').style.display = "none";
				document.getElementById('card_details_form').style.display = "none";

				if (is_edit)
				{
					document.getElementById('edit_submit').style.display = "block";
				}
				else
				{
					document.getElementById('add_submit').style.display = "block";
				}
			}
		}
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

function modifyMedia(obj)
{
	$('#card_details_form').slideUp();
	$('#physical_card_form').slideUp();

	$('#virt_design_type_div_expanded').slideUp();
	$('#phys_design_type_div_expanded').slideUp();

	$('#media_type_div_expanded').slideDown();
	$('#media_type_div_collapsed').slideUp();
}

function handleStateSelection(obj)
{
	switch (obj.value)
	{
		case 'AB':
		case 'BC':
		case 'MB':
		case 'NB':
		case 'NL':
		case 'NS':
		case 'NT':
		case 'NU':
		case 'ON':
		case 'PE':
		case 'QC':
		case 'SK':
		case 'YT':
			curZipCodeLength = 6;
			break;
		default:
			curZipCodeLength = 5;
	}

	$('#numZipDigits').html(curZipCodeLength);
	$('#shipping_zip').attr('maxlength', curZipCodeLength);

	if (curZipCodeLength == 6)
	{
		$('#shipping_zip').removeAttr('data-number');
	}
	else
	{
		$('#shipping_zip').attr('data-number', 'true');
	}
}

function update_required(currentMediaType)
{

	if (currentMediaType == "phys")
	{
		$("#shipping_address_1").prop('required', true);
		$("#shipping_city").prop('required', true);
		$("#shipping_state").prop('required', true);
		$("#shipping_zip").prop('required', true);
		$("#shipping_first_name").prop('required', true);
		$("#shipping_last_name").prop('required', true);
		$("#physical_card").prop('required', true);
		$("#shipping_zip").prop('required', true);

		$("#recipient_email").prop('required', false);
		$("#confirm_recipient_email").prop('required', false);
	}
	else
	{
		$("#shipping_address_1").prop('required', false);
		$("#shipping_city").prop('required', false);
		$("#shipping_state").prop('required', false);
		$("#shipping_zip").prop('required', false);
		$("#shipping_first_name").prop('required', false);
		$("#shipping_last_name").prop('required', false);
		$("#physical_card").prop('required', false);
		$("#shipping_zip").prop('required', false);

		$("#recipient_email").prop('required', true);
		$("#confirm_recipient_email").prop('required', true);
	}

	if (currentMediaType)
	{
		document.getElementById("media_type").value = currentMediaType;
	}

	if (currentDesignType)
	{
		document.getElementById("design_id").value = currentDesignType;
	}

	if (currentMediaType == "phys")
	{
		var zipStr = document.getElementById('shipping_zip').value;
		if (zipStr.length != curZipCodeLength)
		{

		}
	}

}

function quantityClick(val)
{
	if (val > 50)
	{
		document.getElementById('quantity').value = 50;
		alert("You can only order 50 gift cards at a time. To order more than 50 gift cards at once please contact Dream Dinners Customer Support.");
	}
}

function updateInfo()
{
	if (document.getElementById('sameInfo').checked == 1)
	{
		document.getElementById('name_credit_card').value = document.getElementById('shipping_first_name').value + ' ' + document.getElementById('shipping_last_name').value;
		document.getElementById('billing_address').value = document.getElementById('shipping_address_1').value + ' ' + document.getElementById('shipping_address_2').value;
		document.getElementById('billing_zip').value = document.getElementById('shipping_zip').value;
	}
	else
	{
		document.getElementById('name_credit_card').value = '';
		document.getElementById('billing_address').value = '';
		document.getElementById('billing_zip').value = '';
	}

}

var codes = '';
var i = 0;
var x = 0;
var browser = navigator.appName;
var charCode = '';

function keyHandler(evt)
{
	if (browser == "Microsoft Internet Explorer")
	{
		charCode = (document.all) ? event.keyCode : evt.keyCode;
	}
	else
	{
		charCode = evt.keyCode ? evt.keyCode : evt.charCode;
	}
	if (charCode == 13)
	{
		return false;
	}

	if (charCode == 94)
	{
		if (codes.length >= 15)
		{
			document.getElementById('gift_card_number').value = codes;
			codes = '';
			i = 0;
		}
		else
		{
			codes = '';
			i = 0;
		}
		return false;
	}
	if (i == 2 && (parseInt(charCode) > 57 || parseInt(charCode) < 48) && codes.length < 15 && charCode != 94 && charCode != 8)
	{
		//document.getElementById('gift_card_number').value=codes;
		return false;
	}
	if ((parseInt(charCode) > 57 || parseInt(charCode) < 48) && i < 2 && charCode != 94 && (String.fromCharCode(charCode)) != 'B' && charCode != 8)
	{
		//document.getElementById('gift_card_number').value=codes;

	}
	if ((String.fromCharCode(charCode) == '%') && (i == 0))
	{
		i = 1;
	}
	else if ((String.fromCharCode(charCode) == 'B') && (i == 1))
	{
		i = 2;
	}

	if ((String.fromCharCode(charCode) != 'B') && (i == 2))
	{
		if ((String.fromCharCode(charCode) != '^'))
		{
			codes += String.fromCharCode(charCode);
		}
	}

}

document.onkeypress = keyHandler;

$(function () {

	$('.choose_media:not(.disabled)').on('click', function(e) {

		mediaClick(this);
		e.preventDefault();

	});

	$('.choose_design').on('click', function(e) {

		designClick(this);
		e.preventDefault();

	});

	$('.modify_media').on('click', function(e) {

		modifyMedia();
		e.preventDefault();

	});

	$('.modify_design').on('click', function(e) {

		modifyDesign();
		e.preventDefault();

	});

	if (is_edit || is_error)
	{

		$('#media_type_div_collapsed').show();
		$('#design_type_div_collapsed').show();
		$('#card_details_form').show();

		if (currentMediaType == 'virt')
		{

			$('#virtual_card_form').show();
			mediaClick(document.getElementById('virtual_card'));

		}
		else
		{

			$('#physical_card_form').show();
			mediaClick(document.getElementById('physical_card'));

		}

		var designName = 'cd_' + currentDesignType;
		designClick(document.getElementById(designName));

	}
	else if (false /*$this->hadError*/)
	{

		$('#media_type_div_expanded').slideUp();
		$('#media_type_div_collapsed').show();
		$('#design_type_div_expanded').hide();
		$('#design_type_div_collapsed').show();

		$('#add_submit').show();

	}
	else
	{

		$('#media_type_div_expanded').show();
		$('#add_submit').hide();

	}

	handleStateSelection($('#shipping_state')[0]);

});