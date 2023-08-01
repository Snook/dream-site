<?php


// constants for all report pages.
$PAGETITLE = 'Cash Flow Report';
if (!empty($this->store_name))
{
	$PAGETITLE .= '<br /> for ' . $this->store_name;
}
include $this->loadTemplate('admin/page_header_reports.tpl.php');

?>


<form name="frm" action="<?=(isset($FORMACTION) ? $FORMACTION : "")?>" method="post" onSubmit="return _override_check_form(this);" >

<?php if (isset($this->form['store_html']))	 { ?>
		<div style="margin-left:25px; margin-top:15px;">
		<?php  echo  $this->form['store_html']; ?>
		</div>
<?php } ?>


<table style="width:100%">
<tr>
<td  style="width:40%">

	<div style="max-width:400px; border:thin solid black; margin-left:25px; margin-top:10px; padding:25px; padding-top:10px;background-color:#BEB7AE;">

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
		Select a month and enter a year
		<br />
		<?php echo $this->form['month_popup_html']; ?>
		<?php echo $this->form['year_field_001_html']; ?>
		<?php if (isset($this->form['menu_or_calendar_html']['menu'])) {
				echo "<div style='margin-left:10px;'>" . $this->form['menu_or_calendar_html']['menu'] . "&nbsp; Menu Month <br />";
        		echo $this->form['menu_or_calendar_html']['cal'] . "&nbsp; Calendar Month </div>";
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
		<?php echo $this->form['year_field_002_html']; ?>

		<hr>

		</div>



		</td>
		<td>
		<div style=""><input class="button" type="submit" name="submit_report" value="Run Report" style="height:70px;" /> </div>

		</td>
		</tr>
		</table>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>