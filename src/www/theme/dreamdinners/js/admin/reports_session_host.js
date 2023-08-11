function reports_session_host_init()
{
	$('#result_frame').on("load", function(){
		$('#result_div').show();

	});

	$('#export').val('');

}

function submitMe()
{
	if ($('#export').val() != 'xlsx')
	{
	    $("#frm").prop("target", "result_frame");
	}

	$("#empty_result_msg").hide();
	return true;
}

function export_report()
{
    $("#frm").prop("target", "");
	$('#export').val('xlsx');
	$('#frm').submit();
	$('#export').val('');
}
