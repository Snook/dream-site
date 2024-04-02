<div id="payment2_cc" class="collapse">
	<table width="100%">
		<tr class="form_field_cell">
			<td class="form_field_cell" valign="top">Credit Card Amount</td>
			<td class="form_field_cell">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<?php echo $this->form_direct_order['payment2_cc_total_amount_html'] ?>
				</div>
				<label id="payment2_cc_total_amount_lbl" for="payment2_cc_total_amount" message="Please enter the total amount of credit card payment."></label>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell" valign="top">Name On Credit Card</td>
			<td>
				<?php echo $this->form_direct_order['payment2_ccNameOnCard_html']?><br />
				<label id="payment2_ccNameOnCard_lbl" for="payment2_ccNameOnCard"
					   message="Please enter the name of the cardholder."></label>
			</td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Credit Card Type</td>
			<td class="form_field_cell"><?php echo $this->form_direct_order['payment2_ccType_html']?>
				<br /> <label id="payment2_ccType_lbl" for="payment2_ccType"
							  message="Please choose a payment type."></label>
			</td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Credit Card Number</td>
			<td class="form_field_cell"><?php echo $this->form_direct_order['payment2_ccNumber_html']?>
				<br /> <label id="payment2_ccNumber_lbl" for="payment2_ccNumber"
							  message="Please enter your credit card number."></label></td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Expiration Date</td>
			<td class="form_field_cell">
				<table width="100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td width="50%"><?php echo $this->form_direct_order['payment2_ccMonth_html']?><br />
							<label id="payment2_ccMonth_lbl" for="payment2_ccMonth"
								   message="Please enter an expiration month."></label>
						</td>
						<td width="50%"><?php echo $this->form_direct_order['payment2_ccYear_html']?>
							<br /> <label id="payment2_ccYear_lbl" for="payment2_ccYear"
										  message="Please enter an expiration year."></label>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="top" class="form_field_cell">Security Code</td>
			<td class="form_field_cell"><?php echo $this->form_direct_order['payment2_cc_security_code_html']?>
				<br /> <label id="payment2_cc_security_code_lbl"
							  for="payment2_cc_security_code"
							  message="Please enter the security code printed on your credit card."></label>
			</td>
		</tr>
		<tr class="form_field_cell">
			<td class="form_field_cell">Billing Street Address</td>
			<td><?php echo $this->form_direct_order['billing_address_2_html']?>
			</td>
		</tr>

		<tr class="form_field_cell">
			<td class="form_field_cell">Billing Zip Code</td>
			<td><?php echo $this->form_direct_order['billing_postal_code_2_html']?>
			</td>
		</tr>

		<?php if (isset($this->form_direct_order['payment2_is_store_specific_flat_rate_deposit_delayed_payment_html']) ) { ?>
			<tr class="delayedPaymentSection form_field_cell collapse <?php echo ($this->canDelayPayment ? "show" : ""); ?>">
				<td class="form_field_cell">Delay Payment</td>
				<td>
					<?php echo $this->form_direct_order['payment2_is_store_specific_flat_rate_deposit_delayed_payment_html']['0'] ?>
					<?php echo $this->form_direct_order['payment2_is_store_specific_flat_rate_deposit_delayed_payment_html']['1'] ?>
					<?php if ($this->store_specific_deposit != 20.00) { ?>
						<?php echo $this->form_direct_order['payment2_is_store_specific_flat_rate_deposit_delayed_payment_html']['2'] ?>
					<?php } ?>
					<div id="payment2_CC_DP_note" style="color:red; display:none;"></div>
				</td>
			</tr>
		<?php } ?>

		<?php if (isset($this->form_direct_order['save_cc_as_ref_2_html'])) { ?>
			<tr>
				<td valign="top" class="form_field_cell">Save Card</td>
				<td class="form_field_cell">
					<?php echo $this->form_direct_order['save_cc_as_ref_2_html'] ?>
				</td>
			</tr>
		<?php } ?>
	</table>
</div>