<?php
$this->setCSS(CSS_PATH . '/admin/jquery/jsTree/default/style.css');
$this->setScript('head', SCRIPT_PATH . '/admin/vendor/jstree.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/reports_nat_royalty.min.js');
$this->setScript('head', SCRIPT_PATH . '/admin/store_tree.min.js');

$REPORTGIF = NULL;
$PAGETITLE = "National Royalty Report By Store";
$HIDDENPAGENAME = "admin_reports_site_royalty_national";
$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
$ADDFORMTOPAGE=TRUE;
$FORM_ID = "nat_royal_form";
$ON_SUBMIT = "return _override_check_form(this);";
$OVERRIDESUBMITBUTTON = TRUE;


include $this->loadTemplate('admin/page_header_reports.tpl.php');
?>

	<div style="width:50%; float:left;">
		<?php
		include $this->loadTemplate('admin/reports_form.tpl.php');

		if (!empty($this->form_session_list['report_submit_html']))
		{
			echo $this->form_session_list['report_submit_html'] . "<br /><br />";
		}

		if (!empty($this->form_session_list['report_export_html']))
		{
			echo $this->form_session_list['report_export_html'];
		}

		echo "<hr>";
		?>

	</div>

<?php echo $this->form_session_list['hidden_html']; ?>
	</form>

	<div style="width:50%; float:right;">
		<?php
		include $this->loadTemplate('admin/subtemplate/store_tree.tpl.php'); ?>

	</div>

<?php

