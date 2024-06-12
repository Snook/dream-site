<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports.css');
 $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports-new.css');?>

<?php $this->assign('topnav', 'reports'); ?>
<?php $this->assign('page_title', 'Business Analysis Report'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/dashboard.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/reports_trending_new.min.js'); ?>

<?php $this->setOnload('trending_report_init();'); ?>


<?php if (isset($_REQUEST['print']) && $_REQUEST['print'] == "true")
{
	$this->assign('print_view', true);
}
else
{
	$this->assign('print_view', false);
}
?>

<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports-new.css'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>


<?php if (false) { ?>
<div style="background-color:#d0d0d0; border:2px; black solid; text-align:center; font-weight:bold; font-size:14pt; margin:50px; padding:25px;">
<span style="color:red;">This page is down for maintenance. Please Check Back Shortly.</span>
</div>
<?php include $this->loadTemplate('admin/page_footer.tpl.php');
return; }
?>


<div style="background-color:#d0d0d0; border:2px; black solid; text-align:center; font-weight:bold; font-size:14pt; margin:0px; padding:5px;">
<span style="color:green;"><?php echo $this->titleString; ?></span>
</div>
<div style="text-align:center; margin-bottom:10px;"><h2></h2></div>

<form id="reports_trending_form" action="/backoffice/reports-trending-new?export=xlsx" method="post">
<?php echo $this->form_array['hidden_html'];?>


<?php if ($this->showStoreSelector && !$this->print_view) { ?>
<table>
<tr>
	<td style="text-align:right;"><b>Single Store</b></td>
	<td colspan="4"><?php echo $this->form_array['report_type_html']['dt_single_store']; ?><label for="report_typedt_single_store" >Select a Store</label>:
	<?php echo $this->form_array['store_html']; ?></td>

</tr>
<tr>
	<td style="text-align:right; vertical-align:top;"><b>Roll up</b></td>
	<td  style="vertical-align:top; "><?php echo $this->form_array['report_type_html']['dt_corp_stores']; ?><label for="report_typedt_corp_stores" >Corporate Stores</label></td>
	<td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_non_corp_stores']; ?><label for="report_typedt_non_corp_stores" >Franchise Stores</label></td>
	<td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_all_stores']; ?><label for="report_typedt_all_stores" >All Stores</label></td>
	<td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_stores_by_region']; ?><label for="report_typedt_stores_by_region" >Stores by Region</label> <?php echo $this->form_array['trade_area_html']; ?></td>
	<td  style="vertical-align:top;"><?php echo $this->form_array['select_inactive_stores_html']; ?><label for="select_inactive_stores" >Include Inactive Stores</label><br />
	<?php echo $this->form_array['use_cal_month_html']; ?><label for="use_cal_month" >Use Calendar Month</label></td>
</tr>
<tr>
<td colspan="6" style="text-align:right"><button class="btn btn-primary btn-sm" onclick="export_trending_report();">Export Report</button></td>
</tr>
</table>
<?php } else { ?>
    <tr>
    <td colspan="6" style="text-align:right">
        <?php echo $this->form_array['use_cal_month_html']; ?><label for="use_cal_month" >Use Calendar Month</label>
        <button class="btn btn-primary btn-sm" onclick="export_trending_report();">Export Report</button></td>
    </tr>
<?php } ?>



<?php if (isset($this->trending_report_error)) {?>

<div style="color:red; width:100%; font-weight:bold; text-align:center;"><?php echo $this->trending_report_error; ?></div>
<?php } else { ?>

<?php } ?>

</form>
<?php if (!empty($this->agr_image_path )) { ?>
<div style="text-align:center; margin-bottom:10px">
<img src="<?php echo $this->agr_image_path ?>" />
</div>
<?php }
 if (!empty($this->guest_image_path )) { ?>
<div style="text-align:center">
<img src="<?php echo $this->guest_image_path ?>" />
</div>
<?php  } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>