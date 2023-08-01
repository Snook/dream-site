function admin_list_stores_init()
{
	$('#store').change(function () {

		bounce('main.php?page=admin_store_details&id=' + $(this).val());

	});
}