var timeframe = 60; // minutes
var checkperiod = 10; // seconds
var last_time = (((new Date).getTime() / 1000) - (timeframe * 60));
var map = null;
var markers = [];
var hasrun = false;

function initMap()
{
	var country = "United States";

	var myOptions = {
		zoom: 5,
		mapTypeId: 'roadmap'
	};

	// create map
	map = new google.maps.Map(document.getElementById("map"), myOptions);

	// center map on country
	var geocoder = new google.maps.Geocoder();
	geocoder.geocode({'address': country}, function (results, status) {
		if (status == google.maps.GeocoderStatus.OK)
		{
			map.setCenter(results[0].geometry.location);
		}
		else
		{
			alert("Could not find location: " + location);
		}
	});

	// start loop
	map_activity_timer();
}

function map_activity_timer()
{
	// cancel existing timer if there is one
	$.doTimeout('map_activity_timer');

	// check every checkperiod for new activity
	$.doTimeout('map_activity_timer', (checkperiod * 1000), function () {

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 60000,
			dataType: 'json',
			data: {
				processor: 'admin_reports_map_activity',
				last_time: last_time
			},
			success: function (json, status) {

				time_in = 1;

				$.each(json.data, function (key) {

					// first load drops pins separately
					time_in++;

					// store data
					var marker_data = this;

					// set latest timestamp from data
					if (this.timestamp_updated_unix > last_time)
					{
						last_time = this.timestamp_updated_unix;
					}

					setTimeout(function (expire) {
						set_marker(marker_data, PATH.image + '/style/logo/heart-grn-75x55.png', 0);
						console.log((new Date).toTimeString().slice(0, 8) + ': Adding marker #' + marker_data.id);
					}, time_in * 200);

					fadmin_is_idle(false);

				});

			},
			error: function (objAJAXRequest, strError) {
				// error
			}

		});

		// prevent fadmin logout
		if (USER_LOGGEDIN && $.cookie('DreamSite_TO'))
		{
			TO = $.cookie('DreamSite_TO');

			fadmin_is_idle(false);
		}

		// loop doTimeout
		return true;

	});

	// call it immediately
	$.doTimeout('map_activity_timer', true);
}

function sort_list()
{
	$("#event").each(function () {
		$(this).html($(this).children('li').sort(function (a, b) {
			return ($(b).data('time')) > ($(a).data('time')) ? 1 : -1;
		}));
	});
}

function set_marker(marker_data, icon, zindex)
{
	// create marker
	var marker = new google.maps.Marker({
		animation: google.maps.Animation.DROP,
		position: {
			lat: marker_data.lat,
			lng: marker_data.lon
		},
		map: map,
		icon: icon,
		zIndex: zindex
	});

	// create event
	create_event(marker_data, icon);

	// figure out expire time
	var time_now = (new Date).getTime();
	var expire = ((marker_data.timestamp_updated_unix * 1000) + (timeframe * 60 * 1000)) - time_now;

	// store marker info
	markers[marker_data.id] = {
		'marker': marker,
		'expire': expire,
		'marker_data': marker_data
	};

	// delete marker
	setTimeout(function (expire) {
		markers[marker_data.id].marker.setMap(null);
		$('#' + marker_data.id).slideUp("normal", function () {
			$(this).remove();
		});
		update_servings(marker_data, true);
		update_foundation(marker_data, true);
		console.log((new Date).toTimeString().slice(0, 8) + ': Removing marker #' + marker_data.id);
	}, expire);
}

function create_event(marker_data, icon)
{
	// create order event
	$('<li id="' + marker_data.id + '" data-time="' + marker_data.timestamp_updated_unix + '" class="event row py-1 m-0 border-bottom cursor-pointer">').html('<div class="col-2 align-self-center"><img src="' + icon + '" /></div><div class="col-10"><div class="font-weight-bold">' + marker_data.firstname + ' ' + marker_data.lastname_letter + '</div><div>' + marker_data.store_name + ', ' + marker_data.state + '</div><div class="text-muted font-size-small">' + marker_data.date_time + '</div></div>').prependTo($('#event')).hide().slideDown();

	// update servings
	update_servings(marker_data, false);

	// update foundation
	update_foundation(marker_data, false);

	sort_list();
}

function update_servings(marker_data, remove)
{
	if (remove == false)
	{
		var new_serving_total = $('#servings').data('total') + marker_data.servings_total_count;
	}
	else
	{
		var new_serving_total = $('#servings').data('total') - marker_data.servings_total_count;
	}

	$('#servings').data('total', new_serving_total);
	$('#servings').text(new_serving_total.toLocaleString("en"));
}

function update_foundation(marker_data, remove)
{
	if (remove == false)
	{
		var new_foundation_total = $('#foundation').data('total') + marker_data.ltd_round_up_value + marker_data.subtotal_ltd_menu_item_value;
	}
	else
	{
		var new_foundation_total = $('#foundation').data('total') - marker_data.ltd_round_up_value - marker_data.subtotal_ltd_menu_item_value;
	}

	$('#foundation').data('total', new_foundation_total);
	$('#foundation').text(Math.floor(new_foundation_total / .25).toLocaleString("en"));
}

function toggleBounce(id)
{
	if (markers[id].marker.getAnimation() !== null)
	{
		markers[id].marker.setAnimation(null);
		$('#' + id).removeClass('border-width-2-imp border-green');
	}
	else
	{
		markers[id].marker.setZIndex(100);
		markers[id].marker.setAnimation(google.maps.Animation.BOUNCE);
		$('#' + id).addClass('border-width-2-imp border-green');
	}
}

$(document).on("click", ".event", function () {

	toggleBounce(this.id);

});