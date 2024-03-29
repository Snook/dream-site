var g_lastSearch = '';

function handle_browser_location()
{
	// only attempt auto search if zip or state not being queried by url
	if (!getQueryVariable('state') && !getQueryVariable('zip') && $('#zipsearch_zipcode_only').length)
	{
		address_location(function (status, results) {

			if (status == 'ok')
			{
				latitude = results[0].geometry.location.lat();
				longitude = results[0].geometry.location.lng();

				addressArray = address_components(results);
				szZip = addressArray['postal_code'].long_name;

				$('#zipsearch_zipcode_only').val(addressArray['postal_code'].long_name);

				/*
				if (typeof addressArray['street_number'] != 'undefined')
				{
					$('#zipsearch_address').val(addressArray['street_number'].long_name + ' ' + addressArray['route'].short_name);
				}

				$('#zipsearch_zipcode').val(addressArray['postal_code'].long_name);
				$('#zipsearch_city').val(addressArray['locality'].long_name);
				$('#zipsearch_state_id').val(addressArray['administrative_area_level_1'].short_name);
				*/

				retrieve_stores_for_lat_long({
					latitude: latitude,
					longitude: longitude,
					zip: szZip
				}, false);
			}
			else if (status == 'error' || status == 'no_result')
			{
				modal_message({message: results});
			}
			else
			{
				dd_console_log(results);
			}

		});
	}
}

function address_components(results)
{
	addressArray = [];

	$.each(results[0].address_components, function () {

		addressArray[this.types[0]] = this;

	});

	return addressArray;
}

function handle_address_search()
{
	if (!$('#zipsearch_zipcode').getVal() && (!$('#zipsearch_city').getVal() && !$('#zipsearch_state_id').getVal()))
	{
		modal_message({message: 'Zip code or City & State required'});
		return false;
	}

	if (!$('#zipsearch_zipcode').getVal() && (($('#zipsearch_city').getVal() && !$('#zipsearch_state_id').getVal()) || (!$('#zipsearch_city').getVal() && $('#zipsearch_state_id').getVal())))
	{
		modal_message({message: 'City & State required'});
		return false;
	}

	address_search = '';

	if ($('#zipsearch_address').getVal())
	{
		address_search += $('#zipsearch_address').getVal();
	}

	if ($('#zipsearch_city').getVal())
	{
		address_search += ', ' + $('#zipsearch_city').getVal();
	}

	if ($('#zipsearch_state_id').getVal())
	{
		address_search += ' ' + $('#zipsearch_state_id').getVal();
	}

	if ($('#zipsearch_zipcode').getVal())
	{
		address_search += ' ' + $('#zipsearch_zipcode').getVal();
	}

	address_location({address: address_search}, function (status, results) {

		if (status == 'ok')
		{
			latitude = results[0].geometry.location.lat();
			longitude = results[0].geometry.location.lng();

			addressArray = address_components(results);

			if (typeof addressArray['postal_code'] === 'undefined')
			{

				address_location({latlong: latitude + ',' + longitude}, function (status, results) {

					if (status == 'ok')
					{
						latitude = results[0].geometry.location.lat();
						longitude = results[0].geometry.location.lng();

						addressArray = address_components(results);

						szZip = '';
						if (typeof addressArray['postal_code'] !== 'undefined')
						{
							szZip = addressArray['postal_code'].long_name;
						}

						retrieve_stores_for_lat_long({
							latitude: latitude,
							longitude: longitude,
							zip: szZip
						}, true);
					}
					else if (status == 'error' || status == 'no_results')
					{

						modal_message({message: results});
					}
					else
					{
						dd_console_log(results);
					}

				});

			}
			else
			{
				szZip = addressArray['postal_code'].long_name;

				retrieve_stores_for_lat_long({
					latitude: latitude,
					longitude: longitude,
					zip: szZip

				}, true);
			}

		}
		else if (status == 'error' || status == 'no_result')
		{
			$("#store_search_results").empty();
			$("#store_search_results").html('<text>No local stores near you.</text>');
		}
		else
		{
			dd_console_log(results);
		}

	});

}

