function store_details_init()
{

	handle_preview_elements();

	handle_dynamic_store_hours();

	SetGrandOpeningWidget(true);

	if ($('#update_grandopeningdate').length)
	{
		document.getElementById('update_grandopeningdate').checked = false;
	}

	handle_archive_store();

	handle_delete_store();

	handle_empty_latlong();

	handle_address_change();

	$('.telephone').mask('999-999-9999');

	handle_same_as_store();

	handle_order_customization();

	$('#manager_1_user_id').bind('change, keypress, keyup', function (e) {

		if (!$('#manager_1_user_id').val())
		{
			$('#manager_1_name, #manager_1_primary_email, #manager_1_telephone_1').val('');
		}

	});

	$(document).on('keyup', '.previewable', function (e) {

		if ($(this).val() != strip_tags($(this).val(), '<a>'))
		{
			$(this).val(strip_tags($(this).val(), '<a><b>'));
		}

		if ($(this).val() != '')
		{
			$('#' + $(this).attr('id') + '_preview').html(nl2br($(this).val())).slideDown();
		}
		else
		{
			$('#' + $(this).attr('id') + '_preview').slideUp();
		}
	});

	$(document).on('change', '#supports_delayed_payment', function (e) {

		if($(this).is(':checked'))
		{
			$('#default_delayed_payment_deposit, #delayed_payment_order_minimum').prop({disabled: false})
		}
		else
		{
			$('#default_delayed_payment_deposit, #delayed_payment_order_minimum').prop({disabled: true})
		}

	});

	$(document).on('focusout', '#social_twitter, #social_facebook, #social_instagram', function (e) {

		if ($(this).val() == '')
		{
			$(this).val('dreamdinners');
		}

	});
}

function addManager(guest)
{
	$('#manager_1_user_id').val($(guest).data('user_id'));
	$('#manager_1_name').val($(guest).data('firstname') + ' ' + $(guest).data('lastname'));
	$('#manager_1_primary_email').val($(guest).data('primary_email'));
	$('#manager_1_telephone_1').val($(guest).data('telephone_1'));
}

function handle_dynamic_store_hours()
{
	$('.store-hours-selector-open').each(function() {
		createTimeSelection($(this),'open');
	});

	$('.store-hours-selector-close').each(function() {
		createTimeSelection($(this),'close');
	});

	$('.store-closed').each(function() {
		createClosedCheckbox($(this));
	});

	$('#clear-store-hours').on('click',function() {
		$('#bio_store_hours').val('');
		$('#bio_store_hours_preview').hide();
		$('#bio_store_hours_preview')[0].innerHTML = '';
	});

	$('#set-default-hours').on('click',function() {
		populateDefaultStoreHours();
		updateStoreHoursPreview();
	});

	$('#preview-store-hours').on('click',function() {
		updateStoreHoursPreview();
	});

	$('.store-hour-selector').on('change',function() {
		validateSelectedStoreHours($(this));
		updateStoreHoursPreview();
	});

	togglePreview($('#bio_store_hours'));

	if($.trim($('#bio_store_hours').val()) != ''){
		encodeTimeSelection();
	}
}
function validateSelectedStoreHours(element)
{
	let day = element.data('day');
	let open = $('#store-hours-open-'+day).val();
	let close = $('#store-hours-close-'+day).val();

	if($('#store-is-closed-'+day).prop('checked') == false && isOpenBeforeClose(open,close))
	{
		dd_message({
			title: 'Warning',
			message: "The selected closing time is before the selected opening time."
		});
		$('#store-hours-open-'+day).addClass('input_in_error');
		$('#store-hours-close-'+day).addClass('input_in_error');

		return false;
	}
	else if($('#store-is-closed-'+day).prop('checked') == false && isOpenSameAsClose(open,close))
	{
		dd_message({
			title: 'Warning',
			message: "The selected closing time is the same as the selected opening time."
		});
		$('#store-hours-open-'+day).addClass('input_in_error');
		$('#store-hours-close-'+day).addClass('input_in_error');

		return false;
	}
	else
	{
		$('#store-hours-open-'+day).removeClass('input_in_error');
		$('#store-hours-close-'+day).removeClass('input_in_error');
		return true;
	}
}

function isOpenBeforeClose(open,close)
{
	open = open.replace(":", "");
	close = close.replace(":", "");
	open = parseInt(open);
	close= parseInt(close);

	return (open > close);
}

function isOpenSameAsClose(open,close)
{
	return (open === close);
}

