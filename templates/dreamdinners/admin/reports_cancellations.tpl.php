<?php
$REPORTGIF = NULL;
$PAGETITLE = "Cancellation Report";
$HIDDENPAGENAME = "admin_reports_cancellations";
$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>

<p>This report will return a list of customers that have canceled session(s) during the selected timespan.
Please note: The Excel document will return additional information such as phone, call time, sessions attended.</p>


<?php
include $this->loadTemplate('admin/reports_form.tpl.php');
?>


<?php if (isset($this->empty_result) && $this->empty_result) { ?>
		<table><tr><td width="610" class="headers" style="padding-left: 5px;" colspan="5"><b>Sorry, could not generate a report for this date.</b></td></tr></table>
<?php }
?>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>