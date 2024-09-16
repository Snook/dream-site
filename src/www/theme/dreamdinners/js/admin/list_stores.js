function admin_list_stores_init()
{
	$('#store').change(function () {

		bounce('/backoffice/store-details?id=' + $(this).val());

	});
}