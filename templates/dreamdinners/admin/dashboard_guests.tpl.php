<?php
// Cap all in-store rates at 100% - it is fully legal to exceed 100% but has caused confusion so we cap it
if ($this->curMonthGuestMetrics['instore_signup_total'] > $this->curMonthGuestMetrics['total_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_total'] = $this->curMonthGuestMetrics['total_to_date_orders'];

if ($this->curMonthGuestMetrics['instore_signup_existing_regular'] > $this->curMonthGuestMetrics['existing_regular_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_existing_regular'] = $this->curMonthGuestMetrics['existing_regular_to_date_orders'];
if ($this->curMonthGuestMetrics['instore_signup_existing_intro'] > $this->curMonthGuestMetrics['existing_intro_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_existing_intro'] = $this->curMonthGuestMetrics['existing_intro_to_date_orders'];
if ($this->curMonthGuestMetrics['instore_signup_existing_taste'] > $this->curMonthGuestMetrics['existing_taste_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_existing_taste'] = $this->curMonthGuestMetrics['existing_taste_to_date_orders'];
if ($this->curMonthGuestMetrics['instore_signup_existing_fundraiser'] > $this->curMonthGuestMetrics['existing_fundraiser_to_date_orders'])
    $this->curMonthGuestMetrics['instore_signup_existing_fundraiser'] = $this->curMonthGuestMetrics['existing_fundraiser_to_date_orders'];


if ($this->curMonthGuestMetrics['instore_signup_reacquired_regular'] > $this->curMonthGuestMetrics['reacquired_regular_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_reacquired_regular'] = $this->curMonthGuestMetrics['reacquired_regular_to_date_orders'];
if ($this->curMonthGuestMetrics['instore_signup_reacquired_intro'] > $this->curMonthGuestMetrics['reacquired_intro_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_reacquired_intro'] = $this->curMonthGuestMetrics['reacquired_intro_to_date_orders'];
if ($this->curMonthGuestMetrics['instore_signup_reacquired_taste'] > $this->curMonthGuestMetrics['reacquired_taste_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_reacquired_taste'] = $this->curMonthGuestMetrics['reacquired_taste_to_date_orders'];
if ($this->curMonthGuestMetrics['instore_signup_reacquired_fundraiser'] > $this->curMonthGuestMetrics['reacquired_fundraiser_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_reacquired_fundraiser'] = $this->curMonthGuestMetrics['reacquired_fundraiser_to_date_orders'];


if ($this->curMonthGuestMetrics['instore_signup_new_regular'] > $this->curMonthGuestMetrics['new_regular_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_new_regular'] = $this->curMonthGuestMetrics['new_regular_to_date_orders'];
if ($this->curMonthGuestMetrics['instore_signup_new_intro'] > $this->curMonthGuestMetrics['new_intro_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_new_intro'] = $this->curMonthGuestMetrics['new_intro_to_date_orders'];
if ($this->curMonthGuestMetrics['instore_signup_new_taste'] > $this->curMonthGuestMetrics['new_taste_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_new_taste'] = $this->curMonthGuestMetrics['new_taste_to_date_orders'];
if ($this->curMonthGuestMetrics['instore_signup_new_fundraiser'] > $this->curMonthGuestMetrics['new_fundraiser_to_date_orders'])
	$this->curMonthGuestMetrics['instore_signup_new_fundraiser'] = $this->curMonthGuestMetrics['new_fundraiser_to_date_orders'];
?>


<table width="100%" style="border-collapse: collapse; padding:0px; border:4px solid black; margin-bottom:12px;">
	<tr>
		<td colspan="9" class="space_section_head" style="text-align:center;"><span data-help="dashboard-orders">Guest Orders</span></td>
	</tr>

	<tr>
		<td colspan="2" class="space_section_head"></td>
		<td colspan="3" class="space_right_delimited" style="text-align:center; border-right:none; border-bottom:gray 1px solid;" >Counts</td>
		<td class="space_section_head" style="text-align:center;"></td>
		<td colspan="2" class="space_right_delimited" style="text-align:center; border-right:none; border-bottom:gray 1px solid;" ><span data-help="dashboard-orders-in_store_signup">In-Store Sign-Up</span></td>
		<td class="space_section_head" style="text-align:center;" ></td>
	</tr>

	<tr>
		<td colspan="2" class="space_right_delimited"></td>
		<td class="space_right_delimited" style="text-align:center; border-right:gray 1px solid;background-color:#C8D375;" ><span data-help="dashboard-orders-order_count">Orders</span></td>
		<td class="space_right_delimited" style="text-align:center; border-right:gray 1px solid;background-color:#C8D375;"><span data-help="dashboard-orders-completed_order_count">Completed Orders</span></td>
		<td class="space_right_delimited" style="text-align:center;background-color:#C8D375;"><span data-help="dashboard-orders-unique_guests">Unique Guests</span></td>
		<td class="space_right_delimited" style="text-align:center;" ><span data-help="dashboard-orders-percent_to_total">% of Orders to Total Orders</span></td>
		<td class="space_right_delimited" style="text-align:center; border-right:gray 1px solid;background-color:#C8D375;" ><span data-help="dashboard-orders-in_store_signup">In-Store Rate</span></td>
		<td class="space_right_delimited" style="text-align:center; background-color:#C8D375;" ><span data-help="dashboard-orders-in_store_signup">In-Store Count</span></td>
		<td class="space_right_open" style="text-align:center;" ><span data-help="dashboard-guests-servings_per_guest">Servings per Order</span></td>
	</tr>

  <!--  Totals -->
	<tr>
		<td  colspan="2" class="label_delimited" style="text-align:right;  width:20%;">Totals</td>
		<td class="value_delimited" style="text-align:center; width:7%; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_all'];?></td>
		<td class="value_delimited" style="text-align:center; width:7%; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['total_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center; width:6%;"><?php echo $this->curMonthGuestMetrics['guest_count_total'];?></td>
		<td class="value_delimited" style="text-align:center; width:20%;">100%</td>
		<td class="value_delimited" style="text-align:center; width:10%; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format(floatval($this->curMonthGuestMetrics['instore_signup_total']) * 100, floatval($this->curMonthGuestMetrics['total_to_date_orders']), 2);?>%</td>
		<td class="value_delimited" style="text-align:center; width:10%;"><?php echo $this->curMonthGuestMetrics['instore_signup_total']?></td>
		<td class="value_delimited" style="text-align:center; width:20%;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_all'], 2);?></td>
	</tr>

  <!-- Existing -->
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
		<td class="label_delimited" style="text-align:center"><span>Regular Order</span></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_regular_existing_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['existing_regular_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_existing_regular'];?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_regular_existing_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format(floatval($this->curMonthGuestMetrics['instore_signup_existing_regular']) * 100, floatval($this->curMonthGuestMetrics['existing_regular_to_date_orders']), 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_existing_regular']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_existing_regular'], 2);?></td>
	</tr>
	<?php if ($this->showAdditionalOrderRows) { ?>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Additional Order</span></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_additional_existing_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['existing_additional_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_existing_additional'];?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_additional_existing_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['instore_signup_existing_additional'] * 100, $this->curMonthGuestMetrics['existing_additional_to_date_orders'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_existing_additional']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_existing_additional'], 2);?></td>
	</tr>
	<?php	} ?>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Starter Pack</span></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_intro_existing_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['existing_intro_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_existing_intro']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_intro_existing_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format(floatval($this->curMonthGuestMetrics['instore_signup_existing_intro']) * 100, floatval($this->curMonthGuestMetrics['existing_intro_to_date_orders']), 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_existing_intro']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_existing_intro'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Workshops</span></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_taste_existing_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['existing_taste_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_existing_taste']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_taste_existing_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format(floatval($this->curMonthGuestMetrics['instore_signup_existing_taste']) * 100, floatval($this->curMonthGuestMetrics['existing_taste_to_date_orders']), 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_existing_taste']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_existing_taste'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Fundraising</span></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_fundraiser_existing_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['existing_fundraiser_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_existing_fundraiser']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_fundraiser_existing_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['instore_signup_existing_fundraiser'] * 100, $this->curMonthGuestMetrics['existing_fundraiser_to_date_orders'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_existing_fundraiser']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_existing_fundraiser'], 2);?></td>
	</tr>

	<?php if ($this->showDeliveredRows) { ?>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Shipping</span></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_delivered_existing_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['existing_delivered_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_existing_delivered']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_delivered_existing_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;">n/a</td>
		<td class="value_delimited" style="text-align:center;">n/a</td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_existing_delivered'], 2);?></td>
	</tr>
	<?php	} ?>

  <!-- Reacquired -->
	<tr>
		<td rowspan="<?php echo $rowSpan; ?>" class="label_delimited" style="text-align:right"><span data-help="dashboard-guests-reacquired_guest">Re-Acquired</span></td>
		<td class="label_delimited" style="text-align:center;">Regular Order</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_regular_reacquired_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['reacquired_regular_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_reacquired_regular']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_regular_reacquired_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format(floatval($this->curMonthGuestMetrics['instore_signup_reacquired_regular']) * 100, floatval($this->curMonthGuestMetrics['reacquired_regular_to_date_orders']), 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_reacquired_regular']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_reacquired_regular'], 2);?></td>
	</tr>
	<?php if ($this->showAdditionalOrderRows) { ?>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Additional Order</span></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_additional_reacquired_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['reacquired_additional_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_reacquired_additional']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_additional_reacquired_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['instore_signup_reacquired_additional'] * 100, $this->curMonthGuestMetrics['reacquired_additional_to_date_orders'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_reacquired_additional']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_reacquired_additional'], 2);?></td>
	</tr>
	<?php } ?>
	<tr>
		<td class="label_delimited" style="text-align:center;">Starter Pack</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_intro_reacquired_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['reacquired_intro_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_reacquired_intro']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_intro_reacquired_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format(floatval($this->curMonthGuestMetrics['instore_signup_reacquired_intro']) * 100, floatval($this->curMonthGuestMetrics['reacquired_intro_to_date_orders']), 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_reacquired_intro']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_reacquired_intro'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:center;">Workshops</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_taste_reacquired_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['reacquired_taste_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_reacquired_taste']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_taste_reacquired_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format(floatval($this->curMonthGuestMetrics['instore_signup_reacquired_taste']) * 100, floatval($this->curMonthGuestMetrics['reacquired_taste_to_date_orders']), 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_reacquired_taste']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_reacquired_taste'], 2);?></td>
	</tr>

	<tr>
		<td class="label_delimited" style="text-align:center;">Fundraising</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_fundraiser_reacquired_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['reacquired_fundraiser_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_reacquired_fundraiser']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_fundraiser_reacquired_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['instore_signup_reacquired_fundraiser'] * 100, $this->curMonthGuestMetrics['reacquired_fundraiser_to_date_orders'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_reacquired_fundraiser']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_reacquired_fundraiser'], 2);?></td>
	</tr>

<?php if ($this->showDeliveredRows) { ?>
	<tr>
		<td class="label_delimited" style="text-align:center;">Shipping</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_delivered_reacquired_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['reacquired_delivered_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_reacquired_delivered']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format($this->curMonthGuestMetrics['orders_count_delivered_reacquired_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;">n/a</td>
		<td class="value_delimited" style="text-align:center;">n/a</td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_reacquired_delivered'], 2);?></td>
	</tr>
<?php } ?>

  <!-- New -->
	<tr>
		<td rowspan="<?php echo $rowSpan; ?>" class="label_delimited" style="text-align:right"><span data-help="dashboard-guests-new_guest">New Guests</span></td>
		<td class="label_delimited" style="text-align:center;">Regular Order</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_regular_new_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['new_regular_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_new_regular']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_regular_new_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format(floatval($this->curMonthGuestMetrics['instore_signup_new_regular']) * 100, floatval($this->curMonthGuestMetrics['new_regular_to_date_orders']), 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_new_regular']?></td>
		<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_new_regular'], 2);?></td>
	</tr>
	<?php if ($this->showAdditionalOrderRows) { ?>
	<tr>
		<td class="label_delimited" style="text-align:center"><span>Additional Order</span></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_additional_new_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['new_additional_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_new_additional']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_additional_new_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['instore_signup_new_additional'] * 100, $this->curMonthGuestMetrics['new_additional_to_date_orders'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_new_additional']?></td>
		<td class="value_delimited" style="text-align:center;" ><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_new_additional'], 2);?></td>
	</tr>
	<?php } ?>
	<tr>
		<td class="label_delimited" style="text-align:center;">Starter Pack</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_intro_new_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['new_intro_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_new_intro']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_intro_new_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format(floatval($this->curMonthGuestMetrics['instore_signup_new_intro']) * 100, floatval($this->curMonthGuestMetrics['new_intro_to_date_orders']), 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_new_intro']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_new_intro'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:center;">Workshops</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_taste_new_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['new_taste_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_new_taste']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_taste_new_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format(floatval($this->curMonthGuestMetrics['instore_signup_new_taste']) * 100, floatval($this->curMonthGuestMetrics['new_taste_to_date_orders']), 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_new_taste']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_new_taste'], 2);?></td>
	</tr>
	<tr>
		<td class="label_delimited" style="text-align:center;">Fundraising</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_fundraiser_new_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['new_fundraiser_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_new_fundraiser']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_fundraiser_new_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['instore_signup_new_fundraiser'] * 100, $this->curMonthGuestMetrics['new_fundraiser_to_date_orders'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['instore_signup_new_fundraiser']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_new_fundraiser'], 2);?></td>
	</tr>

<?php if ($this->showDeliveredRows) { ?>
	<tr>
		<td class="label_delimited" style="text-align:center;">Shipping</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['orders_count_delivered_new_guests'];?></td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;"><?php echo $this->curMonthGuestMetrics['new_delivered_to_date_orders']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo $this->curMonthGuestMetrics['guest_count_new_delivered']?></td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::divide_and_format((int)$this->curMonthGuestMetrics['orders_count_delivered_new_guests'] * 100, $this->curMonthGuestMetrics['orders_count_all'], 2);?>%</td>
		<td class="value_delimited" style="text-align:center; border-right:gray 1px solid;">n/a</td>
		<td class="value_delimited" style="text-align:center;">n/a</td>
		<td class="value_delimited" style="text-align:center;"><?php echo CTemplate::number_format($this->curMonthGuestMetrics['avg_servings_per_guest_new_delivered'], 2);?></td>
	</tr>
<?php } ?>
</table>