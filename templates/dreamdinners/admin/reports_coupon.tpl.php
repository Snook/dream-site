<?php
// constants for all report pages.
//$REPORTGIF = "page_header_entreereport.gif";
$PAGETITLE = "Coupon Report";
$HIDDENPAGENAME = "admin_reports_coupon";
$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
$OVERRIDESUBMITBUTTON=TRUE;
$ADDFORMTOPAGE = TRUE;
include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>
    <div id="coupon_report_form" style="display:<?=($this->report_submitted == TRUE && !empty($this->report_data) )? "none" : "block"?>;">
        <?php
        include $this->loadTemplate('admin/reports_form.tpl.php');
        ?>

<?php if (isset($this->coupon_html_refs) && !empty($this->coupon_html_refs) && isset($this->coupon_html_refs2) && !empty($this->coupon_html_refs2))  { ?>
    <script type="text/javascript">
        function doCheckUncheckCurrent()
        {
			let checked = $("#checkUncheckCurrent").is(":checked");

            $("[data-dd_type='current']").each(function ()
			{
            	if (checked)
				{
					$(this).prop('checked', true);
				}
            	else
            	{
					$(this).prop('checked', false);
				}

            });
        }

        function doCheckUncheckExpired()
        {
			let checked = $("#checkUncheckExpired").is(":checked");

			$("[data-dd_type='expired']").each(function ()
			{
				if (checked)
				{
					$(this).prop('checked', true);
				}
				else
				{
					$(this).prop('checked', false);
				}

			});
        }
    </script>

	<?php
	if (!empty($this->form_session_list['report_submit_html'])) {
		echo $this->form_session_list['report_submit_html'];
		echo "<hr>";
	}
	?>

	<table class="table">
        <tr class="row">
            <td class="col-6">
                <h5>Current Coupons</h5>
                Select Coupons:<br/>
                <input data-couponState="current" type="checkbox" name="checkUncheckCurrent" id='checkUncheckCurrent' onClick="javascript:doCheckUncheckCurrent();">&nbsp;&nbsp;Select
                All / Unselect All<br/>
				<br/>
				<?php foreach ($this->coupon_html_refs as $thisCheckbox) { ?>
                    <?= $this->form_session_list[$thisCheckbox['box']] ?>&nbsp; <?= $thisCheckbox['title'] ?>(<?= $thisCheckbox['code'] ?>)
                    <br/>
                <?php }
                ?>
            </td>
            <td class="col-6">
                <h5>Expired Coupons</h5>
                Select Coupons:<br/>
                <input data-couponState="expired" type="checkbox" id='checkUncheckExpired' name="checkUncheckExpired" onClick="javascript:doCheckUncheckExpired();">&nbsp;&nbsp;Select
                All / Unselect All<br/>
                <br/>
                <?php foreach ($this->coupon_html_refs2 as $thisCheckbox) { ?>
                    <?= $this->form_session_list[$thisCheckbox['box']] ?>&nbsp; <?= $thisCheckbox['title'] ?>(<?= $thisCheckbox['code'] ?>)
                    <br/>
                <?php }

                ?>

            </td>
        </tr>
    </table>

 </form>
	</div>

<?php }  ?>

<?php
if (isset($this->report_data) && count($this->report_data) > 0) {	?>

    <script type="text/javascript">
        function externalLink()
        {

            var sWinHTML = document.getElementById('printer').innerHTML;
            var winprint=window.open("","");
            winprint.document.open();
            winprint.document.write("<html><head><title>Dream Dinners | Coupon Report</title><link href='<?= CSS_PATH ?>/admin/admin-styles-reports.css' rel='stylesheet' type='text/css' /><link href='<?= CSS_PATH ?>/admin/print.css' rel='stylesheet' type='text/css' /></head><body onload='window.print();'" +
                "bgcolor='#ffffff'><table width='600' bgcolor='#FFFFFF'><tr><td>");
            winprint.document.write("<div><div style='margin: 10px; '>");
            winprint.document.write("<h2>Coupon Report: <?=$this->report_title_range ?></h2>");
            winprint.document.write(sWinHTML);
            winprint.document.write("</div></div></td></tr></table></body></html>");
            winprint.document.close();
            winprint.focus();

        }
    </script>
<?php } ?>

    <script type="text/javascript">
        function showform()
        {
            document.getElementById('coupon_report_form').style.display = "block";
            document.getElementById('report_area').style.display = "none";
        }
    </script>

<?php
if ($this->report_submitted == TRUE) {
    if (isset($this->report_data) && count($this->report_data) > 0)
    { ?>
        <div id="report_area">
            <button class="button" onclick="showform();">Run New Coupon Report</button>

            <table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >
                <tr>
                    <td  >&nbsp;</td>
                    <td >&nbsp;</td>
                    <td align="right">
                        <A HREF="javascript:void(0)" onclick="externalLink('print');">Printer-Friendly Version&nbsp;</A><img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" />&nbsp;</td>
                    <td align="right">
                        <?php
                        $exportAllLink = '/?page=admin_reports_coupon&store=' . $this->store . '&day=' . $this->report_day . '&month=' . $this->report_month .
                            '&year=' . $this->report_year . '&duration=' . urlencode($this->report_duration) . '&report_type=' . $this->report_type . '&export=xlsx&coupons=' . $this->export_list;
                        include $this->loadTemplate('admin/export.tpl.php');
                        ?>
                    </td>
                </tr>
            </table>
            <table class='report' width='100%' border='0' cellpadding='0' cellspacing='0' >
                <tr>
                    <td class="headers" ><b>Coupons Report: <?=$this->report_title_range ?> for <?=$this->store_name ?></b></td>
                </tr>
            </table>

            <div id="printer">
                <table class='report' width='100%' border='0' cellpadding='4' cellspacing='0' >

                    <tr>
                        <td  class="subheaders" >Coupon Title</td>
                        <td  class="subheaders" >Coupon Code</td>
                        <td  class="subheaders" >Total Spend</td>
                        <td  class="subheaders" >Total Save</td>
                        <td  class="subheaders" >Num <br />Transactions</td>
                        <?php if ($this->all_stores) { ?>
                            <td  class="subheaders" >Operation</td>
                        <?php } ?>
                    </tr>

                    <?php foreach($this->report_data as $aRow) {	?>
                        <tr>
                            <td class="cc_data_cell" ><?=$aRow['title']?></td>
                            <td class="cc_data_cell" ><?=$aRow['code']?></td>
                            <td class="cc_data_cell"   align="right"><?=$aRow['total_spend']?></td>
                            <td class="cc_data_cell"  align="right"><?=$aRow['total_save']?></td>
                            <td class="cc_data_cell" align="center"><?=$aRow['num_trans']?></td>
                            <?php if ($this->all_stores) { ?>
                                <td  class="cc_data_cell" ><a href="/?page=admin_reports_coupon&coupon_detail=true&coupon=<?=$aRow['id']?>&day=<?=$this->report_day?>&month=<?=$this->report_month?>
		 			&year=<?=$this->report_year?>&duration=<?=urlencode($this->report_duration)?>&report_type=<?=$this->report_type?>&export=xlsx">export detail</a></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>

                </table>
            </div></div>
    <?php } else { ?>
        <table><tr><td width="610" style="padding-left: 5px;" colspan="5"><b>Sorry, could not generate a report for this date.</b></td></tr></table>
    <?php }
}
?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>