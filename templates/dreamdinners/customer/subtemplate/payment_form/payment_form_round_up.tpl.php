<div class="row mb-4 bg-gray">
	<div class="col">
		<div class="row mb-2 pt-2">
			<div class="col">
				<h2 class="text-uppercase font-weight-bold font-size-medium-small text-left text-orange">Dream Dinners Foundation Donation</h2>
			</div>
		</div>
		<div class="row">
			<div class="col-xl-6">
				<p class="font-size-small">You can make a difference in the fight against hunger. Donate to the Dream Dinners Foundation by rounding up your order to the nearest dollar or select an amount from the dropdown menu.</p>
				<?php if ($this->ltd_roundup_orders['info']['num_meals'] > 1) {?>
					<p class="font-size-small">Your donations to date have provided families with <?php echo number_format($this->ltd_roundup_orders['info']['num_meals']); ?> meals.</p>
				<?php } ?>
			</div>
			<div class="col-xl-6">
				<div class="form-row">
					<div class="form-group col-6 p-2">
						<div class="custom-control custom-checkbox text-right">
							<input id="add_ltd_round_up" name="add_ltd_round_up" class="custom-control-input" type="checkbox" <?php echo (!empty($this->cart_info['order_info']['ltd_round_up_value']) && $this->cart_info['order_info']['ltd_round_up_value'] > 0)  ? 'checked="checked"' : ''; ?> />
							<label for="add_ltd_round_up" class="custom-control-label">Round up</label>
						</div>
					</div>
					<div class="form-group col-6">
						<select id="ltd_round_up_select" class="custom-select" disabled="disabled">
							<option id="round_up_nearest_dollar" value="0" <?php echo ($this->cart_info['order_info']['ltd_round_up_value'] <= 1) ? 'selected="selected"' : ''; ?>>$ 0.00</option>
							<option value="5" <?php echo ($this->cart_info['order_info']['ltd_round_up_value'] == 5) ? 'selected="selected"' : ''; ?>>$ 5.00</option>
							<option value="10" <?php echo ($this->cart_info['order_info']['ltd_round_up_value'] == 10) ? 'selected="selected"' : ''; ?>>$ 10.00</option>
							<option value="25" <?php echo ($this->cart_info['order_info']['ltd_round_up_value'] == 25) ? 'selected="selected"' : ''; ?>>$ 25.00</option>
							<option value="54" <?php echo ($this->cart_info['order_info']['ltd_round_up_value'] == 54) ? 'selected="selected"' : ''; ?>>$ 54.00</option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
