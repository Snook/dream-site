<?php
// constants for all report pages.
$PAGETITLE = "Same Store Sales Report";
$HIDDENPAGENAME = "admin_reports_same_store_sales";
$SHOWSINGLEDATE=FALSE;
$SHOWRANGEDATE=FALSE;
$SHOWMONTH=TRUE;
$SHOWYEAR=FALSE;
$this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports-new.css');

$this->setScript('head', SCRIPT_PATH . '/admin/vendor/jstree.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_same_store_sales.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/store_tree.min.js');
$this->setCSS(CSS_PATH . '/admin/jquery/jsTree/default/style.css');

$this->setOnload('reports_same_store_sales_init();');
$this->assign('topnav','reports');

include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if (!empty($this->report_title)) { ?>
<div style="width: 100%; text-align:center;">
<h3><?php echo $this->report_title;?></h3>
</div>
<table width="100%" style="border-collapse: collapse; padding:0px; border:4px solid black; margin-bottom:12px;">
   <tr>
    <td class="label_delimited" style="text-align:right"><?php echo $this->month_str;?> AGR</td>
    <td class="value_delimited" style="text-align:center;" colspan="2">$<?php echo CTemplate::number_format($this->revenue_sum, 2);?></td>
  </tr>
   <tr>
    <td class="space_right_delimited" style="text-align:right"></td>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><?php echo $this->ly_month_str;?> </td>
    <td class="space_right_delimited" style="text-align:center; width:20%;" ><?php echo $this->lm_month_str;?> </td>
  </tr>
   <tr>
    <td class="label_delimited" style="text-align:right"><span data-help="dashboard-adjusted_gross_revenue_history">Month End Total</span></td>
    <td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->ly_revenue_sum, 2);?></td>
    <td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->lm_revenue_sum, 2);?></td>
  </tr>
   <tr>
    <td class="label_delimited" style="text-align:right">+/-</td>
      <td class="value_delimited" style="text-align:center;"><?php echo $this->ly_diff;?></td>
     <td class="value_delimited" style="text-align:center;"><?php echo $this->lm_diff;?></td>
  </tr>
   <tr>
    <td class="label_delimited" style="text-align:right">%</td>
    <td class="value_delimited" style="text-align:center;"><?php echo $this->ly_percent_diff;?></td>
    <td class="value_delimited" style="text-align:center;"><?php echo $this->lm_percent_diff;?></td>
  </tr>
</table>



<?php } ?>

<?php if (!empty($this->chart_image_path )) { ?>
<div style="text-align:center; margin-bottom:10px; margin-top:20px;">
<img src="<?php echo $this->chart_image_path ?>" />
</div>
<?php } ?>
<?php if (!empty($this->chart_image_path_acc )) { ?>
<div style="text-align:center; margin-bottom:10px; margin-top:20px;">
<img src="<?php echo $this->chart_image_path_acc ?>" />
</div>
<?php } ?>


<?php if (!empty($this->store_details)) {?>
<div>
<table width="100%" style="border-collapse: collapse; padding:0px; border:4px solid black; margin-bottom:12px;">

<tr>
<th colspan="4" class="space_right_delimited" >Store</th>
<th class="space_right_delimited" ></th>
<th colspan="3" class="space_right_delimited" >Revenue Same Month Last Year</th>
<th colspan="3" class="space_right_delimited" >Revenue Last Month</th>
</tr>


<tr>
<th class="space_right_delimited" >HO ID</th>
<th class="space_right_delimited" >Name</th>
<th class="space_right_delimited" >City</th>
<th class="space_right_delimited" >State</th>
<th class="space_right_delimited" >Revenue</th>
<th class="space_right_delimited" >Revenue </th>
<th class="space_right_delimited" >Diff</th>
<th class="space_right_delimited" >Percent Diff</th>
<th class="space_right_delimited" >Revenue</th>
<th class="space_right_delimited" >Diff</th>
<th class="space_right_delimited" >Percent Diff</th>
</tr>
<?php foreach ($this->store_details as $id => $data) {?>
<tr>
<td class="value_delimited" style="padding-left:4px;"><?php echo $data['hoid']; ?></td>
<td class="value_delimited" style="padding-left:4px;" ><?php echo $data['name']; ?></td>
<td class="value_delimited" style="padding-left:4px;" ><?php echo $data['city']; ?></td>
<td class="value_delimited" style="padding-left:4px;" ><?php echo $data['state']; ?></td>
<td class="value_delimited" style="padding-left:4px;" ><?php echo '$' . CTemplate:: number_format($data['cur_month']); ?></td>
<td class="value_delimited" style="padding-left:4px;" ><?php echo '$' . CTemplate:: number_format($data['last_year_month']); ?></td>
<td class="value_delimited" style="padding-left:4px;" ><?php echo $data['ly_diff']; ?></td>
<td class="value_delimited" style="padding-left:4px;" ><?php echo $data['ly_percent_diff']; ?></td>
<td class="value_delimited" style="padding-left:4px;" ><?php echo '$' . CTemplate:: number_format($data['last_month_month']); ?></td>
<td class="value_delimited" style="padding-left:4px;" ><?php echo $data['lm_diff']; ?></td>
<td class="value_delimited" style="padding-left:4px;" ><?php echo $data['lm_percent_diff']; ?></td>
</tr>

<?php } ?>
</table>




</div>
<?php  } ?>


