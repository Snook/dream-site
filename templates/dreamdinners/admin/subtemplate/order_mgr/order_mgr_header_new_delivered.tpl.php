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
					<td><span id="OEH_box_count_label">Total Boxes Count</span></td>
					<td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_number_boxes" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td></td>
					<td style="display:none" align="right">0</td>
				</tr>
				<tr>
					<td><span id="OEH_item_count_label">Total Item Count</span></td>
					<td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_item_count" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td></td>
					<td style="display:none" align="right">0</td>
				</tr>
                <tr>
					<td>Total Number of Servings</td>
					<td align="left" >&nbsp;&nbsp;</td>
					<td align="right"><span id="OEH_number_servings" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td></td>
					<td style="display:none" align="right">0</td>
				</tr>
				<tr >
					<td>Total Boxes Cost</td>
					<td  align="right" style="color:blue;">$&nbsp;</td>
					<td align="right">
						<span id="OEH_menu_subtotal" style="color:blue;">0</span>
					</td>
					<td width="5"></td>
					<td  style="display:none"  align="right">$&nbsp;</td><td style="display:none"  align="right">0</td>
				</tr>

				<tr id="directDiscountRow">
					<td>Direct Order Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_direct_order_discount" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td  style="display:none"  align="right">-$&nbsp;</td>
					<td style="display:none" align="right"><span id="OEH_direct_order_discount_org">0</span></td>
				</tr>

				<tr id="couponDiscountRow">
					<td>Coupon Discount<span id="OEH_bonus_credit">&nbsp;</span></td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_coupon_discount" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none" align="right">-$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_coupon_discount_org">0</span></td>
				</tr>

				<tr id="membershipDiscountRow">
					<td>Meal Prep+ Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right" style="color:blue;"><span id="OEH_membership_discount" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td  style="display:none" align="right">-$&nbsp;</td>
					<td  style="display:none" align="right">0</td>
				</tr>

				<tr id="dinnerDollarsDiscountRow">
					<td>Dinner Dollars Discount</td>
					<td  align="right" style="color:blue;">-$&nbsp;</td>
					<td align="right"><span id="OEH_plate_points_order_discount_fee" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td  align="right">-$&nbsp;</td><td align="right">0</td>
				</tr>

                <tr id="DeliveryFeeRow">
                    <td>Delivery Fee</td>
                    <td  align="right" style="color:blue;">$&nbsp;</td>
                    <td align="right"><span id="OEH_subtotal_delivery_fee" style="color:blue;">0</span></td>
                    <td width="5"></td>
                    <td style="display:none"  align="right">$&nbsp;</td>
                    <td  style="display:none" align="right"><span id="OEH_subtotal_delivery_fee_org">0</span></td>
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

				<tr id="DeliveryTaxRow">
					<td>Delivery Fee Tax</td>
					<td align="right" style="color:blue;">$&nbsp;</td>
					<td align="right"><span id="OEH_delivery_tax_subtotal" style="color:blue;">0</span></td>
					<td width="5"></td>
					<td style="display:none" align="right">$&nbsp;</td>
					<td  style="display:none" align="right"><span id="OEH_delivery_tax_subtotal_org">0</span></td>
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
		</td>
	</tr>

</table>