<?php
require_once("includes/DAO/BusinessObject/CCustomerReferral.php");

$this->assign('page_title','Session Report');
$this->assign('topnav','reports');
$this->setScript('head', SCRIPT_PATH . '/admin/misc.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/ajax_support.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/no_show.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_datefield.min.js');
$this->setCSS(CSS_PATH . '/admin/admin-styles-reports.css');
if  ($this->print_view == true)
{
	$this->setOnload("$('[id^=\"guest_carryover_note_\"]').show();$('[id^=\"show_guest_carryover_note_\"]').hide();");
}
include $this->loadTemplate('admin/page_header.tpl.php');
?>

<script src="<?php echo SCRIPT_PATH; ?>/admin/vendor/calendarDateInput.js" type="text/javascript"></script>

<style type="text/css">
img
{
	border-style:none;
}
</style>

<script type="text/javascript">
//<![CDATA[
<?php
	$zeroItemsAreHiddenByDefault = CBrowserSession::instance()->getValue('hide_zero_qty_items', true);
	$fastlaneHiddenByDefault = CBrowserSession::instance()->getValue('hide_show_fastlane_sn', true);

	if ($fastlaneHiddenByDefault==true)
	{
		$fastlanecheck = 'checked="checked"';
	}

	$displayProp = CTemplate::isIE() ? "block" : "table-row";
	$initialZeroItemDisplay = $zeroItemsAreHiddenByDefault ? "none" : $displayProp;
?>

var zerosAreHidden = <?=($zeroItemsAreHiddenByDefault ? "true" : "false")?>;
var faslaneIsOPen = <?=($fastlaneHiddenByDefault ? "true" : "false")?>;



function handleFilterInit()
{
    var query = window.location.search.substring(1);
    var URL_without_quwey = window.location.protocol + '//' + window.location.host + window.location.pathname;
    var newQuery = "?";
    var FinalParms = [];
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++)
    {
        var pair = vars[i].split("=");
        if (pair[0] != "filter_to")
        {
            FinalParms.push(vars[i])
        }
    }
    newQuery = newQuery + FinalParms.join("&");

    return URL_without_quwey + newQuery;
}


function warnOfPartial()
{
	alert("Please upgrade this Partial Account account to an Standard Dream Dinners account before placing an order for this customer.");
}

function warnOfPartialEdit()
{
	alert("Please upgrade this Partial Account account to an Standard Dream Dinners account before editing an order for this customer.");
}

function showHideFastLane(checkbox)
{
	var current_date = new Date;
	var cookie_year = current_date.getFullYear ( ) + 1;
	var cookie_month = current_date.getMonth ( );
	var cookie_day = current_date.getDate ( );

	var cookieState = 0;
	if (checkbox.checked)
	{
		cookieState = 1;
	}
	set_cookie('hide_show_fastlane_sn', cookieState, cookie_year, cookie_month, cookie_day);
}

function PrintLabels(urlpass)
{
	var item = document.getElementsByName("FastLane");
	var suppress="&fastlane="+item[0].checked;
	printwindow=window.open(urlpass+suppress);
	//printwindow.print();
}


function PrintLabelsForSummary(urlpass)
{
	var baseURL = "main.php?page=admin_reports_customer_menu_item_labels_multi&report_date=<?=$this->report_date?>&store_id=<?=$this->store_id?>&menuid=<?=$this->menu_id?>&back=<?=urlencode($this->form_submit_string)?>";
	baseURL += urlpass;
	var item = document.getElementsByName("FastLane");
	var suppress="&fastlane="+item[0].checked;
	printwindow=window.open(baseURL+suppress);
	//printwindow.print();
}

function showHideZeros()
{
	if (zerosAreHidden)
	{
		$('[id^="hz_"]').show();
		zerosAreHidden = 0;
	}
	else
	{
		$('[id^="hz_"]').hide();
	 	zerosAreHidden = 1;
	}

	var current_date = new Date;
	var cookie_year = current_date.getFullYear ( ) + 1;
	var cookie_month = current_date.getMonth ( );
	var cookie_day = current_date.getDate ( );

 	set_cookie('hide_zero_qty_items', zerosAreHidden, cookie_year, cookie_month, cookie_day);
}

function filterBySession()
{

    var currentURL = handleFilterInit();

    var URL_Add = "&filter_to=";
    var filterArr = [];
    var allSelected = true;
    var noneAreSelected = true;

    $("[id^='sid_']").each(function(){
        if ($(this).prop("checked"))
        {
            filterArr.push(this.id.split("_")[1]);
            noneAreSelected = false;
        }
        else
        {
            allSelected = false;
        }
    });

    if (noneAreSelected)
    {
        alert("Please select at least 1 session");
    }
    else if (!allSelected)
    {
        currentURL += URL_Add + filterArr.join("|");
        window.location.href = currentURL;
    }
    else
    {
        window.location.href = currentURL;
    }
}

function printSeperatedSession()
{
	var currentURL = handleFilterInit();

	var URL_Add = "&filter_to=";
	var filterArr = [];
	var noneAreSelected = true;

	$("[id^='sid_']").each(function(){
		if ($(this).prop("checked"))
		{
			filterArr.push(this.id.split("_")[1]);
			noneAreSelected = false;
		}
		else
		{
			allSelected = false;
		}
	});

	if (noneAreSelected)
	{
		alert("Please select at least 1 session");
	}
	else
	{
		for (const id of filterArr) {
			var newUrl = currentURL + URL_Add + id;
			window.open(newUrl, '_blank');
		}


	}


}

//]]>
</script>

<?php
if ($this->print_view == false) {
?>

<form action="<?=$this->form_submit_string?>" name="frm" method="post" onsubmit="return _check_form(this);" >
<input type="hidden" name="page" value="admin_reports_select_multi_session" />

<?php
	if (isset($this->form_session_list['store_html']) )
	{
		echo '<strong>Store</strong>' . $this->form_session_list['store_html'] . '<br />';
	}

	$varChecked = "";
	if (!isset($this->session_type_to_run) || $this->session_type_to_run == "1") $varChecked = 'checked="checked"';
	{
		echo '<input type="radio" onclick="hidefields(1)" name="pickSession" value="1" ' . $varChecked . ' />';
	}
?>
Select to view all Sessions for a given day:
<?php
	if (isset($this->report_date))
	{
		echo "<script type='text/javascript'>DateInput('session_day', false, 'YYYY-MM-DD','" . $this->report_date . "')</script>";
	}
	else
	{
		echo "<script type='text/javascript'>DateInput('session_day', true, 'YYYY-MM-DD')</script>";
	}
?>
<hr />
<?php
	$varChecked = "";
	if ($this->session_type_to_run == "2") $varChecked = 'checked="checked"';
	{
		echo '<input type="radio" onclick="hidefields(2)" name="pickSession" value="2" ' . $varChecked . ' />';
	}
	?>
Pick an individual session:

<br />

<?php echo $this->form_session_list['sessionpopup_html']; ?>

<br />
<hr />
<?php echo $this->form_session_list['report_submit_html']; ?>

<?php
if (!isset($this->session_type_to_run) || $this->session_type_to_run == 1)
{
 	echo '<script type="text/javascript">hidefields(1);</script>';
}
else
{
 	echo '<script type="text/javascript">hidefields(2);</script>';
}
?>

<hr />

</form>

<?php
} // THIS IS FOR SHOWING PRINT VERSION
?>

<table class="report" width="100%" border="0" cellpadding="2" cellspacing="2">

