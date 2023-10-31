let numberOfCustomerFacingCTSItems = 0;
let numberOfCustomerFacingEFLItems = 0;
let sidesDataArray = [];
let gNumOptionalsAdded = 0;
let notShown = true;
let dp_processing = false;
let xmlHttp = null;
let noOverrideUnderBase = false;

function resetPage()
{
	document.getElementById('action').value = "menuChange";
	document.getElementById('menu_editor_form').submit();
}

function getPreviewItemPrice(itemID)
{
	// does not exist so return the current price
	let ovrPrice = Number($("#ovr_" + itemID).val());

	if (isNaN(ovrPrice) || ovrPrice.valueOf() == 0)
	{
		// does not exist so return the markup price
		return Number($("#row_" + itemID + " > .preview-price").text());
	}

	return ovrPrice;
}

function removeOptionSelected()
{
	let elSel = document.getElementById('sides');
	for (let i = elSel.length - 1; i >= 1; i--)
	{
		if (elSel.options[i].selected)
		{
			elSel.remove(i);
		}
	}
}

function removeItem(itemNum)
{
	gNumOptionalsAdded--;
	let rowID = "row_" + itemNum;
	let rowObj = document.getElementById(rowID);
	rowObj.parentNode.removeChild(rowObj);

	let sidesData = sidesDataArray[itemNum];
	let sideName = "(" + sidesData['SUPC_number'] + ") " + sidesData['name'];

	let selObj = document.getElementById('sides');

	selObj.options[selObj.length] = new Option(sideName, itemNum);

	calculatePage();
}

function displayValidationErrorMsg(msg)
{
	msg = "<span style='color:red; font-size:larger;'>" + msg + "</span>";

	dd_message({
		title: 'Error',
		message: msg
	});
}

function clearErrors()
{
	$('#errorMsgText').html('');
	$('#errorMsg').hideFlex();
}

function storeChange(obj)
{
	document.getElementById('action').value = "storeChange";
	document.getElementById('menu_editor_form').submit();
}

function menuChange(obj)
{
	document.getElementById('action').value = "menuChange";
	document.getElementById('menu_editor_form').submit();
}

function confirm_and_round_form(tab)
{
	const finalizeMessage = "Are you sure want to update all Override Prices with rounded Markup Prices? " +
		"This action will replace existing override prices with the newly calculated price.";
	let hasOverridePrice = false;
	$('.' + tab + '-override-price-input').each(function () {
			hasOverridePrice = $(this).val().trim() !== '';
		}
	);

	if (hasOverridePrice)
	{
		dd_message({
			title: 'Attention',
			message: finalizeMessage,
			modal: true,
			confirm: function () {
				round_markup_to_override(tab);
				return false;
			},
			cancel: function () {
				return false;
			}
		});
	}
	else
	{
		round_markup_to_override(tab);
	}

	return false;
}

function round_markup_to_override(tab)
{
	let dirty = false;
	$('.markup-price').each(function (index) {
		if ($(this).is('[readonly]'))
		{
			//nothing;
		}
		else
		{
			let o_input = $(this).parent().find('.' + tab + '-override-price-input');
			if (o_input.length)
			{
				let price = parseFloat($(this).text());
				let o_price = Math.ceil(price * 2) / 2;
				o_input.val(o_price.toFixed(2));
				dirty = true;
			}
		}
	});

	if (dirty)
	{
		calculatePage();
		$('#saved_message, #saved_message_2').showFlex();
	}

}

function selectedDefault()
{
	if (notShown)
	{
		bootbox.alert("Any Menus that do not currently have their own specific settings finalized will inherit these default settings.");
		notShown = false;
	}

	calculatePage();
}

function showPopup(config)
{
	var settings = { //defaults
		type: 'GET',
		height: 500,
		width: 600,
		modal: false,
		callBack: false,
		resizable: true
	};

	$.extend(settings, config);

	$.ajax({
		url: '?' + settings.module,
		type: settings.type,
		success: function (data, status) {
			settings.message = data;

			dd_message(settings);

			if (typeof settings.callBack == 'function')
			{
				settings.callBack();
			}
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';
		}
	});
}

function getXmlHttpObject()
{
	let objXMLHttp = null;

	try
	{
		// Opera 8.0+, Firefox, Safari
		objXMLHttp = new XMLHttpRequest();
	}
	catch (e)
	{
		// Internet Explorer Browsers
		try
		{
			objXMLHttp = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			try
			{
				objXMLHttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				// Something went wrong
			}
		}
	}

	return objXMLHttp;
}

function def_price_save_handleTimeout()
{
	if (dp_processing)
	{
		dp_processing = false;

		let printString = "The server has not responded. Please try again.";

		document.getElementById("dp_error").style.display = 'block';

		document.getElementById("dp_error").innerHTML = printString;
		document.getElementById('dp_proc_mess').style.display = "none";
	}
}

function def_price_retreive_handleTimeout()
{
	if (dp_processing)
	{
		dp_processing = false;

		let printString = "The server has not responded. Please try again.";

		document.getElementById("dp_error").style.display = 'block';

		document.getElementById("dp_error").innerHTML = printString;
		document.getElementById('dp_proc_mess').style.display = "none";
	}
}

function SaveDefPricingComplete()
{
	if ((xmlHttp.readyState == 4 || xmlHttp.readyState == "complete") && dp_processing)
	{
		dp_processing = false;
		document.getElementById('dp_proc_mess').style.display = "none";
		let printString = "Sides &amp; Sweets default pricing has been saved";
		document.getElementById("dp_error").style.display = 'block';
		document.getElementById("dp_error").innerHTML = printString;
	}
}

function RetrieveDefPricingComplete()
{
	if ((xmlHttp.readyState == 4 || xmlHttp.readyState == "complete") && dp_processing)
	{
		dp_processing = false;

		if (xmlHttp.responseText && xmlHttp.responseText != "error" && xmlHttp.responseText != "")
		{
			let pricingArray = xmlHttp.responseText.split("|");
			let RidMap = [];
			for (let key in pricingArray)
			{
				var item = pricingArray[key].split("~");
				if (item[0])
				{
					RidMap[item[0]] = [
						item[1],
						item[2],
						item[3]
					];
				}
			}

			calculatePage();

			let totalCurrentlyWebVisible = numberOfCustomerFacingCTSItems;

			let ctsItemRows = document.getElementById('ctsItemsTbl').rows;

			for (let i = 0; i < ctsItemRows.length; i++)
			{
				if (ctsItemRows[i].id.indexOf('row_') == -1)
				{
					continue;
				}

				let itemNumber = null;
				itemNumber = ctsItemRows[i].id.substr(4);

				let rid = document.getElementById('rec_id_' + itemNumber).innerHTML;

				if (RidMap[rid])
				{
					let overrideElemName = 'ovr_' + itemNumber;
					let overrideElem = document.getElementById(overrideElemName);
					if (overrideElem)
					{
						overrideElem.value = RidMap[rid][2];
					}

					let showCustomerBox = document.getElementById('vis_' + itemNumber);

					let is_web_visible = showCustomerBox.checked;

					if (showCustomerBox)
					{
						if (RidMap[rid][0] == "1")
						{
							if (!is_web_visible && totalCurrentlyWebVisible < 20)
							{
								showCustomerBox.checked = true;
								totalCurrentlyWebVisible++;
							}
						}
						else
						{
							if (is_web_visible)
							{
								showCustomerBox.checked = false;
								totalCurrentlyWebVisible--;
							}
						}
					}

					let hideEverywhereBox = document.getElementById('hid_' + itemNumber);
					if (hideEverywhereBox)
					{
						if (RidMap[rid][1] == "1")
						{
							hideEverywhereBox.checked = true;
						}
						else
						{
							hideEverywhereBox.checked = false;
						}
					}
				}
			}
			calculatePage();

			document.getElementById('dp_proc_mess').style.display = "none";
			document.getElementById("dp_error").style.display = 'none';
		}
		else
		{
			document.getElementById('dp_proc_mess').style.display = "none";
			let printString = "There was an error when retrieving the default prices. Please try again.";
			document.getElementById("dp_error").style.display = 'block';
			document.getElementById("dp_error").innerHTML = printString;
		}
	}
}