<div style="width:100%; border:2px green solid; overflow:hidden;">
<form method="post" action="" onsubmit="return _override_check_form();">
<?php echo $this->query_form['hidden_html'];?>
<div id="same_store_sales_report_form" style="width:90%; margin: 0 auto; margin-top:20px;">

<div style="clear:both; float:right; width:200px;">
<?php  echo $this->query_form['report_submit_html'];  ?><br />
<?php if ($this->show_store_selectors) { ?>
<input type="checkbox" name="get_store_detail" />&nbsp;Show Store Detail
<?php  } ?><br /><br /><br />
<?php  echo $this->query_form['report_export_html'];  ?><br />

</div>

<div style="float:left;">
<h4>Select a Month or Menu</h4>
<?php echo $this->query_form['date_type_html']['current_menu']; ?><label for="date_typecurrent_menu">Current Menu</label><br />
<?php echo $this->query_form['date_type_html']['current_month']; ?><label for="date_typecurrent_month">Current Calendar Month</label><br />

<?php echo $this->query_form['date_type_html']['other_menu']; ?><label for="date_typeother_menu">Other Menu</label><br />
<div style="padding-left:40px;">
<?php echo $this->query_form['month_other_menu_html']; ?>&nbsp;
<?php echo $this->query_form['year_other_menu_html']; ?><br />
</div>

<?php echo $this->query_form['date_type_html']['other_month']; ?><label for="date_typeother_month">Other Calendar Month</label><br />
<div style="padding-left:40px;">
<?php echo $this->query_form['month_other_month_html']; ?>&nbsp;
<?php echo $this->query_form['year_other_month_html']; ?><br />
</div>

</div>

<?php if ($this->show_store_selectors) { ?>
<div style="float:right;">
<h4>Select Store(s)</h4>
<?php echo $this->query_form['store_type_html']['corporate_stores']; ?><label for="store_typecorporate_stores">Corporate Stores</label><br />
<?php echo $this->query_form['store_type_html']['franchise_stores']; ?><label for="store_typefranchise_stores">Franchise Stores</label><br />
<?php echo $this->query_form['store_type_html']['region']; ?><label for="store_typeregion">Region</label><?php echo $this->query_form['regions_html']?><br />

<?php echo $this->query_form['store_type_html']['selected_stores']; ?><label for="store_typeselected_Stores">Selected Stores</label><br />


<?php if (!empty($this->store_data)) {
	include $this->loadTemplate('admin/subtemplate/store_tree.tpl.php');
} ?>

<!-- <div id="store_selector" style="min-width:380px; max-width:380px; border:thin solid black; margin:10px; padding:10px; text-align:left; display:none">
<?php echo $this->store_data;?>
</div>-->


</div>
<?php  } ?>

</div>
</form>
</div>


<div style="clear:both"></div>

<?php if (isset($this->revenue_sum)) { ?>

<div style="float:left;">
	<pre>
	<?php print_r($this->day_array); ?>
	</pre>
</div>
<?php } ?>

<?php if (isset($this->ly_revenue_sum)) { ?>

<div style="float:right;">
	<pre>
	<?php print_r($this->ly_day_array); ?>
	</pre>
</div>
<?php } ?>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>