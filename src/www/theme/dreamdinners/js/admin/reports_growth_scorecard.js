function reports_growth_scorecard_init()
{
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
	    $("#growth_scorecard_form").prop("target", "result_frame");
	}

	return true;
}

function export_report()
{
    $("#growth_scorecard_form").prop("target", "");
	$('#export').val('xlsx');
	$('#growth_scorecard_form').submit();
	$('#export').val('');
}
