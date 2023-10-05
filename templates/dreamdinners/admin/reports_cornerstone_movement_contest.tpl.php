<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports.css'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports-new.css'); ?>
<?php $this->assign('topnav', 'reports'); ?>
<?php $this->assign('page_title', 'Cornerstone Movement Contest Report'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/dashboard.min.js'); ?>
<?php $this->setOnload('trending_report_init();'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/reports_embedded_excel.min.js');
$this->setOnload('reports_embedded_excel_init();');?>

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
<?php include $this->loadTemplate('admin/page_header.tpl.php');
$this->setOnload('reports_embedded_excel_init();');?>

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

<form id="frm" method="post"  onSubmit="submitMe();" >
<?php echo $this->form_array['hidden_html'];?>

    <table>

<?php if ($this->showStoreSelector) { ?>
<tr>
	<td colspan="2" style="text-align:right;"><b>Select Store</b>
	<label for="report_typedt_single_store" >Select a Store</label>:
	<?php echo $this->form_array['store_html']; ?></td>
</tr>
<?php } else { ?>
        <tr></tr> <td colspan="2" style="text-align:right; width:600px;" ></td>
<?php } ?>

<tr><td style="text-align:left; vertical-align:top;"><b>Select Year and Month</b></td></tr>

<tr><td  style="vertical-align:top;"><label>Year</label>&nbsp;<?php echo $this->form_array['year_popup_html']; ?></td></tr>
<tr><td  style="vertical-align:top;"><label>Month</label>&nbsp;<?php echo $this->form_array['month_popup_html']; ?></tr>


<tr><td></td><td><input type="submit" class="btn btn-primary btn-sm" name="run_report" value="Export Report" /></td></tr>
		<?php if (!$this->showStoreSelector) { ?>
			<tr><td></td><td><input type="submit" class="btn btn-primary btn-sm" name="run_web" value="Web Report" /></td></tr>
		<?php } ?>

</table>

</form>

<div style="text-align:center; margin: 0 auto; margin-top:20px; padding:1px; background-color:#DED6CB; width:1060px; display:none;" id="result_div">
	<iframe style="display:block; margin: 0 auto; width:1040px; height:380px; padding:0px;" id="result_frame" name="result_frame">
	</iframe>
</div>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>