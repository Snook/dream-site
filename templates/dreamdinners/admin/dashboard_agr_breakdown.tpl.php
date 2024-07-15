<table width="100%" style="border-collapse: collapse; padding:0px; border:4px solid black; margin-bottom:12px;"">
	<tr>
		<td colspan="6" class="space_section_head" style="text-align:center;"><span data-help="dashboard-gross_revenue_breakdown">Gross Revenue Break Down</span></td>
	</tr>
	<tr>
		<td colspan="2" class="space_right_delimited"></td>
		<td class="space_right_delimited" style="text-align:center;" ><span data-help="dashboard-gross_revenue">Gross Revenue</span></td>
		<td class="space_right_delimited" style="text-align:center;"><span data-help="dashboard-gross_revenue_breakdown-average_ticket">Average Ticket</span></td>
		<td class="space_right_delimited" style="text-align:center;" ><span data-help="dashboard-gross_revenue_breakdown-addon_sales">Total Sides &amp; Sweets Sales</span></td>
		<td class="space_right_open" style="text-align:center;" ><span data-help="dashboard-gross_revenue_breakdown-avg_addon_sales">Avg. Sides &amp; Sweets Sales</span></td>
	</tr>
	<tr>
		<td  colspan="2" class="label_delimited" style="text-align:right; width:20%;"><span data-help="dashboard-gross_revenue_breakdown-totals">Totals</span></td>
    	<td class="value_delimited" style="text-align:center; width:20%;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_existing_taste'] +
    																										$this->curMonthAGRMetrics['revenue_by_guest_existing_intro'] +
    																										$this->curMonthAGRMetrics['revenue_by_guest_existing_regular'] +
																											$this->curMonthAGRMetrics['revenue_by_guest_existing_additional'] +
                                                                                                            $this->curMonthAGRMetrics['revenue_by_guest_existing_fundraiser'] +
																									    		$this->curMonthAGRMetrics['revenue_by_guest_reacquired_taste'] +
																									    		$this->curMonthAGRMetrics['revenue_by_guest_reacquired_intro'] +
																									    		$this->curMonthAGRMetrics['revenue_by_guest_reacquired_regular'] +
																												$this->curMonthAGRMetrics['revenue_by_guest_reacquired_additional'] +
                                                                                                                $this->curMonthAGRMetrics['revenue_by_guest_reacquired_fundraiser'] +
																									    		$this->curMonthAGRMetrics['revenue_by_guest_new_taste'] +
																									    		$this->curMonthAGRMetrics['revenue_by_guest_new_intro'] +
																									    		$this->curMonthAGRMetrics['revenue_by_guest_new_regular'] +
																												$this->curMonthAGRMetrics['revenue_by_guest_new_additional'] +
                                                                                                                $this->curMonthAGRMetrics['revenue_by_guest_new_fundraiser'] +
																													$this->curMonthAGRMetrics['agr_from_door_dash'], 2);?></td>
		<td class="value_delimited" style="text-align:center; width:20%;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_all'], 2);?></td>
		<td class="value_delimited" style="text-align:center; width:20%;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_total'], 2);?>
			 &nbsp;(<?php echo CTemplate::divide_and_format((float)$this->curMonthAGRMetrics['addon_sales_total'] * 100, $this->curMonthAGRMetrics['total_agr'], 2);?>%)</td>
		</td>
		<td class="value_delimited" style="text-align:center; width:20%;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_total'], $this->curMonthGuestMetrics['total_to_date_orders'], 2);?></td>
  </tr>

	<?php
	$rowSpan = 4;
	if ($this->showDeliveredRows) {
		$rowSpan++;
	}

	if ($this->showAdditionalOrderRows) {
		$rowSpan++;
	}?>

	<tr>
		<td rowspan="<?php echo $rowSpan; ?>" class="label_delimited" style="text-align:right"><span data-help="dashboard-guests-existing_guest">Existing Guests</span></td>
		<td  class="label_delimited" style="text-align:center"><span>Regular Order</span></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_existing_regular'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_existing_regular'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_existing_regular'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_existing_regular'], $this->curMonthGuestMetrics['existing_regular_to_date_orders'], 2);?></td>
	</tr>
	<?php if ($this->showAdditionalOrderRows) { ?>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Additional Order</span></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_existing_additional'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_existing_additional'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_existing_additional'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_existing_additional'], $this->curMonthGuestMetrics['existing_additional_to_date_orders'], 2);?></td>
	</tr>
	<?php	} ?>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Starter Pack</span></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_existing_intro'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_existing_intro'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_existing_intro'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_existing_intro'], $this->curMonthGuestMetrics['existing_intro_to_date_orders'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Workshops</span></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_existing_taste'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_existing_taste'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_existing_taste'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_existing_taste'], $this->curMonthGuestMetrics['existing_taste_to_date_orders'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Fundraising</span></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_existing_fundraiser'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_existing_fundraiser'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_existing_fundraiser'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_existing_fundraiser'], $this->curMonthGuestMetrics['existing_fundraiser_to_date_orders'], 2);?></td>
	</tr>

