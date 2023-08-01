<?php
$REPORTGIF = NULL;
$PAGETITLE = "Report Usage Report";
$HIDDENPAGENAME = "admin_reports_report_usage";
$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>




<?php
include $this->loadTemplate('admin/reports_form.tpl.php');
?>


<?php if (isset($this->empty_result) && $this->empty_result) { ?>
		<table><tr><td width="610" class="headers" style="padding-left: 5px;" colspan="5"><b>Sorry, could not generate a report for this date.</b></td></tr></table>
<?php }
?>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>