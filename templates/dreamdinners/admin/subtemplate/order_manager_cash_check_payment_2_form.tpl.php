<div id="payment2_cash" style="display: none;">
	<br />
	<table>
		<tr>
			<td class="form_field_cell">Please enter cash Amount</td>
			<td class="form_field_cell">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<?= $this->form_direct_order['payment2_cash_total_amount_html'] ?>
				</div>
				<label
						id="payment2_cash_total_amount_lbl"
						name="payment2_cash_total_amount_lbl"
						message="Please enter the payment amount."></label>
			</td>
		</tr>
	</table>
</div>

<div id="payment2_check" style="display: none;">
	<br />
	<table>
		<tr>
			<td class="form_field_cell">Please enter check Amount</td>
			<td class="form_field_cell">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<?= $this->form_direct_order['payment2_check_total_amount_html'] ?>
				</div>
				<label
						id="payment2_check_total_amount_lbl"
						name="payment2_check_total_amount_lbl"
						message="Please enter the payment amount."></label>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell">Check Number</td>
			<td class="form_field_cell" style="padding-left: 6px;"><?= $this->form_direct_order['payment2_check_payment_number_html'] ?>
			</td>
		</tr>
	</table>
</div>