if (isset($this->report_submitted) && $this->report_submitted == TRUE)
{
	if ( isset($this->royalty_data) && $this->royalty_data != "" && ($this->royalty_data) > 0)
	{
		for ($var = 0; $var < count($this->royalty_data); $var++)
		{
			$array_entity = $this->royalty_data[$var];

			if ($array_entity == NULL) {
				continue;
			}

			echo '<table class="table table-sm table-striped ddtemp-table-border-collapse bg-white" border="0">';
			echo '<tr>';
			if ($this->report_type_to_run == 4)
			{
				echo '<th width=100 class="headers" ><b>Yearly Royalty Summary </b></th>';
			}
			else
			{
				echo '<th width=100 class="headers" ><b>Monthly Royalty Summary </b></th>';
			}

			echo '<td class="headers" width ="130"><b>Totals</b></td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td width ="140"><b>Home Office ID' . '</b></td>';
			echo '<td><b>#' . $array_entity['home_office_id'] . ' ( ' . $array_entity['store_name'] . ' &#151; '. $array_entity['city'] . ', ' . $array_entity['state_id'] . ' )</b></td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td><b>Grand Opening Date:</b></td>';
			echo '<td>' . $array_entity['grand_opening_date'] . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td><b>Gross Income (Less Taxes):</b></td>';
			echo '<td>' . CSessionReports::formatCurrency($array_entity['grand_total_less_taxes']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td><b>Discounts:</b></td>';
			echo '<td>(' . CSessionReports::formatCurrency($array_entity['discounts']) . ')</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td><b>Adjustments:</b></td>';

			if ($array_entity['adjustments'] < 0)
			{
				echo '<td>(' . CSessionReports::formatCurrency($array_entity['adjustments']) . ')</td>';
			}
			else
			{
				echo '<td>' . CSessionReports::formatCurrency($array_entity['adjustments']) . '</td>';
			}
			echo '</tr>';

			echo '<tr>';
			echo '<td><b>Fundraising Contribution:</b></td>';
			echo '<td>(' . CSessionReports::formatCurrency($array_entity['fundraiser_total']) . ')</td>';
			echo '</tr>';

			if (!empty($array_entity['ltd_menu_item_value']))
			{
				echo '<tr>';
				echo '<td><b>LTD Menu Item Donations:</b></td>';
				echo '<td>(' . CSessionReports::formatCurrency($array_entity['ltd_menu_item_value']) . ')</td>';
				echo '</tr>';
			}

			if (!empty($array_entity['subtotal_delivery_fee']))
			{
				echo '<tr>';
				echo '<td><b>Total Delivery Fees Owed to Delivery Vendor:</b></td>';
				echo '<td>(' . CSessionReports::formatCurrency($array_entity['subtotal_delivery_fee']) . ')</td>';
				echo '</tr>';
			}

			if (!empty($array_entity['door_dash_fees']))
			{
				echo '<tr>';
				echo '<td><b>Door Dash Fees:</b></td>';
				echo '<td>(' . CSessionReports::formatCurrency($array_entity['door_dash_fees']) . ')</td>';
				echo '</tr>';
			}

			echo '<tr>';
			echo '<td><b>Total Sales after Discounts/Adjustments:</b></td>';
			echo '<td>' . CSessionReports::formatCurrency($array_entity['total_less_discounts']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			if ($this->report_type_to_run == 3 || $this->report_type_to_run == 4)
			{
				echo '<td><b>National Marketing Fee:</b></td>';
			}
			else
			{
				echo '<td><b>National Marketing Fee (2% estimate only):</b></td>';
			}
			echo '<td>' . CSessionReports::formatCurrency($array_entity['marketing_total']) . '</td>';
			echo '</tr>';

			if (!empty($array_entity['salesforce_fee']))
			{
				echo '<tr>';
				echo '<td><b>Technology Fee:</b></td>';
				echo '<td>' . CSessionReports::formatCurrency($array_entity['salesforce_fee']) . '</td>';
				echo '</tr>';
			}

			echo '<tr>';
			echo '<td><b>Royalites Owed:</b></td>';
			echo '<td>' . CSessionReports::formatCurrency($array_entity['royalty_fee']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td><b>Total Fees Owed:</b></td>';
			echo '<td>' . CSessionReports::formatCurrency($array_entity['total_fees']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td><b>Driver Tips:</b></td>';
			echo '<td>' . CSessionReports::formatCurrency($array_entity['delivery_tip']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td><b>LTD Round Up Donations:</b></td>';
			echo '<td>' . CSessionReports::formatCurrency($array_entity['ltd_round_up_value']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td><b>LTD Menu Item Donations:</b></td>';
			echo '<td>' . CSessionReports::formatCurrency($array_entity['ltd_menu_item_value']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td><b>LTD Total Donations:</b></td>';
			echo '<td>' . CSessionReports::formatCurrency($array_entity['ltd_menu_item_value'] + $array_entity['ltd_round_up_value']) . '</td>';
			echo '</tr>';

			if (!empty($array_entity['subtotal_delivery_fee']))
			{
				echo '<tr>';
				echo '<td><b>Total Delivery Fees Owed to Delivery Vendor:</b></td>';
				echo '<td>' . CSessionReports::formatCurrency($array_entity['subtotal_delivery_fee']) . '</td>';
				echo '</tr>';
			}

			echo '<tr>';
			echo '<td><b>Performance Standard:</b></td>';

			if ($array_entity['performance_standard'] == 0)
			{
				echo '<td>Store Open <= 1 year: Adjusted Income $ X royalty fee %</td>';
			}
			else if ($array_entity['performance_standard'] == 1)
			{
				echo '<td>Store Open > 1 year: Adjusted Income $ X royalty fee %</td>';
			}
			else if ($array_entity['performance_standard'] == 2)
			{
				echo '<td>Store Open > 1 year: Performance $ X royalty fee %</td>';
			}
			else if ($array_entity['performance_standard'] == 3)
			{
				echo '<td><font color=red>Performance Override was issued</font></td>';
			}

			if (isset($array_entity['store_history']))
			{
				foreach($array_entity['store_history'] as $element)
				{
					echo '<tr>';
					echo '<td><b>Store Ownership Details:</b></td>';
					echo '<td>#' . $element[0] . " (" . CSessionReports::newDayFormat($element[1]) . ')</td>';
					echo '</tr>';
				}
			}

			echo '</tr>';
		}
		echo '</table>';
	}
	else
	{
		echo '<table class="report" border="0">';
		echo '<tr>';
		echo '<td class="headers" width="224">' . '<b>Sorry, a royalty report does not exist for this time period.</b>' . '</td>';
		echo '</tr>';
		echo '</table>';
	}
}
?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>