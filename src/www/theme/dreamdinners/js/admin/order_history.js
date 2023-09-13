function order_history_init(currentUser)
{
	handle_order_notes_click();
	handle_order_details_click();

	$('#orders_history_table').stickyTableHeaders();

	handle_cancel_order();
	handle_cancel_order_delivered();
	handle_delete_saved_order();
	handle_reschedule_button();

	restorePagingLocation(currentUser);
	handlePaging();
}

function handle_order_details_click()
{
	$('[data-view_order_details]').each(function ()
	{

		if ($(this).hasClass('disabled'))
		{
			return;
		}

		// handle view order details
		$(this).on('click', function (e)
		{
			e.preventDefault();

			var order_id = $(this).data('view_order_details');
			var booking_id = $(this).data('booking_id');

			// hide existing open order
			$('[data-view_order_details_table]').parent().hide();

			if ($(this).data('is_shown') == true)
			{
				historyPush({url: '?' + (getQueryVariable('page') ? 'page=' + getQueryVariable('page') + '&' : '') + 'id=' + getQueryVariable('id')});
				$(this).text('View Order').data('is_shown', false);
				return;
			}

			$('[data-view_order_details]').text('View Order').data('is_shown', false);

			// show this order
			$('[data-view_order_details_table="' + booking_id + '"]').parent().show();
			$(this).text('Close Order').data('is_shown', true);

			// scroll to guest details
			$('html, body').animate({

				scrollTop: $('[data-view_order_details="' + order_id + '"]').offset().top - 40

			}, 500);

			// cancel scroll if user manually interrupts the scroll
			$('html, body').on('scroll mousedown DOMMouseScroll mousewheel keyup', function (e)
			{

				$('html, body').stop();

			});

			$.ajax({
				url: '/processor',
				type: 'POST',
				timeout: 60000,
				dataType: 'json',
				data: {
					processor: 'admin_fadmin_home_order_details',
					op: 'order_details',
					order_id: order_id,
					booking_id: booking_id
				},
				success: function (json)
				{
					if (json.processor_success)
					{
						$('[data-view_order_details_table="' + json.booking_id + '"]').html(json.order_details_table +json.history_table );

						historyPush({url: '?' + (getQueryVariable('page') ? 'page=' + getQueryVariable('page') + '&' : '') + 'id=' + getQueryVariable('id') + '&order=' + json.order_id});
					}
				},
				error: function (objAJAXRequest, strError)
				{
					response = 'Unexpected error';
				}
			});

		});

		// handle closing of order details
		$(document).on("click",'.close_order_details_table', function (e)
		{

			$('#order_details_tbody_id_' + $(this).data('booking_id')).hide();

			historyPush({url: '?' + (getQueryVariable('page') ? 'page=' + getQueryVariable('page') + '&' : '') + 'id=' + getQueryVariable('id')});

		});

	});

	if (getQueryVariable('order'))
	{
		$('[data-view_order_details="' + getQueryVariable('order') + '"]').trigger('click');
	}
}

function handle_order_notes_click()
{
	$('[id^="view_notes-"]').each(function ()
	{

		$(this).unbind('click').on('click', function ()
		{

			if ($(this).hasClass('selected'))
			{
				$(this).removeClass('selected');

				$('[id^="order_user_notes-"], [id^="order_admin_notes-"]').hide();
			}
			else
			{
				$('.button.selected').removeClass('selected');

				$('[id^="order_user_notes-"], [id^="order_admin_notes-"]').hide();

				$(this).addClass('selected');

				$('#order_user_notes-' + $(this).data('order_id') + ', #order_admin_notes-' + $(this).data('order_id')).show();
			}

		});

	});
}

function handlePaging(){

	//***** Paging Controls
	$(document).on("click", ".orders-page-prev", function (event) {
		event.preventDefault();

		let user_id = $(this).data('user');
		let current_page = $(this).data('current');

		let cached = current_page;

		if(cached > 0){
			cached = cached -2;
		}

		localStorage.setItem('orders-paging-current',cached);
		localStorage.setItem('history-paging-user',user_id);

		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: current_page,
				processor: 'admin_order_history',
				op: 'prev'
			},
			success: function (json, status) {


				if (json.processor_success)
				{
					$('#order_history').html(json.html);
					handle_order_details_click();
					handle_order_notes_click();
					handle_cancel_order();
					handle_cancel_order_delivered();
					handle_delete_saved_order();
					handle_reschedule_button();
					$('#orders_history_table').stickyTableHeaders();

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

	$(document).on("click", ".orders-page-next",function (event) {
		event.preventDefault();


		let user_id = $(this).data('user');
		let current_page = $(this).data('current');

		localStorage.setItem('orders-paging-current',current_page);
		localStorage.setItem('history-paging-user',user_id);
		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: current_page,
				processor: 'admin_order_history',
				op: 'next'
			},
			success: function (json, status) {


				if (json.processor_success)
				{
					$('#order_history').html(json.html);
					handle_order_details_click();
					handle_order_notes_click();
					handle_cancel_order();
					handle_cancel_order_delivered();
					handle_delete_saved_order();
					handle_reschedule_button();
					$('#orders_history_table').stickyTableHeaders();

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
}


function restorePagingLocation(currentUser)
{
	let page = localStorage.getItem('orders-paging-current');
	let user_id = localStorage.getItem('history-paging-user');

	if(user_id != currentUser){
		localStorage.setItem('orders-paging-current',null);
		localStorage.setItem('history-paging-user',null);

		return;
	}

	if (page != null && typeof page !== 'undefined' && user_id != null && typeof user_id !== 'undefined'){
		$('#order_history').html('');
		$.ajax({
			url: '/processor',
			type: 'POST',
			timeout: 100000000,
			dataType: 'json',
			data: {
				user_id: user_id,
				page: page,
				processor: 'admin_order_history',
				op: 'next'
			},
			success: function (json, status) {

				if (json.processor_success)
				{
					$('#order_history').html(json.html);
					handle_order_details_click();
					handle_order_notes_click();
					handle_cancel_order();
					handle_cancel_order_delivered();
					handle_delete_saved_order();
					handle_reschedule_button();
					$('#orders_history_table').stickyTableHeaders();

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