<?php if ($this->showDeliveredRows) { ?>

	<td class="label_delimited" style="text-align:center"><span>Shipping</span></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_existing_delivered'], 2);?></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_existing_delivered'], 2);?></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format(0, 2);?></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format(0, 2);?></td>
<?php	} ?>

	<tr>
		<td rowspan="<?php echo $rowSpan; ?>" class="label_delimited" style="text-align:right; width:140px;"><span data-help="dashboard-guests-reacquired_guest">Re-Acquired</span></td>
		<td class="label_delimited" style="text-align:center;">Regular Order</td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_reacquired_regular'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_reacquired_regular'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_reacquired_regular'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_reacquired_regular'], $this->curMonthGuestMetrics['reacquired_regular_to_date_orders'], 2);?></td>
	</tr>
<?php if ($this->showAdditionalOrderRows) { ?>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Additional Order</span></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_reacquired_additional'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_reacquired_additional'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_reacquired_additional'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_reacquired_additional'], $this->curMonthGuestMetrics['reacquired_additional_to_date_orders'], 2);?></td>
	</tr>
<?php	} ?>
	<tr>
		<td class="label_delimited" style="text-align:center;">Starter Pack</td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_reacquired_intro'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_reacquired_intro'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_reacquired_intro'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_reacquired_intro'], $this->curMonthGuestMetrics['reacquired_intro_to_date_orders'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:center; width:100px;">Workshops</td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_reacquired_taste'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_reacquired_taste'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_reacquired_taste'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_reacquired_taste'], $this->curMonthGuestMetrics['reacquired_taste_to_date_orders'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:center; width:100px;">Fundraising</td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_reacquired_fundraiser'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_reacquired_fundraiser'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_reacquired_fundraiser'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_reacquired_fundraiser'], $this->curMonthGuestMetrics['reacquired_fundraiser_to_date_orders'], 2);?></td>
	</tr>

<?php if ($this->showDeliveredRows) { ?>
	<td class="label_delimited" style="text-align:center"><span>Shipping</span></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_reacquired_delivered'], 2);?></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_reacquired_delivered'], 2);?></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format(0, 2);?></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format(0, 2);?></td>
<?php	} ?>

	<tr>
		<td rowspan="<?php echo $rowSpan; ?>" class="label_delimited" style="text-align:right"><span data-help="dashboard-guests-new_guest">New Guests</span></td>
		<td class="label_delimited" style="text-align:center;">Regular Order</td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_new_regular'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_new_regular'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_new_regular'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_new_regular'], $this->curMonthGuestMetrics['new_regular_to_date_orders'], 2);?></td>
	</tr>
<?php if ($this->showAdditionalOrderRows) { ?>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Additional Order</span></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_new_additional'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_new_additional'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_new_additional'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_new_additional'], $this->curMonthGuestMetrics['new_additional_to_date_orders'], 2);?></td>
	</tr>
<?php	} ?>
	<tr>
		<td class="label_delimited" style="text-align:center;">Starter Pack</td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_new_intro'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_new_intro'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_new_intro'], 2);?></td>
		<td class="value_delimited" style="text-align:center;" >$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_new_intro'], $this->curMonthGuestMetrics['new_intro_to_date_orders'], 2);?></td>
	</tr>

	<tr>
		<td class="label_delimited" style="text-align:center;">Workshops</td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_new_taste'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_new_taste'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_new_taste'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_new_taste'], $this->curMonthGuestMetrics['new_taste_to_date_orders'], 2);?></td>
	</tr>

	<tr>
		<td class="label_delimited" style="text-align:center;">Fundraising</td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_new_fundraiser'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_new_fundraiser'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['addon_sales_by_guest_new_fundraiser'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::divide_and_format($this->curMonthAGRMetrics['addon_sales_by_guest_new_fundraiser'], $this->curMonthGuestMetrics['new_fundraiser_to_date_orders'], 2);?></td>
	</tr>

<?php if ($this->showDeliveredRows) { ?>
	<td class="label_delimited" style="text-align:center"><span>Shipping</span></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['revenue_by_guest_new_delivered'], 2);?></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['avg_ticket_by_guest_new_delivered'], 2);?></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format(0, 2);?></td>
	<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format(0, 2);?></td>
<?php	} ?>

	<tr>
		<td class="label_delimited" style="text-align:right"><span data-help="">DoorDash</span></td>
		<td class="label_delimited" style="text-align:center;">Delivery Orders</td>
		<td class="value_delimited" style="text-align:center;">$<?php echo CTemplate::number_format($this->curMonthAGRMetrics['agr_from_door_dash'], 2);?></td>
		<td class="value_delimited" style="text-align:center;">-</td>
		<td class="value_delimited" style="text-align:center;">-</td>
		<td class="value_delimited" style="text-align:center;" >-</td>
	</tr>
</table>