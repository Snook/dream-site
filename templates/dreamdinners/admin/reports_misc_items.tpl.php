<?php
// constants for all report pages.
$REPORTGIF = "page_header_miscaddonsrepor.gif";
$PAGETITLE = "Misc Add-ons Report";
$HIDDENPAGENAME = "admin_reports_misc_items";
$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=FALSE;
include $this->loadTemplate('admin/page_header_reports.tpl.php');
include $this->loadTemplate('admin/reports_form.tpl.php');
?>

<style>
.my_report_cell
{
	border-left: thin black solid;
	border-bottom: thin black solid;
	padding: 3px;
}
</style>

<?php if (isset($this->table_data) && count($this->table_data) > 0) { ?>
<script type="text/javascript">
function externalLink()
{

		var sWinHTML = document.getElementById('printer').innerHTML;
		   var winprint=window.open("","");
	       winprint.document.open();
		   winprint.document.write("<html><body onload='window.print();' bgcolor='#537686'><title>Dream Dinners | Misc Item Report</title><table bgcolor='#FFFFFF'><tr><td>");
		   winprint.document.write("<div><div style='margin: 10px; '>");
		   winprint.document.write("<h2>Misc Add-ons Report</h2>");
	       winprint.document.write(sWinHTML);
		   winprint.document.write("</div></div></td></tr></table></body></html>");
	       winprint.document.close();
	       winprint.focus();

}
</script>
<?php } ?>



<?php
if ($this->report_submitted == TRUE) {

	if (isset($this->table_data) && count($this->table_data) > 0)
	{
		$counter = 0;
		$oldMenuID = 0;

		echo "<table  class='report' width='100%' border='0' cellpadding='3' cellspacing='0' >";

		echo '<tr>';

		echo '<td  >&nbsp;</td>';
		echo '<td colspan="2">&nbsp;</td>';

		echo '<td colspan="2" align="right">';
		if (isset($this->store)) {
		 	$exportAllLink = '?page=admin_reports_misc_items&store=' . $this->store . '&day=' . $this->report_day . '&month=' . $this->report_month . '&year=' . $this->report_year . '&duration=' . urlencode($this->report_duration) . '&report_type=' . $this->report_type . '&export=xlsx';
			include $this->loadTemplate('admin/export.tpl.php');
		}
		echo '</td>';


		echo '</tr>';

		echo '<tr>';
		echo '<td colspan="5" class="headers" ><b><center>Misc Add-ons Report</center></b></td>';
		echo '</tr>';


		echo '<tr>';
		echo '<td class="headers"  style="border-bottom:thin black solid;" width="150"><b>Customer</b></td>';
		echo '<td class="headers"  style="border-bottom:thin black solid;"width="160"><b>Session</b></td>';
		echo '<td class="headers"  style="border-bottom:thin black solid;"width="70"><b>Type</b></td>';
		echo '<td class="headers"  style="border-bottom:thin black solid;"width="290"><b>Description</b></td>';
		echo '<td class="headers"  style="border-bottom:thin black solid;"width="40"><b>Price</b></td>';
		echo '</tr>';

		foreach($this->table_data as $thisRow)
		{
			echo '<tr>';
			echo '<td class="my_report_cell">' . $thisRow['customer_name'] . '</td>';
			echo '<td class="my_report_cell">' . $thisRow['session_start'] . '</td>';
			echo '<td class="my_report_cell">' . $thisRow['type'] . '</td>';
			echo '<td class="my_report_cell">' . $thisRow['description'] . '</td>';
			echo '<td class="my_report_cell" style ="border-right:thin black solid">' . $thisRow['price'] . '</td>';
			echo '</tr>';
		}
		echo '</table>';

	}
	else {
		$r_display = '<table><tr><td width="610" style="padding-left: 5px;" colspan="5"><b>Sorry, could not generate a report for this date.</b></td></tr></table>';
		echo $r_display;
	}

}
echo "</div>";
?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>