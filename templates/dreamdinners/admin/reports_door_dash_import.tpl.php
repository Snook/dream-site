<?php
$HIDDENPAGENAME = "admin_reports_door_dash_import";
$this->assign('page_title','Door Dash Import');

$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
$SHOW_WEEK=TRUE;
$ADDFORMTOPAGE=TRUE;
$OVERRIDESUBMITBUTTON=TRUE;

include $this->loadTemplate('admin/page_header_reports.tpl.php'); ?>


<div style="text-align:center;">
    <h3>Door Dash Order Report</h3>
</div>

<div>
    <div style="width:50%; float:left; margin-left:50px;">
	<?php if (!$this->storeView)	{ ?>
        <h4>Select Time Span & Store</h4>
	<?php  } else { ?>
		<h4>Select Time Span</h4>
	<?php } ?>
        <?php include $this->loadTemplate('admin/reports_form.tpl.php'); ?>
		<input id="revenue_only" name="revenue_only" type="checkbox" checked="checked" /><label for="revenue_only">Report Revenue Only</label>
	</div>

    <div style="float:left; margin:50px;">
        <?php  echo $this->form_session_list['report_submit_html'];  ?>

	</div>
</div>
</form>

<?php if (!$this->storeView)	{ ?>

	<form enctype="multipart/form-data" id="import_door_dash_data" method="POST" >
		<div style="margin-top:120px; margin-left:50px; clear:both;">
		<hr>
			<div>
			 <br />
					<h4>Import file:</h4>
				<input type="file" id="door_dash_input_file" name="door_dash_input_file" /><br /><br />
				<input id="submit_door_dash_input" name="submit_door_dash_input" type="submit" class="btn btn-primary btn-sm" value="Import Door Dash Report"/>
				<img id="processing_image" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/throbber_circle.gif" class="img_valign" style="display: none;" alt="Processing" />
			</div>

			<div style="clear:both; height:10px;"></div>

	</div>
	</form>
<?php } ?>

	<?php 	include $this->loadTemplate('admin/page_footer.tpl.php');
?>