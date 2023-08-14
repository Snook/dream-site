function edit(id)
{
	$('#tr_Add').hide();
	$('#tr_Edit').show();
	$('#name').val($('#name_' + id).html());
	$('#link').val($('#link_' + id).html());

	$('#edit_id').val(id);
}

function cancel_edit()
{
	$('#tr_Add').show();
	$('#tr_Edit').hide();
	$('#name').val('');
	$('#link').val('');
}