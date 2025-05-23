function reports_fin_perf_init()
{
	store_qty();


	$('#result_frame').on("load", function(){
		$('#result_div').show();
		$("#fin_perf_run").remove();

	});

	$('#export').val('');


}

function submitMe()
{
	if ($('#export').val() != 'xlsx')
	{
	    $("#fin_perf_form").prop("target", "result_frame");
		$('#export').val('');

		if ($("#show_comparisons").is(":checked") && $("#date_typesingle_month").is(":checked"))
		{
			displayModalWaitDialog("fin_perf_run", "Comparison data is being computed. This may take a minute or two. Thank you for your patience.");
		}

	}

	return true;

}

function export_report()
{
    $("#fin_perf_form").prop("target", "");
	$('#export').val('xlsx');
	$('#fin_perf_form').submit();

	$('#export').val('');


}

function store_qty()
{
	$('#show_comparisons, #use_percent_of_agr').each(function(){

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

	$("#show_comparisons, #use_percent_of_agr").bind("keyup change", function(e){

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