function handle_zipcode_search()
{
	address_search = '';
	if ($('#zipsearch_zipcode_only').getVal() == '')
	{
		modal_message({message: 'Zip code required'});
		return false;
	}
	else
	{
		address_search += ' ' + $('#zipsearch_zipcode_only').getVal();
	}

	address_location({address: address_search}, function (status, results) {

		if (status == 'ok')
		{
			latitude = results[0].geometry.location.lat();
			longitude = results[0].geometry.location.lng();

			addressArray = address_components(results);

			szZip = addressArray['postal_code'].long_name;

			retrieve_stores_for_lat_long({
				latitude: latitude,
				longitude: longitude,
				zip: szZip
			}, true);
			//hide invalid search results message above search box
			$('#zipsearch_zipcode_errorMessage').addClass('collapse');

			//get rid of red border
			$("#zipsearch_zipcode_only").removeClass('border-red');
		}

		else if (status == 'error' || status == 'no_result')
		{
			//the postal code search box results are BAD, clear old store search results to indicate bad postal code was entered
			$("#store_search_results").empty();
			//show Invalid Zip Code message
			$("#zipsearch_zipcode_errorMessage").removeClass('collapse');
			//red outline in the search box, indicating to the user that they entered an invalid zip code
			$("#zipsearch_zipcode_only").addClass('border-red');
			//handle the loading spinner on the button
			$('#zipsearch_search_btn').removeClass('btn-spinner');
			$('#zipsearch_search_btn').removeClass('btn-spinning');
			$('#zipsearch_search_btn').addClass('btn-spinner');
			//pop up a dd_alert indicating that the postal code that was entered is not valid
		}
		else
		{
			dd_console_log(results);
		}

	});

}

function retrieve_stores_for_address(settings)
{
	var config = {
		address: false, // required
		compact: false
	};

	if (!settings.address)
	{
		dd_console_log('retrieve_stores_for_address() requires settings.address');
	}

	$.extend(config, settings);

	address_location(settings, function (status, results) {

		if (status == 'ok')
		{
			let szZip = '';
			latitude = results[0].geometry.location.ob;
			longitude = results[0].geometry.location.pb;

			for (key in results[0].address_components)
			{
				if (results[0].address_components[key].types[0] == 'postal_code')
				{
					szZip = results[0].address_components[key].long_name;
				}
			}

			if (szZip != '')
			{
				retrieve_stores_for_lat_long({
					latitude: latitude,
					longitude: longitude,
					zip: szZip,
					compact: settings.compact
				}, false);
			}
			else
			{
				$("#store_search_results").empty();
				$("#store_search_results").html('<text>No local stores near you.</text>');
			}

		}
		else if (status == 'error' || status == 'no_result')
		{
			$("#store_search_results").empty();

			$("#store_search_results").html('<text>No local stores near you.</text>');
		}
		else
		{
			dd_console_log(results);
		}

	});
}

function retrieve_stores_for_lat_long(settings, doScrolling)
{
	var config = {
		compact: false,
		callBack: false
	};

	$.extend(config, settings);

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'location_search',
			op: 'json',
			latitude: settings.latitude,
			longitude: settings.longitude,
			zip: settings.zip,
			compact: settings.compact

		},
		success: function (json) {

			if (settings.callBack == false)
			{
				$("#store_search_results").html(json.html);

				if (doScrolling)
				{
					//scroll to successful search results
					$('html, body').animate({
						scrollTop: $('#store_search_results').offset().top
					}, 2000);
					//handle the loading spinner on the button
					$('#zipsearch_search_btn').removeClass('btn-spinner');
					$('#zipsearch_search_btn').removeClass('btn-spinning');
					$('#zipsearch_search_btn').addClass('btn-spinner');

				}
				//$("#store_search_results").slideDown();
				select_location_click_handler();

				if (getQueryVariable('page') == 'locations')
				{
					// Update login form so they come back to last search
					$('#back_url_top').val('/locations?zip=' + szZip);

					// Record history in html5 browser
					historyPush({
						url: '/locations?zip=' + szZip,
						title: szZip + ' ' + document.title
					});
				}
			}
			else if (typeof settings.callBack === 'function')
			{
				settings.callBack(json);
			}

		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';
		}
	});
}

