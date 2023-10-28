function my_account_init()
{
	handle_rsvps();
}
//
function handle_rsvps()
{
	$('.cancel_session_rsvp').each(function () {
		$(this).on('click', function (e) {
			var user_id = $(this).data('user_id');
			var session_id = $(this).data('session_id');

			modal_message({
				title: 'Confirmation',
				message: 'Are you sure you wish to cancel this RSVP?',
				modal: true,
				confirm: function () {
					$.ajax({
						url: '/processor',
						type: 'POST',
						timeout: 20000,
						dataType: 'json',
						data: {
							processor: 'session_rsvp',
							user_id: user_id,
							session_id: session_id,
							op: 'delete_rsvp'
						},
						success: function (json, status) {
							if (json.processor_success)
							{
								$('#session_rsvp-' + session_id + '-' + user_id).remove();

								dd_toast({'message': 'RSVP has been canceled.'});
							}
							else
							{
								modal_message({
									title: 'Error',
									message: json.processor_message
								});

							}
						},
						error: function (objAJAXRequest, strError) {
							response = 'Unexpected error';
						}

					});
				}
			});
		});
	});
}