<?php

	// constants for all report pages.
	$REPORTGIF = null;
	$PAGETITLE = "Weekly Summary Report";
	$HIDDENPAGENAME = "admin_reports_dream_weekly_v2";
	$SHOWSINGLEDATE=FALSE;
	$SHOWRANGEDATE=TRUE;
	$SHOWMONTH=TRUE;
	$SHOWYEAR=FALSE;
	$varChecked = "";
	include $this->loadTemplate('admin/page_header_reports.tpl.php');
	include $this->loadTemplate('admin/reports_form.tpl.php');

?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>