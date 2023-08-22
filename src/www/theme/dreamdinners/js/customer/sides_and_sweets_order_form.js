$(document).on('change', '#orders', function (e) {

	bounce('main.php?page=sides_and_sweets_order_form&id=' + $(this).val());

});

$(document).on('change keyup', '[data-menu_item_id]', function (e) {

	$('[data-menu_item_id]').each(function ()
	{
		if (Number($(this).val()) > Number($(this).prop('max')))
		{
			$(this).val($(this).prop('max'));

			modal_message({message: 'Amount exceeded maximum available inventory of ' + $(this).prop('max') + '.'})
		}
		else if ($(this).val() == '' && $(this).not(':focus').length)
		{
			$(this).val(0);
		}
	});

	let total_items = 0;
	let sub_total = 0.00;

	$('[data-menu_item_id]').each(function ()
	{
		total_items += Number($(this).val());

		if (Number($(this).val()) > 0)
		{
			sub_total += Number($(this).data('price')) * Number($(this).val());
		}
	});

	$('#totalItems').text(total_items);
	$('#total').text(formatAsMoney(sub_total));

	$('#submit').prop({'disabled': true});
	if (total_items > 0)
	{
		$('#submit').prop({'disabled': false});
	}

});