<table role="presentation" width="650" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="3"><hr /></td>
	</tr>
	<tr>
		<td width="315" align="left" valign="top"><strong>Visit Details</strong><br /><br />
			<strong>Time:</strong>
			<?= $this->dateTimeFormat( $this->sessionInfo['session_start'], VERBOSE) ?>
			<br />
			<strong> Location:</strong>
			<?= $this->sessionInfo['store_name'] ?><br />
			<strong>Order Confirmation:</strong> <a href="<?= HTTPS_BASE ?><?= $this->details_page ?>?order=<?= $this->orderInfo['id'] ?>"><b>
					<?= $this->orderInfo['order_confirmation'] ?>
				</b></a><br /><br />
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
				<?php

				if ( isset( $this->paymentInfo ) ) {
					echo '<tr><td align="left"><b>Payment Details</b><br /></td></tr>';
					$counter = 0;
					foreach( $this->paymentInfo as $arrItem ) {
						if( is_array( $arrItem ) ) {
							echo "<tr>";
							if( $arrItem['payment_type'] === CPayment::CC ) {
								$isDeposit = isset( $arrItem['deposit'] ) ? '<i>(Status: Deposit Processed)</i>&nbsp;' : '' ;
								$isDelayed = isset( $arrItem['delayed_status'] ) ? '<i>(' . $arrItem['delayed_status']['other'] . ')</i>&nbsp;' : '';
								echo '<td><b>'. $arrItem['credit_card_type']['other'] . '</b>&nbsp;&nbsp;' . $isDeposit . $isDelayed . '<br />';
								echo 'Last 4 digits:&nbsp;' . substr( $arrItem['payment_number']['other'], strlen( $arrItem['payment_number']['other'] ) - 4, strlen( $arrItem['payment_number']['other'] ) ) . '<br />';
							} else if( $arrItem['payment_type'] === CPayment::GIFT_CARD ) {
								echo 'Card Type:&nbsp;Dream Dinners Debit Gift Card<br />';
								echo 'Last 4 digits:&nbsp;' . substr( $arrItem['payment_number']['other'], strlen($arrItem['payment_number']['other'])-4, strlen($arrItem['payment_number']['other'])) . '<br />';
							} else if( $arrItem['payment_type'] === CPayment::STORE_CREDIT ) {
								if (isset($arrItem['payment_number']))
									echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />Last 4 digits:&nbsp;' . substr( $arrItem['payment_number']['other'], strlen( $arrItem['payment_number']['other'] ) - 4, strlen( $arrItem['payment_number']['other'] ) ) . '<br />';
								else
									echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />';
							} else if( $arrItem['payment_type'] === CPayment::GIFT_CERT ) {
								echo '<td><b>' . $arrItem['payment_info']['other'] . '</b><br />Gift Type:&nbsp;' . $arrItem['gift_cert_type']['other'] . '<br />';
							} else {
								echo '<td><b>' . $arrItem['payment_info']['other']. '</b><br />';
							}

							if (isset($arrItem['delayed_date']))
								echo 'Payment Date:&nbsp;' . $arrItem['delayed_date']['other'] . '</td>';
							else
								echo 'Payment Date:&nbsp;' . $arrItem['paymentDate']['other'] . '</td>';

							echo '<td align="right" valign="bottom">$' . $arrItem['total']['other'] . '</td>';
							echo '<td>&nbsp;</td>';
							echo '</tr>';
							if (isset($arrItem['payment_note']) && isset($arrItem['payment_note']['other']) && !empty($arrItem['payment_note']['other']))
								echo '<tr><td colspan="3">Payment Note:&nbsp;' . $arrItem['payment_note']['other'] . '</td></tr>';
							$counter++;
						}

					}
				}
				?>
			</table>
		</td>
		<td width="16" rowspan="2"><img src="<?php echo EMAIL_IMAGES_PATH; ?>/email/style/vert-dotted-line.png" border="0" alt="" width="16" height="231" /></td>
		<td width="289" rowspan="2" align="right" valign="top"><table role="presentation" width="100%" border="0">
				<tr>
					<td colspan="2" align="right"><strong>Order Details</strong></td>
				</tr>
				<tr>
					<td align="right">Total Item Count</td>
					<td align="right"><?= $this->orderInfo['menu_items_total_count'] ?></td>
				</tr>
				<?php if( !$this->isEmptyFloat($this->orderInfo['misc_food_subtotal']) ) { ?>
					<tr>
						<td align="right">Misc Food (
							<?=$this->orderInfo['misc_food_subtotal_desc']?>
										  )</td>
						<td align="right">$
							<?= $this->moneyFormat($this->orderInfo['misc_food_subtotal']) ?></td>
					</tr>
				<?php } ?>
				<?php if( !$this->isEmptyFloat($this->orderInfo['misc_nonfood_subtotal']) ) { ?>
					<tr>
						<td align="right">Misc Non-Food (
							<?=$this->orderInfo['misc_nonfood_subtotal_desc']?>
										  )</td>
						<td align="right">$
							<?= $this->moneyFormat($this->orderInfo['misc_nonfood_subtotal']) ?></td>
					</tr>

				<?php } ?>
				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_service_fee'] ) || $this->orderInfo['service_fee_description'] == "Free Assembly Promo" ) { ?>
					<tr>
						<td align="right">Service Fees</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_service_fee'] ) ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td align="right">Food Tax</td>
					<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_food_sales_taxes'] ) ?></td>
				</tr>
				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_service_tax'] ) ) { ?>
					<tr>
						<td align="right">Service Tax</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_service_tax'] ) ?></td>
					</tr>
				<?php } ?>
				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_delivery_tax'] ) ) { ?>
					<tr>
						<td align="right">Delivery Fee Tax</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_delivery_tax'] ) ?></td>
					</tr>
				<?php } ?>
				<?php if( !$this->isEmptyFloat( $this->orderInfo['subtotal_sales_taxes'] ) ) { ?>
					<tr>
						<td align="right">Non-Food Tax</td>
						<td align="right"><?= $this->moneyFormat( $this->orderInfo['subtotal_sales_taxes'] ) ?></td>
					</tr>
				<?php } ?>
				<?php
				if (empty($this->orderInfo['bundle_id'])) {
					// older olders
					if (isset($this->orderInfo['average_per_serving_cost']) && !$this->isEmptyFloat($this->orderInfo['average_per_serving_cost']) ) { ?>
						<tr>
							<td align="right">Average Cost Per Serving</td>
							<td align="right"><?= $this->moneyFormat($this->orderInfo['average_per_serving_cost']) ?></td>
						</tr>
					<?php } else if (!empty($this->orderInfo['servings_total_count'])) {
						$basisAdjustment = COrders::getBasisAdjustment($this->menuInfo);
						?>
					<?php } } ?>
				<tr>
					<td align="right"><b>Menu Order Total</b></td>
					<td align="right"><b>$<?= $this->moneyFormat($this->orderInfo['grand_total']) ?></b></td>
				</tr>
				<?php if (!$this->isEmptyFloat($this->orderInfo['ltd_round_up_value'])) { ?>
					<tr>
						<td align="right">Donation Total:</td>
						<td align="right">$<?php echo CTemplate::moneyFormat($this->orderInfo['ltd_round_up_value']); ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td colspan="2" align="right"><p>Your Special Instructions:		<br />
							<?php
							if( $this->orderInfo['order_user_notes'] != NULL ) {
								echo $this->orderInfo['order_user_notes'];
							} else {
								echo "none";
							}
							?></p></td>
				</tr>
			</table></td>
	</tr>
	<tr>
		<td align="left" valign="top">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3"><hr /></td>
	</tr>
	<tr>
		<td colspan="3"><?php
			if( isset( $this->menuInfo ) ) {
				?>
				<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr bgcolor="#E8E8E8">
						<td class="cart_ordered" style="padding: 5px;" align="center" width="40">Qty</td>
						<td class="cart_ordered" style="padding: 5px;" align="center" width="70">Size</td>
						<td class="cart_ordered" style="padding: 5px;" width="200">Menu Items</td>
						<td class="cart_ordered" style="padding: 5px;" align="center" width="50"><?= $this->isEmptyFloat( $this->orderInfo['family_savings_discount'] ) ? 'Item Price' : 'Family Savings Price' ?></td>
						<td style="width: 25px;">&nbsp;</td>
						<td class="cart_ordered" style="padding: 5px;" align="center" width="50">Total Price</td>
					</tr>
					<?php
					foreach ($this->menuInfo as $categoryName => $subArray) {

						//$showedCategory = false;
						if (is_array($subArray))
							foreach ( $subArray as $id => $item ) { if ( is_numeric($id) && $item['qty'] ) {

								/*
								if (!$showedCategory)
								{
									$showedCategory = true;
									echo '<tr><td>' . $categoryName . '</td></tr>';
								}
								*/
								?>
								<tr>
									<td class="customersData" style="padding: 5px;" align="center" valign="top"><?= $item['qty'] ?></td>
									<?php if (isset($item['servings_per_item'])) { ?>
										<td class="customersData" style="padding: 5px;" align="center" valign="top" ><?=((!empty($item['is_side_dish']) || !empty($item['is_kids_choice'])
												|| !empty($item['is_menu_addon']) || !empty($item['is_chef_touched']) || (isset($item['servings_per_item']) && $item['servings_per_item'] == 0)) ? "" : $item['servings_per_item'] . "-serving")?></td>
									<?php } else { ?>
										<td class="customersData" style="padding: 5px;" align="center" valign="top"><?=!empty($item['is_side_dish']) ? "" : ($item['pricing_type'] == 'HALF' ? 'Medium' : 'Large');?></td>
									<?php } ?>
									<td class="customersData" style="padding: 5px;" valign="top"><?= $item['display_title'] ?></td>
									<td class="customersData" style="padding: 5px;" align="right" valign="top">
										<?php
										$itemPrice = 0.0;
										$numItemServings = isset($item['servings_per_item']) ? isset($item['servings_per_item']) : $item['pricing_type']=="HALF" ? 3 : 6;

										if( !$this->isEmptyFloat( $this->orderInfo['family_savings_discount'] ) ) {
											if( isset( $item['discounted_price'] ) ) {

												$itemPrice = $this->moneyFormat( round( $item['discounted_price'], 2 ) );
											} else {


												$itemPrice = $this->moneyFormat( COrders::getItemDiscountedPrice( $item['price'],
													$this->orderInfo['number_servings'], $numItemServings, $this->menuInfo['markup_discount_scalar'] ) );
											}
										} else {
											if( isset( $this->menuInfo['items_total_count'] ) ) {
												//echo '$', $this->menuInfo['items_total_count'] < 12 ? $this->moneyFormat( $item['premium_price'] ) : $this->moneyFormat( $item['price'] );
												$itemPrice = $this->menuInfo['items_total_count'] < 12 ? $this->moneyFormat( $item['premium_price'] ) : $this->moneyFormat( $item['price'] );
											} else {
												//echo '$', $this->moneyFormat( $item['price'] );
												$itemPrice = $this->moneyFormat( $item['price'] );
											}
										}
										echo '$', $itemPrice;
										?>
									</td>
									<td style="width: 25px;">&nbsp;</td>
									<td align="right" valign="top" style="padding: 5px;" >$<?= $this->moneyFormat( $item['qty'] * $itemPrice ) ?></td>
								</tr>
								<?php
							} }
					}
					if( isset( $this->menuInfo['promo_item'] ) ) {
						$numPromoItemServings = isset($this->menuInfo['promo_item']['servings_per_item']) ? $this->menuInfo['promo_item']['servings_per_item'] : ($this->menuInfo['promo_item']['pricing_type']=="HALF" ? 3 : 6);
						?>
						<tr>
							<td class="customersData" style="padding: 5px;" align="center">1</td>
							<td class="customersData" style="padding: 5px;" align="center"><?=$numPromoItemServings == 3 ? "3 servings" : "6 servings";?></td>
							<td width="1">&nbsp;</td>
							<td class="customersData" style="padding: 5px;"><?=$this->menuInfo['promo_item']['display_title']?> (Promotion)</td>
							<td width="1">&nbsp;</td>
							<td class="customersData" style="padding: 5px;" align="right"> $<?php echo $this->moneyFormat($this->menuInfo['promo_item']['price']); ?></td>
							<td width="1">&nbsp;</td>
						</tr>
					<?php } ?>
					<?php if( isset( $this->menuInfo['free_meal_item'] ) ) {
						$numFreeMealItemServings = isset($this->menuInfo['free_meal_item']['servings_per_item']) ? $this->menuInfo['free_meal_item']['servings_per_item'] : ($this->menuInfo['free_meal_item']['pricing_type']=="HALF" ? 3 : 6);
						?>
						<tr>
							<td class="customersData" style="padding: 5px;" align="center">1</td>
							<td class="customersData" style="padding: 5px;" align="center"><?=$numFreeMealItemServings == 3 ? "3 servings" : "6 servings";?></td>
							<td width="1">&nbsp;</td>
							<td class="customersData" style="padding: 5px;"><?=$this->menuInfo['free_meal_item']['display_title']?> (Coupon - Free Item)</td>
							<td width="1">&nbsp;</td>
							<td class="customersData" style="padding: 5px;" align="right"> $<?php echo $this->moneyFormat($this->menuInfo['free_meal_item']['price']); ?></td>
							<td width="1">&nbsp;</td>
						</tr>
					<?php } ?>
				</table>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td colspan="3" bgcolor="#5c6670" style="padding: 5px;"><p align="center"><span style="color:#FFF;">Thank you!</span></p></td>
	</tr>
	<tr>
		<td colspan="3" align="left" style="padding: 15px;"><p><b>Reschedule and Cancellation Policy</b><br />
				If you need to reschedule or cancel your order, please contact us 6 days prior to your session. Cancellations with 6 or more days' notice will receive a full refund. Cancellations with 5 or fewer days' notice will be subject to a 25% restocking fee.</p>
				<p><b>Allergens</b><br /> In compliance with the "Food Allergen Labeling and Consumer Protection Act of 2004" please note that Dream Dinners' facilities may contain Dairy, Eggs, Crustacean Shellfish, Fish, Tree Nuts, Peanuts, Wheat, Soybeans and Sesame which account for most known allergens. Although Dream Dinners' store staff take appropriate safety measures, guests should be aware that cross contamination can occur among food products in store kitchens and at stations. The standard ingredients are available upon request from your local store; however, ingredient substitutions can be made at the store level due to regional availability. If guests feel that there may be a chance of allergens in any recipe, especially due to pre-made ingredients, they need to call the store to ask for specific nutritional information.</p>
				<p><b>Policies & Terms</b><br />
				By participating in the Dream Dinners program, you agree to the <a href="https://dreamdinners.com/terms">Policy and Terms</a>.</p>
			<hr width="100%" size="1" noshade color="#666666" style="color: #666; height:1px; border: 0;"></td>
	</tr>
	<tr>
		<td colspan="3" align="center"><p align="center">If you have questions please contact us at
				<?= $this->sessionInfo['telephone_day'] ?> or via email by replying.<br />
														 Dream Dinners <?=$this->sessionInfo['store_name']?><br /><?= $this->sessionInfo['address_line1'] ?>, <?= !empty( $this->sessionInfo['address_line2'] ) ? $this->sessionInfo['address_line2'] . '<br />' : '' ?> <?= $this->sessionInfo['city'] ?>&nbsp;<?= $this->sessionInfo['state_id'] ?>&nbsp;<?= $this->sessionInfo['postal_code'] ?><br />
				<a href="<?=$this->sessionInfo['map']?>">Get Directions</a></p></td>
	</tr>
</table>
<p></p>