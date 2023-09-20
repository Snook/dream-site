function generate_signature_init()
{
	$(".telephone").mask("999-999-9999");

	$('[id^="sig"]').unbind("keyup change").bind("keyup change", function(e) {
		generate_html_signature();
	});

	generate_html_signature();
}

function download_signature()
{
	var gen_signature = generate_html_signature();

	//signature = '<html><head></head><body><div>&nbsp;</div>' + gen_signature + '<div>&nbsp;</div></body></html>';
	signature = '<div>&nbsp;</div>' + gen_signature + '<div>&nbsp;</div>';

	create_and_submit_form({input:{signature: signature}});
}

function generate_html_signature()
{
	var element = {};
	$('[id^="sig-"]').each(function() {
		var id = this.id.split("-")[1];

		element[id] = $(this).val();
	});

	var dd_link = PATH.https_server;
	if ($.isNumeric(element.dd_link) && element.dd_link != 0)
	{
		dd_link += '/location/id=' + element.dd_link;
	}

	var sig_string = '<table width="400" cellpadding="0" cellspacing="0" border="0" style="width:300.0pt;border:solid #563518 2.25pt;border-bottom:solid #563518 1.0pt;border-color:#563518;border-style:solid;border-width:3px 3px 0px 3px;"><tr><td width="144" style="width:1.5in;border:none;padding:7.5pt 7.5pt 7.5pt 7.5pt;text-align:center;vertical-align:top;"><div><a href="' + PATH.https_server + '"><img src="' + PATH.https_server + '/web_resources/campaigns/sig_files/olive_box_white_logo.gif" style="border:0px;width:122px;height:78px;" alt="Dream Dinners" /></a></div><div><a href="' + dd_link + '" style="color:#563518;text-decoration:none;">dreamdinners.com</a></div><div>&nbsp;</div><div>';

	$('[id^="sig_link-"]').each(function() {
		link = $(this).val();
		img = $(this).attr('data-img');
		title = $(this).attr('data-title');

		if (link.length > 0)
		{
			sig_string += '<a href="' + link + '"><img src="' + PATH.https_server + '/web_resources/campaigns/sig_files/' + img + '" style="border:0px;width:16px;height:16px;" alt="' + title + '" /></a>&nbsp;';
		}
	});

	sig_string += '</div></td><td width="256" style="width:192.0pt;border:none;padding:7.5pt 7.5pt 7.5pt 7.5pt;vertical-align:top;color:#563518;font-size:9pt;font-family:arial;"><div style="font-weight:bold;color:#00583f;font-size:11pt;">' + element.first_last + '</div><div style="font-weight:bold;font-size:10pt;">' + element.title + '</div><div><div>&nbsp;</div>';

	if (element.telephone.length > 0)
	{
		sig_string += '<div>P. ' + element.telephone + '</div>';
	}

	if (element.cellphone.length > 0)
	{
		sig_string += '<div>C. ' + element.cellphone + '</div>';
	}

	if (element.faxnumber.length > 0)
	{
		sig_string += '<div>F. ' + element.faxnumber + '</div>';
	}

	if (element.email.length > 0)
	{
		sig_string += '<div><a href="mailto:' + element.email + '">' + element.email + '</a></div>';
	}

	if (element.telephone.length + element.cellphone.length + element.faxnumber.length + element.email.length > 0)
	{
		sig_string += '<div>&nbsp;</div>';
	}

	if (element.addressline2.length > 0)
	{
		element.addressline = element.addressline1 + ', ' + element.addressline2;
	}
	else
	{
		element.addressline = element.addressline1;
	}

	sig_string += '<div>' + element.addressline + '</div>';

	sig_string += '<div>' + element.city + ', ' + element.state + ' ' + element.zipcode + '</div>';

	sig_string += '</div></td></tr><tr><td colspan="2" style="background-color:#563518;text-align:right;padding:0px;"><img src="' + PATH.https_server + '/web_resources/campaigns/sig_files/brown_footer_make_time.gif" style="border:0px;width:376;height:21px;" alt="Make Time. Make Dinner. Make Memories." /></td></tr></table>';

	$('#sig_preview').html(sig_string);

	return sig_string;
}