<?php
$HIDDENPAGENAME = "admin_home_office_reports_weekly_metrics";
$this->assign('page_title','Weekly Metrics Report');

$this->setScript('head', SCRIPT_PATH . '/admin/vendor/jstree.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/store_tree.js');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_weekly_metrics.js');
$this->setCSS(CSS_PATH . '/admin/jquery/jsTree/default/style.css');
$this->setOnload('reports_dashboard_aggregate_init();');

include $this->loadTemplate('admin/page_header_reports.tpl.php');

?>
<form method="post" action="" onsubmit="return _override_check_form();">
<?php echo $this->query_form['hidden_html'];?>

<div style="margin:30px;">
	<div style="text-align:center;">
		<h3>Weekly Metrics Report</h3>
	</div>

	<div style="float:left;">
        <h4>Select Store(s)</h4>
        <?php echo $this->query_form['store_type_html']['corporate_stores']; ?><label for="store_typecorporate_stores">Corporate Stores</label><br />
        <?php echo $this->query_form['store_type_html']['franchise_stores']; ?><label for="store_typefranchise_stores">Franchise Stores</label><br />
        <?php echo $this->query_form['store_type_html']['region']; ?><label for="store_typeregion">Region</label>&nbsp;&nbsp;<?php echo $this->query_form['regions_html']?><br />
        <?php echo $this->query_form['store_type_html']['store_class']; ?><label for="store_typestore_class">Store Class</label>&nbsp;&nbsp;<?php echo $this->query_form['store_class_html']?><br />
        <?php echo $this->query_form['store_type_html']['selected_stores']; ?><label for="store_typeselected_Stores">Selected Stores</label><br />
        <?php if (!empty($this->store_data)) {
        	include $this->loadTemplate('admin/subtemplate/store_tree.tpl.php');
        } ?>
    </div>

	<div style="float:right;">
        <h4>Select Focus Week</h4>
        <?php echo $this->query_form['focus_week_html']['current_week']; ?><label for="focus_weekcurrent_week">Current Week</label><br />
        <?php echo $this->query_form['focus_week_html']['last_week']; ?><label for="focus_weeklast_week">Last Week</label><br />
        <?php echo $this->query_form['focus_week_html']['other_week']; ?><label for="focus_weekother_week">Other Week</label><br />
        <script>DateInput('single_date', true, 'YYYY-MM-DD');</script>
        <br />
        <br />
        <be />
        <?php  echo $this->query_form['report_submit_html'];  ?>

    </div>

</div>
</form>
<?php 	include $this->loadTemplate('admin/page_footer.tpl.php');
?>