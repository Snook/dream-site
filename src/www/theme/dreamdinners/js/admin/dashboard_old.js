var isProcessingMetrics = false;


function trending_report_init()
{
	$('[id^="report_typedt"]').on('click', function () {
		$('#reports_trending_form').submit();
	});




	$('[id^="trade_area"]').change(function() {

		$("#report_typedt_stores_by_region").attr('checked', 'checked');

		if ($(this).val() != 0)
			$('#reports_trending_form').submit();
	});

	$('[id^="store"]').change(function() {

		$("#report_typedt_single_store").attr('checked', 'checked');

		$('#reports_trending_form').submit();
	});

}


function dashboard_init()
{
	$('[id^="report_typedt_"]').on('click', function () {
		$('#dashboard_form').submit();
	});


	$('[id^="rank_met-"]').on('click', function () {

		var metric = this.id.split("-")[1];
		var targetRow = "tr_rank_met-" + metric;
		var targetCell = "td_rank_met-" + metric;
		var disc_triangle = "disc_rank_met-" + metric;

		var curMonth = $('#curMonthStr').val();

		if( $('#'+targetRow).is(':visible') )
		{
			$('#' + targetRow).slideUp('slow');
			$('#' + disc_triangle).addClass("disc_closed");
			$('#' + disc_triangle).removeClass("disc_open");

		}
		else
		{
			$.ajax({
				url: 'ddproc.php?processor=admin_top30',
				type: 'POST',
				dataType : 'json',
				data : {
					metric_type: metric,
					month: curMonth
				},
				success: function(json)
				{
					if (json.result_code == 1)
					{
						$('#' +targetCell).html(json.data);
						$('#' + targetRow).slideDown('slow');

						$('#' + disc_triangle).removeClass("disc_closed");
						$('#' + disc_triangle).addClass("disc_open");

					}
					else
					{
						dd_message({ title: 'Error', message: json.processor_message});

					}
			    },
				error: function(objAJAXRequest, strError)
				{
					dd_message({ title: 'Error', message: strError});
				}
			});
		}

	});
}

function toggleMonth(mode)
{
	// always force the month selector back to default
	$('#override_month').val("0");

	$('#monthMode').val(mode);
	$('#dashboard_form').submit();
}

function handleMetricsUpdateComplete()
{
	$('#dashboard_form').submit();
}


function processMetrics()
{

	var config = {
			resizable: false,
			modal: true,
			title: "Metrics Update!",
			beforeClose: function(event, ui) {return false;}
	};

	$('#busy_updating').dialog(config);

	isProcessingMetrics = true;

	var sid = $("#store_id").val();

	$.ajax({
		url: 'ddproc.php?processor=admin_processMetrics',
		type: 'POST',
		dataType : 'json',
		timeout: 600000,
		data : {
			store_id: sid
		},
		success: function(data)
		{

			isProcessingMetrics = false;

			if (data.result_code == 1)
			{
				handleMetricsUpdateComplete();
			}
	    },
		error: function(objAJAXRequest, strError)
		{
			isProcessingMetrics = false;
			dd_message({ title: 'Error', message: strError});
		}
	});

}

function selectStoreTR(which)
{
	if (which != "")
	{
		$('input:radio[name=report_type]:nth(0)').attr('checked',true);
		$('#reports_trending_form').submit();
	}
}

function selectStore(which)
{
	if (which != "")
	{
		$('input:radio[name=report_type]:nth(0)').attr('checked',true);
		$('#dashboard_form').submit();
	}
}

function print_trending_report()
{
	var targ = $('#reports_trending_form')[0].target;
	var act = $('#reports_trending_form')[0].action;
	$('#reports_trending_form')[0].target = "_print";
	$('#reports_trending_form')[0].action = "?page=admin_reports_trending&print=true";

	$('#reports_trending_form').submit();

	$('#reports_trending_form')[0].target = targ;
	$('#reports_trending_form')[0].action = act;

}

function export_trending_report()
{
	var targ = $('#reports_trending_form')[0].target;
	var act = $('#reports_trending_form')[0].action;
	$('#reports_trending_form')[0].action = "?page=admin_reports_trending&export=xlsx";

	$('#reports_trending_form').submit();

	$('#reports_trending_form')[0].action = act;

}

function export_order_list()
{


		if (!$('#report_typedt_single_store').length || $('input:radio[name=report_type]:nth(0)').attr('checked'))
		{
			var act = $('#dashboard_form')[0].action;
			$('#dashboard_form')[0].action = "?page=admin_dashboard_new&export=xlsx";

			$('#dashboard_form').submit();

			$('#dashboard_form')[0].action = act;
		}
		else
		{
			dd_message({ title: 'Error', message: "The order list can only be exported when viewing a single store"});
		}



}

function print_dashboard()
{
	var targ = $('#dashboard_form')[0].target;
	var act = $('#dashboard_form')[0].action;
	$('#dashboard_form')[0].target = "_print";
	$('#dashboard_form')[0].action = "?page=admin_dashboard_new&print=true";

	$('#dashboard_form').submit();

	$('#dashboard_form')[0].target = targ;
	$('#dashboard_form')[0].action = act;
}