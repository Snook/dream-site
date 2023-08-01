<div id="newPaymentDiv" style="display: block">
	<?php if (!empty($this->form_direct_order['payment1_type_html'])) { ?>

		<table border="0" width="100%">
			<tr align="center" class="form_subtitle_cell">
				<td colspan="2">
					<h3>New Payment</h3>
				</td>
			</tr>
			<tr>
				<td colspan="2">

					<!-- Payment 1 Type -->
					<div valign="top" id="payment1" class="form_field_cell" style="margin-bottom:10px;">
						<?php echo $this->form_direct_order['payment1_type_html']?>
						<label id="payment1_type_lbl" name="payment1_type_lbl" message="Please enter a payment type."></label>
					</div>

					<hr />

					<?php if (isset($this->PendingDP)) { ?>
						<?php include $this->loadTemplate('admin/subtemplate/order_manager_pending_delayed_payment_form.tpl.php');?>
					<?php } ?>

					<?php include $this->loadTemplate('admin/subtemplate/order_manager_gift_cert_payment_form.tpl.php');?>

					<?php include $this->loadTemplate('admin/subtemplate/order_manager_cash_check_payment_form.tpl.php');?>

					<?php include $this->loadTemplate('admin/subtemplate/order_manager_gift_card_payment_form.tpl.php');?>

					<?php include $this->loadTemplate('admin/subtemplate/order_manager_ref_payment_form.tpl.php');?>

					<?php include $this->loadTemplate('admin/subtemplate/order_manager_cc_payment_form.tpl.php');?>

					<div id="payment1_ref" style="display:none">
						<?php if (isset($this->form_direct_order['payment1_ref_total_amount_html'])) { ?>
							Charge Credit Card by referencing transaction id: <span id="p1_ref_id">---</span><br /> which used card number <span id="p1_card_num">---</span><br />
							<div class="input-group">
								<div class="input-group-prepend">
									<div class="input-group-text">Amount to Charge $</div>
								</div>
								<?php echo $this->form_direct_order['payment1_ref_total_amount_html'] ?>
							</div>
						<?php } ?>

						<?php if (!empty($this->form_direct_order['ref_payment1_is_store_specific_flat_rate_deposit_delayed_html'])) { ?>
							<table class="delayedPaymentSection collapse <?php echo ($this->canDelayPayment ? "show" : ""); ?>">
								<tr>
									<td>Delay Payment</td>
									<td>
										<?php echo $this->form_direct_order['ref_payment1_is_store_specific_flat_rate_deposit_delayed_html']['0'] ?>
										<?php echo $this->form_direct_order['ref_payment1_is_store_specific_flat_rate_deposit_delayed_html']['1'] ?>
										<?php if ($this->store_specific_deposit != 20.00) { ?>
											<?php echo $this->form_direct_order['ref_payment1_is_store_specific_flat_rate_deposit_delayed_html']['2'] ?>
										<?php } ?>
										<div id="payment1_Ref_DP_note" style="color:red; display:none;"></div>
									</td>
								</tr>
							</table>
						<?php } ?>
					</div>

					<div id="payment1_credit" style="display: none;">No charge</div>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div id="payment2" style="display: none;">
						<p class="form_subtitle_cell"><b>Additional New Payment</b></p>
						<?php echo $this->form_direct_order['payment2_type_html']?>
						<label id="payment2_type_lbl" name="payment2_type_lbl" message="Please enter a payment type."></label>
						<a href="javascript:document.getElementById('payment2_type').value='CC'; changePayment2('CC'); prepareForCCSwipe(2);">Swipe Credit Card</a><br />
						<hr />

						<?php include $this->loadTemplate('admin/subtemplate/order_manager_cash_check_payment_2_form.tpl.php');?>

						<div id="payment2_ref" style="display:none">
							<?php if (isset($this->form_direct_order['payment2_ref_total_amount_html'])) { ?>
								Charge Credit Card by referencing transaction id: <span id="p2_ref_id">---</span><br /> which used card number <span id="p2_card_num">---</span><br />
								<div class="input-group">
									<div class="input-group-prepend">
										<div class="input-group-text">Amount to Charge $</div>
									</div>
									<?php echo $this->form_direct_order['payment2_ref_total_amount_html'] ?>
								</div>
							<?php } ?>

							<?php if (!empty($this->form_direct_order['ref_payment2_is_store_specific_flat_rate_deposit_delayed_html'])) { ?>
								<table class="delayedPaymentSection collapse <?php echo ($this->canDelayPayment ? "show" : ""); ?>">
									<tr>
										<td>Delay Payment</td>
										<td>
											<?php echo $this->form_direct_order['ref_payment2_is_store_specific_flat_rate_deposit_delayed_html']['0'] ?>
											<?php echo $this->form_direct_order['ref_payment2_is_store_specific_flat_rate_deposit_delayed_html']['1'] ?>
											<?php if ($this->store_specific_deposit != 20.00) { ?>
												<?php echo $this->form_direct_order['ref_payment2_is_store_specific_flat_rate_deposit_delayed_html']['2'] ?>
											<?php } ?>
											<div id="payment2_Ref_DP_note" style="color:red; display:none;"></div>
										</td>
									</tr>
								</table>
							<?php } ?>
						</div>

						<?php include $this->loadTemplate('admin/subtemplate/order_manager_cc_payment_2_form.tpl.php');?>

					</div>
				</td>
			</tr>
		</table>
	<?php } ?>
</div>

<?php if (isset($this->PendingDP)) { ?>
	<?php include $this->loadTemplate('admin/subtemplate/order_manager_pending_dp_credit_form.tpl.php');?>
<?php } ?>