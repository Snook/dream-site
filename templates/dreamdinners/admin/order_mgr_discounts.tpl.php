<?php $showExpiredFootNote = true;?>
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
						<tr><td colspan="2">
							<div class="dod_accordion_container">
								<a class="dod_accordion_header catagory_row" style="color: #225824;font-weight: bold;">Show Admin Order Notes &#8744; </a>
								<div class="dod_accordion_content" style="display:none">
									<table style="margin-left:10px;width:100%">
										<tr>
											<td class="" style="vertical-align: top; text-align: left;">
												<div id="gd_admin_note-two-<?php echo $this->orderInfo['id']; ?>" class="guest_note multi-populate-admin-note" data-user-id="<?php echo $this->user_obj->id; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" data-order_id="<?php echo $this->orderInfo['id']; ?>"><?php echo $this->orderInfo['order_admin_notes']; ?></div>
												<span id="gd_admin_note_button-two-<?php echo $this->orderInfo['id']; ?>" data-uid='two' data-button_name="Edit Note"  data-booking_id="<?php echo $this->orderInfo['id']; ?>" data-order_id="<?php echo $this->orderInfo['id']; ?>" data-user_id="<?php echo $this->user_obj->id; ?>" data-edit_mode="false" data-tooltip="Staff notes for this order. Not visible to guests." class="button">Edit Note</span>
												<span id="gd_admin_note_cancel_button-two-<?php echo $this->orderInfo['id']; ?>" data-booking_id="<?php echo $this->orderInfo['id']; ?>" class="button" style="display: none;">Cancel Edit</span>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</td></tr>

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

		<?php  if (!$this->discountEligable['referral_reward']) { ?>
		<tr style="margin:0px;">
			<td style="margin:0px;">
				<table style="margin: 0px; width: 100%;">
					<tr>
						<td class="bgcolor_dark catagory_row" style="width: 200px;">Referral Rewards</td>
						<td class="bgcolor_light" style="font-style: italic;">Order ineligible for Referral Rewards</td>
					</tr>
				</table>
			</td>
		</tr>
		<?php } else if (isset($this->form_direct_order['referral_reward_discount_html'])) { ?>
			<tr>
				<td>
					<table>
						<tr>
							<td class="bgcolor_dark catagory_row" style="width:200px;">
								Referral Rewards
							</td>
							<td>
								<table>
									<tbody>
									<tr class="bgcolor_lighter">
										<td width="45%">Total available including applied</td>
										<td>$&nbsp; <span id="referral_reward_available"><?php echo $this->maxReferralRewards; ?></span></td>
									</tr>
									</tbody>
									<tbody id="tbody_max_referral_reward">
									<tr class="bgcolor_lighter">
										<td width="45%">Total Allowed This Order</td>
										<td>$&nbsp; <span id="max_referral_reward_deduction"><?php echo $this->maxReferralRewardsDeduction; ?></span></td>
									</tr>
									</tbody>
									<tbody>
									<tr class="bgcolor_lighter">
										<td width="45%">&nbsp;Applied amount</td>
										<td>
											<div class="input-group">
												<div class="input-group-prepend">
													<div class="input-group-text">$</div>
												</div>
												<?php echo $this->form_direct_order['referral_reward_discount_html']; ?>
											</div>
											<span id="rr_discountable_cost_msg" style="display: none"></span>
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
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Referral Rewards</td>
							<td class="bgcolor_light" style="font-style: italic;"><?php echo (!empty($this->noReferralRewardReason)) ? $this->noReferralRewardReason : ''; ?></td>
						</tr>
					</table>
				</td>
			</tr>
		<?php } ?>

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
		<?php }
		else if (isset($this->form_direct_order['plate_points_discount_html'])) { ?>
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
										<td width="45%">Total available including applied</td>
										<td>$&nbsp; <span id="plate_points_available"><?php echo $this->maxPPCredit; ?></span></td>
									</tr>
									</tbody>
									<tbody id="tbody_max_plate_points_deduction">
									<tr class="bgcolor_lighter">
										<td width="45%">Total Allowed This Order</td>
										<td>$&nbsp; <span id="max_plate_points_deduction"><?php echo $this->maxPPDeduction; ?></span></td>
									</tr>
									</tbody>
									<tbody>
									<tr class="bgcolor_lighter">
										<td width="45%">&nbsp;Applied amount</td>
										<td>
											<div class="input-group">
												<div class="input-group-prepend">
													<div class="input-group-text">$</div>
												</div>
												<?php echo $this->form_direct_order['plate_points_discount_html']; ?>
											</div>
											<span id="pp_discountable_cost_msg" style="display: none"></span>
										</td>
									</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</table>
					<?php
					if(count($this->dd_history) > 0){?>
					<div class="dd_accordion_container">
						<a class="dd_accordion_header catagory_row" style="color: #225824;font-weight: bold;">Show Recent Dinner Dollar History &#8744; </a>
						<div class="dd_accordion_content" style="display:none">
							<ul>
								<?php
								$showExpiredFootNote = false;
									foreach ($this->dd_history as $entry)
									{
										$expiredStr = 'Expires on ' .CTemplate::dateTimeFormat($entry['expires'], MONTH_DAY_YEAR);

										$date = new DateTime($entry['expires']);
										$now = new DateTime();

										if ($date < $now)
										{
											$expiredStr = 'Expired on ' .CTemplate::dateTimeFormat($entry['expires'], MONTH_DAY_YEAR);
										}

										$orderStr = $entry['orders'];
										if ($entry['orders'] == $this->orderInfo['id'])
										{
											$orderStr = 'this order';
											if ($date < $now)
											{
												$showExpiredFootNote = true;
												$expiredStr = 'Expired. Cannot be transferred.<span style="font-weight:bold">**</span>';
											}
										}
										echo "<li>";
										switch ($entry['state'])
										{
											case 'AVAILABLE':
												if($entry['amount'] > 0){
													echo '<b>$' . $entry['amount'] . ' available. Expires on ' . CTemplate::dateTimeFormat($entry['expires'], MONTH_DAY_YEAR).'</b>';
												}
												break;
											case 'CONSUMED':
												echo '$' . $entry['amount'] . ' applied to <a href="/?page=admin_order_mgr&order=' . $entry['orders'] . '" target="_blank">' . $orderStr . '</a>. ';
												echo $expiredStr;
												break;
											case 'EXPIRED':
												echo '$' . $entry['amount'] . ' expired on ' . CTemplate::dateTimeFormat($entry['expires'], MONTH_DAY_YEAR);
												break;
										}
										echo "</li>";
									}
								?>

								<li style="list-style-type: none !important;"></li>
								<li style="list-style-type: none !important;"><a href="/?page=admin_user_plate_points&amp;id=<?php echo $this->user['id']; ?>&amp;back=<?php echo urlencode($_SERVER['REQUEST_URI']);?>" target="_blank">Full Dinner Dollar/PlatePoint History</a></li>
							</ul>
						</div>
					</div>
					<?php } ?>
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
								<input type="button" class="button" onclick="processCode();" value="Submit Code" id="couponCodeSubmitter" name="couponCodeSubmitter" />
								<span id="proc_mess" style="display: none;"><img src="<?php echo ADMIN_IMAGES_PATH; ?>/throbber_processing_noborder.gif" alt="Processing" /></span>
								<input type="button" class="button" onclick="removeCode();" value="Delete Coupon" id="couponDeleter" name="couponDeleter" style="display: none;" />
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

		<?php if (!$this->discountEligable['preferred']) { ?>
			<tr>
				<td>
					<table id="PUD_area" style="width:100%">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Preferred User Discount</td>
							<td class="bgcolor_light" style="font-style: italic;">Order ineligible for Preferred Discount</td>
						</tr>
					</table>
				</td>
			</tr>
			<?php
		}
		else if ($this->originalUP || $this->activeUP)
		{
			$checkOrg = false;
			$checkCur = false;
			$checkDoNot = false;

			if (isset($_POST['PUD']))
			{
				switch ($_POST['PUD'])
				{
					case "originalUP":
						$checkOrg = true;
						break;
					case "activeUP":
						$checkCur = true;
						break;
					case "noUP":
						$checkDoNot = true;
						break;
				}
			}
			else
			{
				if ($this->originalUP)
				{
					$checkOrg = true;
				}
				else
				{
					$checkDoNot = true;
				}
			}
			?>

			<tr>
				<td>
					<table style="width:100%">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Preferred User Discount
							</td>
							<td>
								<table id="PUD_area" style="width:100%">
									<?php if ($this->originalUP) { ?>
										<tr>
											<td class="bgcolor_light">
												<input id="PUDoriginalUP" name="PUD" value="originalUP" type="radio" onChange="calculateTotal();" <?php echo($checkOrg ? "checked" : ""); ?> />
											</td>
											<td class="bgcolor_light">
												<div>Use preferred customer discount in place at time of order.</div>
												<div>Value: <span class="font-weight-bold"><?php echo $this->originalUP['value_display']; ?></span></div>
												<div>Sides Discounted: <span class="font-weight-bold"><?php echo ((!empty($this->originalUP['include_sides'])) ? 'Yes' : 'No'); ?></span></div>
											</td>
										</tr>
									<?php }
									if ($this->activeUP) { ?>
										<tr>
											<td class="bgcolor_light">
												<input id="PUDactiveUP" name="PUD" value="activeUP" type="radio" onChange="calculateTotal();" <?php echo($checkCur ? "checked" : ""); ?> />
											</td>
											<td class="bgcolor_light">
												<div>Use current preferred customer discount.</div>
												<div>Value: <span class="font-weight-bold"><?php echo $this->activeUP['value_display']; ?></span></div>
												<div>Sides Discounted: <span class="font-weight-bold"><?php echo ((!empty($this->activeUP['include_sides'])) ? 'Yes' : 'No'); ?></span></div>
											</td>
										</tr>
									<?php } ?>
									<tr>
										<td class="bgcolor_light">
											<input id="PUDnoUP" name="PUD" value="noUP" type="radio" onChange="calculateTotal();" <?php echo($checkDoNot ? "checked" : ""); ?> />
										</td>
										<td class="bgcolor_light">Do not use a preferred customer discount.</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		<?php } else { ?>
			<tr>
				<td>
					<table id="PUD_area" style="width:100%">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Preferred User Discount</td>
							<td class="bgcolor_light"><i><?php echo $this->customerName; ?> is not a preferred customer.</i></td>
						</tr>
					</table>
				</td>
			</tr>
		<?php } ?>

		<?php if (!$this->discountEligable['preferred']) { ?>
			<tr>
				<td>
					<table style="width:100%" class="SD_area">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Session Discount</td>
							<td class="bgcolor_light" style="font-style: italic;">Order ineligible for Session Discount</td>
						</tr>
					</table>
				</td>
			</tr>
		<?php }
		else if (($this->originalSD || $this->activeSD) && $this->orderState == 'ACTIVE')
		{
			$checkOrg = false;
			$checkCur = false;
			$checkDoNot = false;

			if (isset($_POST['SessDisc']))
			{
				switch ($_POST['SessDisc'])
				{
					case "originalSD":
						$checkOrg = true;
						break;
					case "activeSD":
						$checkCur = true;
						break;
					case "noSD":
						$checkDoNot = true;
						break;
				}
			}
			else
			{
				if ($this->originalSD)
				{
					$checkOrg = true;
				}
				else
				{
					$checkDoNot = true;
				}
			}
			?>

			<tr>
				<td>
					<table style="width:100%" class="SD_area">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width:200px;">Session
																					   Discount
							</td>
							<td>
								<table style="width:100%">
									<?php if ($this->originalSD) { ?>
										<tr>
											<td class="bgcolor_light" align="right" colspan="1">
												<input id="SessDiscoriginalSD" name="SessDisc" value="originalSD" type="radio" onChange="calculateTotal();" <?php echo($checkOrg ? "checked" : ""); ?> />
											</td>
											<td class="bgcolor_light">
												Use Session Discount in place at time of order.<br />
												Type: <?php echo $this->originalSD['type']; ?> Value: <?php echo $this->originalSD['value']; ?>
											</td>
										</tr>
									<?php }
									if ($this->activeSD) { ?>
										<tr>
											<td align="right" colspan="1" class="bgcolor_light">
												<input id="SessDiscactiveSD" name="SessDisc" value="activeSD" type="radio" onChange="calculateTotal();" <?php echo($checkCur ? "checked" : ""); ?> />
											</td>
											<td class="bgcolor_light">
												Use current Session Discount.<br />
												Type: <b><?php echo $this->activeSD['type']; ?></b> Value: <b><?php echo $this->activeSD['value']; ?></b>
											</td>
										</tr>
									<?php } ?>
									<tr>
										<td align="right" colspan="1" class="bgcolor_light">
											<input id="SessDiscnoSD" name="SessDisc" value="noSD" type="radio" onChange="calculateTotal();" <?php echo($checkDoNot ? "checked" : ""); ?> />
										</td>
										<td class="bgcolor_light">Do not use a Session Discount.</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		<?php }
		else
		{
			$checkOrg = false;
			if (!empty($this->orderInfo['session_discount_id']) && $this->orderInfo['session_discount_id'] != 'null')
			{
				$checkOrg = true;
			}
			?>
			<tr id="noSessionDiscountRow" <?php echo(!empty($this->originalSD) || !empty($this->activeSD) ? 'style="display:none;"' : ""); ?>>
				<td>
					<table style="width:100%" class="SD_area">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Session Discount</td>
							<td class="bgcolor_light"><i>No session discount for this order.</i></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr id="newSessionDiscountBody" <?php echo(empty($this->originalSD) && empty($this->activeSD) ? 'style="display:none;"' : ""); ?>>
				<td>
					<table style="width:100%">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">Session Discount</td>
							<td>
								<table class="SD_area" style="width:100%">
									<tr>
										<td align="right" colspan="1" class="bgcolor_light">
											<input id="SessDiscoriginalSD" name="SessDisc" value="originalSD" type="radio" onChange="calculateTotal();" <?php echo ($checkOrg) ? 'checked="checked"' : ""; ?> />
										</td>
										<td class="bgcolor_light">Use current Session Discount.<br />
																  Type: <b><span id="sessionDiscountTypeSpan"></span></b> Value:
											<b><?php echo is_array($this->originalSD)? $this->originalSD['value']:'0'; ?>%</b><span id="sessionDiscountValueSpan"></span>
										</td>
									</tr>
									<tr>
										<td align="right" colspan="1" class="bgcolor_light">
											<input id="SessDiscnoSD" name="SessDisc" value="noSD" type="radio" onChange="calculateTotal();" <?php echo (!$checkOrg) ? 'checked="checked"' : ""; ?> />
										</td>
										<td class="bgcolor_light">Do not use a Session Discount.</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		<?php } ?>


		<?php if ($this->storeSupportsMembership) {
			if ($this->orderIsEligibleForMembershipDiscount) { ?>







		<?php
			}
		} ?>



		<?php if ((isset($this->fundraiser_show_input) && $this->fundraiser_show_input) || (isset($this->ltd_roundup_show_input) && $this->ltd_roundup_show_input)) { ?>
			<tr>
				<td style="text-align: center;">
					<h3>Reporting</h3>
				</td>
			</tr>
		<?php } ?>
		<?php if (isset($this->fundraiser_show_input) && $this->fundraiser_show_input) { ?>
			<tr>
				<td>
					<table style="width:100%">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">
								Fundraiser
								<div style="font-size: .75em">(Does not affect balance)</div>
							</td>
							<td class="bgcolor_light">
								<div><?php echo $this->form_direct_order['fundraiser_id_html']; ?></div>
								<div>
									<div class="input-group">
										<div class="input-group-prepend">
											<div class="input-group-text">$</div>
										</div>
										<?php echo $this->form_direct_order['fundraiser_value_html']; ?>
									</div>
								</div>
								<div id="fundraiser_description_div"><?php echo (!empty($this->fundraiser_description)) ? $this->fundraiser_description : ''; ?></div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		<?php } ?>
		<?php if (isset($this->ltd_roundup_show_input) && $this->ltd_roundup_show_input) { ?>
			<tr>
				<td>
					<table style="width:100%">
						<tr>
							<td class="bgcolor_dark catagory_row" style="width: 200px;">
								Dream Dinners Foundation Round Up Donation
							</td>
							<td class="bgcolor_light">
								<?php
								CForm::formElement(array(
									CForm::type => CForm::CheckBox,
									CForm::name => 'add_ltd_round_up',
									CForm::attribute => array(
										'data-org_value' => ((!empty($this->orderInfo['ltd_round_up_value']) && $this->orderInfo['ltd_round_up_value'] > 0) ? 'true' : 'false'),
									),
									CForm::checked => ((!empty($this->orderInfo['ltd_round_up_value']) && $this->orderInfo['ltd_round_up_value'] > 0) ? true : false),
									CForm::label => 'Round Up'
								));
								?>
								<select class="custom-select" id="ltd_round_up_select" name="ltd_round_up_select" disabled="disabled" data-number="true" data-org_value="<?php echo $this->orderInfo['ltd_round_up_value']; ?>">
									<option id="round_up_nearest_dollar" value="0" <?php echo ($this->orderState == 'SAVED' && $this->orderInfo['ltd_round_up_value'] <= 1) ? 'selected="selected"' : ''; ?>>$ 0.00</option>
									<?php if ($this->orderState == 'ACTIVE' && !empty($this->orderInfo['ltd_round_up_value']) && $this->orderInfo['ltd_round_up_value'] <= 1) { ?>
										<option value="<?php echo $this->orderInfo['ltd_round_up_value']; ?>" selected="selected">$ <?php echo number_format($this->orderInfo['ltd_round_up_value'], 2); ?></option>
									<?php } ?>
									<option value="2" <?php echo ($this->orderInfo['ltd_round_up_value'] == 2) ? 'selected="selected"' : ''; ?>>$ 2.00</option>
									<option value="5" <?php echo ($this->orderInfo['ltd_round_up_value'] == 5) ? 'selected="selected"' : ''; ?>>$ 5.00</option>
									<option value="10" <?php echo ($this->orderInfo['ltd_round_up_value'] == 10) ? 'selected="selected"' : ''; ?>>$ 10.00</option>
									<option value="35" <?php echo ($this->orderInfo['ltd_round_up_value'] == 35) ? 'selected="selected"' : ''; ?>>$ 35.00</option>
									<option value="54" <?php echo ($this->orderInfo['ltd_round_up_value'] == 54) ? 'selected="selected"' : ''; ?>>$ 54.00</option>
								</select>
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