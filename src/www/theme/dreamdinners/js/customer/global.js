var AJAX_IN_PROCESS = false;
var GUEST_PREFERENCES = {};
var lang = {
	'en': {
		'dd': {
			'dream_dinners': 'Dream Dinners'
		},
		'tc': {
			'terms_and_conditions': 'Terms & Conditions',
			'delayed_payment': 'When I opt to use the Delayed Payment service to pay for my order, I understand that the balance due for this and future orders will be automatically withdrawn (5) five days prior to my session date and will transact using the same credit card as was used to pay for the order deposit.',
			'delayed_payment_decline': 'By declining the Delayed Payment Terms and Conditions future orders are required to be paid in full upon submission. The balance due for existing Delayed Payement orders will be due at session.'
		}
	}
};
function set_user_pref(pref, setting, user_id, callBack)
{
	pref = pref.toUpperCase();
	let pref_value = USER_PREFERENCES[pref].value;

	if ($.isPlainObject(pref_value) && $.isPlainObject(setting))
	{
		setting = JSON.stringify($.extend(USER_PREFERENCES[pref].value, setting));
	}
	else
	{
		setting = setting.toString()
	}

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'account',
			op: 'update_pref',
			key: pref,
			user_id: user_id,
			value: setting.toString()
		},
		success: function (json, status) {
			if (typeof callBack === 'function')
			{
				callBack(json, status);
			}

			response = json;
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';
		}
	});

}

function get_user_pref(pref, user_id, callBack)
{

	if (typeof user_id != 'undefined') // get guest pref
	{
		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data: {
				processor: 'account',
				op: 'get_pref',
				key: pref,
				user_id: user_id
			},
			success: function (json, status) {
				if (typeof callBack === 'function')
				{
					callBack(json, status);
				}

				return json;
			},
			error: function (objAJAXRequest, strError) {
				response = 'Unexpected error';
			}
		});
	}
	else // get current user pref
	{

		if (typeof USER_PREFERENCES !== 'undefined')
		{
			if (typeof USER_PREFERENCES[pref] === 'object')
			{
				return USER_PREFERENCES[pref];
			}
		}
	}

	return false;
}

function select_location_click_handler()
{
	// Register click handler to select store
	$(document).on('click', '[id^="select_location-"], [id^="select_intro_location-"]', function (e) {

		var config = {
			store_id: null,
			order_type: 'standard'
		};

		if (this.id.indexOf('intro_location') > 0)
		{
			config.order_type = 'intro';
		}

		config.store_id = this.id.split("-")[1];

		setStoreAndBeginOrder(config);

		// Stop browser from following href
		e.preventDefault();
	});
}

function bounce(location, target)
{
	if (!location)
	{
		location = '/';
	}

	if (target)
	{
		window.open(location, target);
	}
	else
	{
		window.location.href = location;
	}
}

function back_path()
{
	// returns encoded string to pass to back=
	return encodeURIComponent(location.pathname + location.search);
}

function incrementVal(value, incrementBy)
{
	value = parseInt(value);
	incrementBy = parseInt(incrementBy);

	if (isNaN(value))
	{
		value = 0;
	}

	value = value + incrementBy;

	if (parseInt(value) < 0)
	{
		value = 0;
	}

	return value;
}

function toggleId(id)
{
	$('#' + id).slideToggle();
}

function setQueryString(variable, value)
{
	var query = window.location.search.substring(1);
	var vars = query.split("&");
	var_found = false;
	new_query = '?';

	for (var i = 0; i < vars.length; i++)
	{
		var pair = vars[i].split("=");

		if (pair[0] == variable)
		{
			pair[1] = value;
			var_found = true;
		}

		if (i > 0)
		{
			new_query += '&';
		}

		new_query += pair[0] + '=' + pair[1];
	}

	if (!var_found)
	{
		if (i > 0)
		{
			new_query += '&';
		}

		new_query += variable + '=' + value;
	}

	return new_query;
}

function getQueryVariable(variable)
{
	var query = window.location.search.substring(1);
	var vars = query.split("&");

	for (var i = 0; i < vars.length; i++)
	{
		var pair = vars[i].split("=");
		if (pair[0] == variable)
		{
			return decodeURI(pair[1]);
		}
	}

	return false;
}

function removeQueryVariable(key, sourceURL)
{
	var rtn = sourceURL.split("?")[0],
		param,
		params_arr = [],
		queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";

	if (queryString !== "")
	{
		params_arr = queryString.split("&");

		for (var i = params_arr.length - 1; i >= 0; i -= 1)
		{
			param = params_arr[i].split("=")[0];

			if (param === key)
			{
				params_arr.splice(i, 1);
			}
		}

		rtn = rtn + "?" + params_arr.join("&");
	}

	return rtn;
}

function mailHideOnSubmit(token)
{
	document.getElementById("mailhide_form").submit();
}

function historyPush(config)
{
	// Record history in html5 browser if supported
	if (window.history && window.history.pushState)
	{
		var title = document.title;
		if (config.title)
		{
			title = config.title;
		}

		history.pushState(null, title, config.url);

	}
}

function create_and_submit_form(config)
{
	var settings = { //defaults
		method: 'post',
		target: false
	};

	$.extend(settings, config);

	// Create dynamic form to post and redirect to session_menu
	var form = $('<form></form>');
	form.attr('method', settings.method);

	if (settings.action)
	{
		form.attr('action', settings.action);
	}

	if (settings.target)
	{
		form.attr('target', settings.target);
	}

	for (var property in settings.input)
	{
		$('<input>').attr({
			type: 'hidden',
			name: property,
			value: settings.input[property]
		}).appendTo(form);
	}

	$(document.body).append(form);
	form.submit();
	$(form).remove();
}

function setStoreAndBeginOrder(config)
{
	// Show trobber
	$('.img_throbber_circle').show();

	// Create dynamic form to post and redirect to session_menu
	create_and_submit_form({
		action: '/session-menu',
		input: ({
			store: config.store_id,
			order_type: config.order_type
		})
	});
}

