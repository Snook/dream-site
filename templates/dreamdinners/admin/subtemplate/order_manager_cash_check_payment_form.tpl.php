
<div id="payment1_cash" style="display: none;">
	<table width="100%">
		<tr>
			<td class="form_field_cell">Please enter cash Amount</td>
			<td class="form_field_cell">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<?= $this->form_direct_order['payment1_cash_total_amount_html'] ?>
				</div>
				(Dollar amount 00.00)<br /> <label id="payment1_cash_total_amount_lbl"
				name="payment1_cash_total_amount_lbl"
				message="Please enter the payment amount."></label>
			</td>
		</tr>
	</table>
</div>

<div id="payment1_check" style="display: none;">
	<table width="100%">
		<tr>
			<td class="form_field_cell">Please enter check Amount</td>
			<td class="form_field_cell">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<?= $this->form_direct_order['payment1_check_total_amount_html'] ?>
				</div>
				(Dollar amount 00.00)<br /> <label id="payment1_check_total_amount_lbl"
				name="payment1_check_total_amount_lbl"
				message="Please enter the payment amount."></label>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell">Check Number</td>
			<td class="form_field_cell" style="padding-left: 6px;"><?= $this->form_direct_order['payment1_check_payment_number_html'] ?>
			</td>
		</tr>
	</table>
</div>


<div id="payment1_refund_cash" style="display: none;">
	<table width="100%">
		<tr>
			<td class="form_field_cell">Please enter cash amount</td>
			<td class="form_field_cell">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<?= $this->form_direct_order['payment1_refund_cash_total_amount_html'] ?>
				</div>
				(Dollar amount 00.00)<br /> <label
				id="payment1_refund_cash_total_amount_lbl"
				name="payment1_refund_cash_total_amount_lbl"
				message="Please enter the payment amount."></label>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell">Payment Number</td>
			<td class="form_field_cell" style="padding-left: 6px;"><?= $this->form_direct_order['payment1_refund_cash_payment_number_html'] ?>
			</td>
		</tr>
	</table>
</div>
