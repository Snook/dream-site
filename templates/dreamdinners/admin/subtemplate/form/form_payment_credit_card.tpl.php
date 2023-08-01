<div class="form-row">
	<div class="form-group col-md-12">
		<label class="font-weight-bold" for="ccNameOnCard">Name on card</label>
		<?php echo $this->form_payment['ccNameOnCard_html']; ?>
	</div>
</div>
<div class="form-row">
	<div class="form-group col-md-6">
		<label class="font-weight-bold" for="ccNumber">Credit card number</label>
		<?php echo $this->form_payment['ccNumber_html']; ?>
		<div class="credit_card_warning text-muted collapse">The number does not match the selected card type.</div>
	</div>
	<div class="form-group col-md-6">
		<label class="font-weight-bold" for="ccType">Card type</label>
		<?php echo $this->form_payment['ccType_html']; ?>
	</div>
</div>
<div class="form-row">
	<div class="form-group col-4">
		<label class="font-weight-bold" for="ccMonth">Expiration month</label>
		<?php echo $this->form_payment['ccMonth_html']; ?>
	</div>
	<div class="form-group col-4">
		<label class="font-weight-bold" for="ccYear">Expiration year</label>
		<?php echo $this->form_payment['ccYear_html']; ?>
	</div>
	<div class="form-group col-4">
		<label class="font-weight-bold" for="ccSecurityCode">CVV</label>
		<?php echo $this->form_payment['ccSecurityCode_html']; ?>
	</div>
</div>