function SavePricing()
{
	dd_message({
		title: 'Save Default Pricing and Visibility',
		message: 'Are you sure you want to save the default Sides and Sweets pricing and visibility? This cannot be undone.',
		confirm: function () {
			let payLoad = "data=";
			// Chef Touched Selections
			let ctsItemRows = document.getElementById('ctsItemsTbl').rows;
			for (let i = 0; i < ctsItemRows.length; i++)
			{
				if (ctsItemRows[i].id.indexOf('row_') == -1)
				{
					continue;
				}

				let itemNumber = null;
				itemNumber = ctsItemRows[i].id.substr(4);

				let rid = document.getElementById('rec_id_' + itemNumber).innerHTML;

				let currentPrice = Number(menuInfo.mid[itemNumber].price);
				let previewPrice = Number($("#row_" + itemNumber + " > .preview-price").text());
				let basePrice = Number(menuInfo.mid[itemNumber].base_price);

				if (previewPrice != "" && previewPrice != 0 && !isNaN(previewPrice))
				{
					currentPrice = previewPrice;
				}

				if (currentPrice < basePrice)
				{
					document.getElementById('dp_proc_mess').style.display = "none";
					document.getElementById("dp_error").style.display = "block";
					document.getElementById("dp_error").innerHTML = "Override price must be greater than the price.";
					return;
				}

				let show_to_customer = "0";

				if (document.getElementById('vis_' + itemNumber) && document.getElementById('vis_' + itemNumber).checked)
				{
					show_to_customer = "1";
				}

				let hide_completely = "0";
				if (document.getElementById('hid_' + itemNumber) && document.getElementById('hid_' + itemNumber).checked)
				{
					hide_completely = "1";
				}

				payLoad += rid + "~" + show_to_customer + "~" + hide_completely + "~" + currentPrice + "|";
			}

			xmlHttp = getXmlHttpObject();
			if (xmlHttp == null)
			{
				// temporary
				bootbox.alert("The Default Pricing function is not compatible with your browser. Please contact your store for assistance.");
				return;
			}

			document.getElementById('dp_proc_mess').style.display = "inline";
			document.getElementById("dp_error").style.display = "none";
			document.getElementById("dp_error").innerHTML = "";

			let url = "/processor?processor=admin_defaultPricingProcessor&action=save&store_id=" + storeInfo.id;
			//	var url = "/processor?processor=admin_defaultPricingProcessor&action=save&data=" + payLoad + "&store_id=<?=$this->store_id?>";
			xmlHttp.onreadystatechange = SaveDefPricingComplete;

			xmlHttp.open("POST", url, true);
			xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xmlHttp.setRequestHeader("Content-length", payLoad.length);
			xmlHttp.setRequestHeader("Connection", "close");
			xmlHttp.send(payLoad);

			setTimeout("def_price_save_handleTimeout();", 20000);
			dp_processing = true;
		},
		cancel: function () {
		}
	});

}

function RetrievePricing()
{
	if (dp_processing)
	{
		return;
	}

	xmlHttp = getXmlHttpObject();
	if (xmlHttp == null)
	{
		// temporary
		bootbox.alert("The Default Pricing function is not compatible with your browser. Please contact your store for assistance.");
		return;
	}

	dd_message({
		title: 'Retrieve Default Pricing and Visibility',
		message: 'Are you sure you want to retrieve the default Sides and Sweets pricing and visibility? This cannot be undone.',
		confirm: function () {

			document.getElementById('dp_proc_mess').style.display = "inline";
			document.getElementById("dp_error").style.display = "none";
			document.getElementById("dp_error").innerHTML = "";

			var url = "/processor?processor=admin_defaultPricingProcessor&action=retrieve&store_id=" + storeInfo.id;
			//	var url = "/processor?processor=admin_defaultPricingProcessor&action=save&data=" + payLoad + "&store_id=<?=$this->store_id?>";
			xmlHttp.onreadystatechange = RetrieveDefPricingComplete;

			xmlHttp.open("GET", url, true);
			xmlHttp.send(null);

			setTimeout("def_price_retreive_handleTimeout();", 20000);
			dp_processing = true;
		},
		cancel: function () {
		}
	});

}