function showMap(address)
{
	modal_message({
		width: 670,
		height: 580,
		modal: false,
		resizable: true,
		message: '<iframe width="100%" height="97%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="//maps.google.com/maps?q=Dream%20Dinners%20' + encodeURIComponent(address) + '&amp;hnear=Dream%20Dinners%20' + encodeURIComponent(address) + '&amp;f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;ie=UTF8&amp;hq=&amp;t=m3&amp;spn=0.02878,0.054932&amp;z=14&amp;iwloc=A&amp;output=embed"></iframe>'
	});
}

function confirmNavigate(goUrl, message)
{
	modal_message({
		message: message,
		confirm: function () {
			bounce(goUrl);
		}
	});
}

function dd_console_log(message)
{
	if (is_debug() && window.console)
	{
		console.log('DD Console Log: ' + message);
	}
}

function address_location(settings, callBack)
{
	var config = {
		address: false, // mailing address string, can be partial address like just zip code or city and state.
		latlong: false // latitude,longitude
	};

	// settings can be optional so check if callback is the first parameter
	if (typeof callBack === 'undefined' && typeof settings === 'function')
	{
		callBack = settings;
		settings = config;
	}
	else
	{
		$.extend(config, settings);
	}

	var getLocation = function (geolocation, address, latlong) {
		//dd_console_log('Fresh Geolocation data.');

		var geocoder = new google.maps.Geocoder();
		var geoparams;

		if (address != undefined)
		{
			address = address.trim();
			//the entered address was a postal code or it was less than 5 characters, will send it as a postal code anyways, but will return an invalid zip code
			if (address.length <= 5)
			{
				geoparams = {'latLng': latlng, 'componentRestrictions': {'postalCode': address}};
			}
		}

		if (typeof latlong != 'undefined' && latlong)
		{
			latlong = latlong.split(",");
			var lat = parseFloat(latlong[0]);
			var lng = parseFloat(latlong[1]);
			var latlng = new google.maps.LatLng(lat, lng);

			geoparams = {'latLng': latlng};
		}
		else if (geolocation !== false)
		{
			var lat = parseFloat(geolocation.coords.latitude);
			var lng = parseFloat(geolocation.coords.longitude);
			var latlng = new google.maps.LatLng(lat, lng);


			geoparams = {'latLng': latlng};
		}
		//the entered address was not a postal code
		else if(address != undefined && address.length > 5){
			geoparams = {'address': address};
		}

		geocoder.geocode(geoparams, function (results, status) {

			if (status == google.maps.GeocoderStatus.OK)
			{

				if (results)
				{
					callBack('ok', results);
				}
				else
				{
					callBack('no_result', 'No geocoder results found.');
				}
			}
			else
			{
				callBack('error', 'No geocoder results found.');

			}
		});

	};

	var onError = function (error) {
		switch (error.code)
		{
			case error.PERMISSION_DENIED:
				callBack('gl_error', 'User denied the request for Geolocation.');
				break;
			case error.POSITION_UNAVAILABLE:
				callBack('gl_error', 'Geolocation information is unavailable.');
				break;
			case error.TIMEOUT:
				callBack('gl_error', 'The request to get user location timed out.');
				break;
			case error.UNKNOWN_ERROR:
				callBack('gl_error', 'An unknown error occurred.');
				break;
		}
	};

	if (settings.address) // query by address
	{
		getLocation(false, settings.address, false);
	}
	else if (settings.latlong) // query by latlong
	{
		getLocation(false, false, settings.latlong);
	}
	else // browser geolocation
	{
		if (navigator.geolocation)
		{
			navigator.geolocation.getCurrentPosition(getLocation, onError);
		}
		else
		{


			callBack('gl_error', 'Geolocation not supported.');
		}
	}
}

function dd_toast(settings)
{
	var config = {
		animation: true,
		autohide: true,
		delay: 5000,
		title: false,
		message: false,
		type: 'primary'
	};

	$.extend(config, settings);

	if (!$('.toast-container').length)
	{
		$('<div class="toast-container fixed-top mt-4"></div>').appendTo('body');
	}

	switch (config.type)
	{
		case 'primary':
			config.css_header = 'bg-green-dark text-white';
			config.title = ((config.title) ? config.title : 'Notice');
			break;
		case 'secondary':
			config.css_header = 'bg-secondary text-white';
			config.title = ((config.title) ? config.title : 'Notice');
			break;
		case 'info':
			config.css_header = 'bg-info text-white';
			config.title = ((config.title) ? config.title : 'Notice');
			break;
		case 'success':
			config.css_header = 'bg-success text-white';
			config.title = ((config.title) ? config.title : 'Success');
			break;
		case 'warning':
		case 'warn':
			config.css_header = 'bg-warning text-white';
			config.title = ((config.title) ? config.title : 'Warning');
			break;
		case 'error':
		case 'danger':
			config.css_header = 'bg-danger text-white';
			config.title = ((config.title) ? config.title : 'Error');
			break;
	}

	var html = '<div class="toast mx-auto" role="alert" aria-live="assertive" aria-atomic="true" data-delay="' + config.delay + '" data-autohide="' + config.autohide + '">';
	html += '<div class="toast-header ' + config.css_header + '">';
	html += '<strong class="mr-auto">' + ((config.title) ? config.title : 'Notice') + '</strong>';
	html += '<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span aria-hidden="true" class="text-white">&times;</span></button>';
	html += '</div>';
	html += '<div class="toast-body">' + config.message + '</div>';
	html += '</div>';

	$('.toast-container').append(html);
	$('.toast-container .toast:last').toast('show');
}

function fetchToasts(json)
{
	if (json.dd_toasts)
	{
		let time = 0;

		$.each(json.dd_toasts, function (key) {
			// do a short delay so they don't all show up at once.
			setTimeout(() => {
				dd_toast(json.dd_toasts[key]);
			}, time);
			time += 500; // .5 seconds, time between toasts for a response containing multiple toasts
		});
	}
}

