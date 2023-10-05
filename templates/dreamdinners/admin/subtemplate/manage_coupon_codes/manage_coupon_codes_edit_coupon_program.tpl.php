<form id="coupon_program_edit" name="coupon_program_edit" data-coupon_id="<?php echo (!empty($this->coupon->id)) ? $this->coupon->id : 'new'; ?>">

	<table style="width: 100%;">
		<thead>
		<tr>
			<th class="bgcolor_medium header_row">Property</th>
			<th class="bgcolor_medium header_row">Value</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>Program Name</td>
			<td>
				<input type="text" id="coupon_code_title" name="coupon_code_title" data-message="Please enter a title." value="<?php echo $this->coupon->coupon_code_title; ?>" required/>
			</td>
		</tr>
		<tr>
			<td>Customer Type</td>
			<td>
				<select id="applicable_customer_type" name="applicable_customer_type" data-message="Please select a customer type." required>
					<option>Select</option>
					<option value="DD_MARKETING"<?php echo ($this->coupon->applicable_customer_type == 'DD_MARKETING') ? ' selected="selected"' : ''; ?>>Marketing</option>
					<option value="OTHER"<?php echo ($this->coupon->applicable_customer_type == 'OTHER') ? ' selected="selected"' : ''; ?>>Other</option>
				</select>
			</td>
		</tr>
		</tbody>
		<tfoot>
		<tr>
			<td></td>
			<td><span class="btn btn-primary btn-sm" id="coupon_program_save">Save Program</span></td>
		</tr>
		</tfoot>
	</table>

</form>