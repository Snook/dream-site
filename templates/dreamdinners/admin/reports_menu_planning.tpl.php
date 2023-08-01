<?php
// constants for all report pages.
$this->page_title = "Menu Planning Report";
//$this->setScript('head', SCRIPT_PATH . '/admin/reports_growth_scorecard.min.js');
//$this->setOnload('reports_growth_scorecard_init();');

include $this->loadTemplate('admin/page_header_reports.tpl.php');

?>

<form method="post" action="" id="menu_planning_form">
<?php echo $this->query_form['hidden_html'];?>

<div style="width: 100%; text-align:center; margin-bottom:16px;">
<h3>Menu Planning Report</h3>
</div>

<div style="float:left; margin-bottom:30px;">


 <div style="padding-left:40px;">
    <span style="font-weight:bold;">Select a Menu/Month</span><br />
    <?php echo $this->query_form['month_html']; ?>&nbsp;
    <?php echo $this->query_form['year_html']; ?><br />
    </div>
</div>


<div style="float:right;">
	<?php echo $this->query_form['export_report_html']; ?><br />
</div>

</form>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>