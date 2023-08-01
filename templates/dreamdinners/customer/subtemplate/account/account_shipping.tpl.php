<div class="form-row">
	<h2 class="text-green-dark text-uppercase font-weight-bold text-left font-size-medium-small mb-4 ml-xs-2">Home Delivery Address</h2>
	<?php if ($this->hasUpComingOrders) { ?>
		<div class="form-group col-md-12">
			<p class="text-muted font-size-small mb-0">*Editing your address here will not update an existing order. Please contact the store to change your address on an existing order.</p>
		</div>
	<?php } ?>
</div>
<div class="form-row">
	<div class="form-group col-md-12">
		<?php echo $this->form_account['shipping_address_line1_html']; ?>
	</div>
</div>
<div class="form-row">
	<div class="form-group col-md-12">
		<?php echo $this->form_account['shipping_address_line2_html']; ?>
	</div>
</div>
<div class="form-row">
	<div class="form-group col-md-12">
		<?php echo $this->form_account['shipping_city_html']; ?>
	</div>
</div>
<div class="form-row">
	<div class="form-group col-md-6">
		<?php echo $this->form_account['shipping_state_id_html']; ?>
	</div>
	<div class="form-group col-md-6">
		<?php echo $this->form_account['shipping_postal_code_html']; ?>
	</div>
</div>
<div class="form-row">
	<div class="form-group col-md-12">
		<?php echo $this->form_account['shipping_address_note_html']; ?>
	</div>
</div>