<?php
// constants for all report pages.
$REPORTGIF = "page_header_financstatsreport.gif";
$PAGETITLE = "Financial Statistical Report";
$HIDDENPAGENAME = "admin_reports_financial_statistic_v2";
$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
$ON_SUBMIT="return override_check_form(this);";
$this->assign('helpLinkSection','FS');
include $this->loadTemplate('admin/page_header_reports.tpl.php');
include $this->loadTemplate('admin/reports_form.tpl.php');
?>

<script type="text/javascript">
function override_check_form(form) 
{ 
	$('#no_result_msg').hide();
	 return _check_form(form); 
}
</script>

<?php if (isset($this->empty_result) && $this->empty_result) { ?>
		<table><tr><td width="610" class="headers" style="padding-left: 5px;" colspan="5">
					<span id="no_result_msg" style="font-weight:bold">Sorry, could not generate a report for this date.</span>
			</td></tr></table>
<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