function calculatePage()
{
	numberOfCustomerFacingCTSItems = 0;
	numberOfCustomerFacingEFLItems = 0;

	let uniqueEFLEntreeId = [];
	let is_unsaved = false;

	let sidesCount = 0;

	let current2ServMarkup = ((!$('#markup_2_serving').val()) ? null : $('#markup_2_serving').val());
	let current3ServMarkup = ((!$('#markup_3_serving').val()) ? null : $('#markup_3_serving').val());
	let current4ServMarkup = ((!$('#markup_4_serving').val()) ? null : $('#markup_4_serving').val());
	let current6ServMarkup = ((!$('#markup_6_serving').val()) ? null : $('#markup_6_serving').val());
	let currentSidesMarkup = ((!$('#markup_sides').val()) ? null : $('#markup_sides').val());
	let currentAssemblyFee = ((!$('#assembly_fee').val()) ? null : $('#assembly_fee').val());
	let currentDeliveryAssemblyFee = ((!$('#delivery_assembly_fee').val()) ? null : $('#delivery_assembly_fee').val());

	let currentDefaultStatus = document.getElementById('is_default_markupno').checked ? 0 : 1;

	if ($('#delivery_assembly_fee').length != 0)
	{
		if (formatAsMoney(markupData.delivery_assembly_fee) != formatAsMoney(currentDeliveryAssemblyFee))
		{
			is_unsaved = true;
			//dd_console_log("Unsaved 1");
		}
	}

	if ($('#assembly_fee').length != 0)
	{
		if (formatAsMoney(markupData.assembly_fee) != formatAsMoney(currentAssemblyFee))
		{
			is_unsaved = true;
			//dd_console_log("Unsaved 2");
		}
	}

	if ($('#markup_2_serving').length != 0)
	{
		if (formatAsMoney(markupData.markup_value_2_serving) != formatAsMoney(current2ServMarkup))
		{
			is_unsaved = true;
			//dd_console_log("Unsaved 3");
		}
	}

	if ($('#markup_3_serving').length != 0)
	{
		if (formatAsMoney(markupData.markup_value_3_serving) != formatAsMoney(current3ServMarkup))
		{
			is_unsaved = true;
			//dd_console_log("Unsaved 4");
		}
	}

	if ($('#markup_4_serving').length != 0)
	{
		if (formatAsMoney(markupData.markup_value_4_serving) != formatAsMoney(current4ServMarkup))
		{
			is_unsaved = true;
			//dd_console_log("Unsaved 5");
		}
	}

	if ($('#markup_6_serving').length != 0)
	{
		if (formatAsMoney(markupData.markup_value_6_serving) != formatAsMoney(current6ServMarkup))
		{
			is_unsaved = true;
			//dd_console_log("Unsaved 6");
		}
	}

	if ($('#markup_sides').length != 0)
	{
		if (formatAsMoney(markupData.markup_value_sides) != formatAsMoney(currentSidesMarkup))
		{
			is_unsaved = true;
			//dd_console_log("Unsaved 7");
		}
	}

	if (menuInfo.menu_id != '100')
	{
		if (markupData.is_default != currentDefaultStatus)
		{
			is_unsaved = true;
			//dd_console_log("Unsaved 8");
		}
	}

	let itemRows = document.getElementById('itemsTbl').rows;

	for (let i = 0; i < itemRows.length; i++)
	{
		if (itemRows[i].id.indexOf('row_') == -1 && itemRows[i].id != 'addonEditorRow')
		{
			continue;
		}

		let itemNumber = null;

		if (itemRows[i].id == 'addonEditorRow')
		{
			itemNumber = itemRows[i].getAttribute('item_id');
			if (!itemNumber)
			{
				continue;
			}
		}
		else
		{
			itemNumber = itemRows[i].id.substr(4);
		}

		let basePrice = Number(menuInfo.mid[itemNumber].base_price);
		let currentPrice = Number(menuInfo.mid[itemNumber].price);

		let ltd_menu_item_value = 0;
		if (storeInfo.supports_ltd_roundup == 1)
		{
			ltd_menu_item_value = Number(formatAsMoney(menuInfo.mid[itemNumber].ltd_menu_item_value));
		}

		if (menuInfo.mid[itemNumber].is_chef_touched == '1')
		{
			sidesCount++;
		}

		let overridePrice = Number(0);
		let orgOverridePrice = Number(0);

		if (document.getElementById('ovr_' + itemNumber))
		{
			overridePrice = Number(formatAsMoney(document.getElementById('ovr_' + itemNumber).value));
			orgOverridePrice = Number(formatAsMoney(document.getElementById('ovr_' + itemNumber).getAttribute('data-orgval')));
		}

		if (overridePrice > 0 && ltd_menu_item_value > 0)
		{
			overridePrice = Number(formatAsMoney((overridePrice * 1) + (ltd_menu_item_value * 1)));
		}

		let markupPrice = null;

		// if current markup then update markup column
		$("#row_" + itemNumber + " > .markup-price").html();

		if (menuInfo.mid[itemNumber].pricing_type == "HALF")
		{
			if (current3ServMarkup == 0 || current3ServMarkup == null || current3ServMarkup == "")
			{
				markupPrice = basePrice;
				if (ltd_menu_item_value > 0)
				{
					markupPrice = Number((markupPrice * 1) + (ltd_menu_item_value * 1));
				}
			}
			else
			{
				markupPrice = formatAsMoney(basePrice + (Math.round((basePrice * current3ServMarkup) + 0.000000001) / 100));

				$("#row_" + itemNumber + " > .markup-price").html(markupPrice);

				if (ltd_menu_item_value > 0)
				{
					markupPrice = Number((markupPrice * 1) + (ltd_menu_item_value * 1));
				}

			}
		}
		else if (menuInfo.mid[itemNumber].pricing_type == "TWO")
		{
			if (current2ServMarkup == 0 || current2ServMarkup == null || current2ServMarkup == "")
			{
				markupPrice = basePrice;
				if (ltd_menu_item_value > 0)
				{
					markupPrice = Number((markupPrice * 1) + (ltd_menu_item_value * 1));
				}

			}
			else
			{
				markupPrice = formatAsMoney(basePrice + (Math.round((basePrice * current2ServMarkup) + 0.000000001) / 100));

				$("#row_" + itemNumber + " > .markup-price").html(markupPrice);

				if (ltd_menu_item_value > 0)
				{
					markupPrice = Number((markupPrice * 1) + (ltd_menu_item_value * 1));
				}

			}
		}
		else if (menuInfo.mid[itemNumber].pricing_type == "FOUR")
		{
			if (current4ServMarkup == 0 || current4ServMarkup == null || current4ServMarkup == "")
			{
				markupPrice = basePrice;
				if (ltd_menu_item_value > 0)
				{
					markupPrice = Number((markupPrice * 1) + (ltd_menu_item_value * 1));
				}

			}
			else
			{
				markupPrice = formatAsMoney(basePrice + (Math.round((basePrice * current4ServMarkup) + 0.000000001) / 100));

				$("#row_" + itemNumber + " > .markup-price").html(markupPrice);

				if (ltd_menu_item_value > 0)
				{
					markupPrice = Number((markupPrice * 1) + (ltd_menu_item_value * 1));
				}

			}
		}
		else // menuInfo.mid[itemNumber].pricing_type == "FULL"
		{
			if (current6ServMarkup == 0 || current6ServMarkup == null || current6ServMarkup == "")
			{
				markupPrice = basePrice;

				if (ltd_menu_item_value > 0)
				{
					markupPrice = Number((markupPrice * 1) + (ltd_menu_item_value * 1));
				}

			}
			else
			{
				markupPrice = formatAsMoney(basePrice + (Math.round((basePrice * current6ServMarkup) + 0.00000001) / 100));

				$("#row_" + itemNumber + " > .markup-price").html(markupPrice);

				if (ltd_menu_item_value > 0)
				{
					markupPrice = Number((markupPrice * 1) + (ltd_menu_item_value * 1));
				}
			}
		}

		markupPrice = formatAsMoney(markupPrice) * 1;

		let ORdelta = overridePrice - currentPrice;
		let MUdelta = markupPrice - currentPrice;

		let newPriceByMarkUp = false;
		if (MUdelta != 0 && ORdelta != 0)
		{
			newPriceByMarkUp = true;
		}

		if (isEnabled_Markup && newPriceByMarkUp > 0 && overridePrice == 0)
		{
			$("#row_" + itemNumber + " > .preview-price").text(formatAsMoney(markupPrice));
			is_unsaved = true;
			//dd_console_log("Unsaved 9");
		}
		else if (overridePrice > 0 && ORdelta != 0)
		{
			$("#row_" + itemNumber + " > .preview-price").text(formatAsMoney(overridePrice));
			is_unsaved = true;
			//dd_console_log("Unsaved 10");
		}
		else if (isEnabled_Markup && overridePrice > 0 && newPriceByMarkUp)
		{
			$("#row_" + itemNumber + " > .preview-price").text(formatAsMoney(markupPrice));
			is_unsaved = true;
			//dd_console_log("Unsaved 11");
		}
		else
		{
			$("#row_" + itemNumber + " > .preview-price").text('');
		}

		let visFlag = document.getElementById("vis_" + itemNumber);
		if (visFlag)
		{
			orgState = visFlag.getAttribute('data-orgval');
			if ((visFlag.checked && orgState != 'CHECKED') || (!visFlag.checked && orgState == 'CHECKED'))
			{
				is_unsaved = true;
				//dd_console_log("Unsaved 12");
			}
		}
	}

	// EFL Items

	let EFL_itemRows = document.getElementById('EFLitemsTbl').rows;

	for (let i = 0; i < EFL_itemRows.length; i++)
	{
		if (EFL_itemRows[i].id.indexOf('row_') == -1 && EFL_itemRows[i].id != 'addonEditorRow')
		{
			continue;
		}

		let itemNumber = null;
		if (EFL_itemRows[i].id == 'addonEditorRow')
		{
			itemNumber = EFL_itemRows[i].getAttribute('item_id');
			if (!itemNumber)
			{
				continue;
			}
		}
		else
		{
			itemNumber = EFL_itemRows[i].id.substr(4);
		}

		let basePrice = Number(menuInfo.mid[itemNumber].base_price);
		let currentPrice = Number(menuInfo.mid[itemNumber].price);

		if (!$('#vis_' + itemNumber).is(':checked'))
		{
			let entreeId = $('#vis_' + itemNumber).data('entree_id');

			if (!uniqueEFLEntreeId.includes(entreeId))
			{
				numberOfCustomerFacingEFLItems++;
				uniqueEFLEntreeId.push(entreeId);
			}

		}

		if (menuInfo.mid[itemNumber].is_chef_touched == '1')
		{
			sidesCount++;
		}

		if (itemNumber == 13944)
		{
			let x = 1;
		}

		let overridePrice = Number(0);
		let orgOverridePrice = Number(0);

		if (document.getElementById('ovr_' + itemNumber))
		{
			overridePrice = Number(formatAsMoney(document.getElementById('ovr_' + itemNumber).value));
			orgOverridePrice = Number(formatAsMoney(document.getElementById('ovr_' + itemNumber).getAttribute('data-orgval')));
		}

		let markupPrice = null;

		// if current markup then update markup column
		$("#row_" + itemNumber + " > .markup-price").html();

		if (menuInfo.mid[itemNumber].pricing_type == "HALF")
		{
			if (current3ServMarkup == 0 || current3ServMarkup == null || current3ServMarkup == "")
			{
				markupPrice = basePrice;
			}
			else
			{
				markupPrice = formatAsMoney(basePrice + (Math.round((basePrice * current3ServMarkup) + 0.00000001) / 100));
				$("#row_" + itemNumber + " > .markup-price").html(markupPrice);
			}
		}
		else if (menuInfo.mid[itemNumber].pricing_type == "TWO")
		{
			if (current2ServMarkup == 0 || current2ServMarkup == null || current2ServMarkup == "")
			{
				markupPrice = basePrice;
			}
			else
			{
				markupPrice = formatAsMoney(basePrice + (Math.round((basePrice * current2ServMarkup) + 0.00000001) / 100));
				$("#row_" + itemNumber + " > .markup-price").html(markupPrice);
			}
		}
		else if (menuInfo.mid[itemNumber].pricing_type == "FOUR")
		{
			if (current4ServMarkup == 0 || current4ServMarkup == null || current4ServMarkup == "")
			{
				markupPrice = basePrice;
			}
			else // menuInfo.mid[itemNumber].pricing_type == "FULL"
			{
				markupPrice = formatAsMoney(basePrice + (Math.round((basePrice * current4ServMarkup) + 0.00000001) / 100));
				$("#row_" + itemNumber + " > .markup-price").html(markupPrice);
			}
		}
		else // servingSize == "FULL"
		{
			if (current6ServMarkup == 0 || current6ServMarkup == null || current6ServMarkup == "")
			{
				markupPrice = basePrice;
			}
			else
			{
				markupPrice = formatAsMoney(basePrice + (Math.round((basePrice * current6ServMarkup) + 0.00000001) / 100));
				$("#row_" + itemNumber + " > .markup-price").html(markupPrice);
			}
		}

		let markdown_change = false;
		// At this point we know the new price... apply the markdown if it exists
		if (isEnabled_Markup && $('#mkdn_' + itemNumber)[0])
		{
			let markdown = $('#mkdn_' + itemNumber);
			let markdown_value = formatAsMoney(markdown.data('markdown_value'));
			let original_markdown = markdown.data('org_val');
			let markedDownPrice = 0;
			if (overridePrice > 0)
			{
				overridePrice -= formatAsMoney(overridePrice * (markdown_value / 100));
				markedDownPrice = overridePrice;
			}
			else if (markupPrice > 0)
			{
				let discount = Number(formatAsMoney(markupPrice * (markdown_value / 100)));
				markupPrice = formatAsMoney((markupPrice * 100) / 100);
				markupPrice = Math.round((markupPrice - discount.valueOf()) * 100) / 100;
				markedDownPrice = markupPrice;
			}

			if (original_markdown != markdown_value)
			{
				markdown_change = true;
			}

		}

		let ORdelta = overridePrice - currentPrice;
		let MUdelta = markupPrice - currentPrice;

		if (Math.abs(ORdelta) < .005)
		{
			ORdelta = 0;
		}
		if (Math.abs(MUdelta) < .005)
		{
			MUdelta = 0;
		}

		let newPriceByMarkUp = false;
		if (MUdelta != 0 && ORdelta != 0)
		{
			newPriceByMarkUp = true;
		}

		if (overridePrice > 0 && ORdelta != 0)
		{
			$("#row_" + itemNumber + " > .preview-price").text(formatAsMoney(overridePrice));
			is_unsaved = true;
			//dd_console_log("Unsaved 13");
		}
		else if (isEnabled_Markup && newPriceByMarkUp)
		{
			$("#row_" + itemNumber + " > .preview-price").text(formatAsMoney(markupPrice));
			is_unsaved = true;
			//dd_console_log("Unsaved 14");
		}
		else if (markdown_change)
		{
			$("#row_" + itemNumber + " > .preview-price").text(formatAsMoney(markedDownPrice));
			is_unsaved = true;
		}
		else
		{
			$("#row_" + itemNumber + " > .preview-price").text('');
		}

		let visFlag = document.getElementById("vis_" + itemNumber);
		if (visFlag)
		{
			let orgState = visFlag.getAttribute('data-orgval');
			if ((visFlag.checked && orgState != 'CHECKED') || (!visFlag.checked && orgState == 'CHECKED'))
			{
				is_unsaved = true;
				//dd_console_log("Unsaved 15");
			}
		}

		let picFlag = document.getElementById("pic_" + itemNumber);
		if (picFlag)
		{
			let orgState = picFlag.getAttribute('data-orgval');
			if ((picFlag.checked && orgState != 'CHECKED') || (!picFlag.checked && orgState == 'CHECKED'))
			{
				is_unsaved = true;
				//dd_console_log("Unsaved 16");
			}
		}

	}

	// Chef Touched Selections
	let ctsItemRows = document.getElementById('ctsItemsTbl').rows;
	for (let i = 0; i < ctsItemRows.length; i++)
	{
		if (ctsItemRows[i].id.indexOf('row_') == -1)
		{
			continue;
		}

		let itemNumber = null;
		itemNumber = ctsItemRows[i].id.substr(4);

		let basePrice = Number(menuInfo.mid[itemNumber].base_price);
		let currentPrice = Number(menuInfo.mid[itemNumber].price);

		if (menuInfo.mid[itemNumber].is_chef_touched == '1')
		{
			sidesCount++;
		}

		let overridePrice = Number(0);
		let orgOverridePrice = Number(0);

		if (document.getElementById('ovr_' + itemNumber))
		{
			overridePrice = Number(formatAsMoney(document.getElementById('ovr_' + itemNumber).value));
			orgOverridePrice = Number(formatAsMoney(document.getElementById('ovr_' + itemNumber).getAttribute('data-orgval')));
		}

		let markupPrice = null;

		if ($('#vis_' + itemNumber).is(':checked'))
		{
			numberOfCustomerFacingCTSItems++;
		}

		if (currentSidesMarkup == 0 || currentSidesMarkup == null || currentSidesMarkup == "")
		{
			$("#row_" + itemNumber + " > .markup-price").html();
			markupPrice = basePrice;
		}
		else
		{
			markupPrice = formatAsMoney(basePrice + (Math.round((basePrice * currentSidesMarkup) + 0.00000001) / 100));
			$("#row_" + itemNumber + " > .markup-price").html(markupPrice);
		}

		markupPrice = formatAsMoney(markupPrice) * 1;

		let ORdelta = overridePrice - currentPrice;
		let MUdelta = markupPrice - currentPrice;

		let newPriceByMarkUp = false;
		if (MUdelta != 0 && ORdelta != 0)
		{
			newPriceByMarkUp = true;
		}

		if (overridePrice > 0 && ORdelta != 0)
		{
			$("#row_" + itemNumber + " > .preview-price").text(formatAsMoney(overridePrice));
			is_unsaved = true;
			//dd_console_log("Unsaved 17");
		}
		else if (isEnabled_Markup && newPriceByMarkUp)
		{
			$("#row_" + itemNumber + " > .preview-price").text(formatAsMoney(markupPrice));
			is_unsaved = true;
			//dd_console_log("Unsaved 18");
		}
		else
		{
			$("#row_" + itemNumber + " > .preview-price").text('');
		}

		let visFlag = document.getElementById("vis_" + itemNumber);
		if (visFlag)
		{
			let orgState = visFlag.getAttribute('data-orgval');
			if ((visFlag.checked && orgState != 'CHECKED') || (!visFlag.checked && orgState == 'CHECKED'))
			{
				is_unsaved = true;
				//dd_console_log("Unsaved 19");
			}
		}

		let hidFlag = document.getElementById("hid_" + itemNumber);
		if (hidFlag)
		{
			let orgState = hidFlag.getAttribute('data-orgval');
			if ((hidFlag.checked && orgState != 'CHECKED') || (!hidFlag.checked && orgState == 'CHECKED'))
			{
				is_unsaved = true;
				//dd_console_log("Unsaved 20");
			}
		}

		let formFlag = document.getElementById("form_" + itemNumber);
		if (formFlag)
		{
			let orgState = formFlag.getAttribute('data-orgval');
			if ((formFlag.checked && orgState != 'CHECKED') || (!formFlag.checked && orgState == 'CHECKED'))
			{
				is_unsaved = true;
				//dd_console_log("Unsaved 21");
			}
		}

	}

	if (gNumOptionalsAdded != 0)
	{
		is_unsaved = true;
		//dd_console_log("Unsaved 22");
	}

	if (is_unsaved)
	{
		$('#saved_message, #saved_message_2').showFlex();
	}
	else
	{
		$('#saved_message, #saved_message_2').hideFlex();
	}
}

