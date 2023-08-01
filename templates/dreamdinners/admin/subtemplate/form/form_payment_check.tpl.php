<div class="form-row">
	<div class="form-group col-md-8">
		<label class="font-weight-bold" for="ccNumber">Check amount</label>
		<div class="input-group">
			<div class="input-group-prepend">
				<div class="input-group-text">$</div>
			</div>
			<?php echo $this->form_payment['payment_check_html']; ?>
		</div>
	</div>
	<div class="form-group col-md-4">
		<label class="font-weight-bold" for="ccNumber">Check number</label>
		<?php echo $this->form_payment['payment_number_html']; ?>
	</div>
</div>