function modal_message(settings)
{
	var config = {
		title: false,
		message: false,
		size: false // small, large, extra-large
	};

	$.extend(config, settings);

	if (typeof config.confirm != 'undefined')
	{
		// convert old dd_message
		if (typeof config.confirm == 'function')
		{
			config.buttons = {
				confirm: {
					label: 'Confirm',
					className: 'btn-primary',
					callback: config.confirm
				},
				cancel: {
					label: 'Cancel',
					className: 'btn-secondary',
					callback: config.cancel
				}
			}
		}

		bootbox.dialog(config);
	}
	else if (typeof config.buttons != 'undefined')
	{
		// convert old dd_message
		var buttons = {};

		$.each(config.buttons, function (key, value) {

			if (typeof value == 'function')
			{
				buttons[key] = {
					label: key,
					className: 'btn-primary',
					callback: value
				}
			}

		});

		config.buttons = buttons;

		bootbox.dialog(config);
	}
	else
	{
		bootbox.alert(config);
	}

}

function dd_alert(msg)
{
	modal_message({
		title: 'Alert',
		message: msg
	});
}

function formatAsMoney(mnt)
{
	if (typeof mnt == 'undefined')
	{
		mnt = 0;
	}

	mnt -= 0;
	mnt = (Math.round(mnt * 100)) / 100;
	return (mnt == Math.floor(mnt)) ? mnt + '.00'
		: ((mnt * 10 == Math.floor(mnt * 10)) ?
			mnt + '0' : mnt);
}

function get_todays_date()
{
	var today = new Date();
	var dd = ("0" + today.getDate()).slice(-2);
	var mm = ('0' + (today.getMonth() + 1)).slice(-2); //January is 0!
	var yyyy = today.getFullYear();

	return yyyy + '-' + mm + '-' + dd;
}

function cookieCheck()
{
	var hostAccess = getQueryVariable('host_url');
	var overrideCookieCheck = false;
	if (hostAccess && hostAccess == 'http://support.dreamdinners.com')
	{
		overrideCookieCheck = true;
	}

	$.cookie('chkcookie', true, {domain: COOKIE.domain, path: '/'});

	if (!$.cookie('chkcookie') && !overrideCookieCheck)
	{
		modal_message({
			title: 'Alert',
			message: 'You have cookies turned off in your browser. Please check your settings, and enable cookies for this site in order to continue. Thank you.'
		});
	}
}

function is_debug()
{
	if (typeof DEBUG !== 'undefined' && DEBUG === true)
	{
		return true;
	}

	return false;
}

function dd_print()
{
	if (is_debug())
	{
		dd_console_log('Debug: Print dialog called here.');
	}
	else
	{
		window.print();
	}
}