function confirm_and_check_form()
{
	let form = $("#menu_editor_form")[0];

	clearErrors();

	let validated = _check_form(form);

	let current2ServMarkup = ((!$('#markup_2_serving').val()) ? null : $('#markup_2_serving').val());
	let current3ServMarkup = ((!$('#markup_3_serving').val()) ? null : $('#markup_3_serving').val());
	let current4ServMarkup = ((!$('#markup_4_serving').val()) ? null : $('#markup_4_serving').val());
	let current6ServMarkup = ((!$('#markup_6_serving').val()) ? null : $('#markup_6_serving').val());
	let currentSidesMarkup = ((!$('#markup_sides').val()) ? null : $('#markup_sides').val());
	let warnOffOfEmptyMarkups = false;

	let currentVolumeReward = "0";

	let message = "";

	if (validated)
	{
		if (current2ServMarkup < 0 || current4ServMarkup < 0 || current3ServMarkup < 0 || current6ServMarkup < 0 || currentSidesMarkup < 0)
		{
			message = "Negative per cent markups are not currently permitted.<br />";
		}

		if (current2ServMarkup > 70 || current4ServMarkup > 70 || current3ServMarkup > 70 || current6ServMarkup > 70 || currentSidesMarkup > 70)
		{
			message += "Markups greater than 70% are not currently permitted.<br />";
		}

		if (currentVolumeReward < 0)
		{
			message += "Negative volume discounts are not permitted.<br />";
		}

		if (currentVolumeReward > 75)
		{
			message += "Volume Dscounts greater than $75.00 are not currently permitted.<br />";
		}

		if (currentVolumeReward == "")
		{
			message += "Please supply a valid volume discount. Use zero if you want no volume discount applied.<br />";
		}

		if (($('#markup_2_serving').length != 0 && current2ServMarkup == null) || ($('#markup_4_serving').length != 0 && current4ServMarkup == null) || ($('#markup_3_serving').length != 0 && current3ServMarkup == null) || ($('#markup_6_serving').length != 0 && current6ServMarkup == null) || ($('#markup_sides').length != 0 && currentSidesMarkup == null))
		{
			warnOffOfEmptyMarkups = true;
		}

		let assembly_fee = ((!$('#assembly_fee').val()) ? 0 : $('#assembly_fee').val());
		if (assembly_fee < 0)
		{
			message += "The Assembly Fee must be greater than or equal to 0.<br />";
		}

		let delivery_assembly_fee = ((!$('#delivery_assembly_fee').val()) ? 0 : $('#delivery_assembly_fee').val())
		if (delivery_assembly_fee < 0)
		{
			message += "The Delivery Assembly Fee must be greater than or equal to 0.<br />";
		}

		let itemRows = document.getElementById('itemsTbl').rows;

		let encounteredBasePriceIssue = false;
		let problemItemList = "";

		for (let i = 0; i < itemRows.length; i++)
		{
			if (itemRows[i].id.indexOf('row_') == -1)
			{
				continue;
			}

			let itemNumber = itemRows[i].id.substr(4);
			let basePrice = Number(menuInfo.mid[itemNumber].base_price);
			let overridePrice = "";

			if (document.getElementById('ovr_' + itemNumber))
			{
				overridePrice = Number(document.getElementById('ovr_' + itemNumber).value);
			}

			if (overridePrice && overridePrice != "")
			{
				if (noOverrideUnderBase && overridePrice < basePrice)
				{
					encounteredBasePriceIssue = true;

					problemItemList += menuInfo.mid[itemNumber].menu_item_name + "<br />";

				}
				if (overridePrice > 999)
				{
					message += "The Override Price must be less than $999.00<br />";
					break;
				}
			}

		}

		let EFL_itemRows = document.getElementById('EFLitemsTbl').rows;

		for (let i = 0; i < EFL_itemRows.length; i++)
		{
			if (EFL_itemRows[i].id.indexOf('row_') == -1)
			{
				continue;
			}

			let itemNumber = EFL_itemRows[i].id.substr(4);
			let basePrice = Number(menuInfo.mid[itemNumber].base_price);

			let markdownObj = $("#mkdn_" + itemNumber)[0];
			let markDownPostObj = $("#upd_mkdn_" + itemNumber)[0];

			if (markdownObj)
			{
				let value = $(markdownObj).data("markdown_id") + "|" + $(markdownObj).data("markdown_value");

				if (!markDownPostObj)
				{
					let el = document.createElement('input');
					el.setAttribute("type", "hidden");
					el.setAttribute("value", value);
					let idStr = "upd_mkdn_" + itemNumber;
					el.setAttribute("id", idStr);
					el.setAttribute("name", idStr);
					markdownObj.appendChild(el);
				}
				else
				{
					markDownPostObj.val(value);
				}

			}

			let overridePrice = "";

			if (document.getElementById('ovr_' + itemNumber))
			{
				overridePrice = Number(document.getElementById('ovr_' + itemNumber).value);
			}

			if (overridePrice && overridePrice != "")
			{
				if (noOverrideUnderBase && overridePrice < basePrice)
				{
					encounteredBasePriceIssue = true;
					problemItemList += menuInfo.mid[itemNumber].menu_item_name + "<br />";

				}
				if (overridePrice > 999)
				{
					message += "The Override Price must be less than $999.00<br />";
					break;
				}
			}

		}

		let ctsItemRows = document.getElementById('ctsItemsTbl').rows;

		for (let i = 0; i < ctsItemRows.length; i++)
		{
			if (ctsItemRows[i].id.indexOf('row_') == -1)
			{
				continue;
			}

			let itemNumber = ctsItemRows[i].id.substr(4);
			let basePrice = Number(menuInfo.mid[itemNumber].base_price);
			let overridePrice = "";

			if (document.getElementById('ovr_' + itemNumber))
			{
				overridePrice = Number(document.getElementById('ovr_' + itemNumber).value);
			}

			if (overridePrice && overridePrice != "")
			{
				if (noOverrideUnderBase && overridePrice < basePrice)
				{
					encounteredBasePriceIssue = true;
					problemItemList += menuInfo.mid[itemNumber].menu_item_name + "<br />";
				}

				if (overridePrice > 999)
				{
					message += "The Override Price must be less than $999.00<br />";
					break;
				}
			}
		}

		if (encounteredBasePriceIssue)
		{
			message += "The Override Price must be greater than the base price for:<br />";
			message += problemItemList;

		}

		if (message != "")
		{
			validated = false;
			displayValidationErrorMsg(message);
		}
	}

	if (validated)
	{
		let finalizeMessage = "Are you sure want to submit these changes to your menu? If the menu is active the changes will be immediately available to your customers.";

		if (warnOffOfEmptyMarkups)
		{
			finalizeMessage += "<br /><br /><span class='text-red font-weight-bold'>One or more of the Percentage MarkUp values is empty or 0. Please review and correct if this was unintentional.</span><br />";
		}

		dd_message({
			title: 'Attention',
			message: finalizeMessage,
			modal: true,
			confirm: function () {
				document.getElementById('action').value = "finalize";
				$("#menu_editor_form").submit();
				return false;
			},
			cancel: function () {
				return false;
			}
		});

		/*
		if (confirm(finalizeMessage))
		{
			document.getElementById('action').value = "finalize";
			return true;
		}
		else
		{
			return false;
		}
		*/
	}

	return false;
}

