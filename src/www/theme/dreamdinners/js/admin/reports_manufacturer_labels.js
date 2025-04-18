function reports_manufacturer_labels_init()
{
	search_filter();

	store_qty();

	clear_qty();
}

function store_qty()
{
	// restore values from storage
	$('[id^="large-"],[id^="medium-"],#print_label_type').each(function(){

		if ($.totalStorage(this.id) != 'null' && $.totalStorage(this.id) != null && $.totalStorage(this.id).length != 0)
		{
			this.value = $.totalStorage(this.id);
		}

	});

	// on change put value in storage
	$('[id^="large-"],[id^="medium-"]').bind("keyup change", function(e){

		if ($.isNumeric(this.value) || this.value.length == 0)
		{
			$.totalStorage(this.id, this.value);
		}
		else
		{
			this.value = '';
		}

		count_labels();
	});

	// on change put value in storage
	$('#print_label_type').change(function(e){

		$.totalStorage(this.id, this.value);

	});

	count_labels();
}

function clear_qty()
{
	$('#clear_medium').on('click', function (){

		$('[id^="medium-"]').val('').change();

	});

	$('#clear_large').on('click', function (){

		$('[id^="large-"]').val('').change();

	});
}

function count_labels()
{
	numlabels = 0;

	$('[id^="large-"],[id^="medium-"]').each(function(){

		if ($.isNumeric(this.value))
		{
			numlabels += Number(this.value);
		}

	});

	if (numlabels > 0)
	{
		$('#generate_nutritional_labels,#generate_cooking_instructions').prop('disabled', false);
	}
	else
	{
		$('#generate_nutritional_labels,#generate_cooking_instructions').prop('disabled', true);
	}

	$('#num_sheets').html(Math.ceil(numlabels / 6).toString());
	$('#num_labels').html(numlabels.toString());
}

function search_filter()
{
	if (getQueryVariable('s'))
	{
		search = getQueryVariable('s');

		$('#filter').val(search).change();
		$.uiTableFilter($('#recipe_list'), search);
	}

	$('#filter').bind('keyup change', function(e){
		$.uiTableFilter($('#recipe_list'), this.value);

		new_query_string = setQueryString('s', this.value);
		historyPush({ url: new_query_string });
	});

	$('#clear_filter').on('click', function ()
	{
		$('#filter').val('').change();

	});
}
