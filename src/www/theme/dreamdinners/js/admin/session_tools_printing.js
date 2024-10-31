function session_tools_printing_init()
{
	handle_submit_print();
}

function handle_submit_print()
{
	$(document).on('change', '#menus', function (e) {

		if ($(this).val() > 280)
		{
			$('#dream_taste').prop({
				'disabled': true,
				'checked': false
			});
		}
		else
		{
			$('#dream_taste').prop({
				'disabled': false,
			});
		}

	});

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

		bounce('/backoffice/session-tools-printing?do=print&menu=' + menu_id + '&store_id=' + store_id + print_menu, 'print_menus');

	});

}