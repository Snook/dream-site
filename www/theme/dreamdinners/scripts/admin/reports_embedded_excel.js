function reports_embedded_excel_init()
{
	$('#result_frame').on("load", function(){
		$('#result_div').show();

		var theSheet = window.frames['result_frame'].document.getElementById('sheet0');

		$(theSheet).css("border", "3px black solid");
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
