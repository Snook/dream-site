<?php
$REPORTGIF = NULL;
$PAGETITLE = "Meal Prep Plus Site Wide Report";
$HIDDENPAGENAME = "admin_reports_meal_prep_plus_site_wide";
$SHOWSINGLEDATE=FALSE;
$SHOWRANGEDATE=FALSE;
$SHOWMONTH=FALSE;
$SHOWYEAR=FALSE;
$this->assign('page_title','Meal Prep Plus Site Wide Report');
include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>


<form action="/backoffice/reports-meal-prep-plus-site-wide" name="frm" method="post" onsubmit="$('#empty_result_warning').hide(); return true;">
	<left>

		<!--Select a Valid Menu Month:
	<?php echo  $this->form_session_list['menu_popup_html']; ?>&nbsp;Note: this will select all memberships whose time span includes this month.-->
	<br/><br/>
	<?php echo $this->form_session_list['report_submit_html']; ?>&nbsp;Note: This report includes all Meal Prep+ memberships.  Use the status column to filter to current memberships.
	<?php  if (!empty($this->no_results)) { ?>
		<br/><br/><span id='empty_result_warning' style='font-weight: bold'>Sorry, data isn't available for your query.</span>
	<?php } ?>


	</left>
</form>

<?php include $this->loadTemplate("admin/page_footer.tpl.php"); ?>