<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports.css'); ?>
<?php $this->setCSS(CSS_PATH . '/admin/admin-dashboard-reports-new.css'); ?>
<?php $this->assign('topnav', 'reports'); ?>
<?php $this->assign('page_title', 'Menu Skipping Report');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_embedded_excel.min.js');
$this->setOnload('reports_embedded_excel_init();');?>

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

<form id="frm" method="post" onSubmit="submitMe();" >

<?php echo $this->form_array['hidden_html'];?>


<table>
<tr>


    <td> <label for="month" >Month</label>:<?php echo $this->form_array['month_html']; ?></td>
    <td> <label for="year" >Year</label><?php echo $this->form_array['year_html']; ?></td>
    <td> <label for="month" >Month Back</label>:<?php echo $this->form_array['months_back_html']; ?></td>
    <td> <label for="month" >Number Months Skipped</label>:<?php echo $this->form_array['months_skipped_html']; ?></td>

    <td><input type="submit" class="btn btn-primary btn-sm" name="run_report" value="Run Web Report" /></td>
    <td><input type="button" class="btn btn-primary btn-sm" style="cursor:pointer;" value="Export" onclick="export_report();" /></td>

</tr>
</table>


</form>


<div style="text-align:center; margin: 0 auto; margin-top:20px; padding:1px; background-color:#DED6CB; width:960px; display:none;" id="result_div">
    <iframe style="display:block; margin: 0 auto; width:940px; height:560px; padding:0px;" id="result_frame" name="result_frame">
    </iframe>
</div>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>