function populateDefaultStoreHours()
{
	$('.store-hour-selection-container').each(function() {
		let day = $(this).data('day');
		$('#store-is-closed-'+day).prop('checked', false);
		if( day == 'Sat' || day == 'Sun'){
			$('#store-hours-open-'+day).val('09:00');
			$('#store-hours-close-'+day).val('17:00');
		}else{
			$('#store-hours-open-'+day).val('08:00');
			$('#store-hours-close-'+day).val('19:00');
		}

		$('#store-hours-open-'+day).removeClass('input_in_error');
		$('#store-hours-close-'+day).removeClass('input_in_error');
	});
}

function updateStoreHoursPreview()
{
	let previewDiv = document.getElementById('bio_store_hours_preview');
	if ( previewDiv )
	{
		previewDiv.style.display = 'block';
		previewDiv.innerHTML = nl2br(decodeTimeSelection());
	}
	$('#bio_store_hours').val(decodeTimeSelection());
}

function decodeTimeSelection(){
	let result = '';
	$('.store-hour-selection-container').each(function() {
		let day = $(this).data('day');
		let is_closed = $('#store-is-closed-'+day).is(':checked');
		if( is_closed ){
			result += day + ': Closed\n';
		}else{
			let open = $('#store-hours-open-'+day).val();
			let close =  $('#store-hours-close-'+day).val();
			result += day + ': ' + millitaryToMeridiem(open) + ' - ' + millitaryToMeridiem(close) +'\n';
		}
	});

	return result;
}

function encodeTimeSelection(){
	let currentSelection = $('#bio_store_hours').val();
	let currentSelections = currentSelection.split(/\r?\n/);

	for(let i = 0; i < currentSelections.length;i ++){

		if($.trim(currentSelections[i]) != '')
		{
			let data = currentSelections[i].split(': ');
			let day = data[0]
			if( data[1] == 'Closed'){
				$('#store-is-closed-'+day).prop('checked', true);
			}else{
				let openClose = data[1].split(' - ');
				let open = openClose[0];
				let close = openClose[1];
				$('#store-hours-open-'+day).val(meridiemToMillatary(open));
				$('#store-hours-close-'+day).val(meridiemToMillatary(close));
			}
		}
	}
}

function millitaryToMeridiem(time){
	time = time.split(":");
	let hours = time[0];
	let minutes = time[1];
	let suffix = (hours >= 12)? 'pm' : 'am';
	hours = (hours > 12)? hours -12 : hours;
	hours = (hours == '00')? 12 : hours;

	return hours + ':' + minutes + ' ' +suffix;
}

function meridiemToMillatary(time){
	var hours = Number(time.match(/^(\d+)/)[1]);
	var minutes = Number(time.match(/:(\d+)/)[1]);
	var AMPM = time.match(/\s(.*)$/)[1];
	if(AMPM.toUpperCase() == "PM" && hours<12) hours = hours+12;
	if(AMPM.toUpperCase() == "AM" && hours==12) hours = hours-12;
	var sHours = hours.toString();
	var sMinutes = minutes.toString();
	if(hours<10) sHours = "0" + sHours;
	if(minutes<10) sMinutes = "0" + sMinutes;

	return sHours + ":" + sMinutes;
}

function createTimeSelection(container,type)
{
	let open = $('<select id="store-hours-'+type+'-'+container.data('day')+'" class="store-hour-selector" data-day="'+container.data('day')+'"/>');

	for(let val in time_picker_hours) {
		if(type == 'close' && val == '23:30'){
			$('<option/>', {value: val, text: time_picker_hours[val], selected: 'true'}).appendTo(open);
		}
		else
		{
			$('<option />', {value: val, text: time_picker_hours[val]}).appendTo(open);
		}

	}
	open.appendTo(container);
}

function createClosedCheckbox(container)
{
	let checkbox = $('<input type="checkbox" id="store-is-closed-'+container.data('day')+'" class="store-hour-selector" data-day="'+container.data('day')+'" name="Closed"/> <label for="Closed">Closed</label>');

	checkbox.appendTo(container);
}

function handle_preview_elements()
{
	$('.previewable').each(function() {
		togglePreview($( this ));
	});
}

function handle_order_customization()
{
	$('#supports_meal_customization').on('click', function () {
		toggleOrderCustomization(this.checked);
	});
}

function toggleOrderCustomization(show){
	if(show){
		$(".customization_fields").show();
	}else{
		$(".customization_fields").hide();
	}
}