function getMarkUpPrice(servingsSize, basePrice)
{

	let markupPrice = Number(basePrice);
	basePrice = Number(basePrice);

	let current2ServMarkup = ((!$('#markup_2_serving').val()) ? null : $('#markup_2_serving').val());
	let current3ServMarkup = ((!$('#markup_3_serving').val()) ? null : $('#markup_3_serving').val());
	let current4ServMarkup = ((!$('#markup_4_serving').val()) ? null : $('#markup_4_serving').val());
	let current6ServMarkup = ((!$('#markup_6_serving').val()) ? null : $('#markup_6_serving').val());

	// if current markup then update markup column
	if (servingsSize == "Sm (2)")
	{
		if (current2ServMarkup == 0 || current2ServMarkup == null || current2ServMarkup == "")
		{
			markupPrice = basePrice.valueOf();
		}
		else
		{
			markupPrice = formatAsMoney(basePrice.valueOf() + (Math.round(basePrice.valueOf() * current2ServMarkup) / 100));
		}
	}
	else if (servingsSize == "Md (4)")
	{
		if (current4ServMarkup == 0 || current4ServMarkup == null || current4ServMarkup == "")
		{
			markupPrice = basePrice.valueOf();
		}
		else
		{
			markupPrice = formatAsMoney(basePrice.valueOf() + (Math.round(basePrice.valueOf() * current4ServMarkup) / 100));
		}
	}
	else if (servingsSize == "Md (3)")
	{
		if (current3ServMarkup == 0 || current3ServMarkup == null || current3ServMarkup == "")
		{
			markupPrice = basePrice.valueOf();
		}
		else
		{
			markupPrice = formatAsMoney(basePrice.valueOf() + (Math.round(basePrice.valueOf() * current3ServMarkup) / 100));
		}
	}
	else
	{
		if (current6ServMarkup == 0 || current6ServMarkup == null || current6ServMarkup == "")
		{
			markupPrice = basePrice.valueOf();
		}
		else
		{
			markupPrice = formatAsMoney(basePrice.valueOf() + (Math.round(basePrice.valueOf() * current6ServMarkup) / 100));
		}
	}

	return markupPrice;
}

