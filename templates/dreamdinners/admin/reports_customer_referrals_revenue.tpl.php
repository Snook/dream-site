<?php
// constants for all report pages.
$REPORTGIF = "";
$PAGETITLE = "Customer Referral Revenue Reporting";
$HIDDENPAGENAME = "admin_reports_customer_referrals_revenue";
$SHOWSINGLEDATE=TRUE;
$SHOWRANGEDATE=TRUE;
$SHOWMONTH=TRUE;
$SHOWYEAR=TRUE;
include $this->loadTemplate('admin/page_header_reports.tpl.php');
include $this->loadTemplate('admin/reports_form.tpl.php');
?>



<?php
$varcount = 0;
if ($this->report_submitted == TRUE) {

	if (isset($this->report_data) && count($this->report_data) > 0) {


		echo '<table width="1024px">';

		echo "<tr align='right' >";
		echo "<td>&nbsp;</td>";
		echo "<td>";

		if (!empty($this->form_session_list['store']))
			$exportAllLink = '/backoffice/reports-customer-referrals-revenue?store=' . $this->form_session_list['store'] . '&day=' . $this->report_day .
					'&month=' . $this->report_month . '&year=' . $this->report_year . '&duration=' . urlencode($this->report_duration) . '&export=xlsx&referraltypefilter=' . $this->referraltypefilter;
		else
			$exportAllLink = '/backoffice/reports-customer-referrals-revenue?day=' . $this->report_day . '&month=' . $this->report_month .
				 '&year=' . $this->report_year . '&duration=' . urlencode($this->report_duration) . '&export=xlsx&referraltypefilter=' . $this->referraltypefilter;

		if (!empty($this->form_session_list['groupbyfilter']))
			$exportAllLink ='&referraltypefilter=' . $this->groupbyfilter;


		include $this->loadTemplate('admin/export.tpl.php');


		echo "</td></tr>";

		echo '</table>';





		echo '<table class="table table-striped table-bordered table-hover ddtemp-table-border-collapse" >';
		echo '<thead class="text-center bgcolor_gray sticky-top"><tr>';

        foreach( $this->labels as $info ){


			echo '<th class="align-middle">';
			echo $info;
			echo '</th>';
        }
		echo '</tr>
								</thead>';
		$count = 0;
		foreach ($this->report_data as $array_entity) {
			echo '<tr style="">';

			foreach( $this->labels as $info ){
			if ($info == "User ID") {
				$url = '<a target="_blank" href="/backoffice/user-details?id=' . $array_entity[$info]  .   '">' . $array_entity[$info] . '</a>';


			 	echo '<td>' . $url . '</td>';
			}
			else
				 echo '<td>' . $array_entity[$info] . '</td>';
			}

			echo '</tr>';
			$varcount++;
		}
		echo '</table>';



	}
	else {
		$r_display = '<table><tr><td width="610" class="headers" style="padding-left: 5px;" colspan="5"><b>Sorry, could not generate a report for this date.</b></td></tr></table>';
		echo $r_display;
	}
}
?>


<?php include $this->loadTemplate('admin/page_footer.tpl.php'); ?>