function getStoresForState(state_id, scrollto)
{
	if (typeof scrollto == 'undefined' || scrollto != false)
	{
		$('html, body').animate({
			scrollTop: $('#zipsearch_zipcode_only').offset().top
		}, 2000);
	}

	// Don't do anything if they are searching for what they have currently displayed
	if (g_lastSearch == state_id)
	{
		return false;
	}

	// Hide old results and scroll to top of page
	$("#store_search_results").slideUp();

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'location_search',
			op: 'json',
			state: state_id

		},
		success: function (json) {
			$("#store_search_results").html(json.html);
			$("#store_search_results").slideDown();
			select_location_click_handler();

			// Store last search
			g_lastSearch = state_id;
			$('#zipsearch_state_id').val(state_id);

			// Clear out search for effect
			$("#zipsearch_zipcode, #zipsearch_address, #zipsearch_city").val('').blur();

			if (getQueryVariable('page') == 'locations')
			{
				// Update login form so they come back to last search
				$('#back_url_top').val('/locations?state=' + state_id);

				// Record history in html5 browser
				historyPush({
					url: '/locations?state=' + state_id,
					title: state_id + ' ' + document.title
				});
			}
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';
		}
	});
}

$(function () {

	// Register click handler to search states
	$(document).on('click', '[data-linear_address]', function (e) {
		e.preventDefault();
		showMap($(this).data('linear_address'));
	});

	// Register click handler to search states
	$(document).on('click', '[id^="locations_state-"]', function (e) {
		getStoresForState(this.id.split("-")[1]);
		// Stop browser from following href
		e.preventDefault();
	});

	$(document).on('click', '#zipsearch_search_btn', function (e) {
		e.preventDefault();
		handle_zipcode_search();
		$(this).addClass('');
	});

	$(document).on('click', '#full_addr_search_btn', function (e) {
		e.preventDefault();
		if (handle_address_search() !== false)
		{
			$('html, body').animate({
				scrollTop: $('#store_search_results').offset().top
			}, 2000);
		}
	});

	$(document).on('keypress', '#zipsearch_zipcode', function (e) {
		if (!$("#zipsearch_city").getVal())
		{
			$("#zipsearch_state_id").getVal('');
		}

		if (e.which == 13)
		{
			$("#zipsearch_search_btn").trigger('click');
		}
	});

	$(document).on('submit', '.form-shipping-search', function (e) {

		e.preventDefault();

		if (this.checkValidity() !== false)
		{
			let zipSearch = $.trim($(this).find('input.form-shipping-search-zip').getVal());

			$.ajax({
				url: '/processor',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'location_search',
					op: 'shipping',
					zip: zipSearch
				},
				success: function (json) {

					if (json.delivered_store)
					{
						if (json.shipping_has_inventory)
						{
							create_and_submit_form({
								action: '/box-select',
								input: ({
									delivered_zip: zipSearch
								})
							});
						}
						else
						{
							bootbox.dialog({
								title: 'Out of stock',
								message: "We can ship to you, but our fridge is currently empty. We are busy prepping our new menu. Check back soon for a new selection of meals.",
								centerVertical: true,
								buttons: {
									locations: {
										label: 'Search all locations',
										callback: function () {
											create_and_submit_form({
												action: '/locations',
												input: ({
													zip: zipSearch
												})
											});
										}
									},
									cancel: {
										label: 'Close'
									}
								}
							})
						}
					}
					else
					{
						bootbox.dialog({
							title: 'Shipping not available',
							message: 'We do not ship to the zip code provided.',
							centerVertical: true,
							buttons: {
								locations: {
									label: 'Search all locations',
									callback: function () {
										create_and_submit_form({
											action: '/locations',
											input: ({
												zip: zipSearch
											})
										});
									}
								},
								cancel: {
									label: 'Close'
								}
							}
						})
					}
				},
				error: function (objAJAXRequest, strError) {
					response = 'Unexpected error';
				}
			});
		}
	});

	// Check that cookies are enabled
	cookieCheck();

	handle_browser_location();

	if (typeof simplemaps_usmap != 'undefined')
	{
		simplemaps_usmap.hooks.zoomable_click_state = function (id) {
			getStoresForState(id, false);
		}

		simplemaps_usmap.hooks.click_state = function (id) {
			getStoresForState(id, true);
		}
	}

});