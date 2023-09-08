		<td width="50%" rowspan="2" valign="top" >

			<table border="0" width="100%" height="100%" cellspacing="0" cellpadding="2"  class="form_field_cell" style="border:2px #808080 solid;">
				<tr>
					<td width="100%" align="center" class ="form_subtitle_cell" style="background-color:#bcbcbc" colspan="7"><b>Order Status</b></td>
				</tr>
				<tr class ="form_subtitle_cell">
					<td colspan="2"><input type="checkbox" id="showall" name="showall" onclick="handleSummaryDisplayChange(this);" /><label for="showall">Show All Line Items</label></td>
					<td colspan="2" style="color:blue;"><b>New</b></td>
					<td></td>
					<td  style="display:none" colspan="2"><b>Current</b></td>
				</tr>

				<tr>
					<td><span id="OEH_box_count_label">Total Box Count</span></td>
					<td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_number_boxes" style="color:blue;"><?php echo count($this->current_boxes);   ?></span></td>
					<td width="5"></td>
					<td></td>
					<td style="display:none" align="right"><?php echo count($this->current_boxes);   ?></td>
				</tr>

				<tr>
					<td><span id="OEH_item_count_label">Total Item Count</span></td>
					<td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_item_count" style="color:blue;"><?= $this->orderInfo['menu_items_total_count'] ?></span></td>
					<td width="5"></td>
					<td></td>
					<td style="display:none" align="right"><?= $this->orderInfo['menu_items_total_count'] ?></td>
				</tr>

                <tr>
					<td>Total Number of Servings</td>
					<td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_number_servings" style="color:blue;"><?= $this->orderInfo['servings_total_count'] ?></span></td>
					<td width="5"></td>
					<td></td>
					<td style="display:none" align="right"><?= $this->orderInfo['servings_total_count'] ?></td>
				</tr>


				<tr >
					<td>Total Boxes Cost</td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right">
				<span id="OEH_menu_subtotal" style="color:blue;">
					<?= $this->moneyFormat($this->orderInfo['subtotal_menu_items'] - $this->orderInfo['bundle_discount']); ?>
				</span>
					</td>
					<td width="5"></td>
					<td  style="display:none"  align="right">$&nbsp;</td><td style="display:none"  align="right"><?php //changed to include markup 12/23/05 ?>
						<?= $this->moneyFormat($this->orderInfo['subtotal_menu_items'] - $this->orderInfo['bundle_discount']); ?>
					</td>
				</tr>


				<tr id="directDiscountRow">
					<td>Direct Order Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_direct_order_discount" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['direct_order_discount']) ?></span></td>
					<td width="5"></td>
					<td  style="display:none"  align="right">-$&nbsp;</td>
					<td style="display:none" align="right"><span id="OEH_direct_order_discount_org"><?= $this->moneyFormat($this->orderInfo['direct_order_discount']) ?></span></td>
				</tr>

				<tr id="couponDiscountRow">
					<td>Coupon Discount<span id="OEH_bonus_credit">&nbsp;</span></td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_coupon_discount" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['coupon_code_discount_total']) ?></span></td>
					<td width="5"></td>
					<td style="display:none" align="right">-$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_coupon_discount_org"><?= $this->moneyFormat($this->orderInfo['coupon_code_discount_total']) ?></span></td>
				</tr>

				<tr id="membershipDiscountRow">
					<td>Meal Prep+ Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right" style="color:blue;"><span id="OEH_membership_discount" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['membership_discount']) ?></span></td>
					<td width="5"></td>
					<td  style="display:none" align="right">-$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_membership_discount_org"><?= $this->moneyFormat($this->orderInfo['membership_discount']) ?></span></td>
				</tr>

				<tr id="dinnerDollarsDiscountRow">
					<td>Dinner Dollars Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_plate_points_order_discount_fee" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['points_discount_total_fee']) ?></span></td>
					<td width="5"></td>
					<td  align="right" style="display:none">-$&nbsp;</td><td align="right"  style="display:none"><span id="OEH_plate_points_discount_org_fee"><?= $this->moneyFormat($this->orderInfo['points_discount_total_fee']) ?></span></td>
				</tr>

                <tr id="DeliveryFeeRow">
                    <td>Delivery Fee</td>
                    <td  align="right" style="color:blue;">$&nbsp;</td>
                    <td align="right"><span id="OEH_subtotal_delivery_fee" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_delivery_fee']) ?></span></td>
                    <td width="5"></td>
                    <td style="display:none"  align="right">$&nbsp;</td>
                    <td  style="display:none" align="right"><span id="OEH_subtotal_delivery_fee_org"><?= $this->moneyFormat($this->orderInfo['subtotal_delivery_fee']) ?></span></td>
                </tr>

                <tr id="foodTaxRow">
					<td>Food Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_food_tax_subtotal" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_food_sales_taxes']) ?></span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_food_tax_subtotal_org"><?= $this->moneyFormat($this->orderInfo['subtotal_food_sales_taxes']) ?></span></td>
				</tr>

				<tr id="nonFoodTaxRow">
					<td><span id="nonFoodTaxLabel">Non-Food Tax</span></td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_tax_subtotal" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_sales_taxes']) ?></span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_tax_subtotal_org"><?= $this->moneyFormat($this->orderInfo['subtotal_sales_taxes']) ?></span></td>
				</tr>

				<tr id="DeliveryTaxRow">
					<td>Delivery Fee Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_delivery_tax_subtotal" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['subtotal_delivery_tax']) ?></span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_delivery_tax_subtotal_org"><?= $this->moneyFormat($this->orderInfo['subtotal_delivery_tax']) ?></span></td>
				</tr>

				<tr>
					<td colspan="6"><hr width="100%" size="1" noshade></td>
				</tr>

				<tr>
					<td>Total</td>
					<td align="right" style="color:blue;"><b>$&nbsp;</b></td>
					<td align="right"><span id="OEH_grandtotal" style="color:blue;"><b><?= $this->moneyFormat($this->orderInfo['grand_total']) ?></b></span></td>
					<td width="5"></td>
					<td style="display:none"  align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><?= $this->moneyFormat($this->orderInfo['grand_total']) ?></td>
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

					if (isset ($payment['delayed_payment_status']) && ($payment['delayed_payment_status'] == 'CANCELLED' || $payment['delayed_payment_status'] == 'FAIL'))
					{
						$payment_text_decor = 'text-decoration:line-through;color:#808080;';
						$is_cancelled = 'cancelled_';
					}
					else if(isset($payment['is_delayed_payment']) && $payment['is_delayed_payment'] && isset($payment['delayed_payment_status']) && $payment['delayed_payment_status'] != 'SUCCESS')
					{
						$payment_text_decor = 'color:#808080;';
					}

					if($payment['payment_type'] == 'REFUND_CASH' || $payment['payment_type'] == 'REFUND' || $payment['payment_type'] == 'REFUND_STORE_CREDIT' || $payment['payment_type'] == 'REFUND_GIFT_CARD')
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
		</td>
	</tr>

	<?php if (!isset($this->orderEditSuccess)) { ?>
		<tr valign="top">
			<td width="50%" valign="top">
				<table width="100%" class="form_field_cell" cellpadding="0" cellspacing="0">
					<tr>
						<td>
							<table width="100%">
								<tr>
									<td>Current Total</td>
									<td align="right" width="10"><b>$&nbsp;</b></td>
									<td align="right" width="50"><b><span id="OEH_org_total"><?= $this->moneyFormat($this->orderInfo['grand_total'] + $this->orderInfo['ltd_round_up_value']) ?></span></b></td>
								</tr>
							</table>

							<hr align="left" style="padding: 1px; margin:0px;" width="100%" size="1" noshade="noshade" />

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
									<td colspan="2"></td>
									<?php if ($this->orderState != 'NEW') { ?>
										<td colspan="1">
											<div>
												<div style="float:right">
													<span id="gd_delete_delivered_order-<?php echo $this->orderInfo['id']; ?>" data-user_id="<?php echo $this->orderInfo['user_id']; ?>" data-store_id="<?php echo $this->orderInfo['store_id']; ?>"
													 data-session_id="<?php echo $this->sessionInfo['id'] ?>" data-order_id="<?php echo $this->orderInfo['id']; ?>" data-bounce="?page=admin_order_mgr_delivered&user=<?php echo $this->orderInfo['user_id']; ?>"  class="button">Delete Saved Order</span>
												</div>
											</div>
										</td>
									<?php } else { ?>
										<td colspan="1" align="right">
										</td>
									<?php  } ?>
								</tr>
								<tr>
									<td colspan="3" align="center"><div id="help_msg" style="display: none; color:red;"></div></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	<?php } ?>
</table>