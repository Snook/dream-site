<div id="payment1_cc" class="collapse">
	<table width="100%">
		<tr class="form_field_cell">
			<td valign="top">Credit Card Amount</td>
			<td class="form_field_cell">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<?php echo $this->form_direct_order['payment1_cc_total_amount_html'] ?>
				</div>
				<label id="payment1_cc_total_amount_lbl" for="payment1_cc_total_amount" message="Please enter the total amount of credit card payment."></label>
			</td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Name On Credit Card</td>
			<td class="form_field_cell">
				<?php echo $this->form_direct_order['payment1_ccNameOnCard_html']?><br />
				<label id="payment1_ccNameOnCard_lbl" for="payment1_ccNameOnCard" message="Please enter the name of the cardholder."></label>
			</td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Credit Card Type</td>
			<td class="form_field_cell">
				<?php echo $this->form_direct_order['payment1_ccType_html']?>
				<label id="payment1_ccType_lbl" for="payment1_ccType" message="Please choose a payment type."></label>
			</td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Credit Card Number</td>
			<td class="form_field_cell">
				<?php echo $this->form_direct_order['payment1_ccNumber_html']?>
				<label id="payment1_ccNumber_lbl" for="payment1_ccNumber" message="Please enter your credit card number."></label>
			</td>
		</tr>
		<tr class="form_field_cell">
			<td valign="top" class="form_field_cell">Expiration Date</td>
			<td class="form_field_cell">
				<table width="100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td width="50%">
							<?php echo $this->form_direct_order['payment1_ccMonth_html']?><br />
							<label id="payment1_ccMonth_lbl" for="payment1_ccMonth" message="Please enter an expiration month."></label>
						</td>
						<td width="50%">
							<?php echo $this->form_direct_order['payment1_ccYear_html']?>
							<label id="payment1_ccYear_lbl" for="payment1_ccYear" message="Please enter an expiration year."></label>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Security Code</td>
			<td class="form_field_cell">
				<?php echo $this->form_direct_order['payment1_cc_security_code_html']?>
				<label id="payment1_cc_security_code_lbl" for="payment1_cc_security_code" message="Please enter the security code printed on your credit card."></label>
			</td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Billing Street Address</td>
			<td class="form_field_cell"><?php echo $this->form_direct_order['billing_address_1_html']?></td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Billing City</td>
			<td class="form_field_cell"><?php echo $this->form_direct_order['billing_city_1_html']?></td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Billing State</td>
			<td class="form_field_cell"><?php echo $this->form_direct_order['billing_state_id_1_html']?></td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Billing Zip Code</td>
			<td class="form_field_cell"><?php echo $this->form_direct_order['billing_postal_code_1_html']?></td>
		</tr>
		<?php if ( isset($this->form_direct_order['payment1_is_store_specific_flat_rate_deposit_delayed_payment_html'])) { ?>
			<tr class="delayedPaymentSection form_field_cell collapse <?php echo ($this->canDelayPayment ? "show" : ""); ?>">
				<td class="form_field_cell">Delay Payment</td>
				<td>
					<?php echo $this->form_direct_order['payment1_is_store_specific_flat_rate_deposit_delayed_payment_html']['0'] ?>
					<?php echo $this->form_direct_order['payment1_is_store_specific_flat_rate_deposit_delayed_payment_html']['1'] ?>
					<?php if ($this->store_specific_deposit != 20.00) { ?>
						<?php echo $this->form_direct_order['payment1_is_store_specific_flat_rate_deposit_delayed_payment_html']['2'] ?>
					<?php } ?>
					<div id="payment1_CC_DP_note" style="color:red; display:none;"></div>
				</td>
			</tr>
		<?php } ?>
		<?php if (isset($this->form_direct_order['save_cc_as_ref_1_html'])) { ?>
			<tr>
				<td valign="top" class="form_field_cell">Save Card</td>
				<td class="form_field_cell">
					<?php echo $this->form_direct_order['save_cc_as_ref_1_html'] ?>
				</td>
			</tr>
		<?php } ?>
	</table>
</div>

<div id="payment1RefundCCDiv" class="collapse">
	<table width="100%">
		<tr class="form_subtitle_cell"><td colspan="2" style="text-align:center"><b>Refund Credit Card</b></td></tr>
		<?php
		$RefundCCCount = 0;
		if (!empty($this->refTransArray)) {
			foreach($this->refTransArray as $id => $val)
			{
				if ($id !== 0 )
				{
					$cap = $val['amount'];
					if (!empty($val['current_refunded_amount'])) {
						$cap = $val['amount'] - $val['current_refunded_amount'];
					}

					?>
					<tr class="form_subtitle_cell"><td colspan="2"><b>Refund <?php echo $val['cc_type']?> <?php echo substr($val['card_number'],strlen($val['card_number']) - 4,4)?></b></td></tr>
					<tr>
						<td class="form_field_cell">Transaction Number</td>
						<td class="form_field_cell"><?php echo $id?></td>
					</tr>
					<?php 	if ($cap == 0) { ?>
					<tr>
						<td class="form_field_cell">Max amount</td>
						<td class="form_field_cell"><span style="color:red">This payment has been fully refunded</span></td>
					</tr>
				<?php } else if (!empty($val['current_refunded_amount'])) { ?>
					<tr>
						<td class="form_field_cell">Max amount</td>
						<td class="form_field_cell">$<?php echo CTemplate::moneyFormat($val['amount'] - $val['current_refunded_amount']) ?> <span style="font-size:smaller;">( Orig. Amount: $<?php echo $val['amount']?> <br />minus $<?php echo $val['current_refunded_amount']?> already refunded )</span></td>
					</tr>
				<?php } else { ?>
					<tr>
						<td class="form_field_cell">Max amount</td>
						<td class="form_field_cell">$<?php echo $val['amount']?></td>
					</tr>
				<?php } ?>
					<tr>
						<td class="form_field_cell">Amount to Credit</td>
						<td class="form_field_cell">
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text">$</div>
								</div>
								<input class="form-control" type="text" name="Dir_Cr_RT_<?php echo $id?>" id="Dir_Cr_RT_<?php echo $id?>" onkeyup="validateRefTrans(this, <?php echo $cap?>);" <?php if ($cap == 0) echo 'disabled="disabled"';?> />
							</div>
						</td>
					</tr>
					<tr>
						<td class="form_field_cell" colspan="2"><hr /></td>
					</tr>

					<?php
				} } }
		?>
	</table>
</div>