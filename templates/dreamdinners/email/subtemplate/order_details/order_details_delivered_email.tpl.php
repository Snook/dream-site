<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%" align="left" valign="top" bgcolor="#f9fadc">
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="15">
				<tr>
					<td valign="top">
						<strong>Order Details</strong><br /><br />
						<strong>Date:</strong>
						<?php echo $this->dateTimeFormat( $this->sessionInfo['session_start'], FULL_MONTH_DAY_YEAR) ?>
						<br />
						<strong>Order Confirmation:</strong> <a href="<?php echo HTTPS_BASE ?><?php echo $this->details_page ?>?order=<?php echo $this->orderInfo['id'] ?>">
							<?php echo $this->orderInfo['order_confirmation'] ?>
						</a>
						<?php if (!empty($this->orderInfo['order_user_notes'])) { ?>
							<p>Your notes to the store:<br />
								<?php echo $this->orderInfo['order_user_notes']; ?>
							</p>
						<?php } ?>
					</td>
					<?php if (!empty($this->sessionInfo['session_type_subtype']) && $this->sessionInfo['session_type_subtype'] == CSession::DELIVERY) { ?>
						<td valign="top">
							<strong>Shipping Details</strong><br /><br />
							<p>
								<?php echo $this->orderInfo['orderAddress']['firstname']; ?> <?php echo $this->orderInfo['orderAddress']['lastname']; ?><br />
								<?php echo $this->orderInfo['orderAddress']['telephone_1']; ?>
							</p>
							<p>
								<?php echo $this->orderInfo['orderAddress']['address_line1']; ?><br />
								<?php if (!empty($this->orderInfo['orderAddress']['address_line2'])) { ?>
									<?php echo $this->orderInfo['orderAddress']['address_line2']; ?><br />
								<?php } ?>
								<?php echo $this->orderInfo['orderAddress']['city']; ?>, <?php echo $this->orderInfo['orderAddress']['state_id']; ?> <?php echo $this->orderInfo['orderAddress']['postal_code']; ?>
							</p>
							<?php if (!empty($this->orderInfo['orderAddress']['address_note'])) { ?>
								<p>Address Note: <?php echo $this->orderInfo['orderAddress']['address_note']; ?></p>
							<?php } ?>
						</td>
					<?php } ?>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="100%" align="right" valign="top" bgcolor="#f1f1f1">
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="15">
				<tr>
					<td>
						<?php if (!empty($this->membership) && $this->membership['status'] == CUser::MEMBERSHIP_STATUS_CURRENT) { ?>
							<!--Membership details-->
							<table role="presentation" width="100%" style="width:100%;">

								<tr>
									<td colspan="2" style="width: 100%; text-align:left;"><b>Meal Prep+ Membership</b></td>
								</tr>
								<tr>
									<td style="width: 70%; text-align:left;">
										This is <?php echo $this->membership['display_strings']['order_position']; ?> membership orders.<br />
										My last membership month is <?php echo $this->membership['display_strings']['completion_month']; ?>.<br />
										My membership savings to date is $<?php echo CTemplate::moneyFormat($this->membership['display_strings']['total_savings']); ?>.<br />
									</td>
									<td style="width: 30%; text-align:center;"><img src="<?php echo EMAIL_IMAGES_PATH?>/style/membership/meal-prep-plus-badge-119x119.png" style="width: 119px; height: 119px;" alt="Meal Prep+" /></td>
								</tr>
							</table>
						<?php } ?>

						<?php if (!empty($this->plate_points) && $this->plate_points['status'] == 'active') { ?>
							<!--PLATEPOINTS details-->
							<table role="presentation" width="100%" style="width:100%;">

								<tr>
									<td colspan="2" style="width: 100%; text-align:left;"><b>PlatePoints</b></td>
								</tr>
								<tr>
									<td style="width: 70%; text-align:left;">
										<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="2" style="width: 100%;">
											<tr align="center">
												<td colspan="2"></td>
											</tr>
											<?php if (!$this->isEmptyFloat($this->plate_points['points_this_order'])) { ?>
												<tr>
													<td>Points earned this order:</td>
													<td><?php echo number_format($this->plate_points['points_this_order']); ?></td>
												</tr>
											<?php } ?>
											<?php if (!empty($this->plate_points['available_credit'])) { ?>
												<tr>
													<td>Available Dinner Dollars:</td>
													<td>$<?php echo CTemplate::moneyFormat($this->plate_points['available_credit']); ?></td>
												</tr>
											<?php } ?>
											<?php if (!$this->isEmptyFloat($this->plate_points['next_expiring_credit_amount'])) { ?>
												<tr>
													<td>Next Dinner Dollar Expiration:</td>
													<td>$<?php echo number_format($this->plate_points['next_expiring_credit_amount']) ?> on <br /><?php echo $this->plate_points['next_expiring_credit_date'] ?></td>
												</tr>
											<?php } ?>

										</table>
									</td>
									<td style="width: 30%; text-align:center;"></td>
								</tr>
							</table>
						<?php } else { ?>
							<p><strong>Rate Your Meals</strong><br />
								Did you know rating your meals helps us decide on future menus? If you want to see your favorites back on the menu, make sure you are rating your meals each month.</p>
							<p><a href="<?php echo HTTPS_BASE ?>my-meals">Rate your meals now &gt;</a></p>

						<?php } ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>


