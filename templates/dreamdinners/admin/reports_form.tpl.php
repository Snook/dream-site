<form name="frm" action="<?php echo (isset($FORMACTION) ? $FORMACTION : "")?>" method="post" onSubmit="<?php echo (isset($ON_SUBMIT) ? $ON_SUBMIT : "return _check_form(this);")?>" id="<?php echo (isset($FORM_ID) ? $FORM_ID : "frm")?>" >

	<?php if (isset($this->form_session_list['store_html']) && !defined('SUPPRESS_STORE_SELECTOR') ) { ?>
		<div class="row mb-3">
			<div class="col">
				<div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">Store</span>
					</div>
					<?php echo $this->form_session_list['store_html']; ?>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if (isset($this->form_session_list['store_type_html']) && !defined('SUPPRESS_STORE_SELECTOR') ) { ?>
		<table>
			<tr>
				<td class="font-weight-bold">Store</td>
				<td>
					<?php if($this->form_session_list['store_type_html']['single_store']) { ?>
						<?php echo $this->form_session_list['store_type_html']['single_store'] . ' ' . $this->form_session_list['single_store_select_html']; ?>
					<?php } ?>
				</td>
			</tr>
			<?php if($this->form_session_list['store_type_html']['all_stores']){ ?>
				<tr>
					<td style="text-align:right; vertical-align:top;"></td>
					<td style="vertical-align:top; "><?php echo $this->form_session_list['store_type_html']['all_stores']; ?><label for="report_typedt_corp_stores">All Stores</label></td>
				</tr>
			<?php } ?>
			<?php if($this->form_session_list['store_type_html']['soft_launch']){ ?>
				<tr>
					<td style="text-align:right; vertical-align:top;"></td>
					<td style="vertical-align:top; "><?php echo $this->form_session_list['store_type_html']['soft_launch']; ?><label for="report_typedt_corp_stores">Soft Launch Stores<</label></td>
				</tr>
			<?php } ?>
			<?php if($this->form_session_list['store_type_html']['custom']){ ?>
				<tr>
					<td style="text-align:right; vertical-align:top;"></td>
					<td style="vertical-align:top; "><?php echo $this->form_session_list['store_type_html']['custom']; ?><label for="report_typedt_corp_stores">Select Multiple Stores</label></td>
				</tr>
			<?php } ?>
		</table>
	<?php } ?>

	<?php /** this is for the Orders Placed By Customers Report */
	if (isset($this->form_session_list['show_all_orders_html'])) { ?>
		<?php echo $this->form_session_list['show_all_orders_html']; ?>
		Show Orders Placed During Time Span By New Customers Only<br /><br />
	<?php } ?>

	<?php /** this is for the Entree Report for Home site */
	if (isset($this->form_session_list['show_by_store_html'])) { ?>
		<?php echo $this->form_session_list['show_by_store_html']; ?>
		Show Entree Counts Broken down By Store<br /><br />
	<?php } ?>

	<?php /** this is for the Entree Report for Home site */
	if (isset($this->sessionTypes)) { ?>
		<hr><strong>Session Types</strong><br>
		<table><tr>
				<?php foreach ($this->sessionTypes as $type => $label) { ?>
					<td><?php echo $this->form_session_list['session_type_' . strtolower($type) . '_html']; ?></td>
					<td></td>
				<?php } ?>
			</tr>
		</table>
		<hr>
	<?php } ?>

	<?php if (!empty($SHOWDATEHEADER) && $SHOWDATEHEADER == TRUE) { ?>
		<br />
		<h2>Select Date Range</h2>
	<?php } ?>

	<?php if (isset($SHOWSINGLEDATE) && $SHOWSINGLEDATE == TRUE) { ?>
		<input type="radio" onclick="hidefields(1)" name="pickDate" value="1" <?php echo (!isset($this->report_type_to_run) || $this->report_type_to_run == "1") ? 'checked' : ''; ?>>
		Select a single date:
	<?php if (isset($this->day_start_set)) { ?>
		<script>DateInput('single_date', false, 'YYYY-MM-DD','<?php echo $this->day_start_set; ?>')</script>
	<?php } else { ?>
		<script>DateInput('single_date', true, 'YYYY-MM-DD')</script>
	<?php } ?>
		<hr>
	<?php } ?>

	<?php if (isset($SHOWRANGEDATE) && $SHOWRANGEDATE == TRUE) { ?>
		<input Type="radio" onclick="hidefields(2)" name="pickDate" value="2" <?php echo (isset($this->report_type_to_run) && $this->report_type_to_run == "2") ? 'checked' : ''; ?>>
		Select a range of dates:
	<?php if (isset($this->range_day_start_set) && isset($this->range_day_end_set)) { ?>
		<script>DateInput('range_day_start', false, 'YYYY-MM-DD','<?php echo $this->range_day_start_set; ?>')</script>
		<script>DateInput('range_day_end', false, 'YYYY-MM-DD','<?php echo $this->range_day_end_set; ?>')</script>
	<?php } else { ?>
		<script>DateInput('range_day_start', true, 'YYYY-MM-DD')</script>
		<script>DateInput('range_day_end', true, 'YYYY-MM-DD')</script>
	<?php } ?>
		<hr>
	<?php } ?>

	<?php if (isset($SHOWMONTH) && $SHOWMONTH == TRUE) { ?>
		<input type="radio" onclick="hidefields(3)" name="pickDate" value="3" <?php echo (isset($this->report_type_to_run) && $this->report_type_to_run == "3") ? 'checked' : ''; ?>>
		Select a month and enter a year
		<br />
		<?php echo $this->form_session_list['month_popup_html']; ?>
		<?php echo $this->form_session_list['year_field_001_html']; ?>

		<?php if (isset($this->form_session_list['menu_or_calendar_html']['menu'])) { ?>
			<div>
				<?php echo $this->form_session_list['menu_or_calendar_html']['menu']; ?> Menu Month
				<?php echo $this->form_session_list['menu_or_calendar_html']['cal']; ?> Calendar Month
			</div>
		<?php } ?>
		<hr>
	<?php } ?>

	<?php if (isset($SHOW_WEEK) && $SHOW_WEEK == TRUE) { ?>
		<input type="radio" onclick="hidefields(3)" name="pickDate" value="5" <?php echo (isset($this->report_type_to_run) && $this->report_type_to_run == "5") ? 'checked' : ''; ?>>
		Select a year then select a week
		<br />
		<?php echo $this->form_session_list['year_week_html']; ?>
		<?php echo $this->form_session_list['week_html']; ?>
		<hr>
	<?php } ?>

	<?php if ($SHOWYEAR == TRUE) { ?>
		<input type="radio" onclick="hidefields(4)" name="pickDate" value="4" <?php echo (isset($this->report_type_to_run) && $this->report_type_to_run == "4") ? 'checked' : ''; ?>>
		Enter a year
		<br />
		<?php echo $this->form_session_list['year_field_002_html']; ?>
		<hr>
	<?php } ?>

	<script>
		var color = '#f8f6f5';
		if (document.getElementById('range_day_start_Year_ID'))
			document.getElementById('range_day_start_Year_ID').style.background = color;

		if (document.getElementById('range_day_start_Month_ID'))
			document.getElementById('range_day_start_Month_ID').style.background = color;

		if (document.getElementById('range_day_start_Year_ID'))
			document.getElementById('range_day_start_Day_ID').style.background = color;

		if (document.getElementById('range_day_end_Year_ID'))
			document.getElementById('range_day_end_Year_ID').style.background = color;

		if (document.getElementById('range_day_end_Month_ID'))
			document.getElementById('range_day_end_Month_ID').style.background = color;

		if (document.getElementById('range_day_end_Day_ID'))
			document.getElementById('range_day_end_Day_ID').style.background = color;

		if (document.getElementById('single_date_Year_ID'))
			document.getElementById('single_date_Year_ID').style.background = color;

		if (document.getElementById('single_date_Month_ID'))
			document.getElementById('single_date_Month_ID').style.background = color;

		if (document.getElementById('single_date_Day_ID'))
			document.getElementById('single_date_Day_ID').style.background = color;

		if (document.frm.month_popup)
			document.frm.month_popup.style.background = color;
	</script>

	<?php if (isset($this->form_session_list['filterToDFLDiabeticOrders_html'])) { ?>
		<table>
			<tr>
				<td>
					<?php echo $this->form_session_list['filterToDFLDiabeticOrders_html'] ?> Show Dinners for Life orders only for the selected time period.
				</td>
			</tr>
		</table>
		<hr>
	<?php } ?>

	<?php if (isset($this->form_session_list['discounts_filter_html'])) { ?>
		<table>
			<tr>
				<td>Filter Discounts: <br /><?php echo $this->form_session_list['discounts_filter_html'] ?></td>
			</tr>
		</table>
		<hr>
	<?php } ?>

	<?php if (isset($this->form_session_list['filter_html'])) { ?>
		<table>
			<tr>
				<td>Filter: <br /><?php echo $this->form_session_list['filter_html'] ?></td>
			</tr>
		</table>
	<?php } ?>

	<?php if (isset($this->form_session_list['session_type_filter_html'])) { ?>
		<table>
			<tr>
				<td>Session Type: <br /><?php echo $this->form_session_list['session_type_filter_html'] ?></td>
			</tr>
		</table>
		<br />
	<?php } ?>

	<?php if (!isset($OVERRIDESUBMITBUTTON)) { ?>
		<?php if (!empty($this->form_session_list['report_submit_html'])) { ?>
			<?php echo $this->form_session_list['report_submit_html']; ?>
			<hr>
		<?php } ?>

	<?php } ?>

	<?php if (!isset($this->report_type_to_run)) { ?>
		<script type="text/javascript">hidefields(1);</script>
	<?php } else { ?>
		<script type="text/javascript">hidefields("<?php echo $this->report_type_to_run; ?>");</script>
	<?php } ?>

	<?php if (!isset($ADDFORMTOPAGE)) { ?>
</form>
<?php } ?>