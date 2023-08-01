<?php
$REPORTGIF = NULL;
$PAGETITLE = "Session Host Report";
$HIDDENPAGENAME = "admin_reports_session_host";
$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
$ADDFORMTOPAGE=TRUE;
$OVERRIDESUBMITBUTTON=TRUE;
$ON_SUBMIT="return submitMe(this);";
$this->setScript('head', SCRIPT_PATH . '/admin/reports_session_host.min.js');
$this->setOnload('reports_session_host_init();');
include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>

<p>This report will return a list of session hosts during the selected timespan.
Please note: The Excel document will return additional information such as phone, call time, sessions attended.</p>


<?php  include $this->loadTemplate('admin/reports_form.tpl.php'); ?>
<?php echo $this->form_session_list['hidden_html'];?>



    <div style="float:left;">
        <?php
         echo $this->form_session_list['report_submit_html'];
	     echo '<input type="button" class="button" style="cursor:pointer;" value="Export" onclick="export_report();" //>'; ?>
    </div>
    <div style="clear:both;"></div>
    <hr />
</form>


    <?php if (isset($this->empty_result) && $this->empty_result) { ?>
        <div id="empty_result_msg" >
        <table><tr><td width="610" class="headers" style="padding-left: 5px;" colspan="5"><b>Sorry, could not generate a report for this date.</b></td></tr></table>
        </div>
    <?php } ?>



    <div style="margin-top:20px; padding:1px; background-color:#DED6CB; width:1200px; display:none;" id="result_div">
        <iframe style="width:1200px; height:504px; padding:0px;" id="result_frame" name="result_frame">
        </iframe>
    </div>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>