<?php if ($this->print_view == FALSE && isset($this->run_report) && isset($this->report_type) && $this->run_report == true && isset($this->report_tab_data) && $this->report_type == 1 && count($this->report_tab_data) > 0) { ?>
<tr>
	<td>
		<table width="100%" border="0">
		<tr>
			<td colspan="4"><font size="3" color="#480000"><b>Session Tools</b></font></td>
		</tr>
		<tr>
			<td><a target="_print" href="main.php?page=admin_order_details_view_all_multi&amp;report_date=<?=$this->report_date?>&amp;store_id=<?=$this->store_id?>&amp;back=<?=urlencode($this->form_submit_string)?>">Franchise View <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>
			<td><a target="_print" href="main.php?page=admin_order_details_view_all_multi&amp;customer_print_view=1&amp;report_date=<?=$this->report_date?>&amp;store_id=<?=$this->store_id?>&amp;back=<?=urlencode($this->form_submit_string)?>">Customer View <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>
			<td><a target="_print" href="main.php?page=admin_order_details_view_all_multi&amp;report_date=<?=$this->report_date?>&amp;store_id=<?=$this->store_id?>&amp;issidedish=1&amp;back=<?=urlencode($this->form_submit_string)?>">Side Dish Report <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>
			<td><a target="_print" href="main.php?page=admin_order_details_view_all_multi&amp;customer_print_view=1&amp;report_date=<?=$this->report_date?>&amp;store_id=<?=$this->store_id?>&amp;ispreassembled=1&amp;back=<?=urlencode($this->form_submit_string)?>">Fast Lane Report <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>
		</tr>
		<tr>
			<td><a target="_print" href="main.php?page=admin_reports_dream_rewards_for_session_multi&amp;report_date=<?=$this->report_date?>&amp;store_id=<?=$this->store_id?>&amp;menuid=<?=$this->menu_id?>&amp;back=<?=urlencode($this->form_submit_string)?>">Print Dream Rewards <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>
			<td><a target="_print" href="main.php?page=admin_order_details_view_all_future_multi&amp;report_date=<?=$this->report_date?>&amp;store_id=<?=$this->store_id?>&amp;back=<?=urlencode($this->form_submit_string)?>">Future Orders <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>
			<td><a target="_print" href="main.php?page=admin_reports_goal_tracking&amp;multi_session=<?=$this->report_date?>&amp;store_id=<?=$this->store_id?>&amp;report_submit=true&amp;print=true">Session Goal Sheet <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>
			<td><a href="main.php?page=admin_reports_goal_tracking&amp;export=xlsx&amp;hideheaders=true&amp;csvfilename=SessionGoalSheetSummary&amp;multi_session=<?=$this->report_date?>&amp;store_id=<?=$this->store_id?>&amp;report_submit=true">Session Goal Sheet <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/page_excel.png" alt="Excel" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>
		</tr>
		<?php if ($this->menu_id > 137) { ?>
		<tr>
			<td colspan="4">
				<a target="_print" href="main.php?page=admin_finishing_touch_printable_form&amp;store_id=<?= $this->store_id ?>&amp;menu_id=<?= $this->menu_id ?>&amp;back=<?= urlencode($this->form_submit_string) ?>">Print
					Sides &amp; Sweets Pick Sheet <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;"/></a></td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="4" style="padding-top: 10px;">
				<font size="3" color="#480000"><b>Customer Cooking Instruction Labels</b></font>
				<input type="checkbox" onclick="showHideFastLane(this);" id="FastLane" name="FastLane" <?= $fastlanecheck ?> />&nbsp;<i>Suppress FastLane Label Printing</i>
			</td>
		</tr>
		<tr>
			<td colspan="1"><a href="#" onclick="PrintLabelsForSummary('');">Print Labels <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png"
			alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>

			<td colspan="1"><a href="#" onclick="PrintLabelsForSummary('&amp;break=1');">Print Labels w/ Breaks <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png"
			 alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>

			<td colspan="2"><a href="#" onclick="PrintLabelsForSummary('&amp;order_by=dinner');">Print Labels by Dinner <img src="<?php echo ADMIN_IMAGES_PATH; ?>/icon/printer.png"
			 alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>
		</tr>
		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>
		</table>
	</td>
</tr>
<?php } ?>

<?php
if (isset($this->run_report) && isset($this->report_type) && $this->run_report== true && isset($this->report_tab_data))
{
	if ($this->session_type_to_run == 2 || $this->report_type > 1)
	{
		$results_display = "";
		$results_display = '<tr>';

	 	if ($this->print_view == false)
	 	{
			$results_display .= '<td><table width="100%" border="0">';
			$results_display .= '<tr>';
			$results_display .=	'<td colspan="4"><font size="3" color="#480000"><b>Session Tools</b></font></td>';
			$results_display .= '</tr>' . "\n";

			if ($this->report_type ==2 && $this->session_type_to_run == 2)
			{
				$results_display .= "<tr>";
				$results_display .= '<td><a target="_print" href="main.php?page=admin_order_details_view_all&amp;session_id=' . $this->sessionID . '&amp;menuid=' . $this->menu_id . '&amp;back='.urlencode($this->form_submit_string).'">Franchise View <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
				$results_display .= '<td><a target="_print" href="main.php?page=admin_order_details_view_all&amp;customer_print_view=1&amp;session_id=' . $this->sessionID . '&amp;menuid=' . $this->menu_id . '&amp;back='.urlencode($this->form_submit_string).'">Customer View <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';

				if ($this->menu_id >= 77 ) {
					$results_display .= '<td><a target="_print" href="main.php?page=admin_order_details_view_all&amp;session_id=' . $this->sessionID . '&amp;menuid=' . $this->menu_id . '&amp;issidedish=1&amp;back='.urlencode($this->form_submit_string).'">Side Dish Report <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= '<td><a target="_print" href="main.php?page=admin_order_details_view_all&amp;customer_print_view=1&amp;session_id=' . $this->sessionID . '&amp;menuid=' . $this->menu_id . '&amp;ispreassembled=1&amp;back='.urlencode($this->form_submit_string).'">Fast Lane Report <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
				}
				else
				{
					$results_display .= "<td>&nbsp;</td>" ;
					$results_display .= "<td>&nbsp;</td>" ;
				}

				$results_display .= "</tr>" . "\n";


				if ($this->menu_id > 137)
				{
					$results_display .= '<tr><td colspan="4"><a target="_print" href="main.php?page=admin_finishing_touch_printable_form&amp;store_id=' . $this->store_id . '&amp;menu_id=' . $this->menu_id . '&amp;back=' . urlencode($this->form_submit_string) . '">Print Sides &amp; Sweets Pick Sheet <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td></tr>';
				}


				if (isset($this->show_labels) && $this->show_labels == true)
				{

					$results_display .= "<tr>" ;
					$results_display .= '<td><a target="_print" href="main.php?page=admin_reports_dream_rewards_for_session&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;menuid=' . $this->menu_id . '&amp;back='.urlencode($this->form_submit_string).'">Print Dream Rewards <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= '<td><a target="_print" href="main.php?page=admin_order_details_view_all_future&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;back='.urlencode($this->form_submit_string).'">Future Orders <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= '<td><a target="_print" href="main.php?page=admin_reports_goal_tracking&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;report_submit=true&amp;print=true">Session Goal Sheet <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= '<td><a href="main.php?page=admin_reports_goal_tracking&amp;export=xlsx&amp;hideheaders=true&amp;csvfilename=SessionGoalSheet&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;report_submit=true">Session Goal Sheet <img src="' . ADMIN_IMAGES_PATH . '/icon/page_excel.png" alt="Excel" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= "</tr>" . "\n";

					$results_display .= "<tr>";
					$results_display .=	"<td colspan='2' style='padding-top: 10px;'><font size='3' color='#480000'><b>Customer Cooking Instruction Labels</b></font></td>";
					$results_display .= "<td colspan='2' style='padding-top: 10px;'><input type='checkbox' onclick='showHideFastLane(this);' name='FastLane' " . $fastlanecheck . " />&nbsp;<i>Supress FastLane Label Printing</i></td>";
					$results_display .= "</tr>" . "\n";

					$results_display .= "<tr>" . "\n";

					$url1 = 'main.php?page=admin_reports_customer_menu_item_labels&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;menuid=' . $this->menu_id . '&amp;back='.urlencode($this->form_submit_string);

					$results_display .= '<td colspan="1"><a onclick="PrintLabels(\'' . $url1 . '\');" href="#">Print Labels <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= '<td colspan="1"><a onclick="PrintLabels(\'' . $url1 . '&amp;break=1\');" href="#">Print Labels w/ Breaks <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= '<td colspan="2"><a onclick="PrintLabels(\'' . $url1 . '&amp;order_by=dinner\');" href="#">Print Labels by Dinner <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';

					$results_display .= "</tr>" . "\n";

					$results_display .= "<tr>" ;
					$results_display .=	"<td colspan='4' style='padding-top: 10px;' ><font size='3' color='#480000'><b>General Cooking Instruction Labels</b></font></td>";
					$results_display .= "</tr>" . "\n";

					$results_display .= "<tr>" ;
					$results_display .= '<td colspan="4"><a target="_print" href="main.php?page=admin_reports_customer_menu_item_labels&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;interface=1&amp;menuid=' . $this->menu_id . '&amp;back='.urlencode($this->form_submit_string).'">Print Generic Labels <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= "</tr>" . "\n";
				}
			}
			else
			{
				$results_display .= "<tr>" ;

				$results_display .= '<td><a target="_print" href="main.php?page=admin_order_details_view_all&amp;session_id=' . $this->sessionID . '&amp;menuid=' . $this->menu_id . '&amp;back='.urlencode($_SERVER['REQUEST_URI']).'">Franchise View <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';

				$results_display .= '<td><a target="_print" href="main.php?page=admin_order_details_view_all&amp;customer_print_view=1&amp;session_id=' . $this->sessionID . '&amp;menuid=' . $this->menu_id . '&amp;back='.urlencode($_SERVER['REQUEST_URI']).'">Customer View <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>' ;

				if ($this->menu_id >= 77 )
				{
					$results_display .= '<td><a target="_print" href="main.php?page=admin_order_details_view_all&amp;session_id=' . $this->sessionID . '&amp;menuid=' . $this->menu_id . '&amp;issidedish=1&amp;back='.urlencode($_SERVER['REQUEST_URI']).'">Side Dish Report <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= '<td><a target="_print" href="main.php?page=admin_order_details_view_all&amp;customer_print_view=1&amp;session_id=' .
					$this->sessionID . '&amp;menuid=' . $this->menu_id . '&amp;ispreassembled=1&amp;back='.urlencode($_SERVER['REQUEST_URI']).'">Fast Lane Report <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>' ;
				}
				else
				{
					$results_display .= "<td>&nbsp;</td>" ;
					$results_display .= "<td>&nbsp;</td>" ;
				}

				$results_display .= "</tr>" . "\n";


				$results_display .= "<tr>" ;
				$results_display .= '<td><a target="_print" href="main.php?page=admin_reports_dream_rewards_for_session&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;menuid=' . $this->menu_id . '&amp;back='.urlencode($this->form_submit_string).'">Print Dream Rewards <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
				$results_display .= '<td><a target="_print" href="main.php?page=admin_order_details_view_all_future&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;back='.urlencode($this->form_submit_string).'">Future Orders <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
				$results_display .= '<td><a target="_print" href="main.php?page=admin_reports_goal_tracking&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;report_submit=true&amp;print=true">Session Goal Sheet <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
				$results_display .= '<td><a href="main.php?page=admin_reports_goal_tracking&amp;export=xlsx&amp;hideheaders=true&amp;csvfilename=SessionGoalSheet&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;report_submit=true">Session Goal Sheet <img src="' . ADMIN_IMAGES_PATH . '/icon/page_excel.png" alt="Excel" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
				$results_display .= "</tr>" . "\n";

				if ($this->menu_id > 137)
				{
					$results_display .= '<tr><td colspan="4"><a target="_print" href="main.php?page=admin_finishing_touch_printable_form&amp;store_id=' . $this->store_id . '&amp;menu_id=' . $this->menu_id . '&amp;back=' . urlencode($this->form_submit_string) . '">Print Sides &amp; Sweets Pick Sheet <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td></tr>';
				}


				if (isset($this->show_labels) && $this->show_labels == true)
				{

					$results_display .= "<tr>" ;
					$results_display .=	"<td colspan='2' style='padding-top: 10px;' ><font size='3' color='#480000'><b>Customer Cooking Instruction Labels</b></font></td>";
					$results_display .= "<td colspan='2' style='padding-top: 10px;' ><input type='checkbox' onclick='showHideFastLane(this);' id='FastLane' name='FastLane' " . $fastlanecheck . " />&nbsp;<i>Supress FastLane Label Printing</i></td>";
					$results_display .= "</tr>" ;

					$results_display .= "<tr>" . "\n";

					$url1 = 'main.php?page=admin_reports_customer_menu_item_labels&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;menuid=' . $this->menu_id . '&amp;back='.urlencode($this->form_submit_string);

					$results_display .= '<td colspan="1"><a href="#" onclick="PrintLabels(\'' . $url1 . '\');">Print Labels <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= '<td colspan="1"><a href="#" onclick="PrintLabels(\'' . $url1 . '&amp;break=1\');">Print Labels w/ Breaks <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= '<td colspan="2"><a href="#" onclick="PrintLabels(\'' . $url1 . '&amp;order_by=dinner\');">Print Labels by Dinner <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';

					$results_display .= "</tr>" . "\n";

					$results_display .= "<tr>" ;
					$results_display .=	"<td colspan='4' style='padding-top: 10px;' ><font size='3' color='#480000'><b>General Cooking Instruction Labels</b></font></td>";
					$results_display .= "</tr>" . "\n";

					$results_display .= "<tr>" ;
					$results_display .= '<td colspan="4"><a target="_print" href="main.php?page=admin_reports_customer_menu_item_labels&amp;session_id=' . $this->sessionID . '&amp;store_id=' . $this->store_id . '&amp;interface=1&amp;menuid=' . $this->menu_id . '&amp;back='.urlencode($this->form_submit_string).'">Print Generic Labels <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
					$results_display .= "</tr>" . "\n";

				}

			}

			$results_display .= "<tr>" ;
			$results_display .=	"<td colspan='5' >&nbsp;</td>";
			$results_display .= "</tr>" . "\n";

			$results_display .= "</table>";

		}
		$results_display .= '</td></tr>' . "\n";
		echo $results_display;
	}
}

?>

<?php
	// short circut everything if there isn't a session with any orders
	if (isset($this->run_report) && $this->run_report == TRUE && $this->is_valid_session == FALSE)
	{
		if ($this->session_type_to_run == 1 && $this->report_type == 1)
		{
			if (count($this->report_tab_data) > 0)
			{
				$r_display = '<tr><td class="headers" style="padding-left: 2px;" colspan="5"><b>These sessions do exist but no orders have been placed against them:</b></td></tr>'. "\n";
				echo $r_display;

				foreach ($this->report_tab_data as $entity)
				{
					$convertTimeStamp = CSessionReports::convertTimeStamp ($entity['session_start']);
					$r_display = '<tr><td class="headers" style="padding-left: 2px;" colspan="5"><b>' . $convertTimeStamp . '</b></td></tr>' . "\n";
					echo $r_display;
				}
			}
			else
			{
				$r_display = '<tr><td class="headers" style="padding-left: 2px;" colspan="5"><b>No Sessions have been created for this day.</b></td></tr>' . "\n";
				echo $r_display;
			}
			echo "</tr></table>";
			include $this->loadTemplate('admin/page_footer.tpl.php');
			return;
		}
	}
?>

<?php
// someone clicked on an actual tab.. report the session data for that tab...
if ( $this->print_view == false && isset($this->report_submit) && isset($this->run_report) && isset($this->report_tab_data) && $this->session_type_to_run == 1)
{

	if ($this->report_type == 1)
	{
		echo '<a href="#" class="fadmin_nav fadmin_subnav fadmin_subnav_active" onclick="return false;">Summary</a>';
	}
	else
	{
		echo '<a class="fadmin_nav fadmin_subnav" href="main.php?page=admin_reports_select_multi_session&amp;query_submit=1&amp;report_date=' . $this->report_date . '&amp;report_id=1">Summary</a>';
	}

	$varTabId = 2;
	foreach ($this->report_tab_data as $entity)
	{
		$sqltime = $entity['session_start'];
		$sep = explode(" ",$sqltime);

		$convertTimeStamp = CSessionReports::convertTimeStamp ($sqltime);
		$varcloseString = $convertTimeStamp;
		if ($entity['session_publish_state'] == "CLOSED")
		{
			$varcloseString = $convertTimeStamp . '<br />' . 'CLOSED' ;
		}

		if ($entity['id'] == $this->sessionID)
		{
			echo '<a href="#" class="fadmin_nav fadmin_subnav fadmin_subnav_active" onclick="return false;">' . $varcloseString . '</a>';
		}
		else
		{
			echo '<a class="fadmin_nav fadmin_subnav" href="main.php?page=admin_reports_select_multi_session&amp;query_submit=1&amp;report_date=' . $sep[0] . '&amp;report_id=' . $varTabId . '&amp;session_id='. $entity['id'] . '">' . $varcloseString . '</a>';
		}
		$varTabId++;
	}
}
?>

<?php

if (isset($this->run_report) && $this->run_report== true && isset($this->report_tab_data))
{

	if (!isset ($this->menu_array) || count($this->menu_array) == 0)
	{
	 	$r_display = '<tr><td class="headers" style="padding-left: 2px;" colspan="5"><b>Sorry, no orders have been placed against this session.</b></td></tr>' . "\n";
		echo $r_display;
		echo "</tr></table>";
		include $this->loadTemplate('admin/page_footer.tpl.php');
		return;
	}

	$hasSessionFilter = false;
	$results_display = "";
	if ($this->report_type > 1 && $this->print_view == true)
	{
		$dateFormat = CSessionReports::newDayFormat ($this->printdate);
		$convertTimeStamp = CSessionReports::convertTimeStamp ($this->printdate);
		$results_display .= '<td class="headers" style="padding-left: 2px;" colspan="0"><b>Session Time: ' . $dateFormat . '&nbsp;' . $convertTimeStamp . '</b></td>';
		$results_display .= '</tr>' . "\n";
	}
	else
	{

        if (!empty($this->sessionArray) && count($this->sessionArray) > 1)
        {
            $currentFilter = false;

            if (!empty($_REQUEST['filter_to']))
            {
                $currentFilter = explode("|", $_REQUEST['filter_to']);
            }

            $delayedOutput = "";

            if ($currentFilter)
            {
                $filterDesc = " Filtered to sessions: ";
            }
            else
            {
                $filterDesc = " All Sessions ";

            }

            foreach ($this->sessionArray as $thisSession)
            {
                $CHECKED = "";
                if (empty($currentFilter) || in_array($thisSession['session_id'], $currentFilter))
                {
                    $CHECKED = 'checked="checked"';

                    if ($currentFilter)
                    {
                        $filterDesc .= CTemplate::dateTimeFormat($thisSession['session_start']) . "; ";
                    }
                }

                $delayedOutput .= "<tr class='rsms_session_filter' style='display:none;'><td>";// . print_r($thisSession, true);

				$date = CTemplate::dateTimeFormat($thisSession['session_start']);
				if(strtoupper($thisSession['session_type_string']) == CSession::WALK_IN){
					$date = CTemplate::dateTimeFormat($thisSession['session_start'],NORMAL_NO_TIME);
				}

                $delayedOutput .= '<input type="checkbox" id="sid_' . $thisSession['session_id'] . '"  name="sid_' . $thisSession['session_id'] .
                    '" ' . $CHECKED . ' /><label for="sid_' . $thisSession['session_id'] . '" >' . $date . " - ".$thisSession['session_type_title']."</label>";



                $delayedOutput .= "</td></tr>";

                $hasSessionFilter = true;
            }

            $delayedOutput .= '<tr class=\'rsms_session_filter\' style=\'display:none;\'><td><span class="button" onclick="filterBySession()" >Filter by Selected Sessions</span> &nbsp;&nbsp;';
			$delayedOutput .= '<span class="button" onclick="printSeperatedSession()" >Print Selected Sessions Separately</span></td></tr>';



		}
        $results_display .= '<td class="headers" style="padding-left: 2px;" colspan="0"><b>Session Date: ' . CTemplate::dateTimeFormat($this->printdate, MONTH_DAY_YEAR) . '</b>' . $filterDesc . '</td>';
		$results_display .= '</tr>' . "\n";



	}

	$results_display .= '<tr>';
	$results_display .= '<td style="padding-left: 0px;" colspan="1">';
	$results_display .= '<font size="3" color="#480000"><b>Entr&eacute;e Summary</b></font>';

    if (!empty($this->sessionArray) && count($this->sessionArray) > 1)
    {
        $results_display .= '&nbsp;<span>(<a href="javascript:$(\'.rsms_session_filter\').toggle();">Toggle Session Filter</a>)</span>';
    }

	if ($this->print_view == false)
	{
		if ($this->session_type_to_run == 1 && $this->report_type == 1)
		{
			$results_display .= ' <a target="_printer" href="main.php?page=admin_reports_select_multi_session&amp;query_submit=1&amp;report_date=' . $sep[0] . '&amp;report_id=1&amp;printer=1&amp;session_id='. $entity['id'] . '">Printer-Friendly Version <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
		}
		else
		{
			$results_display .= ' <a target="_printer" href="main.php?page=admin_reports_select_multi_session&amp;query_submit=1&amp;report_id=' . $this->report_type . '&amp;printer=1&amp;pickSession=2&amp;session_id='. $this->sessionID . '">Printer-Friendly Version <img src="' . ADMIN_IMAGES_PATH . '/icon/printer.png" alt="Print" style="vertical-align:middle;margin-bottom:.25em;" /></a></td>';
		}

	}
	$results_display .= '</tr>' . "\n";

    $results_display .= $delayedOutput;

    echo $results_display;
}
?>

<?php
$counter = 0;
// This if for entree items only
// do not include Side Dishes or Pre assembled items
if (isset($this->run_report) && $this->run_report== true && isset($this->report_tab_data))
{
	if (isset ($this->menu_array) || count($this->menu_array) > 0)
	{
		$results_display = "";
		$varcounter = 0;
		$halfcounter = 0;
		$fullcounter = 0;
		$servingsCounterTotal = 0;
		$promo_large = 0;
		$promo_med = 0;
		$introcounter = 0;
		$total_dinners_for_ordering = 0;
		$lastcategory = null;

		foreach ($this->menu_array as $entity)
		{
			if ((!empty($entity['sidedishes']) && $entity['sidedishes'] == 1) || $entity['categories_type'] == 'KidsChoice' || $entity['categories_type'] == 'Add-on Items' || ($entity['is_chef_touched']))
			{
				continue;
			}

			if ($counter == 0)
			{
?>
				<tr bgcolor="#686868">
					<td>
						<table width="100%" class="subheaders" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td width="220" style="padding-left: 2px;"><font color="white"><b>Entr&eacute;e Name</b></font>&nbsp;</td>
							<?php if ($this->is_test_store) {?>
								<td width="40"><font color="white"><b>Small Items</b></font></td>
								<td width="40"><font color="white"><b>Md (3) Items</b></font></td>
								<td width="40"><font color="white"><b>Md (4) Items</b></font></td>
							<?php } else { ?>
								<td width="40"><font color="white"><b>Medium Items</b></font></td>
							<?php }  ?>
							<td width="40"><font color="white"><b>Large Items</b></font></td>
							<td width="40" align="center"><font color="white"><b>Total Items</b></font></td>
							<td width="40" align="center" ><font color="white"><b>Total Srv</b></font></td>
							<td width="40" align="center"><font color="white"><b>Total Dinners for Ordering</b></font></td>
						</tr>
						</table>
					</td>
				</tr>
<?php
			}
			$counter++;

			$entree = $entity['menu_name'];
			$category = isset($entity['categories_type']) ? $entity['categories_type'] : null;

			if($category == "Fast Lane" && $entity['storespecial'] == "1" && $entity['preassembled'] == "1"){
				$category = 'Add On Dinners';
			}
			if($category == "Fast Lane" && $entity['storespecial'] == "0" && $entity['preassembled'] == "1"){
				$category = '&nbsp;</td></tr><tr><td><strong><font size="3" color="#480000">Pre-Assembled';
			}

			if($category == "Specials" && $entity['is_bundle'] == "1"){
				$category = 'Holiday and Dinner Bundles';
			}

			if (!empty($category))
			{
				if (empty($lastcategory) || $category != $lastcategory)
				{
					$lastcategory = $category;
					$results_display .= '<tr><td><strong><font size="3" color="#480000">' . $lastcategory . '</font></strong></td></tr>' . "\n";
				}
			}

			$quantity = $entity['count'];
			$servingsCounter = $entity['servings_sold'];
			$DinnersforOrder = $entity['actual_dinners'];

			$results_display .= '<tr>';
			$results_display .= '<td colspan="5">';
			$results_display .= '<table width="100%" class="subheaders" cellpadding="0" cellspacing="0" border="0">';
			$results_display .= '<tr>';
			$results_display .= '<td class="subheaders" width="200" style="padding-left: 2px;" align="left">' . htmlspecialchars($entree) . '</td>';
			if ($this->is_test_store) {
				$results_display .= '<td class="subheaders" width="40" style="padding-right: 0px;" align="center">'. $entity[CMenuItem::TWO] . '</td>';
				$results_display .= '<td class="subheaders" width="40" style="padding-right: 0px;" align="center">'. $entity[CMenuItem::HALF] . '</td>';
				$results_display .= '<td class="subheaders" width="40" style="padding-right: 0px;" align="center">'. $entity[CMenuItem::FOUR] . '</td>';
			} else {
				$results_display .= '<td class="subheaders" width="40" style="padding-right: 0px;" align="center">'. $entity[CMenuItem::HALF] . '</td>';

			}
			$results_display .= '<td class="subheaders" width="40" style="padding-right: 0px;" align="center">'. $entity[CMenuItem::FULL] . '</td>';
			$results_display .= '<td class="subheaders" width="40" style="padding-left: 0px;" align="center">'. $quantity . '</td>';
			$results_display .= '<td class="subheaders" width="40" style="padding-left: 0px;" align="center">' . $servingsCounter . '</td>';
			$results_display .= '<td class="subheaders" width="40" bgcolor="LightGrey" style="padding-left: 2px;" align="center">' . $DinnersforOrder . '</td>';
			$results_display .= '</tr>';
			$results_display .= '</table>' . "\n";
			$results_display .= '</td>';
			$results_display .= '</tr>';

			$promo_large += $entity['PROMO_FULL'];
			$promo_med += $entity['PROMO_HALF'];
			$servingsCounterTotal += $servingsCounter;
			$halfcounter += $entity[CMenuItem::HALF] ;
			$twocounter += $entity[CMenuItem::TWO];
			$fourcounter += $entity[CMenuItem::FOUR] ;
			$fullcounter += $entity[CMenuItem::FULL];
			$introcounter += $entity[CMenuItem::INTRO];
			$varcounter += $quantity;
			$total_dinners_for_ordering += $DinnersforOrder;
		}

		$results_display .= '<tr>';
		$results_display .= '<td colspan="5">';
		$results_display .= '<table bgcolor="#989898" width="100%" cellpadding="0" cellspacing="0" border="0">';
		$results_display .= '<tr>';
		$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>Entr&eacute;e Totals:</b></font></td>';
		if ($this->is_test_store)
		{
			$results_display .= '<td width="40" style="padding-right: 0px;" align="center"><font color="white">' . $twocounter . '</font></td>';
			$results_display .= '<td width="40" style="padding-right: 0px;" align="center"><font color="white">' . $halfcounter . '</font></td>';
			$results_display .= '<td width="40" style="padding-right: 0px;" align="center"><font color="white">' . $fourcounter . '</font></td>';
		}else{
			$results_display .= '<td width="40" style="padding-right: 0px;" align="center"><font color="white">' . $halfcounter . '</font></td>';
		}
		$results_display .= '<td width="40" style="padding-right: 0px;" align="center"><font color="white">'. $fullcounter . '</font></td>';
		$results_display .= '<td width="40" style="padding-left: 6px;" align="center"><font color="white">'. $varcounter . '</font></td>';
		$results_display .= '<td width="40" style="padding-left: 0px;" align="center"><font color="white">'. $servingsCounterTotal . '</font></td>';
		$results_display .= '<td width="40" bgcolor="#989898" style="padding-left: 0px;" align="center"><font color="white">'. $total_dinners_for_ordering . '</font></td>';
		$results_display .= '</tr>';
		$results_display .= '</table>' . "\n";
		$results_display .= '</td></tr>' . "\n";

		echo $results_display;
	}
}
?>

<?php if (isset($this->run_report) && $this->run_report == true ) { ?>
<tr><td>&nbsp;</td></tr>
<tr><td class="headers"><font size="3" color="#480000"><b>Meal Prep Starter Pack Bundles</b></font></td></tr>
<tr><td>
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td class="subheaders" width="200" style="padding-left: 2px;" align="left">Number of Meal Prep Starter Pack Sold</td>
<td class="subheaders" width="20" align="center" ><?=$this->bundleOfferCount?></td>
</tr>
</table>
</td></tr>

<?php } ?>

<?php
if ($this->print_view == false) {
?>
<tr><td>&nbsp;</td></tr>
<tr style="display:<?=(isset($this->run_report) && $this->run_report) ? "$displayProp" : "none" ?>"><td class="headers"><font size="3" color="#480000"><b>Additional Menu Items</b></font></td></tr>
<tr style="display:<?=(isset($this->run_report) && $this->run_report) ? "$displayProp" : "none" ?>"><td>
		<input type="checkbox" id="showZeros" name="showZeros" onclick="showHideZeros();" <?= $zeroItemsAreHiddenByDefault ? 'checked="checked"' : ''; ?> /><label for="showZeros">Show only items sold
			(uncheck to show all)</label>
</td></tr>
<?php
}
?>

<?php
$rowCounter = 0;
// This is for Seasonal Bundles category
if (false && isset($this->run_report) && $this->run_report== true && isset($this->report_tab_data)) {
	if (isset ($this->menu_array) || count($this->menu_array) > 0) {

		$totalcount = 0;
		$arraypre = array();
		foreach ($this->menu_array as $entity) {
			if (!empty($entity['is_bundle']) && $entity['is_bundle']) {
				$arraypre[$entity['id']] = array($entity['menu_name'], $entity['count']);
				$totalcount += $entity['count'];
			}
		}

		if (count($arraypre) > 0) {

			$results_display = "";

			$results_display .= '<tr><td><font size="3" color="#480000"><b>Holiday and Dinner Bundles Menu Items</b></font></td></tr>' . "\n";
			$results_display .= '<tr>';
			$results_display .= '<td style="padding-left: 0px;" colspan="0">';
			$results_display .= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
			$results_display .= '<tr bgcolor="#686868">';
			$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>Meal Name</b></font></td>';
			$results_display .= '<td width="20" align="center" style="padding-right: 2px;" ><font color="white"><b>Holiday and Dinner Bundles Sold</b></font></td>';
			$results_display .= '</tr>' . "\n";

			foreach($arraypre as $element)
			{
				$results_display .= '<tr id="'. ($element[1] == "0" ? "hz_" : "nhz_") . (++$rowCounter) . '" style="display:' . ($element[1] == "0" ? "$initialZeroItemDisplay" : "$displayProp"). '">';
				$results_display .= '<td class="subheaders" width="200" style="padding-left: 2px;" align="left">' . htmlspecialchars($element[0]) . '</td>';
				$results_display .= '<td class="subheaders" width="20" align="center" >' . $element[1] . '</td>';
				$results_display .= '</tr>' . "\n";
			}

			$results_display .= '<tr bgcolor="#989898">';
			$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>Holiday and Dinner Bundles Totals:</b></font></td>';
			$results_display .= '<td width="20" align="center" ><font color="white"><b>' . $totalcount . '</b></font></td>';
			$results_display .= '</tr>' . "\n";
			$results_display .= '<tr><td>&nbsp;</td></tr>';
			$results_display .= '</table>';
			$results_display .= '</tr>' . "\n";

			echo $results_display;
		}
	}
}
?>

<?php

// This is for KidsChoices category
if (isset($this->run_report) && $this->run_report== true && isset($this->report_tab_data)) {
	if (isset ($this->menu_array) || count($this->menu_array) > 0) {

		$totalcount = 0;
		$arraypre = array();
		foreach ($this->menu_array as $entity)
		{
			if (!empty($entity['categories_type']) && $entity['categories_type'] == 'KidsChoice') {
				$arraypre[$entity['id']] = array($entity['menu_name'], $entity['count']);
				$totalcount += $entity['count'];
			}
		}

		if (count($arraypre) > 0)
		{
			$results_display = "";
			$results_display .= '<tr><td>&nbsp;</td></tr>' . "\n";
			$results_display .= '<tr><td><font size="3" color="#480000"><b>KidsChoice Menu Items</b></font></td></tr>' . "\n";
			$results_display .= '<tr>';
			$results_display .= '<td style="padding-left: 0px;" colspan="0">';
			$results_display .= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
			$results_display .= '<tr bgcolor="#686868">';
			$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>Meal Name</b></font></td>';
			$results_display .= '<td width="20" align="center" style="padding-right: 2px;" ><font color="white"><b>KidChoice Meals Sold</b></font></td>';
			$results_display .= '</tr>' . "\n";

			foreach($arraypre as $element)
			{
				$results_display .= '<tr id="'. ($element[1] == "0" ? "hz_" : "nhz_") . (++$rowCounter) . '" style="display:' . ($element[1] == "0" ? "$initialZeroItemDisplay" : "$displayProp"). '">';
				$results_display .= '<td class="subheaders" width="200" style="padding-left: 2px;" align="left">' . htmlspecialchars($element[0]) . '</td>';
				$results_display .= '<td class="subheaders" width="20" align="center" >' . $element[1] . '</td>';
				$results_display .= '</tr>' . "\n";
			}

			$results_display .= '<tr bgcolor="#989898">';
			$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>KidsChoice Totals:</b></font></td>';
			$results_display .= '<td width="20" align="center"><font color="white"><b>' . $totalcount . '</b></font></td>';
			$results_display .= '</tr>' . "\n";
			$results_display .= '<tr><td>&nbsp;</td></tr>';
			$results_display .= '</table>';
			$results_display .= '</tr>' . "\n";

			echo $results_display;
		}
	}
}
?>

<?php
// This is for Side Dishes Only
if (isset($this->run_report) && $this->run_report== true && isset($this->report_tab_data)) {
	if (isset ($this->menu_array) || count($this->menu_array) > 0) {

		$totalcount = 0;
		$arraypre = array();
		foreach ($this->menu_array as $entity)
		{
			if (!empty($entity['sidedishes']) && $entity['sidedishes'] == 1)
			{
				$arraypre[$entity['id']] = array($entity['menu_name'], $entity['count']);
				$totalcount += $entity['count'];
			}
		}

		if (count($arraypre) > 0)
		{
			$results_display = "";
			$results_display .= '<tr><td>&nbsp;</td></tr>' . "\n";
			$results_display .= '<tr><td><font size="3" color="#480000"><b>Side Dish Menu Items</b></font></td></tr>' . "\n";
			$results_display .= '<tr>';
			$results_display .= '<td style="padding-left: 0px;" colspan="0">';
			$results_display .= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
			$results_display .= '<tr bgcolor="#686868">';
			$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>Side Dish Name</b></font></td>';
			$results_display .= '<td width="20" align="center" style="padding-right: 2px;" ><font color="white"><b>Side Dishes Sold</b></font></td>';
			$results_display .= '</tr>' . "\n";

			foreach($arraypre as $element)
			{
				$results_display .= '<tr id="'. ($element[1] == "0" ? "hz_" : "nhz_") . (++$rowCounter) . '" style="display:' . ($element[1] == "0" ? "$initialZeroItemDisplay" : "$displayProp"). '">';
				$results_display .= '<td class="subheaders" width="200" style="padding-left: 2px;" align="left">' . htmlspecialchars($element[0]) . '</td>';
				$results_display .= '<td class="subheaders" width="20" align="center">' . $element[1] . '</td>';
				$results_display .= '</tr>' . "\n";
			}

			$results_display .= '<tr bgcolor="#989898">';
			$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>Side Dish Totals:</b></font></td>';
			$results_display .= '<td width="20" align="center"><font color="white"><b>' . $totalcount . '</b></font></td>';
			$results_display .= '</tr>' . "\n";
			$results_display .= '<tr><td>&nbsp;</td></tr>' . "\n";
			$results_display .= '</table>';
			$results_display .= '</tr>' . "\n";

			echo $results_display;
		}
	}
}
?>

<?php
// This is for Chef Touched Selections category
if (isset($this->run_report) && $this->run_report== true && isset($this->report_tab_data))
{
	if (isset ($this->menu_array) || count($this->menu_array) > 0)
	{
		$totalcount = 0;
		$arraypre = array();

		foreach ($this->menu_array as $entity)
		{
			if ((!empty($entity['categories_type']) && $entity['categories_type'] == 'Chef Touched Selections') || ($entity['is_chef_touched'] && $entity['categories_type'] == "Station 4"))
			{
				$arraypre[$entity['id']] = array($entity['menu_name'], $entity['count']);
				$totalcount += $entity['count'];
			}
		}

		if (count($arraypre) > 0)
		{
			$results_display = "";
			$results_display .= '<tr><td>&nbsp;</td></tr>' . "\n";
			$results_display .= '<tr><td><font size="3" color="#480000"><b>Sides &amp; Sweets Items</b></font></td></tr>' . "\n";
			$results_display .= '<tr>';
			$results_display .= '<td style="padding-left: 0px;">';
			$results_display .= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
			$results_display .= '<tr bgcolor="#686868">';
			$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>Meal Name</b></font></td>';
			$results_display .= '<td width="20" align="center" style="padding-right: 2px;" ><font color="white"><b>Sides &amp; Sweets Items Sold</b></font></td>';
			$results_display .= '</tr>' . "\n";

			foreach($arraypre as $element)
			{
				$results_display .= '<tr id="'. ($element[1] == "0" ? "hz_" : "nhz_") . (++$rowCounter) . '" style="display:' . ($element[1] == "0" ? "$initialZeroItemDisplay" : "$displayProp"). '">';
				$results_display .= '<td class="subheaders" width="200" style="padding-left: 2px;" align="left">' . htmlspecialchars($element[0]) . '</td>';
				$results_display .= '<td class="subheaders" width="20" align="center" >' . $element[1] . '</td>';
				$results_display .= '</tr>' . "\n";
			}

			$results_display .= '<tr bgcolor="#989898">';
			$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>Sides &amp; Sweets Totals:</b></font></td>';
			$results_display .= '<td width="20" align="center"><font color="white"><b>' . $totalcount . '</b></font></td>';
			$results_display .= '</tr>' . "\n";
			$results_display .= '<tr><td>&nbsp;</td></tr>';
			$results_display .= '</table>' . "\n";
			$results_display .= '</td>';
			$results_display .= '</tr>' . "\n";

			echo $results_display;
		}
	}
}
?>

<?php

// This is for Menu Addons category

if (isset($this->run_report) && $this->run_report== true && isset($this->report_tab_data))
{
	if (isset ($this->menu_array) || count($this->menu_array) > 0)
	{
		$totalcount = 0;
		$arraypre = array();
		foreach ($this->menu_array as $entity)
		{
			if (!empty($entity['categories_type']) && $entity['categories_type'] == 'Add-on Items')
			{
				$arraypre[$entity['id']] = array($entity['menu_name'], $entity['count']);
				$totalcount += $entity['count'];
			}
		}

		if (count($arraypre) > 0)
		{
			$results_display = "";
			$results_display .= '<tr><td>&nbsp;</td></tr>';
			$results_display .= '<tr><td><font size="3" color="#480000"><b>Add-on Items</b></font></td></tr>';
			$results_display .= '<tr>';
			$results_display .= '<td style="padding-left: 0px;" colspan="0">';
			$results_display .= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
			$results_display .= '<tr>';
			$results_display .= '</tr>';
			$results_display .= '<tr bgcolor="#686868">';
			$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>Meal Name</b></font></td>';
			$results_display .= '<td width="20" align="center" style="padding-right: 2px;" ><font color="white"><b>Add-on Items Sold</b></font></td>';
			$results_display .= '</tr>' . "\n";

			foreach($arraypre as $element)
			{
				$results_display .= '<tr id="'. ($element[1] == "0" ? "hz_" : "nhz_") . (++$rowCounter) . '" style="display:' . ($element[1] == "0" ? "$initialZeroItemDisplay" : "$displayProp"). '">';
				$results_display .= '<td class="subheaders" width="200" style="padding-left: 2px;" align="left">' . $element[0] . '</td>';
				$results_display .= '<td class="subheaders" width="20" align="center" >' . $element[1] . '</td>';
				$results_display .= '</tr>' . "\n";
			}

			$results_display .= '<tr bgcolor="#989898">';
			$results_display .= '<td width="200" style="padding-left: 2px;" align="left"><font color="white"><b>Add-on Items Totals:</b></font></td>';
			$results_display .= '<td width="20" align="center"><font color="white"><b>' . $totalcount . '</b></font></td>';
			$results_display .= '</tr>';
			$results_display .= '<tr><td>&nbsp;</td></tr>';
			$results_display .= '</table>';
			$results_display .= '</tr>' . "\n";

			echo $results_display;
		}
	}
}
?>

<?php
//**************************************************************************************************************************************
// CUSTOMERS ATTENDING
//**************************************************************************************************************************************
// customers For Daily Summary.. do not include ?? maybe ?? could tally all customers schedule for the day? hum?

/*
 *  Remove from this section, requested by Jeb Aug 27, 2014
 *  --Ryan Snook
 */

if (false && isset($this->run_report) && isset($this->report_type) && $this->run_report== true && isset($this->report_tab_data))
{
	if ($this->session_type_to_run == 2 || $this->report_type > 1)
	{
		if (count($this->customer_array) > 0)
		{

			$results_display = '<tr>';
			$results_display .= '<td><font size="3" color="#480000"><b>Customers Attending</b></font></td></tr>' . "\n";

			foreach ($this->customer_array as $entity)
			{
				$vardeleted = $entity['acount_deleted'];
				$varFirstName = $entity['firstname'];
				$varLastName = $entity['lastname'];
				$customers_id = $entity['customer_id'];
				$order_id = $entity['order_id'];




				$results_display .= '<tr>';
				$results_display .= '<td colspan="0">';
				$results_display .= '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
				$results_display .= '<tr>';
				$additional_info = '';

				if (isset($this->history_list[$customers_id]))
				{
					$additional_info .= '<br /><span style="font-style:italic;white-space:nowrap;">Last visit: ' . CSessionReports::newDayFormat ($this->history_list[$customers_id]['last_session_attended']) . '<br />Previous Bookings: ' . $this->history_list[$customers_id]['bookings_made'] . '</span>';
				}

				$additional_info .= '<br /><span style="font-style:italic;white-space:nowrap;">Dream Rewards: ' . (($entity['dream_reward_status'] == 1 || $entity['dream_reward_status'] == 3) ? 'YES' : 'NO') . '</span>';

				if (!empty($entity['coupon_code']) && !is_null($entity['coupon_code']))
				{
					$additional_info .= '<br /><span style="font-style:italic;font-weight:bold;color:#FF6A6A;white-space:nowrap;">Coupon Code: ' . $entity['coupon_code'] . '</span>';
				}

				if ($entity['is_partial_account'])
				{
					$additional_info .= '<br />Partial Account (<a href="main.php?page=admin_account&amp;upgrade=true&amp;id='.$customers_id.'&amp;back='.urlencode($_SERVER['REQUEST_URI']). '">Upgrade Account</a>)';
				}

				// PRINT OUT USER INFORMATION
				if (true)
				{
					if ($vardeleted == 0)
					{
						if ($this->report_type ==2 && $this->session_type_to_run == 2)
						{
							$results_display .= '<td width="190"><a href="main.php?page=admin_user_details&amp;id=' . $customers_id . '&amp;back='.urlencode($this->form_submit_string). '"><b>' . $varFirstName . ' ' . $varLastName . '</b></a>' .  $additional_info . '</td>';
						}
						else
						{
							$results_display .= '<td width="190"><a href="main.php?page=admin_user_details&amp;id=' . $customers_id . '&amp;back='.urlencode($_SERVER['REQUEST_URI']). '"><b>' . $varFirstName . ' ' . $varLastName . '</b></a>'  . $additional_info . '</td>';
						}

						if ($this->print_view == false)
						{
							$canEditURL = "";
							$viewOrderURL = '<a href="main.php?page=admin_order_details&amp;order=' . $order_id . '&amp;back='.urlencode($this->form_submit_string).'"><font color="#333366">View Order</font></a><br />';
							if (CSessionReports::can_edit_order($this->printdate, $this->store_id))
							{
								if ($entity['is_partial_account'])
								{
									$canEditURL .= '<a href="main.php?page=admin_order_mgr&amp;order=' . $order_id . '&amp;back='.urlencode($this->form_submit_string).'"><font color="#cc0000">Edit Order</font></a><br />';
								}
								else
								{
									$canEditURL .= '<a href="main.php?page=admin_order_mgr&amp;order=' . $order_id . '&amp;back='.urlencode($this->form_submit_string).'"><font color="#cc0000">Edit Order</font></a><br />';
								}
							}

							if ($entity['is_partial_account'])
							{
								$placeOrderULR = '<a href="javascript:warnOfPartial();"><font color="#333366">Place Order</font></a>';
							}
							else
							{
								$placeOrderULR = '<a href="main.php?page=admin_order_mgr&amp;user=' . $customers_id . '&amp;back='.urlencode($this->form_submit_string).'"><font color="#333366">Place Order</font></a>';
							}

							$results_display .= '<td width="100" align="left">';
							$results_display .= "<table><tr><td><strong>$viewOrderURL</strong></td></tr>" . "\n";
							$results_display .= "<tr><td><strong>$canEditURL</strong></td></tr>" . "\n";
						 	$results_display .= "<tr><td><strong>$placeOrderULR</strong></td></tr></table>";
							$results_display .= "</td>";
						}
						$results_display .= '<td align="left">';
						$results_display .= "<table>";

					 	if ($entity['booking_type'] == CMenuItem::INTRO)
					 	{
					 		$results_display .= "<tr><td width='90'><strong>Starter Pack Order:</strong></td><td width='16' style='color: #FF6A6A;font-weight: bold;'>Yes</td></tr>" . "\n";
						}
					 	else
					 	{
							$results_display .= "<tr><td width='90'><strong>Starter Pack Order:</strong></td><td width='16'>No</td></tr>" . "\n";
						}

						$results_display .= '</table>';
						$results_display .= '</td>';
						$results_display .= '<td align="right">';
						$results_display .= '<table width="150px">';
						$results_display .= '<tr><td><span style="font-weight:bold;white-space:nowrap;">No Show:</span></td><td style="text-align:right"><input id="bns_' . $entity['booking_id'] . '" onclick="setNoShowState(' . $entity['booking_id'] . ', this)" type="checkbox" ' . ($entity['booking_no_show'] ? 'checked="checked"' : '') . ' style="vertical-align:middle;" /> <span style="display:none;" id="bnsp_' . $entity['booking_id'] . '"><img src="' . ADMIN_IMAGES_PATH . '/throbber_circle.gif" alt="Processing" style="vertical-align:middle;margin-bottom:.25em;" /></span></td></tr>';

						if (isset($this->payment_failed_balance_due_array[$entity['customer_id']][$entity['order_id']]['balance_due']))
						{
							$viewOrderURL = '<a href="main.php?page=admin_order_details&amp;order=' . $order_id . '&amp;back='.urlencode($this->form_submit_string).'"><b>$' . $this->payment_failed_balance_due_array[$entity['customer_id']][$entity['order_id']]['balance_due'] . '</b></a>';
							$viewOrderURLBalanceDue = '<a href="main.php?page=admin_order_details&amp;order=' . $order_id . '&amp;back='.urlencode($this->form_submit_string).'"><b>Balance Due:</b></a>';

							$results_display .= '<tr><td width="90">' . $viewOrderURLBalanceDue . '</td><td style="color:#FF6A6A;font-weight:bold;text-align:right">' . $viewOrderURL . '</td></tr>';
						}
						else
						{
							$results_display .= '<tr><td width="90"><strong>Balance Due:</strong></td><td style="text-align:right;">$0.00</td></tr>';
						}

						if (isset($this->payment_failed_balance_due_array[$entity['customer_id']][$entity['order_id']]['delayed_payment_failure']))
						{
							$results_display .= '<tr><td width="90"><span style="font-weight:bold;white-space:nowrap;">Delayed Payment:</span></td><td width="30" style="color: #FF6A6A;font-weight: bold;">' . $this->payment_failed_balance_due_array[$entity['customer_id']][$entity['order_id']]['delayed_payment_failure'] . '</td></tr>';
						}
						else
						{
							$results_display .= '<tr><td width="90">&nbsp;</td><td width=30>&nbsp;</td></tr>';
						}

						$results_display .= '</table>';
						$results_display .= '</td>';
					}
					else
					{ // else for deleted user
						$results_display .= '<td class="subheaders" style="padding: 3px;" align="left"><b>'. $varFirstName . ' ' . $varLastName . '</b></a></td>';
						$results_display .= '<td class="subheaders" style="padding: 3px;" align="center">Deactivated Account</td>';
					}

				} // END HOMESITE VIEW (DB1 FORMAT)

				$results_display .= '</tr>';
				if (isset($entity['referring_user_email']) && !empty($entity['referring_user_email']))
				{
					$results_display .= "<tr><td></td><td colspan='3' style='background-color:#CCDDDD'>Referred By: <b><a href='main.php?page=admin_user_details&amp;id=" . $entity['referring_user_id'] . "' >" . $entity['referring_user_name'] . "</a></b></td></tr>";
					$results_display .= "<tr><td></td><td colspan='3' style='background-color:#CCDDDD'>Referral Type: <b>" . CCustomerReferral::$ShortOriginationDescription[$entity['referral_type']] . "</b></td></tr>";
				}
				// This is set for guest carryover notes
				$this->user['id'] = $customers_id;

				$results_display .= '<tr><td colspan="4" style="vertical-align:top;">Carryover Notes:<div style="margin:4px;">' . $this->fetch('admin/guest_carryover_notes.tpl.php') . '</div></td></tr>';

				$results_display .= '</table>';
				$results_display .= '</td></tr>';

				if (count($this->customer_array) > 1)
				{
					$results_display .= '<tr>';
					$results_display .= '<td colspan="5" class="" align="">';
					$results_display .= '<table width="100%" cellpadding="0" cellspacing="0" border="0"><td><hr /></td>';
					$results_display .= '</tr>';
					$results_display .= '</table>';
					$results_display .= '</td></tr>';
				}
			}
		}
		else // NO CUSTOMERS FOR THIS SESSION
		{
			$errorNoCustomers = "Sorry, there are no customers registered for this session.";
			$results_display .= '<tr>';
			$results_display .= '<table class="report" width="100%" cellpadding="0" cellspacing="0" border="0">';
			$results_display .= '<tr>';
			$results_display .= '<td width="30" style="padding: 3px;"></td>';
			$results_display .= '<td class="report" style="padding: 3px;" align="right">'. $errorNoCustomers . '</a></td>';
			$results_display .= '<td width="30" style="padding: 3px;"></td>';
			$results_display .= '</tr>';
			$results_display .= '</table>';
			$results_display .= '</td></tr>';
		}
		echo $results_display;
	}
} // END CUSTOMER PRINT OUT
?>

</table>


<?php if ($this->print_view == true) { ?>
</body>
</html>
<?php
}
else
{
	include $this->loadTemplate('admin/page_footer.tpl.php');
}
?>