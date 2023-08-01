function manage_dream_event_properties_init()
{
	handle_view_details();
}

function handle_view_details()
{
	$('.view_items').on('click', function () {

		var dtep_id = $(this).data('dtep_id');

		$('[data-properties]').hide();
		$('.selected').not(this).removeClass('selected');

		if ($(this).hasClass('selected'))
		{
			$(this).removeClass('selected');
		}
		else
		{
			$(this).addClass('selected');

			$('[data-properties="' + dtep_id + '"]').show();
		}


	});
}
