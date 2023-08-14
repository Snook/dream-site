$(document).on('click','#enroll_in_plate_points', function (e) {
	if( $('#plate-points-fields').hasClass('collapse')){
		$('#plate-points-fields').removeClass('collapse')
	}else{
		$('#plate-points-fields').addClass('collapse')
	}
});



$(document).on('click','#handle-plate-points-enroll', function (e) {
	let user_id = $(this).data('user_id');
	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'plate_points_processor',
			user_id: user_id,
			birth_month: $('#birthday_month').find(":selected").val(),
			birth_year: $('#birthday_year').find(":selected").val(),
			op: 'opt_in_with_birthday'
		},
		success: function (json, status) {
			if (json.processor_success)
			{
				$('#plate-points-fields').addClass('collapse');
				$('#plate-points-checkbox').addClass('collapse');

				dd_message({
					title: 'PlatePoints Enrollment Complete',
					message: 'You are now enrolled in the PlatePoints program.'
				});
			}
			else
			{
				dd_message({
					title: 'Error',
					message: 'There was a problem opting into the PlatePoints program.'
				});

			}
		},
		error: function (objAJAXRequest, strError) {
			response = 'Unexpected error';
		}
	});
});




$(document).on('click','#handle-cancel-delivered-order', function (e) {

	var session_id = $(this).data('session_id');
	var order_id = $(this).data('order_id');
	var store_id = $(this).data('store_id');
	var user_id = $(this).data('user_id');
	var bounce_to = $(this).data('bounce');

	var spinner;

	$.ajax({
		url: 'ddproc.php',
		type: 'POST',
		timeout: 20000,
		dataType: 'json',
		data: {
			processor: 'delivered_cancel_order',
			op: 'cancel_preflight',
			store_id: store_id,
			session_id: session_id,
			user_id: user_id,
			order_id: order_id
		},
		success: function (json, status) {
			if (json.processor_success && json.data)
			{

				dd_message({
					title: 'Cancel Order?',
					message: json.data,
					size: 'large',
					modal: true,
					confirm: function (json, status) {

						var reason = $("#cancellation_reason").val();
						if (reason == "")
						{
							dd_message({
								title: '',
								div_id: 'Uneek262020',
								message: "Please select a reason for cancelling"});

							return false;
						}

						var paymentList = {};
						$("#cancel_parent input:hidden").each(function () {

							paymentList[this.id] = $(this).val();

						});

						var reason = $("#cancellation_reason").val();


						spinner = bootbox.dialog({
							message: '<p class="text-center mb-0"><i class="fa fa-spin fa-cog"></i> Cancelling the order...</p>',
							closeButton: false
						});

						// do something in the background


						$.ajax({
							url: 'ddproc.php',
							type: 'POST',
							timeout: 30000,
							dataType: 'json',
							data: {
								processor: 'delivered_cancel_order',
								op: 'cancel',
								store_id: store_id,
								session_id: session_id,
								user_id: user_id,
								order_id: order_id,
								paymentList: paymentList,
								reason: reason
							},
							success: function (json) {
								spinner.remove();
								if (json.processor_success)
								{

									dd_message({
										title: 'Order was Canceled',
										message: json.data,
										div_id: 'cancel_order',
										closeOnEscape: false,
										closeButton: false,
										modal: true,
										noOk: true,
										buttons: {
											"OK": function () {
													bounce('main.php?page=my_meals&tab=nav-past_orders');
											}
										}
									});
								}
								else
								{
									dd_message({
										title: 'Error Cancelling Order',
										message: json.processor_message
									});

								}
							},
							error: function (objAJAXRequest, strError) {
								console.log('Unexpected error: ' + strError);
								spinner.remove();
								dd_message({
									title: 'Error Cancelling Order',
									message: 'Sorry, their was an error processing your order cancellation.',
									buttons: {
										"OK": function () {
											bounce('main.php?page=my_meals&tab=nav-past_orders');
										}
									}
								});

							}

						});
					}
				});
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

});