<div>
	<?php
	$customerView = true;
	if (isset($this->customer_view) && $this->customer_view === 0)
	{
		$customerView = false;
	}

	// Disabled for now, confused customers
	$displayStationNumber = false;

	$DFL_tag_for_standard = false;
	if (isset($this->store_supports_DFL) && $this->store_supports_DFL)
	{
		if ($this->orderInfo['menu_program_id'] == 2)
		{
			$DFL_tag_for_standard = " (from Std Menu)";
		}
	}

	$orderIsPP = false;
	$orderIsDR = false;
	$orderIsMPP = false;

	$userIsDR = false;
	$userIsPP = false;
	$userIsMPP = false;


	if (!empty($this->membership_status) && $this->membership_status == CUser::MEMBERSHIP_STATUS_CURRENT)
	{
		$userIsMPP = true;
	}
	else if (!empty($this->plate_points) && $this->plate_points['status'] == 'active')
	{
		$userIsPP = true;
	}
	else if (!empty($this->dr_info) && ($this->dr_info['status'] == 'Active' || $this->dr_info['status'] == 'Reactivated'))
	{
		$userIsDR = true;
	}

	if (!empty($this->orderInfo['membership_id']))
	{
		$orderIsMPP = true;
	}
	else if (!empty($this->orderInfo['is_in_plate_points_program']))
	{
		$orderIsPP = true;
	}
	else if (!empty($this->orderInfo['dream_rewards_level']))
	{
		$orderIsDR = true;
	}
	?>
	<?php if (!empty($this->sessionInfo['session_start']) && !empty($this->orderInfo['order_confirmation'])) { ?>
		<table class="mb-3">
			<tr>
				<td style="vertical-align:top;">
					<table class="font-size-medium-small" style="line-height: 1rem;">
						<?php if ($customerView) { ?>
							<tr>
								<td rowspan="4">
									<img style="height: 110px;" src="<?php echo ADMIN_IMAGES_PATH; ?>/style/light_green_logo.png" alt="Dream Dinners" /></td>
								<td colspan="2" class="font-weight-bold">Welcome to Dream Dinners</td>
							</tr>
						<?php } ?>
						<tr>
							<td class="font-weight-bold">Guest:</td>
							<?php
							if (!empty($this->user))
							{
								if (is_object($this->user))
								{
									$this->customerName = $this->user->firstname . " " . $this->user->lastname;
									$phone = $this->user->telephone_1;
								}
								else
								{
									$this->customerName = $this->user['firstname'] . " " . $this->user['lastname'];
									$phone = $this->user['telephone_1'];
								}
							}
							?>
							<?php if (!empty($this->customerName)) { ?>
								<td><?php echo $this->customerName; ?>
									<?php if (!empty($this->corporate_crate_client) && !empty($this->corporate_crate_client->is_active)) { ?>
										<img alt="<?php echo $this->corporate_crate_client->company_name; ?>" src="<?php echo ADMIN_IMAGES_PATH; ?>/corporate/<?php echo $this->corporate_crate_client->icon_path; ?>_icon.png" style="margin-left: 4px;" data-tooltip="<?php echo $this->corporate_crate_client->company_name; ?>" />
									<?php } ?>
								</td>
							<?php } else { ?>
								<td>&nbsp;</td>
							<?php } ?>
						</tr>
						<tr>
							<td class="font-weight-bold">Delivery:</td>
							<?php if (empty($this->sessionInfo['session_is_deleted'])) { ?>
							<td>
								<?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], MONTH_DAY_YEAR); ?>
							</td>
							<?php } else { ?>
								<td style="font-size: smaller;" ><span style="text-decoration: line-through;">
								<?php echo $this->dateTimeFormat($this->sessionInfo['session_start'], MONTH_DAY_YEAR); ?></span> (Deleted)<td>
							<?php } ?>
						</tr>
						<tr>
							<td class="font-weight-bold">Phone:</td>
							<td><?php echo $phone; ?></td>
						</tr>
					</table>
				</td>

				<?php if (!$customerView) { // Store Receipt ?>
					<td class="w-50">
						<table>
							<tr>
								<td class="font-weight-bold">Guest Email:</td>
								<td><?php echo $this->user->primary_email; ?></td>
							</tr>
							<?php if (!empty($this->user->telephone_1)) { ?>
								<tr>
									<td class="font-weight-bold">Primary Phone:</td>
									<td><?php echo $this->user->telephone_1; ?></td>
								</tr>
							<?php } ?>
							<?php if (!empty($this->user->telephone_2)) { ?>
								<tr>
									<td class="font-weight-bold">Secondary Phone:</td>
									<td><?php echo $this->user->telephone_2; ?></td>
								</tr>
							<?php } ?>
						</table>
					</td>
				<?php } ?>
			</tr>
		</table>
	<?php } ?>

	<div class="row mb-2">
		<div class="col">
			<p class="p-1 mb-1 font-weight-bold border-top border-bottom font-size-medium-small">Order Summary</p>
			<table>
				<?php if (isset($this->is_customer_franchise_report) && $this->is_customer_franchise_report) { ?>
					<tr>
						<td class="font-weight-bold">Last Session Attended at this Store:</td>
						<td>
							<?php if (!empty($this->last_session_attended)) { ?>
								<?php echo $this->dateTimeFormat($this->last_session_attended, NORMAL); ?>
							<?php } else { ?>
								Never
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<td class="font-weight-bold">Order Date:</td>
					<td>
						<?php echo CTemplate::dateTimeFormat($this->orderInfo['timestamp_created'], NORMAL, $this->orderInfo['store_id'], CONCISE); ?>
					</td>
				</tr>
				<tr>
					<td class="font-weight-bold">Box Count:</td>
					<td><?php echo count($this->menuInfo['current_boxes']); ?></td>
				</tr>
				<?php if (!empty($this->orderInfo['servings_total_count'])) { //orders older than 2007 are null ?>
					<tr>
						<td class="font-weight-bold">Cost Per Serving:</td>
						<td>$<?php echo $this->moneyFormat($this->orderInfo['grand_total'] / $this->orderInfo['servings_total_count']); ?></td>
					</tr>

					<tr>
						<td class="font-weight-bold">Servings Count:</td>
						<td><?php echo $this->orderInfo['servings_total_count']; ?></td>
					</tr>

				<?php } ?>

				<?php if (true /*!isset($this->is_customer_franchise_report) || !$this->is_customer_franchise_report*/) { // Don't show on customer/franchise report ?>
					<tr>
						<td class="font-weight-bold">Confirmation Number:</td>
						<td><?php echo $this->orderInfo['order_confirmation']; ?></td>
					</tr>
					<tr>
						<td class="font-weight-bold">Tracking Number(s):</td>
						<td><?php echo $this->orderInfo['tracking_number']; ?></td>
					</tr>
				<?php } ?>
			</table>
		</div>
	</div>

	<div class="row">
		<?php if (!empty($this->sessionInfo) && ($this->sessionInfo['session_type_text'] == CSession::DELIVERY ||  $this->sessionInfo['session_type_text'] == CSession::DELIVERED)
			&& !empty($this->orderInfo['orderAddress']['address_line1'])) { ?>
			<div class="col">
				<p class="p-1 mb-1 font-weight-bold border-top border-bottom font-size-medium-small">Shipping Address</p>
				<div class="p-1">
					<div><?php echo $this->orderInfo['orderAddress']['firstname']; ?> <?php echo $this->orderInfo['orderAddress']['lastname']; ?></div>
					<div><?php echo $this->orderInfo['orderAddress']['address_line1']; ?></div>
					<?php echo (!empty($this->orderInfo['orderAddress']['address_line2'])) ? "<div>" . $this->orderInfo['orderAddress']['address_line2'] . "</div>" : ''; ?>
					<div><?php echo $this->orderInfo['orderAddress']['city']; ?>, <?php echo $this->orderInfo['orderAddress']['state_id']; ?> <?php echo $this->orderInfo['orderAddress']['postal_code']; ?></div>
					<div><?php echo $this->orderInfo['orderAddress']['telephone_1']; ?></div>
					<?php if (!empty($this->orderInfo['orderAddress']['address_note'])) { ?>
						<div>Notes: <?php echo $this->orderInfo['orderAddress']['address_note']; ?></div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
		<?php if (!empty($this->user->preferences[CUser::USER_ACCOUNT_NOTE]['value']) || !empty($this->orderInfo['order_user_notes'])
			|| ($customerView == false && (!empty($this->orderInfo['guest_carryover_notes']) || !empty($this->orderInfo['order_admin_notes'])))) { ?>
			<?php if (!empty($this->user->preferences[CUser::USER_ACCOUNT_NOTE]['value'])) { // if order instructions ?>
				<div class="col">
					<p class="p-1 mb-1 font-weight-bold border-top border-bottom font-size-medium-small">Account Notes</p>
					<div class="p-1"><?php echo nl2br($this->user->preferences[CUser::USER_ACCOUNT_NOTE]['value']); ?></div>
				</div>
			<?php } ?>
			<?php if (!empty($this->orderInfo['order_user_notes'])) { // if order instructions ?>
				<div class="col">
					<p class="p-1 mb-1 font-weight-bold border-top border-bottom font-size-medium-small">Guest Special Instructions</p>
					<div class="p-1"><?php echo $this->orderInfo['order_user_notes']; ?></div>
				</div>
			<?php } ?>
			<?php if ($customerView == false && (!empty($this->orderInfo['guest_carryover_notes']) || !empty($this->orderInfo['order_admin_notes']))) { // If carryover or admin notes ?>
				<?php if (!empty($this->orderInfo['order_admin_notes'])) { // if admin notes ?>
					<div class="col">
						<p class="p-1 mb-1 font-weight-bold border-top border-bottom font-size-medium-small">Administrative Notes</p>
						<div class="p-1"><?php echo $this->orderInfo['order_admin_notes']; ?></div>
					</div>
				<?php } ?>
				<?php if (!empty($this->orderInfo['guest_carryover_notes'])) { // if carryover notes ?>
					<div class="col">
						<p class="p-1 mb-1 font-weight-bold border-top border-bottom font-size-medium-small">Guest Carryover Notes</p>
						<div class="p-1"><?php echo $this->orderInfo['guest_carryover_notes']; ?></div>
					</div>
				<?php } ?>
			<?php } ?>
		<?php } ?>
	</div>

	<div class="row my-2">
		<div class="col">
			<p class="p-1 mb-1 font-weight-bold border-top border-bottom font-size-medium-small">Order Details</p>
			<table style="width:100%;">
				<thead>
				<tr>
					<th class="text-center">Qty</th>
					<th class="text-center">Size</th>
					<th class="font-weight-bold">Dinner</th>
				</tr>
				</thead>
				<tbody>

				<?php if (!empty($this->menuInfo['current_boxes']))
				{
					foreach($this->menuInfo['current_boxes'] as $box_inst_id => $boxContents)
					{
						$this->assign('bundle_data', $boxContents['bundle_data']);
						$this->assign('bundle_items', $boxContents['bundle_items']);
						$this->assign('box_inst_id', $box_inst_id);
						$this->assign('box_id',  $boxContents['box_id']);
						$this->assign('box_type',  $boxContents['box_type']);
						$this->assign('box_label', $boxContents['box_label']);

						echo '<tr><td colspan="3" class="font-weight-bold text-white-space-nowrap">';
						echo $boxContents['box_label'];
						echo '</td><td colspan="3">&nbsp;</td></tr>';

						foreach($this->bundle_items as $id => $itemData) {
							if( $itemData['qty'] > 0){
								echo '<tr>';
								echo '<td class="text-center">';
								if($boxContents['box_type'] == CBox::DELIVERED_FIXED){
									echo '1';
								}else{
									echo $itemData['qty'];
								}
								echo '</td>';
								echo '<td>';
								echo $itemData['is_side_dish'] ? "" : ($itemData['pricing_type'] == 'FULL' ? 'Large' : 'Medium');
								echo '</td>';
								echo '<td>';
								echo $itemData['display_title'];
								echo '</td>';
								echo '</tr>';
							}

						}
					}
				} ?>



				<?php if (!$this->isEmptyFloat($this->orderInfo['misc_food_subtotal']) || !$this->isEmptyFloat($this->orderInfo['misc_nonfood_subtotal'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '6' : '5'; ?>" class="text-right">
							<hr class="float-right w-25" size="1" noshade="noshade" />
						</td>
					</tr>
				<?php } ?>

				<?php if (!$this->isEmptyFloat($this->orderInfo['misc_food_subtotal'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Misc Food ( <?= $this->orderInfo['misc_food_subtotal_desc']; ?> )</td>
						<td align="right"><?php echo '$', $this->moneyFormat($this->orderInfo['misc_food_subtotal']); ?></td>
					</tr>
				<?php } ?>
				<?php if (!$this->isEmptyFloat($this->orderInfo['misc_nonfood_subtotal'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Misc Non-Food ( <?= $this->orderInfo['misc_nonfood_subtotal_desc']; ?> )</td>
						<td align="right"><?php echo '$', $this->moneyFormat($this->orderInfo['misc_nonfood_subtotal']); ?></td>
					</tr>
				<?php } ?>


				<!-- Order subtotal -->
				<tr>
					<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">
						<?= $this->isEmptyFloat($this->orderInfo['family_savings_discount']) ? 'Order Subtotal' : 'Discounted Order Subtotal' ?>
						<?php echo(!$this->isEmptyFloat($this->orderInfo['misc_food_subtotal']) || !$this->isEmptyFloat($this->orderInfo['misc_nonfood_subtotal']) ? '( includes misc )' : ''); ?></td>
					<td align="right">
						$<span id="DO_item_subtotal"><?= $this->moneyFormat($this->orderInfo['subtotal_menu_items'] + $this->orderInfo['subtotal_products'] + $this->orderInfo['misc_food_subtotal'] + $this->orderInfo['subtotal_home_store_markup'] - $this->orderInfo['subtotal_menu_item_mark_down'] - ($this->isEmptyFloat($this->orderInfo['family_savings_discount']) ? 0 : $this->orderInfo['family_savings_discount']) - ($this->isEmptyFloat($this->orderInfo['bundle_discount']) ? 0 : $this->orderInfo['bundle_discount'])); ?></span><span id="DO_nonFoodTotal" class="collapse"><?= $this->orderInfo['subtotal_products'] ?></span>
					</td>
				</tr>

				<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_service_fee']) || $this->orderInfo['service_fee_description'] == "Free Assembly Promo") { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Service Fee</td>
						<td align="right">$<span id="DO_subtotal_service_fee"><?= $this->moneyFormat($this->orderInfo['subtotal_service_fee']) ?></span></td>
					</tr>
				<?php } ?>

				<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_delivery_fee'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Delivery Fee</td>
						<td align="right">$<span id="DO_subtotal_delivery_fee"><?= $this->moneyFormat($this->orderInfo['subtotal_delivery_fee']) ?></span></td>
					</tr>
				<?php } ?>

				<?php if (!$this->isEmptyFloat($this->orderInfo['volume_discount_total'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Volume Reward</td>
						<td align="right">-$<?= $this->moneyFormat($this->orderInfo['volume_discount_total']) ?></td>
					</tr>
				<?php } ?>
				<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_premium_markup'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Quick 6 Premium</td>
						<td align="right">-$<?= $this->moneyFormat($this->orderInfo['subtotal_premium_markup']) ?></td>
					</tr>
				<?php } ?>
				<?php if (!$this->isEmptyFloat($this->orderInfo['user_preferred_discount_total'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Preferred Discount</td>
						<td align="right">-$<span id="DO_preferred_discount"><?= $this->moneyFormat($this->orderInfo['user_preferred_discount_total']) ?></span></td>
					</tr>
				<?php } ?>
				<?php if (!$this->isEmptyFloat($this->orderInfo['membership_discount'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Meal Prep+ Discount</td>
						<td align="right">-$<span><?= $this->moneyFormat($this->orderInfo['membership_discount']) ?></span></td>
					</tr>
				<?php } ?>

				<?php if (!$this->isEmptyFloat($this->orderInfo['dream_rewards_discount'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Dream Rewards Discount</td>
						<td align="right">-$<span id="DO_dream_rewards_discount"><?= $this->moneyFormat($this->orderInfo['dream_rewards_discount']) ?></span></td>
					</tr>
				<?php } ?>
				<?php $hasDiscount = $this->isEmptyFloat($this->orderInfo['direct_order_discount']) ? false : true; ?>
				<tr id="DOD_row" class="<?= ($hasDiscount ? '' : 'collapse') ?>">
					<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Direct Order Discount</td>
					<td align="right">-$<span id="DO_discount"><?= $this->moneyFormat($this->orderInfo['direct_order_discount']) ?></span></td>
				</tr>
				<?php $hasPPDiscount = $this->isEmptyFloat($this->orderInfo['points_discount_total']) ? false : true; ?>
				<tr id="PPD_row" class="<?= ($hasPPDiscount ? '' : 'collapse') ?>">
					<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">PLATEPOINTS Dinner Dollars</td>
					<td align="right">-$<span id="PP_discount"><?= $this->moneyFormat($this->orderInfo['points_discount_total']) ?></span></td>
				</tr>
				<?php $hasPromo = $this->isEmptyFloat($this->orderInfo['promo_code_discount_total']) ? false : true; ?>
				<tr id="DOPromo_row" class="<?= ($hasPromo ? '' : 'collapse') ?>">
					<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Promotion Discount</td>
					<td align="right">-$<span id="DO_promo_discount"><?= $this->moneyFormat($this->orderInfo['promo_code_discount_total']) ?></span></td>
				</tr>
				<?php $hasCoupon = ($this->isEmptyFloat($this->orderInfo['coupon_code_discount_total']) && empty($this->coupon_title)) ? false : true; ?>
				<tr id="DOCoupon_row" class="<?= ($hasCoupon ? '' : 'collapse') ?>">
					<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">
						<input type="hidden" name="temp_coupon_id" id="temp_coupon_id" value="<?= $this->orderInfo['coupon_code_id'] ?>" />Coupon <?= isset($this->coupon_title) ? '(' . $this->coupon_title . ')' : "" ?>
					</td>
					<td align="right">-$<span id="DO_coupon_discount"><?= $this->moneyFormat($this->orderInfo['coupon_code_discount_total']) ?></span></td>
				</tr>
				<?php if (!$this->isEmptyFloat($this->orderInfo['session_discount_total'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Session Discount</td>
						<td align="right">-$<span id="DO_session_discount"><?= $this->moneyFormat($this->orderInfo['session_discount_total']) ?></span></td>
					</tr>
				<?php } ?>
				<tr>
					<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Food Tax</td>
					<td align="right">$<span id="DO_taxes"><?= $this->moneyFormat($this->orderInfo['subtotal_food_sales_taxes']) ?></span></td>
				</tr>
				<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_sales_taxes'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Non-Food Tax</td>
						<td align="right">$<span id="DO_taxes2"><?= $this->moneyFormat($this->orderInfo['subtotal_sales_taxes']) ?></span></td>
					</tr>
				<?php } ?>
				<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_service_tax'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Service Tax</td>
						<td align="right">$<span id="DO_taxes_service_fee"><?= $this->moneyFormat($this->orderInfo['subtotal_service_tax']) ?></span></td>
					</tr>
				<?php } ?>
				<?php if (!$this->isEmptyFloat($this->orderInfo['subtotal_delivery_tax'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Delivery Fee Tax</td>
						<td align="right">$<span id="DO_taxes_delivery_fee"><?= $this->moneyFormat($this->orderInfo['subtotal_delivery_tax']) ?></span></td>
					</tr>
				<?php } ?>
				<tr>
					<td colspan="<?php echo ($displayStationNumber) ? '6' : '5'; ?>" class="text-right">
						<hr class="float-right w-25" size="1" noshade="noshade" />
					</td>
				</tr>
				<tr>
					<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right"><b>Grand Total</b></td>
					<td align="right"><b>$<span id="ODT_grand_total"><?= $this->moneyFormat($this->orderInfo['grand_total']) ?></span></b></td>
				</tr>
				<?php if (!$this->isEmptyFloat($this->orderInfo['ltd_round_up_value'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right"><b>Dream Dinners Foundation Donation</b></td>
						<td align="right"><b>$<span id="ODT_lt_round_up_value"><?= $this->moneyFormat($this->orderInfo['ltd_round_up_value']) ?></span></b></td>
					</tr>
				<?php } ?>
				<?php if (!$this->isEmptyFloat($this->orderInfo['family_savings_discount'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '6' : '5'; ?>">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Your total family savings is</td>
						<td align="right">$<?= $this->moneyFormat($this->orderInfo['family_savings_discount']) ?></td>
					</tr>
				<?php } ?>
				<?php if (isset($this->orderInfo['total_payments'])) { ?>
					<tr>
						<td colspan="<?php echo ($displayStationNumber) ? '5' : '4'; ?>" class="text-right">Total Payments</td>
						<td align="right">$<?= $this->moneyFormat($this->orderInfo['total_payments']) ?></td>
					</tr>
					<?php if ($this->orderInfo['total_payments'] > ($this->orderInfo['grand_total'] + .00001)) { ?>
						<tr>
							<td colspan="<?php echo ($displayStationNumber) ? '6' : '5'; ?>" align="right"><b>** OVERPAYMENT **</b></td>
						</tr>
					<?php } ?>
				<?php } ?>

				</tbody>
			</table>
		</div>
	</div>

	<?php if ($this->orderInfo['order_confirmation']) { // Show section only if order has been completed ?>
		<p class="p-1 mb-1 font-weight-bold border-top border-bottom font-size-medium-small">Payment Information</p>
		<table class="mb-2" style="width:100%;">
			<?php
			if (isset($this->paymentInfo))
			{
				if (isset($this->gift_card_credits))
				{
					foreach ($this->gift_card_credits as $credit)
					{
						?>
						<tr>
							<td style="width:150px;">Payment Type</td>
							<td>Debit Gift Card</td>
							<td class="text-right">Payment Total</td>
							<td class="text-right" style="width:70px;">-<?php echo $credit['gc_amount']; ?></td>
						</tr>
						<tr>
							<td>Gift Card Number</td>
							<td colspan="3"><?php echo $credit['gc_number']; ?></td>
						</tr>
						<?php
					}
				}

				$paymentCount = 0;
				foreach ($this->paymentInfo as $arrItem)
				{
					if (is_array($arrItem))
					{
						$paymentCount++;
						?>
						<tr>
							<td style="width:200px;">Payment Type</td>
							<td><?php echo $arrItem['payment_info']['other']; ?></td>
							<td class="text-right">Payment Total</td>
							<td class="text-right" style="width:70px;"><?php echo(($arrItem['payment_info']['other'] != 'Refund') ? "-" : ""); ?>
								$<?php echo (isset($arrItem['is_delayed_payment']) && ($arrItem['delayed_payment_status'] == 'CANCELLED' || $arrItem['delayed_payment_status'] == 'FAIL')) ? '<span style="text-decoration:line-through;">' . $arrItem['total']['other'] . '</span>' : $arrItem['total']['other']; ?></td>
						</tr>

						<?php if (isset($arrItem['gift_certificate_type'])) { ?>
						<tr>
							<td>Gift Certificate Type</td>
							<td colspan="3"><?php echo ucfirst(strtolower($arrItem['gift_certificate_type']['other'])); ?></td>
						</tr>
					<?php } ?>

						<?php if (isset($arrItem['gift_cert_id'])) { ?>
						<tr>
							<td>Gift Certificate Number</td>
							<td colspan="3"><?php echo ucfirst(strtolower($arrItem['gift_cert_id']['other'])); ?></td>
						</tr>
					<?php } ?>


						<?php if (isset($arrItem['is_delayed_payment'])) { ?>
						<tr>
							<td>Payment Status</td>
							<td colspan="3"><?php echo ucfirst(strtolower($arrItem['delayed_payment_status'])); ?></td>
						</tr>
					<?php } ?>

						<tr>
							<td>Payment Date</td>
							<td colspan="3"><?php echo (isset($arrItem['is_delayed_payment'])) ? $arrItem['delayed_date']['other'] : $arrItem['paymentDate']['other']; ?></td>
						</tr>
						<?php if (isset($arrItem['credit_card_type']['other'])) { ?>
						<tr>
							<td>Credit Card Number</td>
							<td colspan="3"><?php echo $arrItem['credit_card_type']['other']; ?><?php echo $arrItem['payment_number']['other']; ?></td>
						</tr>
					<?php } ?>

						<?php if (isset($arrItem['is_delayed_payment']) && $arrItem['delayed_payment_status'] == "SUCCESS") { ?>
						<tr>
							<td>Delayed Payment Transaction ID</td>
							<td colspan="3"><?php echo (!empty($arrItem['delayed_tran_num']['other'])) ? $arrItem['delayed_tran_num']['other'] : ''; ?></td>
						</tr>
					<?php } else if (isset($arrItem['payment_transaction_number']['other'])) { ?>
						<tr>
							<td>Payment Transaction ID</td>
							<td colspan="3"><?php echo $arrItem['payment_transaction_number']['other']; ?></td>
						</tr>
					<?php } ?>

						<?php if (isset($arrItem['payment_note']['other'])) { ?>
						<tr>
							<td>Payment Note</td>
							<td colspan="3"><?php echo $arrItem['payment_note']['other']; ?></td>
						</tr>
					<?php } ?>
						<?php if ($paymentCount != $this->paymentInfo['payment_count']) { ?>
						<tr>
							<td colspan="4">&nbsp;</td>
						</tr>
					<?php } ?>
					<?php } ?>
				<?php } ?>
			<?php } ?>
			<tr>
				<td colspan="4">
					<hr class="float-right w-25" size="1" noshade="noshade" />
				</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
				<td class="text-right font-weight-bold">Balance Due</td>
				<td class="text-right font-weight-bold">

					<?php
					if (isset($this->balanceDue))
					{
						if (is_numeric($this->balanceDue))
						{
							if ($this->balanceDue < 0)
							{
								?>
								<span style="color:blue;">-$<?php echo abs($this->moneyFormat($this->balanceDue)); ?></span>
								<?php
							}
							else if ($this->balanceDue > 0)
							{
								if (isset($this->isCancelled) && $this->isCancelled)
								{ ?>
									<span style="color:blue;">$<?php echo $this->moneyFormat($this->balanceDue); ?></span>
								<?php } else { ?>
									<span style="color:red;">$<?php echo $this->moneyFormat($this->balanceDue); ?></span>
									<?php
								}
							}
							else
							{
								?>
								<span style="color:green;">$<?php echo $this->moneyFormat($this->balanceDue); ?></span>
								<?php
							}
						}
						else
						{
							?>
							<?php echo $this->balanceDue; ?>
							<?php
						}
					}
					?>
				</td>
			</tr>
		</table>
	<?php } // end if order_confirmation ?>

	<?php if (isset($this->storeInfo)) { ?>
		<p class="p-1 mb-1 font-weight-bold border-top border-bottom font-size-medium-small">Session Details: <?= $this->dateTimeFormat($this->sessionInfo['session_start'], NORMAL) ?></p>
		<table style="width:100%;">
			<tbody>
			<tr>
				<td width="50%" valign="top">
					<b><?php echo $this->storeInfo['store_name']; ?></b><br />
					<?php echo $this->storeInfo['address_line1']; ?><br /><?php if (strlen($this->storeInfo['address_line2']))
					{
						echo $this->storeInfo['address_line2'] . '<br />';
					} ?>
					<?= $this->storeInfo['city'] ?>, <?= $this->storeInfo['state_id'] ?> <?= $this->storeInfo['postal_code'] ?><br />
					<a target="_map" href="<?= $this->storeInfo['map'] ?>"><img src="<?= ADMIN_IMAGES_PATH ?>/icon/map.png" width="16" height="16" style="vertical-align:middle;margin-bottom:.25em;">
						Map</a>
				</td>
				<td width="50%">
					<?php if (!empty($this->storeInfo['telephone_day'])) { ?>Phone (day): <?= $this->storeInfo['telephone_day'] ?><br /><?php } ?>
					<?php if (!empty($this->storeInfo['telephone_evening'])) { ?>Phone (evening): <?= $this->storeInfo['telephone_evening'] ?><br /><?php } ?>
					<?php if (!empty($this->storeInfo['fax'])) { ?>Phone (fax): <?= $this->storeInfo['fax'] ?><br /><?php } ?>
					Email: <a href="mailto:<?= $this->storeInfo['email_address'] ?>"><?= $this->storeInfo['email_address'] ?></a><br /><br />
				</td>
			</tr>
			</tbody>
		</table>
	<?php } // end if sessionInfo ?>

	<?php if (false && $customerView && isset($this->finishingTouchSuggestions) && !empty($this->finishingTouchSuggestions)) { ?>
		<p class="p-1 mb-1 font-weight-bold border-top border-bottom font-size-medium-small">Suggested Pairings</p>
		<table style="width:100%;">
			<tbody>
			<?php
			$countFTs = 0;
			foreach ($this->finishingTouchSuggestions as $id => $FTItem)
			{
				$countFTs++;
				if ($countFTs > 3)
				{
					break;
				}
				if ($FTItem['remaining_inventory'] > 0)
				{
					if ($FTItem['has_match'])
					{
						?>
						<tr>
							<td valign="top"><?php echo $FTItem['sideName']; ?>
							</td>
							<td style="width:70%">Complements the <?php echo $FTItem['matches'][0]['name']; ?>
							</td>
						</tr>
					<?php } else { ?>
						<tr>
							<td valign="top"><?php echo $FTItem['sideName']; ?>
							</td>
							<td>
							</td>
						</tr>

					<?php } } } ?>
			</tbody>
		</table>
	<?php } // end if sessionInfo ?>
</div>