///------------ phpjs functions ----------------------
function nl2br(str, is_xhtml)
{
	//  discuss at: http://phpjs.org/functions/nl2br/
	//   example 1: nl2br('Kevin\nvan\nZonneveld');
	//   returns 1: 'Kevin<br />\nvan<br />\nZonneveld'
	//   example 2: nl2br("\nOne\nTwo\n\nThree\n", false);
	//   returns 2: '<br>\nOne<br>\nTwo<br>\n<br>\nThree<br>\n'
	//   example 3: nl2br("\nOne\nTwo\n\nThree\n", true);
	//   returns 3: '<br />\nOne<br />\nTwo<br />\n<br />\nThree<br />\n'

	var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

	return (str + '')
		.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function strip_tags(input, allowed)
{
	//  discuss at: http://phpjs.org/functions/strip_tags/
	//   example 1: strip_tags('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
	//   returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
	//   example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
	//   returns 2: '<p>Kevin van Zonneveld</p>'
	//   example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
	//   returns 3: "<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>"
	//   example 4: strip_tags('1 < 5 5 > 1');
	//   returns 4: '1 < 5 5 > 1'
	//   example 5: strip_tags('1 <br/> 1');
	//   returns 5: '1  1'
	//   example 6: strip_tags('1 <br/> 1', '<br>');
	//   returns 6: '1 <br/> 1'
	//   example 7: strip_tags('1 <br/> 1', '<br><br/>');
	//   returns 7: '1 <br/> 1'

	allowed = (((allowed || '') + '')
		.toLowerCase()
		.match(/<[a-z][a-z0-9]*>/g) || [])
		.join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
	var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
		commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	return input.replace(commentsAndPhpTags, '')
		.replace(tags, function ($0, $1) {
			return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
		});
}

///------------ Dream Dinners JQuery Extensions ----------------------
(function ($) {

	$.oauthpopup = function (onCloseCallBack) {
		if (!$(this).data('path'))
		{
			throw new Error("data-path must not be empty");
		}

		var defaults = {
			windowName: 'ConnectWithOAuth',
			windowOptions: {
				width: '800',
				height: '400',
				location: '0',
				status: '0'
			}
		};

		var options = {
			windowOptions: {
				width: $(this).data('width'),
				height: $(this).data('height')
			},
			path: $(this).data('path'),
			ref_page: $(this).data('ref_page'),
			reload_page: $(this).data('reload'),
			callback: onCloseCallBack
		};

		var settings = $.extend({}, defaults, options);

		var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
		var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

		var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
		var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

		var left = ((width / 2) - (settings.windowOptions.width / 2)) + dualScreenLeft;
		var top = ((height / 2) - (settings.windowOptions.height / 2)) + dualScreenTop;

		var windowConfig = 'location=' + settings.windowOptions.location + ', status=' + settings.windowOptions.status + ', width=' + settings.windowOptions.width + ', height=' + settings.windowOptions.height + ', top=' + top + ', left=' + left;

		var openPath = settings.path;

		if (typeof settings.ref_page != 'undefined')
		{
			openPath += '&ref_page=' + settings.ref_page;
		}

		if (typeof settings.reload_page != 'undefined')
		{
			openPath += '&reload_page=' + settings.reload_page;
		}

		var oauthWindow = window.open(openPath, settings.windowName, windowConfig);

		if (typeof settings.callback == 'function')
		{

			$.doTimeout('oauth_interval', 1000, function () {

				if (oauthWindow.closed)
				{
					$.doTimeout('oauth_interval');
					settings.callback();

					return false;
				}

				return true;
			});

		}

		return this;
	};

	//bind to element and pop oauth when clicked
	$.fn.oauthpopup = function (options) {
		$this = $(this);
		$this.click($.oauthpopup.bind(this, options));
	};

	// highlight an elements background color temporarily to call attention to it
	$.fn.attention = function (set_color, set_duration) {
		if (set_color == undefined)
		{
			set_color = '#ffed8c';
		}

		if (set_duration == undefined)
		{
			set_duration = 2000;
		}

		$(this).stop().css('background-color', set_color).animate({backgroundColor: 'transparent'}, {duration: set_duration});

		return this;
	};

	// center an element on the screen
	$.fn.center = function () {
		this.css("position", "absolute");
		this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) + $(window).scrollTop()) + "px");
		this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) + $(window).scrollLeft()) + "px");
		return this;
	};

	// get value if not equal to placeholder, primarily for ie
	$.fn.getVal = function () {
		var $this = $(this),
			val = $this.eq(0).val();

		if (val == $this.attr('placeholder'))
		{
			return '';
		}
		else
		{
			return val;
		}
	};

	$.fn.addSpinner = function (e) {
		if (this.hasClass('btn-spin') && this.find('.spinner-border').length === 0)
		{
			// bootstrap Spinner
			this.append('<span class="spinner-border spinner-border-sm float-right mt-1" role="status" aria-hidden="true"></span>');
		}
		else if (this.hasClass('btn-spinner') && !this.hasClass('btn-spinning'))
		{
			// DD spinner
			this.addClass('btn-spinning').append('<div class="ld-spin"></div>');
		}

		return this;
	};

	$.fn.removeSpinner = function (e) {
		// remove DD spinner
		this.removeClass('btn-spinning');
		this.find('.ld-spin').remove();
		// remove Bootstrap
		this.find('.spinner-border').remove();

		return this;
	};

	$.fn.hideFlex = function (e) {
		this.addClass('collapse');
		this.removeClass('show');

		return this;
	};

	$.fn.showFlex = function (e) {
		this.addClass('collapse show');

		return this;
	};

	$.fn.toggleFlex = function (e) {

		if (this.is(':visible'))
		{
			this.hideFlex(e)
		}
		else
		{
			this.showFlex(e);
		}

		return this;
	};

	$.fn.valDefault = function (value) {

		if (typeof value !== 'undefined')
		{
			this.val(value);
		}
		else if (typeof this[0].dataset.valdefault !== 'undefined')
		{
			this.val(function () {
				return this.dataset.valdefault;
			});
		}
		else if (typeof this[0].defaultValue !== 'undefined')
		{
			this.val(function () {
				return this.defaultValue;
			});
		}
		else if (this[0].type == 'select-one')
		{
			for (var i = 0; i < this[0].options.length; i++)
			{
				if (this[0].options[i].defaultSelected == true)
				{
					this[0].selectedIndex = i;
					break;
				}
			}
		}

		return this;
	};

	$.fn.valCheckDiff = function (dataObj) {

		// no value, use input's value as the base value
		if (typeof dataObj === 'undefined' || typeof dataObj.value === 'undefined')
		{
			dataObj.value = this.value;
		}

		if (typeof dataObj.group === 'undefined')
		{
			dataObj.group = '_main';
		}

		this.data({'checkdiff' : dataObj}).val(dataObj.value).on('change keyup', function (e) {

			let diffData = {};
			let dataObj = $(this).data().checkdiff;

			if (typeof $(document).data().checkdiff != 'undefined')
			{
				diffData = $(document).data().checkdiff;
			}

			if ((dataObj.value != null && this.value != dataObj.value) || (this.value != '' && dataObj.value == null))
			{
				if (typeof diffData[dataObj.group] == 'undefined')
				{
					diffData[dataObj.group] = {};
				}

				diffData[dataObj.group][this.id] = dataObj;
			}
			else
			{
				// values are not different, remove from diff object
				delete diffData[dataObj.group][this.id];
			}

			$(document).data({'checkdiff': diffData}).trigger('dd:checkdiff');

		});

		return this;
	};

	$.fn.valCheckDiffRemove = function (value) {

		if (typeof value != 'undefined')
		{
			this.val(value);
		}

		let dataObj = $(this).data();

		if (typeof dataObj != 'undefined')
		{
			if (typeof dataObj.checkdiff != 'undefined')
			{
				this.removeData('checkdiff').off('change keyup');
			}
		}

		return this;

	};


	$.fn.validateForm = function () {

		let form = $(this);
		let validity = true;

		if (form.length)
		{
			// prevent spaces from validating form
			$(form).find('input, textarea').each(function (e) {
				$(this).val($(this).val().trim());
			});

			if ($(this).find(':submit').hasClass('btn-onclick-disable'))
			{
				$(this).find(':submit').addClass('disabled');
			}

			// handle checkbox groups
			let checkbox_groups = [];

			// collect list of unique checkbox required groups
			$(form).find('[data-checkbox_group_required="true"]').each(function (e) {
				let group_name = $(this).data('checkbox_group');
				if (checkbox_groups[group_name] !== 'undefined')
				{
					checkbox_groups.push(group_name);
				}
				// set them all to required
				$('[data-checkbox_group="' + group_name + '"]').attr('required', true);
			});

			// check each group
			$.each(checkbox_groups, function (key, group_name) {
				// check each checkbox in group for validity
				$('[data-checkbox_group="' + group_name + '"]').each(function () {
					// found a box that is checked
					if ($(this).is(':checked'))
					{
						// one was checked, remove required
						$('[data-checkbox_group="' + group_name + '"]').attr('required', false);
					}
				});
			});
			// end handle checkbox groups

			$(form).find('.form-feedback').hide();

			if (form['0'].checkValidity() === false)
			{
				validity = false;

				if ($(this).find(':submit').hasClass('btn-onclick-disable'))
				{
					$(this).find(':submit').removeClass('disabled');
				}

				$('.btn-spinning').removeClass('btn-spinning');
				$('.ld-spin').remove();

				$(form).find('.form-feedback').show();

				$(form).data('was_submitted', false);
			}

			form['0'].classList.add('was-validated');
		}
		else
		{
			console.log();
		}

		return validity;
	};

})(jQuery);

