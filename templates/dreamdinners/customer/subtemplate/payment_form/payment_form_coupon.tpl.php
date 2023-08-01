<div class="row mb-2">
	<div class="col">
		<h2 class="text-uppercase font-weight-bold font-size-medium-small text-left">Coupon Code</h2>
	</div>
</div>
<div class="row mb-4">
	<div class="col">
		<form id="check-balance">
			<div class="form-row">
				<div class="form-group col-6 col-xl-4">
					<input id="add_coupon_code" type="text" class="form-control" placeholder="Promo Code" value="<?php echo (!empty($this->cart_info['coupon'])) ? $this->cart_info['coupon']['coupon_code'] : ''; ?>" <?php echo (!empty($this->cart_info['coupon']) ? 'disabled="disabled"' : ''); ?> maxlength="36" />
				</div>
				<div class="form-group col-6 col-xl-4">
					<input id="add_coupon_add" class="btn btn-primary btn-block" value="Apply" <?php echo (!empty($this->cart_info['coupon'])) ? 'disabled="disabled"' : ''; ?> />
				</div>
			</div>
		</form>
	</div>
</div>