function nutritional_summary_init()
{
	$('#print_nutrition_summary').on('click', function (e) {

		show_entree = 0;
		show_efl = 0;
		show_ft = 0;
		filter_zero_inventory = 0;

		if ( $('#show_entree').is(':checked') )
		{
			show_entree = 1;
		}

		if ( $('#show_efl').is(':checked') )
		{
			show_efl = 1;
		}

		if ( $('#show_ft').is(':checked') )
		{
			show_ft = 1;
		}

		if ( $('#filter_zero_inventory').is(':checked') )
		{
			filter_zero_inventory = 1;
		}

		bounce('/nutritionals?store=' + $('#store').val() + '&menu=' + $('#menus_dropdown').val() + '&show_entree=' + show_entree + '&show_efl=' + show_efl + '&show_ft=' + show_ft + '&filter_zero_inventory=' + filter_zero_inventory + '&print=1', 'print');

	});
}