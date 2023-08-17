<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports.css'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports-new.css'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/jquery/jsTree/default/style.css');?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jstree.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/store_tree.min.js'); ?>
<?php $this->setScriptVar('isHomeOfficeAccess = ' . ($this->showReportTypeSelector ? 'true' : 'false') . ';'); ?>


<?php if (isset($this->updateRequired) && $this->updateRequired) {
	$this->setOnLoad("processMetrics();");
} ?>
<?php $this->assign('page_title','Menu-Based Dashboard'); ?>
<?php $this->assign('topnav','reports'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/dashboard.min.js'); ?>


<?php if (isset($_REQUEST['print']) && ($_REQUEST['print'] == "true" || $_REQUEST['print']))
{
	$this->assign('print_view', true);

}
else
{
	$this->assign('print_view', false);
}
?>
<?php $this->setScriptVar('hasStoreData = ' . (!empty($this->store_data) ? "true" : 'false') . ';'); ?>
<?php $this->setScriptVar('didReturnCustomRollup = ' . (!empty($this->didReturnCustomRollup) ? "true" : 'false') . ';'); ?>


<?php $this->setOnload('dashboard_init();'); ?>
<?php include $this->loadTemplate('admin/page_header.tpl.php'); ?>

<?php if (false) { ?>
<div style="background-color:#d0d0d0; border:2px; black solid; text-align:center; font-weight:bold; font-size:14pt; margin:50px; padding:25px;">
<span style="color:red;">This page is down for maintenance. Please Check Back Shortly.</span>
</div>
<?php include $this->loadTemplate('admin/page_footer.tpl.php');
return; }
?>


<div style="background-color:#d0d0d0; border:2px; black solid; text-align:center; font-weight:bold; font-size:14pt; margin:0px; padding:5px;">
	<span style="color:green;">Dashboard (by Menu Month)</span>
	<div style="float:right"><a href="/main.php?page=admin_dashboard_new" class="button">to Calendar-Month Dashboard</a></div>
</div>
<form id="dashboard_form" action="main.php?page=admin_dashboard_menu_based" method="post">
<?php echo $this->form_array['hidden_html'];?>

<?php if (!$this->print_view) { ?>


<?php if (!$this->showReportTypeSelector) {
// Store View
	?>
<table style="width: 100%; margin:0px; padding:0px;">
<tr>
	<td style="text-align:right;">

	<?php  if ($this->showCurrentMonth) { ?>
	<button onclick="toggleMonth('previous');" class="button">Show previous month</button>
	<?php } else { ?>
	<button onclick="toggleMonth('current');" class="button">Show current month</button>
<?php } ?>
or Pick a Month: <?php echo $this->form_array['override_month_html']; ?>
	</td>
</tr>
<tr>
	<td style="text-align:center"><h2><?php echo $this->titleString; ?></h2></td>
</tr>
</table>

<?php } else {
	// Home Office View
	?>
<table style="width: 100%; margin:0px; padding:0px;">
<tr>
	<td style="text-align:left;"><b>Step 1 - Choose a Menu-Month</b></td>
	<td><?php echo $this->form_array['override_month_html']; ?></td>
</tr>
<?php if (!empty($this->selected_menu)) { ?>
<tr>
	<td style="text-align:left;"><b>Step 2 - Choose a Store or Stores</b></td>
	<td><?php echo $this->form_array['report_type_html']['dt_single_store']; ?><label for="report_typedt_single_store" >Select Store</label>:
	<?php echo $this->form_array['store_html']; ?></td>
</tr>
<tr>
	<td style="text-align:right; vertical-align:top;"></td>
	<td style="vertical-align:top; "><?php echo $this->form_array['report_type_html']['dt_soft_launch']; ?><label for="report_typedt_corp_stores" >Marketing Test Stores</label></td>
</tr>
<tr>
	<td style="text-align:right; vertical-align:top;"></td>
	<td style="vertical-align:top; "><?php echo $this->form_array['report_type_html']['dt_non_soft_launch']; ?><label for="report_typedt_corp_stores" >Non-Marketing Test Stores</label></td>
</tr>
<tr>
	<td style="text-align:right; vertical-align:top;"></td>
	<td style="vertical-align:top; "><?php echo $this->form_array['report_type_html']['dt_custom']; ?><label for="report_typedt_corp_stores" >Select Multiple Stores</label></td>
</tr>
<?php if (!empty($this->store_data)) { ?>
<tr>
	<td style="text-align:left; vertical-align:top;"><b>Step 3 - Run Report</b></td>
	<td style="vertical-align:top; "><button type="button" onclick="runMultiStoreReport();" class="button">Run Report</button><br />
</td>
</tr>
<?php  } ?>

<tr id="custom_store_select" style="display:<?php echo ($this->didReturnCustomRollup ? "none" : "table-row")?>;">
<td colspan="2">

<?php if (!empty($this->store_data)) {
	include $this->loadTemplate('admin/subtemplate/store_tree.tpl.php');
} ?>

</td>
</tr>

<tr id="custom_store_select_minimized" style="display:<?php echo ($this->didReturnCustomRollup ? "table-row" : "none")?>;">
<td colspan="2">
<div style="border:thin solid black; width:640px; min-width:380px;  margin-left:10px; margin-top:10px; text-align:center; background-color:#BEB7AE; float:left;">
		<h2 style="margin-left:10px; margin-top:10px;">Select Store(s)</h2><button type="button" class="button" onclick="$('#custom_store_select').show(); $('#custom_store_select_minimized').hide();">Re-select</button>
</div>
</td>
</tr>

<tr>
	<td colspan="2" style="text-align:center"><h2><?php echo $this->titleString; ?></h2></td>
</tr>


<?php } ?>
</table>
<?php } ?>


<?php if (!isset($this->dashboard_error)) { ?>
<div style="float:right;">
<a  href="javascript:export_order_list(true);">Export Order List <img style="vertical-align:middle;margin-bottom:.25em;" alt="Export" src="<?php echo IMAGES_PATH;?>/admin/icon/page_excel.png"></a>&nbsp;&nbsp;
<a  href="javascript:print_dashboard();">Printer-Friendly Version <img style="vertical-align:middle;margin-bottom:.25em;" alt="Print" src="<?php echo IMAGES_PATH;?>/admin/icon/printer.png"></a>
</div>
<?php } ?>

<?php } else { ?>

<table style="width: 100%; margin:0px; padding:0px;">
<tr>
	<td colspan="5" style="text-align:center"><h2><?php echo $this->titleString; ?></h2> for <?php echo CTemplate::dateTimeFormat(date("Y-m-d H:i:s", time())); ?></td>
</tr>
</table>



<?php } ?>

<?php if (isset($this->dashboard_error)) {?>

<div style="color:red; width:100%; font-weight:bold; text-align:center;"><?php echo $this->dashboard_error; ?></div>
<div id="busy_updating" style="display:none">The Metrics are currently being updated. Please wait. The page will reload in a few moments when the update is complete.<br />
				<div style="vertical-align:center; text-align:center; width:100%; height:100%;"><br /><br /><img src="<?php echo IMAGES_PATH?>/admin/throbber_processing_noborder.gif" /></div></div>
<?php } else { ?>

<?php include $this->loadTemplate('admin/dashboard_agr_summary.tpl.php'); ?>

<?php include $this->loadTemplate('admin/dashboard_agr_breakdown.tpl.php'); ?>

<?php include $this->loadTemplate('admin/dashboard_guests.tpl.php'); ?>

	<?php include $this->loadTemplate('admin/dashboard_rsvps.tpl.php'); ?>

	<?php include $this->loadTemplate('admin/dashboard_sessions.tpl.php'); ?>

<?php include $this->loadTemplate('admin/dashboard_retention.tpl.php'); ?>

<?php
if (!$this->isFutureMonth && !$this->isDistantMonth)
	include $this->loadTemplate('admin/dashboard_rankings.tpl.php');
?>

<?php } ?>

</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
