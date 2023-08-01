<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports.css'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports-new.css'); ?>
<?php $this->assign('topnav', 'reports'); ?>
<?php $this->assign('page_title', 'Business Health Assessment'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/dashboard.min.js'); ?>
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

<form id="reports_bha_form" method="post">
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

<tr><td style="text-align:left; vertical-align:top;"><b>Time Span</b></td><td style="text-align:left; vertical-align:top;"><b>Report Type</b></td></tr>
<tr><td  style="vertical-align:top; "><?php echo $this->form_array['report_type_html']['dt_year_to_date']; ?><label for="report_typedt_year_to_date" >Year to Date</label></td>
    <td><?php echo $this->form_array['report_depth_html']['dp_roll_up']; ?><label for="report_depthdp_rool_up" >Roll up</label></td></tr>

<tr><td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_year']; ?><label for="report_typedt_year" >Full Year</label>&nbsp;<?php echo $this->form_array['year_popup_html']; ?></td>
    <td><?php echo $this->form_array['report_depth_html']['dp_month']; ?><label for="report_depthdp_month" >Detail by Month</label></td></tr>

<tr><td  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_quarter']; ?><label for="report_typedt_quarter" >Quarter</label><?php echo $this->form_array['quarter_popup_html']; ?></td>
    <td><?php echo $this->form_array['years_back_popup_html']; ?>&nbsp;<label>Years Back</label></td></tr>

<tr><td  colspan="2" style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_semi_annual']; ?><label for="report_typedt_semi_annual" >Semi-Annual (1st Half)</label></tr>
<tr><td colspan="2"  style="vertical-align:top;"><?php echo $this->form_array['report_type_html']['dt_month']; ?><label for="report_typedt_month" >Month</label>&nbsp;<?php echo $this->form_array['month_popup_html']; ?></tr>


<tr><td></td><td><input type="submit" class="button" name="run_report" value="Run Report" /></td></tr>

</table>

</form>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>
