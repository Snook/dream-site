function admin_list_stores_init()
{
	$('#store').change(function () {

		bounce('/?page=admin_store_details&id=' + $(this).val());

	});
}