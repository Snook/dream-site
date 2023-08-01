function reports_customer_init()
{
	store_qty();
}


function _report_submitClick(form)
{
	if ($('#no_results_msg').length)
		$('#no_results_msg').hide();

	return true;
}




function store_qty()
{
	$('[id^="drsw_"]').each(function(){

		if ($.totalStorage(this.id) != null)
		{
			if ($.totalStorage(this.id) == "1")
			{
				this.checked = true;
			}
			else
			{
				this.checked = false;
			}
		}

	});

	$('[id^="drsw_"]').bind("keyup change", function(e){

		if (this.checked)
		{
			$.totalStorage(this.id, "1");
		}
		else
		{
			$.totalStorage(this.id, "0");
		}

	});

}