// --------------------------------------------------------------------
//	PayFlow_Payload
//  support for encoding custom data sent to PayFlow as a commnt
// ---------------------------------------------------------------------
function PayFlow_Payload()
{
	this.encodedString = "";
}

PayFlow_Payload.prototype = {
	constructor: PayFlow_Payload,
	addNameValuePair: function (name, value) {
		value += "";

		var cleanName = name.replace("+", "");
		cleanName = cleanName.replace(":", "");
		var cleanValue = value.replace("+", "");
		cleanValue = cleanValue.replace(":", "");
		cleanValue = cleanValue.replace("`", "");
		cleanValue = cleanValue.replace("'", "");
		cleanValue = cleanValue.replace("\"", "");
		cleanValue = cleanValue.replace("&", "and");

		if (this.encodedString == "")
		{
			this.encodedString = cleanName + ":" + cleanValue;
		}
		else
		{
			this.encodedString += "+" + cleanName + ":" + cleanValue;
		}
	},
	addAssocArray: function (name, arr) {
		var cleanName = name.replace("+", "");
		cleanName = cleanName.replace(":", "");

		arrString = "";

		for (var value in arr)
		{
			if (arr[value])
			{
				var cleanValue = arr[value].replace("+", "");
				cleanValue = cleanValue.replace(":", "");
				cleanValue = cleanValue.replace("|", "");
				cleanValue = cleanValue.replace("~", "");
				cleanValue = cleanValue.replace("`", "");
				cleanValue = cleanValue.replace("'", "");
				cleanValue = cleanValue.replace("\"", "");
				cleanValue = cleanValue.replace("&", "and");

				var cleanPropName = value.replace("+", "");
				cleanPropName = cleanPropName.replace(":", "");
				cleanPropName = cleanPropName.replace("|", "");
				cleanPropName = cleanPropName.replace("~", "");

				if (arrString == "")
				{
					arrString = cleanPropName + "~" + cleanValue;
				}
				else
				{
					arrString += "|" + cleanPropName + "~" + cleanValue;
				}
			}
		}

		if (this.encodedString == "")
		{
			this.encodedString = cleanName + ":" + arrString;
		}
		else
		{
			this.encodedString += "+" + cleanName + ":" + arrString;
		}
	},
	retrieveEncodedString: function () {

		return this.encodedString;

	}
};

/* handle Bootstrap form validation */
$(document).on('submit', '.needs-validation', function (e) {

	if ($(this).validateForm() !== true)
	{
		e.preventDefault();
		e.stopPropagation();
	}

});

/* Handle Bootstrap Tabbed Content */
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	e.target; // newly activated tab
	e.relatedTarget; // previous active tab

	// html5 update url history
	if ($(e.target).data('urlpush') !== undefined && $(e.target).data('urlpush') === true)
	{
		if ($(e.target).data('target') !== undefined)
		{
			var target = $(e.target).data('target');
		}
		else
		{
			var target = $(e.target).attr('href');
		}

		var tabid = $(target).attr('id');

		new_query_string = setQueryString('tab', tabid);
		historyPush({url: new_query_string});
	}
});

function isIE()
{
	var ua = window.navigator.userAgent; //Check the userAgent property of the window.navigator object
	var msie = ua.indexOf('MSIE '); // IE 10 or older
	var trident = ua.indexOf('Trident/'); //IE 11

	return (msie > 0 || trident > 0);
}

function isSafari()
{
	var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

	return isSafari;
}

$(document).ajaxSuccess(function (event, xhr, settings) {

	$('.telephone:not(.no-tel), [type="tel"]:not(.no-tel)').each(function (index) {
		new Cleave(this, {
			delimiters: [
				'-',
				'-',
				'-'
			],
			blocks: [
				3,
				3,
				4
			]
		});
	});

	// inside try to attempt to decode response as json
	try
	{
		// decode the response as json
		var obj = $.parseJSON(xhr.responseText);

		// if the json contains toasts, pop them up
		if (obj.dd_toasts)
		{
			fetchToasts(obj)
		}

		// updated user preferences
		if (obj.user_preferences)
		{
			USER_PREFERENCES = obj.user_preferences;
		}

		// updated guest preferences
		if (obj.guest_preferences)
		{
			GUEST_PREFERENCES = $.extend(GUEST_PREFERENCES, obj.guest_preferences);
		}

		// bounce
		if (obj.bounce_to)
		{
			bounce(obj.bounce_to);
		}
	}
	catch (e)
	{
		// not json
	}

});

// handle setToastMsg
if ($.cookie('toastMsg'))
{
	var messages = $.parseJSON($.cookie('toastMsg'));

	var time = 0;

	$.each(messages, function (key, toastArray) {

		setTimeout(() => {
			dd_toast(toastArray);
		}, time);

		time += 500; // .5 seconds, time between toasts for a response containing multiple toasts

	});

	$.removeCookie('toastMsg', {domain: COOKIE.domain});
}

// load bootstrap tooltips
//$('[data-toggle="tooltip"]').tooltip();
$(document).tooltip({selector: '[data-toggle="tooltip"]'});

