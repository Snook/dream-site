<?php
$HIDDENPAGENAME = "admin_home_office_reports_weekly_metrics";
$this->assign('page_title','Weekly Metrics Report');

$this->setScript('head', SCRIPT_PATH . '/admin/vendor/jstree.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/store_tree.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_weekly_metrics.js');
$this->setCSS(CSS_PATH . '/admin/jquery/jsTree/default/style.css');
$this->setOnload('reports_dashboard_aggregate_init();');

include $this->loadTemplate('admin/page_header_reports.tpl.php');

?>
<form method="post" action="" onsubmit="return _override_check_form();">
<?php echo $this->query_form['hidden_html'];?>


<div style="margin:30px;">

	<div style="text-align:center;">
		<h3>Guest Metrics Report</h3>
	</div>

    <div style="width:100%;">
        <h4>Pick Metric</h4>
        <div style="float:left;"> <h2>Metric</h2>
            <?php echo $this->query_form['metric_html']; ?>
        </div>

        <div style="float:left; margin-left:30px;"> <h2>Type</h2>
        <?php echo $this->query_form['focus_type_html']['week']; ?><label for="focus_typeweek">Weekly</label><br />
        <?php echo $this->query_form['focus_type_html']['month']; ?><label for="focus_typemonth">Monthly</label><br />
        </div>

           <div style="float:right;">
        <h4>Select Time Span</h4>
        		Select a range of dates:<br />
        		(Note: Any week or month within the range <br />will be incuded in its entirety)
        		<?php
        		$rangestart = NULL;
        		$rangeend = NULL;
        		if (isset($this->range_day_start_set) && isset($this->range_day_end_set)) {
        		    echo "<script>DateInput('range_day_start', false, 'YYYY-MM-DD','" . $this->range_day_start_set .  "')</script>";
        			echo "<script>DateInput('range_day_end', false, 'YYYY-MM-DD','" . $this->range_day_end_set .  "')</script>";
        		}
        		else {
        			echo "<script>DateInput('range_day_start', true, 'YYYY-MM-DD')</script>";
        			echo "<script>DateInput('range_day_end', true, 'YYYY-MM-DD')</script>";
        		}
        		?>

    </div>


    </div>

    <div style="clear:both; height:10px;"></div>

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

        <?php  echo $this->query_form['report_submit_html'];  ?>
</div>
</div>
</form>
<?php 	include $this->loadTemplate('admin/page_footer.tpl.php');
?>