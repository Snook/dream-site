<?php
$REPORTGIF = NULL;
$PAGETITLE = "Meal Prep Plus Report";
$HIDDENPAGENAME = "admin_reports_meal_prep_plus";
$SHOWSINGLEDATE=FALSE;
$SHOWRANGEDATE=FALSE;
$SHOWMONTH=FALSE;
$SHOWYEAR=FALSE;
$this->assign('page_title','Meal Prep Plus Report');
include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>


<form action="/?page=admin_reports_meal_prep_plus" name="frm" method="post" onsubmit="$('#empty_result_warning').hide(); return true;">
	<left>

	<?php
		if (isset($this->form_session_list['store_html']) ) {
		    echo '<strong>Pick a Store:</strong>' .  $this->form_session_list['store_html'] . '<br/><br/>';
		}
	?>

	Select a Valid Menu Month:
	<?php echo  $this->form_session_list['menu_popup_html']; ?>&nbsp;Note: this will select all memberships whose time span includes this month.
	<br/><br/>
	<?php echo $this->form_session_list['report_submit_html']; ?>

	<?php  if (!empty($this->no_results)) { ?>
		<br/><br/><span id='empty_result_warning' style='font-weight: bold'>Sorry, data isn't available for your query.</span>
	<?php } ?>


	</left>
</form>

<?php include $this->loadTemplate("admin/page_footer.tpl.php"); ?>