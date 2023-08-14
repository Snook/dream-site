		<td width="50%" rowspan="2" valign="top" >

			<table border="0" width="100%" height="100%" cellspacing="0" cellpadding="2"  class="form_field_cell" style="border:2px #808080 solid;">
				<tr>
					<td width="100%" align="center" class ="form_subtitle_cell" style="background-color:#bcbcbc" colspan="7"><b>Order Status Comparison</b></td>
				</tr>
				<tr class ="form_subtitle_cell">
					<td colspan="2">
						<?php CForm::formElement(array(CForm::type => CForm::CheckBox, CForm::name => 'showall', CForm::onClick => 'handleSummaryDisplayChange', CForm::label => 'Show All Line Items')); ?>
					</td>
					<td colspan="2" style="color:blue;"><b>New</b></td>
					<td></td>
					<td colspan="2"><b>Current</b></td>
				</tr>
				<tr>
					<td><span id="OEH_item_count_label">Total Item Count</span></td>
					<td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_item_count" style="color:blue;"><?= $this->orderInfo['menu_items_total_count'] ?></span></td>
					<td width="5"></td>
					<td></td>
					<td align="right"><?= $this->orderInfo['menu_items_total_count'] ?></td>
				</tr>
                <?php if ($this->PlatePointsRulesVersion > 1) { ?>
                <tr>
                    <td><?= $this->order_minimum_header_label?></td>
                    <td align="left" >&nbsp;&nbsp;</td>
                    <td align="right"><span id="OEH_number_core_servings" style="color:blue;"><?php echo $this->requiredMinimumType == COrderMinimum::SERVING ? $this->orderInfo['servings_core_total_count'] :  $this->orderInfo['menu_items_core_total_count']; ?></span>/<span id="OEH_required_servings"><?php echo $this->requiredMinimum ?></span></td>
                    <td width="5"></td>
                    <td></td>
                    <td align="right"><?php echo $this->requiredMinimumType == COrderMinimum::SERVING ? $this->orderInfo['servings_core_total_count'] :  $this->orderInfo['menu_items_core_total_count']; ?>/<?php echo $this->requiredMinimum ?></td>
                </tr>
                <?php } ?>
                <tr>
					<td>Total Number of Servings</td>
					<td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_number_servings" style="color:blue;"><?= $this->orderInfo['servings_total_count'] ?></span></td>
					<td width="5"></td>
					<td></td>
					<td align="right"><?= $this->orderInfo['servings_total_count'] ?></td>
				</tr>
				<tr >
					<td><?=$this->isEmptyFloat($this->orderInfo['family_savings_discount']) ? "Menu Items Cost" : "Discounted Item Subtotal"?></td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right">
				<span id="OEH_menu_subtotal" style="color:blue;">
					<?= $this->moneyFormat($this->orderInfo['subtotal_menu_items'] + $this->orderInfo['subtotal_home_store_markup'] - $this->orderInfo['subtotal_menu_item_mark_down'] -
						($this->isEmptyFloat($this->orderInfo['family_savings_discount']) ? 0 : $this->orderInfo['family_savings_discount']) -
						($this->isEmptyFloat($this->orderInfo['bundle_discount']) ? 0 : $this->orderInfo['bundle_discount'])); ?>
				</span>
					</td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td><td align="right"><?php //changed to include markup 12/23/05 ?>
						<?= $this->moneyFormat($this->orderInfo['subtotal_menu_items'] + $this->orderInfo['subtotal_home_store_markup'] - $this->orderInfo['subtotal_menu_item_mark_down'] -
							($this->isEmptyFloat($this->orderInfo['family_savings_discount']) ? 0 : $this->orderInfo['family_savings_discount']) -
							($this->isEmptyFloat($this->orderInfo['bundle_discount']) ? 0 : $this->orderInfo['bundle_discount'])); ?>
					</td>
				</tr>

				<?php
				$miscFoodSubtotal = $this->isEmptyFloat($this->orderInfo['misc_food_subtotal']) ? 0 : $this->orderInfo['misc_food_subtotal'];
				$miscFoodSubtotal = $this->moneyFormat($miscFoodSubtotal);
				?>

				<tr id="miscFoodRow">
					<td>Misc Food Cost</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_misc_food_subtotal" style="color:blue;"><?=$miscFoodSubtotal?></span></td>
					<td width="5"></td>
					<td align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_misc_food_subtotal_org"><?=$miscFoodSubtotal?></span></td>
				</tr>

				<?php $foodTotal = $this->moneyFormat($this->orderInfo['misc_food_subtotal'] + $this->orderInfo['subtotal_menu_items']+ $this->orderInfo['subtotal_home_store_markup'] -  $this->orderInfo['subtotal_menu_item_mark_down'] - ($this->isEmptyFloat($this->orderInfo['bundle_discount']) ? 0 : $this->orderInfo['bundle_discount'])); ?>

				<tr>
					<td>Food Items Subtotal</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_food_cost_subtotal" style="color:blue;"><?=$foodTotal?></span></td>
					<td width="5"></td>
					<td align="right">$&nbsp;</td>
					<td align="right"><?=$foodTotal?></td>
				</tr>

				<?php
				$miscNonFoodSubtotal = $this->isEmptyFloat($this->orderInfo['misc_nonfood_subtotal']) ? 0 : $this->orderInfo['misc_nonfood_subtotal'];
				$miscNonFoodSubtotal = $this->moneyFormat($miscNonFoodSubtotal);
				?>

				<tr id="productSubtotalRow">
					<td>Enrollments</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_products_subtotal" style="color:blue;"><?=$this->moneyFormat($this->orderInfo['subtotal_products'] - $this->orderInfo['misc_nonfood_subtotal'])?></span></td>
					<td width="5"></td>
					<td align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_products_subtotal_org"><?=$this->moneyFormat($this->orderInfo['subtotal_products'] - $this->orderInfo['misc_nonfood_subtotal'])?></span></td>
				</tr>

				<tr id="miscNonFoodRow">
					<td>Misc Non-Food Cost</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_misc_nonfood_subtotal" style="color:blue;"><?=$miscNonFoodSubtotal?></span></td>
					<td width="5"></td>
					<td align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_misc_nonfood_subtotal_org"><?=$miscNonFoodSubtotal?></span></td>
				</tr>
				<!--
		<tr >
			<td><?=$this->isEmptyFloat($this->orderInfo['family_savings_discount']) ? "Item Subtotal" : "Discounted Item Subtotal"?></td>
			<td  align="right" style="color:blue;">$&nbsp;</td>
			<td align="right">
				<span id="OEH_menu_subtotal" style="color:blue;">
					<?= $this->moneyFormat($this->orderInfo['subtotal_menu_items'] + $this->orderInfo['subtotal_home_store_markup'] -
					($this->isEmptyFloat($this->orderInfo['family_savings_discount']) ? 0 : $this->orderInfo['family_savings_discount'])); ?>
				</span>
			</td>
			<td width="5"></td>
			<td  align="right">$&nbsp;</td><td align="right"><?php //changed to include markup 12/23/05 ?>
				<?= $this->moneyFormat($this->orderInfo['subtotal_menu_items'] + $this->orderInfo['subtotal_home_store_markup'] -
					($this->isEmptyFloat($this->orderInfo['family_savings_discount']) ? 0 : $this->orderInfo['family_savings_discount'])); ?>
			</td>
		</tr>
