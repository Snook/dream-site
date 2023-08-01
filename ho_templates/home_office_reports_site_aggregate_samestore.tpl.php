<?php
$REPORTGIF = NULL;
$PAGETITLE = "Same Sales Financial Report";
$HIDDENPAGENAME = "admin_home_office_reports_site_aggregate_samestore";
$this->assign('page_title','Menu Push Report');


if (empty($this->rows)) {


$SHOWSINGLEDATE=FALSE;
$SHOWRANGEDATE=FALSE;
$SHOWMONTH=FALSE;
$SHOWYEAR=FALSE;
$ADDFORMTOPAGE=TRUE;
$OVERRIDESUBMITBUTTON=TRUE;

include $this->loadTemplate('admin/page_header_reports.tpl.php');
include $this->loadTemplate('admin/reports_form.tpl.php');

echo "Show Active Stores Only";
echo "<br>";
echo $this->form_session_list['contest_type_html'];
echo "<br><br>";
echo $this->form_session_list['report_submit_html'];



}
else if (isset($this->rows)  && count($this->rows) > 0 ) {
	    require_once('CSV.inc');
		$rows = $this->rows;
		$fileName = "reports_site_aggregate_samestore";
		CSV::writeCSVFile($fileName, $this->labels, $rows);
		return;
}

?>


</form>
<?php 	include $this->loadTemplate('admin/page_footer.tpl.php');
?>