// handle analytics click
$('[data-gaq_cat]').each(function () {
	$(this).on('click', function () {

		if (typeof _gaq != 'undefined')
		{
			gaq_cat = $(this).data('gaq_cat');
			gaq_action = $(this).data('gaq_action');
			gaq_label = $(this).data('gaq_label');

			_gaq.push([
				'_trackEvent',
				gaq_cat,
				gaq_action,
				gaq_label
			]);
		}
	});
});

$('[data-background-image]').each(function () {
	var bg_image = $(this).data('background-image');
	$(this).css('background-image', 'url(' + bg_image + ')');
});

// Click handler for clear cart
$(document).on('click', '.clear-cart, .clear-cart-gc', function (e) {

	var category = 'all';
	if (window.location.href.indexOf('gift_card_cart') != -1)
	{
		category = 'giftcard';
	}

	$.ajax({
		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_clear_category',
			op: 'get_clear_form',
			output: 'json'
		},
		success: function (json) {
			modal_message({
				title: 'Clear your Cart',
				message: json.html,
				height: 300,
				width: 300,
				resizable: false,
				confirm: function () {

					$.ajax({
						url: '/processor',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'cart_clear_category',
							op: 'do_clear',
							clear_items: category,
							output: 'json'
						},
						success: function (json) {
							// salesforce clear cart
							if (typeof _etmc !== 'undefined')
							{
								_etmc.push([
									"trackCart",
									{"clear_cart": true}
								]);
							}

							// reload page
							window.location.reload();
						},
						error: function (objAJAXRequest, strError) {
							response = 'Unexpected error';
						}
					});
				},
				cancel: function () {
				}
			});
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';
		}
	});
});


//Freshworks help widget - responsive view control
$(document).ready(function() {
	if(window.innerWidth <= 1000)
	{
		FreshworksWidget('hide', 'launcher');
	}
});

window.onresize = function() {
	if(typeof FreshworksWidget !== 'undefined'){
		if(window.innerWidth <= 1000)
		{
			FreshworksWidget('hide', 'launcher');
		}else{
			FreshworksWidget('show', 'launcher');
		}
	}
};

$(document).on('click', '.help-search-launcher', function (event) {
	event.preventDefault();
	FreshworksWidget('open');
});


$(document).on('click', '.clear-edit-delivered-order', function (e) {

	$.ajax({

		url: '/processor',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'cart_clear_category',
			op: 'get_clear_form',
			output: 'json'
		},
		success: function (json) {
			modal_message({
				title: 'Clear Cart',
				message: 'Are you sure you want to stop editing this Delivered order?',
				height: 300,
				width: 300,
				resizable: false,
				confirm: function () {

					$.ajax({
						url: '/processor',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'cart_clear_category',
							op: 'do_clear',
							clear_items: 'all',
							clear_edit_order: 'all',
							output: 'json'
						},
						success: function (json) {

							// reload page
							window.location = '/my-meals?tab=nav-past_orders';
						},
						error: function (objAJAXRequest, strError) {
							console.log('Unexpected error');
						}
					});
				},
				cancel: function () {
				}
			});
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';
		}
	});
});

// Handle textarea maxlength
$(document).on('keyup', 'textarea[maxlength]', function (e) {

	//get the limit from maxlength attribute
	var limit = parseInt($(this).attr('maxlength'));
	//get the current text inside the textarea
	var text = $(this).val();
	//count the number of characters in the text
	var chars = text.length;

	//check if there are more characters then allowed
	if (chars > limit)
	{
		//and if there are use substr to get the text before the limit
		var new_text = text.substr(0, limit);

		//and change the current text with the new text
		$(this).val(new_text);
	}
});

// handle btn spinner
$(document).on('click', '.btn-spinner:not(.disabled), .btn-spin:not(.disabled)', function (e) {
	$(this).addSpinner();
});

// handle masks
$('.telephone:not(.no-tel), [type="tel"]:not(.no-tel)').each(function (index) {
	new Cleave(this, {
		delimiters: [
			'-',
			'-',
			'-'
		],
		blocks: [
			3,
			3,
			4
		]
	});
});

// nutritionals menu dropdown
$(document).on('change', '#menus_dropdown', function (e) {
	bounce('/nutritionals?menu=' + this.value);
});

// platepoints enroll
$(document).on('click change', '#enroll_in_plate_points', function (e) {
	set_req = false;
	if ($(this).is(":checked"))
	{
		set_req = true;
	}
	$('#referral_source, #gender, #birthday_month, #birthday_year, #number_of_kids, #desired_homemade_meals_per_week, #number_feeding,  #number_of_adults, #contribute_income, #use_lists, #number_monthly_dine_outs').attr('required', set_req);
	$('#prefer_daytime_sessions, #prefer_evening_sessions, #prefer_weekend_sessions').attr({
		'data-checkbox_group_required': set_req,
		'required': set_req
	});
});

// handle share event
$('[data-share-social]').each(function () {

	var title = $(this).data('share-title');
	var text = $(this).data('share-text');
	var url = $(this).data('share-url');

	var services = $(this).data('share-social').split(",");

	var dropdown_menu = $('<div></div>').addClass('dropdown-menu');

	$(services).each(function (key, service) {

		if (service == 'facebook')
		{
			$(dropdown_menu).append('<a class="dropdown-item nav-link facebook-share" data-share-url="' + url + '" href="#"><i class="dd-icon icon-facebook mr-2"></i> Facebook</a>');
		}

		if (service == 'twitter')
		{
			$(dropdown_menu).append('<a class="dropdown-item nav-link" target="_blank" href="https://twitter.com/intent/tweet?via=DreamDinners&&hashtags=TheOriginalMealKit&text=' + text + '&url=' + url + '"><i class="dd-icon icon-twitter mr-2"></i> Twitter</a>');
		}

	});

	if (navigator.share)
	{
		let moreOpts = $('<a href="#" class="dropdown-item nav-link"><i class="dd-icon icon-share2 mr-2"></i> More Options</a>').on('click', function (e) {

			e.preventDefault();

			navigator.share({
				title: title,
				text: text,
				url: url
			});

		});

		$(dropdown_menu).append(moreOpts);
	}

	$(this).attr({
		'data-toggle': 'dropdown',
		'aria-haspopup': 'true',
		'aria-expanded': 'false'
	})
		.wrap('<div></div>')
		.addClass('dropdown')
		.after(dropdown_menu);

});