function handle_same_as_store()
{
	$('#pkg_ship_same_as_store, #letter_ship_same_as_store').on('click', function () {

		var contact = $(this).data('contact');
		var set_disabled = true;

		if (!$(this).is(":checked"))
		{
			set_disabled = false;
		}
		else
		{
			$('#' + contact + '_is_commercial, #' + contact + '_address_line1, #' + contact + '_address_line2, #' + contact + '_city, #' + contact + '_state_id, #' + contact + '_postal_code, #' + contact + '_telephone_day').val(function () {

				return $(this).data('store_value');

			});
		}

		$('#' + contact + '_is_commercial, #' + contact + '_address_line1, #' + contact + '_address_line2, #' + contact + '_city, #' + contact + '_state_id, #' + contact + '_postal_code, #' + contact + '_telephone_day').attr({'disabled': set_disabled});

	});
}

function onPlatePointsOptinChange(obj)
{
	if (obj.checked)
	{
		$("#supports_plate_points_signature_row").show();

		$("#supports_plate_points_signature").attr('data-dd_required', true);
	}
	else
	{
		$("#supports_plate_points_signature_row").hide();

		$("#supports_plate_points_signature").attr('data-dd_required', false);

	}

}

function handle_address_change()
{
	$('#address_line1, #address_line2, #city, #state_id, #postal_code, #usps_adc').bind('keyup change click', function () {

		linear_address = $('#address_line1').val();

		/*
		if ($('#address_line2').val() != '')
		{
			linear_address += ' ' + $('#address_line2').val();
		}
		*/

		linear_address += ', ' + $('#city').val();
		linear_address += ', ' + $('#state_id').val();
		linear_address += ' ' + $('#postal_code').val();

		if ($('#usps_adc').val() != '')
		{
			linear_address += '-' + $('#usps_adc').val();
		}

		$('.gllpSearchField').val(linear_address);
		$('#map_link').prop('href', 'https://maps.google.com/maps?q=' + linear_address + '&iwloc=A&hl=en');

	});
}

function handle_empty_latlong()
{
	if (!$('#address_latitude').is(":disabled"))
	{
		if ($('#address_latitude').val() == '' || $('#address_latitude').val() == '0.000000' || $('#address_longitude').val() == '' || $('#address_longitude').val() == '0.000000')
		{
			dd_message({message: 'Latitude and Longitude have not yet been set for this store, please drag the marker to the front door of the store.'});

			$('.gllpSearchButton').trigger('click');
		}
	}
}

function handle_archive_store()
{
	$('#archive_store').on('click', function () {

		store_name = $(this).data('store_name');
		home_office_id = $(this).data('home_office_id');
		store_id = $(this).data('store_id');

		dd_message({
			title: 'Archive Store',
			message: 'Are you sure you want to archive and re-open ' + store_name + ' #' + home_office_id + '?',
			confirm: function () {
				bounce('/backoffice/archive-store?store=' + store_id);
			}
		});

	});
}

function handle_delete_store()
{
	$('#delete_store').on('click', function () {

		store_id = $(this).data('store_id');

		dd_message({
			title: 'Delete Store',
			message: 'Are you sure you want to <span style="color: red; font-weight: bold;">permanently delete</span> this store from all of Dream Dinners?',
			confirm: function () {
				bounce('/backoffice/store_details?id=' + store_id + '&action=deleteStore');

				create_and_submit_form({
					action: '/backoffice/store_details?id' + store_id,
					input: ({
						action: 'deleteStore',
						id: store_id
					})
				});
			}
		});

	});
}

function updatePreview(textArea)
{
	$("#" + textArea.attr('name') + "_preview").html(textArea.val());
}

function togglePreview(textArea)
{
	$("#" + textArea.attr('name') + "_preview").toggleFlex();

	updatePreview(textArea);
}

function update_grand_opening_function(element)
{
	SetGrandOpeningWidget(!element.checked);
}

function SetGrandOpeningWidget(vDisable)
{
	var visibility = 'hidden';
	if (document.getElementById('grand_opening_date_Month_ID'))
	{
		document.getElementById('grand_opening_date_Month_ID').disabled = vDisable;
	}
	if (document.getElementById('grand_opening_date_Day_ID'))
	{
		document.getElementById('grand_opening_date_Day_ID').disabled = vDisable;
	}
	if (document.getElementById('grand_opening_date_Year_ID'))
	{
		document.getElementById('grand_opening_date_Year_ID').disabled = vDisable;
	}
	if (document.getElementById('grand_opening_date_Month_ID'))
	{
		document.getElementById('grand_opening_date_Month_ID').disabled = vDisable;
	}
	if (vDisable == false)
	{
		visibility = 'visible';
	}
	if (document.getElementById('grand_opening_date_ID_Link'))
	{
		document.getElementById('grand_opening_date_ID_Link').style.visibility = visibility;
	}
}