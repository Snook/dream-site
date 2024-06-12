<?php $this->setCSS(CSS_PATH . '/admin/jquery/jsTree/default/style.css'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/vendor/jstree.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/reports_payment.min.js'); ?>
<?php $this->setScript('head', SCRIPT_PATH . '/admin/store_tree.min.js');

$this->setScriptVar('isFranchiseAccess = ' . (CUser::getCurrentUser()->isFranchiseAccess() ? 'true' : 'false') . ';');

$this->setOnload('reports_payment_init();');

// constants for all report pages.
$PAGETITLE = 'Payment Reconciliation Report';
if (!empty($this->store_name))
{
	$PAGETITLE .= '<br /> for ' . $this->store_name;
} ?>


<?php include $this->loadTemplate('admin/page_header_reports.tpl.php');

?>


<form name="frm" action="<?=(isset($FORMACTION) ? $FORMACTION : "")?>" method="post" onSubmit="return _override_check_form(this);" >

	<?php echo  $this->form_session_list['hidden_html'];?>

	<?php if (isset($this->store_data))	 { ?>
	<div style="width:100%; text-align:right"><input class="btn btn-primary btn-sm" type="submit" name="submit_report" value="Run Report" /> </div>
	<?php  }  ?>




	<div style="max-width:350px; border:thin solid black; margin-left:25px; margin-top:10px; padding:25px; padding-top:10px; float:left; background-color:#BEB7AE;">

		<h2>Select Payments using</h2>
		<input type="radio" name="select_key" id="select_key_payment_date" value="payment_date" checked="checked" /><label for="select_key_payment_date">Payment Date</label> <br />
		<input type="radio" name="select_key" id="select_key_session_date" value="session_date" /><label for="select_key_session_date">Session Date</label> <br />


	 	<h2 style="padding-top:10px;">Select Date Range</h2>

		<?php
		$varChecked = "";
		if (!isset($this->report_type_to_run) || $this->report_type_to_run == "1") $varChecked = "CHECKED";
		echo '<Input Type="Radio" onClick="hidefields(1)" Name="pickDate" Value="1"' . $varChecked . '>';
		$varChecked = "";
		?>
		Select a single date:
		<?php

		if (isset($this->day_start_set)) echo "<script>DateInput('single_date', false, 'YYYY-MM-DD','" . $this->day_start_set .  "')</script>";
		else echo "<script>DateInput('single_date', true, 'YYYY-MM-DD')</script>";
		?>
		<hr>

		<?php
		if (isset($this->report_type_to_run) && $this->report_type_to_run == "2") $varChecked = "CHECKED";
		echo '<Input Type="Radio" onClick="hidefields(2)" Name="pickDate" Value="2"' . $varChecked . '>';
		$varChecked = "";
		?>
		Select a range of dates:
		<?php
		$rangestart = NULL;
		$rangeend = NULL;
		if (isset($this->range_day_start_set) && isset($this->range_day_end_set)) {
		    echo "<script>DateInput('range_day_start', false, 'YYYY-MM-DD','" . $this->range_day_start_set .  "')</script>";
			echo "<script>DateInput('range_day_end', false, 'YYYY-MM-DD','" . $this->range_day_end_set .  "')</script>";
		}
		else {
			echo "<script>DateInput('range_day_start', true, 'YYYY-MM-DD')</script>";
			echo "<script>DateInput('range_day_end', true, 'YYYY-MM-DD')</script>";
		}
		?>
		<hr>

		<?php
		$varChecked = "";
		if (isset($this->report_type_to_run) && $this->report_type_to_run == "3") $varChecked = "CHECKED";
		echo '<Input Type="Radio" onClick="hidefields(3)" Name="pickDate" Value="3"' . $varChecked . '>';
		$varChecked = "";
		?>
		Select a month and a year
		<br />
		<?php echo $this->form_session_list['month_popup_html']; ?>
		<?php echo $this->form_session_list['year_field_001_html']; ?>
		<?php if (isset($this->form_session_list['menu_or_calendar_html']['menu'])) {
				echo "<div style='margin-left:10px;'>" . $this->form_session_list['menu_or_calendar_html']['menu'] . "&nbsp; Menu Month <br />";
        		echo $this->form_session_list['menu_or_calendar_html']['cal'] . "&nbsp; Calendar Month </div>";
		  } ?>

	<hr>


		<?php
		$varChecked = "";
		if (isset($this->report_type_to_run) && $this->report_type_to_run == "4") $varChecked = "CHECKED";
		echo '<Input Type="Radio" onClick="hidefields(4)" Name="pickDate" Value="4"' . $varChecked . '>';
		$varChecked = "";
		?>

		Enter a year
		<br />
		<?php echo $this->form_session_list['year_field_002_html']; ?>

		<hr>

		</div>


		<?php if (isset($this->store_data))	 { ?>

	<div style="max-width:200px; border:thin solid black; margin-left:10px; margin-top:10px; padding:10px; float:left; background-color:#BEB7AE;"><h2 style="margin-left:10px;">Filters</h2>

	<?php } else { ?>

	<div style="max-width:400px; border:thin solid black; margin-left:10px; margin-top:10px; padding:10px; float:left; background-color:#BEB7AE;"><h2 style="margin-left:10px;">Filters</h2>

	<?php } ?>

	<input type="checkbox" name="only_show_orders_with_balance_due" id="show_if_balance_due"><label for="show_if_balance_due">Only show orders with a balance due.</label><br />
	<input type="checkbox" name="also_show_cancelled_orders" id="also_show_cancelled_orders"><label for="also_show_cancelled_orders">Also show canceled orders.</label><br /><br />

	<h2>Include Payment type:</h2>
	<div><input type="checkbox" name="pfa_All" id="pfa_All"><label for="pfa_All">All</label></div>

	<div style="margin-left:10px;"><input type="checkbox" name="pf_CASH" id="pf_CASH" /><label for="pf_CASH">Cash</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_CHECK" id="pf_CHECK" /><label for="pf_CHECK">Check</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_CC" id="pf_CC" /><label for="pf_CC">Credit Card</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_GIFT_CARD" id="pf_GIFT_CARD" /><label for="pf_GIFT_CARD">Gift Card</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_GIFT_CERT_DONATED" id="pf_GIFT_CERT_DONATED" /><label for="pf_GIFT_CERT_DONATED">Gift Certificate - Donated</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_GIFT_CERT_SCRIP" id="pf_GIFT_CERT_SCRIP" /><label for="pf_GIFT_CERT_SCRIP">Gift Certificate - Scrip</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_GIFT_CERT_STANDARD" id="pf_GIFT_CERT_STANDARD" /><label for="pf_GIFT_CERT_STANDARD">Gift Certificate - Standard</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_GIFT_CERT_VOUCHER" id="pf_GIFT_CERT_VOUCHER" /><label for="pf_GIFT_CERT_VOUCHER">Voucher</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_CREDIT" id="pf_CREDIT" /><label for="pf_CREDIT">No Charge</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_REFUND" id="pf_REFUND" /><label for="pf_REFUND">CC Refund</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_REFUND_CASH" id="pf_REFUND_CASH" /><label for="pf_REFUND_CASH">Cash Refund</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_REFUND_STORE_CREDIT" id="pf_REFUND_STORE_CREDIT" /><label for="pf_REFUND_STORE_CREDIT">Store Credit Refund</label></div>
    <div style="margin-left:10px;"><input type="checkbox" name="pf_REFUND_GIFT_CARD" id="pf_REFUND_GIFT_CARD" /><label for="pf_REFUND_GIFT_CARD">Gift Card Refund</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_STORE_CREDIT" id="pf_STORE_CREDIT" /><label for="pf_STORE_CREDIT">Store Credit</label></div>
	<div style="margin-left:10px;"><input type="checkbox" name="pf_PAY_AT_SESSION" id="pf_PAY_AT_SESSION" /><label for="pf_PAY_AT_SESSION">Pay at Session</label></div>
	</div>



<?php if (isset($this->store_data))	 {
include $this->loadTemplate('admin/subtemplate/store_tree.tpl.php');
 } else { ?>
		<div style="width:150px; padding-top:15px; text-align:right; float:left;"><input class="btn btn-primary btn-sm" type="submit" name="submit_report" value="Run Report" /> </div>
<?php } ?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>