function addUnsavedEFLItem(item_id, name, size, base_price, thisItemCount, countItemsInRecipe)
{
	let insertCellNum = 0;

	if (item_id == 0)
	{
		return;
	} // do nothing

	var tbl = document.getElementById('EFLitemsTbl');

	var row = tbl.insertRow(-1);
	row.setAttribute("id", "row_" + item_id);

	// visibility cell
	var cellLeft = row.insertCell(0);

	var visBox = document.createElement("input");

	visBox.setAttribute("type", "checkbox");
	var idStr = "vis_" + item_id;
	visBox.setAttribute("id", idStr);
	visBox.setAttribute("name", idStr);
	visBox.setAttribute("class", "align-middle");
	visBox.setAttribute("data-orgval", "CHECKED");
	visBox.setAttribute("data-menu_item_id", item_id);
	visBox.setAttribute("checked", "checked");
	cellLeft.appendChild(visBox);

	// picksheet cell
	var cellLeft2 = row.insertCell(++insertCellNum);

	var pickBox = document.createElement("input");

	pickBox.setAttribute("type", "checkbox");
	var idStr = "pic_" + item_id;
	pickBox.setAttribute("id", idStr);
	pickBox.setAttribute("name", idStr);
	pickBox.setAttribute("class", "align-middle");
	pickBox.setAttribute("data-orgval", "");
	pickBox.setAttribute("step", "any");

	cellLeft2.appendChild(pickBox);

	// name cell
	var cellName = row.insertCell(++insertCellNum);
	cellName.setAttribute("class", "align-middle text-left");

	var nameNode = document.createTextNode(name);
	cellName.appendChild(nameNode);

	// size cell
	var cellSize = row.insertCell(++insertCellNum);
	cellSize.setAttribute("class", "align-middle");

	var el = document.createTextNode(size);
	cellSize.appendChild(el);

	if (isEnabled_Markup)
	{
		// basePrice cell
		var cellbase = row.insertCell(++insertCellNum);
		cellbase.setAttribute("class", "align-middle");

		var el = document.createTextNode(formatAsMoney(base_price));
		cellbase.appendChild(el);

		var markup_price = getMarkUpPrice(size, base_price);
	}

	// currentPrice cell
	var cellcurrent = row.insertCell(++insertCellNum);
	cellcurrent.setAttribute("class", "align-middle");

	if (isEnabled_Markup)
	{
		var el = document.createTextNode(markup_price);
	}
	else
	{
		var el = document.createTextNode(formatAsMoney(base_price));
	}
	cellcurrent.appendChild(el);

	if (isEnabled_Markup)
	{
		// markupPrice cell
		var cellmarkup = row.insertCell(++insertCellNum);
		cellmarkup.setAttribute("class", "align-middle markup-price");

		var el = document.createTextNode(markup_price);
		cellmarkup.appendChild(el);
	}

	// overridePrice cell
	var celloverride = row.insertCell(++insertCellNum);

	var el = document.createElement('input');
	el.setAttribute("type", "number");
	el.setAttribute("size", "3");
	el.setAttribute("step", "any");

	el.setAttribute("class", "form-control form-control-sm no-spin-button");

	el.setAttribute("maxlength", "6");
	el.setAttribute("data-orgval", "");

	var idStr = "ovr_" + item_id;
	el.setAttribute("id", idStr);
	el.setAttribute("name", idStr);
	if (!isEnabled_Markup)
	{
		el.setAttribute("value", formatAsMoney(base_price));
	}
	celloverride.appendChild(el);

	// markdown
	var cellmarkdown = row.insertCell(++insertCellNum);

	var el = document.createElement('button');
	var idStr = "add-mkdn_" + item_id;
	el.setAttribute("id", idStr);
	el.setAttribute("class", "btn btn-primary btn-sm");
	var buttonTextNode = document.createTextNode("Add");
	el.appendChild(buttonTextNode);

	cellmarkdown.appendChild(el);

	// previewPrice cell
	var cellpreview = row.insertCell(++insertCellNum);
	cellpreview.setAttribute("class", "align-middle preview-price");
}

function addUnsavedSideItem(item_id, name, size, base_price, category_label)
{
	let insertCellNum = 0;

	if (item_id == 0)
	{
		return;
	} // do nothing

	var tbl = document.getElementById('ctsItemsTbl');

	if ($('#temp_category').length == 0)
	{
		var row = tbl.insertRow(-1);
		row.setAttribute("id", "temp_category");

		var cellName = row.insertCell(0);
		cellName.setAttribute("class", "font-weight-bold py-3");
		cellName.setAttribute("colspan", "11");
		var nameNode = document.createTextNode('Newly Added Items');
		cellName.appendChild(nameNode);
	}

	var row = tbl.insertRow(-1);
	row.setAttribute("id", "row_" + item_id);

	// visibility cell
	var cellLeft = row.insertCell(insertCellNum++);

	var visBox = document.createElement("input");

	visBox.setAttribute("type", "checkbox");
	var idStr = "vis_" + item_id;
	visBox.setAttribute("id", idStr);
	visBox.setAttribute("name", idStr);
	visBox.setAttribute("class", "align-middle");
	visBox.setAttribute("data-orgval", "CHECKED");
	visBox.setAttribute("data-menu_item_id", item_id);
	visBox.setAttribute("checked", "checked");
	cellLeft.appendChild(visBox);

	// picksheet cell
	var cellLeft2 = row.insertCell(insertCellNum++);

	var pickBox = document.createElement("input");

	pickBox.setAttribute("type", "checkbox");
	var idStr = "form_" + item_id;
	pickBox.setAttribute("id", idStr);
	pickBox.setAttribute("name", idStr);
	pickBox.setAttribute("class", "align-middle");
	pickBox.setAttribute("data-orgval", "");
	pickBox.setAttribute("step", "any");

	cellLeft2.appendChild(pickBox);

	// hide everywhere cell
	var cellLeft3 = row.insertCell(insertCellNum++);

	var pickBox = document.createElement("input");

	pickBox.setAttribute("type", "checkbox");
	var idStr = "hid_" + item_id;
	pickBox.setAttribute("id", idStr);
	pickBox.setAttribute("name", idStr);
	pickBox.setAttribute("class", "align-middle");
	pickBox.setAttribute("data-orgval", "");
	pickBox.setAttribute("step", "any");

	cellLeft3.appendChild(pickBox);

	// name cell
	var cellName = row.insertCell(insertCellNum++);
	cellName.setAttribute("class", "align-middle text-left");
	var nameNode = document.createTextNode(name);
	cellName.appendChild(nameNode);

	// size cell
	var cellSize = row.insertCell(insertCellNum++);
	cellSize.setAttribute("class", "align-middle");
	var el = document.createTextNode(size);
	cellSize.appendChild(el);

	// basePrice cell
	var cellbase = row.insertCell(insertCellNum++);
	cellbase.setAttribute("class", "align-middle");
	var el = document.createTextNode(formatAsMoney(base_price));
	cellbase.appendChild(el);

	var markup_price = getMarkUpPrice(size, base_price);

	// currentPrice cell
	var cellcurrent = row.insertCell(insertCellNum++);
	cellcurrent.setAttribute("class", "align-middle");
	var el = document.createTextNode(markup_price);
	cellcurrent.appendChild(el);

	// markupPrice cell
	var cellmarkup = row.insertCell(insertCellNum++);
	cellmarkup.setAttribute("class", "align-middle markup-price");
	var el = document.createTextNode(markup_price);
	cellmarkup.appendChild(el);

	// overridePrice cell
	var celloverride = row.insertCell(insertCellNum++);
	var el = document.createElement('input');
	el.setAttribute("type", "number");
	el.setAttribute("size", "3");
	el.setAttribute("step", "any");

	el.setAttribute("class", "form-control form-control-sm no-spin-button");

	el.setAttribute("maxlength", "6");
	el.setAttribute("data-orgval", "");

	var idStr = "ovr_" + item_id;
	el.setAttribute("id", idStr);
	el.setAttribute("name", idStr);
	celloverride.appendChild(el);

	// previewPrice cell
	var cellpreview = row.insertCell(insertCellNum++);
	cellpreview.setAttribute("class", "align-middle preview-price");
	var el = document.createTextNode('');
	cellpreview.appendChild(el);

	// remianingInventory cell
	var cellremaining = row.insertCell(insertCellNum++);
	cellremaining.setAttribute("class", "align-middle remaining-price");
	var el = document.createTextNode('0');
	cellremaining.appendChild(el);
}

function giveTabFocus()
{
	const params = new Proxy(new URLSearchParams(window.location.search), {
		get: (searchParams, prop) => searchParams.get(prop)
	});
	let value = params.tabs; // "some_value"
	if (typeof value != 'undefined' && value != null)
	{
		value = value.substring(value.indexOf('menu.') + 5);
		$(document).find("[data-nav='" + value + "']").click();
	}

}

