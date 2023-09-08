function manage_bundle_init()
{
	handle_view_items();

	if (editable)
	{
		handle_edit_bundle();
	}
}

function handle_view_items()
{
	$('.view_items').on('click', function () {

		var bundle_id = $(this).data('bundle_id');

		$('[data-bundle_menu_items]').hide();
		$('.selected').not(this).removeClass('selected');

		if ($(this).hasClass('selected'))
		{
			$(this).removeClass('selected');
		}
		else
		{
			$(this).addClass('selected');

			$('[data-bundle_menu_items="' + bundle_id + '"]').show();
		}


	});
}

function handle_edit_bundle()
{
	$('#bundle_type').on('change', function (e) {

		switch($(this).val()) {
			case 'MASTER_ITEM':
				$('.master_menu_item_option').show();
				break;
			default:
				$('.master_menu_item_option').hide();
		}

	});

	$('[data-bundle_menu_item_id]').on('click', function (e) {

		var bundle_id = $(this).data('bundle_id');
		var menu_item_id = $(this).data('bundle_menu_item_id');
		var state = 0;

		if ($(this).is(':checked'))
		{
			var state = 1;
		}

		$.ajax({
			url: 'ddproc.php',
			type: 'POST',
			timeout: 20000,
			dataType: 'json',
			data : {
				processor: 'admin_manage_bundle',
				op: 'update_bundle_menu_item',
				bundle_id: bundle_id,
				menu_item_id: menu_item_id,
				state: state
			},
			success: function(json)
			{
				if (json.processor_success)
				{

				}
			},
			error: function(objAJAXRequest, strError)
			{
				response = 'Unexpected error';
			}
		});

	});

	$('#menu_id').on('change', function (e) {

		let menu_id = $(this).val();

		if (menu_id)
		{
			bounce('?page=admin_manage_bundle&create&menu=' + menu_id);
		}

	});


}