Excel<?php
// constants for all report pages.
//$REPORTGIF = "page_header_entreereport.gif";
$PAGETITLE = $this->page_title;
$HIDDENPAGENAME = "admin_reports_made_for_you";
$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
$FORMACTION=$_SERVER['REQUEST_URI'] . "&export=xlsx";

include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>

<?php if ($_REQUEST['MFY_Report_Type'] == 1) { ?>
<p>
This report lists all Made For You (Special Event) sessions for the selected time span. The exported Excel file lists the store name, home office ID, store city and state and the session times.
The report can be filtered to a single store or displa all stores.
</p>
<?php } else if ($_REQUEST['MFY_Report_Type'] == 3) { ?>
<p>
This report lists for each session type (Standard and Made for You) the number of sessions (only session with one more guests are included) and the total number of guests.
</p>
<?php } else if ($_REQUEST['MFY_Report_Type'] == 4) { ?>
<p>
This report lists every guest and guest's order amount for the selected time span. The results can be for all Guests, only Made for You guests or only Standard Session guests. The order amount is shown
both with and without taxes included.
</p>
<?php } ?>



<script type="text/javascript">
<!--
	function _report_submitClick()
	{
		var resultsElem = document.getElementById("results_mess");
		if (resultsElem)
		 resultsElem.style.display = "none";
	}
//-->
</script>

<?php
include $this->loadTemplate('admin/reports_form.tpl.php');
?>

<div id="results_mess">
<?php if (isset($this->no_results) && $this->no_results) { ?>
<table><tr><td width="610" style="padding-left: 5px;" colspan="5"><b>Sorry, could not generate a report for this date. There were no results for this query.</b></td></tr></table>
<?php } ?>
</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>