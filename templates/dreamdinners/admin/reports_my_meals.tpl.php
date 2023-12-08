<?php
// constants for all report pages.
$PAGETITLE = "My Meals Report";
$HIDDENPAGENAME = "admin_reports_my_meals";
$SHOWSINGLEDATE=FALSE;
$SHOWRANGEDATE=FALSE;
$SHOWMONTH=TRUE;
$SHOWYEAR=FALSE;
include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>
<form method="post" action="/backoffice/reports-my-meals?export=csv">

<div id="coupon_report_form" style="display:block;">
<h4>Select a Menu by Month and Year</h4>
<?php echo $this->form_session_list['month_popup_html']; ?>
<?php echo $this->form_session_list['year_field_001_html']; ?>
</div>

<br /><input type="checkbox" name="vip_only" id="vip_only" /><label>Get ratings for VIP guests only</label><br /><br />

<?php  echo $this->form_session_list['report_submit_html'];  ?>
</form>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>