/*
$(document).on('click', '.start-intro-offer', function (e) {

	e.preventDefault();

	$.cookie('dd_start_intro', true);

	create_and_submit_form({
		action: '/processor?processor=session_type',
		input: ({
			type: 'starter'
		})
	});

});
*/

$(document).on('click', '[data-start-delivered-order]', function (e) {

	e.preventDefault();

	let start_delivered_zip = this.getAttribute('data-start-delivered-order');

	create_and_submit_form({
		action: '/box-select',
		input: ({
			delivered_zip: start_delivered_zip
		})
	});

});

$(document).on('click', '.btn-click-add-cart', function (e) {

	$('.btn-click-add-cart').find('.fa-shopping-cart').remove();

	$(this).append('<i class="fas fa-shopping-cart float-left text-green-dark-extra pt-1"></i>');

});

$(document).on('click', '[data-click_id]', function (e) {

	let click_id = $(this).data('click_id');

	$('#' + click_id).trigger('click');

	if (typeof $('#' + click_id).attr('href') !== 'undefined')
	{
		window.location.href = $('#' + click_id).attr('href');
	}

});

// handle platepoints enroll
if (getQueryVariable('pp_enroll'))
{
	$('#enroll_in_plate_points').attr('checked', true).trigger('change');
}

// handle tab selection
if (getQueryVariable('tab'))
{
	var tabid = getQueryVariable('tab');

	$('#' + tabid + '-tab').tab('show');
}

// footer nav
if ($('.footer-nav').length)
{
	$('.footer-nav').scrollToFixed({
		bottom: 0,
		limit: $('.footer-nav').offset().top
	});
}

// sidenav
$('#main-sidenav').on('hide.bs.collapse', function () {
	$('body').removeClass('modal-open');
	$('.sidenav-modal').removeClass('show');

	setTimeout(() => {
		$('.sidenav-modal').remove();
	}, 500);

});

$('#main-sidenav').on('show.bs.collapse', function () {
	$('body').addClass('modal-open').append($('<div class="sidenav-modal modal-backdrop fade"></div>'));
	$('.sidenav-modal').tab('show');
});

$(document).on('click', '.sidenav-modal', function (e) {
	$('#main-sidenav').collapse('toggle');
});

$(document).on('keyup change', '.dd-strip-tags', function (e) {

	var allowed;

	if ($(this).data('allowed-tags'))
	{
		allowed = $(this).data('allowed-tags');
	}

	if ($(this).val() != strip_tags($(this).val(), allowed))
	{
		$(this).val(strip_tags($(this).val(), allowed));
	}

});

// On error, show dd_console_log
$(document).ajaxError(function (event, jqxhr, settings, exception) {
	AJAX_IN_PROCESS = false;
	dd_console_log('Ajax Error: ' + jqxhr.statusText);
	//modal_message({ title: 'Ajax Error', message: request.statusText });
});

// userpreferences
if (typeof USER_PREFERENCES === 'string')
{
	USER_PREFERENCES = $.parseJSON(USER_PREFERENCES);
}

// handle checkboxes
$(document).on('click', '[data-user_pref][type=checkbox]', function (e) {

	var pref_elem = this;
	var user_id = 0;
	var value = $(this).is(':checked');

	if ($(this).data('user_id'))
	{
		user_id = $(this).data('user_id');
	}

	if (!$(this).data('user_pref_orig'))
	{
		$(this).data('user_pref_orig', $(this).is(':checked'));
	}

	if (value && $(this).data('user_pref_value_check'))
	{
		value = $(this).data('user_pref_value_check');
	}
	else if (!value && $(this).data('user_pref_value_uncheck'))
	{
		value = $(this).data('user_pref_value_uncheck');
	}

	if( typeof preferenceChangeListener === "function"){
		preferenceChangeListener($(this).data('user_pref'), value, user_id, function (json) {

			if (!json.processor_success)
			{
				modal_message({message: json.processor_message});

				$(this).prop('checked', $(pref_elem).data('user_pref_orig'));
			}
			else
			{
				$(this).data('user_pref_orig', $(pref_elem).is(':checked'));
			}

		});
	}else{
		set_user_pref($(this).data('user_pref'), value, user_id, function (json) {

			if (!json.processor_success)
			{
				modal_message({message: json.processor_message});

				$(this).prop('checked', $(pref_elem).data('user_pref_orig'));
			}
			else
			{
				$(this).data('user_pref_orig', $(pref_elem).is(':checked'));
			}

		});
	}



});

// handle dropdown select
$(document).on('change', '[data-user_pref]select', function (e) {

	var pref_elem = this;
	var user_id = 0;

	if ($(this).data('user_id'))
	{
		user_id = $(this).data('user_id');
	}

	if (!$(this).data('user_pref_orig'))
	{
		$(this).data('user_pref_orig', $(this).val());
	}

	set_user_pref($(this).data('user_pref'), $(this).val(), user_id, function (json) {

		if (!json.processor_success)
		{
			modal_message({message: json.processor_message});

			$(pref_elem).val($(pref_elem).data('user_pref_orig'));
		}
		else
		{
			$(pref_elem).data('user_pref_orig', $(pref_elem).val());
		}

	});

});


