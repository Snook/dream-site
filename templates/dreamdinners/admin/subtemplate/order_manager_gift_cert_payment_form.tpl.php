
<div id="payment1_gc" style="display: none;">
	<table width="100%">
		<tr>
			<td class="form_field_cell">Please enter Gift Certificate Amount<br /><span style="font-size:smaller">The amount entered here will not reduce<br /> the sales tax
				obligation.</span></td>
			<td class="form_field_cell">
				<div class="input-group">
					<div class="input-group-prepend">
						<div class="input-group-text">$</div>
					</div>
					<?= $this->form_direct_order['payment1_gc_total_amount_html'] ?>
				</div>
				(dollar amount - 00.00)<br /> <label
				id="payment1_gc_total_amount_lbl"
				name="payment1_gc_total_amount_lbl"
				message="Please enter the gift certificate amount."></label>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell">Gift Certificate Number</td>
			<td class="form_field_cell" style="padding-left: 6px;"><?= $this->form_direct_order['payment1_gc_payment_number_html'] ?>
				<label id="payment1_gc_payment_number_lbl"
				name="payment1_gc_payment_number_lbl"
				message="Please enter the gift certificate number."></label>
			</td>
		</tr>
		<tr>
			<td class="form_field_cell">Gift Certificate Type</td>
			<td class="form_field_cell" style="padding-left: 6px;"><?= $this->form_direct_order['payment1_gift_cert_type_html']?></td>
		</tr>
	</table>
	<br />
</div>