-->

				<tr id="preferredUserDiscountRow">
					<td>Preferred Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_preferred" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['user_preferred_discount_total']) ?></span></td>
					<td width="5"></td>
					<td  align="right">-$&nbsp;</td>
					<td align="right"><span id="OEH_preferred_org"><?= $this->moneyFormat($this->orderInfo['user_preferred_discount_total']) ?></span></td>
				</tr>

				<tr id="directDiscountRow">
					<td>Direct Order Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_direct_order_discount" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['direct_order_discount']) ?></span></td>
					<td width="5"></td>
					<td  align="right">-$&nbsp;</td><td align="right"><span id="OEH_direct_order_discount_org"><?= $this->moneyFormat($this->orderInfo['direct_order_discount']) ?></span></td>
				</tr>


				<tr id="platePointsDiscountRow">
					<td>PLATEPOINTS Discount (Food)</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_plate_points_order_discount_food" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['points_discount_total_food']) ?></span></td>
					<td width="5"></td>
					<td  align="right">-$&nbsp;</td><td align="right"><span id="OEH_plate_points_discount_org_food"><?= $this->moneyFormat($this->orderInfo['points_discount_total_food']) ?></span></td>
				</tr>

				<tr id="platePointsFeeDiscountRow">
					<td>PLATEPOINTS Discount (Service Fee)</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_plate_points_order_discount_fee" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['points_discount_total_fee']) ?></span></td>
					<td width="5"></td>
					<td  align="right">-$&nbsp;</td><td align="right"><span id="OEH_plate_points_discount_org_fee"><?= $this->moneyFormat($this->orderInfo['points_discount_total_fee']) ?></span></td>
				</tr>

				<tr id="referralRewardDiscountRow">
					<td>Referral Reward Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_referral_reward_order_discount" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['discount_total_customer_referral_credit']) ?></span></td>
					<td width="5"></td>
					<td  align="right">-$&nbsp;</td><td align="right"><span id="OEH_referral_reward_order_discount_orig"><?= $this->moneyFormat($this->orderInfo['discount_total_customer_referral_credit']) ?></span></td>
				</tr>

				<tr id="couponDiscountRow">
					<td>Coupon Discount<span id="OEH_bonus_credit">&nbsp;</span></td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_coupon_discount" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['coupon_code_discount_total']) ?></span></td>
					<td width="5"></td>
					<td align="right">-$&nbsp;</td>
					<td align="right"><span id="OEH_coupon_discount_org"><?= $this->moneyFormat($this->orderInfo['coupon_code_discount_total']) ?></span></td>
				</tr>

				<tr id="sessionDiscountRow">
					<td>Session Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right" style="color:blue;"><span id="OEH_session_discount" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['session_discount_total']) ?></span></td>
					<td width="5"></td>
					<td  align="right">-$&nbsp;</td>
					<td align="right"><span id="OEH_session_discount_org"><?= $this->moneyFormat($this->orderInfo['session_discount_total']) ?></span></td>
				</tr>

				<tr id="membershipDiscountRow">
					<td>Meal Prep+ Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right" style="color:blue;"><span id="OEH_membership_discount" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['membership_discount']) ?></span></td>
					<td width="5"></td>
					<td  align="right">-$&nbsp;</td>
					<td align="right"><span id="OEH_membership_discount_org"><?= $this->moneyFormat($this->orderInfo['membership_discount']) ?></span></td>
				</tr>


				<tr id="ServiceFeeRow">
                    <td>Service Fee</td>
                    <td  align="right" style="color:blue;">$&nbsp;</td>
                    <td align="right"><span id="OEH_subtotal_service_fee" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_service_fee']) ?></span></td>
                    <td width="5"></td>
                    <td  align="right">$&nbsp;</td>
                    <td align="right"><span id="OEH_subtotal_service_fee_org"><?= $this->moneyFormat($this->orderInfo['subtotal_service_fee']) ?></span></td>
                </tr>

				<tr id="BagFeeRow">
					<td>Bag Fee</td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_subtotal_bag_fee" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_bag_fee']) ?></span></td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_subtotal_bag_fee_org"><?= $this->moneyFormat($this->orderInfo['subtotal_bag_fee']) ?></span></td>
				</tr>

                <tr id="DeliveryFeeRow">
					<td>Delivery Fee</td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_subtotal_delivery_fee" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_delivery_fee']) ?></span></td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_subtotal_delivery_fee_org"><?= $this->moneyFormat($this->orderInfo['subtotal_delivery_fee']) ?></span></td>
				</tr>

				<tr id="MealCustomizationFeeRow">
					<td>Meal Customization Fee</td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_subtotal_meal_customization_fee" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_meal_customization_fee']) ?></span></td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_subtotal_meal_customization_fee_org"><?= $this->moneyFormat($this->orderInfo['subtotal_meal_customization_fee']) ?></span></td>
				</tr>

				<tr id="foodTaxRow">
					<td>Food Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_food_tax_subtotal" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_food_sales_taxes']) ?></span></td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_food_tax_subtotal_org"><?= $this->moneyFormat($this->orderInfo['subtotal_food_sales_taxes']) ?></span></td>
				</tr>

				<tr id="nonFoodTaxRow">
					<td><span id="nonFoodTaxLabel">Non-Food Tax</span></td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_tax_subtotal" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_sales_taxes']) ?></span></td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_tax_subtotal_org"><?= $this->moneyFormat($this->orderInfo['subtotal_sales_taxes']) ?></span></td>
				</tr>

				<tr id="DeliveryTaxRow">
					<td>Delivery Fee Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_delivery_tax_subtotal" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_delivery_tax']) ?></span></td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_delivery_tax_subtotal_org"><?= $this->moneyFormat($this->orderInfo['subtotal_delivery_tax']) ?></span></td>
				</tr>

				<tr id="ServiceTaxRow">
					<td>Services Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_service_tax_subtotal" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_service_tax']) ?></span></td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_service_tax_subtotal_org"><?= $this->moneyFormat($this->orderInfo['subtotal_service_tax']) ?></span></td>
				</tr>

				<tr id="BagFeeTaxRow">
					<td>Bag Fee Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_bag_fee_tax_subtotal" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_bag_fee_tax']) ?></span></td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_bag_fee_tax_subtotal_org"><?= $this->moneyFormat($this->orderInfo['subtotal_bag_fee_tax']) ?></span></td>
				</tr>

				<tr id="LTDRoundUpRow">
					<td>DDF Round Up</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_ltd_round_up" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['ltd_round_up_value']) ?></span></td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_ltd_round_up_org"><?= $this->moneyFormat($this->orderInfo['ltd_round_up_value']) ?></span></td>
				</tr>

				<tr>
					<td colspan="6"><hr width="100%" size="1" noshade></td>
				</tr>

				<tr>
					<td>Total</td>
					<td align="right" style="color:blue;"><b>$&nbsp;</b></td>
					<td align="right"><span id="OEH_grandtotal" style="color:blue;"><b><?= $this->moneyFormat($this->orderInfo['grand_total']) ?></b></span></td>
					<td width="5"></td>
					<td align="right">$&nbsp;</td>
					<td align="right"><?= $this->moneyFormat($this->orderInfo['grand_total'] + (isset($this->orderInfo['ltd_round_up_value']) ?  floatval($this->orderInfo['ltd_round_up_value']) : 0)) ?></td>
				</tr>


			</table>

			<table border="0" width="100%" height="100%" cellspacing="0" cellpadding="2"  class="form_field_cell" style="border:2px #808080 solid; margin-top:2px;">
				<tr>
					<td colspan="5" align="center" class ="form_subtitle_cell" style="background-color:#bcbcbc;font-weight:bold;">Summary of Payments</td>
				</tr>
				<?php
				$counter = 0;
				foreach ( $this->paymentInfo as $payment )
				{
					$is_cancelled = '';
					$is_refund = '';
					$payment_text_decor = 'text-decoration:none;';

					if (!empty($payment['delayed_payment_status']) && ($payment['delayed_payment_status'] == 'CANCELLED' || $payment['delayed_payment_status'] == 'FAIL'))
					{
						$payment_text_decor = 'text-decoration:line-through;color:#808080;';
						$is_cancelled = 'cancelled_';
					}
					else if(!empty($payment['is_delayed_payment']) && $payment['delayed_payment_status'] != 'SUCCESS')
					{
						$payment_text_decor = 'color:#808080;';
					}

					if(is_array($payment) && ($payment['payment_type'] == 'REFUND_CASH' || $payment['payment_type'] == 'REFUND' || $payment['payment_type'] == 'REFUND_STORE_CREDIT' || $payment['payment_type'] == 'REFUND_GIFT_CARD'))
					{
						$payment_text_decor = 'color:#808080;';
						$is_refund = 'refund_';
					}

					if (is_array($payment))
					{
						$paymentName = CPayment::translatePaymentTypeStr($payment['payment_info']['other']);

						if (isset($payment['credit_card_type']) && isset($payment['payment_number']['other']))
						{
							$paymentName .= ' (#' . substr($payment['payment_number']['other'], strlen($payment['payment_number']['other'])-4) . ')';
						}
						else if(isset($payment['payment_number']['other']) && is_numeric($payment['payment_number']['other']))
						{
							$paymentName .= ' (#' . $payment['payment_number']['other'] . ')';
						}

						if ($paymentName == 'Gift Cert' && isset($payment['gift_cert_id']['other']))
						{
							$paymentName .= ' (#' . $payment['gift_cert_id']['other'] . ')';
						}

						if (isset($payment['credit_card_type']))
						{
							$paymentType = $payment['credit_card_type']['other'];
						}
						else
						{
							$paymentType = "&nbsp;";
						}

						if (isset($payment['credit_card_type']))
						{
							$paymentType = $payment['credit_card_type']['other'];
						}
						else
						{
							$paymentType = "&nbsp;";
						}

						$paymentValue = $payment['total']['other'];

						if (isset($payment['delayed_date']['other']))
						{
							if (isset($payment['delayed_status']['other']) && $payment['delayed_status']['other'] == 'Status: Payment Scheduled')
							{
								$dateParts = explode(" ", $payment['delayed_date']['other']);
								$dateParts[2] = str_replace("-", "/", $dateParts[2]);
								$paymentDate = date("m.d.Y", strtotime($dateParts[2]));
							}
							elseif (isset($payment['delayed_status']['other']) && $payment['delayed_status']['other'] == 'Status: CANCELLED')
							{
								$paymentDate = date("m.d.Y", strtotime($payment['last_modified_date']));
							}
							else
							{
								$dateParts = explode(" - ", $payment['delayed_date']['other']);
								$paymentDate = date("m.d.Y", strtotime($dateParts[0]));
							}
						}
						else
						{
							$dateParts = explode(" - ", $payment['paymentDate']['other']);
							$paymentDate = date("m.d.Y", strtotime($dateParts[0]));
						}

						echo '<tr>';
						echo '<td id="payment_sum_row_' . $payment['id'] . '" style="' . $payment_text_decor . 'text-align:right;">' . ++$counter . ')</td>';
						echo '<td id="payment_sum_name_' . $payment['id'] . '" style="' . $payment_text_decor . '">' . $paymentName . '</td>';
						echo '<td id="payment_sum_type_' . $payment['id'] . '" style="' . $payment_text_decor . '">' . $paymentType . '</td>';
						echo '<td id="' . $is_refund . $is_cancelled . 'payment_sum_value_' . $payment['id'] . '" style="' . $payment_text_decor . 'text-align:right;">' . $paymentValue . '</td>';
						echo '<td id="payment_sum_date_' . $payment['id'] . '" style="' . $payment_text_decor . 'text-align:right;">' . $paymentDate . '</td>';
						echo '</tr>';
					}
				}
				?>
			</table>
			<?php include $this->loadTemplate('admin/order_mgr_header_other_orders.tpl.php'); ?>
		</td>
	</tr>

		<tr valign="top">
			<td width="50%" valign="top">
				<table width="100%" class="form_field_cell" cellpadding="0" cellspacing="0">
					<tr>
						<td>

				<table width="100%">
				<tr>
					<td>New Total</td>
					<td align="right" width="10"><b>$&nbsp;</b></td>
					<td align="right" width="50"><b><span id="OEH_new_total"><?= $this->moneyFormat($this->orderInfo['grand_total']) ?></span></b></td>
				</tr>
				<tr>
					<td>Current Total</td>
					<td align="right"><b>$&nbsp;</b></td>
					<td align="right"><b><span id="OEH_org_total"><?= $this->moneyFormat($this->orderInfo['grand_total'] + (isset($this->orderInfo['ltd_round_up_value']) ?  floatval($this->orderInfo['ltd_round_up_value']) : 0)) ?></span></b></td>
				</tr>
				</table>

				<hr align="left" style="padding: 1px; margin:0px;" width="100%" size="1" noshade="noshade" />

				<table width="100%">
				<tr style="color:blue;">
					<td>Difference</td>
					<td align="right" width="10"><b>$&nbsp;</b></td>
					<td align="right" width="50"><b><span id="OEH_delta"><?= $this->moneyFormat(0) ?></span></b></td>
				</tr>
				</table>

				<br />

							<table width="100%">
								<tr>
									<td>Existing Payments Total</td>
									<td align="right" width="10"><b>$&nbsp;</b></td>
									<td align="right" width="50"><b><span id="OEH_paymentsTotal"><?= $this->moneyFormat($this->paymentInfo['paymentsTotal']) ?></span></b></td>
								</tr>
								<tr id="balanceRow" style="color:blue;">
									<td>Balance Remaining</td>
									<td align="right"><b>$&nbsp;</b></td>
									<td align="right"><b><span id="OEH_remaing_balance"><?= $this->moneyFormat($this->moneyFormat($this->orderInfo['grand_total']) - $this->paymentInfo['paymentsTotal']) ?></span></b></td>
								</tr>
							</table>

							<table width="100%">
								<tr>

									<td style="text-align: right;">
									<?php if (!$this->discountEligable['limited_access'] && $this->orderState != 'CANCELLED'){ ?>
									<span id="gd_cancel_order-<?php echo $this->orderInfo['id']; ?>" data-user_id="<?php echo $this->orderInfo['user_id']; ?>" data-store_id="<?php echo $this->orderInfo['store_id']; ?>" data-session_id="<?php echo $this->sessionInfo['id']; ?>" data-order_id="<?php echo $this->orderInfo['id']; ?>" data-bounce="main.php?page=admin_main" class="button">Cancel Order</span>
								<?php } ?>
									<input class="button" type="button" value="Reset to Current" onClick="resetPage();"></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
</table>