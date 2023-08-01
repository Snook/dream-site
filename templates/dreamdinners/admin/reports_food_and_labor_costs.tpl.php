<?php
$HIDDENPAGENAME = "admin_reports_food_and_labor_costs";
$this->assign('page_title','Food and Labor Costs Report');

$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
$SHOW_WEEK=TRUE;
$ADDFORMTOPAGE=TRUE;
$OVERRIDESUBMITBUTTON=TRUE;

$this->setScript('head', SCRIPT_PATH . '/admin/reports_food_and_labor.min.js');

if (!empty($this->no_data))
{
	$this->setOnload('reports_food_and_labor_init(true,' . $this->pickdate .');');
}
else
{
	$this->setOnload('reports_food_and_labor_init(false, 0);');
}

include $this->loadTemplate('admin/page_header_reports.tpl.php'); ?>


<div style="text-align:center;">
    <h3>Food and Labor Costs Report</h3>
</div>

<div>
    <div style="width:30%; float:left; margin-left:50px;">
        <h4>Select Time Span</h4>
        <?php include $this->loadTemplate('admin/reports_form.tpl.php'); ?>
    </div>

    <div style="float:left; margin-left:50px;">
        <h4>Select Report Type</h4>
        <?php  echo $this->form_session_list['report_type_html'];  ?>
        <?php  echo $this->form_session_list['corp_store_html'];  ?>
        <?php  echo $this->form_session_list['report_submit_html'];  ?>

    </div>
</div>
</form>

<form enctype="multipart/form-data" id="import_labor_data" method="POST" >
<div style="margin-top:120px; margin-left:50px; clear:both;">
<hr>
    <div>
     <br />
    		<h4>Import file:</h4>
<p>Warning: Imported data is always appended so it is possible to create duplicates if data for the same date range was previously imported.</p>
		<input type="file" id="labor_input_file" name="labor_input_file" /><br /><br />
		<input id="submit_labor_input" name="submit_labor_input" type="submit" class="button" value="Import Labor Report"/>
		<img id="processing_image" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign" style="display: none;" alt="Processing" />

    </div>

    <div style="clear:both; height:10px;"></div>

</div>
</form>
<?php 	include $this->loadTemplate('admin/page_footer.tpl.php');
?>