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


    if (!empty($this->form_session_list['report_submit_html'])) {
    echo $this->form_session_list['report_submit_html'] . "<br /><br />";
    }

    if (!empty($this->form_session_list['report_export_html'])) {
    echo $this->form_session_list['report_export_html'];
    }

    echo "<hr>";
?>

</div>

<?php echo  $this->form_session_list['hidden_html']; ?>
</form>

<div style="width:50%; float:right;">
<?php
include  $this->loadTemplate('admin/subtemplate/store_tree.tpl.php'); ?>

</div>


<?php

if (isset($this->report_submitted) && $this->report_submitted == TRUE) {
	if ( isset($this->royalty_data) && $this->royalty_data != "" && ($this->royalty_data) > 0) {

		echo '<table class="report" border="0">';
		echo '<tr align="right"><td>&nbsp;</td><td>';
		//$exportAllLink = '?page=admin_reports_site_royalty_national&day=' . $this->report_day . '&month=' . $this->report_month . '&year=' . $this->report_year . '&duration=' . urlencode($this->report_duration) . '&report_type=' . $this->report_type .  '&export=csv';
		//include $this->loadTemplate('admin/export.tpl.php');
		echo "</td></tr>";
		echo '</table>';

	   for ($var = 0; $var < count($this->royalty_data); $var++)
	    {
			$array_entity = $this->royalty_data[$var];
			if ($array_entity == NULL) {
			    continue;
			}
			echo '<table class="report" border="0">';


			echo '<tr>';
			if ($this->report_type_to_run == 4)
				echo '<td width=100 class="headers" ><b>' . 'Yearly Royalty' . '&nbsp;Summary </b></td>';
			else
				echo '<td width=100 class="headers" ><b>' . 'Monthly Royalty' . '&nbsp;Summary </b></td>';

			echo '<td  class="headers" width ="130"><b>Totals</b></td>';
			echo '</tr>';
			echo '</table>';


			echo '<table class="report" border="0">';

			echo '<tr>';
			echo '<td  width ="140"><b>Home Office ID' . '</b></td>';
			echo '<td width="180"><b>#' . $array_entity['home_office_id'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;' . $array_entity['store_name'] . '&nbsp;&nbsp;&#151;&nbsp;&nbsp;'.  $array_entity['city'] . ',&nbsp;' . $array_entity['state_id'] . '&nbsp;)</b></td>';

			echo '</tr>';


			echo '<tr>';
			echo '<td width="140"><b>Grand Opening Date:</b></td>';
			echo '<td width="180">' .  $array_entity['grand_opening_date'] . '</td>';
			echo '</tr>';






			echo '<tr>';
			echo '<td width="140"><b>Gross Income (Less Taxes):</b></td>';
			echo '<td width="180">' .  CSessionReports::formatCurrency($array_entity['grand_total_less_taxes']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td width="140"><b>Discounts:</b></td>';
			echo '<td width="180">('  .  CSessionReports::formatCurrency($array_entity['discounts']) . ')</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td width="140"><b>Adjustments:</b></td>';

			if ($array_entity['adjustments'] < 0)
					echo '<td width="180">('  .  CSessionReports::formatCurrency($array_entity['adjustments']) . ')</td>';
				else
					echo '<td width="180">'  .  CSessionReports::formatCurrency($array_entity['adjustments']) . '</td>';

			echo '</tr>';


			echo '<tr>';
			echo '<td width="140"><b>Fundraising Contribution:</b></td>';
			echo '<td width="180">('  .  CSessionReports::formatCurrency($array_entity['fundraiser_total']) . ')</td>';
		    echo '</tr>';


			if (!empty($array_entity['ltd_menu_item_value']))
			{
			    echo '<tr>';
			    echo '<td width="140"><b>LTD Menu Item Donations:</b></td>';
			    echo '<td width="180">('  .  CSessionReports::formatCurrency($array_entity['ltd_menu_item_value']) . ')</td>';
		    echo '</tr>';

			}

            if (!empty($array_entity['subtotal_delivery_fee']))
            {
                echo '<tr>';
                echo '<td width="140"><b>Total Delivery Fees Owed to Delivery Vendor:</b></td>';
                echo '<td width="180">('  .  CSessionReports::formatCurrency($array_entity['subtotal_delivery_fee']) . ')</td>';
                echo '</tr>';

            }

			if (!empty($array_entity['door_dash_fees']))
			{
				echo '<tr>';
				echo '<td width="140"><b>Door Dash Fees:</b></td>';
				echo '<td width="180">('  .  CSessionReports::formatCurrency($array_entity['door_dash_fees']) . ')</td>';
				echo '</tr>';

			}

            echo '<tr>';
				echo '<td width="140"><b>Total Sales after Discounts/Adjustments:</b></td>';
				echo '<td width="180">'  .  CSessionReports::formatCurrency($array_entity['total_less_discounts']) . '</td>';
				echo '</tr>';


			echo '<tr>';
			if ($this->report_type_to_run == 3 || $this->report_type_to_run == 4)
				echo '<td width="140"><b>National Marketing Fee:</b></td>';
			else
				echo '<td width="140"><b>National Marketing Fee (2% estimate only):</b></td>';
			echo '<td width="180">' .  CSessionReports::formatCurrency($array_entity['marketing_total']) . '</td>';
			echo '</tr>';

			if (!empty($array_entity['salesforce_fee']))
			{
			    echo '<tr>';
			    echo '<td width="140"><b>SalesForce Fee:</b></td>';
			    echo '<td width="180">'  .  CSessionReports::formatCurrency($array_entity['salesforce_fee']) . '</td>';
			    echo '</tr>';
			}


			echo '<tr>';
			echo '<td width="140"><b>Royalites Owed:</b></td>';
			echo '<td width="180">'  .  CSessionReports::formatCurrency($array_entity['royalty_fee']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td width="140"><b>Total Fees Owed:</b></td>';
			echo '<td width="180">'  .  CSessionReports::formatCurrency($array_entity['total_fees']) . '</td>';
			echo '</tr>';


			echo '<tr>';
			echo '<td width="140"><b>LTD Round Up Donations:</b></td>';
			echo '<td width="180">'  .  CSessionReports::formatCurrency($array_entity['ltd_round_up_value']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td width="140"><b>LTD Menu Item Donations:</b></td>';
			echo '<td width="180">'  .  CSessionReports::formatCurrency($array_entity['ltd_menu_item_value']) . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td width="140"><b>LTD Total Donations:</b></td>';
			echo '<td width="180">'  .  CSessionReports::formatCurrency($array_entity['ltd_menu_item_value'] + $array_entity['ltd_round_up_value']) . '</td>';
			echo '</tr>';


            if (!empty($array_entity['subtotal_delivery_fee']))
            {
                echo '<tr>';
                echo '<td width="140"><b>Total Delivery Fees Owed to Delivery Vendor:</b></td>';
                echo '<td width="180">'  .  CSessionReports::formatCurrency($array_entity['subtotal_delivery_fee']) . '</td>';
                echo '</tr>';
            }


            echo '<tr>';
			echo '<td width="140"><b>Performance Standard:</b></td>';


			if ($array_entity['performance_standard'] == 0) {
				echo '<td width="180">Store Open <= 1 year: Adjusted Income $ X royalty fee %</td>';
			} else if ($array_entity['performance_standard'] == 1) {
				echo '<td width="180">Store Open > 1 year: Adjusted Income $ X royalty fee %</td>';
			} else if ($array_entity['performance_standard'] == 2) {
				echo '<td width="180">Store Open > 1 year: Performance $ X royalty fee %</td>';
			} else if ($array_entity['performance_standard'] == 3) {
				echo '<td width="180"><font color=red>Performance Override was issued</font></td>';
			}



			if (isset($array_entity['store_history'])) {
				foreach($array_entity['store_history'] as $element){
					echo '<tr>';
					echo '<td width="140"><b>Store Ownership Details:</b></td>';
					echo '<td width="180">#'  .  $element[0] . "&nbsp;&nbsp;&nbsp;("  .  CSessionReports::newDayFormat($element[1]) . ')</td>';
					echo '</tr>';
				}
			}


			echo '</tr>';


		//echo '</table>';
		}
		echo '</table>';

	}
	else {
			echo '<table class="report" border="0">';
			echo '<tr>';
			echo '<td  class="headers" width="224">' . '<b>Sorry, a royalty report does not exist for this time period.</b>' . '</td>';
			echo '</tr>';
			echo '</table>';
	}
}

?>

<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>