<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="3"><hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
	</tr>
	<tr>
		<td colspan="3">
			<?php if (isset($this->show_GC_message) && $this->show_GC_message) { ?>
				<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="4" style="border: 1px solid #CACACA; ">
					<tr>
						<td>
							<img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/giftcards/dd_giftcard_new.gif" border="0" alt="" width="60" height="39" />
						</td>
						<td>
							Gift Card Purchased	(A separate email will be sent.)
						</td>
					</tr>
				</table>
			<?php } ?>
		</td>
	</tr>

	<?php if (!$this->isEmptyFloat($this->orderInfo['ltd_round_up_value'])) { ?>
		<tr>
			<td colspan="3">
				<div class="ltd_round_up" style="margin: 0 20px 10px 20px;">
					Thank you for your support of Dream Dinners Foundation's fight against hunger. Your donation of $<?php echo CTemplate::moneyFormat($this->orderInfo['ltd_round_up_value']); ?> has helped provide nutritious meals abroad.
				</div>
			</td>
		</tr>
	<?php } ?>

	<tr>
		<td colspan="3"><?php
			if( isset( $this->menuInfo ) ) {
				?>
				<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr bgcolor="#b9bf33">
						<td class="cart_ordered" style="padding: 5px;" align="center" width="40">Qty</td>
						<td class="cart_ordered" style="padding: 5px;" align="center" width="70">Size</td>
						<td class="cart_ordered" style="padding: 5px;" width="200">Menu Item</td>
					</tr>
					<?php

					if (!empty($this->menuInfo['current_boxes']))
					{
						foreach ($this->menuInfo['current_boxes'] as $box_inst_id => $boxContents)
						{

							echo '<tr><td colspan="2" class="font-weight-bold text-white-space-nowrap">';
							echo $boxContents['box_label'] . ' - ' . $boxContents['bundle_data']->bundle_name;
							echo '</td><td>&nbsp;&nbsp;&nbsp;$' . $boxContents['bundle_data']->price . '</td></tr>';
							foreach ($boxContents['bundle_items'] as $id => $itemData)
							{
								if ($itemData['qty'] > 0)
								{?>
									<tr>
										<td class="customersData" style="padding: 5px;" align="center" valign="top">
											<?php
											if ($boxContents['box_type'] == CBox::DELIVERED_FIXED)
											{
												echo '1';
											}
											else
											{
												echo $itemData['qty'];
											}
											?>
										</td>
										<td class="customersData" style="padding: 5px;" align="center" valign="top">
											<?php //echo ($itemData['pricing_type'] == 'FULL' ? 'Large' : 'Medium');?>
										</td>
										<td class="customersData" style="padding: 5px;" align="center" valign="top">
											<?php echo $itemData['display_title'];?>
										</td>
									</tr>
									<?php
								}
							}
						}
					}
					?>
				</table>

			<?php } ?>
		</td>
	</tr>

	<tr>
		<td colspan="3"><hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
	</tr>
	<tr>
		<td colspan="2" width="325px" style="vertical-align:top; ">
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
				<?php

				if ( isset( $this->paymentInfo ) ) {
					echo '<tr><td class="sectionhead" colspan="3"><b>Payment Information</b></td></tr>';
					$counter = 0;
					foreach( $this->paymentInfo as $arrItem ) {
						if( is_array( $arrItem ) ) {
							echo "<tr>";
							if( $arrItem['payment_type'] === CPayment::CC )
							{
								$isDeposit = isset( $arrItem['deposit'] ) ? '<i>(Status: Deposit Processed)</i>&nbsp;' : '' ;
								$isDelayed = isset( $arrItem['delayed_status'] ) ? '<i>(' . $arrItem['delayed_status']['other'] . ')</i>&nbsp;' : '';
								echo '<td><b>'. $arrItem['credit_card_type']['other'] . '</b>&nbsp;&nbsp;' . $isDeposit . $isDelayed . '<br />';
								echo 'Last 4 digits:&nbsp;' . substr( $arrItem['payment_number']['other'], strlen( $arrItem['payment_number']['other'] ) - 4, strlen( $arrItem['payment_number']['other'] ) ) . '<br />';
							}
							else if( $arrItem['payment_type'] === CPayment::GIFT_CARD )
							{
								echo '<b>Dream Dinners Debit Gift Card</b><br />';
								echo 'Last 4 digits:&nbsp;' . substr( $arrItem['payment_number']['other'], strlen($arrItem['payment_number']['other'])-4, strlen($arrItem['payment_number']['other'])) . '<br />';
							}
							else if( $arrItem['payment_type'] === CPayment::STORE_CREDIT )
							{
								if (isset($arrItem['payment_number']))
									echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />Last 4 digits:&nbsp;' . substr( $arrItem['payment_number']['other'], strlen( $arrItem['payment_number']['other'] ) - 4, strlen( $arrItem['payment_number']['other'] ) ) . '<br />';
								else
									echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />';
							}
							else if( $arrItem['payment_type'] === CPayment::GIFT_CERT )
							{
								echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />Gift Certificate Type:&nbsp;' . $arrItem['gift_certificate_type']['other'] . '<br />';
							}
							else
							{
								echo '<td><b>' . $arrItem['payment_info']['other']. '</b><br />';
							}

							if (isset($arrItem['delayed_date']))
								echo 'Date:&nbsp;' . $arrItem['delayed_date']['other'] . '<br />';
							else
								echo 'Date:&nbsp;' . $arrItem['paymentDate']['other'] . '<br />';

							echo 'Amount: $' . $arrItem['total']['other'] . '</td>';
							echo '</tr>';
							if (isset($arrItem['payment_note']) && isset($arrItem['payment_note']['other']) && !empty($arrItem['payment_note']['other']))
								echo '<tr><td colspan="3">Note:&nbsp;' . $arrItem['payment_note']['other'] . '</td></tr>';
							$counter++;
						}

					}
				}
				?>

			</table></td>
		<td width="325px" style="vertical-align:top;">
			<table role="presentation" border="0" width="100%">
				<tr>
					<td align="right">Total Item Count:</td>
					<td align="right"><?php echo $this->orderInfo['menu_items_total_count'] ?></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td align="right"><?php echo $this->isEmptyFloat( $this->orderInfo['family_savings_discount'] ) ? 'Item Subtotal:' : 'Discounted Item Subtotal:' ?></td>
					<td align="right">$<?php echo $this->moneyFormat( $this->orderInfo['subtotal_menu_items'] + $this->orderInfo['subtotal_products'] + $this->orderInfo['subtotal_home_store_markup'] -
							( $this->isEmptyFloat( $this->orderInfo['family_savings_discount'] ) ? 0 : $this->orderInfo['family_savings_discount'] ) -
							( $this->isEmptyFloat( $this->orderInfo['bundle_discount'] ) ? 0 : $this->orderInfo['bundle_discount'] ) -
							( $this->isEmptyFloat( $this->orderInfo['subtotal_menu_item_mark_down'] ) ? 0 : $this->orderInfo['subtotal_menu_item_mark_down'] )); ?></td>
					<td>&nbsp;</td>
				</tr>

				<?php if( !$this->isEmptyFloat($this->orderInfo['misc_food_subtotal']) ) { ?>
					<tr>
						<td align="right">Misc Food ( <?php echo $this->orderInfo['misc_food_subtotal_desc']?> ):</td>
						<td align="right">$<?php echo $this->moneyFormat($this->orderInfo['misc_food_subtotal']) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat($this->orderInfo['misc_nonfood_subtotal']) ) { ?>
					<tr>
						<td align="right">Misc Non-Food ( <?php echo $this->orderInfo['misc_nonfood_subtotal_desc']?> ):</td>
						<td align="right">$<?php echo $this->moneyFormat($this->orderInfo['misc_nonfood_subtotal']) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>
				<?php if( !$this->isEmptyFloat( $this->orderInfo['volume_discount_total'] ) ) { ?>
					<tr>
						<td align="right">Volume Reward:</td>
						<td align="right">-<?php echo $this->moneyFormat($this->orderInfo['volume_discount_total']) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>
				<?php if( !$this->isEmptyFloat( $this->orderInfo['points_discount_total'] ) ) { ?>
					<tr>
						<td align="right">PlatePoints Dinner Dollars:</td>
						<td align="right">-<?php echo $this->moneyFormat($this->orderInfo['points_discount_total']) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['user_preferred_discount_total'] ) ) { ?>
					<tr>
						<td align="right">Preferred Discount:</td>
						<td align="right">-<?php echo $this->moneyFormat( $this->orderInfo['user_preferred_discount_total'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['dream_rewards_discount'] ) ) { ?>
					<tr>
						<td align="right">Dream Rewards Discount:</td>
						<td align="right">-<?php echo $this->moneyFormat( $this->orderInfo['dream_rewards_discount'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['direct_order_discount'] ) ) { ?>
					<tr>
						<td align="right">Direct Order Discount:</td>
						<td align="right">-<?php echo $this->moneyFormat( $this->orderInfo['direct_order_discount'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['membership_discount'] ) ) { ?>
					<tr>
						<td align="right">Meal Prep+ Discount:</td>
						<td align="right">-<?php echo $this->moneyFormat( $this->orderInfo['membership_discount'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['promo_code_discount_total'] ) ) { ?>
					<tr>
						<td align="right">Promotional Code Discount:</td>
						<td align="right">-<?php echo $this->moneyFormat( $this->orderInfo['promo_code_discount_total'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['coupon_code_discount_total'] ) ) { ?>
					<tr>
						<td align="right">Promo Code Discount (<?php echo $this->orderInfo['coupon_title']?>):</td>
						<td align="right">-<?php echo $this->moneyFormat( $this->orderInfo['coupon_code_discount_total'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_service_fee'] ) || $this->orderInfo['service_fee_description'] == "Free Assembly Promo" ) { ?>
					<tr>
						<td align="right">Service Fees:</td>
						<td align="right"><?php echo $this->moneyFormat( $this->orderInfo['subtotal_service_fee'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_delivery_fee'] )) { ?>
					<tr>
						<td align="right">Delivery Fee:</td>
						<td align="right"><?php echo $this->moneyFormat( $this->orderInfo['subtotal_delivery_fee'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['delivery_tip'] )) { ?>
					<tr>
						<td align="right">Driver Tip:</td>
						<td align="right"><?php echo $this->moneyFormat( $this->orderInfo['delivery_tip'] ) ?></td>
						<td> </td>
					</tr>
				<?php } ?>

				<tr>
					<td align="right">Food Tax:</td>
					<td align="right"><?php echo $this->moneyFormat( $this->orderInfo['subtotal_food_sales_taxes'] ) ?></td>
					<td>&nbsp;</td>
				</tr>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_service_tax'] ) ) { ?>
					<tr>
						<td align="right">Service Tax:</td>
						<td align="right"><?php echo $this->moneyFormat( $this->orderInfo['subtotal_service_tax'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_delivery_tax'] ) ) { ?>
					<tr>
						<td align="right">Delivery Fee Tax:</td>
						<td align="right"><?php echo $this->moneyFormat( $this->orderInfo['subtotal_delivery_tax'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_sales_taxes'] ) ) { ?>
					<tr>
						<td align="right">Non-Food Tax:</td>
						<td align="right"><?php echo $this->moneyFormat( $this->orderInfo['subtotal_sales_taxes'] ) ?></td>
						<td>&nbsp;</td>
					</tr>
				<?php } ?>

				<tr>
					<td align="right"><b>Menu Order Total:</b></td>
					<td align="right"><b>$<?php echo $this->moneyFormat($this->orderInfo['grand_total']) ?></b></td>
					<td>&nbsp;</td>
				</tr>

				<?php if($this->menuInfo['cost_per_serving'] > 0){ ?>
				<tr>
					<td align="right">Average Cost Per Serving:</td>
					<td align="right"><?php echo $this->menuInfo['cost_per_serving']; ?></td>
					<td>&nbsp;</td>
				</tr>
				<?php } ?>

			</table>
		</td>
	</tr>
	<tr>
		<td colspan="3" bgcolor="#5c6670" style="padding: 2px;"><p align="center">&nbsp;</p></td>
	</tr>
	<tr>
		<td colspan="3" align="left" style="padding: 15px;"><p><b>Reschedule and Cancellation Policy</b><br />
				If you need to reschedule or cancel your order, please contact us 6 days prior to your session. Cancellations with 6 or more days' notice will receive a full refund. Cancellations within 4-5 days' notice will be subject to a 25% restocking fee. Cancellations cannot be made in 3 or less days as your items have been prepped to ship or will be in transit.</p>
				<p><b>Allergens</b><br /> In compliance with the "Food Allergen Labeling and Consumer Protection Act of 2004" please note that Dream Dinners' facilities may contain Dairy, Eggs, Crustacean Shellfish, Fish, Tree Nuts, Peanuts, Wheat, Soybeans and Sesame which account for most known allergens. Although Dream Dinners' store staff take appropriate safety measures, guests should be aware that cross contamination can occur among food products in store kitchens and at stations. The standard ingredients are available upon request from your local store; however, ingredient substitutions can be made at the store level due to regional availability. If guests feel that there may be a chance of allergens in any recipe, especially due to pre-made ingredients, they need to call the store to ask for specific nutritional information.</p>
				<p><b>Policies and Terms</b><br />
				By participating in the Dream Dinners program, you agree to the <a href="<?php echo HTTPS_SERVER; ?>/terms">Policy and Terms</a>.</p>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
	</tr>
		<tr>
		<td colspan="3" align="center"><p align="center">If you have questions please contact Dream Dinners <?php echo $this->sessionInfo['store_name']?> at
				<?php echo $this->sessionInfo['telephone_day'] ?> or via email by replying.</p>
		</td>
	</tr>

</table>