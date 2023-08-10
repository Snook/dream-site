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
					<td><span id="OEH_item_count_label">Total Item Count</span></td>
					<td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_item_count" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td></td>
					<td style="display:none" align="right">0</td>
				</tr>
                <?php if ($this->PlatePointsRulesVersion > 1) { ?>
                <tr>
					<td><?= $this->order_minimum_header_label?></td>
                    <td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_number_core_servings" style="color:blue;">0</span>/<span id="OEH_required_servings"><?php echo $this->requiredMinimum ?></span></td>

					<td width="5"></td>
                    <td></td>
                    <td style="display:none" align="right">0/<?php echo $this->requiredMinimum ?></td>
                </tr>
                <?php } ?>
                <tr>
					<td>Total Number of Servings</td>
					<td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_number_servings" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td></td>
					<td style="display:none" align="right">0</td>
				</tr>
				<tr >
					<td>Menu Items Cost</td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right">
						<span id="OEH_menu_subtotal" style="color:blue;">0</span>
					</td>
					<td width="5"></td>
					<td  style="display:none"  align="right">$&nbsp;</td><td style="display:none"  align="right">0</td>
				</tr>

				<tr id="miscFoodRow">
					<td>Misc Food Cost</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_misc_food_subtotal" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td align="right" style="display:none">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_misc_food_subtotal_org">0</span></td>
				</tr>

				<tr>
					<td>Food Items Subtotal</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_food_cost_subtotal" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td  style="display:none" align="right">0</td>
				</tr>

				<tr id="productSubtotalRow">
					<td>Enrollments</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_products_subtotal" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_products_subtotal_org">0</span></td>
				</tr>

				<tr id="miscNonFoodRow">
					<td>Misc Non-Food Cost</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_misc_nonfood_subtotal" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td align="right"  style="display:none">$&nbsp;</td>
					<td align="right"  style="display:none"><span id="OEH_misc_nonfood_subtotal_org">0</span></td>
				</tr>

				<tr id="preferredUserDiscountRow">
					<td>Preferred Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_preferred" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td   style="display:none" align="right">-$&nbsp;</td>
					<td  style="display:none" align="right">0</td>
				</tr>
				<tr id="directDiscountRow">
					<td>Direct Order Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_direct_order_discount" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td  style="display:none"  align="right">-$&nbsp;</td>
					<td style="display:none" align="right"><span id="OEH_direct_order_discount_org">0</span></td>
				</tr>

				<tr id="platePointsDiscountRow">
					<td>
						Dinner Dollars Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_plate_points_order_discount" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td  align="right" style="display:none">-$&nbsp;</td><td align="right"  style="display:none"><span id="OEH_plate_points_discount_org">0</span></td>
				</tr>

				<tr id="referralRewardDiscountRow">
					<td>Referral Reward Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_referral_reward_order_discount" style="color:blue;"><?= $this->moneyFormat($this->orderInfo['discount_total_customer_referral_credit']) ?></span></td>
					<td width="5"></td>
					<td  align="right" style="display:none">-$&nbsp;</td><td align="right"  style="display:none"><span id="OEH_referral_reward_order_discount_orig"><?= $this->moneyFormat($this->orderInfo['discount_total_customer_referral_credit']) ?></span></td>
				</tr>

				<tr id="couponDiscountRow">
					<td>Coupon Discount<span id="OEH_bonus_credit">&nbsp;</span></td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_coupon_discount" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none" align="right">-$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_coupon_discount_org">0</span></td>
				</tr>

				<tr id="sessionDiscountRow">
					<td>Session Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right" style="color:blue;"><span id="OEH_session_discount" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td  style="display:none" align="right">-$&nbsp;</td>
					<td  style="display:none" align="right">0</td>
				</tr>

				<tr id="membershipDiscountRow">
					<td>Meal Prep+ Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right" style="color:blue;"><span id="OEH_membership_discount" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td  style="display:none" align="right">-$&nbsp;</td>
					<td  style="display:none" align="right">0</td>
				</tr>

				<tr id="ServiceFeeRow">
					<td>Service Fee</td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_subtotal_service_fee" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none"  align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_subtotal_service_fee_org">0</span></td>
				</tr>

				<tr id="BagFeeRow">
					<td>Bag Fee</td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_subtotal_bag_fee" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none"  align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_subtotal_bag_fee_org">0</span></td>
				</tr>

                <tr id="DeliveryFeeRow">
                    <td>Delivery Fee</td>
                    <td  align="right" style="color:blue;">$&nbsp;</td>
                    <td align="right"><span id="OEH_subtotal_delivery_fee" style="color:blue;">0</span></td>
                    <td width="5"></td>
                    <td style="display:none"  align="right">$&nbsp;</td>
                    <td  style="display:none" align="right"><span id="OEH_subtotal_delivery_fee_org">0</span></td>
                </tr>

				<tr id="MealCustomizationFeeRow">
					<td>Meal Customization Fee</td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_subtotal_meal_customization_fee" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td  align="right">$&nbsp;</td>
					<td align="right"><span id="OEH_subtotal_meal_customization_fee_org">0</span></td>
				</tr>

                <tr id="foodTaxRow">
					<td>Food Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_food_tax_subtotal" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_food_tax_subtotal_org">0</span></td>
				</tr>

				<tr id="nonFoodTaxRow">
					<td><span id="nonFoodTaxLabel">Non-Food Tax</span></td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_tax_subtotal" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_tax_subtotal_org">0</span></td>
				</tr>

				<tr id="ServiceTaxRow">
					<td>Services Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_service_tax_subtotal" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_service_tax_subtotal_org">0</span></td>
				</tr>

				<tr id="BagFeeTaxRow">
					<td>Bag Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_bag_fee_tax_subtotal" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_bag_fee_tax_subtotal_org">0</span></td>
				</tr>

				<tr id="DeliveryTaxRow">
					<td>Delivery Fee Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_delivery_tax_subtotal" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_delivery_tax_subtotal_org">0</span></td>
				</tr>

				<tr id="LTDRoundUpRow">
					<td>DDF Round Up</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_ltd_round_up" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td style="display:none" align="right"><span id="OEH_ltd_round_up_org">0</span></td>
				</tr>

				<tr>
					<td colspan="6"><hr width="100%" size="1" noshade></td>
				</tr>

				<tr>
					<td>Total</td>
					<td align="right" style="color:blue;"><b>$&nbsp;</b></td>
					<td align="right"><span id="OEH_grandtotal" style="color:blue;"><b>0</b></span></td>
					<td width="5"></td>
					<td style="display:none"  align="right">$&nbsp;</td>
					<td  style="display:none" align="right">0</td>
				</tr>


			</table>

			<table border="0" width="100%" height="100%" cellspacing="0" cellpadding="2"  class="form_field_cell" style="border:2px #808080 solid; margin-top:2px;">
				<tr>
					<td colspan="5" align="center" class ="form_subtitle_cell" style="background-color:#bcbcbc;font-weight:bold;">Summary of Payments</td>
				</tr>
			</table>
			<?php include $this->loadTemplate('admin/order_mgr_header_other_orders.tpl.php'); ?>
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
									<td align="right" width="50"><b><span id="OEH_org_total">0</span></b></td>
								</tr>
							</table>

							<hr align="left" style="padding: 1px; margin:0px;" width="100%" size="1" noshade="noshade" />

							<table width="100%">
								<tr>
									<td>Existing Payments Total</td>
									<td align="right" width="10"><b>$&nbsp;</b></td>
									<td align="right" width="50"><b><span id="OEH_paymentsTotal">0</span></b></td>
								</tr>
								<tr id="balanceRow" style="color:blue;">
									<td>Balance Remaining</td>
									<td align="right"><b>$&nbsp;</b></td>
									<td align="right"><b><span id="OEH_remaing_balance">0</span></b></td>
								</tr>
							</table>

							<table width="100%">
								<tr>
									<td colspan="2"></td>
									<td colspan="1" align="right">
									</td>
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