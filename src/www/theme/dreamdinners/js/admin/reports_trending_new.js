
function trending_report_init()
{


}

function print_trending_report()
{
	var targ = $('#reports_trending_form')[0].target;
	var act = $('#reports_trending_form')[0].action;
	$('#reports_trending_form')[0].target = "_print";
	$('#reports_trending_form')[0].action = act + "&print=true";

	$('#reports_trending_form').submit();

	$('#reports_trending_form')[0].target = targ;
	$('#reports_trending_form')[0].action = act;

}

function export_trending_report()
{
	$('#reports_trending_form').submit();
}

