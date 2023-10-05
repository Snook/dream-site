<div id="discountsDiv">
	<table style="width:100%">
		<tbody>
		<tr>
			<td style="text-align: center;">
				<h3><?php echo ($this->discountEligable['limited_access']) ? "Discounts are disabled." : "Discounts"; ?></h3>
			</td>
		</tr>
		<tr>
			<td>
				<table style="width:100%">
					<?php if ($this->discountEligable['direct_order']) { ?>
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Direct Order Discount</td>
							<td class="bgcolor_light">
								<div class="input-group">
									<div class="input-group-prepend">
										<div class="input-group-text">$</div>
									</div>
									<?php echo $this->form_direct_order['direct_order_discount_html']; ?>
								</div>
							</td>
						</tr>
					<?php } else { ?>
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Direct Order Discount</td>
							<td class="bgcolor_light" style="font-style: italic;">Order ineligible for Direct Order Discount
							</td>
						</tr>
					<?php } ?>
				</table>
			</td>
		</tr>

		<?php if (!$this->discountEligable['dinner_dollars']) { ?>
			<tr style="margin:0px;">
				<td style="margin:0px;">
					<table style="margin: 0px; width: 100%;">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Dinner Dollars Discount</td>
							<td class="bgcolor_light" style="font-style: italic;">Order ineligible for Dinners Dollars</td>
						</tr>
					</table>
				</td>
			</tr>
		<?php } else if (isset($this->form_direct_order['plate_points_discount_html'])) { ?>
		<tr>
			<td>
				<table>
					<tr>
						<td class="bgcolor_dark catagory_row" style="width:200px;">
							Dinner Dollars Discount
						</td>
						<td>
							<table>
								<tbody>
								<tr class="bgcolor_lighter">
									<td width="40%">Total available including applied</td>
									<td>$&nbsp; <span id="plate_points_available"><?php echo $this->maxPPCredit; ?></span></td>
								</tr>
								</tbody>
								<tbody id="tbody_max_plate_points_deduction">
								<tr class="bgcolor_lighter">
									<td width="40%">Total Allowed This Order</td>
									<td>$&nbsp; <span id="max_plate_points_deduction"><?php echo $this->maxPPDeduction; ?></span></td>
								</tr>
								</tbody>
								<tbody>
								<tr class="bgcolor_lighter">
									<td width="40%">&nbsp;Applied amount</td>
									<td>
										<div class="input-group">
											<div class="input-group-prepend">
												<div class="input-group-text">$</div>
											</div>
											<?php echo $this->form_direct_order['plate_points_discount_html']; ?>
										</div>
										<span id="pp_discountable_cost_msg" style="display: none">There is currently no discountable cost on this order.</span>
									</td>
								</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<?php } else { ?>
		<tr style="margin:0px;">
			<td style="margin:0px;">
				<table style="margin: 0px; width: 100%;">
					<tr>
						<td class="bgcolor_dark catagory_row" style="width: 200px;">Dinner Dollars Discount</td>
						<td class="bgcolor_light" style="font-style: italic;"><?php echo (!empty($this->noPPReason)) ? $this->noPPReason : ''; ?></td>
					</tr>
				</table>
			</td>
		</tr>
		<?php } ?>

		<?php if (!$this->discountEligable['coupon_code']) { ?>
			<tr style="margin:0px;">
				<td style="margin:0px;">
					<table style="margin: 0px; width: 100%;">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Coupon Code</td>
							<td class="bgcolor_light" style="font-style: italic;">Order ineligible for Coupon Code</td>
						</tr>
					</table>
				</td>
			</tr>
		<?php } else { ?>
			<tr>
				<td>
					<table style="width:100%">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Coupon Code</td>
							<td class="bgcolor_light" valign="middle"><?php echo $this->form_direct_order['coupon_code_html']; ?>&nbsp;
								<input type="button" class="btn btn-primary btn-sm" onclick="processCode();" value="Submit Code" id="couponCodeSubmitter" name="couponCodeSubmitter" />
								<span id="proc_mess" style="display: none;"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/throbber_processing_noborder.gif" alt="Processing" /></span>
								<input type="button" class="btn btn-primary btn-sm" onclick="removeCode();" value="Delete Coupon" id="couponDeleter" name="couponDeleter" style="display: none;" />
								<input type="hidden" value="<?php echo isset($this->couponVal) ? $this->couponVal : ''; ?>" name="couponValue" id="couponValue">
								<input type="hidden" value="<?php echo isset($this->coupon['discount_method']) ? $this->coupon['discount_method'] : ''; ?>" name="coupon_type" id="coupon_type">
								<input type="hidden" value="<?php echo isset($this->coupon['id']) ? $this->coupon['id'] : ''; ?>" name="coupon_id" id="coupon_id">
								<input type="hidden" value="<?php echo isset($this->coupon['id']) ? $this->coupon['id'] : ''; ?>" name="org_coupon_id" id="org_coupon_id">
								<input type="hidden" value="<?php echo isset($this->coupon['free_entree_servings']) ? $this->coupon['free_entree_servings'] : ''; ?>" name="free_entree_servings" id="free_entree_servings">
								<br />
								<span id="coupon_error" class="warning_text"></span>
							</td>
						</tr>
						<?php
						$showFreeMeal = false;
						$showFreeFT = false;
						$showFreeEFL = false;
						if (isset($this->coupon) && $this->coupon['discount_method'] == 'FREE_MEAL')
						{
							$showFreeMeal = true;
						}
						?>
						<tr id="coupon_free_meal_row" style="display:<?php echo $showFreeMeal ? 'table-row' : 'none'; ?>">
							<td class="bgcolor_dark catagory_row">Free Entr&eacute;e</td>
							<td class="bgcolor_light"><span id="free_entree"><?php echo isset($this->coupon['free_entree_title']) ? $this->coupon['free_entree_title'] : ''; ?></span></td>
						</tr>
						<tr id="coupon_select_free_menu_item_row" style="display:<?php echo $showFreeEFL ? 'table-row' : 'none'; ?>">
							<td class="bgcolor_dark catagory_row">Free Menu Item</td>
							<td class="bgcolor_light">
								<div id="free_entree_select"><select id="free_menu_item_coupon" name="free_menu_item_coupon" data-message="Please select an item." style="width: 98%;" /></div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		<?php } ?>
		<tr>
			<td height="10"></td>
		</tr>
		</tbody>
	</table>
</div>