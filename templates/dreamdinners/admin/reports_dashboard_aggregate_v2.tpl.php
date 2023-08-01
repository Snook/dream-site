<?php
// constants for all report pages.
$PAGETITLE = "Dashboard Aggregate Report (v2)";
$HIDDENPAGENAME = "admin_reports_dashboard_aggregate_v2";
$SHOWSINGLEDATE=FALSE;
$SHOWRANGEDATE=FALSE;
$SHOWMONTH=TRUE;
$SHOWYEAR=FALSE;

$this->setScript('head', SCRIPT_PATH . '/admin/vendor/jstree.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/store_tree.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_dashboard_aggregate.min.js');
$this->setCSS(CSS_PATH . '/admin/jquery/jsTree/default/style.css');
$this->setOnload('reports_dashboard_aggregate_init();');
include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>

<div style="width:100%; margin-top:15px;">
<form method="post" action="" onsubmit="return _override_check_form();">
<?php echo $this->query_form['hidden_html'];?>

<div id="same_store_sales_report_form" style="width:90%; margin: 0 auto; ">

<div style="float:left">
<h4>Select a Calendar or<br /> Menu Month</h4>
<h3>Type</h3>
<?php
echo $this->query_form['menu_or_calendar_html']['menu'] . "&nbsp; Menu Month <br />";
echo $this->query_form['menu_or_calendar_html']['cal'] . "&nbsp; Calendar Month <br />";
?>
<h3>Month</h3>
<?php echo $this->query_form['date_type_html']['current_month']; ?><label for="date_typecurrent_month">Current Month/Menu</label><br />
<?php echo $this->query_form['date_type_html']['other_month']; ?><label for="date_typeother_month">Other Month/Menu</label><br />
<div style="padding-left:40px;">
<?php echo $this->query_form['month_other_month_html']; ?>&nbsp;
<?php echo $this->query_form['year_other_month_html']; ?><br />
</div>
</div>

<div style="float:right;">
<h4>Sections to Display</h4>
<input type="checkbox" name="dagg_ext_store_info" id="dagg_ext_store_info" /><label for="dagg_ext_store_info">Show Extended Store Info</label><br />
<input type="checkbox" name="dagg_goals" id="dagg_goals"  /><label for="dagg_goals">Show Goals and Progress</label><br />
<input type="checkbox" name="dagg_p_and_l" id="dagg_p_and_l" /><label for="dagg_p_and_l">Show P and L Data</label><br />
<input type="checkbox" name="dagg_same_store" id="dagg_same_store" /><label for="dagg_same_store">Show Same Store Sales</label><br />
<div style="clear:both; float:right; margin-top:40px;">
<?php  echo $this->query_form['report_submit_html'];  ?>
</div>

</div>

<?php if ($this->show_store_selectors) { ?>
<div style="float:right;">
<h4>Select Store(s)</h4>
<?php echo $this->query_form['store_type_html']['corporate_stores']; ?><label for="store_typecorporate_stores">Corporate Stores</label><br />
<?php echo $this->query_form['store_type_html']['franchise_stores']; ?><label for="store_typefranchise_stores">Franchise Stores</label><br />
<?php echo $this->query_form['store_type_html']['soft_launch_stores']; ?><label for="store_typefranchise_stores">Soft Launch Stores</label><br />
<?php echo $this->query_form['store_type_html']['region']; ?><label for="store_typeregion">Region</label>&nbsp;&nbsp;<?php echo $this->query_form['regions_html']?><br />

<?php echo $this->query_form['store_type_html']['selected_stores']; ?><label for="store_typeselected_Stores">Selected Stores</label><br />
<?php if (!empty($this->store_data)) {
	include $this->loadTemplate('admin/subtemplate/store_tree.tpl.php');
} ?>
</div>
<?php  } ?>


</div>
</form>
</div>

<div style="clear:both"></div>

<?php if (isset($this->revenue_sum)) { ?>

<div style="float:left;">
	<h3>Revenue</h3>
	<?php echo CTemplate::moneyFormat($this->revenue_sum);?><br />
	<pre>
	<?php print_r($this->day_array); ?>
	</pre>
</div>
<?php } ?>

<?php if (isset($this->ly_revenue_sum)) { ?>

<div style="float:right;">
	<h3>Last Year To date Revenue</h3>
	<?php echo CTemplate::moneyFormat($this->ly_revenue_sum);?><br />
	<pre>
	<?php print_r($this->ly_day_array); ?>
	</pre>
</div>
<?php } ?>

<?php if (!empty($this->rows)) {
    print_r($this->rows);
}
?>
<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>