// handle input
$(document).on('focus', '[data-user_pref]input[type=text]', function (e) {

	if($(this).attr('type') == 'checkbox' ){
		return;
	}

	var pref_elem = this;
	var user_id = 0;

	if ($(this).data('user_id'))
	{
		user_id = $(this).data('user_id');
	}

	$(this).on('keyup', function (e) {

		if ($(this).val() != strip_tags($(this).val()))
		{
			$(this).val(strip_tags($(this).val()));
		}

	});

	if (!$(this).data('user_pref_orig'))
	{
		$(this).data('user_pref_orig', $(this).val());
	}

	if (!$('#' + $(this).data('user_pref') + '_buttons').length)
	{
		var buttons = $('<div />').attr('id', $(this).data('user_pref') + '_buttons').addClass('text-left collapse');

		$(this).after(buttons);

		var save_button = $('<span />').addClass('btn btn-sm btn-primary mr-2 mt-2').text('Save').on('click', function (e) {

			if( typeof preferenceChangeListener === "function"){
				preferenceChangeListener($(pref_elem).data('user_pref'), $(pref_elem).val(), user_id, function (json) {

					if (!json.processor_success)
					{
						modal_message({message: json.processor_message});

						$(pref_elem).val($(this).data('user_pref_orig'));
					}
					else
					{
						$(pref_elem).data('user_pref_orig', $(pref_elem).val());
					}

					$('#' + $(pref_elem).data('user_pref') + '_buttons').slideUp(function (e) {

						$('#' + $(pref_elem).data('user_pref') + '_buttons').remove();

					});

				});
			}else{
				set_user_pref($(pref_elem).data('user_pref'), $(pref_elem).val(), user_id, function (json) {

					if (!json.processor_success)
					{
						modal_message({message: json.processor_message});

						$(pref_elem).val($(this).data('user_pref_orig'));
					}
					else
					{
						$(pref_elem).data('user_pref_orig', $(pref_elem).val());
					}

					$('#' + $(pref_elem).data('user_pref') + '_buttons').slideUp(function (e) {

						$('#' + $(pref_elem).data('user_pref') + '_buttons').remove();

					});

				});
			}


		});

		var cancel_button = $('<span />').addClass('btn btn-sm btn-primary mt-2').text('Cancel').on('click', function (e) {

			$(pref_elem).val($(pref_elem).data('user_pref_orig'));

			$('#' + $(pref_elem).data('user_pref') + '_buttons').slideUp(function (e) {

				$('#' + $(pref_elem).data('user_pref') + '_buttons').remove();

			});

		});

		var spacer = $('<span />').addClass(' mt-2');

		$(buttons).prepend(cancel_button).prepend(save_button).prepend(spacer).slideDown();

	}

});

// handle textarea
$(document).on('focus', '[data-user_pref]textarea', function (e) {

	var pref_elem = this;
	var user_id = 0;

	if ($(this).data('user_id'))
	{
		user_id = $(this).data('user_id');
	}

	$(this).on('keyup', function (e) {

		if ($(this).val() != strip_tags($(this).val()))
		{
			$(this).val(strip_tags($(this).val()));
		}

	});

	if (!$(this).data('user_pref_orig'))
	{
		$(this).data('user_pref_orig', $(this).val());
	}

	if (!$('#' + $(this).data('user_pref') + '_buttons').length)
	{
		var buttons = $('<div />').attr('id', $(this).data('user_pref') + '_buttons').addClass('text-right collapse');

		$(this).after(buttons);

		var save_button = $('<span />').addClass('btn btn-sm btn-primary mr-2 mt-2').text('Save').on('click', function (e) {

			if( typeof preferenceChangeListener === "function"){
				preferenceChangeListener($(pref_elem).data('user_pref'), $(pref_elem).val(), user_id, function (json) {
					if (!json.processor_success)
					{
						modal_message({message: json.processor_message});

						$(pref_elem).val($(this).data('user_pref_orig'));
					}
					else
					{
						$(pref_elem).data('user_pref_orig', $(pref_elem).val());

					}

					$('#' + $(pref_elem).data('user_pref') + '_buttons').slideUp(function (e) {

						$('#' + $(pref_elem).data('user_pref') + '_buttons').remove();

					});
				});
			}else{
				set_user_pref($(pref_elem).data('user_pref'), $(pref_elem).val(), user_id, function (json) {
					if (!json.processor_success)
					{
						modal_message({message: json.processor_message});

						$(pref_elem).val($(this).data('user_pref_orig'));
					}
					else
					{
						$(pref_elem).data('user_pref_orig', $(pref_elem).val());

					}

					$('#' + $(pref_elem).data('user_pref') + '_buttons').slideUp(function (e) {

						$('#' + $(pref_elem).data('user_pref') + '_buttons').remove();

					});
				});
			}


		});

		var cancel_button = $('<span />').addClass('btn btn-sm btn-primary mt-2').text('Cancel').on('click', function (e) {

			$(pref_elem).val($(pref_elem).data('user_pref_orig'));

			$('#' + $(pref_elem).data('user_pref') + '_buttons').slideUp(function (e) {

				$('#' + $(pref_elem).data('user_pref') + '_buttons').remove();

			});

		});

		$(buttons).prepend(cancel_button).prepend(save_button).slideDown();

	}

});

// handle thaw reminder
$(document).on('click', '[data-user_pref="text_message_thaw_primary"][type=checkbox]', function (e) {

	// var user_id = 0;
	// var value = $(this).is(':checked');
	//
	// if ($(this).data('user_id'))
	// {
	// 	user_id = $(this).data('user_id');
	// }
	//
	// $.ajax({
	// 	url: '/processor',
	// 	type: 'POST',
	// 	timeout: 20000,
	// 	dataType: 'json',
	// 	data: {
	// 		processor: 'account',
	// 		op: 'optin',
	// 		type: 'thaw',
	// 		phone: 'primary',
	// 		user_id: user_id,
	// 		value: value.toString()
	// 	},
	// 	success: function (json, status) {
	//
	// 	},
	// 	error: function (objAJAXRequest, strError) {
	// 		response = 'Unexpected error';
	// 	}
	// });

});

$(function() {
	// clipboard
	if (typeof ClipboardJS !== 'undefined')
	{
		let clipboard = new ClipboardJS('.btn-clip');
	}
});