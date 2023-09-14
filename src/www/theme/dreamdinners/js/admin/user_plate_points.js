function PlatePoints_init(currentUser)
{
	set_platepoints_progress_bar((($('#pp_progressbar').data('percent_complete') == 0) ? 1 : $('#pp_progressbar').data('percent_complete')), false);

	$("#suspend_member").on("click", function () {


		var orderMsg = "";
		if (numConfirmableOrders > 1)
		{
			orderMsg = " " + numConfirmableOrders + " orders will be confirmed.";
		}
		else if (numConfirmableOrders == 1)
		{
			orderMsg = " 1 order will be confirmed.";
		}

		dd_message({
			title: 'Suspend PLATEPOINTS membership',
			div_id: 'aPaBO',
			message: 'Are you sure you want to place this guest on hold?' + orderMsg,
			modal: true,
			width: 400,
			height: 160,
			confirm: function ()
			{
				$("#suspend_form").submit();
			}
		});

		return  false;

	});


	$("#reactivate_member").on("click", function () {

		dd_message({
			title: 'Suspend PLATEPOINTS membership',
			div_id: 'aPaBO',
			message: 'Are you sure want to reinstate this guest?',
			modal: true,
			width: 400,
			height: 160,
			confirm: function ()
			{
				$("#suspend_form").submit();
			}
		});

		return  false;

	});

	//***** Paging Controls
	$(document).on("click", ".points-page-prev", function (event) {
		event.preventDefault();

		let user_id = $(this).data('user');
		let current_page = $(this).data('current');

		let cached = current_page;

		if(cached > 0){
			cached = cached -2;
		}

		localStorage.setItem('history-paging-current',cached);
		localStorage.setItem('history-paging-user',user_id);

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: current_page,
				processor: 'admin_user_plate_points',
				op: 'prev'
			},
			success: function (json, status) {


				if (json.processor_success)
				{
					$('#history').html(json.html);
				}
				else
				{
					alert('NOT OK');
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {

				alert('AJAX ERROR');
				return false;
			}
		});
		return false;
	});

	$(document).on("click", ".points-page-next",function (event) {
		event.preventDefault();


		let user_id = $(this).data('user');
		let current_page = $(this).data('current');

		localStorage.setItem('history-paging-current',current_page);
		localStorage.setItem('history-paging-user',user_id);
		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: current_page,
				processor: 'admin_user_plate_points',
				op: 'next'
			},
			success: function (json, status) {


				if (json.processor_success)
				{
					$('#history').html(json.html);
				}
				else
				{
					alert('NOT OK');
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {

				alert('AJAX ERROR');
				return false;
			}
		});
		return false;
	});

	$(document).on("click", ".dollars-page-prev", function (event) {
		event.preventDefault();

		let user_id = $(this).data('user');
		let current_page = $(this).data('current');

		let cached = current_page;

		if(cached > 0){
			cached = cached -2;
		}

		localStorage.setItem('dollars-history-paging-current',cached);
		localStorage.setItem('dollars-history-paging-user',user_id);

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: current_page,
				processor: 'admin_user_plate_points',
				op: 'dd-prev'
			},
			success: function (json, status) {


				if (json.processor_success)
				{
					$('#dinner_dollar_history_table').html(json.html);
				}
				else
				{
					alert('NOT OK');
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {

				alert('AJAX ERROR');
				return false;
			}
		});
		return false;
	});

	$(document).on("click", ".dollars-page-next",function (event) {
		event.preventDefault();


		let user_id = $(this).data('user');
		let current_page = $(this).data('current');

		localStorage.setItem('dollars-history-paging-current',current_page);
		localStorage.setItem('dollars-history-paging-user',user_id);
		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: current_page,
				processor: 'admin_user_plate_points',
				op: 'dd-next'
			},
			success: function (json, status) {


				if (json.processor_success)
				{
					$('#dinner_dollar_history_table').html(json.html);
				}
				else
				{
					alert('NOT OK');
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {

				alert('AJAX ERROR');
				return false;
			}
		});
		return false;
	});
	restorePagingLocation(currentUser);
}
function restorePagingLocation(currentUser)
{
	let page = localStorage.getItem('history-paging-current');
	let ddpage = localStorage.getItem('dollars-history-paging-current');
	let user_id = localStorage.getItem('history-paging-user');
	let dd_user_id = localStorage.getItem('history-paging-user');

	if(user_id != currentUser){
		localStorage.setItem('history-paging-current',null);
		localStorage.setItem('history-paging-user',null);
		localStorage.setItem('dollars-history-paging-current',null);
		localStorage.setItem('dollars-history-paging-user',null);
		return;
	}

	if (page != null && typeof page !== 'undefined' && user_id != null && typeof user_id !== 'undefined'){
		$('#history').html('');
		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: page,
				processor: 'admin_user_plate_points',
				op: 'next'
			},
			success: function (json, status) {

				if (json.processor_success)
				{
					$('#history').html(json.html);
				}
				else
				{
					alert('NOT OK');
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {

				alert('AJAX ERROR');
				return false;
			}
		});

		$('#dinner_dollar_history_table').html('');
		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: dd_user_id,
				page: ddpage,
				processor: 'admin_user_plate_points',
				op: 'dd-next'
			},
			success: function (json, status) {

				if (json.processor_success)
				{
					$('#dinner_dollar_history_table').html(json.html);
				}
				else
				{
					alert('NOT OK');
				}
				return false;
			},
			error: function (objAJAXRequest, strError) {

				alert('AJAX ERROR');
				return false;
			}
		});
	}
}