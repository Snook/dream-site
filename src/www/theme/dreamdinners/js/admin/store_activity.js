function store_activity_init()
{
	init_action_handlers();
}


function init_action_handlers(){
	$(".show_edit_notes").click(function (e) {
		e.preventDefault();
		let index = $(this).data('index');
		let header = $(this);

		const content = $(document).find(`.edit_note_content[data-index='${index}']`)
		header.html(function () {
			return (content.is(":visible") ? "&bigtriangledown;" : "&bigtriangleup;"  );
		});
		content.slideToggle(500, function () {});

	});


	// dream taste buttons
	$('.sd_invitation_pdf').on('click', function (e) {
		e.preventDefault();
		let selected_session_id = $(this).data('session');
		bounce('?page=print&dream_taste_event_pdf=' + selected_session_id, '_blank');

	});

	// fundraiser buttons
	$('.sd_fundraiser_invitation_pdf').on('click', function (e) {
		e.preventDefault();
		let selected_session_id = $(this).data('session');
		bounce('?page=print&fundraiser_event_pdf=' + selected_session_id, '_blank');

	});

	// community pickup buttons
	$('.sd_pick_up_event_invitation_pdf').on('click', function (e) {
		e.preventDefault();
		let selected_session_id = $(this).data('session');
		bounce('?page=print&remote_pickup_private_event_pdf=' + selected_session_id, '_blank');

	});

	$('.sd_resend_hostess_email').on('click', function (e) {
		e.preventDefault();
		let store_id = $(this).data('store_id');
		let session_id = $(this).data('session');
		resend_dream_taste_notification(store_id, session_id);

	});
}

function resend_dream_taste_notification(store_id, session_id)
{
	dd_message({
		title: 'Send notice confirmation',
		message: 'Are you sure you wish to resend the host email notification?',
		modal: true,
		confirm: function () {
			$.ajax({
				url: 'ddproc.php',
				type: 'POST',
				timeout: 20000,
				dataType: 'json',
				data: {
					processor: 'admin_fadmin_home',
					op: 'resend_dream_taste_email',
					store_id: store_id,
					session_id: session_id
				},
				success: function (json) {
					if (json.processor_success)
					{
						dd_message({
							title: 'Send notice confirmation',
							message: 'Email notification has been resent to the session host.'
						});
					}
				},
				error: function (objAJAXRequest, strError) {
					response = 'Unexpected error';
				}
			});
		}
	});
}