$(function () {

	$(document).ready(function () {
		calculatePage()
		giveTabFocus();
	});

	$(document).on('change keyup', '#markup_6_serving, #markup_4_serving, #markup_3_serving, #markup_2_serving, #markup_sides, #assembly_fee, #delivery_assembly_fee, #volume_reward, [name="is_default_markup"], [id^=pic_], [id^=form_], [id^=ovr_], [id^=vis_], [id^=hid_]', function (e) {
		calculatePage();
	});

	$(document).on('change keyup', '[id^=vis_]', function (e) {

		let menu_item_id = $(this).data('menu_item_id');
		let checkedState = $(this).is(':checked');

		// Sides & Sweets
		if (menuInfo.mid[menu_item_id].is_chef_touched == '1')
		{
			if (checkedState && numberOfCustomerFacingCTSItems > 20)
			{
				$(this).prop({'checked': false});

				bootbox.alert('You can only display 20 Sides &amp; Sweets items on the customer facing menu.');
			}
			else
			{
				$('#hid_' + menu_item_id).prop({'checked': false});
			}
		}
		// EFL
		else if (menuInfo.mid[menu_item_id].is_store_special == '1')
		{
			if (!checkedState && numberOfCustomerFacingEFLItems > 10)
			{
				$(this).prop({'checked': true});

				bootbox.alert("You can only make 10 Fast Lane items visible at a time. Please hide another store special if you wish to make this item visible.");
			}
		}

	});

	$(document).on('change keyup', '[id^=form_]', function (e) {

		let menu_item_id = $(this).data('menu_item_id');

		$('#hid_' + menu_item_id).prop({'checked': false});

	});

	$(document).on('change keyup', '[id^=hid_]', function (e) {

		let menu_item_id = $(this).data('menu_item_id');

		$('#vis_' + menu_item_id).prop({'checked': false});
		$('#form_' + menu_item_id).prop({'checked': false});

	});

	$(document).on('keyup change', '#filter', function (e) {
		$.uiTableFilter($('#recipe_list'), this.value);
	});

	$(document).on('click', '#clear_filter', function (e) {

		$('#filter').val('').change();

		if (!Modernizr.input.placeholder)
		{
			// trick to restore placeholder on IE
			$('#filter').focus().blur();
		}

	});

	// handle markdowns
	$(document).on('click', '[id^=mkdn_]', function (e) {

		tempArr = this.id.split("_");
		let itemID = tempArr[1];

		let markdownVal = $(this).data('markdown_value');
		let markdownID = $(this).data('markdown_id');

		dd_message({
			title: 'Edit Markdown',
			div_id: "edit_mkdn",
			message: $("#markdown_editor").html(),
			height: 350,
			width: 400,
			resizable: true,
			noOk: true,
			open: function () {
				$("#edit_mkdn").find("#markdown_amount").val(markdownVal);

				let start_price = getPreviewItemPrice(itemID);

				let price = start_price;
				price -= (price * (markdownVal / 100));

				$("#edit_mkdn").find("#markdown_price").val(formatAsMoney(price));

				$("#edit_mkdn").find("#markdown_amount").on('keyup', function (e) {
					price = start_price;

					newDiscount = $(this).val();

					if (newDiscount < 0)
					{
						newDiscount = 0;
						$(this).val(newDiscount);
					}

					if (newDiscount > 20)
					{
						newDiscount = 20;
						$(this).val(newDiscount);
					}

					price -= (price * (newDiscount / 100));
					$("#edit_mkdn").find("#markdown_price").val(formatAsMoney(price));
				});

				$("#edit_mkdn").find("#markdown_price").on('keyup', function (e) {

					let targetPrice = $(this).val();
					let newPercentage = ((start_price - targetPrice) / start_price) * 100;

					$("#edit_mkdn").find("#markdown_amount").val(formatAsMoney(newPercentage));

				});

				$("#edit_mkdn").find("#current_price").html("$" + formatAsMoney(menuInfo.mid[itemID].price));

				$("#edit_mkdn").find("#base_price").html("$" + formatAsMoney(menuInfo.mid[itemID].base_price));

				$("#edit_mkdn").find("#item_title").html(menuInfo.mid[itemID].menu_item_name);

			},
			buttons: {
				Accept: function () {

					let start_price = getPreviewItemPrice(itemID);

					let newPercentage = $("#edit_mkdn").find("#markdown_amount").val();
					if (newPercentage > 20)
					{
						newPercentage = 20;
						price = (start_price * (newPercentage / 100));
						$("#edit_mkdn").find("#markdown_amount").val(formatAsMoney(newPercentage));
						$("#edit_mkdn").find("#markdown_price").val(formatAsMoney(start_price - price));

						dd_message({
							title: 'Error',
							message: "The percentage of discount is limited to 20%. The amounts have been adjusted. Please review and re-submit."
						});
						return;

					}
					else if (newPercentage < 0)
					{
						newPercentage = 0;
						$("#edit_mkdn").find("#markdown_amount").val(formatAsMoney(newPercentage));
						$("#edit_mkdn").find("#markdown_price").val(formatAsMoney(start_price));

						dd_message({
							title: 'Error',
							message: "The percentage of discount cannot be negative. The amounts have been adjusted. Please review and re-submit."
						});
						return;

					}

					$('#mkdn_' + itemID).html(formatAsMoney(newPercentage) + "%");
					$('#mkdn_' + itemID).data('markdown_value', newPercentage);
					calculatePage();

					$(this).remove();
				},
				Cancel: function () {
					$(this).remove();
				}
			}
		});

		return false;
	});

	// handle_add_new_markdown
	$(document).on('click', '[id^=add-mkdn_]', function (e) {

		tempArr = this.id.split("_");
		let itemID = tempArr[1];

		let markdownVal = $(this).data('markdown_value');
		let markdownID = $(this).data('markdown_id');

		dd_message({
			title: 'Add Markdown',
			div_id: "new_mkdn",
			message: $("#markdown_editor").html(),
			height: 350,
			width: 400,
			resizable: true,
			noOk: true,
			open: function () {
				$("#new_mkdn").find("#markdown_amount").val(markdownVal);

				let start_price = getPreviewItemPrice(itemID);

				$("#new_mkdn").find("#markdown_price").val(formatAsMoney(start_price));
				$("#new_mkdn").find("#markdown_amount").val(formatAsMoney(0));

				$("#new_mkdn").find("#markdown_amount").on('keyup', function (e) {
					price = start_price;
					newDiscount = $(this).val();

					if (newDiscount < 0)
					{
						newDiscount = 0;
						$(this).val(newDiscount);
					}

					if (newDiscount > 20)
					{
						newDiscount = 20;
						$(this).val(newDiscount);
					}

					price -= (price * (newDiscount / 100));
					$("#new_mkdn").find("#markdown_price").val(formatAsMoney(price));
				});

				$("#new_mkdn").find("#markdown_price").on('keyup', function (e) {

					let targetPrice = $(this).val();
					let newPercentage = ((start_price - targetPrice) / start_price) * 100;

					$("#new_mkdn").find("#markdown_amount").val(formatAsMoney(newPercentage));

				});

				$("#new_mkdn").find("#current_price").html("$" + formatAsMoney(menuInfo.mid[itemID].price));

				$("#new_mkdn").find("#base_price").html("$" + formatAsMoney(menuInfo.mid[itemID].base_price));

				$("#new_mkdn").find("#item_title").html(menuInfo.mid[itemID].menu_item_name);

			},
			buttons: {
				Accept: function () {
					let start_price = getPreviewItemPrice(itemID);

					let newPercentage = $("#new_mkdn").find("#markdown_amount").val();
					if (newPercentage > 20)
					{
						newPercentage = 20;
						price = (start_price * (newPercentage / 100));
						$("#new_mkdn").find("#markdown_amount").val(formatAsMoney(newPercentage));
						$("#new_mkdn").find("#markdown_price").val(formatAsMoney(start_price - price));

						dd_message({
							title: 'Error',
							message: "The percentage of discount is limited to 20%. The amounts have been adjusted. Please review and re-submit."
						});
						return;

					}
					else if (newPercentage < 0)
					{
						newPercentage = 0;
						$("#new_mkdn").find("#markdown_amount").val(formatAsMoney(newPercentage));
						$("#new_mkdn").find("#markdown_price").val(formatAsMoney(start_price));

						dd_message({
							title: 'Error',
							message: "The percentage of discount cannot be negative. The amounts have been adjusted. Please review and re-submit."
						});
						return;

					}

					let new_button = '<button class="btn btn-primary btn-sm" id="mkdn_' + itemID + '" data-org_val="0" data-markdown_id="new" data-markdown_value="' + newDiscount + '" >' + formatAsMoney(newDiscount) + '%</button>';
					$('#add-mkdn_' + itemID).replaceWith(new_button);
					calculatePage();

					$(this).remove();

				},
				Cancel: function () {
					$(this).remove();
				}
			}
		});

		return false;
	});

	$(document).on('click', '#add_past_menu_item:not(.disabled)', function (e) {

		displayModalWaitDialog('wait_for_adding_item_div', "Retrieving items. Please wait ...");

		showPopup({
			modal: true,
			title: 'Add EFL Item',
			noOk: true,
			closeOnEscape: false,
			height: 720,
			div_id: 'add_menu_item_popup_div',
			module: 'page=admin_menu_editor_add_item&menu_id=' + menuInfo.menu_id,
			open: function (event, ui) {
				$(this).parent().find('.ui-dialog-titlebar-close').hide();
				$("#wait_for_adding_item_div").remove();
			},
			buttons: {
				Cancel: function () {
					$(this).remove();
				},
				Okay: function () {

					let entreeList = {};

					$('#sel_list').find('[data-rmv_menu_item]').each(function () {

						let thisEntree = $(this).data('entree_id');
						entreeList[thisEntree] = thisEntree;

					});

					dd_message({
						title: '',
						message: 'This will immediately add the listed items to the menu. Are you sure?',
						noOk: true,
						height: 180,
						buttons: {
							'Add Items to Menu': function () {

								$.ajax({
									url: '/processor',
									type: 'POST',
									timeout: 20000,
									dataType: 'json',
									data: {
										processor: 'admin_menuEditor',
										store_id: STORE_DETAILS.id,
										op: 'add_menu_item',
										menu_id: menuInfo.menu_id,
										entree_ids: entreeList
									},
									success: function (json) {
										if (json.processor_success)
										{
											for (let entree_id in json.results)
											{
												let count = json.results[entree_id]['count'];
												let items = json.results[entree_id]['items'];

												let handledCount = 1;
												for (let id in items)
												{
													// update json array with latest data
													menuInfo = json.menuInfo;

													addUnsavedEFLItem(items[id].item_id, items[id].name, items[id].size, items[id].base_price, handledCount, count);
													handledCount++;

												}
												calculatePage();

												if (handledCount > 1)
												{
													$("#empty_menu_message").remove();
												}
											}
										}
										else
										{
											dd_message({
												title: 'Error',
												message: json.processor_message
											});
										}
									},
									error: function (objAJAXRequest, strError) {
										response = 'Unexpected error: ' + strError;
										dd_message({
											title: 'Error',
											message: response
										});

									}

								});

								$(this).remove();
								$('#add_menu_item_popup_div').remove();

							},
							'Cancel': function () {
								$(this).remove();
							}
						}
					});
				}

			},
			close: function () {
				bounce('/backoffice/menu_editor?tabs=menu.efl');
			}
		});

	});

	$(document).on('click', '[data-add_menu_item]', function (e) {
		// prevent multiple submissions
		if ($(this).hasClass('disabled'))
		{

		}
		else
		{
			$(this).addClass('disabled');

			let entree_id = $(this).data('entree_id');
			let recipe_id = $(this).data('recipe_id');

			let tr = $('[data-recipe_id_row="' + recipe_id + '"]').clone();

			tr.find("[data-rmv_menu_item=" + entree_id + "]").show();
			tr.find("[data-add_menu_item=" + entree_id + "]").remove();

			$("#selected_items tbody").append(tr);
		}

	});

	$(document).on('click', '[data-rmv_menu_item]', function (e) {

		let entree_id = $(this).data('entree_id');
		let recipe_id = $(this).data('recipe_id');

		$('#selected_items tbody [data-recipe_id_row="' + recipe_id + '"]').remove();
		let org_tr = $('#recipe_list tbody [data-recipe_id_row="' + recipe_id + '"]');
		org_tr.find('[data-add_menu_item="' + entree_id + '"]').removeClass('disabled');

	});

	$(document).on('click', '[data-info_menu_item]', function (e) {

		let entree_id = $(this).data('entree_id');
		let recipe_id = $(this).data('recipe_id');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_menuEditor',
				store_id: STORE_DETAILS.id,
				op: 'menu_item_info',
				menu_id: menuInfo.menu_id,
				entree_id: entree_id,
				recipe_id: recipe_id
			},
			success: function (json) {
				dd_message({
					modal: true,
					title: 'Item Information',
					height: 720,
					width: 720,
					message: json.data,
					open: function () {
						handle_tabbed_content();
					}
				});

			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});

			}

		});
	});

	//////---------Add past Sides & Sweets

	$(document).on('click', '#add_past_menu_item_sides', function (e) {

		displayModalWaitDialog('wait_for_adding_item_div', "Retrieving Sides & Sweets. Please wait ...");

		showPopup({
			modal: true,
			title: 'Add Sides & Sweets',
			noOk: true,
			closeOnEscape: false,
			height: 720,
			div_id: 'add_menu_item_popup_div',
			module: 'page=admin_menu_editor_add_sides_sweets&menu_id=' + menuInfo.menu_id,
			open: function (event, ui) {
				$(this).parent().find('.ui-dialog-titlebar-close').hide();
				$("#wait_for_adding_item_div").remove();
			},
			buttons: {
				Cancel: function () {
					$(this).remove();
				},
				Okay: function () {

					let entreeList = {};

					let errorMissingCategory = false;
					$('#sel_list').find('[data-rmv_menu_side]').each(function () {

						let thisEntree = $(this).data('entree_id');
						entreeList[thisEntree] = thisEntree;
						let categoryLabel = $("[data-recipe_category=" + thisEntree + "]:visible").find(":selected").val();
						if (typeof categoryLabel == 'undefined'){
							categoryLabel = $("[data-recipe_category=" + thisEntree + "]:visible").html();
						}

						if (typeof categoryLabel == 'undefined')
						{

							$("[data-recipe_category=" + thisEntree + "]").css("border-color", "red");
							errorMissingCategory = true;
						}else{
							entreeList[thisEntree] = categoryLabel;
						}
					});

					if (errorMissingCategory)
					{
						console.log('Missing');

						dd_message({
							title: 'Please select a category for all items',
							message: 'Please select a category for all items'
						});
					}
					else
					{
						dd_message({
							title: '',
							message: 'This will immediately add the listed Sides & Sweets to the menu. Are you sure?',
							noOk: true,
							height: 180,
							buttons: {
								'Add Sides & Sweets to Menu': function () {

									$.ajax({
										url: '/processor',
										type: 'POST',
										timeout: 20000,
										dataType: 'json',
										data: {
											processor: 'admin_menuEditor',
											store_id: STORE_DETAILS.id,
											op: 'add_menu_side',
											menu_id: menuInfo.menu_id,
											entree_ids: entreeList
										},
										success: function (json) {
											if (json.processor_success)
											{
												let handledCount = 0;
												for (let entree_id in json.results)
												{
													let count = json.results[entree_id]['count'];
													let items = json.results[entree_id]['items'];

													let handledCount = 1;
													for (let id in items)
													{
														// update json array with latest data
														menuInfo = json.menuInfo;

														addUnsavedSideItem(items[id].item_id, items[id].name, items[id].size, items[id].base_price, items[id].subcategory_label);
														handledCount++;

													}
												}

												calculatePage();

												if (handledCount > 1)
												{
													$("#empty_menu_message").remove();
												}

												$([
													document.documentElement,
													document.body
												]).animate({
													scrollTop: $("#temp_category").offset().top
												}, 2000);
											}
											else
											{
												dd_message({
													title: 'Error adding Sides & Sweets',
													message: json.processor_message
												});
											}
										},
										error: function (objAJAXRequest, strError) {
											response = 'Unexpected error: ' + strError;
											dd_message({
												title: 'Network Error adding Sides and Sweets',
												message: response
											});

										}

									});

									$(this).remove();
									$('#add_menu_item_popup_div').remove();

								},
								'Cancel': function () {
									$(this).remove();
								}
							}
						});
					}
				}

			},
			close: function () {
				bounce('/backoffice/menu_editor?tabs=menu.efl');
			}
		});

	});

	$(document).on('change', '[data-recipe_category]', function (e) {
		let categoryLabel = $(this).find(":selected").val();

		if (typeof categoryLabel == 'undefined')
		{
			$(this).css("border-color", "red");
		}
		else
		{
			$(this).css("border-color", "black");
		}
	});

	$(document).on('click', '[data-add_menu_side]', function (e) {
		// prevent multiple submissions
		if ($(this).hasClass('disabled'))
		{

		}
		else
		{
			$(this).addClass('disabled');

			let entree_id = $(this).data('entree_id');
			let recipe_id = $(this).data('recipe_id');

			let tr = $('[data-recipe_id_row="' + recipe_id + '"]').clone();

			tr.find("[data-rmv_menu_side=" + entree_id + "]").show();
			tr.find("[data-add_menu_side=" + entree_id + "]").remove();

			tr.find("[data-recipe_category=" + entree_id + "]").show();

			let oldCategory = tr.find("[data-old_category=" + entree_id + "]").html();

			tr.find("[data-old_category=" + entree_id + "]").remove();

			$("#selected_items tbody").append(tr);

			$("[data-recipe_category=" + entree_id + "]").val(oldCategory);
		}

	});

	$(document).on('click', '[data-rmv_menu_side]', function (e) {

		let entree_id = $(this).data('entree_id');
		let recipe_id = $(this).data('recipe_id');

		$('#selected_items tbody [data-recipe_id_row="' + recipe_id + '"]').remove();
		let org_tr = $('#recipe_list tbody [data-recipe_id_row="' + recipe_id + '"]');
		org_tr.find('[data-add_menu_side="' + entree_id + '"]').removeClass('disabled');

	});

	$(document).on('click', '[data-info_menu_side]', function (e) {

		let entree_id = $(this).data('entree_id');
		let recipe_id = $(this).data('recipe_id');

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'admin_menuEditor',
				store_id: STORE_DETAILS.id,
				op: 'menu_item_info',
				menu_id: menuInfo.menu_id,
				entree_id: entree_id,
				recipe_id: recipe_id
			},
			success: function (json) {
				dd_message({
					modal: true,
					title: 'Item Information',
					height: 720,
					width: 720,
					message: json.data,
					open: function () {
						handle_tabbed_content();
					}
				});

			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error: ' + strError;
				dd_message({
					title: 'Error',
					message: response
				});

			}

		});
	});
});