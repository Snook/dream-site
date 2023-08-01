<table width="100%" style="border-collapse: collapse; padding:0px; border:4px solid black; margin-bottom:12px;">
	<tr>
		<td colspan="5"class="space_section_head" style="text-align:center;"><span data-help="dashboard-sessions">Sessions</span></td>
	</tr>
	<tr>
		<td class="space_right_delimited" style="width:20%;"></td>
		<td class="space_right_delimited" style="width:20%; text-align:center;" ><span data-help="dashboard-sessions-session_count">Sessions</span></td>
		<td class="space_right_delimited" style="width:20%; text-align:center;" ><span data-help="dashboard-sessions-orders_count">Orders</span></td>
		<td class="space_right_delimited" style="width:20%; text-align:center;" ><span data-help="dashboard-sessions-orders_per_session">Orders per Session</span></td>
		<td class="space_right_delimited" style="width:20%; text-align:center;" ><span data-help="dashboard-sessions-adjusted_gross_revenue">Gross Revenue</span></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:right">Total</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['sessions_count_all'];?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['orders_count_all'];?></td>
		<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_all'], $this->curMonthGuestMetrics['sessions_count_all'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['total_agr'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:right">Assembly</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['sessions_count_regular'];?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['orders_count_regular'];?></td>
		<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_regular'], $this->curMonthGuestMetrics['sessions_count_regular'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['agr_by_session_standard'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:right">Pick Up</td>
		<td class="value_delimited" style="text-align:center;"><?php echo ($this->curMonthGuestMetrics['sessions_count_mfy'] );?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo ($this->curMonthGuestMetrics['orders_count_mfy']);?></td>
		<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::divide_and_format(($this->curMonthGuestMetrics['orders_count_mfy'] ), ($this->curMonthGuestMetrics['sessions_count_mfy']), 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format(($this->curMonthAGRMetrics['agr_by_session_mfy'] -  $this->curMonthAGRMetrics['agr_by_session_mfy_community_pickup'] - $this->curMonthAGRMetrics['agr_by_session_mfy_walk_in'] - $this->curMonthAGRMetrics['agr_by_session_mfy_delivery']), 2);?>
																&nbsp;(<?php echo CTemplate::divide_and_format(($this->curMonthAGRMetrics['agr_by_session_mfy'] -  $this->curMonthAGRMetrics['agr_by_session_mfy_community_pickup'] - $this->curMonthAGRMetrics['agr_by_session_mfy_walk_in'] - $this->curMonthAGRMetrics['agr_by_session_mfy_delivery']) * 100, $this->curMonthAGRMetrics['total_agr'], 2);?>%)</td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:right">Community Pick Up</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['sessions_count_mfy_community_pickup'];?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['orders_count_mfy_community_pickup'];?></td>
		<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_mfy_community_pickup'], $this->curMonthGuestMetrics['sessions_count_mfy_community_pickup'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['agr_by_session_mfy_community_pickup'], 2);?>
																&nbsp;(<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['agr_by_session_mfy_community_pickup'] * 100, $this->curMonthAGRMetrics['total_agr'], 2);?>%)</td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:right">Walk-In</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['sessions_count_mfy_walk_in'];?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['orders_count_mfy_walk_in'];?></td>
		<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_mfy_walk_in'], $this->curMonthGuestMetrics['sessions_count_mfy_walk_in'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['agr_by_session_mfy_walk_in'], 2);?>
																&nbsp;(<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['agr_by_session_mfy_walk_in'] * 100, $this->curMonthAGRMetrics['total_agr'], 2);?>%)</td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:right">Home Delivery</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['sessions_count_mfy_delivery'];?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['orders_count_mfy_delivery'];?></td>
		<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_mfy_delivery'], $this->curMonthGuestMetrics['sessions_count_mfy_delivery'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['agr_by_session_mfy_delivery'], 2);?>
																&nbsp;(<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['agr_by_session_mfy_delivery'] * 100, $this->curMonthAGRMetrics['total_agr'], 2);?>%)</td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:right">Workshops</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['sessions_count_taste'];?> <span data-help="dashboard-taste_occupied_count">(<?php echo $this->occupiedTasteSessionCount;?>)</span></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['orders_count_taste'];?></td>
		<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_taste'], $this->curMonthGuestMetrics['sessions_count_taste'], 2);?>
			<span data-help="dashboard-taste_occupied_rate">(<?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_taste'], $this->occupiedTasteSessionCount, 2);?>)</span>
		</td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['agr_by_session_taste'], 2);?></td>
	</tr>

	<tr>
		<td class="label_delimited" style="text-align:right">Fundraising</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['sessions_count_fundraiser'];?> <span data-help="dashboard-fundraiser_occupied_count">(<?php echo $this->occupiedFundraiserSessionCount;?>)</span></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['orders_count_fundraiser'];?></td>
		<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_fundraiser'], $this->curMonthGuestMetrics['sessions_count_fundraiser'], 2);?>
			<span data-help="dashboard-fundraiser_occupied_rate">(<?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_fundraiser'], $this->occupiedFundraiserSessionCount, 2);?>)</span>
		</td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['agr_by_session_fundraiser'], 2);?></td>
	</tr>

	<?php if ($this->showDeliveredRows) { ?>
		<tr>
			<td class="label_delimited" style="text-align:right">Delivered</td>
			<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['sessions_count_delivered'];?> <!--<span>(<?php echo "TBD"; //$this->occupiedFundraiserSessionCount;?>)</span>--></td>
			<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['orders_count_delivered'];?></td>
			<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_delivered'], $this->curMonthGuestMetrics['sessions_count_delivered'], 2);?>
				<!--<span>(<?php echo "TBD"; //CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_delivered'], $this->occupiedFundraiserSessionCount, 2);?>)</span>-->
			</td>
			<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['agr_by_session_delivered'], 2);?></td>
		</tr>
	<?php } ?>
</table>
