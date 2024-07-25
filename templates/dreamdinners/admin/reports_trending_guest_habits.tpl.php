<h4>Guest Habits</h4>
<table style="width:100%; collapse; padding:0; border:1px solid black; margin-bottom:12px;">
	<tr>
		<td class="space_right_delimited" style="width:10%;"></td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" colspan="2" ><span data-help="dashboard-guests-existing_guest">Existing Guests</span></td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" colspan="2" ><span data-help="dashboard-guests-new_guest">New Guests</span></td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" colspan="2" ><span data-help="dashboard-guests-reacquired_guest">Reacquired Guests</span></td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" colspan="4"></td>
	</tr>
	<tr>
		<td class="space_right_delimited" style="width:16%;">Month</td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-guest_habits-existing_count">Count</span></td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-guest_habits-existing_signup">Sign-Up %</span></td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-guest_habits-new_count">Count</span></td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-guest_habits-new_signup">Sign-Up %</span></td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-guest_habits-reac_count">Count</span></td>
		<td class="space_right_delimited" style="width:8%; text-align:center;" ><span data-help="trending-guest_habits-reac_signup">Sign-Up %</span></td>
		<td class="space_right_delimited" style="width:8%; text-align:center;" ><span data-help="trending-guest_habits-45_day_lost_guests">45 Day Lost Guest</span></td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-guest_habits-cancelled_orders">Total Canceled Orders</span></td>
		<td class="space_right_delimited" style="width:8%; text-align:center;" ><span data-help="trending-guest_habits-servings_per_guest">Servings per Guest</span></td>
		<td class="space_right_delimited" style="width:10%; text-align:center;" ><span data-help="trending-guest_habits-average_annual_visits">Average Annual Visits</span></td>
	</tr>
	<?php foreach ($this->store_performance_data as $thisRow) { ?>
		<tr>
			<td class="label_delimited" style="text-align:right;"><?php echo CTemplate::dateTimeFormat($thisRow['date'], VERBOSE_MONTH_YEAR)?></td>
			<td class="value_delimited" style="text-align:center;"><?php echo $thisRow['guest_count_existing']?></td>
			<td class="value_delimited" style="text-align:center;"><?php echo  CTemplate::divide_and_format((int)$thisRow['instore_signup_existing'] * 100, $thisRow['guest_count_existing'], 2) ?>%</td>
			<td class="value_delimited" style="text-align:center;"><?php echo $thisRow['guest_count_new']?></td>
			<td class="value_delimited" style="text-align:center;"><?php echo  CTemplate::divide_and_format((int)$thisRow['instore_signup_new'] * 100, $thisRow['guest_count_new'], 2) ?>%</td>
			<td class="value_delimited" style="text-align:center;"><?php echo $thisRow['guest_count_reacquired']?></td>
			<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$thisRow['instore_signup_reacquired'] * 100, $thisRow['guest_count_reacquired'], 2) ?>%</td>
			<td class="value_delimited" style="text-align:center;"><?php echo $thisRow['lost_guests_at_45_days']?></td>
			<td class="value_delimited" style="text-align:center;"><?php echo $thisRow['num_cancelled_orders']?></td>
			<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($thisRow['avg_servings_per_guest_regular'])?></td>
			<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($thisRow['average_annual_regular_visits'])?></td>
		</tr>
	<?php  } ?>


	<?php if ($this->curReportType == 'single_store') { ?>
		<tr>
			<td class="space_right_delimited" style="text-align:right;"><span data-help="trending-performance-12_month_average">12 mo. Store Avg.</span></td>
			<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['guest_count_existing'])?></td>
			<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['instore_signup_existing'])?>%</td>
			<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['guest_count_new'])?></td>
			<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['instore_signup_new'])?>%</td>
			<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['guest_count_reacquired'])?></td>
			<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['instore_signup_reacquired'])?>%</td>
			<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['lost_guests_at_45_days'])?></td>
			<td class="space_right_delimited" style="text-align:center;"><?php echo $this->rollups['store_avg']['num_cancelled_orders']?></td>
			<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['avg_servings_per_guest_regular'])?></td>
			<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['store_avg']['average_annual_regular_visits'])?></td>
		</tr>
	<?php } ?>


	<tr>
		<td class="space_right_delimited" style="text-align:right;"><span data-help="trending-performance-12_month_national_average">12 mo. National Avg.</span></td>
		<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['guest_count_existing'])?></td>
		<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['instore_signup_existing'])?>%</td>
		<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['guest_count_new'])?></td>
		<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['instore_signup_new'])?>%</td>
		<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['guest_count_reacquired'])?></td>
		<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['instore_signup_reacquired'])?>%</td>
		<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['lost_guests_at_45_days'])?></td>
		<td class="space_right_delimited" style="text-align:center;"><?php echo $this->rollups['national_avg']['num_cancelled_orders']?></td>
		<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['avg_servings_per_guest_regular'])?></td>
		<td class="space_right_delimited" style="text-align:center;"><?php echo CTemplate::number_format( $this->rollups['national_avg']['average_annual_regular_visits'])?></td>
	</tr>

</table>