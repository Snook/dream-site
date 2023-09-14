function session_tools_printing_init()
{
	handle_submit_print();
}

function handle_submit_print()
{
	$('[data-print_menu]').bind('change', function() {

		$('#submit_menus').addClass('disabled');

		$('[data-print_menu]').each(function() {

			if ($(this).is(':checked'))
			{
				$('#submit_menus').removeClass('disabled');
			}

		});

	});

	$('#submit_menus').on('click', function () {

		if ($(this).hasClass('disabled'))
		{
			return;
		}

		menu_id = $('#menus').val();

		var print_menu = '';

		$('[data-print_menu]').each(function() {

			if ($(this).is(':checked'))
			{
				print_menu += '&' + $(this).data('print_menu') + '=true';
			}

		});

		bounce('/?page=admin_session_tools_printing&do=print&menu=' + menu_id + '&store_id=' + store_id + print_menu, 'print_menus');

	});

}