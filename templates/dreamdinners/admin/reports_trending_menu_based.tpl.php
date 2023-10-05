<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports.css'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports-new.css'); ?>
<?php $this->assign('topnav', 'reports'); ?>
<?php $this->assign('page_title', 'Menu-based Trending Report'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/dashboard.min.js'); ?>
<?php $this->setOnload('trending_report_init();'); ?>
<?php if (isset($_REQUEST['print']) && ( $_REQUEST['print'] == "true" || $_REQUEST['print'] == 1))
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
<div style="float:right"><a href="/backoffice/reports_trending" class="btn btn-primary btn-sm">Calendar-Month Trending Report</a></div>

</div>
<div style="text-align:center; margin-bottom:10px;"><h2></h2></div>

<form id="reports_trending_form" action="/backoffice/reports_trending_menu_based" method="post">
<?php echo $this->form_array['hidden_html'];?>


<?php if ($this->showStoreSelector && !$this->print_view) { ?>
<table>
<tr>
	<td style="text-align:right;"><b>Single Store</b></td>
	<td colspan="4"><?php echo $this->form_array['report_type_html']['dt_single_store']; ?><label for="report_typedt_single_store" >Select a Store</label>:
	<?php echo $this->form_array['store_html']; ?></td>

</tr>

<?php if (empty($this->multiStoreOwnerStores))     { ?>
<tr>
	<td style="text-align:right; vertical-align:top;"><b>Roll up</b></td>
	<td  style="vertical-align:top; "><?php echo $this->form_array['report_type_html']['dt_corp_stores']; ?><label for="report_typedt_corp_stores" >Corporate Stores</label></td>
	<td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_non_corp_stores']; ?><label for="report_typedt_non_corp_stores" >Franchise Stores</label></td>
	<td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_all_stores']; ?><label for="report_typedt_all_stores" >All Stores</label></td>
	<td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_stores_by_region']; ?><label for="report_typedt_stores_by_region" >Stores by Region</label> <?php echo $this->form_array['trade_area_html']; ?></td>
</tr>
<?php } else { ?>
    <td style="text-align:right; vertical-align:top;"><b>Roll up</b></td>
    <td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_all_stores']; ?><label for="report_typedt_all_stores" >All Stores</label></td>
<?php } ?>


<tr>
<td colspan="6"><div>
Notes:  Historical values in roll up report are based on current active stores and may not reflect total national revenue.
</div>
</td>
</tr>
</table>
<?php } ?>


<?php if (isset($this->trending_report_error)) {?>

<div style="color:red; width:100%; font-weight:bold; text-align:center;"><?php echo $this->trending_report_error; ?></div>
<?php } else { ?>

<?php if (!$this->print_view) { ?>
<div style="float:right;">
	<a  href="javascript:export_trending_report();">Export XLSX <img style="vertical-align:middle;margin-bottom:.25em;" alt="Export" src="<?php echo IMAGES_PATH;?>/admin/icon/page_excel.png"></a>&nbsp;&nbsp;
	<?php if ($this->showAllTimeExportLink) { ?>
	<a  href="javascript:export_trending_report_all_time();">Export All Time XLSX <img style="vertical-align:middle;margin-bottom:.25em;" alt="Export" src="<?php echo IMAGES_PATH;?>/admin/icon/page_excel.png"></a>&nbsp;&nbsp;
	<?php } ?>
	<a  href="#" onclick="print_trending_report();return false;">Printer-Friendly Version <img style="vertical-align:middle;margin-bottom:.25em;" alt="Print" src="<?php echo IMAGES_PATH;?>/admin/icon/printer.png"></a>
</div>
<?php  }?>

<?php include $this->loadTemplate('admin/reports_trending_store_performance.tpl.php'); ?>
<?php include $this->loadTemplate('admin/reports_trending_guest_habits.tpl.php'); ?>

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