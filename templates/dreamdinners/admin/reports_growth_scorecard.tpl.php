<?php
// constants for all report pages.
$this->page_title = "Growth Scorecard Report";
$this->setScript('head', SCRIPT_PATH . '/admin/reports_growth_scorecard.min.js');
$this->setOnload('reports_growth_scorecard_init();');

include $this->loadTemplate('admin/page_header_reports.tpl.php');

?>

<form method="post" action=""  target="result_frame"  id="growth_scorecard_form" onsubmit="return submitMe(this);">
<?php echo $this->query_form['hidden_html'];?>

<div style="width: 100%; text-align:center; margin-bottom:16px;">
<h3>Growth Scorecard</h3>
</div>

<div style="float:left; margin-bottom:30px;">

<?php if ($this->showStoreSelector) { ?>
    <div style="padding-left:40px; margin-bottom:10px;">
        <span style="font-weight:bold;">Select a Store</span><br />
    	<?php echo $this->query_form['store_html']; ?>
    </div>
<?php } ?>

 <div style="padding-left:40px;">
    <span style="font-weight:bold;">Select a Menu/Month</span><br />
    <?php echo $this->query_form['month_html']; ?>&nbsp;
    <?php echo $this->query_form['year_html']; ?><br />
    </div>
</div>

<div style="float:left; margin-left:48px;">
<span style="font-weight:bold;">Select Report Type</span><br />
<?php echo $this->query_form['report_type_html']['session']; ?><label for="report_typesession">Single Month by Session</label><br />
<?php echo $this->query_form['report_type_html']['week']; ?><label for="report_typesession">Full Year by Week</label><br />
<?php echo $this->query_form['report_type_html']['month']; ?><label for="report_typesession">Full Year by Month</label>
<br /><br />
<input type="checkbox" id="MFY_only" name="MFY_only" /><label for="MFY_only"> Made For You Guests Only</label>
<span style="font-size: smaller"><br />(Only find current MFY guests<br /> and their previous and future Standard or<br />  MFY orders of 36 servings or more.)</span>


</div>

<?php if (isset($this->query_form['weekly_summary_report_submit_html'])) { ?>
<div style="clear:both;">
    <?php // echo $this->query_form['weekly_summary_report_submit_html'];  ?>&nbsp;&nbsp;
    <?php  echo $this->query_form['monthly_summary_report_submit_html'];  ?>&nbsp;&nbsp;
</div>
<?php  } ?>

<div style="float:right;">
    <?php  echo $this->query_form['report_submit_html'];  ?>&nbsp;&nbsp;
	<input type="button" class="button" style="cursor:pointer;" value="Export" onclick="export_report();" />
</div>

<div style="margin-top:20px; padding:1px; background-color:#DED6CB; width:960px; display:none;" id="result_div">
<iframe style="width:956px; height:504px; padding:0px;" id="result_frame" name="result_frame">
</iframe>
</div>

</form>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>