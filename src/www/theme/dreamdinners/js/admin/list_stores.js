function admin_list_stores_init()
{
	$('#store').change(function () {

		bounce('/backoffice/store_details?id=' + $(this).val());

	});
}