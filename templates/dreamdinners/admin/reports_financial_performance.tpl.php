<?php
// constants for all report pages.
$PAGETITLE = "Financial Performance Report";
$HIDDENPAGENAME = "admin_reports_financial_performance";
$SHOWSINGLEDATE=FALSE;
$SHOWRANGEDATE=FALSE;
$SHOWMONTH=TRUE;
$SHOWYEAR=FALSE;

$this->setScript('head', SCRIPT_PATH . '/admin/reports_fin_perf.js');

$this->setOnload('reports_fin_perf_init();');

include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>
<form method="post" action="" target="result_frame" id="fin_perf_form" onsubmit="return submitMe(this);">

<div style="width:100%; margin-top:15px;">
<?php echo $this->query_form['hidden_html'];?>

<div id="same_store_sales_report_form" style="width:90%; margin: 0 auto; ">

<?php
	if (isset($this->query_form['store_html']) && !defined('SUPPRESS_STORE_SELECTOR') ) {
	    echo '<h4>Select a Store&nbsp;</h4>' .  $this->query_form['store_html'] . '<br /><br />';
	}
?>

<div style="float:left">


<h4>Select a Month</h4>
<?php echo $this->query_form['date_type_html']['single_month']; ?><label for="date_typecurrent_month">Single Month</label><br />
<div style="padding-left:40px;">
<?php echo $this->query_form['month_single_month_html']; ?>&nbsp;
<?php echo $this->query_form['year_single_month_html']; ?>&nbsp;&nbsp;<input type="checkbox" name="show_comparisons" id="show_comparisons" />&nbsp;Show Comparison Data<br />
</div>




<?php echo $this->query_form['date_type_html']['month_range']; ?><label for="date_typeother_month">Month Range</label><br />
<div style="padding-left:40px;">
From Month<br />
<?php echo $this->query_form['month_from_month_html']; ?>&nbsp;
<?php echo $this->query_form['year_from_month_html']; ?><br />
</div>
<div style="padding-left:40px;">
To Month<br />
<?php echo $this->query_form['month_to_month_html']; ?>&nbsp;
<?php echo $this->query_form['year_to_month_html']; ?><br />
</div>


<div">
<br />
<br />
<input type="checkbox" name="use_percent_of_agr" id="use_percent_of_agr" />&nbsp;Show Expenses as percent of Adjusted Gross Revenue<br />
</div>

</div>

<div style="clear:both; float:right; margin-right:400px;">
<?php  echo $this->query_form['report_submit_html'];  ?>
</div>
<br />
<br />
</div>
</div>

<div style="clear:both"></div>

<div style="margin-top:20px; padding:1px; background-color:#DED6CB; width:960px; display:none;" id="result_div">
	<input type="button" class="button" style="cursor:pointer; float:right;" value="Export" onclick="export_report();" />
<iframe style="width:956px; height:504px; padding:0px;" id="result_frame" name="result_frame">
</iframe>
</div>
</form>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>